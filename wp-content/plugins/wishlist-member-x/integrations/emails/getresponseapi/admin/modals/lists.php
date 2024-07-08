<?php
foreach ($wpm_levels as $lid => $level) :
    $level     = (object) $level;
    $level->id = $lid;
    ?>
<div
    data-process="modal"
    id="getresponseapi-lists-modal-<?php echo esc_attr($level->id); ?>-template" 
    data-id="getresponseapi-lists-modal-<?php echo esc_attr($level->id); ?>"
    data-label="getresponseapi-lists-modal-<?php echo esc_attr($level->id); ?>"
    data-title="Editing <?php echo esc_attr($config['name']); ?> Settings for <?php echo esc_attr($level->name); ?>"
    data-classes="modal-lg"
    data-show-default-footer="1"
    style="display:none">
    <div class="body">
        <ul class="nav nav-tabs">
            <li class="active nav-item"><a class="nav-link" data-toggle="tab" href="#getresponseapi-ar-when-added-<?php echo esc_attr($level->id); ?>"><?php esc_html_e('Added', 'wishlist-member'); ?></a></li>
            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#getresponseapi-ar-when-removed-<?php echo esc_attr($level->id); ?>"><?php esc_html_e('Removed', 'wishlist-member'); ?></a></li>
            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#getresponseapi-ar-when-cancelled-<?php echo esc_attr($level->id); ?>"><?php esc_html_e('Cancelled', 'wishlist-member'); ?></a></li>
            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#getresponseapi-ar-when-uncancelled-<?php echo esc_attr($level->id); ?>"><?php esc_html_e('Uncancelled', 'wishlist-member'); ?></a></li>
        </ul>
        <div class="tab-content">
            <div class="row tab-pane active" id="getresponseapi-ar-when-added-<?php echo esc_attr($level->id); ?>">
                <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Add to List', 'wishlist-member')); ?>',
                    tooltip : '<?php echo esc_js(__('Adds the User to the selected List(s) when Added to the Level.', 'wishlist-member')); ?>',
                    type : 'text',
                    name : 'list_actions[<?php echo esc_attr($level->id); ?>][added][add]',
                    column : 'col-12',
                    tooltip : '<?php echo esc_js(__('The GetResponse List Name', 'wishlist-member')); ?>',
                }
                </template>
                <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Remove from List', 'wishlist-member')); ?>',
                    tooltip : '<?php echo esc_js(__('Removes the User from the selected List(s) when Added to the Level.', 'wishlist-member')); ?>',
                    type : 'text',
                    name : 'list_actions[<?php echo esc_attr($level->id); ?>][added][remove]',
                    column : 'col-12',
                    tooltip : '<?php echo esc_js(__('The GetResponse List Name', 'wishlist-member')); ?>',
                }
                </template>
            </div>
            <div class="row tab-pane" id="getresponseapi-ar-when-removed-<?php echo esc_attr($level->id); ?>">
                <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Add to List', 'wishlist-member')); ?>',
                    tooltip : '<?php echo esc_js(__('Adds the User to the selected List(s) when Removed from the Level.', 'wishlist-member')); ?>',
                    type : 'text',
                    name : 'list_actions[<?php echo esc_attr($level->id); ?>][removed][add]',
                    column : 'col-12',
                    tooltip : '<?php echo esc_js(__('The GetResponse List Name', 'wishlist-member')); ?>',
                }
                </template>
                <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Remove from List', 'wishlist-member')); ?>',
                    tooltip : '<?php echo esc_js(__('Removes the User from the selected List(s) when Removed from the Level.', 'wishlist-member')); ?>',
                    type : 'text',
                    name : 'list_actions[<?php echo esc_attr($level->id); ?>][removed][remove]',
                    column : 'col-12',
                    tooltip : '<?php echo esc_js(__('The GetResponse List Name', 'wishlist-member')); ?>',
                }
                </template>
            </div>
            <div class="row tab-pane" id="getresponseapi-ar-when-cancelled-<?php echo esc_attr($level->id); ?>">
                <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Add to List', 'wishlist-member')); ?>',
                    tooltip : '<?php echo esc_js(__('Adds the User to the selected List(s) when Cancelled from the Level.', 'wishlist-member')); ?>',
                    type : 'text',
                    name : 'list_actions[<?php echo esc_attr($level->id); ?>][cancelled][add]',
                    column : 'col-12',
                    tooltip : '<?php echo esc_js(__('The GetResponse List Name', 'wishlist-member')); ?>',
                }
                </template>
                <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Remove from List', 'wishlist-member')); ?>',
                    tooltip : '<?php echo esc_js(__('Removes the User from the selected List(s) when Cancelled from the Level.', 'wishlist-member')); ?>',
                    type : 'text',
                    name : 'list_actions[<?php echo esc_attr($level->id); ?>][cancelled][remove]',
                    column : 'col-12',
                    tooltip : '<?php echo esc_js(__('The GetResponse List Name', 'wishlist-member')); ?>',
                }
                </template>
            </div>
            <div class="row tab-pane" id="getresponseapi-ar-when-uncancelled-<?php echo esc_attr($level->id); ?>">
                <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Add to List', 'wishlist-member')); ?>',
                    tooltip : '<?php echo esc_js(__('Adds the User to the selected List(s) when Uncancelled from the Level.', 'wishlist-member')); ?>',
                    type : 'text',
                    name : 'list_actions[<?php echo esc_attr($level->id); ?>][uncancelled][add]',
                    column : 'col-12',
                    tooltip : '<?php echo esc_js(__('The GetResponse List Name', 'wishlist-member')); ?>',
                }
                </template>
                <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Remove from List', 'wishlist-member')); ?>',
                    tooltip : '<?php echo esc_js(__('Removes the User from the selected List(s) when Uncancelled from the Level.', 'wishlist-member')); ?>',
                    type : 'text',
                    name : 'list_actions[<?php echo esc_attr($level->id); ?>][uncancelled][remove]',
                    column : 'col-12',
                    tooltip : '<?php echo esc_js(__('The GetResponse List Name', 'wishlist-member')); ?>',
                }
                </template>
            </div>
        </div>
    </div>
</div>
    <?php
endforeach;
?>
