<div class="page-header">
    <div class="row">
        <div class="col-md-9 col-sm-9 col-xs-8">
            <h2 class="page-title">
                <?php esc_html_e('Caching', 'wishlist-member'); ?>
            </h2>
        </div>
        <div class="col-md-3 col-sm-3 col-xs-4">
            <?php require $this->plugin_dir3 . '/helpers/header-icons.php'; ?>
        </div>
    </div>
</div>
<div class="content-wrapper">
    <div class="row">
        <?php
            $prefix = $this->get_option('CookiePrefix');
            $prefix = $prefix ? $prefix : 'wlm_';
        ?>
        <div class="col-md-12">
            <label for="">
                Cookie Prefix
                <?php $this->tooltip(__('Prepends the specified prefix to cookies set by WishList Member. This can help prevent caching on specific cookies by certain web hosting providers.', 'wishlist-member')); ?>
            </label>
            <div class="row">
                <div class="col-md-3 no-margin">
                    <template class="wlm3-form-group">
                        {
                            name  : 'CookiePrefix',
                            value : '<?php echo esc_js($prefix); ?>',
                            group_class : 'no-margin',
                            'data-initial' : '<?php echo esc_js($prefix); ?>',
                            class : 'cookie-prefix-apply',
                        }
                    </template>
                    <br />
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <h4><?php esc_html_e('Caching Instructions:', 'wishlist-member'); ?></h4>
            <p><?php esc_html_e('WishList Member does not do any caching on its own. We utilize the WordPress cache functions.', 'wishlist-member'); ?></p>
            <p><?php esc_html_e('If you would like to run a third-party caching plugin on your site, we recommend reading the following help article for more details.', 'wishlist-member'); ?></p>
            <p><a href="https://wishlistmember.com/docs/caching/" target="_blank">https://wishlistmember.com/docs/caching/</a></p>
        </div>
    </div>

</div>




