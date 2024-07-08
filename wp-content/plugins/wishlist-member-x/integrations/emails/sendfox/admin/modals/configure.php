<div
    data-process="modal"
    id="configure-<?php echo esc_attr($config['id']); ?>-template" 
    data-id="configure-<?php echo esc_attr($config['id']); ?>"
    data-label="configure-<?php echo esc_attr($config['id']); ?>"
    data-title="<?php echo esc_attr($config['name']); ?> Configuration"
    data-show-default-footer="1"
    style="display:none">
    <div class="body">
        <div class="row -integration-keys">
            <?php echo wp_kses_post($api_status_markup); ?>
            <div class="col-md-12">
                <p>
                <?php
                /* Translators: 1: <a> link to https://sendfox.com/account/oauth */ echo wp_kses(
                    sprintf(__('Generate your SendFox Personal Access Token by going to %s and paste it below.', 'wishlist-member'), '<a href="https://sendfox.com/account/oauth" target="_blank">https://sendfox.com/account/oauth</a>'),
                    [
                        'a' => [
                            'href'   => [],
                            'target' => [],
                        ],
                    ]
                );
                ?>
                </p>
            </div>
            <template class="wlm3-form-group">{label : '<?php echo esc_js(__('Personal Access Token', 'wishlist-member')); ?>', type : 'textarea', name : 'personal_access_token', column : 'col-md-12'}</template>
        </div>
    </div>
</div>
