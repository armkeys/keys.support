<?php
foreach ((array) $wpm_levels as $level_id => $level) :
    ?>
<div
    data-process="modal"
    id="elearncommerce-levels-<?php echo esc_attr($level_id); ?>-template"
    data-id="elearncommerce-levels-<?php echo esc_attr($level_id); ?>"
    data-label="elearncommerce-levels-<?php echo esc_attr($level_id); ?>"
    data-title="Editing <?php echo esc_attr($config['name']); ?> Actions for <?php echo esc_attr($level['name']); ?>"
    data-show-default-footer="1"
    data-classes="modal-lg modal-elearncommerce-actions"
    style="display:none">
    <div class="body">
        <div class="row">
            <div class="col-md-12">
                <ul class="nav nav-tabs">
                    <?php foreach ((array) $level_actions as $key => $value) : ?>
                        <li class="<?php echo esc_attr('add' === $key ? 'active' : ''); ?> nav-item"><a class="nav-link" data-toggle="tab" href="#elearncommerce-when-<?php echo esc_attr($key); ?>-<?php echo esc_attr($level_id); ?>"><?php echo esc_html($value); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <div class="tab-content">
            <?php foreach ((array) $level_actions as $key => $value) : ?>
                <div class="row tab-pane <?php echo esc_attr('add' === $key ? 'active in' : ''); ?> px-2" id="elearncommerce-when-<?php echo esc_attr($key); ?>-<?php echo esc_attr($level_id); ?>">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label><?php esc_html_e('Start a Course', 'wishlist-member'); ?></label>
                            <select class="wlm-select elearncommerce-courses-select <?php echo esc_attr($key); ?> apply-course" multiple="multiple" data-placeholder="Select Courses..." style="width:100%" name="elearncommerce_settings[level][<?php echo esc_attr($level_id); ?>][<?php echo esc_attr($key); ?>][apply_course][]"></select>
                        </div>
                    </div>
                    <?php if ('add' === $key) : ?>
                    <div class="col-md-12 add-checkboxes form-group d-none"><div><label><?php esc_html_e('Enroll existing members:', 'wishlist-member'); ?></label></div></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
    <?php
endforeach;
?>
