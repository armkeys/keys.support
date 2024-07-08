<?php

/**
 * Getting Started Wizard - License
 *
 * @package WishListMember/Wizard
 */

$step_title  = __('License', 'wishlist-member');
$video_url   = 'https://wishlistmember.com/docs/videos/wlm/wizard/license';
$heading_tag = $wizard_mode ? 'h3' : 'h2';
?>
<div class="card wizard wizard-form mx-auto <?php echo $wizard_mode ? 'd-none' : ''; ?> <?php echo wishlistmember_instance()->get_option('wizard/' . $stepname) ? 'is-run' : ''; ?>" data-step-name="<?php echo esc_attr($stepname); ?>" id="<?php echo esc_attr($stepname); ?>">
    <div class="card-header border-0 bg-light px-0">
        <h2 class="wizard-title-heading my-0"><?php echo esc_html(wlm_or($step_title_header, $step_title)); ?></h2>
    </div>
    <div class="card-body border border-bottom-0">
        <div class="row">
            <?php
            require __DIR__ . '/parts/video-column.php';
            ?>
            <div class="col-<?php echo $wizard_mode ? 12 : 7; ?>">
                <<?php echo esc_html($heading_tag); ?> class="mb-3"><?php esc_html_e('Enter your WishList Member License Key', 'wishlist-member'); ?></<?php echo esc_html($heading_tag); ?>>
                <p>
                    <?php
                        echo wp_kses(
                            sprintf(
                                // Translators: 1 - Download Link.
                                __('You were issued a license key when you purchased WishList Member. You can find that license key in the email you were sent after your purchase or you can get it <a href="%1$s" target="_blank">right here</a>. Simply enter your license key into the field and click Save & Continue.', 'wishlist-member'),
                                'https://customers.wishlistproducts.com/downloads/'
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
                <div class="form-group large-form">
                    <label for=""><?php esc_html_e('License Key', 'wishlist-member'); ?></label>
                    <input type="text" name="license" class="form-control input-lg mb-0" value="">
                    <input type="hidden" name="license_only" value="<?php echo $wizard_mode ? 0 : 1; ?>">
                </div>
            </div>
            <div class="col-12 text-center">
                <span class="wizard-step-result form-text text-danger d-none"></span>
            </div>
        </div>
    </div>
    <div class="card-footer bg-light border border-top-0 py-3 text-center">
        <button class="btn -primary pull-right -wizard-next"><span class="d-none d-sm-inline"><?php esc_html_e('Save & Continue', 'wishlist-member'); ?></span><i class="wlm-icons">arrow_forward</i></button>
        <?php if ($wizard_mode) : ?>
        <button class="btn -default pull-right -wizard-prev mr-3"><i class="wlm-icons">arrow_back</i><span class="d-none d-sm-inline"><?php esc_html_e('Back', 'wishlist-member'); ?></span></button>
        <a href="#" class="btn -bare -outline pull-left bg-light border -exit-wizard"><?php esc_html_e('Exit Wizard', 'wishlist-member'); ?></a>
        <?php endif; ?>
        <a href="#" class="btn -bare -lg pull-left skip-license -wizard-next">
            <?php esc_html_e('Skip', 'wishlist-member'); ?>
        </a>
    </div>
</div>
