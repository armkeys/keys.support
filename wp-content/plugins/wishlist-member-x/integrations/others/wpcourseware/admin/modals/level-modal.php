<?php
foreach ($wpm_levels as $level_id => $level) :
    ?>
<div
    data-process="modal"
    id="wpcourseware-levels-<?php echo esc_attr($level_id); ?>-template"
    data-id="wpcourseware-levels-<?php echo esc_attr($level_id); ?>"
    data-label="wpcourseware-levels-<?php echo esc_attr($level_id); ?>"
    data-title="Editing <?php echo esc_attr($config['name']); ?> Actions for <?php echo esc_attr($level['name']); ?>"
    data-show-default-footer="1"
    data-classes="modal-lg modal-wpcourseware-actions"
    style="display:none">
    <div class="body">
        <div class="row">
            <div class="col-md-12">
                <ul class="nav nav-tabs">
                    <li class="active nav-item"><a class="nav-link" data-toggle="tab" href="#wpcourseware-when-added-<?php echo esc_attr($level_id); ?>"><?php esc_html_e('Added', 'wishlist-member'); ?></a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#wpcourseware-when-removed-<?php echo esc_attr($level_id); ?>"><?php esc_html_e('Removed', 'wishlist-member'); ?></a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#wpcourseware-when-cancelled-<?php echo esc_attr($level_id); ?>"><?php esc_html_e('Cancelled', 'wishlist-member'); ?></a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#wpcourseware-when-reregistered-<?php echo esc_attr($level_id); ?>"><?php esc_html_e('Uncancelled', 'wishlist-member'); ?></a></li>
                </ul>
            </div>
        </div>
        <div class="tab-content">
            <div class="row tab-pane active in" id="wpcourseware-when-added-<?php echo esc_attr($level_id); ?>">
                <div class="col-md-12">
                    <div class="form-group">
                        <label><?php esc_html_e('Enroll in Course', 'wishlist-member'); ?></label>
                        <select class="wlm-select wpcourseware-courses-select add apply-course" multiple="multiple" data-placeholder="Select Courses..." style="width:100%" name="wpcourseware_settings[level][<?php echo esc_attr($level_id); ?>][add][apply_course][]"></select>
                    </div>
                </div>
                <div class="col-md-12 add-checkboxes form-group d-none">
                    <div>
                        <label>
                            <?php esc_html_e('Enroll existing members:', 'wishlist-member'); ?>
                            <?php $this->tooltip(__('If enabled, any existing members in the level will be enrolled in the set course(s).', 'wishlist-member')); ?>
                        </label>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label><?php esc_html_e('Remove from Course', 'wishlist-member'); ?></label>
                        <select class="wlm-select wpcourseware-courses-select add remove-course" multiple="multiple" data-placeholder="Select Courses..." style="width:100%" name="wpcourseware_settings[level][<?php echo esc_attr($level_id); ?>][add][remove_course][]"></select>
                    </div>
                </div>
            </div>
            <div class="row tab-pane" id="wpcourseware-when-cancelled-<?php echo esc_attr($level_id); ?>">
                <div class="col-md-12">
                    <div class="form-group">
                        <label><?php esc_html_e('Enroll in Course', 'wishlist-member'); ?></label>
                        <select class="wlm-select wpcourseware-courses-select" multiple="multiple" data-placeholder="Select Courses..." style="width:100%" name="wpcourseware_settings[level][<?php echo esc_attr($level_id); ?>][cancel][apply_course][]"></select>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label><?php esc_html_e('Remove from Course', 'wishlist-member'); ?></label>
                        <select class="wlm-select wpcourseware-courses-select" multiple="multiple" data-placeholder="Select Courses..." style="width:100%" name="wpcourseware_settings[level][<?php echo esc_attr($level_id); ?>][cancel][remove_course][]"></select>
                    </div>
                </div>
            </div>
            <div class="row tab-pane" id="wpcourseware-when-reregistered-<?php echo esc_attr($level_id); ?>">
                <div class="col-md-12">
                    <div class="form-group">
                        <label><?php esc_html_e('Enroll in Course', 'wishlist-member'); ?></label>
                        <select class="wlm-select wpcourseware-courses-select" multiple="multiple" data-placeholder="Select Courses..." style="width:100%" name="wpcourseware_settings[level][<?php echo esc_attr($level_id); ?>][rereg][apply_course][]"></select>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label><?php esc_html_e('Remove from Course', 'wishlist-member'); ?></label>
                        <select class="wlm-select wpcourseware-courses-select" multiple="multiple" data-placeholder="Select Courses..." style="width:100%" name="wpcourseware_settings[level][<?php echo esc_attr($level_id); ?>][rereg][remove_course][]"></select>
                    </div>
                </div>
            </div>
            <div class="row tab-pane" id="wpcourseware-when-removed-<?php echo esc_attr($level_id); ?>">
                <div class="col-md-12">
                    <div class="form-group">
                        <label><?php esc_html_e('Enroll in Course', 'wishlist-member'); ?></label>
                        <select class="wlm-select wpcourseware-courses-select" multiple="multiple" data-placeholder="Select Courses..." style="width:100%" name="wpcourseware_settings[level][<?php echo esc_attr($level_id); ?>][remove][apply_course][]"></select>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label><?php esc_html_e('Remove from Course', 'wishlist-member'); ?></label>
                        <select class="wlm-select wpcourseware-courses-select" multiple="multiple" data-placeholder="Select Courses..." style="width:100%" name="wpcourseware_settings[level][<?php echo esc_attr($level_id); ?>][remove][remove_course][]"></select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    <?php
endforeach;
?>
