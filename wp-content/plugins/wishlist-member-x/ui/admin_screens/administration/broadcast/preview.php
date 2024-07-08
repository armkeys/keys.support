<?php

/**
 * Preview Broadcast
 *
 * @package WishListMember
 */

?>
<div class="row broadcasts-preview">
    <div class="col-md-6">
        <div class="form-group">
            <label for="">From Name</label>
            <?php if ($data['from_name']) : ?>
                <p><?php echo $data['broadcast_use_custom_sender_info'] ? esc_html($data['from_name']) : esc_html($this->get_option('email_sender_name')); ?></p>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">From Email</label>
            <?php if ($data['from_email']) : ?>
                <p><?php echo $data['broadcast_use_custom_sender_info'] ? esc_html($data['from_email']) : esc_html($this->get_option('email_sender_address')); ?></p>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label for="">Email Subject</label>
            <?php if ($data['subject']) : ?>
                <p><?php echo esc_html($data['subject']); ?></p>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label for="">Email Message (<?php echo esc_html($data['sent_as']); ?>)</label>
            <p>
                <?php
                    $address = [];
                    $street1 = $this->get_option('email_sender_street1');
                    $street2 = $this->get_option('email_sender_street2');
                    $city    = $this->get_option('email_sender_city');
                    $state   = $this->get_option('email_sender_state');
                    $zip     = $this->get_option('email_sender_zipcode');
                    $country = $this->get_option('email_sender_country');
                if (wlm_trim($street1)) {
                    $address[] = wlm_trim($street1);
                }
                if (wlm_trim($street2)) {
                    $address[] = wlm_trim($street2);
                }
                if (wlm_trim($city)) {
                    $address[] = wlm_trim($city);
                }
                if (wlm_trim($state)) {
                    $address[] = wlm_trim($state);
                }
                if (wlm_trim($zip)) {
                    $address[] = wlm_trim($zip);
                }
                if (wlm_trim($country)) {
                    $address[] = wlm_trim($country);
                }

                    $canspamaddress = '';
                    $address        = [];
                    $street1        = $this->get_option('email_sender_street1');
                    $street2        = $this->get_option('email_sender_street2');
                    $city           = $this->get_option('email_sender_city');
                    $state          = $this->get_option('email_sender_state');
                    $zip            = $this->get_option('email_sender_zipcode');
                    $country        = $this->get_option('email_sender_country');
                if (wlm_trim($city)) {
                    $address[] = wlm_trim($city);
                }
                if (wlm_trim($state)) {
                    $address[] = wlm_trim($state);
                }
                if (wlm_trim($zip)) {
                    $address[] = wlm_trim($zip);
                }
                if (wlm_trim($country)) {
                    $address[] = wlm_trim($country);
                }
                    $canspamaddress = wlm_trim($street1) . ', ';
                if ('' != wlm_trim($street2)) {
                    $canspamaddress .= wlm_trim($street2) . ', ';
                }
                    $canspamaddress .= implode(', ', $address);

                    $footer    = "\n\n";
                    $signature = isset($data['signature']) ? wlm_trim($data['signature']) : '';
                if (! empty($signature)) {
                    $footer .= $signature . "\n\n";
                }
                    // Add unsubcribe and user details link.
                    $footer .= sprintf(WLMCANSPAM, 'XX/' . substr(md5('XX' . AUTH_SALT), 0, 10)) . "\n\n";
                    $footer .= $canspamaddress;

                    $msg = wlm_trim($data['message']);

                if ('html' === $data['sent_as']) {
                    $fullmsg = $msg . wpautop($footer);
                } else {
                    $fullmsg = $msg . $footer;
                    $fullmsg = nl2br(htmlentities($fullmsg));
                }

                    echo wp_kses_post(stripslashes($fullmsg));
                ?>
            </p>
        </div>
        <?php if ($data['send_to_admin']) : ?>
            <?php if ($data['admin_email_sent']) : ?>
                <em class="pull-right mb-3 text-primary">A copy of this email was sent to (<?php echo esc_html($this->get_option('email_sender_address')); ?>)</em>
            <?php else : ?>
                <em class="pull-right mb-3 text-danger"><?php esc_html_e('An error occured while sending a copy of this email to the site administrator.', 'wishlist-member'); ?></em>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<div class="row send-to-ml">
    <div class="col-md-12">
        <div class="form-group">
            <?php if ('send_mlevels' === $data['send_to']) : ?>
                <label for="">Send to Membership Level/s</label>
                <p>
                    <?php
                    $total_recipients = 0;
                    $levels           = [];
                    foreach ($data['send_mlevels'] as $lvl) {
                        if (isset($wpm_levels[ $lvl ])) {
                            $levels[] = $wpm_levels[ $lvl ]['name'];
                        }
                    }
                        echo esc_html(implode(', ', $levels) . " ({$data['total_recipients']} recipients)");
                    ?>
                </p>
            <?php else : ?>
                <label for="">Sending to Saved Searches</label>
                <p>
                <?php
                if ('' != $data['save_searches']) {
                    $save_searches = $this->get_saved_search($data['save_searches']);
                    if ($save_searches) {
                        $save_searches    = $save_searches[0];
                        $user_search      = isset($save_searches['search_term']) ? $save_searches['search_term'] : '';
                        $user_search      = isset($save_searches['usersearch']) ? $save_searches['usersearch'] : $user_search;
                        $wp_user_search   = new \WishListMember\User_Search($user_search, '', '', '', '', '', 99999999, $save_searches);
                        $total_recipients = $wp_user_search->total_users;
                    }
                    if ($total_recipients) {
                        echo esc_html($data['save_searches'] . " ({$total_recipients} recipients)");
                    } else {
                        echo esc_html($data['save_searches'] . ' (0 recipient)');
                    }
                } else {
                    esc_html_e('no saved search selected', 'wishlist-member');
                }
                ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>
