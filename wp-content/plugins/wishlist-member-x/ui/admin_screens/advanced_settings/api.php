<div class="page-header">
    <div class="row">
        <div class="col-md-9 col-sm-9 col-xs-8">
            <h2 class="page-title">
                <?php esc_html_e('API', 'wishlist-member'); ?>
            </h2>
        </div>
        <div class="col-md-3 col-sm-3 col-xs-4">
            <?php require $this->plugin_dir3 . '/helpers/header-icons.php'; ?>
        </div>
    </div>
</div>
<div class="content-wrapper">
    <div class="row">
        <template class="wlm3-form-group">
            {
                label : '<?php echo esc_js(__('API URL', 'wishlist-member')); ?>',
                column : 'col-12 col-md-6',
                value : '<?php echo esc_js(admin_url()); ?>',
                class : 'copyable',
                readonly : 'readonly',
                tooltip : '<?php echo esc_js(__('All WishList Member API requests for this site should be sent to this API URL. A selection of integrations with WishList Member also require the API URL field and those can be seen in the Setup > Integrations > Payment Providers section of WishList Member.', 'wishlist-member')); ?>',
                tooltip_size: 'lg',
                group_class: 'mb-0',
            }
        </template>
        <div class="col-md-12 mb-3">
            <small class="form-text text-muted" id="helpBlock">
                <?php
                    echo wp_kses_post(
                        sprintf(
                            // Translators: %s: Knowledgebase URL.
                            __('<a href="%s" target="_blank">Click here</a> for more details on the API URL format.', 'wishlist-member'),
                            'https://wishlistmember.com/docs/api-url-api-data-not-being-received/'
                        )
                    );
                    ?>
            </small>
            <br />
        </div>

    </div>
    <div class="row">
        <?php
            $api_key = $this->get_option('WLMAPIKey');
        if (! $api_key) {
            $api_key = wlm_generate_password(50, false);
            $this->get_option('WLMAPIKey', $api_key);
        }
        ?>
        <template class="wlm3-form-group">
            {
                label : '<?php echo esc_js(__('Digest Auth Username', 'wishlist-member')); ?>',
                column : 'col-12 col-md-6',
                value : 'wishlist',
                class : 'copyable',
                readonly : 'readonly',
                tooltip: '<?php echo esc_js(__('The Digest Auth Username option cannot be edited but is required if developers want to access the WishList Member API when using the Digest Auth method.', 'wishlist-member')); ?>'
            }
        </template>
        <div class="col-md-12">
            <label for="">
                <?php esc_html_e('API Key / Digest Auth Password', 'wishlist-member'); ?>
            </label>
            <div class="row">
                <div class="col-12 col-md-6 no-margin">
                    <div class="form-group no-margin">
                        <div class="input-group -form-tight">
                            <input type="text" name="WLMAPIKey" class="form-control api-key-apply" data-initial="<?php echo esc_attr($api_key); ?>" value="<?php echo esc_attr($api_key); ?>" />
                            <div class="input-group-append">
                                <button class="btn -default generate"><?php esc_html_e('Generate', 'wishlist-member'); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <br>
            <ul class="list-unstyled">
                <li><?php esc_html_e('This Key/Password is used by developers to access the WishList Member API. It is also used by certain WishList Member integrations.', 'wishlist-member'); ?></li>
                <li>* <?php esc_html_e('Please note, if this Key/Password is modified, any integrations that use the key/password will need to be updated and reconnected.', 'wishlist-member'); ?></li>
            </ul>
            <!-- start: v4 -->
            <small class="form-text text-muted">
                <?php esc_html_e('For documentation and examples visit our site for developers:', 'wishlist-member'); ?>
                <a href="https://codex.wishlistproducts.com" target="blank">codex.wishlistproducts.com</a>
            </small>
            <!-- end: v4 -->
        </div>
    </div>
</div>
