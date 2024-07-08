<?php
foreach ($wpm_levels as $lid => $level) :
    $level     = (object) $level;
    $level->id = $lid;
    ?>
<div
    data-process="modal"
    id="idevaffiliate-lists-modal-<?php echo esc_attr($level->id); ?>-template" 
    data-id="idevaffiliate-lists-modal-<?php echo esc_attr($level->id); ?>"
    data-label="idevaffiliate-lists-modal-<?php echo esc_attr($level->id); ?>"
    data-title="Editing <?php echo esc_attr($config['name']); ?> Settings for <?php echo esc_attr($level->name); ?>"
    data-show-default-footer="1"
    style="display:none">
    <div class="body">
        <div class="row">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Currency', 'wishlist-member')); ?>',
                    tooltip : '<?php echo esc_js(__('The currency and conversion rate can be set in the Account > System Settings > Localization > Multicurrency section in iDev. That should be configured in iDev before setting the currency here.', 'wishlist-member')); ?>',
                    type : 'select',
                    name : 'WLMiDev[wlm_idevcurrency][<?php echo esc_attr($level->id); ?>]',
                    options : WLM3ThirdPartyIntegration['idevaffiliate'].currencies,
                    column : 'col-3',
                }
            </template>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Initial Price', 'wishlist-member')); ?>',
                    tooltip : '<?php echo esc_js(__('The initial purchase price for the level.', 'wishlist-member')); ?>',
                    type : 'text',
                    class : '-numeric',
                    name : 'WLMiDev[wlm_idevamountfirst][<?php echo esc_attr($level->id); ?>]',
                    placeholder : '<?php echo esc_js(__('0.00', 'wishlist-member')); ?>',
                    'data-mirror-value' : '#idev-values-initial-<?php echo esc_attr($level->id); ?>',
                    column : 'col-3',
                }
            </template>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Recurring Price', 'wishlist-member')); ?>',
                    tooltip : '<?php echo esc_js(__('The recurring price that will be charged after the initial price.', 'wishlist-member')); ?>',
                    type : 'text',
                    class : '-numeric',
                    name : 'WLMiDev[wlm_idevamountrecur][<?php echo esc_attr($level->id); ?>]',
                    placeholder : '<?php echo esc_js(__('0.00', 'wishlist-member')); ?>',
                    'data-mirror-value' : '#idev-values-recur-<?php echo esc_attr($level->id); ?>',
                    column : 'col-6',
                }
            </template>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Fixed Commission', 'wishlist-member')); ?>',
                    tooltip : '<?php echo esc_js(__('If enabled, a fixed price can be set for the commissions. If disabled, the commission rate set in iDev will be used.', 'wishlist-member')); ?>',
                    type : 'checkbox',
                    name : 'WLMiDev[wlm_idevspecificamount][<?php echo esc_attr($level->id); ?>]',
                    value : 'yes',
                    uncheck_value : '',
                    column : 'col-12',
                    'data-level' : '<?php echo esc_js($level->id); ?>',
                    'data-mirror-value' : '#idev-values-fixed-<?php echo esc_attr($level->id); ?>',
                    class : '-commission-type',
                }
            </template>
        </div>
        <div class="row -commission-fixed-<?php echo esc_attr($level->id); ?>">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Initial Commission', 'wishlist-member')); ?>',
                    tooltip : '<?php echo esc_js(__('The fixed rate for the initial commission.', 'wishlist-member')); ?>',
                    type : 'text',
                    class : '-numeric',
                    name : 'WLMiDev[wlm_idevamountpayment][<?php echo esc_attr($level->id); ?>]',
                    placeholder : '<?php echo esc_js(__('0.00', 'wishlist-member')); ?>',
                    'data-mirror-value' : '#idev-values-initialc-<?php echo esc_attr($level->id); ?>',
                    column : 'col-6',
                }
            </template>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Recurring Commission', 'wishlist-member')); ?>',
                    tooltip : '<?php echo esc_js(__('The fixed rate for the recurring commission.', 'wishlist-member')); ?>',
                    type : 'text',
                    class : '-numeric',
                    name : 'WLMiDev[wlm_idevamountpaymentrecur][<?php echo esc_attr($level->id); ?>]',
                    placeholder : '<?php echo esc_js(__('0.00', 'wishlist-member')); ?>',
                    'data-mirror-value' : '#idev-values-recurc-<?php echo esc_attr($level->id); ?>',
                    column : 'col-6',
                }
            </template>
        </div>
        <div class="row -commission-idev-<?php echo esc_attr($level->id); ?>">
            <div class="col-6">
                <p><em><?php esc_html_e('Payout levels set in iDevAffiliate', 'wishlist-member'); ?></em></p>
            </div>
        </div>
    </div>
</div>
    <?php
endforeach;
?>
