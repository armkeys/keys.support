<style media="screen">
  /* code stack numbering */
  table#code-redemption-code-actions {
    counter-reset: rowNumber;
  }
  table#code-redemption-code-actions tbody tr:not(.ui-sortable-placeholder) td:first-child::before {
    counter-increment: rowNumber;
    content: counter(rowNumber);
  }
</style>
<div class="tab-pane" id="coderedemption-campaign-modal-actions">
  <div class="row">
    <div class="col-12">
      <p><?php esc_html_e('A Campaign requires at least one Action. Additional Actions can be set to allow for multiple code redemptions by a single user. This is often referred to as "code stacking". The first code will redeem the first Action in the stack. If a second code is set, it would redeem the second Action in the stack for that User and so on. Drag and drop to reorder.', 'wishlist-member'); ?></p>
    </div>
  </div>
  <div class="table-wrapper -no-shadow">
    <table id="code-redemption-code-actions" class="table table-striped">
      <colgroup>
        <col width="75">
        <col width="130">
        <col>
        <col width="70">
      </colgroup>
      <tbody id="actions-tbody">
        <tr class="button-hover">
          <td class="text-center"></td>
          <td>
            <template class="wlm3-form-group">
              {
                type: 'select',
                name: 'access[0][action]',
                class: 'access-action',
                options: [
                  {value : 'add', text : '<?php echo esc_js(__('Add', 'wishlist-member')); ?>'},
                  {value : 'move', text : '<?php echo esc_js(__('Move', 'wishlist-member')); ?>'},
                ],
                style: 'width: 100%',
              }
            </template>
          </td>
          <td>
            <template class="wlm3-form-group">
              {
                type: 'select',
                name: 'access[0][levels]',
                class: 'access-levels',
                id : 'coderedemption-access-levels',
                grouped: true,
                multiple: true,
                options: all_levels_select_options,
                style: 'width: 100%',
                'data-placeholder': '<?php echo esc_js(__('Select access...', 'wishlist-member')); ?>'
              }
            </template>
          </td>
          <td>
            <div class="btn-group-action text-right">
              <a href="#" title="<?php esc_attr_e('Delete Action', 'wishlist-member'); ?>" class="btn -icon-only -action-del-btn"><i class="wlm-icons md-24" title="<?php esc_attr_e('Delete Action', 'wishlist-member'); ?>">delete</i></a>
              <a href="#" style="cursor: move" title="Move Membership Level" class="btn -icon-only handle ui-sortable-handle"><i class="wlm-icons md-24">swap_vert</i></a>
            </div>
          </td>
        </tr>
      </tbody>
      <thead>
        <tr>
          <th><?php esc_html_e('Stack', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('Displays the order of the Stacked Codes. The first Stacked Code can be redeemed by the user first. If a second Stacked Code is available, it can be redeemed by the user second, and so on for any other Stacked Codes. Stack #1 applies the set Action for the selected Level(s), Stack #2 applies the set Action for the selected Level(s), etc.', 'wishlist-member')); ?></th>
          <th><?php esc_html_e('Action', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('Select the desired Action to be applied to the Level(s).', 'wishlist-member')); ?></th>
          <th><?php esc_html_e('Access', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('Select the desired Level(s) to be applied when the Code is redeemed.', 'wishlist-member')); ?></th>
          <th></th>
        </tr>
      </thead>
      <tfoot>
        <tr>
          <td colspan="10"><button class="btn -success -condensed" id="add-action"><i class="wlm-icons">add</i><?php esc_html_e('Add Action', 'wishlist-member'); ?></button></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>
