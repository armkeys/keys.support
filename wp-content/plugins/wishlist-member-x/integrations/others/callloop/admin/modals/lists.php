<?php
foreach ($wpm_levels as $lid => $level) :
    $level     = (object) $level;
    $level->id = $lid;
    ?>
<div
    data-process="modal"
    id="callloop-lists-modal-<?php echo esc_attr($level->id); ?>-template" 
    data-id="callloop-lists-modal-<?php echo esc_attr($level->id); ?>"
    data-label="callloop-lists-modal-<?php echo esc_attr($level->id); ?>"
    data-title="Editing <?php echo esc_attr($config['name']); ?> Settings for <?php echo esc_attr($level->name); ?>"
    data-show-default-footer="1"
    style="display:none">
    <div class="body">
        <div class="row">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Call Loop List URL', 'wishlist-member')); ?>',
                    type : 'text',
                    name : 'callloop_settings[URL][<?php echo esc_attr($level->id); ?>]',
                    column : 'col-12',
                    'data-mirror-value' : '#callloop-lists-<?php echo esc_attr($level->id); ?>',
                    tooltip : '<?php echo esc_js(__('Copy the list URL from Call Loop and paste it into the corresponding field with no extra spaces.', 'wishlist-member')); ?>',
                }
            </template>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Unsubscribe if Removed from Level', 'wishlist-member')); ?>',
                    name  : 'callloop_settings[callloopUnsub][<?php echo esc_attr($level->id); ?>]',
                    value : '1',
                    uncheck_value : '',
                    type  : 'checkbox',
                    column : 'col-12',
                    'data-mirror-value' : '#callloop-unsubscribe-<?php echo esc_attr($level->id); ?>',
                }
            </template>
        </div>
    </div>
</div>
    <?php
endforeach;
?>
