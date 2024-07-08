<div class="row">
    <?php echo wp_kses_post($api_status_markup); ?>
</div>
<div class="-integration-keys">
    <div class="row api-required">
        <template class="wlm3-form-group">
            {
                label : '<?php echo esc_js(__('Your AWeber API Authorization Key', 'wishlist-member')); ?>',
                type : 'textarea',
                value : WLM3ThirdPartyIntegration.aweberapi.connected_auth_key,
                id : 'connectedkey',
                rows : 4,
                class : 'copyable',
                column : 'col-md-9',
                readonly : 'readonly',
                tooltip : '<?php echo esc_js(__('If you have other WishList Member site(s) connected to your AWeber account, you can paste your existing AWeber API Authorization Key into the available field. The AWeber API Authorization Key can be found in the Setup > Integrations > Email Providers > AWeber section of your other WishList Member site(s).', 'wishlist-member')); ?>'
            }
        </template>
        <div class="col-md-3">
            <label>&nbsp;</label>
            <a class="btn btn-block -default -condensed -no-icon save-keys"><span class="-processing"><?php esc_html_e('Processing...', 'wishlist-member'); ?></span><span class="-connected"><?php esc_html_e('Disconnect', 'wishlist-member'); ?></span><span class="-disconnected"><?php esc_html_e('Connect to AWeber', 'wishlist-member'); ?></span></a>
        </div>
    </div>
    <div class="api-disconnected">
        <div class="row">
            <div class="col-md-9">
                <p>
                    <?php esc_html_e('Do you have any other WishList Member sites currently connected to your AWeber account?', 'wishlist-member'); ?>
                    <?php $this->tooltip(__('Leave the toggle set to "No" if you do not have any other WishList Member sites connected to your AWeber account using the API Authorization Key. If you were to setup a new connection to your AWeber account it would reset the API Authorization Key and disconnect any other existing WishList Member connections. This same API Authorization Key would need to be used for any additional WishList Member connections to your AWeber account.', 'wishlist-member'), 'lg'); ?>
                </p>
            </div>
            <div class="col-md-3">
                <div class="switch-toggle switch-toggle-wlm" id="hassites-toggle">
                    <input id="-hassites-yes" name="sp" type="radio">
                    <label for="-hassites-yes"><?php esc_html_e('Yes', 'wishlist-member'); ?></label>

                    <input id="-hassites-no" name="sp" type="radio" checked="">
                    <label for="-hassites-no">No</label>

                    <a href="" class="btn btn-primary"></a>
                </div>
            </div>
            <div class="col-md-12"><hr></div>
        </div>
        <div id="hassites" class="tab-content">
            <div class="row tab-pane" id="hassites-yes">
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Paste your existing AWeber API Authorization Key below and click "Connect to AWeber"', 'wishlist-member')); ?>',
                        type : 'textarea',
                        name : 'auth_key',
                        rows : 4,
                        help_block : '<?php echo esc_js(__('The existing AWeber API Authorization Key can be obtained from any WishList Member site that is already connected to AWeber. You must be running WishList Member 2.91.3174 or higher in order to copy the key from that site. Please note all WishList Member sites that connect to the AWeber API must use the same AWeber API Authorization Key.', 'wishlist-member')); ?>',
                        column : 'col-md-9',
                        tooltip : '<?php echo esc_js(__('If you have other WishList Member site(s) connected to your AWeber account, you can paste your existing AWeber API Authorization Key into the available field. The AWeber API Authorization Key can be found in the Setup > Integrations > Email Providers > AWeber section of your other WishList Member site(s).', 'wishlist-member')); ?>',
                    }
                </template>
                <div class="col-md-3">
                    <a class="btn btn-block -default -condensed save-keys"><span class="-processing"><?php esc_html_e('Processing...', 'wishlist-member'); ?></span><span class="-connected"><?php esc_html_e('Disconnect', 'wishlist-member'); ?></span><span class="-disconnected"><?php esc_html_e('Connect to AWeber', 'wishlist-member'); ?></span></a>
                </div>
            </div>
            <div class="row tab-pane active" id="hassites-no">
                <div class="col-md-12">
                    <p><?php esc_html_e('Use the link below to access a page that will prompt you to enter your AWeber login information and then click Allow Access.', 'wishlist-member'); ?></p>
                    <a class="btn -primary -condensed" href="https://auth.aweber.com/1.0/oauth/authorize_app/2d8307c8?oauth_callback=<?php echo urlencode($callback); ?>" target="_parent">
                        <span><?php esc_html_e('Connect WishList Member to AWeber', 'wishlist-member'); ?></span>
                    </a>

                </div>
            </div>
        </div>
    </div>
</div>
<input type="hidden" name="access_tokens[0]">
<input type="hidden" name="access_tokens[1]">
