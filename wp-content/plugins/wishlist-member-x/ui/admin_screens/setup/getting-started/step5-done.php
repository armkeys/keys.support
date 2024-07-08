<?php

/**
 * Getting Started Wizard - Done
 *
 * @package WishListMember/Wizard
 */

$step_title = __('Finish', 'wishlist-member');
$video_url  = 'https://wishlistmember.com/docs/videos/wlm/wizard/done';
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
            <div class="col-12 text-center font-weight-bold">
                <img src="<?php echo esc_url(wishlistmember_instance()->plugin_url3 . '/ui/images/trophy.png'); ?>" width="200" class="mb-4">
                <p><?php esc_html_e('You did it! All you have left is to click the Finish button.', 'wishlist-member'); ?></p>
                <p>
                    <?php
                    echo wp_kses(
                        sprintf(
                            // Translators: 1 - link to WishList Member Support.
                            __('As a reminder, <a href="%1$s" target="_blank">our team is here to help</a> if you need it.', 'wishlist-member'),
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
            <div class="col-12 text-center">
                <span class="wizard-step-result form-text text-danger d-none"></span>
            </div>
        </div>
    </div>
    <div class="card-footer bg-light border border-top-0 py-3 text-center">
        <button class="btn -success pull-right -exit-wizard"><span class="d-none d-sm-inline"><?php esc_html_e('Finish', 'wishlist-member'); ?></span><i class="wlm-icons">check</i></button>
        <button class="btn -default pull-right -wizard-prev mr-3"><i class="wlm-icons">arrow_back</i><span class="d-none d-sm-inline"><?php esc_html_e('Back', 'wishlist-member'); ?></span></button>
    </div>
</div>
