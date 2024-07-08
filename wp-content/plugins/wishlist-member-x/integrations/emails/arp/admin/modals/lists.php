<?php
foreach ($wpm_levels as $lid => $level) :
    $level     = (object) $level;
    $level->id = $lid;
    ?>
<div
    data-process="modal"
    id="arp-lists-modal-<?php echo esc_attr($level->id); ?>-template" 
    data-id="arp-lists-modal-<?php echo esc_attr($level->id); ?>"
    data-label="arp-lists-modal-<?php echo esc_attr($level->id); ?>"
    data-title="Editing <?php echo esc_attr($config['name']); ?> Settings for <?php echo esc_attr($level->name); ?>"
    data-show-default-footer="1"
    style="display:none">
    <div class="body">
        <div class="row">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('AutoResponder ID', 'wishlist-member')); ?>',
                    type : 'text',
                    name : 'arID[<?php echo esc_attr($level->id); ?>]',
                    column : 'col-12',
                    'data-mirror-value' : '#arp-lists-<?php echo esc_attr($level->id); ?>',
                    tooltip : '<?php echo esc_js(__('Paste the copied Autoresponder ID number from AutoResponse Plus into the corresponding membership level\'s Autoresponder ID field', 'wishlist-member')); ?>',
                }
            </template>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Unsubscribe if Removed from Level', 'wishlist-member')); ?>',
                    name : 'arID[<?php echo esc_attr($level->id); ?>]',
                    value : '1',
                    uncheck_value : '',
                    type  : 'checkbox',
                    column : 'col-12',
                    'data-mirror-value' : '#arp-lists-<?php echo esc_attr($level->id); ?>',
                }
            </template>
        </div>
    </div>
</div>
    <?php
endforeach;
?>
