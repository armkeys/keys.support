<?php

/**
 * Getting Started Wizard - Welcome
 *
 * @package WishListMember/Wizard
 */

$step_title = __('Welcome', 'wishlist-member');
$video_url  = 'https://wishlistmember.com/docs/videos/wlm/wizard/welcome';
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
                <h3 class="mb-3"><?php esc_html_e('Welcome to the WishList Member Setup Wizard', 'wishlist-member'); ?></h3>
                <p>
                    <?php
                        echo wp_kses(
                            sprintf(
                                // Translators: 1 - Link to support.
                                __('You are moments away from setting up your membership site. A series of short videos have been included to help make it quick and easy for you. As always, our <a href="%1$s" target="_blank">support team</a> is also available and ready to help any time you need it. Let\'s get started!', 'wishlist-member'),
                                'https://wishlistmember.com/support-options/'
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
        </div>
    </div>
    <div class="card-footer bg-light border border-top-0 py-3 text-center">
        <button class="btn -primary pull-right -wizard-next"><span class="d-none d-sm-inline"><?php esc_html_e('Start', 'wishlist-member'); ?></span><i class="wlm-icons">arrow_forward</i></button>
        <a href="#" class="btn -bare -outline pull-left bg-light border -exit-wizard"><?php esc_html_e('Exit Wizard', 'wishlist-member'); ?></a>
    </div>
</div>
