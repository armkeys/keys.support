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
        <div class="row">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Amount', 'wishlist-member')); ?>',
                    name : 'ewaysettings[connections][<?php echo esc_attr($level->id); ?>][rebill_init_amount]',
                    'data-mirror-value' : '#eway-products-amount-<?php echo esc_attr($level->id); ?>',
                    type : 'text',
                    column : 'col-6',
                    class : '-amount',
                }
            </template>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Recurring', 'wishlist-member')); ?>',
                    name : 'ewaysettings[connections][<?php echo esc_attr($level->id); ?>][subscription]',
                    'data-mirror-value' : '#eway-products-recur-<?php echo esc_attr($level->id); ?>',
                    type : 'checkbox',
                    column : 'col-12',
                    value : '1',
                    uncheck_value : '',
                    'data-target' : '.eway-recurring-<?php echo esc_attr($level->id); ?>',
                    class : 'eway-recurring-toggle',
                }
            </template>
        </div>
        <div class="row eway-recurring-<?php echo esc_attr($level->id); ?>">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Recurring Amount', 'wishlist-member')); ?>',
                    name : 'ewaysettings[connections][<?php echo esc_attr($level->id); ?>][rebill_recur_amount]',
                    'data-mirror-value' : '#eway-products-recuramount-<?php echo esc_attr($level->id); ?>',
                    type : 'text',
                    column : 'col-6',
                    class : '-amount',
                }
            </template>
            <template class="wlm3-form-group">
                {
                    label : '<span style="white-space: nowrap"><?php esc_html_e('Interval', 'wishlist-member'); ?></span>',
                    type : 'select',
                    name : 'ewaysettings[connections][<?php echo esc_attr($level->id); ?>][rebill_interval]',
                    'data-mirror-value' : '#eway-products-interval-<?php echo esc_attr($level->id); ?>',
                    options : WLM3ThirdPartyIntegration['eway'].rebill_interval,
                    style : 'width: 100%',
                    column : 'col-2 pr-0',
                }
            </template>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('&nbsp;', 'wishlist-member')); ?>',
                    type : 'select',
                    name : 'ewaysettings[connections][<?php echo esc_attr($level->id); ?>][rebill_interval_type]',
                    'data-mirror-value' : '#eway-products-intervaltype-<?php echo esc_attr($level->id); ?>',
                    options : WLM3ThirdPartyIntegration['eway'].rebill_interval_type,
                    style : 'width: 100%',
                    column : 'col-4 pl-0',
                }
            </template>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Last Rebill Date', 'wishlist-member')); ?>',
                    name : 'ewaysettings[connections][<?php echo esc_attr($level->id); ?>][rebill_end_date]',
                    'data-mirror-value' : '#eway-products-rebillend-<?php echo esc_attr($level->id); ?>',
                    type : 'text',
                    column : 'col-6',
                    class : 'wlm-datetimepicker',
                }
            </template>
        </div>
    </div>
</div>
        <?php
    endforeach;
endforeach;
?>
