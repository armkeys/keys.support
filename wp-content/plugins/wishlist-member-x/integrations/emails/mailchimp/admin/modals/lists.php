<?php
foreach ($wpm_levels as $lid => $level) :
    $level     = (object) $level;
    $level->id = $lid;
    ?>
<div
    data-process="modal"
    id="mailchimp-lists-modal-<?php echo esc_attr($level->id); ?>-template"
    data-id="mailchimp-lists-modal-<?php echo esc_attr($level->id); ?>"
    data-label="mailchimp-lists-modal-<?php echo esc_attr($level->id); ?>"
    data-title="Editing <?php echo esc_attr($config['name']); ?> Settings for <?php echo esc_attr($level->name); ?>"
    data-show-default-footer="1"
    style="display:none">
    <div class="body">
        <div class="row">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('List', 'wishlist-member')); ?>',
                    type : 'select',
                    class : 'mailchimp-lists-select',
                    style : 'width: 100%',
                    name : 'mcID[<?php echo esc_attr($level->id); ?>]',
                    column : 'col-12',
                    'data-mirror-value' : '#mailchimp-list-<?php echo esc_attr($level->id); ?>',
                    tooltip : '<?php echo esc_js(__(' Users will be added to the selected MailChimp List when they join or are added to a Level in WishList Member.', 'wishlist-member')); ?>',
                }
            </template>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Interest Groups', 'wishlist-member')); ?>',
                    type : 'select',
                    style : 'width: 100%',
                    name : 'mcGping[<?php echo esc_attr($level->id); ?>][]',
                    column : 'col-12 interest-group',
                    multiple : 'multiple',
                    tooltip : '<?php echo esc_js(__('Users will be added to the selected MailChimp Group(s) when they joing or are added to the Level in WishList Member.', 'wishlist-member')); ?>',
                    'data-mirror-value' : '#mailchimp-interest-<?php echo esc_attr($level->id); ?>',
                }
            </template>
        </div>
        <div class="row">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Action if Removed from Level', 'wishlist-member')); ?>',
                    type  : 'select',
                    class : 'mailchimp-actions-select',
                    name  : 'mcOnRemCan[<?php echo esc_attr($level->id); ?>]',
                    options : [
                        { value : '', text : 'Do Nothing' },
                        { value : 'unsub', text : 'Unsubscribe from List' },
                        { value : 'move', text : 'Move to Group' },
                        { value : 'add', text : 'Add to Group' }
                    ],
                    column : 'col-12',
                    style : 'width: 100%',
                    'data-mirror-value' : '#mailchimp-remove-<?php echo esc_attr($level->id); ?>',
                    tooltip : '<?php echo esc_js(__('The set Action will occur if a user is removed from the Level in WishList Member.', 'wishlist-member')); ?>',
                }
            </template>
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Interest Groups', 'wishlist-member')); ?>',
                    type : 'select',
                    style : 'width: 100%',
                    name : 'mcRCGping[<?php echo esc_attr($level->id); ?>][]',
                    column : 'col-12 interest-group',
                    multiple : 'multiple',
                    tooltip : '<?php echo esc_js(__(' If "Move to Group" or "Add to Group" is selected from the above field, the desired Group(s) for that Action can be selected in this field.', 'wishlist-member')); ?>',
                    'data-mirror-value' : '#mailchimp-interestr-<?php echo esc_attr($level->id); ?>',
                }
            </template>
        </div>
    </div>
</div>
    <?php
endforeach;
?>
