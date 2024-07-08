<?php
    $_redirects = [
        'afterreg' => __('Custom After Registration Redirect for Pay Per Posts', 'wishlist-member'),
        'login'    => __('Custom After Login Redirect for Pay Per Posts', 'wishlist-member'),
    ];

    ?>
<?php foreach ($_redirects as $redirect_type => $redirect_title) : ?>
<div
    id="custom-redirects-modal-<?php echo esc_attr($redirect_type); ?>"
    data-id="custom-redirects-<?php echo esc_attr($redirect_type); ?>"
    data-label="custom-redirects-<?php echo esc_attr($redirect_type); ?>"
    data-title="<span><?php echo esc_attr($redirect_title); ?></span>"
    data-classes="modal-lg"
    data-show-default-footer=""
    style="display:none">
    <div class="body">
        <div class="-holder">
            <input type="hidden" name="action" value="admin_actions" />
            <input type="hidden" name="WishListMemberAction" value="save_payperpost_settings" />
            <div class="row">
                <div class="col-md-12">
                    <h4 class="mb-3"><?php esc_html_e('Select one of the following options:', 'wishlist-member'); ?></h4>
                </div>
                <div class="col-md-2 col-border-right redirect-type-toggle">
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Page', 'wishlist-member')); ?>',
                            name: 'payperpost[<?php echo esc_attr($redirect_type); ?>_redirect_type]',
                            value : 'page',
                            checked_value : <?php echo json_encode($ppp_settings[ $redirect_type . '_redirect_type' ]); ?>,
                            type: 'radio',
                            class: 'modal-input -redirect-type',
                        }
                    </template>
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Message', 'wishlist-member')); ?>',
                            name: 'payperpost[<?php echo esc_attr($redirect_type); ?>_redirect_type]',
                            value : 'message',
                            checked_value : <?php echo json_encode($ppp_settings[ $redirect_type . '_redirect_type' ]); ?>,
                            type: 'radio',
                            class: 'modal-input -redirect-type',
                        }
                    </template>
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('URL', 'wishlist-member')); ?>',
                            name: 'payperpost[<?php echo esc_attr($redirect_type); ?>_redirect_type]',
                            value : 'url',
                            checked_value : <?php echo json_encode($ppp_settings[ $redirect_type . '_redirect_type' ]); ?>,
                            type: 'radio',
                            class: 'modal-input -redirect-type',
                        }
                    </template>
                </div>
                <div class="col-md-10">
                    <div class="type-page redirect-type"
                    <?php
                    if ('page' !== wlm_arrval($ppp_settings, $redirect_type . '_redirect_type')) {
                        echo 'style="display: none;"';
                    }
                    ?>
                    >
                        <div class="row">
                            <template class="wlm3-form-group">
                                {
                                    name  : 'payperpost[<?php echo esc_attr($redirect_type); ?>_page]',
                                    value  : <?php echo json_encode($ppp_settings[ $redirect_type . '_page' ]); ?>,
                                    type  : 'select',
                                    style : 'width:100%',
                                    options : <?php echo esc_js($redirect_type); ?>_pages,
                                    class : 'system-page',
                                    column: 'col-md-8 col-sm-10',
                                }
                            </template>
                            <div class="col-md-4 col-sm-2">
                                <a href="#<?php echo esc_attr($redirect_type); ?>_create_page" data-toggle="collapse" class="btn -success -icon-only" style="margin-bottom: 15px">
                                    <i class="wlm-icons">add</i>
                                </a>
                            </div>
                        </div>
                        <div class="collapse" id="<?php echo esc_attr($redirect_type); ?>_create_page">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <input type="text" class="form-control create-page" placeholder="Page title" required="required">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <a href="#" class="btn -primary -condensed -no-icon create-page-btn" title="Create Page">
                                        <span><?php esc_html_e('Create Page', 'wishlist-member'); ?></span>
                                    </a>
                                    <a href="#<?php echo esc_attr($redirect_type); ?>_create_page" data-toggle="collapse" class="btn -bare -condensed -icon-only" title="Create Page">
                                        <i class="wlm-icons">close</i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="type-message redirect-type"
                    <?php
                    if ('message' !== wlm_arrval($ppp_settings, $redirect_type . '_redirect_type')) {
                        echo 'style="display: none;"';
                    }
                    ?>
                    >
                        <div class="row">
                            <template class="wlm3-form-group">
                                {
                                    name: 'payperpost[<?php echo esc_attr($redirect_type); ?>_message]',
                                    value: <?php echo json_encode(stripslashes($ppp_settings[ $redirect_type . '_message' ]), JSON_UNESCAPED_SLASHES); ?>,
                                    type: 'textarea',
                                    class: 'richtext',
                                    rows: 10,
                                    cols: 30,
                                    column: 'col-md-12',
                                    group_class : 'mb-2',
                                    id: 'payperpost-<?php echo esc_js($redirect_type); ?>-message'
                                }
                            </template>
                            <div class="col-md-12">
                                <button class="btn -default -condensed page-message-reset-button" data-target="#payperpost-<?php echo esc_attr($redirect_type); ?>-message" data-type="<?php echo esc_attr($redirect_type); ?>"><?php esc_html_e('Reset to Default', 'wishlist-member'); ?></button>
                                <?php if ('logout' !== $redirect_type) : ?>
                                    <template class="wlm3-form-group">
                                        {
                                            type : 'select',
                                            column : 'col-md-5 pull-right no-margin no-padding',
                                            'data-placeholder' : '<?php echo esc_js(__('Insert Merge Codes', 'wishlist-member')); ?>',
                                            group_class : 'shortcode_inserter',
                                            style : 'width: 100%',
                                            value : -1,
                                            options : wlm_shortcodes,
                                            grouped: true,
                                            class : 'insert_text_at_caret',
                                            'data-target': '#payperpost-<?php echo esc_attr($redirect_type); ?>-message',
                                        }
                                    </template>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="type-url redirect-type"
                    <?php
                    if ('url' !== wlm_arrval($ppp_settings, $redirect_type . '_redirect_type')) {
                        echo 'style="display: none;"';
                    }
                    ?>
                    >
                        <div class="row">
                            <template class="wlm3-form-group">
                                {
                                    name: 'payperpost[<?php echo esc_attr($redirect_type); ?>_url]',
                                    value: <?php echo json_encode($ppp_settings[ $redirect_type . '_url' ]); ?>,
                                    type: 'text',
                                    placeholder: '<?php echo esc_js(__('Specify the URL', 'wishlist-member')); ?>',
                                    column: 'col-md-12',
                                }
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="footer">
        <a data-toggle="modal" data-target="#custom-redirects-<?php echo esc_attr($redirect_type); ?>" data-btype="cancel" href="#" class="save-button btn -bare">
            <span><?php esc_html_e('Close', 'wishlist-member'); ?></span>
        </a>
        <a data-toggle="modal" data-target="#custom-redirects-<?php echo esc_attr($redirect_type); ?>" data-btype="save" href="" class="save-button btn -primary">
            <i class="wlm-icons">save</i>
            <span><?php esc_html_e('Save', 'wishlist-member'); ?></span>
        </a>
        &nbsp;
        <a data-toggle="modal" data-target="#custom-redirects-<?php echo esc_attr($redirect_type); ?>" data-btype="save-close" href="" class="save-button btn -success">
            <i class="wlm-icons">save</i>
            <span>Save &amp; Close</span>
        </a>
    </div>
</div>
<?php endforeach; ?>
