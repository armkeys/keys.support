<div id="<?php echo esc_attr($_the_id); ?>" class="content-wrapper">
    <div class="row">
        <?php $option_val = $this->get_option('members_can_update_info'); ?>
        <div class="col-md-12">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Members can update their user info', 'wishlist-member')); ?>',
                    name  : 'members_can_update_info',
                    value : '1',
                    checked_value : '<?php echo esc_js($option_val); ?>',
                    uncheck_value : '0',
                    class : 'wlm_toggle-switch notification-switch',
                    type  : 'checkbox',
                    tooltip : '<?php echo esc_js(__('If enabled, a Member can click the Membership Details option in the Login Widget and adjust their user info.  If disabled selected, the Members Details option is removed from the Login Widget.', 'wishlist-member')); ?>',
                    tooltip_size : 'lg'
                }
            </template>
            <input type="hidden" name="action" value="admin_actions" />
            <input type="hidden" name="WishListMemberAction" value="save" />
            <br>
        </div>
    </div>
    <div class="row">
        <?php $option_val = $this->get_option('payperpost_ismember'); ?>
        <div class="col-md-12">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Consider Pay Per Post Members in [wlm_ismember] and [wlm_nonmember] Merge Codes', 'wishlist-member')); ?>',
                    name  : 'payperpost_ismember',
                    value : '1',
                    checked_value : '<?php echo esc_js($option_val); ?>',
                    uncheck_value : '0',
                    class : 'wlm_toggle-switch notification-switch',
                    type  : 'checkbox',
                    tooltip : '<?php echo esc_js(__('By default the shortcode [wlm_ismember] only applies to users that belong to a membership level. The shortcode [wlm_nonmember] only applies to users that do NOT belong to a membership level. When this setting is enabled users that have a Pay Per Post assigned to them will be considered members even if they do not belong to a membership level.', 'wishlist-member')); ?>',
                    tooltip_size : 'lg'
                }
            </template>
            <input type="hidden" name="action" value="admin_actions" />
            <input type="hidden" name="WishListMemberAction" value="save" />
            <br>
        </div>
    </div>
    <div class="row">
        <div class="col-md-7">
            <label><?php esc_html_e('Text to display for content protected with private tags', 'wishlist-member'); ?></label>
            <?php $this->tooltip(__('This text will be displayed to non-members instead of the protected content when content is protected with the private tag shortcode.<br><br>Merge code [level] will be automatically replaced with the level of membership.<br><br>Note: Private tags can be created and inserted into a page or post by using the blue WishList Member code insert button found in the edit section of all pages and posts.', 'wishlist-member'), 'lg'); ?>
        </div>
        <div class="col-md-6">
            <div class="form-group ">
                <textarea id="private_tag_protect_msg-id-jrcp5x4xelkc3px7ch" class="form-control " name="private_tag_protect_msg" style="min-height: 2em" data-initial="<?php echo esc_attr($this->get_option('private_tag_protect_msg')); ?>" data-lpignore="true"><?php echo esc_textarea($this->get_option('private_tag_protect_msg')); ?></textarea>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-7">
            <label><?php esc_html_e('Text to display for content protected with reverse private tags', 'wishlist-member'); ?></label>
            <?php $this->tooltip(__('This text will be displayed to members in the selected membership level instead of the specified content when it is displayed with the reverse private tag shortcode.<br><br>Merge code [level] will be automatically replaced with the level of membership. <br><br>Note: Reverse Private tags can be created and inserted into a page or post by using the blue WishList Member code insert button found in the edit section of all pages and posts.', 'wishlist-member'), 'lg'); ?>
        </div>
        <div class="col-md-6">
            <div class="form-group ">
                <textarea id="reverse_private_tag_protect_msg-id-jrcp5x4xelkc3px7ch" class="form-control " name="reverse_private_tag_protect_msg" style="min-height: 2em" data-initial="<?php echo esc_attr($this->get_option('reverse_private_tag_protect_msg')); ?>" data-lpignore="true"><?php echo esc_textarea($this->get_option('reverse_private_tag_protect_msg')); ?></textarea>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-7">
            <label><?php esc_html_e('Text to display when comments are protected:', 'wishlist-member'); ?></label>
            <?php $this->tooltip(__('When comments are protected, this text will be displayed rather than the actual comments on a page or post.', 'wishlist-member')); ?>
        </div>
        <div class="col-md-6">
            <div class="form-group ">
                <textarea id="closed_comments_msg-id-jrcp5x4xelkc3px7ch" class="form-control " name="closed_comments_msg" style="min-height: 2em" data-initial="<?php echo esc_attr($this->get_option('closed_comments_msg')); ?>" data-lpignore="true"><?php echo esc_textarea($this->get_option('closed_comments_msg')); ?></textarea>
            </div>
        </div>
    </div>
</div>
