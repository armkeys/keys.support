<?php
global $wp_roles;
$_roles = $wp_roles->roles;

$wpm_useraddress = $profileuser->wpm_useraddress;
$wpm_useraddress = is_array($wpm_useraddress) ? $wpm_useraddress : [];

$registered = wlm_date($this->get_date_time_format(), $this->user_registered($profileuser, false));
$regip      = $profileuser->wpm_registration_ip;

// Fix issue when no login record shows date in 1970/1969.
if (( (int) $profileuser->wpm_login_date ) > 0) {
    $lastlogin = wlm_date($this->get_date_time_format(), (int) $profileuser->wpm_login_date);
} else {
    $lastlogin = __('No login record yet.', 'wishlist-member');
}

$loginip = __('No login record yet.', 'wishlist-member');
if (! empty($profileuser->wpm_login_ip) && $profileuser->wpm_login_ip) {
    $loginip = $profileuser->wpm_login_ip;
}

$today_loggedin_ips = (array) $profileuser->wpm_login_counter;
$today              = wlm_date('Ymd');
foreach ((array) $today_loggedin_ips as $k => $v) {
    if ($v != $today) {
        unset($today_loggedin_ips[ $k ]);
    }
}


$user_roles = array_keys($profileuser->caps);

$level_history = $wlUser->get_history('level');
$ppp_history   = $wlUser->get_history('ppp');

$scheduled_level_actions = apply_filters('wishlistmember_get_user_scheduled_level_actions', [], $profileuser->ID);
$login_history = $wlUser->get_history('login');
$rss_history   = $wlUser->get_history('rss');
?>
<div role="tabpanel" class="tab-pane active" id="member-info" >
    <input type="hidden" name="userid" value="<?php echo esc_attr($profileuser->ID); ?>" />
    <div class="row">
        <div class="col-md-6 col-sm-6 col-xs-6">
            <div class="form-group">
                <label><?php esc_html_e('First Name', 'wishlist-member'); ?></label>
                <input type="text" class="form-control" value="<?php echo esc_attr($profileuser->first_name); ?>" name="first_name"  placeholder="<?php esc_attr_e('First Name', 'wishlist-member'); ?>" />
            </div>
        </div>
        <div class="col-md-6 col-sm-6 col-xs-6">
            <div class="form-group">
                <label><?php esc_html_e('Last Name', 'wishlist-member'); ?></label>
                <input type="text" class="form-control" value="<?php echo esc_attr($profileuser->last_name); ?>" name="last_name"  placeholder="<?php esc_attr_e('Last Name', 'wishlist-member'); ?>" />
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 col-sm-6 col-xs-6">
            <div class="form-group">
                <label><?php esc_html_e('Email', 'wishlist-member'); ?></label>
                <input type="text" class="form-control" value="<?php echo esc_attr($profileuser->user_email); ?>" name="user_email"  placeholder="<?php esc_attr_e('Email', 'wishlist-member'); ?>" required="true"/>
            </div>
        </div>
        <div class="col-md-6 col-sm-6 col-xs-6">
            <div class="form-group">
                <label><?php esc_html_e('Email Broadcasts', 'wishlist-member'); ?></label>
                <?php $checked = $profileuser->wlm_unsubscribe ? '' : 'checked'; ?>
                <div class="form-check -with-tooltip">
                    <label for="wlm_unsubscribe" class="cb-container">
                        <input class="form-check-input" type="checkbox" id="wlm_unsubscribe" name="wlm_unsubscribe" uncheck_value="1" value="0" <?php echo esc_attr($checked); ?>/>
                        <span class="marker checkmark"></span>
                          <span class="text-content"><?php esc_html_e('Subscribed', 'wishlist-member'); ?></span>
                    </label>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 col-sm-6 col-xs-6">
            <div class="form-group">
                <label><?php esc_html_e('Display name publicly as', 'wishlist-member'); ?></label>
                <select style="width: 100%" name="display_name" class="form-control wlm-levels">
                    <?php $selected = $profileuser->first_name == $profileuser->display_name ? $selected = 'selected' : ''; ?>
                     <option value="<?php echo esc_attr($profileuser->first_name); ?>" <?php echo esc_attr($selected); ?> ><?php echo esc_html($profileuser->first_name); ?></option>
                    <?php $selected = $profileuser->user_login == $profileuser->display_name ? $selected = 'selected' : ''; ?>
                     <option value="<?php echo esc_attr($profileuser->user_login); ?>" <?php echo esc_attr($selected); ?> ><?php echo esc_html($profileuser->user_login); ?></option>
                    <?php $selected = $profileuser->last_name == $profileuser->display_name ? $selected = 'selected' : ''; ?>
                     <option value="<?php echo esc_attr($profileuser->last_name); ?>" <?php echo esc_attr($selected); ?> ><?php echo esc_html($profileuser->last_name); ?></option>
                    <?php $fl = $profileuser->first_name . ' ' . $profileuser->last_name; ?>
                    <?php $selected = $fl == $profileuser->display_name ? $selected = 'selected' : ''; ?>
                     <option value="<?php echo esc_attr($fl); ?>" <?php echo esc_attr($selected); ?> ><?php echo esc_html($fl); ?></option>
                    <?php $lf = $profileuser->last_name . ' ' . $profileuser->first_name; ?>
                    <?php $selected = $lf == $profileuser->display_name ? $selected = 'selected' : ''; ?>
                     <option value="<?php echo esc_attr($lf); ?>" <?php echo esc_attr($selected); ?> ><?php echo esc_html($lf); ?></option>
                </select>
            </div>
        </div>
        <div class="col-md-6 col-sm-6 col-xs-6">
            <div class="form-group membership-level-select">
                <label>
                    <?php esc_html_e('Role', 'wishlist-member'); ?>
                    <?php $this->tooltip(__('The WordPress Role for each Level is set in the Levels section. Each new member will be assigned that set WordPress Role by default when they join the Level. <br> <br> The WordPress Role for an individual Member can be changed here on a per Member basis.', 'wishlist-member'), 'lg'); ?>
                </label>
                <?php
                /*
                    This is a fix for an issue where updating the user's data through the Modal removes the admin role of the user.
                 *  This happens when a user has multiple roles (eg. Administrator and bbp_keymaster).
                 *  In this case the bbp_keymaster will be the one selected instead of the Administrator role.
                 *  We want the Administrator role to be the one selected on the dropdown.
                 */
                $default_roles  = ['administrator', 'editor', 'author', 'contributor', 'subscriber'];
                $u_role         = $user_roles;
                if (count($user_roles) >= 2) {
                    $u_role = array_intersect($user_roles, $default_roles);
                    if (empty($u_role)) {
                        $u_role = $user_roles;
                    }
                }
                ?>
                <select style="width: 100%" name="role" class="form-control wlm-levels" <?php echo get_current_user_id() == $profileuser->ID ? 'disabled' : ''; ?>>
                    <?php foreach ($_roles as $rk => $_role) : ?>
                        <?php $selected = in_array($rk, $u_role) ? $selected = 'selected' : ''; ?>
                        <option value="<?php echo esc_attr($rk); ?>" <?php echo esc_attr($selected); ?> ><?php echo esc_html($_role['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 col-sm-6 col-xs-6">
            <div class="form-group">
                <label><?php esc_html_e('Username', 'wishlist-member'); ?></label>
                <input type="text" class="form-control" value="<?php echo esc_attr($profileuser->user_login); ?>" disabled="true" />
            </div>
        </div>
        <div class="col-md-6 col-sm-6 col-xs-6">
            <div class="form-group mb-3">
                <label><?php esc_html_e('Password', 'wishlist-member'); ?></label>
                <div class="form-inline input-group -form-tight" style="display: none">
                    <div class="form-control -pw-form-control">
                        <input type="text" class="form-control password-field" value="" required="true" autocomplete="new-password" />
                        <span class="form-control input-group-addon pass-status text-center"></span>
                    </div>
                    <div class="input-group-append">
                        <button class="btn -default generate-password">
                            <?php esc_html_e('Generate', 'wishlist-member'); ?>
                        </button>
                    </div>
                    <div class="input-group-append">
                        <button class="btn -bare hide-password-field">
                            <?php esc_html_e('Cancel', 'wishlist-member'); ?>
                        </button>
                    </div>
                </div>
                <button type="button" class="btn btn-success form-control -condensed show-password-field" >
                    <?php esc_html_e('Change Password', 'wishlist-member'); ?>
                </button>
                <br>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 col-sm-6 col-xs-6 text-center">
            <a href="#" class="btn -outline -no-icon -condensed resend-reset-link-btn btn-block" data-user-login="<?php echo esc_attr($profileuser->user_login); ?>">
                <span class="text"><?php esc_html_e('Send Reset Password Link to Member', 'wishlist-member'); ?></span>
            </a>
        </div>
        <div class="col-md-6 col-sm-6 col-xs-6 text-center">
            <?php
                $sessions = WP_Session_Tokens::get_instance($profileuser->ID);
                $sessions = $sessions->get_all();
            ?>
            <?php if (count($sessions) > 0) : ?>
                <a href="#" class="btn -outline -condensed -no-icon logout-everywhere-btn btn-block" data-user-id="<?php echo esc_attr($profileuser->ID); ?>"><span class="text"><?php esc_html_e('Log Out Everywhere', 'wishlist-member'); ?></span></a>
            <?php else : ?>
                <a href="#" class="btn -outline -condensed -no-icon -disabled disabled" disabled="true" style="width: 80%;"><span class="text"><?php esc_html_e('Log Out Everywhere', 'wishlist-member'); ?></span></a>
            <?php endif; ?>
        </div>
    </div>
</div>
<div role="tabpanel" class="tab-pane" id="member-address">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label><?php esc_html_e('Company', 'wishlist-member'); ?></label>
                <input type="text" class="form-control" value="<?php echo esc_attr(stripslashes($wpm_useraddress['company'])); ?>" name="wpm_useraddress[company]"  placeholder="Company" />
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="form-group no-margin">
                <label>Street</label>
                <input type="text" class="form-control" value="<?php echo esc_attr(stripslashes($wpm_useraddress['address1'])); ?>" name="wpm_useraddress[address1]"  placeholder="Street Address" />
            </div>
            <div class="form-group">
                <label class="d-md-none d-sm-none" for="">&nbsp;</label>
                <input type="text" class="form-control" value="<?php echo esc_attr(stripslashes($wpm_useraddress['address2'])); ?>" name="wpm_useraddress[address2]"  placeholder="Street Address 2" />
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 col-sm-6 col-xs-6">
            <div class="form-group">
                <label>City/Town</label>
                <input type="text" class="form-control" value="<?php echo esc_attr(stripslashes($wpm_useraddress['city'])); ?>" name="wpm_useraddress[city]"  placeholder="City/Town" />
            </div>
        </div>
        <div class="col-md-6 col-sm-6 col-xs-6">
            <div class="form-group">
                <label><?php esc_html_e('State/Province', 'wishlist-member'); ?></label>
                <input type="text" class="form-control" value="<?php echo esc_attr(stripslashes($wpm_useraddress['state'])); ?>" name="wpm_useraddress[state]"  placeholder="State/Province" />
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 col-sm-6 col-xs-6">
            <div class="form-group mb-sm-1">
                <label>Country</label>
                <select class="form-control wlm-select-country" name="wpm_useraddress[country]" style="width: 100%;">
                    <?php foreach ((array) $this->countries() as $country) : ?>
                        <?php
                            $country_val = 'Select Country' === $country ? '' : $country;
                            $selected    = '';
                        if (isset($profileuser->wpm_useraddress['country'])) {
                            $selected = $country_val == $profileuser->wpm_useraddress['country'] ? ' selected ' : '';
                        }
                        ?>
                        <option value="<?php echo esc_attr($country_val); ?>" <?php echo esc_attr($selected); ?>><?php echo esc_html($country); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="col-md-6 col-sm-6 col-xs-6">
            <div class="form-group mb-sm-1">
                <label>Zip/Postal Code</label>
                <input type="text" class="form-control" value="<?php echo esc_attr(stripslashes($wpm_useraddress['zip'])); ?>" name="wpm_useraddress[zip]"  placeholder="Zip/Postal Code" />
            </div>
        </div>
    </div>
</div>
<div role="tabpanel" class="tab-pane" id="member-level">
    <div class="horizontal-tabs">
        <div class="row no-gutters">
            <div class="col-12 col-md-auto">
                <!-- Nav tabs -->
                <div class="horizontal-tabs-sidebar" style="min-width: 100px;">
                    <ul class="nav nav-tabs -h-tabs flex-column" role="tablist">
                            <li role="presentation" class="nav-item">
                                <a href="#manage-edit-levels" class="nav-link pp-nav-link active" aria-controls="manage-edit-levels" role="tab" data-type="manage-edit-levels" data-title="Levels" data-toggle="tab">Levels</a>
                            </li>
                            <?php if (count($scheduled_level_actions) > 0) : ?>
                                <li role="presentation" class="nav-item">
                                    <a href="#manage-edit-scheduled" class="nav-link pp-nav-link" aria-controls="manage-edit-scheduled" role="tab" data-type="manage-edit-scheduled" data-title="Scheduled Actions" data-toggle="tab">Scheduled</a>
                                </li>
                            <?php endif; ?>
                            <li role="presentation" class="nav-item">
                                <a href="#manage-edit-history" class="nav-link pp-nav-link" aria-controls="manage-edit-history" role="tab" data-type="manage-edit-history" data-title="Levels History" data-toggle="tab">History</a>
                            </li>
                    </ul>
                </div>
            </div>
            <div class="col">
                <!-- Tab panes -->
                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane active" id="manage-edit-levels">
                            <div class="row">
                                <div class="col-md-5 mr-0 pr-0">
                                    <div class="form-group mb-3">
                                        <select class="form-control wlm-levels add-wlm-levels" id="" style="width: 100%">
                                            <option value="">- Select a Level -</option>
                                            <?php foreach ($wpm_levels as $key => $value) : ?>
                                                <?php $disabled = in_array($key, $mlevels) ? 'disabled' : ''; ?>
                                                <option value="<?php echo esc_attr($key); ?>" <?php echo esc_attr($disabled); ?>><?php echo esc_html($value['name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="ml-2 pl-2" style="width: 36%">
                                    <div class="form-group mb-3">
                                        <select class="form-control wlm-levels add-wlm-levels-email" id="" style="width: 100%">
                                            <option value="sendlevel">Use Level Notification Settings</option>
                                            <option value="send">Send Email Notification</option>
                                            <option value="dontsend">Do NOT Send Email Notification</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-3">
                                        <a href="#" class="btn -primary -condensed add-userlevel-btn" user-id="<?php echo esc_attr($profileuser->ID); ?>">
                                            <i class="wlm-icons">add</i>
                                            <span class="text"><?php esc_html_e('Add Level', 'wishlist-member'); ?></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="table-wrapper table-responsive -with-input">
                                <table class="table table-striped table-condensed table-fixed">
                                    <thead>
                                         <tr class="d-flex">
                                             <th class="col-4"><?php esc_html_e('Level Name', 'wishlist-member'); ?></th>
                                             <th class="col-3">
                                                <?php esc_html_e('Transaction ID', 'wishlist-member'); ?>
                                                <?php $this->tooltip(__('A Transaction ID is assigned to a Member during account creation/registration. <br><br>The format of the Transaction ID will be based on the Integrated Payment Provider. In many cases, the Transaction ID ties the member to a payment in the payment provider.<br><br>If no integration with a ShoppingCart is set up, the Transaction ID format will be the standard WishList Member format. Example: WL-0-0000000000', 'wishlist-member'), 'lg'); ?>
                                             </th>
                                             <th colspan="2" class="col-5"><?php esc_html_e('Registration Date', 'wishlist-member'); ?></th>
                                         </tr>
                                    </thead>
                                    <tbody class="user-level-holder" style="max-height: 300px">
                                        <?php $xlevels = preg_grep('/^\d+$/', $mlevels); ?>
                                        <?php if (count($xlevels) > 0) : ?>
                                            <?php foreach ($wpm_levels as $levelid => $level) : ?>
                                                <?php if (in_array($levelid, $xlevels)) : ?>
                                                    <?php $level_cancelled = $this->level_cancelled($levelid, $profileuser->ID); ?>
                                                     <tr class="d-flex button-hover">
                                                         <td class="col-4 pt-3">
                                                             <span class="table-td">
                                                                 <span class="levelname-holder">
                                                                    <?php
                                                                    if (! $level_cancelled) {
                                                                        echo esc_html($level['name']);
                                                                    } else {
                                                                        echo '<strike>' . esc_html($level['name']) . '</strike>';
                                                                    }
                                                                    ?>
                                                                 </span>
                                                                <?php
                                                                    $lvl_parent = $this->level_parent($levelid, $profileuser->ID);
                                                                    $lvl_parent = $lvl_parent && isset($wpm_levels[ $lvl_parent ]) ? $wpm_levels[ $lvl_parent ]['name'] : '';
                                                                ?>
                                                                <?php if ($lvl_parent) : ?>
                                                                     <i class="wlm-icons pull-right" title="Parent Level: <?php echo esc_attr($lvl_parent); ?>">person</i>
                                                                <?php endif; ?>
                                                             </span>
                                                         </td>
                                                         <td class="col-3">
                                                            <?php $txnid = $this->get_membership_levels_txn_id($profileuser->ID, $levelid); ?>
                                                             <input type="text" class="form-control" value="<?php echo esc_attr($txnid); ?>" name="txnid[<?php echo esc_attr($levelid); ?>]"  placeholder="<?php esc_attr_e('Transaction ID', 'wishlist-member'); ?>" />
                                                         </td>
                                                         <td class="col-3">
                                                            <?php
                                                                $datetime_format = get_option('date_format') . ' ' . get_option('time_format'); // Get the date format from the WP settings.
                                                                $reg_date = gmdate($datetime_format, $this->user_level_timestamp($profileuser->ID, $levelid) + $this->gmt);
                                                                $reg_date = $reg_date ? $reg_date : '';
                                                            ?>
                                                             <input id="DateRangePicker" type="text" class="form-control wlm-datetimepicker" value="<?php echo esc_attr($reg_date); ?>" name="lvltime[<?php echo esc_attr($levelid); ?>]"  placeholder="<?php esc_attr_e('Registration Date', 'wishlist-member'); ?>" />
                                                         </td>
                                                         <td class="col-2">
                                                            <div class="btn-group-action pull-right pt-1">
                                                                <a href="#" user-id="<?php echo esc_attr($profileuser->ID); ?>" level-id="<?php echo esc_attr($levelid); ?>" level-name="<?php echo esc_attr($level['name']); ?>" class="btn cancel-level-btn -del-btn
                                                                                                <?php
                                                                                                if ($level_cancelled) {
                                                                                                    echo 'd-none';
                                                                                                }
                                                                                                ?>
                                                                " title="Cancel from Level"><span class="wlm-icons md-24 -icon-only">close</span></a>
                                                                <a href="#" user-id="<?php echo esc_attr($profileuser->ID); ?>" level-id="<?php echo esc_attr($levelid); ?>" level-name="<?php echo esc_attr($level['name']); ?>" class="btn uncancel-level-btn -del-btn
                                                                                                <?php
                                                                                                if (! $level_cancelled) {
                                                                                                    echo 'd-none';
                                                                                                }
                                                                                                ?>
                                                                " title="Uncancel from Level"><span class="wlm-icons md-24 -icon-only">replay</span></a>
                                                                <a href="#" user-id="<?php echo esc_attr($profileuser->ID); ?>" level-id="<?php echo esc_attr($levelid); ?>" class="btn remove-level-btn -del-btn" title="Remove from Level"><span class="wlm-icons md-24 -icon-only">delete</span></a>
                                                            </div>
                                                         </td>
                                                     </tr>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        <?php else : ?>
                                             <tr class="tr-none"><td class="text-center" colspan="4">No membership levels</td></tr>
                                        <?php endif; ?>
                                     </tbody>
                                 </table>
                            </div>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="manage-edit-history">
                            <div class="table-wrapper table-responsive -with-input">
                                <table class="table table-striped table-condensed table-fixed">
                                    <thead>
                                         <tr class="d-flex">
                                             <th class="col-3"><?php esc_html_e('Action', 'wishlist-member'); ?></th>
                                             <th class="col-5"><?php esc_html_e('Levels', 'wishlist-member'); ?></th>
                                             <th class="col-4"><?php esc_html_e('Date', 'wishlist-member'); ?></th>
                                         </tr>
                                    </thead>
                                    <tbody class="user-level-holder" style="max-height: 350px">
                                        <?php if (count($level_history) > 0) : ?>
                                            <?php foreach ($level_history as $i => $history) : ?>
                                                <?php
                                                    $meta_value        = isset($history->log_value) ? wlm_maybe_unserialize($history->log_value) : [];
                                                    $history_timestamp = '-';
                                                if (isset($history->date_added)) {
                                                    $history_timestamp = $this->format_date($history->date_added);
                                                }
                                                ?>
                                                 <tr class="d-flex">
                                                     <td class="col-3"><?php echo isset($history->log_key) ? esc_html(ucwords($history->log_key)) : '-'; ?></td>
                                                     <td class="col-5"><?php echo isset($meta_value['level_names']) ? esc_html($meta_value['level_names']) : '-'; ?></td>
                                                     <td class="col-4"><?php echo esc_html($history_timestamp); ?></td>
                                                 </tr>
                                            <?php endforeach; ?>
                                        <?php else : ?>
                                             <tr class="tr-none"><td class="text-center" colspan="3">No membership level history</td></tr>
                                        <?php endif; ?>
                                     </tbody>
                                 </table>
                            </div>
                    </div>
                    <?php if (count($scheduled_level_actions) > 0) : ?>
                        <div role="tabpanel" class="tab-pane" id="manage-edit-scheduled">
                                     <div class="table-wrapper table-responsive -with-input mt-2">
                                        <table class="table table-striped table-condensed table-fixed">
                                            <thead>
                                                 <tr class="d-flex">
                                                     <th class="col-8"><?php esc_html_e('Scheduled Actions', 'wishlist-member'); ?></th>
                                                     <th class="col-4"><?php esc_html_e('Date', 'wishlist-member'); ?></th>
                                                 </tr>
                                            </thead>
                                            <tbody style="max-height: 350px">
                                                    <?php foreach ($scheduled_level_actions as $i => $sl_action) : ?>
                                                        <?php
                                                            $meta_value       = isset($sl_action['meta_value']) ? wlm_maybe_unserialize($sl_action['meta_value']) : [];
                                                            $action_timestamp = '-';
                                                        if (isset($meta_value['action_timestamp'])) {
                                                            $action_timestamp = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $meta_value['action_timestamp'] + $this->gmt);
                                                        }
                                                            $action_details = isset($meta_value['action_details']) ? $meta_value['action_details'] : [];
                                                            $methods        = [
                                                                'add'    => 'Add to',
                                                                'move'   => 'Move to',
                                                                'cancel' => 'Cancel from',
                                                                'remove' => 'Remove from',
                                                            ];
                                                            $action_method  = isset($methods[ $action_details['level_action_method'] ]) ? $methods[ $action_details['level_action_method'] ] : ' - ';

                                                            $levels = $action_details['action_levels'];
                                                            foreach ($levels as $key => $lvl) {
                                                                if (isset($wpm_levels[ $lvl ]['name'])) {
                                                                    $levels[ $key ] = $wpm_levels[ $lvl ]['name'];
                                                                } else {
                                                                    unset($levels[ $key ]);
                                                                }
                                                            }
                                                            $text = $action_method . ' ' . implode(', ', (array) $levels);
                                                            ?>
                                                         <tr class="d-flex button-hover">
                                                             <td class="col-6"><?php echo esc_html($text); ?></td>
                                                             <td class="col-4"><?php echo esc_html($action_timestamp); ?></td>
                                                             <td class="col-2">
                                                                <div class="btn-group-action pull-right">
                                                                    <a href="#" user-id="<?php echo esc_attr($profileuser->ID); ?>" meta-key="<?php echo esc_attr($sl_action['meta_key']); ?>" class="btn remove-usermeta-btn -del-btn" title="Remove Schedule"><span class="wlm-icons md-24 -icon-only">delete</span></a>
                                                                </div>
                                                             </td>
                                                         </tr>
                                                    <?php endforeach; ?>
                                             </tbody>
                                         </table>
                                    </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>
<div role="tabpanel" class="tab-pane" id="pay-per-posts">
    <?php
        $args          = [
            // 'public'                => true,
            // 'exclude_from_search'   => false,
               '_builtin' => false,
        ];
        $post_types    = get_post_types($args, 'objects');
        $enabled_types = (array) $this->get_option('protected_custom_post_types');

        $ptypes = [
            'post' => 'Posts',
            'page' => 'Pages',
        ];
        foreach ($post_types as $key => $value) {
            if (in_array($value->name, $enabled_types)) {
                $ptypes[ $value->name ] = $value->label;
            }
        }

        $ppp        = $this->get_user_pay_per_post($profileuser->ID, true);
        $user_posts = [
            'post' => [0],
            'page' => [0],
        ];
        foreach ($ppp as $key => $value) {
            $user_posts[ $value->type ][] = $value->content_id;
        }
        ?>
    <div class="row ppp-add-form">
        <div class="col-lg-8 col-md-8 col-sm-7">
            <div class="form-group -ppp">
                <select class="form-control wlm-select wlm-payperposts" name="wlm_payperposts" style="width: 100%">
                </select>
            </div>
        </div>
        <div class="col-lg-4 col-md-8 col-sm-5">
            <div class="form-group">
                <a href="#" class="btn -primary -condensed add-ppp-btn" data-type="">
                    <i class="wlm-icons">add</i>
                    <span class="text"><?php esc_html_e('Add Pay Per Post', 'wishlist-member'); ?></span>
                </a>
            </div>
        </div>
    </div>
    <div class="horizontal-tabs">
        <div class="row no-gutters">
            <div class="col-12 col-md-auto">
                <!-- Nav tabs -->
                <div class="horizontal-tabs-sidebar">
                    <ul class="nav nav-tabs -h-tabs flex-column" role="tablist">
                        <?php foreach ($ptypes as $ptype => $_title) : ?>
                            <li role="presentation" class="nav-item">
                                <a href="#<?php echo esc_attr($ptype); ?>" class="nav-link pp-nav-link <?php echo esc_attr('post' === $ptype ? 'active' : ''); ?>" aria-controls="<?php echo esc_attr($ptype); ?>" role="tab" data-type="<?php echo esc_attr($ptype); ?>" data-title="<?php echo esc_attr($_title); ?>" data-toggle="tab"><?php echo esc_html($_title); ?></a>
                            </li>
                        <?php endforeach; ?>
                        <li role="presentation" class="nav-item">
                            <a href="#ppphistory" class="nav-link pp-nav-link" aria-controls="ppphistory" role="tab" data-type="ppphistory" data-title="Pay Per Post History" data-toggle="tab">Pay Per Post History</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col">
                <!-- Tab panes -->
                <div class="tab-content">
                    <?php foreach ($ptypes as $ptype => $_title) : ?>
                        <?php
                            $p = [];
                        if (isset($user_posts[ $ptype ]) && count($user_posts[ $ptype ])) {
                            $p = new WP_Query(
                                [
                                    'post_type' => $ptype,
                                    'post__in'  => $user_posts[ $ptype ],
                                    'nopaging'  => true,
                                ]
                            );
                            $p = $p->posts;
                        }
                        ?>
                        <div role="tabpanel" class="tab-pane <?php echo esc_attr('post' === $ptype ? 'active' : ''); ?>" id="<?php echo esc_attr($ptype); ?>">
                            <!-- <div class="table-wrapper table-responsive" style="overflow: scroll; max-height: 247px;">
                                <table class="table table-condensed table-striped"> -->
                            <div class="table-wrapper table-responsive -with-input">
                                <table class="table table-striped table-condensed table-fixed">
                                    <tbody class="<?php echo esc_attr($ptype); ?>-holder" style="max-height: 300px">
                                        <?php if (count($p) > 0) : ?>
                                            <?php foreach ($p as $_post) : ?>
                                                 <tr class="d-flex button-hover">
                                                     <td class="col-10"><?php echo esc_html($_post->post_title); ?></td>
                                                     <td class="col-2">
                                                        <div class="btn-group-action pull-right">
                                                            <a href="#" post-id="<?php echo esc_attr($_post->ID); ?>" class="btn remove-ppp-btn -del-btn"><span class="wlm-icons md-18 -icon-only">delete</span></a>
                                                        </div>
                                                     </td>
                                                 </tr>
                                            <?php endforeach; ?>
                                        <?php else : ?>
                                                 <tr class="tr-none"><td class="text-center" colspan="2">- <?php esc_html_e('None Assigned -', 'wishlist-member'); ?></td></tr>
                                        <?php endif; ?>
                                     </tbody>
                                 </table>
                            </div>
                        </div>
                    <?php endforeach; ?>

                        <div role="tabpanel" class="tab-pane ppphistory" id="ppphistory">
                            <div class="table-wrapper table-responsive -with-input mt-2">
                                <table class="table table-striped table-condensed table-fixed">
                                    <thead>
                                         <tr class="d-flex">
                                             <th class="col-2"><?php esc_html_e('Action', 'wishlist-member'); ?></th>
                                             <th class="col-6"><?php esc_html_e('Title', 'wishlist-member'); ?></th>
                                             <th class="col-4"><?php esc_html_e('Date', 'wishlist-member'); ?></th>
                                         </tr>
                                    </thead>
                                    <tbody class="user-level-holder" style="max-height: 350px">
                                        <?php if (count($ppp_history) > 0) : ?>
                                            <?php foreach ($ppp_history as $i => $history) : ?>
                                                <?php
                                                    $meta_value        = isset($history->log_value) ? wlm_maybe_unserialize($history->log_value) : [];
                                                    $history_timestamp = '-';
                                                if (isset($history->date_added)) {
                                                    $history_timestamp = $this->format_date($history->date_added);
                                                }
                                                    $pid = isset($meta_value['post']) ? $meta_value['post'] : '0';
                                                    $p   = get_post($pid);
                                                if ($p) {
                                                    $_title = "{$p->post_title} ({$p->post_type})";
                                                } else {
                                                    $_title = "Invalid Post ({$pid})";
                                                }
                                                ?>
                                                 <tr class="d-flex">
                                                     <td class="col-2"><?php echo isset($history->log_key) ? esc_html(ucwords($history->log_key)) : '-'; ?></td>
                                                     <td class="col-6"><?php echo esc_html($_title); ?></td>
                                                     <td class="col-4"><?php echo esc_html($history_timestamp); ?></td>
                                                 </tr>
                                            <?php endforeach; ?>
                                        <?php else : ?>
                                             <tr class="tr-none"><td class="text-center" colspan="3">No pay per post history</td></tr>
                                        <?php endif; ?>
                                     </tbody>
                                 </table>
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div role="tabpanel" class="tab-pane" id="member-advance">
    <?php

        /**
         * Called at the top of the "Advanced" tab of the member's edit modal
         *
         * @param object $profileuser User object of user being edited.
         */

        do_action('wishlistmember_member_edit_tab_pane-member-advance', $profileuser);
        $blacklist_email = $this->get_option('blacklist_email');
        $blacklist_ip    = $this->get_option('blacklist_ip');
        $privacy_disable_ip_tracking_global = (bool) wishlistmember_instance()->get_option('privacy_disable_ip_tracking');
        $privacy_disable_ip_tracking_user   = wishlistmember_instance()->Get_UserMeta($profileuser->ID, 'privacy_disable_ip_tracking');

        $ip_tracking_disabled = $privacy_disable_ip_tracking_global;
    switch ($privacy_disable_ip_tracking_user) {
        case '1':
            $ip_tracking_disabled = true;
            break;
        case '-1':
            $ip_tracking_disabled = false;
            break;
    }
    ?>
    <div class="row -ip-tracking <?php echo esc_attr($ip_tracking_disabled ? '-ip-tracking-disabled' : ''); ?>">
        <div class="col-sm-4">
            <div class="row no-gutters">
                <div class="col-md-12">
                    <div class="form-group">
                        <label><?php esc_html_e('Registration Email', 'wishlist-member'); ?></label>
                        <?php
                            $t = false === strpos($blacklist_email, $profileuser->user_email) ? __('Add to Blacklist', 'wishlist-member') : __('Remove from Blacklist', 'wishlist-member');
                            $i = false === strpos($blacklist_email, $profileuser->user_email) ? 'add_circle_outline' : 'remove_circle_outline';
                        ?>
                        <a href="#" class="btn -condensed -bare -mini pull-right add-blacklist blacklist-email">
                            <i class="wlm-icons md-18"><?php echo esc_html($i); ?></i>
                            <small><?php echo esc_html($t); ?></small>
                            <input type="hidden" name="blacklist_email" value="<?php echo esc_attr($profileuser->user_email); ?>" />
                            <?php if (false === strpos($blacklist_email, $profileuser->user_email)) : ?>
                                <input type="hidden" name="add_blacklist" value="1" />
                            <?php endif; ?>
                        </a>
                        <span class="form-control email_address" title="<?php echo false !== strpos($blacklist_email, $profileuser->user_email) ? 'Blacklisted' : ''; ?>" readonly>
                            <?php
                                $mail_parts = explode('@', $profileuser->user_email);
                            if (strlen($mail_parts[0]) >= 15 || strlen($mail_parts[1]) >= 20) {
                                if (strlen($mail_parts[0]) >= 15) {
                                    $mail_local = str_split($mail_parts[0], 15);
                                    $mail_local = $mail_local[0] . '...';
                                } else {
                                    $mail_local = $mail_parts[0];
                                }
                                if (strlen($mail_parts[1]) >= 20) {
                                    $mail_domain = str_split($mail_parts[1], 12);
                                    $mail_domain = $mail_domain[0] . '...com';
                                } else {
                                    $mail_domain = $mail_parts[1];
                                }
                                echo '<span title="' . esc_attr($profileuser->user_email) . '" style="cursor: default;">';
                                    echo esc_html($mail_local . '@' . $mail_domain);
                                echo '</span>';
                            } else {
                                echo esc_html($profileuser->user_email);
                            }
                            ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <label><?php esc_html_e('Registration Date', 'wishlist-member'); ?></label>
                <span class="form-control" readonly><?php echo esc_html($registered); ?></span>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <label><?php esc_html_e('Last Login Date', 'wishlist-member'); ?></label>
                <span class="form-control" readonly><?php echo esc_html($lastlogin); ?></span>
            </div>
        </div>
        <div class="col-sm-4">
                        <div class="form-group">
                <label><?php esc_html_e('IP Tracking', 'wishlist-member'); ?></label>
                <select name="privacy_disable_ip_tracking" class="form-control wlm-levels toggle-adjacent" style="width:100%" data-global-default="<?php echo esc_attr($privacy_disable_ip_tracking_global); ?>">
                    <option value="0"><?php /* translators: 1: Enabled/Disabled */ printf(esc_html__('Global Default (%s)', 'wishlist-member'), $privacy_disable_ip_tracking_global ? esc_html__('Disabled', 'wishlist-member') : esc_html__('Enabled', 'wishlist-member')); ?></option>
                    <option value="-1" <?php echo '-1' == $privacy_disable_ip_tracking_user ? 'selected' : ''; ?>><?php esc_html_e('Enabled', 'wishlist-member'); ?></option>
                    <option value="1" <?php echo '1' == $privacy_disable_ip_tracking_user ? 'selected' : ''; ?>><?php esc_html_e('Disabled', 'wishlist-member'); ?></option>
                </select>
            </div>
        </div>
        <div class="col-sm-4 -ip-tracking">
            <div class="row no-gutters">
                <div class="col-md-12">
                    <div class="form-group">
                        <label><?php esc_html_e('Registration IP', 'wishlist-member'); ?></label>
                        <?php if (! empty($regip) && '127.0.0.1' !== $regip) : ?>
                            <?php
                                $t = false === strpos($blacklist_ip, $regip) ? __('Add to Blacklist', 'wishlist-member') : __('Remove from Blacklist', 'wishlist-member');
                                $i = false === strpos($blacklist_ip, $regip) ? 'add_circle_outline' : 'remove_circle_outline';
                            ?>
                            <a href="#" class="btn -condensed -bare -mini pull-right add-blacklist blacklist-ip">
                                <i class="wlm-icons md-18"><?php echo esc_html($i); ?></i>
                                <small><?php echo esc_html($t); ?></small>
                                <input type="hidden" name="blacklist_ip" value="<?php echo esc_attr($regip); ?>" />
                                <?php if (false === strpos($blacklist_ip, $regip)) : ?>
                                    <input type="hidden" name="add_blacklist" value="1" />
                                <?php endif; ?>
                            </a>
                        <?php endif; ?>
                        <span class="form-control ip_address" title="<?php echo esc_attr($regip && false !== strpos($blacklist_ip, $regip) ? 'Blacklisted' : ''); ?>" readonly>
                            <?php echo esc_html($regip); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4 -ip-tracking">
            <div class="row no-gutters">
                <div class="col-md-12">
                    <div class="form-group">
                        <label><?php esc_html_e('Last Login IP', 'wishlist-member'); ?></label>
                        <?php if (! empty($loginip) && '127.0.0.1' !== $loginip && 'No login record yet.' !== $loginip) : ?>
                            <?php
                                $t = false === strpos($blacklist_ip, $loginip) ? __('Add to Blacklist', 'wishlist-member') : __('Remove from Blacklist', 'wishlist-member');
                                $i = false === strpos($blacklist_ip, $loginip) ? 'add_circle_outline' : 'remove_circle_outline';
                            ?>
                            <a href="#" class="btn -condensed -bare -mini pull-right add-blacklist blacklist-ip">
                                <i class="wlm-icons md-18"><?php echo esc_html($i); ?></i>
                                <small><?php echo esc_html($t); ?></small>
                                <input type="hidden" name="blacklist_ip" value="<?php echo esc_attr($loginip); ?>" />
                                <?php if (false === strpos($blacklist_ip, $loginip)) : ?>
                                    <input type="hidden" name="add_blacklist" value="1" />
                                <?php endif; ?>
                            </a>
                        <?php endif; ?>
                        <span class="form-control ip_address" title="<?php echo false !== strpos($blacklist_ip, $loginip) ? 'Blacklisted' : ''; ?>" readonly>
                            <?php echo esc_html($loginip); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4 -ip-tracking">
            <div class="form-group">
                <label>
                    <?php esc_html_e('Login Limit', 'wishlist-member'); ?>
                    <?php $this->tooltip(__('The default Login Limit for all Members is set in the Advanced Options > Logins section.<br><br> The Login Limit for an individual Member can be changed here on a per Member basis. <br><br> This setting controls the total number of IPs a Member can login with each day.<br><br>Note: Special Values can be used.<br><br>0 or Blank: Use the Default Login Limit set in Advanced Options > Logins section for the Member.<br><br>-1: No Login Limit for the Member.', 'wishlist-member'), 'lg'); ?>
                </label>
                <div class="input-group">
                     <input type="number" class="form-control" min="-1" value="<?php echo esc_attr($profileuser->wpm_login_limit); ?>" name="wpm_login_limit" />
                     <div class="input-group-append">
                         <div class="input-group-text"><?php esc_html_e('IPs per day', 'wishlist-member'); ?></div>
                     </div>
                </div>
            </div>
        </div>
        <?php if (count($today_loggedin_ips) > 0) : ?>
        <div class="col-sm-8 -ip-tracking">
            <div class="row no-gutters">
                <div class="col-md-12">
                    <div class="form-group">
                        <label><?php esc_html_e('IPs Logged In Today', 'wishlist-member'); ?></label>
                        <a href="#" class="btn -condensed -bare -mini pull-right reset-limit-counter" user-id="<?php echo esc_attr($profileuser->ID); ?>">
                            <i class="wlm-icons md-18">refresh</i>
                            <small><?php esc_html_e('Reset IP Limit Counter', 'wishlist-member'); ?></small>
                        </a>
                        <span class="form-control" readonly>
                            <?php echo esc_html(implode(', ', array_keys((array) $today_loggedin_ips))); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<div role="tabpanel" class="tab-pane" id="data-privacy">
    <?php if ($this->get_option('privacy_require_tos_on_registration')) : ?>
        <div class="row">
                <div class="col-md-6">
                    <div class="switch">
                        <div class="switch-left">
                            <div class="form-group">
                                <label class="switch-light switch-wlm" onclick="">
                                    <?php $checked = $this->Get_UserMeta($profileuser->ID, 'wlm_tos_accepted') ? 'checked="checked"' : ''; ?>
                                    <input type="checkbox" id="wlm_tos_accepted-id1535116617944" value="1" class="notification-switch form-check-input is-toggle-switch" name="wlm_tos_accepted" uncheck_value="0" <?php echo esc_attr($checked); ?> data-lpignore="true">
                                    <span>
                                        <span><i class="wlm-icons md-18 ico-check">check</i></span>
                                        <span><i class="wlm-icons md-18 ico-close">close</i></span>
                                        <a></a>
                                    </span>
                                </label>
                            </div>
                        </div>
                        <div class="switch-body">
                            <label for="wlm_tos_accepted-id1535116617944">
                                <span class="title-label"><?php esc_html_e('Terms of Service Accepted', 'wishlist-member'); ?></span>
                            </label>
                        </div>
                    </div>
                    <input type="hidden" name="userid" value="<?php echo esc_attr($profileuser->ID); ?>" data-lpignore="true">
                    <input type="hidden" name="action" value="admin_actions" data-lpignore="true">
                    <input type="hidden" name="WishListMemberAction" value="save_user_meta" data-lpignore="true">
                </div>
        </div>
    <?php endif; ?>
    <?php if ($this->get_option('privacy_enable_consent_to_market')) : ?>
        <div class="row">
                <div class="col-md-6">
                    <div class="switch">
                        <div class="switch-left">
                            <div class="form-group">
                                <label class="switch-light switch-wlm" onclick="">
                                    <?php $checked = $this->Get_UserMeta($profileuser->ID, 'wlm_consent_to_market') ? 'checked="checked"' : ''; ?>
                                    <input type="checkbox" id="wlm_consent_to_market-id1535116617944" value="1" class="notification-switch form-check-input is-toggle-switch" name="wlm_consent_to_market" uncheck_value="0" <?php echo esc_attr($checked); ?> data-lpignore="true">
                                    <span>
                                        <span><i class="wlm-icons md-18 ico-check">check</i></span>
                                        <span><i class="wlm-icons md-18 ico-close">close</i></span>
                                        <a></a>
                                    </span>
                                </label>
                            </div>
                        </div>
                        <div class="switch-body">
                            <label for="wlm_consent_to_market-id1535116617944">
                                <span class="title-label"><?php esc_html_e('Consent for Additional Marketing', 'wishlist-member'); ?></span>
                            </label>
                        </div>
                    </div>
                    <input type="hidden" name="userid" value="<?php echo esc_attr($profileuser->ID); ?>" data-lpignore="true">
                    <input type="hidden" name="action" value="admin_actions" data-lpignore="true">
                    <input type="hidden" name="WishListMemberAction" value="save_user_meta" data-lpignore="true">
                </div>
        </div>
    <?php endif; ?>
</div>
<div role="tabpanel" class="tab-pane" id="member-other">
    <p><?php esc_html_e('Any information collected in Custom Registration Forms will be displayed below.', 'wishlist-member'); ?></p>
    <?php
        $custom_fields = $this->get_user_custom_registration_fields($profileuser->ID);
        $custom_fields = is_array($custom_fields) ? $custom_fields : [];
    ?>
    <?php $cnt = 1; ?>
    <?php foreach ($custom_fields as $key => $value) : ?>
        <?php
        if (1 === (int) $cnt) {
            echo '<div class="row">';
        }
        ?>
            <div class="col-md-12 col-sm-12 col-xs-12">
                <?php if ('radio' === $value['type']) : ?>
                    <div class="form-group">
                        <label><?php echo esc_html($value['label']); ?></label>
                        <?php $chk_cnt = 1; ?>
                        <?php foreach ($value['options'] as $key => $v) : ?>
                            <?php
                            if (1 === (int) $chk_cnt) {
                                echo '<div class="radio row mb-2">';
                            }
                            ?>
                            <?php
                            if (! wlm_trim($v['text'])) {
                                continue;
                            }
                            ?>
                            <div class="col-md-auto col-sm-auto col-xs-auto">
                                <?php $checked = $v['checked'] ? $selected = 'checked="checked"' : ''; ?>
                                <div class="form-check -with-tooltip">
                                    <label class="cb-container">
                                        <input type="radio" value="<?php echo esc_attr($v['value']); ?>" name="customfields[<?php echo esc_attr($value['attributes']['name']); ?>]" <?php echo esc_attr($checked); ?> />
                                        <span class="marker btn-radio"></span>
                                          <span class="text-content"><?php echo esc_html($v['text']); ?></span>
                                    </label>
                                </div>
                            </div>
                            <?php
                            if (2 === (int) $chk_cnt) {
                                echo '</div>';
                                $chk_cnt = 1;
                            } else {
                                ++$chk_cnt;
                            }
                            ?>
                        <?php endforeach; ?>
                        <?php
                        if (0 === $chk_cnt % 2) {
                            echo '</div>';
                        }
                        ?>
                    </div>
                <?php elseif ('checkbox' === $value['type']) : ?>
                    <div class="form-group">
                        <label><?php echo esc_html($value['label']); ?></label>
                        <?php $chk_cnt = 1; ?>
                        <?php foreach ($value['options'] as $key => $v) : ?>
                            <?php
                            if (1 === (int) $chk_cnt) {
                                echo '<div class="row mb-2">';
                            }
                            ?>
                            <?php
                            if (! wlm_trim($v['text'])) {
                                continue;
                            }
                            ?>
                            <div class="col-md-6 col-sm-6 col-xs-6">
                                <?php $checked = $v['checked'] ? $selected = 'checked="checked"' : ''; ?>
                                <div class="form-check -with-tooltip">
                                    <label class="cb-container">
                                        <input name="customfields[<?php echo esc_attr($value['attributes']['name']); ?>][]" value="<?php echo esc_attr($v['value']); ?>" type="checkbox" <?php echo esc_attr($checked); ?> />
                                        <span class="marker checkmark"></span>
                                          <span class="text-content"><?php echo esc_html($v['text']); ?></span>
                                    </label>
                                </div>
                            </div>
                            <?php
                            if (2 === (int) $chk_cnt) {
                                echo '</div>';
                                $chk_cnt = 1;
                            } else {
                                ++$chk_cnt;
                            }
                            ?>
                        <?php endforeach; ?>
                        <?php
                        if (0 === $chk_cnt % 2) {
                            echo '</div>';
                        }
                        ?>
                    </div>
                <?php elseif ('textarea' === $value['type']) : ?>
                    <div class="form-group">
                        <label><?php echo esc_html($value['label']); ?></label>
                        <textarea class="form-control" name="customfields[<?php echo esc_attr($value['attributes']['name']); ?>]"><?php echo esc_textarea(stripslashes($value['attributes']['value'])); ?></textarea>
                    </div>
                <?php elseif ('select' === $value['type']) : ?>
                    <div class="form-group">
                        <label><?php echo esc_html($value['label']); ?></label>
                        <select name="customfields[<?php echo esc_attr($value['attributes']['name']); ?>]" class="form-control wlm-levels" style="width: 100%;">
                            <?php foreach ($value['options'] as $key => $v) : ?>
                                    <?php $selected = $v['selected'] ? $selected = 'selected' : ''; ?>
                                     <option value="<?php echo esc_attr($v['value']); ?>" <?php echo esc_attr($selected); ?> ><?php echo esc_html($v['text']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php else : ?>
                    <div class="form-group">
                        <label><?php echo esc_html($value['label']); ?><?php echo 'hidden' === $value['type'] ? ' (Hidden Field)' : ''; ?></label>
                        <input type="text" class="form-control" value="<?php echo esc_attr(stripslashes($value['attributes']['value'])); ?>" name="customfields[<?php echo esc_attr($value['attributes']['name']); ?>]" />
                    </div>
                <?php endif; ?>
            </div>
        <?php
        if (2 === (int) $cnt) {
            echo '</div>';
            $cnt = 1;
        } else {
            ++$cnt;
        }
        ?>
    <?php endforeach; ?>
    <?php
    if (0 == $cnt % 2) {
        echo '</div>';
    }
    ?>
</div>
<div role="tabpanel" class="tab-pane" id="member-history">
    <div class="horizontal-tabs">
        <div class="row no-gutters">
            <div class="col-12 col-md-auto">
                <!-- Nav tabs -->
                <div class="horizontal-tabs-sidebar">
                    <ul class="nav nav-tabs -h-tabs flex-column" role="tablist">
                        <li role="presentation" class="nav-item">
                            <a href="#loginhistory" class="nav-link active" aria-controls="loginhistory" role="tab" data-type="loginhistory" data-title="Login History" data-toggle="tab">Login</a>
                        </li>
                        <li role="presentation" class="nav-item">
                            <a href="#rsshistory" class="nav-link" aria-controls="rsshistory" role="tab" data-type="rsshistory" data-title="RSS Feeds History" data-toggle="tab">RSS</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col">
                <!-- Tab panes -->
                <div class="tab-content">
                        <div role="tabpanel" class="tab-pane active loginhistory" id="loginhistory">
                            <div class="table-wrapper table-responsive -with-input mt-2">
                                <table class="table table-striped table-condensed table-fixed">
                                    <thead>
                                         <tr class="d-flex">
                                             <th class="col-2"><?php esc_html_e('Status', 'wishlist-member'); ?></th>
                                             <th class="col-6"><?php esc_html_e('IP', 'wishlist-member'); ?></th>
                                             <th class="col-4"><?php esc_html_e('Date', 'wishlist-member'); ?></th>
                                         </tr>
                                    </thead>
                                    <tbody class="user-level-holder" style="max-height: 400px">
                                        <?php if (count($login_history) > 0) : ?>
                                            <?php foreach ($login_history as $i => $history) : ?>
                                                <?php
                                                    $meta_value        = isset($history->log_value) ? wlm_maybe_unserialize($history->log_value) : [];
                                                    $history_timestamp = '-';
                                                if (isset($history->date_added)) {
                                                    $history_timestamp = $this->format_date($history->date_added);
                                                }
                                                ?>
                                                 <tr class="d-flex">
                                                     <td class="col-2"><?php echo isset($history->log_key) ? esc_html(ucwords($history->log_key)) : '-'; ?></td>
                                                     <td class="col-6"><?php echo isset($meta_value['ip']) && ! empty($meta_value['ip']) ? esc_html($meta_value['ip']) : '-'; ?></td>
                                                     <td class="col-4"><?php echo esc_html($history_timestamp); ?></td>
                                                 </tr>
                                            <?php endforeach; ?>
                                        <?php else : ?>
                                             <tr class="tr-none"><td class="text-center" colspan="3">No login history</td></tr>
                                        <?php endif; ?>
                                     </tbody>
                                 </table>
                            </div>
                        </div>

                        <div role="tabpanel" class="tab-pane rsshistory" id="rsshistory">
                            <div class="table-wrapper table-responsive -with-input mt-2">
                                <table class="table table-striped table-condensed table-fixed">
                                    <thead>
                                         <tr class="d-flex">
                                             <th class="col-2"><?php esc_html_e('Status', 'wishlist-member'); ?></th>
                                             <th class="col-6"><?php esc_html_e('IP', 'wishlist-member'); ?></th>
                                             <th class="col-4"><?php esc_html_e('Date', 'wishlist-member'); ?></th>
                                         </tr>
                                    </thead>
                                    <tbody class="user-level-holder" style="max-height: 400px">
                                        <?php if (count($rss_history) > 0) : ?>
                                            <?php foreach ($rss_history as $i => $history) : ?>
                                                <?php
                                                    $meta_value        = isset($history->log_value) ? wlm_maybe_unserialize($history->log_value) : [];
                                                    $history_timestamp = '-';
                                                if (isset($history->date_added)) {
                                                    $history_timestamp = $this->format_date($history->date_added);
                                                }
                                                ?>
                                                 <tr class="d-flex">
                                                     <td class="col-2"><?php echo isset($history->log_key) ? esc_html(ucwords($history->log_key)) : '-'; ?></td>
                                                     <td class="col-6"><?php echo isset($meta_value['ip']) && ! empty($meta_value['ip']) ? esc_html($meta_value['ip']) : '-'; ?></td>
                                                     <td class="col-4"><?php echo esc_html($history_timestamp); ?></td>
                                                 </tr>
                                            <?php endforeach; ?>
                                        <?php else : ?>
                                             <tr class="tr-none"><td class="text-center" colspan="3">No login history</td></tr>
                                        <?php endif; ?>
                                     </tbody>
                                 </table>
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
    // Loop through each additional tabs added via the "wishlistmember_member_edit_tabs" filter.
foreach ((array) wlm_arrval($data, 'member_edit_tabs') as $tab_key => $tab_panel) {
    echo '<div role="tabpanel" class="tab-pane" id="member-edit-' . esc_attr($tab_key) . '">';
    /**
     * Allow additional tab panes to be added to the member edit modal
     * Panes must be defined via the "wishlistmember_member_edit_tabs" filter
     *
     * @param int $id User ID of the user being edited
     */
    do_action('wishlistmember_member_edit_tab_pane-' . $tab_key, $profileuser->ID);
    echo '</div>';
}
?>
<style>
    .row.-ip-tracking-disabled .-ip-tracking {
        display: none;
    }
</style>
