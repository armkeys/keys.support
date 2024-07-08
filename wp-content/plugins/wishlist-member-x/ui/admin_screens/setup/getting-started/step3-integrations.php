<?php

/**
 * Getting Started Wizard - Integrations
 *
 * @package WishListMember/Wizard
 */

$step_title = __('Integrations', 'wishlist-member');
$video_url  = 'https://wishlistmember.com/docs/videos/wlm/wizard/integrations';
// Prepare payment provider dropdown options.
$payment_providers        = glob($this->plugin_dir3 . '/integrations/payments/*/config.php');
$active_payment_providers = [];
array_walk(
    $payment_providers,
    function (&$provider) use (&$active_payment_providers) {
        $provider = include $provider;
        $active   = wishlistmember_instance()->payment_integration_is_active(wlm_arrval($provider, 'id')) ? '(' . __('Already Enabled', 'wishlist-member') . ')' : '';
        $provider = wlm_arrval($provider, 'legacy') ? false : [
            'value' => wlm_arrval($provider, 'id'),
            'text'  => trim(wlm_arrval($provider, 'name') . ' ' . $active),
        ];
    }
);
array_unshift(
    $payment_providers,
    [
        'value' => '',
        'text'  => '',
    ]
);
$payment_providers = array_filter($payment_providers);

// Prepare email provider dropdown options.
$email_providers        = glob($this->plugin_dir3 . '/integrations/emails/*/config.php');
$active_email_providers = [];
array_walk(
    $email_providers,
    function (&$provider) use (&$active_email_providers) {
        $provider = include $provider;
        $active   = wishlistmember_instance()->email_integration_is_active(wlm_arrval($provider, 'id')) ? '(' . __('Already Enabled', 'wishlist-member') . ')' : '';
        $provider = wlm_arrval($provider, 'legacy') ? false : [
            'value' => wlm_arrval($provider, 'id'),
            'text'  => trim(wlm_arrval($provider, 'name') . ' ' . $active),
        ];
    }
);
array_unshift(
    $email_providers,
    [
        'value' => '',
        'text'  => '',
    ]
);
$email_providers = array_filter($email_providers);
?>
<div class="card wizard wizard-form d-none mx-auto <?php echo wishlistmember_instance()->get_option('wizard/' . $stepname) ? 'is-run' : ''; ?>" data-step-name="<?php echo esc_attr($stepname); ?>" id="<?php echo esc_attr($stepname); ?>">
    <div class="card-header border-0 bg-light px-0">
        <h2 class="wizard-title-heading my-0"><?php echo esc_html(wlm_or($step_title_header, $step_title)); ?></h2>
    </div>
    <div class="card-body border border-bottom-0">
        <div class="row">
            <?php
            require __DIR__ . '/parts/video-column.php';
            ?>
            <div class="col-12">
                <div class="row">
                    <div class="col-12">
                        <h3 class="mb-3"><?php esc_html_e('Enable Integrations', 'wishlist-member'); ?></h3>
                        <p>
                            <?php
                                echo wp_kses(
                                    sprintf(
                                        // Translators: 1 - Link to Support.
                                        __('You have the option of integrating with a payment provider or with an email provider (or both). Integrating with a payment provider allows you to accept payments for access to your site while integrating with an email provider allows for another method of contacting and marketing to your members. If you don\'t see your preferred provider in the lists below, feel free to <a href="%1$s" target="_blank">let our team know</a>.', 'wishlist-member'),
                                        'https://my.wishlistmember.com/support/'
                                    ),
                                    [
                                        'a' => [
                                            'href'   => 1,
                                            'target' => 1,
                                        ],
                                    ]
                                );
                                ?>
                        </p>
                    </div>
                    <template class="wlm3-form-group">
                        [
                            {
                                label: '<?php echo esc_js(__('Payment Provider', 'wishlist-member')); ?>',
                                name: 'integration/payment',
                                type: 'select',
                                style: 'width: 100%',
                                column: 'col-lg-7',
                                value : '<?php echo esc_js(wlm_trim(wishlistmember_instance()->get_option('wizard/integration/payment/configure'))); ?>',
                                'data-placeholder' : '<?php echo esc_js(__('None', 'wishlist-member')); ?>',
                                'data-allow-clear': 1,
                                options: <?php echo wp_json_encode($payment_providers); ?>,
                                tooltip : '<?php echo esc_js(__('You can select the payment provider of your choice and can complete the integration process later. Or you can skip this step all together if you don\'t need it or plan to look into this more later on.', 'wishlist-member')); ?>',
                                tooltip_size: 'lg'
                            },
                            {
                                label: '<?php echo esc_js(__('Email Provider', 'wishlist-member')); ?>',
                                name: 'integration/email',
                                type: 'select',
                                style: 'width: 100%',
                                column: 'col-lg-7',
                                value : '<?php echo esc_js(wlm_trim(wishlistmember_instance()->get_option('wizard/integration/email/configure'))); ?>',
                                'data-placeholder' : '<?php echo esc_js(__('None', 'wishlist-member')); ?>',
                                'data-allow-clear': 1,
                                options: <?php echo wp_json_encode($email_providers); ?>,
                                tooltip : '<?php echo esc_js(__('You can select the email provider of your choice and can complete the integration process later. Or you can skip this step all together if you don\'t need it or plan to look into this more later on.', 'wishlist-member')); ?>',
                                tooltip_size: 'lg'
                            }
                        ]
                    </template>
                </div>
            </div>
            <div class="col-12 text-center">
                <span class="wizard-step-result form-text text-danger d-none"></span>
            </div>
        </div>
    </div>
    <div class="card-footer bg-light border border-top-0 py-3 text-center">
        <button class="btn -primary pull-right -wizard-next"><span class="d-none d-sm-inline"><?php esc_html_e('Save & Continue', 'wishlist-member'); ?></span><i class="wlm-icons">arrow_forward</i></button>
        <button class="btn -default pull-right -wizard-prev mr-3"><i class="wlm-icons">arrow_back</i><span class="d-none d-sm-inline"><?php esc_html_e('Back', 'wishlist-member'); ?></span></button>
        <a href="#" class="btn -bare -outline pull-left bg-light border -exit-wizard"><?php esc_html_e('Exit Wizard', 'wishlist-member'); ?></a>
    </div>
</div>
