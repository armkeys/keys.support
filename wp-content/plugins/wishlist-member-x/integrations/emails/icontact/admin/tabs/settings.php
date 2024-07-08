<div class="row">
    <div class="col-auto mb-4"><?php echo wp_kses_post($config_button); ?></div>
    <?php echo wp_kses_post($api_status_markup); ?>       
</div>
<div class="row axpi-required">
    <template class="wlm3-form-group">
        {
            label : '<?php echo esc_js(__('Account ID', 'wishlist-member')); ?>',
            type : 'text',
            readonly : 'readonly',
            name : 'icaccountid',
            column : 'col-md-4'
        }
    </template>
    <template class="wlm3-form-group">
        {
            label : '<?php echo esc_js(__('Client Folder ID', 'wishlist-member')); ?>',
            type : 'select',
            name : 'icfolderid',
            column : 'col-auto',
            style: 'width: 100%',
            tooltip : '<?php echo esc_js(__('Displays the Client Folder ID. iContact accounts can have more than one clientFolderID. This Client Folder ID will be set by default. If your iContact account has multiple Client Folder IDs, you can select the desired ID here.', 'wishlist-member')); ?>',
            tooltip_size: 'lg'
        }
    </template>
</div>
