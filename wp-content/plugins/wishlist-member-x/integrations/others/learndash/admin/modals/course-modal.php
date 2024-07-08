<?php
foreach ((array) $courses as $course_id => $course) :
    ?>
<div
    data-process="modal"
    id="learndash-course-<?php echo esc_attr($course_id); ?>-template" 
    data-id="learndash-course-<?php echo esc_attr($course_id); ?>"
    data-label="learndash-course-<?php echo esc_attr($course_id); ?>"
    data-title="Editing <?php echo esc_attr($config['name']); ?> Actions for <?php echo esc_attr($course['title']); ?>"
    data-show-default-footer="1"
    data-classes="modal-lg modal-learndash-actions"
    style="display:none">
    <div class="body">
        <div class="row">
            <div class="col-md-12">
                <ul class="nav nav-tabs">
                    <li class="active nav-item"><a class="nav-link" data-toggle="tab" href="#learndash-course-add-<?php echo esc_attr($course_id); ?>">When Added to a Course</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#learndash-course-complete-<?php echo esc_attr($course_id); ?>">When Course is Completed</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#learndash-course-remove-<?php echo esc_attr($course_id); ?>">When Removed from a Course</a></li>
                </ul>
            </div>
        </div>
        <div class="tab-content">
            <?php $c_actions = ['add', 'complete', 'remove']; ?>
            <?php foreach ($c_actions as $c_action) : ?>
                <div class="row tab-pane <?php echo esc_attr('add' === $c_action ? 'active in' : ''); ?>" id="learndash-course-<?php echo esc_attr($c_action); ?>-<?php echo esc_attr($course_id); ?>">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label><?php esc_html_e('Add to Level', 'wishlist-member'); ?></label>
                            <select class="learndash-levels-select" multiple="multiple" data-placeholder="Select levels..." style="width:100%" name="learndash_settings[course][<?php echo esc_attr($course_id); ?>][<?php echo esc_attr($c_action); ?>][add_level][]"></select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label><?php esc_html_e('Cancel from Level', 'wishlist-member'); ?></label>
                            <select class="learndash-levels-select" multiple="multiple" data-placeholder="Select levels..." style="width:100%" name="learndash_settings[course][<?php echo esc_attr($course_id); ?>][<?php echo esc_attr($c_action); ?>][cancel_level][]"></select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label><?php esc_html_e('Remove from Level', 'wishlist-member'); ?></label>
                            <select class="learndash-levels-select" multiple="multiple" data-placeholder="Select levels..." style="width:100%" name="learndash_settings[course][<?php echo esc_attr($course_id); ?>][<?php echo esc_attr($c_action); ?>][remove_level][]"></select>
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
