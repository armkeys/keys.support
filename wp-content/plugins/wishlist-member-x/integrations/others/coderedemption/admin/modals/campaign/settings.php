<div class="tab-pane" id="coderedemption-campaign-modal-settings">
  <div class="row">
    <template class="wlm3-form-group">
      {
        label : '<?php echo esc_js(__('Name', 'wishlist-member')); ?>',
        tooltip : '<?php echo esc_js(__('Set or edit the Name of the Campaign.', 'wishlist-member')); ?>',
        type : 'text',
        name : 'name',
        column : 'col',
        placeholder: '<?php echo esc_js(__('Enter campaign name', 'wishlist-member')); ?>'
      }
    </template>
    <template class="wlm3-form-group">
      {
        label : '<?php echo esc_js(__('Active', 'wishlist-member')); ?>',
        type : 'toggle-switch',
        name : 'status',
        column : 'col-auto mt-4 pt-1',
        checked: true,
        value : 1,
        uncheck_value: 0,
      }
    </template>
    <template class="wlm3-form-group">
      {
        label : '<?php echo esc_js(__('Description', 'wishlist-member')); ?>',
        tooltip : '<?php echo esc_js(__('Set or edit the Description for the Campaign.', 'wishlist-member')); ?>',
        type : 'textarea',
        name : 'description',
        column : 'col-12',
      }
    </template>
  </div>
  <div class="row edit-only">
    <div class="col">
      <p>
        <span class="coderedemption-code-total"></span> <a onclick="$('.generate-code').click()" href="#"><?php esc_html_e('Add Codes', 'wishlist-member'); ?></a><br>
        <span class="coderedemption-code-stats"></span>
      </p>
    </div>
  </div>
</div>
