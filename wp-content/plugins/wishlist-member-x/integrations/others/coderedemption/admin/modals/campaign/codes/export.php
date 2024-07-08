<div role="tabpanel" class="tab-pane" id="coderedemption-campaign-modal-codes-export">
  <div class="row">
    <div class="col-12">
      <label>
        <?php esc_html_e('Choose which codes to export', 'wishlist-member; ?>'); ?>
        <?php wishlistmember_instance()->tooltip(__('Select which Codes to Export based on the Code Status.', 'wishlist-member')); ?>
      </label>
    </div>
    <template class="wlm3-form-group">
      {
        type: 'select',
        id: 'coderedemption-code-export-status',
        options: [
          {value : '', text : '<?php echo esc_js(__('All', 'wishlist-member')); ?>'},
          {value : '0', text : '<?php echo esc_js(__('Available', 'wishlist-member')); ?>'},
          {value : '1', text : '<?php echo esc_js(__('Redeemed', 'wishlist-member')); ?>'},
          {value : '2', text : '<?php echo esc_js(__('Cancelled', 'wishlist-member')); ?>'},
        ],
        column: 'col-auto'
      }
    </template>
    <div class="col-auto pl-0">
      <button id="coderedemption-code-export-button" type="button" class="btn -default -condensed"><?php esc_html_e('Export', 'wishlist-member'); ?></button>
    </div>
  </div>
</div>
