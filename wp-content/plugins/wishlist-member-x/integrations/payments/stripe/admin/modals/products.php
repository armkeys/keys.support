<?php
foreach ($all_levels as $levels) :
    foreach ($levels as $level) :
        $level = (object) $level;
        ?>
<div
    data-process="modal"
    id="products-<?php echo esc_attr($config['id']); ?>-<?php echo esc_attr($level->id); ?>-template"
    data-id="products-<?php echo esc_attr($config['id']); ?>-<?php echo esc_attr($level->id); ?>"
    data-label="products-<?php echo esc_attr($config['id']); ?>-<?php echo esc_attr($level->id); ?>"
    data-title="Editing <?php echo esc_attr($config['name']); ?> Product for <?php echo esc_attr($level->name); ?>"
    data-show-default-footer="1"
    style="display:none">
    <div class="body">
        <input type="hidden" name="stripeconnections[<?php echo esc_attr($level->id); ?>][sku]" value="<?php echo esc_attr($level->id); ?>">
        <input type="hidden" name="stripeconnections[<?php echo esc_attr($level->id); ?>][membershiplevel]" value="<?php echo esc_attr($level->name); ?>">
        <div class="row mb-3">
            <div class="col-md-12 div-stripe-product-error px-0" id="div-archive-error-<?php echo esc_attr($level->id); ?>" > 
                <div class="error text-left">
                <?php esc_html_e('Error: The selected product/price has been archived in Stripe and cannot be used for new purchases.', 'wishlist-member'); ?> 
                </div>
                <br>
            </div>
            
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Stripe Plan', 'wishlist-member')); ?>',
                    tooltip : '<?php echo esc_js(__('A Stripe Plan will charge the user based on the scheduled payments (recurring payments).', 'wishlist-member')); ?>',
                    type : 'radio',
                    name : 'stripeconnections[<?php echo esc_attr($level->id); ?>][subscription]',
                    value : 1,
                    column : 'col-12',
                    checked : 'checked',
                    class : 'stripe-plan-toggle',
                    'data-target' : '.stripe-plan-<?php echo esc_attr($level->id); ?>',
                }
            </template>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('One Time Payment (Custom Pricing)', 'wishlist-member')); ?>',
                    tooltip : '<?php echo esc_js(__('A One Time Payment will charge the user a one time payment (non-recurring payment).', 'wishlist-member')); ?>',
                    type : 'radio',
                    name : 'stripeconnections[<?php echo esc_attr($level->id); ?>][subscription]',
                    value : 0,
                    column : 'col-12',
                    class : 'stripe-plan-toggle',
                    'data-target' : '.stripe-onetime-<?php echo esc_attr($level->id); ?>',
                }
            </template>
        </div>
        <div style="display:none;" class="row stripe-onetime-<?php echo esc_attr($level->id); ?>">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Amount', 'wishlist-member')); ?>',
                    tooltip : '<?php echo esc_js(__('The One Time Payment price. The currency type is based on the Primary Currency type set in the Settings > blue Configure button > Settings > Primary Currency secton of the Stripe integration within WishList Member.', 'wishlist-member')); ?>',
                    type : 'text',
                    name : 'stripeconnections[<?php echo esc_attr($level->id); ?>][amount]',
                    class : '-amount',
                    placeholder : '<?php echo esc_js(__('Enter Amount', 'wishlist-member')); ?>',
                    column : 'col-12',
                }
            </template>
        </div>
        <div class="row stripe-plan-<?php echo esc_attr($level->id); ?>">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Select Stripe Plan(s)', 'wishlist-member')); ?>',
                    tooltip : '<?php echo esc_js(__('The dropdown is populated with the Stripe Plans from the connected Stripe account. Select the desired Stripe Plan for this Level.', 'wishlist-member')); ?>',
                    type : 'select',
                    multiple : 'multiple',
                    class : 'stripe-products',
                    name : 'stripeconnections[<?php echo esc_attr($level->id); ?>][plan]',
                    style : 'width: 100%',
                    'data-placeholder' : '<?php echo esc_js(__('Choose a Stripe Plan', 'wishlist-member')); ?>',
                    'data-allow-clear' : 'true',
                    options : WLM3ThirdPartyIntegration.stripe.plan_options,
                    column : 'col-12',
                }
            </template>

            <!-- @since 3.6 Support for multiple plans in the same product. -->
            <input type="hidden" class="stripe-plan" name="stripeconnections[<?php echo esc_attr($level->id); ?>][plan]">
            <input type="hidden" class="stripe-plans" name="stripeconnections[<?php echo esc_attr($level->id); ?>][plans]">

            <template class="wlm3-form-group">
                {
                    label : "<?php echo esc_js(__('Cancel the user\'s Stripe Subscription when the membership level is cancelled in WishList Member', 'wishlist-member')); ?>",
                    tooltip : '<?php echo esc_js(__('If the Level is cancelled for a Member in WishList Member, cancel the Stripe Plan within Stripe.', 'wishlist-member')); ?>',
                    type : 'checkbox',
                    name : 'stripeconnections[<?php echo esc_attr($level->id); ?>][cancel_subs_if_cancelled_in_wlm]',
                    value : 'yes',
                    uncheck_value : '',
                    column : 'col-12',
                }
            </template>
            <template class="wlm3-form-group">
                {
                    label : '<?php esc_js_e('Allow Proration for this Level', 'wishlist-member'); ?>',
                    tooltip : '<?php echo esc_js(__('If enabled, users can upgrade to this Level through Proration. This means a user who has purchased a subscription to gain access to another Level could then decide to purchase access to this additional Level and their subscription would be automatically calculated for the new amount. Stripe handles all the calculations to set the proration amount.', 'wishlist-member')); ?>',
                    tooltip_size: 'lg',
                    type : 'checkbox',
                    name : 'stripeconnections[<?php echo esc_attr($level->id); ?>][allow_proration_for_level]',
                    value : 'yes',
                    uncheck_value : '',
                    column : 'col-12',
                    class : 'stripe-allow-prorate',
                    'data-target' : '.stripe-allow-prorate-<?php echo esc_attr($level->id); ?>',
                }
            </template>
            <div style="display:none; margin-top: 5px;" class="stripe-allow-prorate-<?php echo esc_attr($level->id); ?>">
                <template class="wlm3-form-group">
                    {
                        label : '<?php esc_js_e('Remove from Previous Level after Prorated', 'wishlist-member'); ?>',
                        tooltip : '<?php echo esc_js(__('If enabled, the Member will be removed from the Previous Level after they have paid to upgrade through Proration.', 'wishlist-member')); ?>',
                        type : 'checkbox',
                        name : 'stripeconnections[<?php echo esc_attr($level->id); ?>][remove_from_level]',
                        value : 'yes',
                        uncheck_value : '',
                        column : 'col-12',
                    }
                </template>
            </div>

        </div>
    </div>
</div>
        <?php
    endforeach;
endforeach;
?>
