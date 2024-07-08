<div class="wizard-form -dark">
    <div class="content-wrapper -no-header level-data">
        <div class="row align-items-center">
            <div class="col-md-5">
                <div class="information text-center">
                    <img src="<?php echo esc_url($this->plugin_url3); ?>/ui/images/wishlist-member-logo.png" class="mx-auto d-block" alt="">
                </div>
            </div>
            <div class="col-md-7">
                <div class="white-background">
                    <div class="row">
                        <div class="col-md-12">
                            <?php require $this->plugin_dir3 . '/helpers/header-icons.php'; ?>
                        </div>
                    </div>
                    <h4><?php esc_html_e('Congratulations, your license was successfully activated.', 'wishlist-member'); ?></h4>
                    <div class="row">
                        <div class="col-md-12">
                            <?php
                                $WPWLKey       = $this->get_option('LicenseKey');
                                $WPWLKeyExpire = $this->get_option('LicenseExpiration');
                                $WPWLEmail     = $this->get_option('LicenseEmail');
                                // Make sure we have a valid license info.
                                $WPWLKey   = false !== $WPWLKey ? ( '' != wlm_trim($WPWLKey) ? wlm_trim($WPWLKey) : false ) : false;
                                $WPWLEmail = false !== $WPWLEmail ? ( '' != wlm_trim($WPWLEmail) ? wlm_trim($WPWLEmail) : false ) : false;

                                $key_is_expired = wlm_date('Y-m-d 00:00:00') > $WPWLKeyExpire;
                                $lifetime       = substr($WPWLKeyExpire, 0, 4) > 2999;
                                $text1          = $key_is_expired ? __('Support Plan Expired', 'wishlist-member') : __('Support Plan Expiration', 'wishlist-member');
                                $text2          = $key_is_expired ? __('Click Here to update your expired Support Plan Now', 'wishlist-member') : __('Click Here for more information on a Support Plan Renewal', 'wishlist-member');
                                $span_style     = $key_is_expired ? ' style="color:red"' : '';
                            ?>
                            <div class="panel-body">
                                <p><label><strong><?php esc_html_e('License Key', 'wishlist-member'); ?>:</strong> ************************<?php echo esc_html(substr($WPWLKey, -4)); ?></label></p>
                                <p><?php esc_html_e('Support Plan Expiration', 'wishlist-member'); ?>: <span<?php echo wp_kses($span_style, []); ?>><?php echo $lifetime ? esc_html__('Lifetime', 'wishlist-member') : esc_html(wlm_date('F j, Y', strtotime($WPWLKeyExpire))); ?></span></p>
                            </div>
                        </div>
                        <div class="col-md-12 text-center">
                            <?php $wpm_levels = $this->get_option('wpm_levels'); ?>
                            <a href="#" data-screen="license-confirm" next-screen="<?php echo count($wpm_levels) > 0 ? 'start' : 'step-1'; ?>" class="btn -success -lg next-btn">
                                Run the Setup Wizard now
                                <i class="wlm-icons">arrow_forward</i>
                            </a>
                            <br><br>
                            <a href="#" class="btn -bare -lg next-btn" data-screen="thanks">
                                No Thanks
                            </a>
                            <br><br>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
