<?php

/**
 * Getting Started Wizard - Membership Pages
 *
 * @package WishListMember/Wizard
 */

$step_title        = __('Pages', 'wishlist-member');
$step_title_header = __('Membership Pages', 'wishlist-member');
$video_url         = 'https://wishlistmember.com/docs/videos/wlm/wizard/membership-pages';
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
                        <h2><?php esc_html_e('Create Membership Pages', 'wishlist-member'); ?></h2>
                        <p><?php esc_html_e('The pages you enable below will be added to your site. You can click to disable any you don\'t want to use.', 'wishlist-member'); ?></p>
                    </div>
                    <template class="wlm3-form-group">
                        [
                            {
                                label: '<?php echo esc_js(__('Membership Registration Page for FREE Users (creates a free registration page template)', 'wishlist-member')); ?>',
                                name: 'pages/registration/free',
                                type: 'toggle-switch',
                                column: 'col-12',
                                checked: <?php echo wp_json_encode(! (bool) wishlistmember_instance()->get_option('wizard/membership-pages')); ?>,
                                value: 1,
                                tooltip : '<?php echo esc_js(__('A registration page for free users can be created if you plan to allow users to sign up for free. If enabled, a page will be created with a Gutenberg template. You can add your content to this page at any time after completing the Setup Wizard.')); ?>',
                                tooltip_size: 'lg'
                            },
                            {
                                label: '<?php echo esc_js(__('Membership Sales Page for PAID Users (creates a sales page template)', 'wishlist-member')); ?>',
                                name: 'pages/registration/paid',
                                type: 'toggle-switch',
                                column: 'col-12',
                                checked: <?php echo wp_json_encode(! (bool) wishlistmember_instance()->get_option('wizard/membership-pages')); ?>,
                                value: 1,
                                tooltip : '<?php echo esc_js(__('A registration page for paid users can be created if you plan to accept payment to allow users to sign up. If enabled, a page will be created with a Gutenberg template. You can add your content to this page at any time after completing the Setup Wizard.', 'wishlist-member')); ?>',
                                tooltip_size: 'lg'
                            },
                            {
                                label: '<?php echo esc_js(__('Styled Membership Login Page (customizes the default WordPress login page)', 'wishlist-member')); ?>',
                                name: 'pages/login/styled',
                                type: 'toggle-switch',
                                column: 'col-12',
                                checked: <?php echo wp_json_encode(! (bool) wishlistmember_instance()->get_option('login_styling_enable_custom_template')); ?>,
                                value: 1,
                                tooltip : '<?php echo esc_js(__('A styled template is applied to the default WordPress login page. You can customize the applied template with your logo, site colors, etc. or select another template from the library of provided templates at any time after completing the Setup Wizard.', 'wishlist-member')); ?>',
                                tooltip_size: 'lg'
                            },
                            {
                                label: '<?php echo esc_js(__('Member Welcome Page (appears one time after a member registers)', 'wishlist-member')); ?>',
                                name: 'pages/onboarding',
                                type: 'toggle-switch',
                                column: 'col-12',
                                checked: <?php echo wp_json_encode(! (bool) wishlistmember_instance()->get_option('wizard/membership-pages')); ?>,
                                value: 1,
                                tooltip : '<?php echo esc_js(__('A Member Welcome page (often referred to as the After Registration page) will only be seen once. If enabled, a page will be created with a Gutenberg template and set as the Welcome page. You can add your content to this page at any time after completing the Setup Wizard.', 'wishlist-member')); ?>',
                                tooltip_size: 'lg'
                            },
                            {
                                label: '<?php echo esc_js(__('Member Dashboard (appears each time after a member logs in)', 'wishlist-member')); ?>',
                                name: 'pages/dashboard',
                                type: 'toggle-switch',
                                column: 'col-12',
                                checked: <?php echo wp_json_encode(! (bool) wishlistmember_instance()->get_option('wizard/membership-pages')); ?>,
                                value: 1,
                                tooltip : '<?php echo esc_js(__('A Member Dashboard page (often referred to as the After Login page) will be seen each time a member logs in. If enabled, a page will be created with a Gutenberg template and set as the Member Dashboard. You can add your content to this page at any time after completing the Setup Wizard.', 'wishlist-member')); ?>',
                                tooltip_size: 'lg'
                            },
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
