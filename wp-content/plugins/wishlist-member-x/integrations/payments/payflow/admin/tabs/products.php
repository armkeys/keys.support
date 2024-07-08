<div class="table-wrapper -no-shadow">
    <table class="table table-striped" id="payflow-products" style="border-top: none">
        <tbody></tbody>
        <thead>
            <tr>
                <th width="25%"><?php esc_html_e('Product Name', 'wishlist-member'); ?></th>
                <th><?php esc_html_e('Access', 'wishlist-member'); ?></th>
                <th width="80px"></th>
                <th width="80px" class="text-center"><?php esc_html_e('Subscription', 'wishlist-member'); ?></th>
                <th width="80px" class="text-center"><?php esc_html_e('Currency', 'wishlist-member'); ?></th>
                <th width="80px" class="text-center"><?php esc_html_e('Amount', 'wishlist-member'); ?></th>
                <th width="80px"></th>
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
<div class="notice notice-warning"><p><?php esc_html_e('The purchase button for the membership level needs to be inserted into a WordPress page/post by using the blue WishList Member code inserter located on the page/post editor.', 'wishlist-member'); ?></p></div>

<div id="payflow-products-edit"></div>
<script type="text/template" id="payflow-products-template">
    {% _.each(data, function(product, id) { %}
    {% if(!('name' in product)) return; %}
    {% if('new_product' in product) return; %}
    <tr class="button-hover" data-id="{%= id %}">
        <td>{%= product.name %}</td>
        <td>{%= all_levels_flat[product.sku] ? all_levels_flat[product.sku].name : '' %}</td>
        <td class="text-right">
            <a href="" class="wlm-popover clipboard tight btn wlm-icons md-24 -icon-only -link-btn" title="Copy Product Payment Link" alt="Click for Product Payment Link" data-text="{%= WLM3VARS.blogurl %}?ppayflow={%= id %}"><span>link</span></a>
        </td>
        <td class="text-center">{%= product.recurring == 1 ? 'YES' : 'NO' %}</td>
        <td class="text-center">{%= product.currency %}</td>
        <td class="text-center">{%= Number(product.recurring == 1 ? product.recur_amount : product.amount).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}) %}</td>
        <td class="text-right">
            <div class="btn-group-action">
                <a href="#" class="btn wlm-icons md-24 -icon-only -edit-btn"><span>edit</span></a>
                <a href="#" class="btn wlm-icons md-24 -icon-only -del-btn"><span>delete</span></a></div>
        </td>
    </tr>
    {% }) %}
</script>
<script type="text/template" id="payflow-products-edit-template">
{% _.each(data, function(product, id) { %}
<div
    id="payflow_edit_product_{%= id %}-template" 
    data-id="payflow_edit_product_{%= id %}"
    data-label="payflow_edit_product_{%= id %}"
    data-title="Editing {%= product.name %}"
    data-show-default-footer="1"
    data-classes="modal-lg payflow-edit-product"
    data-process="modal"
    style="display:none">
    <div class="body">
        <input type="hidden" name="paypalpayflowproducts[{%= id %}][id]" value="{%= id %}">
        <div class="payflow-product-form">
            <div class="row">
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Product Name', 'wishlist-member')); ?>',
                        name : 'paypalpayflowproducts[{%= id %}][name]',
                        column : 'col-6',
                    }
                </template>
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Access', 'wishlist-member')); ?>',
                        type : 'select',
                        style : 'width: 100%',
                        options : paypal_common.levels_select_group,
                        'data-placeholder' : '<?php echo esc_js(__('Select a Level', 'wishlist-member')); ?>',
                        grouped : true,
                        name : 'paypalpayflowproducts[{%= id %}][sku]',
                        column : 'col-6',
                    }
                </template>
            </div>
            <div class="row">
                <div class="col-3 pt-1" style="white-space: nowrap;">
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('One-Time Payment', 'wishlist-member')); ?>',
                            name : 'paypalpayflowproducts[{%= id %}][recurring]',
                            value : 0,
                            type : 'radio',
                            class : '-paypal-recurring-toggle',
                        }
                    </template>
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Subscription', 'wishlist-member')); ?>',
                            name : 'paypalpayflowproducts[{%= id %}][recurring]',
                            value : 1,
                            type : 'radio',
                            class : '-paypal-recurring-toggle',
                        }
                    </template>
                </div>
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Amount', 'wishlist-member')); ?>',
                        name : 'paypalpayflowproducts[{%= id %}][amount]',
                        column : 'col-3 -payflow-onetime',
                    }
                </template>
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Amount', 'wishlist-member')); ?>',
                        name : 'paypalpayflowproducts[{%= id %}][recur_amount]',
                        column : 'col-3 -payflow-recurring',
                    }
                </template>
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Currency', 'wishlist-member')); ?>',
                        name : 'paypalpayflowproducts[{%= id %}][currency]',
                        type : 'select',
                        style : 'width: 100%',
                        options : paypal_common.pp_currencies,
                        column : 'col-3',
                    }
                </template>
            </div>
            <div class="row">
                <template class="wlm3-form-group">
                    {
                        label : '<span style="white-space: nowrap;"><?php esc_html_e('Pay Period', 'wishlist-member'); ?></span>',
                        name : 'paypalpayflowproducts[{%= id %}][recur_billing_frequency]',
                        column : 'offset-3 col-1 no-padding-right -payflow-recurring -payflow_recur_billing_frequency',
                    }
                </template>
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('&nbsp;', 'wishlist-member')); ?>',
                        name : 'paypalpayflowproducts[{%= id %}][payflow_recur_pay_period]',
                        type : 'select',
                        style : 'width: 100%',
                        options : paypal_common.payflow_billing_cycle,
                        column : 'col-2 no-padding-left -payflow-recurring -payflow_recur_pay_period',
                    }
                </template>
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Stop After', 'wishlist-member')); ?>',
                        name : 'paypalpayflowproducts[{%= id %}][recur_billing_cycles]',
                        type : 'select',
                        style : 'width: 100%',
                        options : paypal_common.pp_stop_after,
                        column : 'col-2 -payflow-recurring',
                    }
                </template>
            </div>
        </div>
    </div>
</div>
{% }) %}
</script>
