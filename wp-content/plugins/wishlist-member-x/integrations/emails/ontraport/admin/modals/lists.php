<?php
foreach ($wpm_levels as $lid => $level) :
    $level     = (object) $level;
    $level->id = $lid;
    ?>
<div
    data-process="modal"
    id="ontraport-lists-modal-<?php echo esc_attr($level->id); ?>-template" 
    data-id="ontraport-lists-modal-<?php echo esc_attr($level->id); ?>"
    data-label="ontraport-lists-modal-<?php echo esc_attr($level->id); ?>"
    data-title="Editing <?php echo esc_attr($config['name']); ?> Settings for <?php echo esc_attr($level->name); ?>"
    data-show-default-footer="1"
    style="display:none">
    <div class="body">
        <div class="row">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Enable', 'wishlist-member')); ?>',
                    tooltip : '<?php echo esc_js(__('Select if the Tags/Sequences functionality is enabled for this Level.', 'wishlist-member')); ?>',
                    name  : 'addenabled[<?php echo esc_attr($level->id); ?>]',
                    value : 'yes',
                    uncheck_value : '',
                    type  : 'checkbox',
                    column : 'col-12',
                    'data-mirror-value' : '#ontraport-enable-<?php echo esc_attr($level->id); ?>',
                }
            </template>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Tags', 'wishlist-member')); ?>',
                    tooltip : '<?php echo esc_js(__('Select any Tag(s) from Ontraport to be applied when a User is added to this Level.', 'wishlist-member')); ?>',
                    type : 'select',
                    class : 'ontraport-tags',
                    style : 'width: 100%',
                    name : 'tags[<?php echo esc_attr($level->id); ?>][]',
                    multiple : 'multiple',
                    column : 'col-12',
                    'data-mirror-value' : '#ontraport-tags-<?php echo esc_attr($level->id); ?>',
                }
            </template>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Sequences', 'wishlist-member')); ?>',
                    tooltip : '<?php echo esc_js(__('Select any Sequence(s) from Ontraport to be applied when a User is added to this Level.', 'wishlist-member')); ?>',
                    type : 'select',
                    class : 'ontraport-sequences',
                    style : 'width: 100%',
                    name : 'sequences[<?php echo esc_attr($level->id); ?>][]',
                    multiple : 'multiple',
                    column : 'col-12',
                    'data-mirror-value' : '#ontraport-sequences-<?php echo esc_attr($level->id); ?>',
                }
            </template>
        </div>
    </div>
</div>
    <?php
endforeach;
?>
