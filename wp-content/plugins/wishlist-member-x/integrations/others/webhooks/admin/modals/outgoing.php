<div
    data-process="modal"
    id="webhooks-outgoing-modal-template"
    data-id="webhooks-outgoing-modal"
    data-label="webhooks-outgoing-modal"
    data-title="Editing WebHook URLs for"
    data-classes="modal-lg"
    data-show-default-footer="1"
    style="display:none">
    <div class="body">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item" role="presentation"><a class="nav-link active" href="#outgoing-modal-add" role="tab" data-toggle="tab"><?php esc_html_e('Added', 'wishlist-member'); ?></a></li>
            <li class="nav-item" role="presentation"><a class="nav-link" href="#outgoing-modal-remove" role="tab" data-toggle="tab"><?php esc_html_e('Removed', 'wishlist-member'); ?></a></li>
            <li class="nav-item levels-only" role="presentation"><a class="nav-link" href="#outgoing-modal-cancel" role="tab" data-toggle="tab"><?php esc_html_e('Cancelled', 'wishlist-member'); ?></a></li>
            <li class="nav-item levels-only" role="presentation"><a class="nav-link" href="#outgoing-modal-uncancel" role="tab" data-toggle="tab"><?php esc_html_e('Uncancelled', 'wishlist-member'); ?></a></li>
            <li class="nav-item levels-only" role="presentation"><a class="nav-link" href="#outgoing-modal-expire" role="tab" data-toggle="tab"><?php esc_html_e('Expired', 'wishlist-member'); ?></a></li>
            <li class="nav-item levels-only" role="presentation"><a class="nav-link" href="#outgoing-modal-unexpire" role="tab" data-toggle="tab"><?php esc_html_e('Unexpired', 'wishlist-member'); ?></a></li>
        </ul>
        <div class="tab-content">
            <div role="tabpanel" class="tab-pane active" id="outgoing-modal-add">
                <div class="row">
                    <template class="wlm3-form-group">
                        {
                            column : 'col-12',
                            label : '<?php echo esc_js(__('Outgoing WebHook URLs', 'wishlist-member')); ?>',
                            type : 'textarea',
                            placeholder : 'https://...',
                            tooltip : '<?php echo esc_js(__('Enter one URL per line', 'wishlist-member')); ?>',
                            name : 'webhooks_settings[outgoing][][add]'
                        }
                    </template>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane" id="outgoing-modal-remove">
                <div class="row">
                    <template class="wlm3-form-group">
                        {
                            column : 'col-12',
                            label : '<?php echo esc_js(__('Outgoing WebHook URLs', 'wishlist-member')); ?>',
                            type : 'textarea',
                            placeholder : 'https://...',
                            tooltip : '<?php echo esc_js(__('Enter one URL per line', 'wishlist-member')); ?>',
                            name : 'webhooks_settings[outgoing][][remove]'
                        }
                    </template>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane" id="outgoing-modal-cancel">
                <div class="row">
                    <template class="wlm3-form-group">
                        {
                            column : 'col-12',
                            label : '<?php echo esc_js(__('Outgoing WebHook URLs', 'wishlist-member')); ?>',
                            type : 'textarea',
                            placeholder : 'https://...',
                            tooltip : '<?php echo esc_js(__('Enter one URL per line', 'wishlist-member')); ?>',
                            name : 'webhooks_settings[outgoing][][cancel]'
                        }
                    </template>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane" id="outgoing-modal-uncancel">
                <div class="row">
                    <template class="wlm3-form-group">
                        {
                            column : 'col-12',
                            label : '<?php echo esc_js(__('Outgoing WebHook URLs', 'wishlist-member')); ?>',
                            type : 'textarea',
                            placeholder : 'https://...',
                            tooltip : '<?php echo esc_js(__('Enter one URL per line', 'wishlist-member')); ?>',
                            name : 'webhooks_settings[outgoing][][uncancel]'
                        }
                    </template>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane" id="outgoing-modal-expire">
                <div class="row">
                    <template class="wlm3-form-group">
                        {
                            column : 'col-12',
                            label : '<?php echo esc_js(__('Outgoing WebHook URLs', 'wishlist-member')); ?>',
                            type : 'textarea',
                            placeholder : 'https://...',
                            tooltip : '<?php echo esc_js(__('Enter one URL per line', 'wishlist-member')); ?>',
                            name : 'webhooks_settings[outgoing][][expire]'
                        }
                    </template>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane" id="outgoing-modal-unexpire">
                <div class="row">
                    <template class="wlm3-form-group">
                        {
                            column : 'col-12',
                            label : '<?php echo esc_js(__('Outgoing WebHook URLs', 'wishlist-member')); ?>',
                            type : 'textarea',
                            placeholder : 'https://...',
                            tooltip : '<?php echo esc_js(__('Enter one URL per line', 'wishlist-member')); ?>',
                            name : 'webhooks_settings[outgoing][][unexpire]'
                        }
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>
