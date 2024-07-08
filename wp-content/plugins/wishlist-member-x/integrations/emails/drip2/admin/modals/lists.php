<?php
foreach ($wpm_levels as $level_id => $level) :
    ?>
<div
    data-process="modal"
    id="drip2-tags-<?php echo esc_attr($level_id); ?>-template" 
    data-id="drip2-tags-<?php echo esc_attr($level_id); ?>"
    data-label="drip2-tags-<?php echo esc_attr($level_id); ?>"
    data-title="Editing <?php echo esc_attr($config['name']); ?> Tags for <?php echo esc_attr($level['name']); ?>"
    data-show-default-footer="1"
    data-classes="modal-lg"
    style="display:none">
    <div class="body">
        <div class="row">
            <div class="col-md-12">
                <ul class="nav nav-tabs">
                    <li class="active nav-item"><a class="nav-link" data-toggle="tab" href="#when-added-<?php echo esc_attr($level_id); ?>"><?php esc_html_e('Added', 'wishlist-member'); ?></a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#when-removed-<?php echo esc_attr($level_id); ?>"><?php esc_html_e('Removed', 'wishlist-member'); ?></a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#when-cancelled-<?php echo esc_attr($level_id); ?>"><?php esc_html_e('Cancelled', 'wishlist-member'); ?></a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#when-reregistered-<?php echo esc_attr($level_id); ?>"><?php esc_html_e('Uncancelled', 'wishlist-member'); ?></a></li>
                </ul>
            </div>
        </div>
        <div class="tab-content">
            <div class="row tab-pane active in" id="when-added-<?php echo esc_attr($level_id); ?>">
                <div class="col-md-12">
                    <div class="form-check pl-0">
                        <input type="checkbox" name="<?php echo esc_attr($level_id); ?>[add][record_event]" value="1" uncheck_value="0">
                        <label><?php esc_html_e('Fire Add Event', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('If enabled, a record of the event will be sent to Drip. This will be displayed in the All Activtiy section for a user in Drip.', 'wishlist-member')); ?></label>
                    </div>
                    <br>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label><?php esc_html_e('Apply Tags', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('Adds the selected Tag(s) to the User when Added to the Level.', 'wishlist-member')); ?></label>
                        <select class="wlm-select drip2-tags-select" multiple="multiple" data-placeholder="Select tags..." style="width:100%" name="<?php echo esc_attr($level_id); ?>[add][apply_tag][]"></select>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label><?php esc_html_e('Remove Tags', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('Removes the selected Tag(s) from the User when Added to the Level.', 'wishlist-member')); ?></label>
                        <select class="wlm-select drip2-tags-select" multiple="multiple" data-placeholder="Select tags..." style="width:100%" name="<?php echo esc_attr($level_id); ?>[add][remove_tag][]"></select>
                    </div>
                </div>
            </div>
            <div class="row tab-pane" id="when-cancelled-<?php echo esc_attr($level_id); ?>">
                <div class="col-md-12">
                    <div class="form-check pl-0">
                        <input type="checkbox" name="<?php echo esc_attr($level_id); ?>[cancel][record_event]" value="1" uncheck_value="0">
                        <label><?php esc_html_e('Fire Cancel Event', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('If enabled, a record of the event will be sent to Drip. This will be displayed in the All Activtiy section for a user in Drip.', 'wishlist-member')); ?></label>
                    </div>
                    <br>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label><?php esc_html_e('Apply Tags', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('Adds the selected Tag(s) to the User when Cancelled from the Level.', 'wishlist-member')); ?></label>
                        <select class="wlm-select drip2-tags-select" multiple="multiple" data-placeholder="Select tags..." style="width:100%" name="<?php echo esc_attr($level_id); ?>[cancel][apply_tag][]"></select>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label><?php esc_html_e('Remove Tags', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('Removes the selected Tag(s) from the User when Cancelled from the Level.', 'wishlist-member')); ?></label>
                        <select class="wlm-select drip2-tags-select" multiple="multiple" data-placeholder="Select tags..." style="width:100%" name="<?php echo esc_attr($level_id); ?>[cancel][remove_tag][]"></select>
                    </div>
                </div>
            </div>
            <div class="row tab-pane" id="when-reregistered-<?php echo esc_attr($level_id); ?>">
                <div class="col-md-12">
                    <div class="form-check pl-0">
                        <input type="checkbox" name="<?php echo esc_attr($level_id); ?>[rereg][record_event]" value="1" uncheck_value="0">
                        <label><?php esc_html_e('Fire Re-Registration Event', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('If enabled, a record of the event will be sent to Drip. This will be displayed in the All Activtiy section for a user in Drip.', 'wishlist-member')); ?></label>
                    </div>
                    <br>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label><?php esc_html_e('Apply Tags', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('Adds the selected Tag(s) to the User when Uncancelled from the Level.', 'wishlist-member')); ?></label>
                        <select class="wlm-select drip2-tags-select" multiple="multiple" data-placeholder="Select tags..." style="width:100%" name="<?php echo esc_attr($level_id); ?>[rereg][apply_tag][]"></select>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label><?php esc_html_e('Remove Tags', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('Removes the selected Tag(s) from the User when Uncancelled from the Level.', 'wishlist-member')); ?></label>
                        <select class="wlm-select drip2-tags-select" multiple="multiple" data-placeholder="Select tags..." style="width:100%" name="<?php echo esc_attr($level_id); ?>[rereg][remove_tag][]"></select>
                    </div>
                </div>
            </div>
            <div class="row tab-pane" id="when-removed-<?php echo esc_attr($level_id); ?>">
                <div class="col-md-12">
                    <div class="form-check pl-0">
                        <input type="checkbox" name="<?php echo esc_attr($level_id); ?>[remove][record_event]" value="1" uncheck_value="0">
                        <label><?php esc_html_e('Fire Remove Event', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('If enabled, a record of the event will be sent to Drip. This will be displayed in the All Activtiy section for a user in Drip.', 'wishlist-member')); ?></label>
                    </div>
                    <br>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label><?php esc_html_e('Apply Tags', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('Adds the selected Tag(s) to the User when Removed from the Level.', 'wishlist-member')); ?></label>
                        <select class="wlm-select drip2-tags-select" multiple="multiple" data-placeholder="Select tags..." style="width:100%" name="<?php echo esc_attr($level_id); ?>[remove][apply_tag][]"></select>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label><?php esc_html_e('Remove Tags', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('Removes the selected Tag(s) from the User when Removed from the Level.', 'wishlist-member')); ?></label>
                        <select class="wlm-select drip2-tags-select" multiple="multiple" data-placeholder="Select tags..." style="width:100%" name="<?php echo esc_attr($level_id); ?>[remove][remove_tag][]"></select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    <?php
endforeach;
?>
