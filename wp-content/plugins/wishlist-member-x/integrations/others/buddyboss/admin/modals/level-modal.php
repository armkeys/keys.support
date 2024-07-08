<?php
foreach ($wpm_levels as $level_id => $level) :
    ?>
<div
    data-process="modal"
    id="buddyboss-levels-<?php echo esc_attr($level_id); ?>-template" 
    data-id="buddyboss-levels-<?php echo esc_attr($level_id); ?>"
    data-label="buddyboss-levels-<?php echo esc_attr($level_id); ?>"
    data-title="Editing Level Actions for <strong><?php echo esc_attr($level['name']); ?></strong>"
    data-show-default-footer="1"
    data-classes="modal-lg modal-buddyboss-actions"
    style="display:none">
    <div class="body">
        <div class="row">
            <div class="col-md-12">
                <ul class="nav nav-tabs">
                    <?php foreach ($level_actions as $key => $value) : ?>
                        <li class="<?php echo esc_attr('add' === $key ? 'active' : ''); ?> nav-item"><a class="nav-link" data-toggle="tab" href="#buddyboss-when-<?php echo esc_attr($key); ?>-<?php echo esc_attr($level_id); ?>"><?php echo esc_html($value); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <div class="tab-content">
            <?php foreach ($level_actions as $key => $value) : ?>
                <div class="row tab-pane <?php echo esc_attr('add' === $key ? 'active in' : ''); ?> px-2" id="buddyboss-when-<?php echo esc_attr($key); ?>-<?php echo esc_attr($level_id); ?>">
                    <div class="horizontal-tabs">
                        <div class="row no-gutters">
                            <div class="col-12 col-md-auto">
                                <!-- Nav tabs -->
                                <div class="horizontal-tabs-sidebar">
                                    <ul class="nav nav-tabs -h-tabs flex-column" role="tablist">
                                        <li role="presentation" class="nav-item">
                                            <a href="#level-<?php echo esc_attr($level_id); ?>-<?php echo esc_attr($key); ?>-buddyboss-group" class="nav-link pp-nav-link active" aria-controls="group" role="tab" data-type="group" data-title="Group Actions" data-toggle="tab">Groups</a>
                                        </li>
                                        <li role="presentation" class="nav-item">
                                            <a href="#level-<?php echo esc_attr($level_id); ?>-<?php echo esc_attr($key); ?>-buddyboss-type" class="nav-link pp-nav-link" aria-controls="type" role="tab" data-type="type" data-title="Profile Type Actions" data-toggle="tab">Profile Types</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col">
                                <!-- Tab panes -->
                                <div class="tab-content">
                                        <div role="tabpanel" class="tab-pane active" id="level-<?php echo esc_attr($level_id); ?>-<?php echo esc_attr($key); ?>-buddyboss-group">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label><?php esc_html_e('Add to Group', 'wishlist-member'); ?></label>
                                                    <select class="buddyboss-groups-select" multiple="multiple" data-placeholder="Select Groups..." style="width:100%" name="buddyboss_settings[level][<?php echo esc_attr($level_id); ?>][<?php echo esc_attr($key); ?>][apply_group][]"></select>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label><?php esc_html_e('Remove from Group', 'wishlist-member'); ?></label>
                                                    <select class="buddyboss-groups-select" multiple="multiple" data-placeholder="Select Groups..." style="width:100%" name="buddyboss_settings[level][<?php echo esc_attr($level_id); ?>][<?php echo esc_attr($key); ?>][remove_group][]"></select>
                                                </div>
                                            </div>
                                        </div>
                                        <div role="tabpanel" class="tab-pane" id="level-<?php echo esc_attr($level_id); ?>-<?php echo esc_attr($key); ?>-buddyboss-type">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label><?php esc_html_e('Add Profile Type', 'wishlist-member'); ?></label>
                                                    <select class="buddyboss-types-select" multiple="multiple" data-placeholder="Select Profile Types..." style="width:100%" name="buddyboss_settings[level][<?php echo esc_attr($level_id); ?>][<?php echo esc_attr($key); ?>][apply_type][]"></select>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label><?php esc_html_e('Remove Profile Type', 'wishlist-member'); ?></label>
                                                    <select class="buddyboss-types-select" multiple="multiple" data-placeholder="Select Profile Types..." style="width:100%" name="buddyboss_settings[level][<?php echo esc_attr($level_id); ?>][<?php echo esc_attr($key); ?>][remove_type][]"></select>
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







<?php
foreach ($wpm_levels as $level_id => $level) :
    break;
    ?>
<div
    data-process="modal"
    id="buddyboss-levels-<?php echo esc_attr($level_id); ?>-template" 
    data-id="buddyboss-levels-<?php echo esc_attr($level_id); ?>"
    data-label="buddyboss-levels-<?php echo esc_attr($level_id); ?>"
    data-title="Editing Level Actions for <strong><?php echo esc_attr($level['name']); ?></strong>"
    data-show-default-footer="1"
    data-classes="modal-lg modal-buddyboss-actions"
    style="display:none">
    <div class="body">
        <div class="row">
            <div class="col-md-12">
                <ul class="nav nav-tabs">
                    <?php foreach ($level_actions as $key => $value) : ?>
                        <li class="<?php echo esc_attr('add' === $key ? 'active' : ''); ?> nav-item"><a class="nav-link" data-toggle="tab" href="#buddyboss-when-<?php echo esc_attr($key); ?>-<?php echo esc_attr($level_id); ?>"><?php echo esc_html($value); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <div class="tab-content">
            <?php foreach ($level_actions as $key => $value) : ?>
                <div class="row tab-pane <?php echo esc_attr('add' === $key ? 'active in' : ''); ?> px-2" id="buddyboss-when-<?php echo esc_attr($key); ?>-<?php echo esc_attr($level_id); ?>">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label><?php esc_html_e('Add to Group', 'wishlist-member'); ?></label>
                                <select class="buddyboss-groups-select" multiple="multiple" data-placeholder="Select Groups..." style="width:100%" name="buddyboss_settings[level][<?php echo esc_attr($level_id); ?>][<?php echo esc_attr($key); ?>][apply_group][]"></select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label><?php esc_html_e('Remove from Group', 'wishlist-member'); ?></label>
                                <select class="buddyboss-groups-select" multiple="multiple" data-placeholder="Select Groups..." style="width:100%" name="buddyboss_settings[level][<?php echo esc_attr($level_id); ?>][<?php echo esc_attr($key); ?>][remove_group][]"></select>
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
