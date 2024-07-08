<?php
foreach ((array) $wpm_levels as $level_id => $level) :
    ?>
<div
    data-process="modal"
    id="learndash-levels-<?php echo esc_attr($level_id); ?>-template"
    data-id="learndash-levels-<?php echo esc_attr($level_id); ?>"
    data-label="learndash-levels-<?php echo esc_attr($level_id); ?>"
    data-title="Editing <?php echo esc_attr($config['name']); ?> Actions for <?php echo esc_attr($level['name']); ?>"
    data-show-default-footer="1"
    data-classes="modal-lg modal-learndash-actions"
    style="display:none">
    <div class="body">
        <div class="row">
            <div class="col-md-12">
                <ul class="nav nav-tabs">
                    <?php foreach ((array) $level_actions as $key => $value) : ?>
                        <li class="<?php echo esc_attr('add' === $key ? 'active' : ''); ?> nav-item"><a class="nav-link" data-toggle="tab" href="#learndash-when-<?php echo esc_attr($key); ?>-<?php echo esc_attr($level_id); ?>"><?php echo esc_html($value); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <div class="tab-content">
            <?php foreach ((array) $level_actions as $key => $value) : ?>
                <div class="row tab-pane <?php echo esc_attr('add' === $key ? 'active in' : ''); ?> px-2" id="learndash-when-<?php echo esc_attr($key); ?>-<?php echo esc_attr($level_id); ?>">
                    <div class="horizontal-tabs">
                        <div class="row no-gutters">
                            <div class="col-12 col-md-auto">
                                <!-- Nav tabs -->
                                <div class="horizontal-tabs-sidebar">
                                    <ul class="nav nav-tabs -h-tabs flex-column" role="tablist">
                                        <li role="presentation" class="nav-item">
                                            <a href="#level-<?php echo esc_attr($level_id); ?>-<?php echo esc_attr($key); ?>-learndash-course" class="nav-link pp-nav-link active" aria-controls="course" role="tab" data-type="course" data-title="Course Actions" data-toggle="tab">Course</a>
                                        </li>
                                        <li role="presentation" class="nav-item">
                                            <a href="#level-<?php echo esc_attr($level_id); ?>-<?php echo esc_attr($key); ?>-learndash-group" class="nav-link pp-nav-link" aria-controls="group" role="tab" data-type="group" data-title="Group Actions" data-toggle="tab">Group</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col">
                                <!-- Tab panes -->
                                <div class="tab-content">
                                        <div role="tabpanel" class="tab-pane active" id="level-<?php echo esc_attr($level_id); ?>-<?php echo esc_attr($key); ?>-learndash-course">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label><?php esc_html_e('Enroll in Course', 'wishlist-member'); ?></label>
                                                    <select class="learndash-courses-select <?php echo esc_attr($key); ?> apply-course" multiple="multiple" data-placeholder="Select Courses..." style="width:100%" name="learndash_settings[level][<?php echo esc_attr($level_id); ?>][<?php echo esc_attr($key); ?>][apply_course][]"></select>
                                                </div>
                                            </div>
                                            <?php if ('add' === $key) : ?>
                                            <div class="col-md-12 add-checkboxes form-group d-none"><div>
                                                <label>
                                                    <?php esc_html_e('Enroll existing members:', 'wishlist-member'); ?>
                                                    <?php wishlistmember_instance()->tooltip(__('If enabled, any existing members in the level will be enrolled in the set course(s).', 'wishlist-member'), 'lg'); ?>
                                                </label>

                                            </div></div>
                                            <?php endif; ?>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label><?php esc_html_e('Remove from Course', 'wishlist-member'); ?></label>
                                                    <select class="learndash-courses-select <?php echo esc_attr($key); ?> remove-course" multiple="multiple" data-placeholder="Select Courses..." style="width:100%" name="learndash_settings[level][<?php echo esc_attr($level_id); ?>][<?php echo esc_attr($key); ?>][remove_course][]"></select>
                                                </div>
                                            </div>
                                        </div>
                                        <div role="tabpanel" class="tab-pane" id="level-<?php echo esc_attr($level_id); ?>-<?php echo esc_attr($key); ?>-learndash-group">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label><?php esc_html_e('Add to Group', 'wishlist-member'); ?></label>
                                                    <select class="learndash-groups-select" multiple="multiple" data-placeholder="Select Groups..." style="width:100%" name="learndash_settings[level][<?php echo esc_attr($level_id); ?>][<?php echo esc_attr($key); ?>][apply_group][]"></select>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label><?php esc_html_e('Remove from Group', 'wishlist-member'); ?></label>
                                                    <select class="learndash-groups-select" multiple="multiple" data-placeholder="Select Groups..." style="width:100%" name="learndash_settings[level][<?php echo esc_attr($level_id); ?>][<?php echo esc_attr($key); ?>][remove_group][]"></select>
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
