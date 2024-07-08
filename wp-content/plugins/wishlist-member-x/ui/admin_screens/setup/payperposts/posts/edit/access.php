<div role="tabpanel" class="tab-pane active" id="ppps_access">
    <div class="content-wrapper">
        <div class="row">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Enable Pay Per Post for this content', 'wishlist-member')); ?>',
                    name  : 'is_ppp',
                    value : '1',
                    uncheck_value : '',
                    checked_value : <?php echo esc_js($data['is_ppp']); ?>,
                    type  : 'toggle-adjacent',
                    column : 'col-md-12',
                    class : 'ppp-toggle',
                    tooltip : '<?php echo esc_js(__('If enabled, the content is protected and can be used as a Pay Per Post. This means you can allow users to register for access to the post for free (if the “Allow Free Registration for this content” option is enabled below) or you can set up an integration in order to accept payment before providing access to the content. You can also manually assign access to users.', 'wishlist-member')); ?>',
                    tooltip_size : 'md'
                }
            </template>
            <div class="col-md-12" 
            <?php
            if (! $data['is_ppp']) {
                echo 'style="display: none;"';
            }
            ?>
            >
                <div class="row">
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Allow Free Registration for this content', 'wishlist-member')); ?>',
                            name  : 'free_ppp',
                            value : '1',
                            uncheck_value : '',
                            type  : 'toggle-adjacent',
                            column : 'col-md-12',
                            class : 'ppp-toggle',
                            tooltip : '<?php echo esc_js(__('If enabled, a Free Registration URL will be displayed and a user can register for access to the piece of content for free. ', 'wishlist-member')); ?>',
                            tooltip_size : 'md'
                        }
                    </template>
                    <div class="col-md-6" 
                    <?php
                    if (! $data['free_ppp']) {
                        echo 'style="display: none;"';
                    }
                    ?>
                    >
                        <template class="wlm3-form-group">
                            {
                                label : '<?php echo esc_js(__('Free Registration URL', 'wishlist-member')); ?>',
                                value : '<?php echo esc_js(WLM_REGISTRATION_URL . '/payperpost/' . $pay_per_post->ID); ?>',
                                class : 'copyable',
                                tooltip : '<?php echo esc_js(__('The Registration URL will direct to a page with a registration form that can be used to register.', 'wishlist-member')); ?>',
                                tooltip_size : 'md'
                            }
                        </template>
                    </div>
                </div>
            </div>
        </div>
        <br>
        <div class="panel-footer -content-footer">
            <div class="row">
                <div class="col-md-12 text-right">
                    <?php echo wp_kses_post($tab_footer); ?>
                </div>
            </div>
        </div>
    </div>
</div>
