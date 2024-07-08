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
        <div class="row plugnpaid-products-<?php echo esc_attr($level->id); ?>">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('plug&paid Plan', 'wishlist-member')); ?>',
                    type : 'select',
                    name : 'plugnpaid_products[<?php echo esc_attr($level->id); ?>]',
                    style : 'width: 100%',
                    'data-placeholder' : '<?php echo esc_js(__('Choose a plug&paid Product', 'wishlist-member')); ?>',
                    'data-allow-clear' : 'true',
                    options : WLM3ThirdPartyIntegration.plugnpaid.products_options,
                    column : 'col-12',
                }
            </template>
        </div>
    </div>
</div>
        <?php
    endforeach;
endforeach;
?>
