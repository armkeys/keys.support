<?php
foreach ($wpm_levels as $lid => $level) :
    $level     = (object) $level;
    $level->id = $lid;
    ?>
<div
    data-process="modal"
    id="evergreen-lists-modal-<?php echo esc_attr($level->id); ?>-template" 
    data-id="evergreen-lists-modal-<?php echo esc_attr($level->id); ?>"
    data-label="evergreen-lists-modal-<?php echo esc_attr($level->id); ?>"
    data-title="Editing <?php echo esc_attr($config['name']); ?> Settings for <?php echo esc_attr($level->name); ?>"
    data-show-default-footer="1"
    style="display:none">
    <div class="body">
        <div class="row">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Auto Registration Link', 'wishlist-member')); ?>',
                    type : 'text',
                    name : 'webinar[evergreen][<?php echo esc_attr($level->id); ?>]',
                    column : 'col-12',
                    'data-mirror-value' : '#evergreen-lists-<?php echo esc_attr($level->id); ?>',
                }
            </template>
        </div>
    </div>
</div>
    <?php
endforeach;
?>
