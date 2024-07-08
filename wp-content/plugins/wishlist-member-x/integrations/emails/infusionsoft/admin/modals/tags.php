<?php
foreach ($wpm_levels as $lid => $level) :
    $level     = (object) $level;
    $level->id = $lid;
    ?>
<div
    data-process="modal"
    id="infusionsoft-lists-modal-<?php echo esc_attr($level->id); ?>-template"
    data-id="infusionsoft-lists-modal-<?php echo esc_attr($level->id); ?>"
    data-label="infusionsoft-lists-modal-<?php echo esc_attr($level->id); ?>"
    data-title="Editing <?php echo esc_attr($config['name']); ?> Tags for <?php echo esc_attr($level->name); ?>"
    data-show-default-footer="1"
    data-classes="modal-lg"
    style="display:none">
    <div class="body">
        <div class="row">
            <div class="col-md-12">
                <ul class="nav nav-tabs">
                    <li class="active nav-item"><a class="nav-link" data-toggle="tab" href="#infusionsoft-ar-when-added-<?php echo esc_attr($level->id); ?>"><?php esc_html_e('Added', 'wishlist-member'); ?></a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#infusionsoft-ar-when-removed-<?php echo esc_attr($level->id); ?>"><?php esc_html_e('Removed', 'wishlist-member'); ?></a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#infusionsoft-ar-when-cancelled-<?php echo esc_attr($level->id); ?>"><?php esc_html_e('Cancelled', 'wishlist-member'); ?></a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#infusionsoft-ar-when-uncancelled-<?php echo esc_attr($level->id); ?>"><?php esc_html_e('Uncancelled', 'wishlist-member'); ?></a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#infusionsoft-ar-when-expired-<?php echo esc_attr($level->id); ?>"><?php esc_html_e('Expired', 'wishlist-member'); ?></a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#infusionsoft-ar-when-unexpired-<?php echo esc_attr($level->id); ?>"><?php esc_html_e('Unexpired', 'wishlist-member'); ?></a></li>
                </ul>
            </div>
        </div>
        <div class="tab-content">
            <div class="row tab-pane active in" id="infusionsoft-ar-when-added-<?php echo esc_attr($level->id); ?>">
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Apply Tags:', 'wishlist-member')); ?>',
                        column : 'col-12',
                        type : 'select',
                        multiple : 'multiple',
                        name : 'istags_add_app[<?php echo esc_attr($level->id); ?>][]',
                        class : 'infusionsoft-tags-<?php echo esc_attr($level->id); ?>',
                        grouped : true,
                        'data-placeholder' : '<?php echo esc_js(__('Select tags...', 'wishlist-member')); ?>',
                        style : 'width: 100%',
                        tooltip : '<?php echo esc_js(__('Adds the selected Tag(s) to the User when Added to the Level.', 'wishlist-member')); ?>'
                    }
                </template>
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Remove Tags:', 'wishlist-member')); ?>',
                        column : 'col-12',
                        type : 'select',
                        multiple : 'multiple',
                        name : 'istags_add_rem[<?php echo esc_attr($level->id); ?>][]',
                        class : 'infusionsoft-tags-<?php echo esc_attr($level->id); ?>',
                        grouped : true,
                        'data-placeholder' : '<?php echo esc_js(__('Select tags...', 'wishlist-member')); ?>',
                        style : 'width: 100%',
                        tooltip : '<?php echo esc_js(__('Removes the selected Tag(s) to the User when Added to the Level.', 'wishlist-member')); ?>'
                    }
                </template>
            </div>
            <div class="row tab-pane" id="infusionsoft-ar-when-removed-<?php echo esc_attr($level->id); ?>">
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Apply Tags:', 'wishlist-member')); ?>',
                        column : 'col-12',
                        type : 'select',
                        multiple : 'multiple',
                        name : 'istags_remove_app[<?php echo esc_attr($level->id); ?>][]',
                        class : 'infusionsoft-tags-<?php echo esc_attr($level->id); ?>',
                        grouped : true,
                        'data-placeholder' : '<?php echo esc_js(__('Select tags...', 'wishlist-member')); ?>',
                        style : 'width: 100%',
                        tooltip : '<?php echo esc_js(__('Adds the selected Tag(s) to the User when Removed from the Level.', 'wishlist-member')); ?>'
                    }
                </template>
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Remove Tags:', 'wishlist-member')); ?>',
                        column : 'col-12',
                        type : 'select',
                        multiple : 'multiple',
                        name : 'istags_remove_rem[<?php echo esc_attr($level->id); ?>][]',
                        class : 'infusionsoft-tags-<?php echo esc_attr($level->id); ?>',
                        grouped : true,
                        'data-placeholder' : '<?php echo esc_js(__('Select tags...', 'wishlist-member')); ?>',
                        style : 'width: 100%',
                        tooltip : '<?php echo esc_js(__('Removes the selected Tag(s) to the User when Removed from the Level.', 'wishlist-member')); ?>'
                    }
                </template>
            </div>
            <div class="row tab-pane" id="infusionsoft-ar-when-cancelled-<?php echo esc_attr($level->id); ?>">
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Apply Tags:', 'wishlist-member')); ?>',
                        column : 'col-12',
                        type : 'select',
                        multiple : 'multiple',
                        name : 'istags_cancelled_app[<?php echo esc_attr($level->id); ?>][]',
                        class : 'infusionsoft-tags-<?php echo esc_attr($level->id); ?>',
                        grouped : true,
                        'data-placeholder' : '<?php echo esc_js(__('Select tags...', 'wishlist-member')); ?>',
                        style : 'width: 100%',
                        tooltip : '<?php echo esc_js(__('Adds the selected Tag(s) to the User when Cancelled from the Level.', 'wishlist-member')); ?>'
                    }
                </template>
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Remove Tags:', 'wishlist-member')); ?>',
                        column : 'col-12',
                        type : 'select',
                        multiple : 'multiple',
                        name : 'istags_cancelled_rem[<?php echo esc_attr($level->id); ?>][]',
                        class : 'infusionsoft-tags-<?php echo esc_attr($level->id); ?>',
                        grouped : true,
                        'data-placeholder' : '<?php echo esc_js(__('Select tags...', 'wishlist-member')); ?>',
                        style : 'width: 100%',
                        tooltip : '<?php echo esc_js(__('Removes the selected Tag(s) to the User when Cancelled from the Level.', 'wishlist-member')); ?>'
                    }
                </template>
            </div>
            <div class="row tab-pane" id="infusionsoft-ar-when-uncancelled-<?php echo esc_attr($level->id); ?>">
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Apply Tags:', 'wishlist-member')); ?>',
                        column : 'col-12',
                        type : 'select',
                        multiple : 'multiple',
                        name : 'istags_uncancelled_app[<?php echo esc_attr($level->id); ?>][]',
                        class : 'infusionsoft-tags-<?php echo esc_attr($level->id); ?>',
                        grouped : true,
                        'data-placeholder' : '<?php echo esc_js(__('Select tags...', 'wishlist-member')); ?>',
                        style : 'width: 100%',
                        tooltip : '<?php echo esc_js(__('Adds the selected Tag(s) to the User when Uncancelled from the Level.', 'wishlist-member')); ?>'
                    }
                </template>
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Remove Tags:', 'wishlist-member')); ?>',
                        column : 'col-12',
                        type : 'select',
                        multiple : 'multiple',
                        name : 'istags_uncancelled_rem[<?php echo esc_attr($level->id); ?>][]',
                        class : 'infusionsoft-tags-<?php echo esc_attr($level->id); ?>',
                        grouped : true,
                        'data-placeholder' : '<?php echo esc_js(__('Select tags...', 'wishlist-member')); ?>',
                        style : 'width: 100%',
                        tooltip : '<?php echo esc_js(__('Removes the selected Tag(s) to the User when Uncancelled from the Level.', 'wishlist-member')); ?>'
                    }
                </template>
            </div>
            <div class="row tab-pane" id="infusionsoft-ar-when-uncancelled-<?php echo esc_attr($level->id); ?>">
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Apply Tags:', 'wishlist-member')); ?>',
                        column : 'col-12',
                        type : 'select',
                        multiple : 'multiple',
                        name : 'istags_uncancelled_app[<?php echo esc_attr($level->id); ?>][]',
                        class : 'infusionsoft-tags-<?php echo esc_attr($level->id); ?>',
                        grouped : true,
                        'data-placeholder' : '<?php echo esc_js(__('Select tags...', 'wishlist-member')); ?>',
                        style : 'width: 100%',
                        tooltip : '<?php echo esc_js(__('Adds the selected Tag(s) to the User when Uncancelled from the Level.', 'wishlist-member')); ?>'
                    }
                </template>
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Remove Tags:', 'wishlist-member')); ?>',
                        column : 'col-12',
                        type : 'select',
                        multiple : 'multiple',
                        name : 'istags_uncancelled_rem[<?php echo esc_attr($level->id); ?>][]',
                        class : 'infusionsoft-tags-<?php echo esc_attr($level->id); ?>',
                        grouped : true,
                        'data-placeholder' : '<?php echo esc_js(__('Select tags...', 'wishlist-member')); ?>',
                        style : 'width: 100%',
                        tooltip : '<?php echo esc_js(__('Removes the selected Tag(s) to the User when Uncancelled from the Level.', 'wishlist-member')); ?>'
                    }
                </template>
            </div>
            <div class="row tab-pane" id="infusionsoft-ar-when-expired-<?php echo esc_attr($level->id); ?>">
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Apply Tags:', 'wishlist-member')); ?>',
                        column : 'col-12',
                        type : 'select',
                        multiple : 'multiple',
                        name : 'istags_expired_app[<?php echo esc_attr($level->id); ?>][]',
                        class : 'infusionsoft-tags-<?php echo esc_attr($level->id); ?>',
                        grouped : true,
                        'data-placeholder' : '<?php echo esc_js(__('Select tags...', 'wishlist-member')); ?>',
                        style : 'width: 100%',
                        tooltip : '<?php echo esc_js(__('Adds the selected Tag(s) to the User when Expired from the Level.', 'wishlist-member')); ?>'
                    }
                </template>
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Remove Tags:', 'wishlist-member')); ?>',
                        column : 'col-12',
                        type : 'select',
                        multiple : 'multiple',
                        name : 'istags_expired_rem[<?php echo esc_attr($level->id); ?>][]',
                        class : 'infusionsoft-tags-<?php echo esc_attr($level->id); ?>',
                        grouped : true,
                        'data-placeholder' : '<?php echo esc_js(__('Select tags...', 'wishlist-member')); ?>',
                        style : 'width: 100%',
                        tooltip : '<?php echo esc_js(__('Removes the selected Tag(s) to the User when Expired from the Level.', 'wishlist-member')); ?>'
                    }
                </template>
            </div>
            <div class="row tab-pane" id="infusionsoft-ar-when-unexpired-<?php echo esc_attr($level->id); ?>">
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Apply Tags:', 'wishlist-member')); ?>',
                        column : 'col-12',
                        type : 'select',
                        multiple : 'multiple',
                        name : 'istags_unexpired_app[<?php echo esc_attr($level->id); ?>][]',
                        class : 'infusionsoft-tags-<?php echo esc_attr($level->id); ?>',
                        grouped : true,
                        'data-placeholder' : '<?php echo esc_js(__('Select tags...', 'wishlist-member')); ?>',
                        style : 'width: 100%',
                        tooltip : '<?php echo esc_js(__('Adds the selected Tag(s) to the User when Unexpired from the Level.', 'wishlist-member')); ?>'
                    }
                </template>
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Remove Tags:', 'wishlist-member')); ?>',
                        column : 'col-12',
                        type : 'select',
                        multiple : 'multiple',
                        name : 'istags_unexpired_rem[<?php echo esc_attr($level->id); ?>][]',
                        class : 'infusionsoft-tags-<?php echo esc_attr($level->id); ?>',
                        grouped : true,
                        'data-placeholder' : '<?php echo esc_js(__('Select tags...', 'wishlist-member')); ?>',
                        style : 'width: 100%',
                        tooltip : '<?php echo esc_js(__('Removes the selected Tag(s) to the User when Unexpired from the Level.', 'wishlist-member')); ?>'
                    }
                </template>
            </div>
        </div>
    </div>
</div>
    <?php
endforeach;
?>
