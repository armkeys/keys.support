<div data-process="modal" id="activecampaign-tag-modal-template" data-id="activecampaign-tag-modal" data-label="activecampaign-tag-modal" data-title="Editing Tag Actions <span></span>" data-show-default-footer="1" data-classes="modal-lg modal-activecampaign-actions"
    style="display:none">
    <div class="body">
        <div class="row form-group">
            <input type="hidden" name="parent_keys[]" value="tag_actions">
            <label class="col-auto col-form-label"><?php esc_html_e('Tag', 'wishlist-member'); ?></label>
            <div class="col">
                <select id="activecampaign-tag-id-select" class="form-control activecampaign-tag-actions-select" name="parent_keys[]" data-placeholder="<?php esc_attr_e('Select a Tag', 'wishlist-member'); ?>"  style="width: 100%; font-size: 1.05rem;"></select>
            </div>
        </div>
        <div id="activecampaign-tag-actions">
            <ul class="nav nav-tabs">
                <li class="active nav-item"><a class="nav-link" data-toggle="tab" href="#activecampaign-tag-add"><?php esc_html_e('When this Tag is Applied', 'wishlist-member'); ?></a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#activecampaign-tag-remove"><?php esc_html_e('When this Tag is Removed', 'wishlist-member'); ?></a></li>
            </ul>
            <div class="tab-content">
                <?php $c_actions = ['add', 'remove']; ?>
                <?php foreach ($c_actions as $c_action) : ?>
                <div class="tab-pane <?php echo esc_attr('add' === $c_action ? 'active in' : ''); ?>" id="activecampaign-tag-<?php echo esc_attr($c_action); ?>">
                    <div class="horizontal-tabs">
                        <div class="row no-gutters">
                            <div class="col-12 col-md-auto">
                                <!-- Nav tabs -->
                                <div class="horizontal-tabs-sidebar" style="min-width: 120px;">
                                    <ul class="nav nav-tabs -h-tabs flex-column" role="tablist">
                                        <li role="presentation" class="nav-item">
                                            <a href="#-<?php echo esc_attr($c_action); ?>-activecampaigntag-level" class="nav-link pp-nav-link active" aria-controls="level" role="tab" data-type="level" data-title="Levels" data-toggle="tab"><?php esc_html_e('Levels', 'wishlist-member'); ?></a>
                                        </li>
                                        <li role="presentation" class="nav-item">
                                            <a href="#-<?php echo esc_attr($c_action); ?>-activecampaigntag-ppp" class="nav-link pp-nav-link" aria-controls="ppp" role="tab" data-type="ppp" data-title="Pay Per Post" data-toggle="tab"><?php esc_html_e('Pay Per Post', 'wishlist-member'); ?></a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col">
                                <!-- Tab panes -->
                                <div class="tab-content">
                                    <div role="tabpanel" class="tab-pane active" id="-<?php echo esc_attr($c_action); ?>-activecampaigntag-level">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label><?php esc_html_e('Add to Level', 'wishlist-member'); ?></label>
                                                <select class="activecampaign-levels-select wlm-select" multiple="multiple" data-placeholder="Select levels..." style="width:100%" name="<?php echo esc_attr($c_action); ?>[add_level][]"></select>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label><?php esc_html_e('Remove from Level', 'wishlist-member'); ?></label>
                                                <select class="activecampaign-levels-select wlm-select" multiple="multiple" data-placeholder="Select levels..." style="width:100%" name="<?php echo esc_attr($c_action); ?>[remove_level][]"></select>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label><?php esc_html_e('Cancel from Level', 'wishlist-member'); ?></label>
                                                <select class="activecampaign-levels-select wlm-select" multiple="multiple" data-placeholder="Select levels..." style="width:100%" name="<?php echo esc_attr($c_action); ?>[cancel_level][]"></select>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label><?php esc_html_e('Uncancel from Level', 'wishlist-member'); ?></label>
                                                <select class="activecampaign-levels-select wlm-select" multiple="multiple" data-placeholder="Select levels..." style="width:100%" name="<?php echo esc_attr($c_action); ?>[uncancel_level][]"></select>
                                            </div>
                                        </div>
                                    </div>
                                    <div role="tabpanel" class="tab-pane" id="-<?php echo esc_attr($c_action); ?>-activecampaigntag-ppp">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label><?php esc_html_e('Add Pay Per Post', 'wishlist-member'); ?></label>
                                                <select class="activecampaign-levels-select-ppp" multiple="multiple" data-placeholder="Select levels..." style="width:100%" name="<?php echo esc_attr($c_action); ?>[add_ppp][]"></select>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label><?php esc_html_e('Remove Pay Per Post', 'wishlist-member'); ?></label>
                                                <select class="activecampaign-levels-select-ppp" multiple="multiple" data-placeholder="Select levels..." style="width:100%" name="<?php echo esc_attr($c_action); ?>[remove_ppp][]"></select>
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
</div>
