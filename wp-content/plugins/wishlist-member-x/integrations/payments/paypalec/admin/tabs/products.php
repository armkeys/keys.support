<div class="table-wrapper -no-shadow">
    <table class="table table-striped" id="paypalec-products" style="border-top:none">
        <tbody></tbody>
        <thead>
            <tr>
                <th width="20%"><?php esc_html_e('Product Name', 'wishlist-member'); ?></th>
                <th>
                    <?php esc_html_e('Access', 'wishlist-member'); ?>
                    <?php wishlistmember_instance()->tooltip(__('The Membership Level, Pay Per Post, Course or Team Plan the users can access after they purchase.', 'wishlist-member'), 'lg'); ?>
                </th>
                <th width="60px"></th>
                <th width="140px" class="text-center">
                    <?php esc_html_e('Subscription', 'wishlist-member'); ?>
                    <?php wishlistmember_instance()->tooltip(__('The Product can include a recurring Subscription payment (Yes or No) ', 'wishlist-member'), 'lg'); ?>
                </th>
                <th width="100px" class="text-center">
                    <?php esc_html_e('Currency', 'wishlist-member'); ?>
                    <?php wishlistmember_instance()->tooltip(__('The Product payment will be charged in the set Currency. ', 'wishlist-member'), 'lg'); ?>
                </th>
                <th width="100px" class="text-center">
                    <?php esc_html_e('Amount', 'wishlist-member'); ?>
                    <?php wishlistmember_instance()->tooltip(__('The Product Payment Amount (based on the set Currency).', 'wishlist-member'), 'lg'); ?>
                </th>
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
<div class="notice notice-warning mb-3">
    <p>
    <?php
    printf(
        wp_kses(
            __('A PayPal purchase button for any created product can be inserted into a WordPress page or post by using the blue WishList Member code inserter available in the Classic Block when using the page/post editor. More details are available in the <a href="%s" target="_blank">PayPal Checkout Knowledge base entry.</a>', 'wishlist-member'),
            [
                'a' => [
                    'href'   => [],
                    'target' => [],
                ],
            ]
        ),
        'https://wishlistmember.com/docs/paypal-checkout/'
    );
    ?>
    </p>
</div>

<div id="paypalec-products-edit"></div>
<script type="text/template" id="paypalec-products-template">
    {% _.each(data, function(product, id) { %}
    {% if(!('name' in product)) return; %}
    {% if('new_product' in product) return; %}
    <tr class="button-hover" data-id="{%= id %}">
        <td>{%= product.name %}</td>
        <td>{%= all_levels_flat[product.sku] ? all_levels_flat[product.sku].name : '' %}</td>
        <td class="text-right">
            <a href="" class="wlm-popover clipboard tight btn wlm-icons md-24 -icon-only -link-btn" title="Copy Product Payment Link" alt="Click for Product Payment Link" data-text="{%= WLM3ThirdPartyIntegration.paypalec.paypalecthankyou_url %}?action=purchase-express&id={%= id %}&t=<?php echo rawurlencode(time()); ?>"><span>link</span></a>
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
<script type="text/template" id="paypalec-products-edit-template">
{% _.each(data, function(product, id) { %}
<div
    id="paypalec_edit_product_{%= id %}-template"
    data-id="paypalec_edit_product_{%= id %}"
    data-label="paypalec_edit_product_{%= id %}"
    data-title="Editing {%= product.name %}"
    data-show-default-footer="1"
    data-classes="modal-lg paypalec-edit-product"
    data-process="modal"
    style="display:none">
    <div class="body">
        <input type="hidden" name="paypalecproducts[{%= id %}][id]" value="{%= id %}">
        <div class="paypalec-product-form">
            <div class="row">
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Product Name', 'wishlist-member')); ?>',
                        name : 'paypalecproducts[{%= id %}][name]',
                        column : 'col-6',
                        tooltip : '<p><?php echo esc_js(__('The default Product Name can be used or it can be changed to something you prefer.', 'wishlist-member')); ?></p>',
                        tooltip_size : 'lg',
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
                        name : 'paypalecproducts[{%= id %}][sku]',
                        column : 'col-6',
                        tooltip : '<p><?php echo esc_js(__('The Membership Level, Pay Per Post, Course or Team Plan the users can access after they purchase.', 'wishlist-member')); ?></p>',
                        tooltip_size : 'lg',
                    }
                </template>
            </div>
            <div class="row">
                <div class="col-3 pt-1" style="white-space: nowrap;">
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('One-Time Payment', 'wishlist-member')); ?>',
                            name : 'paypalecproducts[{%= id %}][recurring]',
                            value : 0,
                            type : 'radio',
                            class : '-paypal-recurring-toggle',
                        }
                    </template>
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Subscription', 'wishlist-member')); ?>',
                            name : 'paypalecproducts[{%= id %}][recurring]',
                            value : 1,
                            type : 'radio',
                            class : '-paypal-recurring-toggle',
                        }
                    </template>
                </div>
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Amount', 'wishlist-member')); ?>',
                        name : 'paypalecproducts[{%= id %}][amount]',
                        column : 'col-3 -paypalec-onetime',
                        tooltip : '<p><?php echo esc_js(__('The Product Payment Amount (based on the set Currency).', 'wishlist-member')); ?></p>',
                        tooltip_size : 'lg',
                    }
                </template>
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Amount', 'wishlist-member')); ?>',
                        name : 'paypalecproducts[{%= id %}][recur_amount]',
                        column : 'col-3 -paypalec-recurring',
                        tooltip : '<p><?php echo esc_js(__('The Product Payment Amount (based on the set Currency).', 'wishlist-member')); ?></p>',
                        tooltip_size : 'lg',
                    }
                </template>
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Currency', 'wishlist-member')); ?>',
                        name : 'paypalecproducts[{%= id %}][currency]',
                        type : 'select',
                        style : 'width: 100%',
                        options : paypal_common.pp_currencies,
                        column : 'col-3',
                        tooltip : '<p><?php echo esc_js(__('The Product Payment will be charged in the set Currency.', 'wishlist-member')); ?></p>',
                        tooltip_size : 'lg',
                    }
                </template>
            </div>
            <div class="row">
                <template class="wlm3-form-group">
                    {
                        label : '<span style="white-space: nowrap;"><?php esc_html_e('Billing Cycle', 'wishlist-member'); ?></span>',
                        name : 'paypalecproducts[{%= id %}][recur_billing_frequency]',
                        column : 'offset-3 col-1 no-padding-right -paypalec-recurring',
                    }
                </template>
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', 'wishlist-member')); ?>',
                        name : 'paypalecproducts[{%= id %}][recur_billing_period]',
                        type : 'select',
                        style : 'width: 100%',
                        options : paypal_common.pp_billing_cycle,
                        column : 'col-2 no-padding-left -paypalec-recurring',
                        tooltip : '<p><?php echo esc_js(__('The Billing Cycle can be set in Days, Weeks, Months or Years.', 'wishlist-member')); ?></p>',
                        tooltip_size : 'lg',
                    }
                </template>
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Stop After', 'wishlist-member')); ?>',
                        name : 'paypalecproducts[{%= id %}][recur_billing_cycles]',
                        type : 'select',
                        style : 'width: 100%',
                        options : paypal_common.pp_stop_after,
                        column : 'col-3 -paypalec-recurring',
                        tooltip : '<p><?php echo esc_js(__('Set when to stop the Subscription after the set number of Billing Cycles. This can be set to 1 Cycle and up to 52 Cycles or Never (ongoing subscription).', 'wishlist-member')); ?></p>',
                        tooltip_size : 'lg',
                    }
                </template>
                <template class="wlm3-form-group">
                    {
                        label : '<span style="white-space:nowrap"><?php esc_html_e('Max Failed Attempts', 'wishlist-member'); ?></span>',
                        name : 'paypalecproducts[{%= id %}][max_failed_payments]',
                        options : paypal_common.pp_billing_cycle,
                        column : 'col-3 -paypalec-recurring',
                        tooltip : '<p><?php echo esc_js(__('Set the Maximum number of Attempts to process a payment after it Fails. The default number is 3 but this can be set to what you prefer.', 'wishlist-member')); ?></p>',
                        tooltip_size : 'lg',
                    }
                </template>
            </div>
            <div class="row">
                <div class="col-3 pt-1 -paypalec-recurring">
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Trial Period', 'wishlist-member')); ?>',
                            name : 'paypalecproducts[{%= id %}][trial]',
                            value : 1,
                            uncheck_value : 0,
                            type : 'checkbox',
                            class : '-paypal-trial1-toggle',
                            tooltip : '<p><?php echo esc_js(__('If enabled, a Trial Period can be set to allow access for a set payment amount and set period of time.', 'wishlist-member')); ?></p>',
                            tooltip_size : 'lg',
                        }
                    </template>
                </div>
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Trial Amount', 'wishlist-member')); ?>',
                        name : 'paypalecproducts[{%= id %}][trial_amount]',
                        column : 'col-3 -paypalec-trial',
                        tooltip : '<p><?php echo esc_js(__('Set the Payment Amount for the Trial Period. The user will pay this price for the Trial.', 'wishlist-member')); ?></p>',
                        tooltip_size : 'lg',
                    }
                </template>
                <template class="wlm3-form-group">
                    {
                        label : '<span style="white-space: nowrap;"><?php esc_html_e('Trial Duration', 'wishlist-member'); ?></span>',
                        name : 'paypalecproducts[{%= id %}][trial_recur_billing_frequency]',
                        column : 'col-1 no-padding-right -paypalec-trial',
                        tooltip : '<p><?php echo esc_js(__(' Set the length of time for the Trial Period in Days, Weeks, Months or Years.', 'wishlist-member')); ?></p>',
                        tooltip_size : 'lg',
                    }
                </template>
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('&nbsp;', 'wishlist-member')); ?>',
                        name : 'paypalecproducts[{%= id %}][trial_recur_billing_period]',
                        type : 'select',
                        style : 'width: 100%',
                        options : paypal_common.pp_billing_cycle,
                        column : 'col-2 no-padding-left -paypalec-trial',
                    }
                </template>
            </div>
            <div class="row">
                <div class="col-12 pt-3">
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Ask for Shipping Address', 'wishlist-member')); ?>',
                            name : 'paypalecproducts[{%= id %}][shipping]',
                            value : 1,
                            uncheck_value : 0,
                            type : 'checkbox',
                            tooltip : '<p><?php echo esc_js(__('If enabled, the user will be asked to enter their Shipping Address during the purchase/registration process. This is optional and can be ignored if you do not require a Shipping Address from those who register.', 'wishlist-member')); ?></p>',
                            tooltip_size : 'lg',
                        }
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>
{% }) %}
</script>
