<div
    data-process="modal"
    id="webhooks-incoming-modal-template" 
    data-id="webhooks-incoming-modal"
    data-label="webhooks-incoming-modal"
    data-title="Editing <?php echo esc_attr($config['name']); ?> Settings"
    data-show-default-footer="1"
    style="display:none">
    <div class="body">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item" role="presentation"><a class="nav-link active" href="#incoming-modal-settings" role="tab" data-toggle="tab"><?php esc_html_e('Settings', 'wishlist-member'); ?></a></li>
            <li class="nav-item" role="presentation"><a class="nav-link" href="#incoming-modal-actions" role="tab" data-toggle="tab"><?php esc_html_e('Actions', 'wishlist-member'); ?></a></li>
            <li class="nav-item" role="presentation"><a class="nav-link" href="#incoming-modal-datamapping" role="tab" data-toggle="tab"><?php esc_html_e('Data Mapping', 'wishlist-member'); ?></a></li>
        </ul>
        <div class="tab-content">
            <div role="tabpanel" class="tab-pane active" id="incoming-modal-settings">
                <div class="row">
                    <template class="wlm3-form-group">
                        {
                            label: '<?php echo esc_js(__('Name', 'wishlist-member')); ?>',
                            type: 'text',
                            column : 'col-12',
                            placeholder : "<?php echo esc_js(__('Your webhook\'s name', 'wishlist-member')); ?>",
                            'data-name' : '[name]',
                        }
                    </template>
                    <template class="wlm3-form-group">
                        {
                            label: '<?php echo esc_js(__('WebHook URL', 'wishlist-member')); ?>',
                            type: 'text',
                            readonly : 'readonly',
                            class : 'copyable',
                            id : 'wlm-webhook-url',
                            column : 'col-12',
                            'data-prefix' : '<?php echo esc_url(add_query_arg('wlm_webhook', '__wlm_webhook__', get_bloginfo('url'))); ?>'
                        }
                    </template>
                    <template class="wlm3-form-group">
                        {
                            label: '<?php echo esc_js(__('Also accept GET Requests', 'wishlist-member')); ?>',
                            type : 'toggle-switch',
                            column : 'col-12 mb-3',
                            value : '1',
                            uncheck_value : '0',
                            'data-name' : '[process_get_requests]',
                            tooltip : '<?php echo esc_js(__('By default this webhook URL will accept POST data. If this setting is enabled, this webhook URL will accept GET requests if POST data is not found.', 'wishlist-member')); ?>',
                        }
                    </template>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane" id="incoming-modal-actions">
                <div class="row">
                    <template class="wlm3-form-group">
                        {
                            column : 'col-12',
                            label : '<?php echo esc_js(__('Add To', 'wishlist-member')); ?>',
                            tooltip : '<?php echo esc_js(__('Adds the User to the selected Level(s) when the WebHook is applied.', 'wishlist-member')); ?>',
                            type : 'select',
                            multiple : true,
                            grouped: true,
                            options : all_levels_select_options,
                            'data-allow-clear' : 1,
                            'data-placeholder' : '<?php echo esc_js(__('Select Access', 'wishlist-member')); ?>',
                            style : 'width: 100%',
                            'data-name' : '[actions][add]',
                        }
                    </template>
                    <template class="wlm3-form-group">
                        {
                            column : 'col-12',
                            label : '<?php echo esc_js(__('Remove From', 'wishlist-member')); ?>',
                            tooltip : '<?php echo esc_js(__('Removes the User from the selected Level(s) when the WebHook is applied.', 'wishlist-member')); ?>',
                            type : 'select',
                            multiple : true,
                            grouped: true,
                            options : all_levels_select_options,
                            'data-allow-clear' : 1,
                            'data-placeholder' : '<?php echo esc_js(__('Select Access', 'wishlist-member')); ?>',
                            style : 'width: 100%',
                            'data-name' : '[actions][remove]',
                        }
                    </template>
                    <template class="wlm3-form-group">
                        {
                            column : 'col-12',
                            label : '<?php echo esc_js(__('Cancel From', 'wishlist-member')); ?>',
                            tooltip : '<?php echo esc_js(__('Cancels the User from the selected Level(s) when the WebHook is applied.', 'wishlist-member')); ?>',
                            type : 'select',
                            multiple : true,
                            options : all_levels_select_options[0].options,
                            'data-allow-clear' : 1,
                            'data-placeholder' : '<?php echo esc_js(__('Select Membership Levels', 'wishlist-member')); ?>',
                            style : 'width: 100%',
                            'data-name' : '[actions][cancel]',
                        }
                    </template>
                    <template class="wlm3-form-group">
                        {
                            column : 'col-12',
                            label : '<?php echo esc_js(__('Uncancel From', 'wishlist-member')); ?>',
                            tooltip : '<?php echo esc_js(__('Uncancels the User from the selected Level(s) when the WebHook is applied.', 'wishlist-member')); ?>',
                            type : 'select',
                            multiple : true,
                            options : all_levels_select_options[0].options,
                            'data-allow-clear' : 1,
                            'data-placeholder' : '<?php echo esc_js(__('Select Membership Levels', 'wishlist-member')); ?>',
                            style : 'width: 100%',
                            'data-name' : '[actions][uncancel]',
                        }
                    </template>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane" id="incoming-modal-datamapping">
                <div class="row">
                    <template class="wlm3-form-group">
                        {
                            column : 'col-md-6',
                            label  : '<?php echo esc_js(__('Email Address', 'wishlist-member')); ?>',
                            placeholder : 'Default: email',
                            'data-name' : '[map][email]',
                        }
                    </template>
                    <div class="col-12">
                        <h3><?php esc_html_e('Optional Fields', 'wishlist-member'); ?></h3>
                        <br>
                    </div>
                    <template class="wlm3-form-group">
                        {
                            column : 'col-md-6',
                            label  : '<?php echo esc_js(__('Username', 'wishlist-member')); ?>',
                            placeholder : 'Default: username',
                            'data-name' : '[map][username]',
                            tooltip : '<?php echo esc_js(__('The Username Format will be used if no username is provided', 'wishlist-member')); ?>',
                            help_block : '<a href="#" class="toggle-username-format"><?php echo esc_js(__('Edit Username Format', 'wishlist-member')); ?></a>'
                        }
                    </template>
                    <template class="wlm3-form-group">
                        {
                            column : 'col-md-6',
                            label  : '<?php echo esc_js(__('Password', 'wishlist-member')); ?>',
                            placeholder : 'Default: password',
                            'data-name' : '[map][password]',
                            tooltip : '<?php echo esc_js(__('The password will be auto-generated if no password is provided', 'wishlist-member')); ?>'
                        }
                    </template>
                    <template class="wlm3-form-group">
                        {
                            column : 'col-md-12 username-format',
                            label  : '<?php echo esc_js(__('Username Format', 'wishlist-member')); ?>',
                            placeholder : '{email}',
                            'data-name' : '[username_format]',
                            tooltip : '<?php echo esc_js(__('Duplicate usernames will automatically be appended with an incrementing number (ex. username-1, username-2)', 'wishlist-member')); ?>',
                            help_block : 'Shortcodes: {name}, {fname}, {lname}, {email}, {rand_ltr 3}, {rand_num 3}, {rand_mix 3}',
                        }
                    </template>
                    <template class="wlm3-form-group">
                        {
                            column : 'col-md-6',
                            label  : '<?php echo esc_js(__('First Name', 'wishlist-member')); ?>',
                            placeholder : 'Default: firstname',
                            'data-name' : '[map][firstname]',
                        }
                    </template>
                    <template class="wlm3-form-group">
                        {
                            column : 'col-md-6',
                            label  : '<?php echo esc_js(__('Last Name', 'wishlist-member')); ?>',
                            placeholder : 'Default: lastname',
                            'data-name' : '[map][lastname]',
                        }
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>
