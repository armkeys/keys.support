<div class="table-wrapper">
    <table id="clickbank-products" class="table table-striped">
        <colgroup>
            <col width="100">
            <col>
            <col width="35%">
            <col width="80">
        </colgroup>
        <tbody></tbody>
        <thead>
            <tr>
                <th class="text-center"><?php esc_html_e('Item ID', 'wishlist-member'); ?></th>
                <th><?php esc_html_e('Access', 'wishlist-member'); ?></th>
                <th><?php esc_html_e('Payment Link', 'wishlist-member'); ?></th>
                <th></th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <td class="pt-3" colspan="7">
                    <p><?php esc_html_e('No products found.', 'wishlist-member'); ?></p>
                </td>
            </tr>
            <tr>
                <td class="pt-3 text-center" colspan="7">
                    <a href="#" class="btn -success -add-btn -condensed">
                        <i class="wlm-icons">add</i>
                        <span><?php esc_html_e('Add New Product', 'wishlist-member'); ?></span>
                    </a>
                </td>
            </tr>
        </tfoot>
    </table>
</div>

<script type="text/template" id="clickbank-products-template2">
    {% _.each(data.products, function(products, level) { %}
    {% if(!(level in all_levels_flat)) return; %}
    {% _.each(products, function(product_id) { %}
    <tr class="button-hover" data-access="{%- level %}" data-id="{%- product_id %}">
        <td class="text-center"><a href="#" class="-edit-btn">{%- product_id %}</a></td>
        <td>{%- all_levels_flat[level].name %}</td>
        <td><input type="text" readonly class="copyable form-control" value="http://{%- product_id %}.{%- WLM3ThirdPartyIntegration.clickbank.cbvendor %}.pay.clickbank.net"></td>
        <td class="text-right">
            <div class="btn-group-action">
                <a href="#" class="btn -edit-btn" title="Edit"><i class="wlm-icons md-24">edit</i></a>
                <a href="#" class="btn wlm-icons md-24 -icon-only -del-btn"><span>delete</span></a>
            </div>
            <div class="btn-group-action">
            </div>
        </td>
    </tr>
    {% }); %}
    {% }); %}
</script>

<div
    data-process="modal"
    id="products-<?php echo esc_attr($config['id']); ?>-template" 
    data-id="products-<?php echo esc_attr($config['id']); ?>"
    data-label="products-<?php echo esc_attr($config['id']); ?>"
    data-title="<?php echo esc_attr($config['name']); ?> Product for <span></span>"
    data-show-default-footer="1"
    style="display:none">
    <div class="body">
        <input type="hidden" name="action" value="wlm3_save_clickbank_product">
        <input type="hidden" name="old_access" value="">
        <input type="hidden" name="old_id" value="">
        <div class="row">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Access', 'wishlist-member')); ?>',
                    type : 'select',
                    name : 'access',
                    grouped : 1,
                    options : all_levels_select_options,
                    column : 'col-12',
                    style : 'width: 100%',
                }
            </template>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Clickbank Product ID', 'wishlist-member')); ?>',
                    type : 'text',
                    name : 'id',
                    column : 'col-6',
                }
            </template>
        </div>
    </div>
</div>


