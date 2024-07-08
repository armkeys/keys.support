<?php
foreach ($member_types as $member_type_id => $member_type) :
    ?>
<div
    data-process="modal"
    id="buddyboss-types-<?php echo esc_attr($member_type['post_id']); ?>-template" 
    data-id="buddyboss-types-<?php echo esc_attr($member_type['post_id']); ?>"
    data-label="buddyboss-types-<?php echo esc_attr($member_type['post_id']); ?>"
    data-title="Editing Profile Type Actions for <strong><?php echo esc_attr($member_type['title']); ?></strong>"
    data-show-default-footer="1"
    data-classes="modal-lg modal-buddyboss-actions"
    style="display:none">
    <div class="body">
        <div class="row">
            <div class="col-md-12">
                <ul class="nav nav-tabs">
                    <li class="active nav-item"><a class="nav-link" data-toggle="tab" href="#buddyboss-type-add-<?php echo esc_attr($member_type_id); ?>">When Added to this Profile Type</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#buddyboss-type-remove-<?php echo esc_attr($member_type_id); ?>">When Removed from this Profile Type</a></li>
                </ul>
            </div>
        </div>
        <div class="tab-content">
            <?php $c_actions = ['add', 'remove']; ?>
            <?php foreach ($c_actions as $c_action) : ?>
                <div class="row tab-pane <?php echo esc_attr('add' === $c_action ? 'active in' : ''); ?>" id="buddyboss-type-<?php echo esc_attr($c_action); ?>-<?php echo esc_attr($member_type_id); ?>">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label><?php esc_html_e('Add to Level', 'wishlist-member'); ?></label>
                            <select class="buddyboss-levels-select" multiple="multiple" data-placeholder="Select levels..." style="width:100%" name="buddyboss_settings[type][<?php echo esc_attr($member_type_id); ?>][<?php echo esc_attr($c_action); ?>][add_level][]"></select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label><?php esc_html_e('Cancel from Level', 'wishlist-member'); ?></label>
                            <select class="buddyboss-levels-select" multiple="multiple" data-placeholder="Select levels..." style="width:100%" name="buddyboss_settings[type][<?php echo esc_attr($member_type_id); ?>][<?php echo esc_attr($c_action); ?>][cancel_level][]"></select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label><?php esc_html_e('Remove from Level', 'wishlist-member'); ?></label>
                            <select class="buddyboss-levels-select" multiple="multiple" data-placeholder="Select levels..." style="width:100%" name="buddyboss_settings[type][<?php echo esc_attr($member_type_id); ?>][<?php echo esc_attr($c_action); ?>][remove_level][]"></select>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
    <?php
endforeach;
?>
