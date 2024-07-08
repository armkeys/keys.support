<div
    id="team-accounts-team-template" 
    data-id="team-accounts-team"
    data-label="team-accounts-team"
    data-title="Editing"
    data-show-default-footer="1"
    style="display:none">
    <div class="body">
        <form>
            <div class="row">
                <template class="wlm3-form-group">
                    [
                        {
                            name: 'id',
                            type: 'hidden'
                        },
                      {
                            column: 'col-12',
                            name: 'name',
                            label: '<?php echo esc_js(__('Team Plan Name', 'wishlist-member')); ?>',
                            placeholder: '<?php echo esc_js(__('Enter a name for this Team Plan.', 'wishlist-member')); ?>',
                            tooltip: '<?php echo esc_js(__('The name of the Team account.', 'wishlist-member')); ?>',
                      },
                        {
                            column: 'col-12',
                            type: 'number',
                            min: 1,
                            name: 'default_children',
                            label: '<?php echo esc_js(__('Number of Team Members', 'wishlist-member')); ?>',
                            value: 1,
                            style: 'max-width: 100px;',
                            tooltip : '<?php echo esc_js(__('The total number of team members in this team.', 'wishlist-member')); ?>',
                      },
                      {
                            column: 'col-12',
                            name: 'triggers',
                            type: 'select',
                            style: 'width: 100%',
                            multiple: true,
                            options: <?php echo wp_json_encode($access_options['__levels__']['options']); ?>,
                            label: '<?php echo esc_js(__('Level Triggers', 'wishlist-member')); ?>',
                            tooltip : '<?php echo esc_js(__('A Team Admin account for this team will be created when a user is added to any of the set level(s).', 'wishlist-member')); ?>',
                            'data-placeholder': '<?php echo esc_js(__('Select Membership Levels', 'wishlist-member')); ?>',
                      }
                    ]
                </template>
            </div>
            <div class="row">
                <template class="wlm3-form-group">
                    [
                        {
                            column: 'col-12 mb-3',
                            name: 'mirrored_access',
                            type: 'toggle-adjacent',
                            class: 'reverse-toggle',
                            checked: 1,
                            label: '<?php echo esc_js(__('Inherit the same membership access as Team Admin', 'wishlist-member')); ?>',
                            tooltip : '<p><?php echo esc_js(__('If enabled, all team members will inherit the same membership access as the Team Admin. All team members would have access to the same content as the Team Admin.', 'wishlist-member')); ?></p><p><?php echo esc_js(__('You can select a member to be a Team Admin by assigning them a team to manage in the Members > Manage > Click to edit a Member > Teams section of WishList Member.', 'wishlist-member')); ?></p><p><?php echo esc_js(__('A Team Admin can only manage teams they have been assigned.', 'wishlist-member')); ?></p>',
                            tooltip_size: 'lg',
                        },
                        {
                            column: 'col-12',
                            name: 'access_levels',
                            type: 'select',
                            style: 'width: 100%',
                            multiple: true,
                            options: <?php echo wp_json_encode($access_options['__levels__']['options']); ?>,
                            label: '<?php echo esc_js(__('Team Member Level Access', 'wishlist-member')); ?>',
                            tooltip : '<?php echo esc_js(__('Team members will be able to access protected content assigned to the set level(s).', 'wishlist-member')); ?>',
                            'data-placeholder': '<?php echo esc_js(__('Choose Membership Levels', 'wishlist-member')); ?>',
                      },
                        {
                            column: 'col-12',
                            name: 'access_payperposts',
                            type: 'select',
                            style: 'width: 100%',
                            grouped: true,
                            multiple: true,
                            options: <?php echo wp_json_encode($payperpost_options); ?>,
                            label: '<?php echo esc_js(__('Team Member Pay Per Post Access', 'wishlist-member')); ?>',
                            tooltip : '<?php echo esc_js(__('Team members will be able to access the set pay per post(s).', 'wishlist-member')); ?>',
                            'data-placeholder': '<?php echo esc_js(__('Choose Pay Per Posts', 'wishlist-member')); ?>',
                      },
                        {
                            column: 'col-12 reverse-toggle',
                            name: 'exclude_levels',
                            type: 'select',
                            style: 'width: 100%',
                            multiple: true,
                            options: <?php echo wp_json_encode($access_options['__levels__']['options']); ?>,
                            label: '<?php echo esc_js(__('Exclude Levels', 'wishlist-member')); ?>',
                            tooltip : '<?php echo esc_js(__('Team members will not be able to access protected content assigned to the set level(s).', 'wishlist-member')); ?>',
                            'data-placeholder': '<?php echo esc_js(__('Choose Membership Levels to exclude', 'wishlist-member')); ?>',
                      },
                        {
                            column: 'col-12 reverse-toggle',
                            name: 'exclude_payperposts',
                            type: 'select',
                            style: 'width: 100%',
                            grouped: true,
                            multiple: true,
                            options: <?php echo wp_json_encode($payperpost_options); ?>,
                            label: '<?php echo esc_js(__('Exclude Pay Per Posts', 'wishlist-member')); ?>',
                            tooltip : '<?php echo esc_js(__('Team members will not be able to access the set Pay Per Post(s).', 'wishlist-member')); ?>',
                            'data-placeholder': '<?php echo esc_js(__('Choose Pay Per Posts to exclude', 'wishlist-member')); ?>',
                      },
                    ]
                </template>
            </div>
    </form>
    </div>
</div>
