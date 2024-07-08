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
                    label : '<?php echo esc_js(__('Recurly Plan', 'wishlist-member')); ?>',
                    type : 'select',
                    name : 'recurlyconnections[<?php echo esc_attr($level->id); ?>][plan]',
                    style : 'width: 100%',
                    'data-mirror-value' : '#recurly-product-plan-<?php echo esc_attr($level->id); ?>',
                    'data-placeholder' : '<?php echo esc_js(__('Choose a Recurly Plan', 'wishlist-member')); ?>',
                    options : WLM3ThirdPartyIntegration.recurly.plan_options,
                    column : 'col-12',
                    'data-allow-clear' : 'true',
                    class : 'recurlyplans',
                }
            </template>
        </div>
    </div>
</div>
        <?php
    endforeach;
endforeach;
?>
