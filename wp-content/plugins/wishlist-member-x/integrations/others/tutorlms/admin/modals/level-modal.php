<?php
$tooltips = [
    'add'    => [
        'add'    => __('Enrolls the User to the selected Course(s) when Added to the Level.', 'wishlist-member'),
        'remove' => __('Enrolls the User to the selected Course(s) when Removed from the Level.', 'wishlist-member'),
        'cancel' => __('Enrolls the User to the selected Course(s) when Cancelled from the Level.', 'wishlist-member'),
        'rereg'  => __('Enrolls the User to the selected Course(s) when Uncancelled from the Level.', 'wishlist-member'),
    ],
    'remove' => [
        'add'    => __('Unenrolls the User from the selected Course(s) when Added to the Level.', 'wishlist-member'),
        'remove' => __('Unenrolls the User from the selected Course(s) when Removed from the Level.', 'wishlist-member'),
        'cancel' => __('Unenrolls the User from the selected Course(s) when Cancelled from the Level.', 'wishlist-member'),
        'rereg'  => __('Unenrolls the User from the selected Course(s) when Uncancelled from the Level.', 'wishlist-member'),
    ],
];
foreach ($wpm_levels as $level_id => $level) :
    ?>
<div
    data-process="modal"
    id="tutorlms-levels-<?php echo esc_attr($level_id); ?>-template" 
    data-id="tutorlms-levels-<?php echo esc_attr($level_id); ?>"
    data-label="tutorlms-levels-<?php echo esc_attr($level_id); ?>"
    data-title="Editing <?php echo esc_attr($config['name']); ?> Actions for <?php echo esc_attr($level['name']); ?>"
    data-show-default-footer="1"
    data-classes="modal-lg modal-tutorlms-actions"
    style="display:none">
    <div class="body">
        <div class="row">
            <div class="col-md-12">
                <ul class="nav nav-tabs">
                    <?php foreach ($level_actions as $key => $value) : ?>
                        <li class="<?php echo esc_attr('add' === $key ? 'active' : ''); ?> nav-item"><a class="nav-link" data-toggle="tab" href="#tutorlms-when-<?php echo esc_attr($key); ?>-<?php echo esc_attr($level_id); ?>"><?php echo esc_html($value); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <div class="tab-content">
            <?php foreach ($level_actions as $key => $value) : ?>
                <div class="row tab-pane <?php echo esc_attr('add' === $key ? 'active in' : ''); ?> px-2" id="tutorlms-when-<?php echo esc_attr($key); ?>-<?php echo esc_attr($level_id); ?>">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>
                                <?php esc_html_e('Enroll in a Course', 'wishlist-member'); ?>
                                <?php wishlistmember_instance()->tooltip($tooltips['add'][ $key ]); ?>
                            </label>
                            <select class="wlm-select tutorlms-courses-select <?php echo esc_attr($key); ?> apply-course" multiple="multiple" data-placeholder="Select Courses..." style="width:100%" name="tutorlms_settings[level][<?php echo esc_attr($level_id); ?>][<?php echo esc_attr($key); ?>][apply_course][]"></select>
                        </div>
                    </div>
                    <?php if ('add' === $key) : ?>
                    <div class="col-md-12 add-checkboxes form-group d-none"><div><label><?php esc_html_e('Enroll existing members:', 'wishlist-member'); ?></label></div></div>
                    <?php endif; ?>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>
                                <?php esc_html_e('Remove from Course', 'wishlist-member'); ?>
                                <?php wishlistmember_instance()->tooltip($tooltips['remove'][ $key ]); ?>
                            </label>
                            <select class="wlm-select tutorlms-courses-select <?php echo esc_attr($key); ?> remove-course" multiple="multiple" data-placeholder="Select Courses..." style="width:100%" name="tutorlms_settings[level][<?php echo esc_attr($level_id); ?>][<?php echo esc_attr($key); ?>][remove_course][]"></select>
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
