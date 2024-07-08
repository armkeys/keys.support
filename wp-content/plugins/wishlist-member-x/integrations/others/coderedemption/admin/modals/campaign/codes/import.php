<div role="tabpanel" class="tab-pane" id="coderedemption-campaign-modal-codes-import">
  <div class="row">
    <template class="wlm3-form-group">
      {
        type: 'file',
        label: '<?php echo esc_js(__('CSV File', 'wishlist-member')); ?>',
        tooltip : '<?php echo esc_js(__('A CSV File containing Codes can be imported. Browse and locate the file on your computer to Import the Codes.', 'wishlist-member')); ?>',
        column: 'col-12',
        id: 'coderedemption-code-import-file',
      }
    </template>
    <template class="wlm3-form-group">
      {
        type: 'select',
        id: 'coderedemption-code-import-option',
        label: '<?php echo esc_js(__('Import Option', 'wishlist-member')); ?>',
        tooltip : '<?php echo esc_js(__('Select the desired option if there are duplicate Codes encountered during the Import. There is an option to delete and replace all existing Codes.', 'wishlist-member')); ?>',
        options: [
          {value: 'skip', text: '<?php echo esc_js(__('Do not import duplicate codes', 'wishlist-member')); ?>'},
          {value: 'update', text: '<?php echo esc_js(__('Update status of duplicate codes', 'wishlist-member')); ?>'},
          {value: 'replace', text: '<?php echo esc_js(__('Delete and replace all Codes', 'wishlist-member')); ?>'},
        ],
        style: 'width: 100%',
        column: 'col-md-6'
      }
    </template>
    <div class="col-12 pt-0">
      <button id="coderedemption-code-import-button" type="button" class="btn -default -condensed" type="button"><?php esc_html_e('Import', 'wishlist-member'); ?></button>
    </div>
  </div>
</div>
