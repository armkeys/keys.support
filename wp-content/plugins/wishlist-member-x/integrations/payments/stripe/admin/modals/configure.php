<?php
$style_keys = '';
$show_keys  = false;
if (! isset($_GET['display-keys']) && ! isset($_COOKIE['wlm_stripe_display_keys']) && ! defined('WLM_DISABLE_Connect')) {
    $style_keys = ' display:none;';
} else {
    $show_keys = true;
}

$account_email        = \WishListMember\PaymentProviders\Stripe\Auth_Utils::get_account_email();
$secret               = \WishListMember\PaymentProviders\Stripe\Auth_Utils::get_account_secret();
$site_uuid            = \WishListMember\PaymentProviders\Stripe\Auth_Utils::get_account_site_uuid();
$service_account_name = wishlistmember_instance()->get_option('wlm_stripe_service_account_name');
$id                   = \WishListMember\PaymentProviders\Stripe\Connect::get_method_id();

// If we're authenticated then let's present a stripe url otherwise an authenticator url.
if ($account_email && $secret && $site_uuid) {
    $connect_url = \WishListMember\PaymentProviders\Stripe\Connect::get_stripe_connect_url();
} else {
    $connect_url = \WishListMember\PaymentProviders\Stripe\Authenticator::get_auth_connect_url(true, \WishListMember\PaymentProviders\Stripe\Connect::get_method_id());
}

$connect_status = \WishListMember\PaymentProviders\Stripe\Connect::connect_status();
$stripeapikey   = \WishListMember\PaymentProviders\Stripe\Gateway_Utils::get_stripeapikey();
$stripetestmode = \WishListMember\PaymentProviders\Stripe\Gateway_Utils::detect_stripe_testmode();

$stripe_keys_styles = [
    'live' => '',
    'test' => ' style="display:none;"',
];

if ('no' === $stripetestmode) {
    $stripe_keys_styles = [
        'live' => '',
        'test' => ' style="display:none;"',
    ];
} elseif ('yes' === $stripetestmode) {
    $stripe_keys_styles = [
        'live' => ' style="display:none;"',
        'test' => '',
    ];
}
?>
<div
    data-process="modal"
    id="configure-<?php echo esc_attr($config['id']); ?>-template"
    data-id="configure-<?php echo esc_attr($config['id']); ?>"
    data-label="configure-<?php echo esc_attr($config['id']); ?>"
    data-title="<?php echo esc_attr($config['name']); ?> Configuration"
    data-show-default-footer="1"
    style="display:none">
    <div class="body">
        <input type="hidden" class="-url" name="stripethankyou" />
        <div class="row">
            <div class="col-md-12">
                <ul class="nav nav-tabs">
                    <li class="active nav-item"><a class="nav-link" data-toggle="tab" href="#stripe-connect"><?php esc_html_e('API', 'wishlist-member'); ?></a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#stripe-settings"><?php esc_html_e('Settings', 'wishlist-member'); ?></a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#stripe-form"><?php esc_html_e('Payment Form', 'wishlist-member'); ?></a></li>
                </ul>
            </div>
        </div>
        <div class="tab-content">
            <div class="tab-pane active in" id="stripe-connect">
                <div class="row">
                    <?php echo wp_kses_post($api_status_markup); ?>
                </div>
                <div class="row -integration-keys">
                    <?php if ('connected' !== $connect_status && '' !== $stripeapikey) : ?>
                    <div class="col-md-12">
                        <p style="color: red;">
                            <?php esc_html_e('You are using an older legacy Stripe integration connection that may become insecure. Click the button below to use the current and recommended method to re-connect your Stripe integration now.', 'wishlist-member'); ?>
                        </p>
                        <p>
                            <a href="https://wishlistmember.com/docs/stripe/#connecting-wishlist-member-and-stripe" target="_blank">
                            <?php esc_html_e('How to connect your Stripe integration', 'wishlist-member'); ?>
                            </a>
                        </p>
                    </div>
                    <?php endif; ?>
                    <div style="<?php echo $style_keys; ?>">
                        <div id="live_stripe_keys" <?php echo $stripe_keys_styles['live']; ?>>
                                <template class="wlm3-form-group">
                                {
                                    label : '<?php echo esc_js(__('Publishable Key', 'wishlist-member')); ?>',
                                    tooltip : '<?php echo esc_js(__('The Publishable Key (with the Secret Key) are used to connect WishList Member with the Stripe account. The Publishable Key is located in the Developers > API Keys section of the Stripe account.', 'wishlist-member')); ?>',
                                    name : 'stripepublishablekey',
                                    column : 'col-md-12',
                                }
                            </template>
                            <template class="wlm3-form-group">
                                {
                                    label : '<?php echo esc_js(__('Secret Key', 'wishlist-member')); ?>',
                                    tooltip : '<?php echo esc_js(__('Secret Key (with the Publishable Key) are used to connect WishList Member with the Stripe account. The Secret Key is located in the Developers > API Keys section of the Stripe account.', 'wishlist-member')); ?>',
                                    name : 'stripeapikey',
                                    column : 'col-md-12',
                                }
                            </template>
                        </div>

                        <div id="test_stripe_keys" <?php echo $stripe_keys_styles['test']; ?>>
                                <template class="wlm3-form-group">
                                {
                                    label : '<?php echo esc_js(__('Test Publishable Key', 'wishlist-member')); ?>',
                                    tooltip : '<?php echo esc_js(__('The Publishable Key (with the Secret Key) are used to connect WishList Member with the Stripe account. The Publishable Key is located in the Developers > API Keys section of the Stripe account.', 'wishlist-member')); ?>',
                                    name : 'test_stripepublishablekey',
                                    column : 'col-md-12',
                                }
                            </template>
                            <template class="wlm3-form-group">
                                {
                                    label : '<?php echo esc_js(__('Test Secret Key', 'wishlist-member')); ?>',
                                    tooltip : '<?php echo esc_js(__('Secret Key (with the Publishable Key) are used to connect WishList Member with the Stripe account. The Secret Key is located in the Developers > API Keys section of the Stripe account.', 'wishlist-member')); ?>',
                                    name : 'test_stripeapikey',
                                    column : 'col-md-12',
                                }
                            </template>
                        </div>
                    </div>
                </div>
                <div class="row -integration-keys">
                    <div style="margin-bottom: 10px;">
                        <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Enable Test Mode', 'wishlist-member')); ?>',
                            tooltip : '<?php echo esc_js(__('If enabled, no live transactions will be processed in Stripe.', 'wishlist-member')); ?>',
                            name  : 'stripetestmode',
                            class  : 'stripetestmode',
                            value : 'yes',
                            uncheck_value : 'no',
                            type  : 'checkbox',
                            column : 'col-12',
                        }
                        </template>
                    </div>
                </div>
                <div class="row">
                        <div class="col-md-12">
                            <?php if ('connected' === $connect_status) : ?>
                            <div>
                                <?php
                                $refresh_url            = add_query_arg(
                                    [
                                        'action'    => 'wlm_stripe_connect_refresh',
                                        'method-id' => \WishListMember\PaymentProviders\Stripe\Connect::get_method_id(),
                                        '_wpnonce'  => wp_create_nonce('stripe-refresh'),
                                    ],
                                    admin_url('admin-ajax.php')
                                );
                                $disconnect_url         = add_query_arg(
                                    [
                                        'action'    => 'wlm_stripe_connect_disconnect',
                                        'method-id' => \WishListMember\PaymentProviders\Stripe\Connect::get_method_id(),
                                        '_wpnonce'  => wp_create_nonce('stripe-disconnect'),
                                    ],
                                    admin_url('admin-ajax.php')
                                );
                                $disconnect_confirm_msg = __('Disconnecting from this Stripe Account will block webhooks from being processed, and prevent wishlist-member payments associated with it from working.', 'wishlist-member');
                                ?>
                              <div id="stripe-connected-actions" class="wlm-payment-option-prompt connected">
                                <?php if (empty($service_account_name)) : ?>
                                    <?php _e('Connected to Stripe', 'wishlist-member'); ?>
                                <?php else : ?>
                                    <?php printf(__('Connected to: %1$s %2$s %3$s', 'wishlist-member'), '<strong>', $service_account_name, '</strong>'); ?>
                                <?php endif; ?>
                                &nbsp;
                                <span style="<?php echo $style_keys; ?>">
                                <a href="<?php echo $refresh_url; ?>"
                                   class="stripe-btn wlm_stripe_refresh_button button-secondary"><?php _e('Refresh Stripe Credentials', 'wishlist-member'); ?></a></span>
                                <a href="<?php echo $disconnect_url; ?>" class="stripe-btn wlm_stripe_disconnect_button button-secondary"
                                   data-disconnect-msg="<?php echo $disconnect_confirm_msg; ?>">
                                  <?php _e('Disconnect', 'wishlist-member'); ?>
                                </a>
                              </div>
                            </div>
                            <?php endif; ?>
                            <?php
                            if ('disconnected' === $connect_status) :
                                ?>
                            <div class="wlm-payment-option-prompt">
                              <h4><strong><?php _e('Re-Connect to Stripe', 'wishlist-member'); ?></strong></h4>
                              <p><?php _e('This Payment Method has been disconnected so it may stop working for new and recurring payments at any time. To prevent this, re-connect your Stripe account by clicking the "Connect with Stripe" button below.', 'wishlist-member'); ?></p>
                              <p>
                                <a href="#" 
                                    data-url="<?php echo $connect_url; ?>" 
                                        data-id="<?php echo $id; ?>" 
                                        data-nonce="<?php echo wp_create_nonce('stripe-connect-save-testmode'); ?>" 
                                        class="wlm-stripe-connect-cta">
                                        <img src="<?php echo wishlistmember_instance()->plugin_url3 . '/assets/images/stripe-connect.png'; ?>" width="200" alt="<?php esc_attr_e('"Connect with Stripe" button', 'wishlist-member'); ?>">
                                  </a>
                              </p>
                            </div>
                            <?php elseif ('connected' !== $connect_status) : ?>
                            <div>
                                    <p>
                                        <a href="#" 
                                            data-url="<?php echo $connect_url; ?>" 
                                            data-id="<?php echo $id; ?>" 
                                            data-nonce="<?php echo wp_create_nonce('stripe-connect-save-testmode'); ?>" 
                                            class="wlm-stripe-connect-cta">
                                        <img src="<?php echo wishlistmember_instance()->plugin_url3 . '/assets/images/stripe-connect.png'; ?>" width="200" alt="<?php esc_attr_e('"Connect with Stripe" button', 'wishlist-member'); ?>">
                                      </a>
                                    </p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
            </div>
            <div class="tab-pane" id="stripe-settings">
                <div class="row">
                    <template class="wlm3-form-group">
                        {
                            type : 'select',
                            label : '<?php echo esc_js(__('Primary Currency', 'wishlist-member')); ?>',
                            tooltip : '<?php echo esc_js(__('Set the currency type for payments. This is the currency users will pay during registration.', 'wishlist-member')); ?>',
                            name : 'stripesettings[currency]',
                            style : 'width: 100%',
                            options : WLM3ThirdPartyIntegration.stripe.currencies,
                            column : 'col-12',
                            'data-placeholder' : '<?php echo esc_js(__('Select a Currency', 'wishlist-member')); ?>',
                        }
                    </template>
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Support Email', 'wishlist-member')); ?>',
                            tooltip : '<?php echo esc_js(__('Set the email address to receive support requests related to purchases.', 'wishlist-member')); ?>',
                            name : 'stripesettings[supportemail]',
                            column : 'col-12',
                        }
                    </template>
                    <template class="wlm3-form-group">
                        {
                            type : 'select',
                            label : '<?php echo esc_js(__('Cancellation Redirect', 'wishlist-member')); ?>',
                            tooltip : '<?php echo esc_js(__('Set the page to be displayed during cancellation.', 'wishlist-member')); ?>',
                            name : 'stripesettings[cancelredirect]',
                            style : 'width: 100%',
                            options : WLM3ThirdPartyIntegration.stripe.pages,
                            column : 'col-12',
                            'data-placeholder' : '<?php echo esc_js(__('Select a Page', 'wishlist-member')); ?>',
                        }
                    </template>
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Immediately cancel Stripe Subscription and Level in WishList Member when the user cancels their subscription via the Stripe Profile Shortcode', 'wishlist-member')); ?>',
                            tooltip : '<?php echo esc_js(__('If enabled, the Stripe Subscription will be cancelled in Stripe and the Level access will be cancelled in WishList Member if a User cancels using the option on the form displayed when using the Stripe Profile Shortcode. The Stripe Profile Shortcode can be created/inserted using the WishList Member Shortcode Inserter or a Shortcode Block.', 'wishlist-member')); ?>',
                            tooltip_size: 'md',
                            name : 'stripesettings[endsubscriptiontiming]',
                            value : 'immediate',
                            uncheck_value : 'periodend',
                            type  : 'checkbox',
                            column : 'col-11',
                        }
                    </template>
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Allow Stripe to Automatically Calculate Tax on Subscriptions', 'wishlist-member')); ?>',
                            tooltip : '<?php echo esc_js(__('If enabled, Stripe will calculate the appropriate Tax for a Subscription.', 'wishlist-member')); ?>',
                            name  : 'stripesettings[automatictax]',
                            value : 'yes',
                            uncheck_value : 'no',
                            type  : 'checkbox',
                            column : 'col-12',
                        }
                    </template>
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Do not cancel membership level in WishList Member when subscription is marked as Unpaid in Stripe', 'wishlist-member')); ?>',
                            tooltip : '<?php echo esc_js(__('If enabled, WishList Member will not cancel the membership level if the status of a subscription in Stripe is set to Unpaid', 'wishlist-member')); ?>',
                            name  : 'stripesettings[keep_unpaid_subs_active]',
                            value : 'yes',
                            uncheck_value : 'no',
                            type  : 'checkbox',
                            column : 'col-12',
                        }
                    </template>
                </div>
            </div>
            <div class="tab-pane" id="stripe-form">
                <div class="row">
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Heading Text', 'wishlist-member')); ?>',
                            tooltip : '<?php echo esc_js(__('Set the text to appear on the Stripe Payment Pop Up. %level will display the name of the Level in the Pop Up.', 'wishlist-member')); ?>',
                            name : 'stripesettings[formheading]',
                            column : 'col-12',
                        }
                    </template>
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Heading Logo', 'wishlist-member')); ?>',
                            tooltip : '<?php echo esc_js(__('Set the logo to appear on the top of the Stripe Payment Pop Up.', 'wishlist-member')); ?>',
                            name : 'stripesettings[logo]',
                            column : 'col-12',
                            type : 'wlm3media'
                        }
                    </template>
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Button Label', 'wishlist-member')); ?>',
                            tooltip : '<?php echo esc_js(__('Set the Label that appears on the purchase Button within the Stripe Payment Pop Up.', 'wishlist-member')); ?>',
                            name : 'stripesettings[buttonlabel]',
                            column : 'col-6',
                        }
                    </template>
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Panel Button Label', 'wishlist-member')); ?>',
                            tooltip : '<?php echo esc_js(__('Set the Label that appears on the purchase Panel within the Stripe Payment Pop Up.', 'wishlist-member')); ?>',
                            name : 'stripesettings[panelbuttonlabel]',
                            column : 'col-6',
                        }
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>
