<?php
foreach ($wpm_levels as $level_id => $level) :
    ?>
<div
    data-process="modal"
    id="sendlane-tags-<?php echo esc_attr($level_id); ?>-template" 
    data-id="sendlane-tags-<?php echo esc_attr($level_id); ?>"
    data-label="sendlane-tags-<?php echo esc_attr($level_id); ?>"
    data-title="Editing <?php echo esc_attr($config['name']); ?> Actions for <?php echo esc_attr($level['name']); ?>"
    data-show-default-footer="1"
    data-classes="modal-lg modal-sendlane-actions"
    style="display:none">
    <div class="body">
        <div class="row">
            <div class="col-md-12">
                <ul class="nav nav-tabs">
                    <li class="active nav-item"><a class="nav-link" data-toggle="tab" href="#sendlane-when-added-<?php echo esc_attr($level_id); ?>"><?php esc_html_e('Added', 'wishlist-member'); ?></a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#sendlane-when-removed-<?php echo esc_attr($level_id); ?>"><?php esc_html_e('Removed', 'wishlist-member'); ?></a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#sendlane-when-cancelled-<?php echo esc_attr($level_id); ?>"><?php esc_html_e('Cancelled', 'wishlist-member'); ?></a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#sendlane-when-reregistered-<?php echo esc_attr($level_id); ?>"><?php esc_html_e('Uncancelled', 'wishlist-member'); ?></a></li>
                </ul>
            </div>
        </div>
        <div class="tab-content">
            <div class="row tab-pane active in" id="sendlane-when-added-<?php echo esc_attr($level_id); ?>">
                <div class="row col-md-12">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><?php esc_html_e('Add to List', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('Adds the User to the selected List(s) when Added to the Level.', 'wishlist-member')); ?></label>
                            <select class="wlm-select sendlane-list-select" data-placeholder="Select list..." data-allow-clear="true" style="width:100%" name="<?php echo esc_attr($level_id); ?>[add][list_add]"></select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><?php esc_html_e('Remove from List', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('Removes the User from the selected List(s) when Added to the Level.', 'wishlist-member')); ?></label>
                            <select class="wlm-select sendlane-list-select" data-placeholder="Select list..." data-allow-clear="true" style="width:100%" name="<?php echo esc_attr($level_id); ?>[add][list_remove]"></select>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label><?php esc_html_e('Apply Tags', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('Adds the selected Tag(s) to the User when Added to the Level.', 'wishlist-member')); ?></label>
                        <select class="wlm-select sendlane-tags-select" multiple="multiple" data-placeholder="Select tags..." style="width:100%" name="<?php echo esc_attr($level_id); ?>[add][apply_tag][]"></select>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label><?php esc_html_e('Remove Tags', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('Removes the selected Tag(s) from the User when Added to the Level.', 'wishlist-member')); ?></label>
                        <select class="wlm-select sendlane-tags-select" multiple="multiple" data-placeholder="Select tags..." style="width:100%" name="<?php echo esc_attr($level_id); ?>[add][remove_tag][]"></select>
                    </div>
                </div>
            </div>
            <div class="row tab-pane" id="sendlane-when-cancelled-<?php echo esc_attr($level_id); ?>">
                <div class="row col-md-12">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><?php esc_html_e('Add to List', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('Adds the User to the selected List(s) when Cancelled from the Level.', 'wishlist-member')); ?></label>
                            <select class="wlm-select sendlane-list-select" data-placeholder="Select list..." data-allow-clear="true" style="width:100%" name="<?php echo esc_attr($level_id); ?>[cancel][list_add]"></select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><?php esc_html_e('Remove from List', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('Removes the User from the selected List(s) when Cancelled from the Level.', 'wishlist-member')); ?></label>
                            <select class="wlm-select sendlane-list-select" data-placeholder="Select list..." data-allow-clear="true" style="width:100%" name="<?php echo esc_attr($level_id); ?>[cancel][list_remove]"></select>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label><?php esc_html_e('Apply Tags', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('Adds the selected Tag(s) to the User when Cancelled from the Level.', 'wishlist-member')); ?></label>
                        <select class="wlm-select sendlane-tags-select" multiple="multiple" data-placeholder="Select tags..." style="width:100%" name="<?php echo esc_attr($level_id); ?>[cancel][apply_tag][]"></select>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label><?php esc_html_e('Remove Tags', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('Removes the selected Tag(s) from the User when Cancelled from the Level.', 'wishlist-member')); ?></label>
                        <select class="wlm-select sendlane-tags-select" multiple="multiple" data-placeholder="Select tags..." style="width:100%" name="<?php echo esc_attr($level_id); ?>[cancel][remove_tag][]"></select>
                    </div>
                </div>
            </div>
            <div class="row tab-pane" id="sendlane-when-reregistered-<?php echo esc_attr($level_id); ?>">
                <div class="row col-md-12">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><?php esc_html_e('Add to List', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('Adds the User to the selected List(s) when Uncancelled from the Level.', 'wishlist-member')); ?></label>
                            <select class="wlm-select sendlane-list-select" data-placeholder="Select list..." data-allow-clear="true" style="width:100%" name="<?php echo esc_attr($level_id); ?>[rereg][list_add]"></select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><?php esc_html_e('Remove from List', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('Removes the User from the selected List(s) when Uncancelled from the Level.', 'wishlist-member')); ?></label>
                            <select class="wlm-select sendlane-list-select" data-placeholder="Select list..." data-allow-clear="true" style="width:100%" name="<?php echo esc_attr($level_id); ?>[rereg][list_remove]"></select>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label><?php esc_html_e('Apply Tags', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('Adds the selected Tag(s) to the User when Uncancelled from the Level.', 'wishlist-member')); ?></label>
                        <select class="wlm-select sendlane-tags-select" multiple="multiple" data-placeholder="Select tags..." style="width:100%" name="<?php echo esc_attr($level_id); ?>[rereg][apply_tag][]"></select>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label><?php esc_html_e('Remove Tags', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('Removes the selected Tag(s) from the User when Uncancelled from the Level.', 'wishlist-member')); ?></label>
                        <select class="wlm-select sendlane-tags-select" multiple="multiple" data-placeholder="Select tags..." style="width:100%" name="<?php echo esc_attr($level_id); ?>[rereg][remove_tag][]"></select>
                    </div>
                </div>
            </div>
            <div class="row tab-pane" id="sendlane-when-removed-<?php echo esc_attr($level_id); ?>">
                <div class="row col-md-12">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><?php esc_html_e('Add to List', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('Adds the User to the selected List(s) when Removed from the Level.', 'wishlist-member')); ?></label>
                            <select class="wlm-select sendlane-list-select" data-placeholder="Select list..." data-allow-clear="true" style="width:100%" name="<?php echo esc_attr($level_id); ?>[remove][list_add]"></select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><?php esc_html_e('Remove from List', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('Removes the User from the selected List(s) when Removed from the Level.', 'wishlist-member')); ?></label>
                            <select class="wlm-select sendlane-list-select" data-placeholder="Select list..." data-allow-clear="true" style="width:100%" name="<?php echo esc_attr($level_id); ?>[remove][list_remove]"></select>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label><?php esc_html_e('Apply Tags', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('Adds the selected Tag(s) to the User when Removed from the Level.', 'wishlist-member')); ?></label>
                        <select class="wlm-select sendlane-tags-select" multiple="multiple" data-placeholder="Select tags..." style="width:100%" name="<?php echo esc_attr($level_id); ?>[remove][apply_tag][]"></select>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label><?php esc_html_e('Remove Tags', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('Removes the selected Tag(s) from the User when Removed from the Level.', 'wishlist-member')); ?></label>
                        <select class="wlm-select sendlane-tags-select" multiple="multiple" data-placeholder="Select tags..." style="width:100%" name="<?php echo esc_attr($level_id); ?>[remove][remove_tag][]"></select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    <?php
endforeach;
?>
