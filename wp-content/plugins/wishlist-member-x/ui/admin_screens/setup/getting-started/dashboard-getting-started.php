<?php

/**
 * Getting Started Dashboard Banner
 *
 * @package WishListMember/Wizard
 */

?>
<?php if (count(wishlistmember_instance()->get_option('wpm_levels')) <= 0 && wishlistmember_instance()->access_control->current_user_can('wishlistmember3_setup/getting-started')) : ?>
<div class="panel panel-default -no-header -getting-started-panel">
    <div class="panel-body">
        <div class="row no-gutters align-items-center img-container">
            <div class="col-auto p-3">
                <img src="<?php echo esc_url(wishlistmember_instance()->plugin_url3); ?>/ui/images/wlm-logo-small.png" class="mx-auto d-block" alt="">
            </div>
            <div class="col">
                <div class="white-bg row align-items-center no-gutters">
                    <div class="col py-1 pr-sm-3">
                        <p><?php esc_html_e('The WishList Member Setup Wizard is here to help you get your site setup quickly.', 'wishlist-member'); ?></p>
                    </div>
                    <div class="col-auto col-lg-auto col-md-12 py-1">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=WishListMember&wl=setup/getting-started')); ?>" class="btn -success -condensed d-block mx-auto pull-right" target="_parent">
                            <i class="wlm-icons">input</i>
                            <span><?php esc_html_e('Run Wizard Now', 'wishlist-member'); ?></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
