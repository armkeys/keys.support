<div role="tabpanel" class="tab-pane" id="coderedemption-campaign-modal-codes-manage">
  <div class="row">
    <template class="wlm3-form-group">
      {
        label : '<?php echo esc_js(__('Search', 'wishlist-member')); ?>',
        tooltip : '<?php echo esc_js(__('Enter a Full Code or a Partial Code to search for a specific Code or set of Codes.', 'wishlist-member')); ?>',
        type: 'text',
        id: 'coderedemption-code-search',
        column: 'col',
        placeholder: '<?php echo esc_js(__('Full / Partial Code', 'wishlist-member')); ?>',
      }
    </template>
    <template class="wlm3-form-group">
      {
        label : '<?php echo esc_js(__('Category', 'wishlist-member')); ?>',
        tooltip : '<?php echo esc_js(__('Select the category of Codes to be searched. All will display all results. Available will display Codes that are still Available to be used. Redeemed will display Codes that have been Redeemed. Cancelled will display Codes that have been Cancelled.', 'wishlist-member')); ?>',
        tooltip_size: 'md',
        type: 'select',
        id: 'coderedemption-code-search-status',
        column: 'col-auto px-0',
        style: 'width: 100%',
        options: [
          {value: '', text: '<?php echo esc_js(__('All', 'wishlist-member')); ?>'},
          {value: '0', text: '<?php echo esc_js(__('Available', 'wishlist-member')); ?>'},
          {value: '1', text: '<?php echo esc_js(__('Redeemed', 'wishlist-member')); ?>'},
          {value: '2', text: '<?php echo esc_js(__('Cancelled', 'wishlist-member')); ?>'},
        ]
      }
    </template>
    <div class="col-auto pt-2">
        <br>
      <button type="button" class="btn -default -condensed" id="coderedemption-code-search-button"><?php esc_html_e('Search', 'wishlist-member'); ?></button>
    </div>
  </div>
  <div id="coderedemption-code-search-results-wrapper" class="table-wrapper -no-shadow">
    <table id="coderedemption-code-search-results" class="table table-striped table-small table-borderless">
      <colgroup>
        <col>
        <col width="120">
        <col width="80">
      </colgroup>
      <tbody></tbody>
      <thead>
        <tr>
          <th><?php esc_html_e('Code', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('Displays the full Code.', 'wishlist-member')); ?></th>
          <th><?php esc_html_e('Status', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('Displays the Status of the Codes.', 'wishlist-member')); ?></th>
          <th></th>
        </tr>
      </thead>
    </table>
  </div>
  <p id="coderedemption-code-search-results-summary"></p>
</div>
