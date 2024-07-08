<?php while ($level = array_shift($levels)) : ?>
    <?php
    if (! $level->Name) {
        continue;
    }
    ?>
    <tr class="more-details wlm-user-level-details" data-levelid="<?php echo esc_attr($level->Level_ID); ?>">
    <td class="table-form-container text-center pr-sm-0 pl-sm-0 level-tools-sm">
        <div class="btn-group-action">
        <?php if (false !== $level->Timestamp && ! $level->Scheduled) : ?>
            <a href="#" title="Move Level" data-userid="<?php echo esc_attr($uid); ?>" data-lvlname="<?php echo esc_attr($level->Name); ?>" data-levelid="<?php echo esc_attr($level->Level_ID); ?>" class="wlm-icons md-24 move-level-btn d-lg-inline d-md-inline">swap_horiz</a>
            <a href="#" title="<?php echo esc_attr($level->Cancelled ? 'Uncancel from Level' : 'Cancel from Level'); ?>" data-userid="<?php echo esc_attr($uid); ?>" data-lvlname="<?php echo esc_attr($level->Name); ?>" data-levelid="<?php echo esc_attr($level->Level_ID); ?>" class="wlm-icons md-24 d-lg-inline d-md-inline <?php echo esc_attr($level->Cancelled ? 'uncancel' : 'cancel'); ?>-level-btn"><?php echo $level->Cancelled ? 'replay' : 'close'; ?></a>
            <a href="#" title="Remove from Level" data-userid="<?php echo esc_attr($uid); ?>" data-lvlname="<?php echo esc_attr($level->Name); ?>" data-levelid="<?php echo esc_attr($level->Level_ID); ?>" class="wlm-icons md-24 delete-level-btn d-lg-inline d-md-inline">remove_circle_outline</a>
        <?php endif; ?>
        </div>
    </td>
    <!-- <td style="padding-left: 34px"> -->
    <td style="padding-left: 9px">
            <?php
            if ($level->Expired) :
                ?>
                <i class="wlm-icons md-24 level-icon color-orange" title="Expired" style="cursor: default;">timer_off</i>
            <?php endif; ?>
        <?php
        if ($level->Cancelled) :
            ?>
                <i class="wlm-icons md-24 level-icon color-red" title="Cancelled" style="cursor: default;">cancelled_icon</i>
        <?php endif; ?>
        <?php
        if ($level->UnConfirmed) :
            ?>
                <i class="wlm-icons md-24 level-icon color-blue02" title="Unconfirmed" style="cursor: default;">needs_confirm</i>
        <?php endif; ?>
        <?php
        if ($level->Pending) :
            ?>
                <i class="wlm-icons md-24 level-icon color-blue02" title="Needs Approval" style="cursor: default;">needs_approval</i>
        <?php endif; ?>
        <?php if (! $level->Expired && ! $level->Cancelled && ! $level->UnConfirmed && ! $level->Pending) : ?>
                <?php if (in_array('Scheduled', $level->Status)) : ?>
            <i class="wlm-icons md-24 level-icon color-blue02" title="Scheduled" style="cursor: default;">date_range</i>
                <?php else : ?>
            <i class="wlm-icons md-24 level-icon color-green" title="Active" style="cursor: default;">active_icon</i>
                <?php endif; ?>
        <?php endif; ?>
        &nbsp;
        <span class='wlm-level-name' style="vertical-align: middle;">
            <?php
            $lname = $level->Name;
            if ($level->Expired || $level->Cancelled) {
                printf('<strike>%s</strike>', esc_html($lname));
            } elseif ($level->UnConfirmed || $level->Pending) {
                printf('<em>%s</em>', esc_html($lname));
            } else {
                echo esc_html($lname);
            }
            ?>
        </span>
    </td>
    <td>
        <span class='wlm-level-details'>
        <?php
            $this_status = [];
        if (! in_array('Active', $level->Status) && ! in_array('Scheduled', $level->Status)) {
            $level_pending    = [];
            $pending_statuses = $level->Status;
            foreach ($pending_statuses as $pending_status) {
                if ('For Approval' === $pending_status) {
                    // Check if the reason for "For Approval" is due to a pending payment in Payment Provider,
                    // So far only Pin Payments and Paypal are the SC's that sends pending notifications.
                    if (in_array($level->Pending, ['Paypal Pending', 'Pin Payments Confirmation'])) {
                        $level_pending[] = $pending_status . ' ' . $level->Pending;
                    } else {
                        // $_link = sprintf('<small><a href="#" data-schedule-type="approve" data-level-id="%s" data-userid="%d" class="wlm-icons md-24 text-success unschedule-level-btn">active_icon</a></small>', $level->Level_ID, $wlUser->ID );
                        // $level_pending[] = "{$_link} {$pending_status}";
                        $level_pending[] = sprintf('<a href="#" title="Approve" data-schedule-type="approve" data-level-id="%s" data-userid="%d" class="scheduled-action-btn -approve unschedule-level-btn"><i class="wlm-icons md-20">check_circle</i></a> Needs Approval', $level->Level_ID, $wlUser->ID);
                    }
                } else {
                    $status_date = '';
                    if ($level->Expired) {
                        $status_date = __(' on ', 'wishlist-member') . date_i18n(get_option('date_format'), $level->ExpiryDate + $this->gmt);
                    } elseif ($level->Cancelled) {
                        $status_date = __(' on ', 'wishlist-member') . date_i18n(get_option('date_format'), $level->CancelledDate + $this->gmt);
                    }
                    $level_pending[] = $pending_status . $status_date;
                }
            }
            $this_status = $level_pending;
        }
        if (in_array('Scheduled', $level->Status)) {
            $_link         = sprintf('<a href="#" title="Unschedule" data-schedule-type="%s" data-level-id="%s" data-userid="%d" class="scheduled-action-btn -remove unschedule-level-btn"><i class="wlm-icons md-20">remove_circle</i></a>', $level->ScheduleInfo['type'], $level->Level_ID, $wlUser->ID);
            $str_action    = 'move' === $level->ScheduleInfo['type'] ? ucwords($level->ScheduleInfo['type']) . " from {$wpm_levels[$level->ScheduleInfo['level_from']]['name']}" : ucwords($level->ScheduleInfo['type']);
            $this_status[] = sprintf('<span>%s %s to %s on %s</span>', $_link, $str_action, $level->Name, date_i18n(get_option('date_format'), wlm_strtotime($level->ScheduleInfo['date']) + $this->gmt));
        } else {
            if ($level->SequentialCancelled && $level->Active) {
                echo '<span><em>Sequential Upgrade stopped</em></span>';
            }
            $more_schedules = [];
            $remove         = $this->Get_UserLevelMeta($wlUser->ID, $level->Level_ID, 'scheduled_remove');
            // Check if serialized data, added this part because the result has '";' added to it.
            if (maybe_unserialize($remove)) {
                $cnt = 0;
                while (! is_array($remove) && $cnt < 4) {
                    $remove = wlm_maybe_unserialize($remove);
                    ++$cnt;
                }
            }
            // End -> need to double check on this issue.
            if ($remove) {
                // GMT value should be subtracted since remove['date'] are scheduled and processed in localized/WordPress time value.
                $more_schedules['remove'] = strtotime($remove['date']) - $this->gmt;
            }
            if ($level->CancelDate && ! $level->Cancelled) {
                $more_schedules['cancel'] = $level->CancelDate;
            }
            if ($level->ExpiryDate && ! $level->Expired) {
                $more_schedules['expire'] = $level->ExpiryDate - $this->gmt;
            }
            if ($more_schedules) {
                asort($more_schedules);
            }
            foreach ($more_schedules as $key => $date) {
                $event        = '';
                $event_reason = '';
                $_link        = '';
                switch ($key) {
                    case 'remove':
                        $event = __('be Removed', 'wishlist-member');
                        $_link = sprintf('<a href="#" title="Unschedule" data-schedule-type="remove" data-level-id="%s" data-userid="%d" class="scheduled-action-btn -remove unschedule-level-btn"><i class="wlm-icons md-20">remove_circle</i></a>', $level->Level_ID, $wlUser->ID);
                        break;
                    case 'cancel':
                        $event = __('be Cancelled', 'wishlist-member');
                        if ($level->CancelDateReason) {
                            $event_reason = wishlistmember_instance()->tooltip(
                                wlm_arrval($level, 'CancelDateReason', 'text'),
                                '',
                                true,
                                [
                                    'icon-class' => 'md-20 align-bottom ml-1',
                                    'icon'       => wlm_arrval($level, 'CancelDateReason', 'icon') ? wlm_arrval('lastresult') : 'help',
                                ]
                            );
                        }
                        $_link = sprintf('<a href="#" title="Unschedule" data-schedule-type="cancel" data-level-id="%s" data-userid="%d" class="scheduled-action-btn -remove unschedule-level-btn"><i class="wlm-icons md-20">remove_circle</i></a>', $level->Level_ID, $wlUser->ID);
                        break;
                    case 'expire':
                        $event = 'Expire';
                        break;
                }
                if ($event) {
                    $this_status[] = sprintf('<span>%s To %s on %s</span> %s', $_link, $event, wlm_date(get_option('date_format'), $date), $event_reason);
                }
            }
        }
        if (! empty($this_status)) {
            echo wp_kses_post(implode(', ', $this_status));
        }
        ?>
        </span>
    </td>
    <td>
        <span class='wlm-level-date'>
            <?php echo ( false !== $level->Timestamp && ! $level->Scheduled ) ? esc_html(wlm_date(get_option('date_format'), $level->Timestamp)) : ''; ?>
        </span>
    </td>
    <td></td>
    </tr>
<?php endwhile; ?>
<?php if (wlm_arrval($wlUser, 'pay_per_posts', '_all_')) : ?>
    <tr class="more-details wlm-user-level-details">
    <td class="table-form-container xtext-center xpr-sm-0 xpl-sm-0 xlevel-tools-sm">
    </td>
    <td style="padding-left: 9px">
        <a href="#" title="<?php esc_attr_e('Pay Per Posts History', 'wishlist-member'); ?>" data-userid="<?php echo esc_attr($uid); ?>" data-tab-focus="#pay-per-posts,#ppphistory" class="edit-user-btn">
        <i class="wlm-icons md-24 level-icon" title="Pay Per Posts" style="cursor: default;">description</i>
        &nbsp;
        <span class="text-body"><?php esc_html_e('Pay Per Posts', 'wishlist-member'); ?></span>
        </a>
    </td>
    </tr>
<?php endif; ?>
