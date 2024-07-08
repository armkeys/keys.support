<?php
foreach ($wpm_levels as $level_id => $level) :
    ?>
<div
    data-process="modal"
    id="lifterlms-levels-<?php echo esc_attr($level_id); ?>-template" 
    data-id="lifterlms-levels-<?php echo esc_attr($level_id); ?>"
    data-label="lifterlms-levels-<?php echo esc_attr($level_id); ?>"
    data-title="Editing <?php echo esc_attr($config['name']); ?> Actions for <?php echo esc_attr($level['name']); ?>"
    data-show-default-footer="1"
    data-classes="modal-lg modal-lifterlms-actions"
    style="display:none">
    <div class="body">
        <div class="row">
            <div class="col-md-12">
                <ul class="nav nav-tabs">
                    <?php foreach ($level_actions as $key => $value) : ?>
                        <li class="<?php echo esc_attr('add' === $key ? 'active' : ''); ?> nav-item"><a class="nav-link" data-toggle="tab" href="#lifterlms-when-<?php echo esc_attr($key); ?>-<?php echo esc_attr($level_id); ?>"><?php echo esc_html($value); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <div class="tab-content">
            <?php foreach ($level_actions as $key => $value) : ?>
                <div class="row tab-pane <?php echo esc_attr('add' === $key ? 'active in' : ''); ?> px-2" id="lifterlms-when-<?php echo esc_attr($key); ?>-<?php echo esc_attr($level_id); ?>">
                    <div class="horizontal-tabs">
                        <div class="row no-gutters">
                            <div class="col-12 col-md-auto">
                                <!-- Nav tabs -->
                                <div class="horizontal-tabs-sidebar">
                                    <ul class="nav nav-tabs -h-tabs flex-column" role="tablist">
                                        <li role="presentation" class="nav-item">
                                            <a href="#level-<?php echo esc_attr($level_id); ?>-<?php echo esc_attr($key); ?>-lifterlms-course" class="nav-link pp-nav-link active" aria-controls="course" role="tab" data-type="course" data-title="Course Actions" data-toggle="tab">Course</a>
                                        </li>
                                        <li role="presentation" class="nav-item">
                                            <a href="#level-<?php echo esc_attr($level_id); ?>-<?php echo esc_attr($key); ?>-lifterlms-membership" class="nav-link pp-nav-link" aria-controls="membership" role="tab" data-type="membership" data-title="Lifter Membership Actions" data-toggle="tab">Lifter Membership</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col">
                                <!-- Tab panes -->
                                <div class="tab-content">
                                        <div role="tabpanel" class="tab-pane active" id="level-<?php echo esc_attr($level_id); ?>-<?php echo esc_attr($key); ?>-lifterlms-course">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label><?php esc_html_e('Enroll in Course', 'wishlist-member'); ?></label>
                                                    <select class="wlm-select lifterlms-courses-select <?php echo esc_attr($key); ?> apply-course" multiple="multiple" data-placeholder="Select Courses..." style="width:100%" name="lifterlms_settings[level][<?php echo esc_attr($level_id); ?>][<?php echo esc_attr($key); ?>][apply_course][]"></select>
                                                </div>
                                            </div>
                                            <?php if ('add' === $key) : ?>
                                            <div class="col-md-12 add-checkboxes form-group d-none"><div><label><?php esc_html_e('Enroll existing members:', 'wishlist-member'); ?></label></div></div>
                                            <?php endif; ?>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label><?php esc_html_e('Remove from Course', 'wishlist-member'); ?></label>
                                                    <select class="wlm-select lifterlms-courses-select <?php echo esc_attr($key); ?> remove-course" multiple="multiple" data-placeholder="Select Courses..." style="width:100%" name="lifterlms_settings[level][<?php echo esc_attr($level_id); ?>][<?php echo esc_attr($key); ?>][remove_course][]"></select>
                                                </div>
                                            </div>
                                        </div>
                                        <div role="tabpanel" class="tab-pane" id="level-<?php echo esc_attr($level_id); ?>-<?php echo esc_attr($key); ?>-lifterlms-membership">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label><?php esc_html_e('Add to Lifter Membership', 'wishlist-member'); ?></label>
                                                    <select class="wlm-select lifterlms-memberships-select" multiple="multiple" data-placeholder="Select Memberships..." style="width:100%" name="lifterlms_settings[level][<?php echo esc_attr($level_id); ?>][<?php echo esc_attr($key); ?>][apply_membership][]"></select>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label><?php esc_html_e('Remove from Lifter Membership', 'wishlist-member'); ?></label>
                                                    <select class="wlm-select lifterlms-memberships-select" multiple="multiple" data-placeholder="Select Memberships..." style="width:100%" name="lifterlms_settings[level][<?php echo esc_attr($level_id); ?>][<?php echo esc_attr($key); ?>][remove_membership][]"></select>
                                                </div>
                                            </div>
                                        </div>
                                </div>
                            </div>
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
