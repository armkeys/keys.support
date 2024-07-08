<div class="table-wrapper -no-shadow">
    <table id="authorizenet-arb-products-table" class="table table-striped" style="border-top: none">
        <colgroup>
            <col width="25%">
            <col>
            <col width="150">
            <col width="100">
            <col width="150">
            <col width="80">
        </colgroup>
        <tbody/>
        <thead>
            <tr>
                <th>
                    <?php esc_html_e('Name', 'wishlist-member'); ?>
                    <?php wishlistmember_instance()->tooltip(__('The name of the product.', 'wishlist-member')); ?>
                </th>
                <th>
                    <?php esc_html_e('Access', 'wishlist-member'); ?>
                    <?php wishlistmember_instance()->tooltip(__('The level assigned to the product.', 'wishlist-member')); ?>
                </th>
                <th class="text-center">
                    <?php esc_html_e('Subscription', 'wishlist-member'); ?>
                    <?php wishlistmember_instance()->tooltip(__('The subscription length.', 'wishlist-member')); ?>
                </th>
                <th class="text-center">
                    <?php esc_html_e('Currency', 'wishlist-member'); ?>
                    <?php wishlistmember_instance()->tooltip(__('The currency type.', 'wishlist-member')); ?>
                </th>
                <th class="text-center">
                    <?php esc_html_e('Amount', 'wishlist-member'); ?>
                    <?php wishlistmember_instance()->tooltip(__('The set payment amount.', 'wishlist-member')); ?>
                </th>
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
    <br>
    <div style="-moz-border-radius: 6px;
     -webkit-border-radius: 6px;
     background-color: #f0f7fb;
     border: solid 1px #3498db;
     border-radius: 6px;
     line-height: 18px;
     overflow: hidden;
     padding: 8px 8px;">
        <small class="form-text text-muted">
                <b><?php esc_html_e('Additional Info on Recurring Billing:', 'wishlist-member'); ?></b><br><br>
                <?php esc_html_e('The integration charges a one cent ($0.01) payment to the card when a subscription is created in order to ensure the credit card is valid. That payment is immediately refunded once the card is validated successfully.', 'wishlist-member'); ?><br><br>
                <?php esc_html_e('Please Note: Creating a subscription transaction successfully does not guarantee subscription payments will process through your Authorize.net account successfully as Authorize.net processes all the actual payments at a set time every day.', 'wishlist-member'); ?><br><br>
                <?php esc_html_e('All the details on this integration can be found in this ', 'wishlist-member'); ?>
                <a href="https://wishlistmember.com/docs/authorizenet-arb/" target="blank">Knowledge Base article.</a>
        </small>
    </div>
</div>
<script type="text/template" id="authorizenet-arb-products-table-template">
{% _.each(data.subscriptions, function(ss) { %}
{% if(ss.sku in all_levels_flat) { %}
<tr class="button-hover" data-id="{%- ss.id %}">
    <td>{%- ss.name  %}</td>
    <td>{%- all_levels_flat[ss.sku].name %}</td>
    <td class="text-center">{%- ss.recurring == '1' ? 'Every ' + ss.recur_billing_frequency + ' ' +  ss.recur_billing_period + (ss.recur_billing_frequency > 1 ? 's' : '') : 'NO' %}</td>
    <td class="text-center">{%- ss.currency  %}</td>
    <td class="text-center">
        {% if (ss.recurring == '1') { %}
            {% if (ss.trial_billing_cycle > 0 && ss.trial_amount > 0) { %}
                {%- Number(ss.trial_amount).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}) %} Trial
                for
                {%- ss.trial_billing_cycle %}
                Cycle{%- ss.trial_billing_cycle > 1 ? 's' : '' %}
                <br>THEN<br>
            {% } %}
            {%- Number(ss.recur_amount).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}) %}
            for
            {%- ss.recur_billing_cycle > 0 ? ss.recur_billing_cycle : 'Unlimited' %}
            Cycle{%- ss.recur_billing_cycle != 1 ? 's' : '' %}

        {% } else { %}
            {%- Number(ss.amount).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}) %}
        {% } %}
    </td>
    <td class="text-right">
        <div class="btn-group-action">
            <a href="#" class="btn wlm-icons md-24 -icon-only -edit-btn" data-toggle="modal" data-target="#authorizenet-arb-products-{%- ss.id %}"><span>edit</span></a>
            <a href="#" class="btn wlm-icons md-24 -icon-only -del-btn -do-confirm" data-confirm-message="Click again to delete."><span>delete</span></a>
        </div>
    </td>
</tr>
{% } %}
{% }); %}
</script>

<div id="authorizenet-arb-products"></div>
<script type="text/template" id="authorizenet-arb-products-template">
{% _.each(data, function(product, id) { %}
<div
    id="authorizenet-arb-products-{%- id %}-template" 
    data-id="authorizenet-arb-products-{%- id %}"
    data-label="authorizenet-arb-products-{%- id %}"
    data-title="Editing {%- product.name %}"
    data-show-default-footer="1"
    data-process="modal"
    data-classes="modal-lg"
    style="display:none">
    <div class="body">
        <input type="hidden" name="anetarbsubscriptions[{%- id %}][id]">
        <div class="row">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Name', 'wishlist-member')); ?>',
                    name : 'anetarbsubscriptions[{%- id %}][name]',
                    column : 'col-6'
                }
            </template>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Access', 'wishlist-member')); ?>',
                    type : 'select',
                    style : 'width: 100%',
                    name : 'anetarbsubscriptions[{%- id %}][sku]',
                    grouped : true,
                    options : all_levels_select_options,
                    column : 'col-6',
                }
            </template>
        </div>
        <div class="row">
            <div class="col-3 pt-1 text-nowrap">
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('One-Time Payment', 'wishlist-member')); ?>',
                        name : 'anetarbsubscriptions[{%- id %}][recurring]',
                        value : 0,
                        type : 'radio',
                        class : 'anetarb-recurring-toggle',
                    }
                </template>
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Subscription', 'wishlist-member')); ?>',
                        name : 'anetarbsubscriptions[{%- id %}][recurring]',
                        value : 1,
                        type : 'radio',
                        class : 'anetarb-recurring-toggle',
                    }
                </template>
            </div>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Amount', 'wishlist-member')); ?>',
                    name : 'anetarbsubscriptions[{%- id %}][amount]',
                    column : 'col-3 -anetarb-onetime',
                    tooltip : '<?php echo esc_js(__('One Time Payment Amount', 'wishlist-member')); ?>',
                }
            </template>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Amount', 'wishlist-member')); ?>',
                    name : 'anetarbsubscriptions[{%- id %}][recur_amount]',
                    column: 'col-3 -anetarb-recurring',
                    tooltip : '<?php echo esc_js(__('Recurring Amount', 'wishlist-member')); ?>', 
                }
            </template>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Currency', 'wishlist-member')); ?>',
                    type : 'select',
                    style : 'width: 100%',
                    name : 'anetarbsubscriptions[{%- id %}][currency]',
                    options : WLM3ThirdPartyIntegration['authorizenet-arb'].currencies,
                    column : 'col-3'
                }
            </template>
        </div>
        <div class="row -anetarb-recurring">
            <template class="wlm3-form-group">
                {
                    label : '<span class="text-nowrap"><?php esc_html_e('Charge Every', 'wishlist-member'); ?></span>',
                    name : 'anetarbsubscriptions[{%- id %}][recur_billing_frequency]',
                    column: 'offset-3 col-1 no-padding-right',
                    type : 'number',
            }
            </template>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('&nbsp;', 'wishlist-member')); ?>',
                    type : 'select',
                    style : 'width: 100%',
                    name : 'anetarbsubscriptions[{%- id %}][recur_billing_period]',
                    options : WLM3ThirdPartyIntegration['authorizenet-arb'].billing_periods,
                    column : 'col-2 no-padding-left',
                }
            </template>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Cycles', 'wishlist-member')); ?>',
                    name : 'anetarbsubscriptions[{%- id %}][recur_billing_cycle]',
                    column: 'col-3',
                    type : 'number',
                    min : '0',
                    placeholder : '<?php echo esc_js(__('Unlimited', 'wishlist-member')); ?>',
                    tooltip : '<?php echo esc_js(__('Leave blank or set to 0 for Unlimited cycles', 'wishlist-member')); ?>',
                }
            </template>
        </div>
        <div class="row -anetarb-recurring">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Trial Amount', 'wishlist-member')); ?>',
                    name : 'anetarbsubscriptions[{%- id %}][trial_amount]',
                    column: 'offset-3 col-3 -anetarb-trial',
                    tooltip : '<?php echo esc_js(__('Leave blank or set to 0 to disable trial period', 'wishlist-member')); ?>',
                }
            </template>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Cycles', 'wishlist-member')); ?>',
                    name : 'anetarbsubscriptions[{%- id %}][trial_billing_cycle]',
                    column: 'col-3 -anetarb-trial',
                    type : 'number',
                    min : '0',
                    tooltip : '<?php echo esc_js(__('Leave blank or set to 0 to disable trial period', 'wishlist-member')); ?>',
                }
            </template>
        </div>
    </div>
</div>
{% }); %}
</script>
<style type="text/css">
#authorizenet-arb-products-table tbody:not(:empty) ~ tfoot tr:first-child {
    display: none;
}
#authorizenet-arb-products-table tbody:empty ~ thead {
    display: none;
}
</style>
