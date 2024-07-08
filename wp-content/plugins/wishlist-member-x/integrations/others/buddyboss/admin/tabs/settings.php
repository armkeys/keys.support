<div class="row">
    <div class="col-sm-8 col-md-6 col-xxl-4 col-xxxl-3">
        <template class="wlm3-form-group">
            {
                label : '<?php echo esc_js(__('Enable Default Settings for New Groups', 'wishlist-member')); ?>',
                name  : 'wlm_bb_group_default',
                value : '1',
                checked_value : '<?php echo esc_js($wlm_bb_group_default); ?>',
                uncheck_value : '0',
                class : 'wlm_toggle-switch notification-switch',
                type  : 'toggle-adjacent-disable',
                tooltip : '<?php echo esc_js(__('If a Group is created on the fly in the BuddyBoss Platform (possibly by another plugin), you have the option to pre-configure the Membership Level Actions for these new Groups.', 'wishlist-member')); ?>'
            }
        </template>
        <input type="hidden" name="action" value="admin_actions" />
        <input type="hidden" name="WishListMemberAction" value="save" />
    </div>
    <div class="col">
        <button href="#" data-toggle="modal" data-target="#buddyboss-group-default" id="wlm_bb_group_default_btn" class="btn -primary -condensed edit-notification <?php echo esc_attr($wlm_bb_group_default && '1' == $wlm_bb_group_default ? '' : '-disable'); ?>">
            <i class="wlm-icons">settings</i>
            <span><?php esc_html_e('Configure', 'wishlist-member'); ?></span>
        </button>
    </div>
</div>
<div
    data-process="modal"
    id="buddyboss-group-default-template"
    data-id="buddyboss-group-default"
    data-label="buddyboss-group-default"
    data-title="Default Group Actions"
    data-show-default-footer="1"
    data-classes="modal-lg modal-buddyboss-actions"
    style="display:none">
    <div class="body">
        <div class="row">
            <div class="col-md-12">
                <ul class="nav nav-tabs">
                    <li class="active nav-item"><a class="nav-link" data-toggle="tab" href="#buddyboss-group-add-default">When Added to a Group</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#buddyboss-group-remove-default">When Removed from a Group</a></li>
                </ul>
            </div>
        </div>
        <div class="tab-content">
            <?php $c_actions = ['add', 'remove']; ?>
            <?php foreach ($c_actions as $c_action) : ?>
                <div class="row tab-pane <?php echo esc_attr('add' === $c_action ? 'active in' : ''); ?>" id="buddyboss-group-<?php echo esc_attr($c_action); ?>-default">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label><?php esc_html_e('Add to Level', 'wishlist-member'); ?></label>
                            <select class="wlm-select buddyboss-levels-select" multiple="multiple" data-placeholder="Select levels..." style="width:100%" name="buddyboss_settings[group][default][<?php echo esc_attr($c_action); ?>][add_level][]"></select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label><?php esc_html_e('Cancel from Level', 'wishlist-member'); ?></label>
                            <select class="wlm-select buddyboss-levels-select" multiple="multiple" data-placeholder="Select levels..." style="width:100%" name="buddyboss_settings[group][default][<?php echo esc_attr($c_action); ?>][cancel_level][]"></select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label><?php esc_html_e('Remove from Level', 'wishlist-member'); ?></label>
                            <select class="wlm-select buddyboss-levels-select" multiple="multiple" data-placeholder="Select levels..." style="width:100%" name="buddyboss_settings[group][default][<?php echo esc_attr($c_action); ?>][remove_level][]"></select>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php if ($is_member_type_enabled) : ?>
<div class="row">
    <div class="col-sm-8 col-md-6 col-xxl-4 col-xxxl-3">
        <template class="wlm3-form-group">
            {
                label : '<?php echo esc_js(__('Enable Default Settings for New Profile Type', 'wishlist-member')); ?>',
                name  : 'wlm_bb_ptype_default',
                value : '1',
                checked_value : '<?php echo esc_js($wlm_bb_ptype_default); ?>',
                uncheck_value : '0',
                class : 'wlm_toggle-switch notification-switch',
                type  : 'toggle-adjacent-disable',
                tooltip : '<?php echo esc_js(__('If a Profile Type is created on the fly in the BuddyBoss Platform (possibly by another plugin), you have the option to pre-configure the Membership Level Actions for these new Profile Types.', 'wishlist-member')); ?>'
            }
        </template>
        <input type="hidden" name="action" value="admin_actions" />
        <input type="hidden" name="WishListMemberAction" value="save" />
    </div>
    <div class="col">
        <button href="#" data-toggle="modal" data-target="#buddyboss-ptype-default" id="wlm_bb_ptype_default_btn" class="btn -primary -condensed edit-notification <?php echo esc_attr($wlm_bb_ptype_default && '1' == $wlm_bb_ptype_default ? '' : '-disable'); ?>">
            <i class="wlm-icons">settings</i>
            <span><?php esc_html_e('Configure', 'wishlist-member'); ?></span>
        </button>
    </div>
</div>

<div
    data-process="modal"
    id="buddyboss-ptype-default-template"
    data-id="buddyboss-ptype-default"
    data-label="buddyboss-ptype-default"
    data-title="Default Profile Type Actions"
    data-show-default-footer="1"
    data-classes="modal-lg modal-buddyboss-actions"
    style="display:none">
    <div class="body">
        <div class="row">
            <div class="col-md-12">
                <ul class="nav nav-tabs">
                    <li class="active nav-item"><a class="nav-link" data-toggle="tab" href="#buddyboss-ptype-add-default">When Added to this Profile Type</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#buddyboss-ptype-remove-default">When Removed from this Profile Type</a></li>
                </ul>
            </div>
        </div>
        <div class="tab-content">
            <?php $c_actions = ['add', 'remove']; ?>
            <?php foreach ($c_actions as $c_action) : ?>
                <div class="row tab-pane <?php echo esc_attr('add' === $c_action ? 'active in' : ''); ?>" id="buddyboss-ptype-<?php echo esc_attr($c_action); ?>-default">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label><?php esc_html_e('Add to Level', 'wishlist-member'); ?></label>
                            <select class="wlm-select buddyboss-levels-select" multiple="multiple" data-placeholder="Select levels..." style="width:100%" name="buddyboss_settings[ptype][default][<?php echo esc_attr($c_action); ?>][add_level][]"></select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label><?php esc_html_e('Cancel from Level', 'wishlist-member'); ?></label>
                            <select class="wlm-select buddyboss-levels-select" multiple="multiple" data-placeholder="Select levels..." style="width:100%" name="buddyboss_settings[ptype][default][<?php echo esc_attr($c_action); ?>][cancel_level][]"></select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label><?php esc_html_e('Remove from Level', 'wishlist-member'); ?></label>
                            <select class="wlm-select buddyboss-levels-select" multiple="multiple" data-placeholder="Select levels..." style="width:100%" name="buddyboss_settings[ptype][default][<?php echo esc_attr($c_action); ?>][remove_level][]"></select>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>
