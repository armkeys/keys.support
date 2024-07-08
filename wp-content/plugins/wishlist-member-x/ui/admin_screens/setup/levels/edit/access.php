<div role="tabpanel" class="tab-pane active" id="" data-id="levels_access">
    <div class="content-wrapper">
        
        <div id="expire_options" class="save-section">
            <input type="hidden" name="noexpire" />
            <div class="row expire_settings" data-initial="">
                <div class="col-md-12">
                    <label class="mb-3"><?php esc_html_e('Expiration Options', 'wishlist-member'); ?></label>
                    <?php $this->tooltip(__('<p>Ongoing: An ongoing membership will give access with no specific expiration. A member\'s access will be cancelled if a payment tied to an integration fails to process successfully.</p><p>Fixed Term: When using a fixed term expiration a level can be scheduled to automatically expire after a certain amount of time. It can be a specified number of Days, Weeks, Months or Years.</p><p>Specific Date: When using a Specific Date expiration a level can be scheduled to automatically expire on a specific date. A date is chosen from a calendar and all members will expire on the same date.</p>', 'wishlist-member'), 'xxl'); ?>
                </div>
                <template class="wlm3-form-group">
                    {
                        column : 'col-md-3 col-sm-4 col-xs-6',
                        name  : 'expire_option',
                        value : '0',
                        type  : 'select',
                        style : 'width: 100%',
                        options : [
                            {value : 0, text : 'Ongoing'},
                            {value : 1, text : 'Fixed Term'},
                            {value : 2, text : 'Specific Date'},
                        ],
                    }
                </template>
                <div class="col-md-3 col-sm-4 col-xs-6 expire_option expire_fixed_term" style="display:none">
                    <div class="form-inline -combo-form">
                        <div>
                            <label class="sr-only" for=""><?php esc_html_e('Fixed Term', 'wishlist-member'); ?></label>
                            <div class="input-group">
                                <input type="number" style="width: 35%" min="1" name="expire" class="form-control text-center">
                                <select class="form-control wlm-select" name="calendar" style="width: 65%;">
                                    <option value="Days"><?php esc_html_e('Day(s)', 'wishlist-member'); ?></option>
                                    <option value="Weeks"><?php esc_html_e('Week(s)', 'wishlist-member'); ?></option>
                                    <option value="Months"><?php esc_html_e('Month(s)', 'wishlist-member'); ?></option>
                                    <option value="Years"><?php esc_html_e('Year(s)', 'wishlist-member'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxxl-2 col-md-3 col-sm-4 col-xs-6 expire_option expire_specific_date" style="display:none">
                    <div class="date-ranger">
                        <label class="sr-only" for=""><?php esc_html_e('Specific Date', 'wishlist-member'); ?></label>
                        <div class="date-ranger-container">
                            <input id="DateRangePicker" type="text" name="expire_date" class="form-control" placeholder="" style="max-width: 250px">
                            <i class="wlm-icons">date_range</i>
                        </div>
                    </div>
                </div>
                <div class="col-md-auto col-sm-4 expire_notification" style="display:none">
                    <button data-toggle="modal" data-target="#email-notification-settings" class="btn -primary -condensed" data-notif-setting="expiring" data-notif-title="Expiring Email Notifications">
                        <i class="wlm-icons">settings</i>
                        <span class="text"><?php esc_html_e('Edit Notifications', 'wishlist-member'); ?></span>
                    </button>
                </div>
                <div class="col-md-auto expire_apply" style="display:none">
                    <button class="btn -success -condensed"><?php esc_html_e('Apply', 'wishlist-member'); ?></button>
                    <button class="btn -bare -condensed"><?php esc_html_e('Cancel', 'wishlist-member'); ?></button>
                </div>
            </div>
            <br>
        </div>
        <div class="row">
            <div class="col-md-4">
                <label><?php esc_html_e('Access To', 'wishlist-member'); ?></label>
                <?php $this->tooltip(__('<p>Enabling any of these settings will provide access to all protected content for any member of this level. These settings are helpful when you have one level that should have access to everything.</p><p>These settings do not apply to unprotected content. All members and non-members already have access to all unprotected content.</p>', 'wishlist-member'), 'xxl'); ?>
                <br>
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('All Posts', 'wishlist-member')); ?>',
                        name  : 'allposts',
                        value : 'on',
                        uncheck_value : '',
                        type  : 'toggle-switch',
                        column : 'col-md-12 no-padding'
                    }
                </template>
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('All Pages', 'wishlist-member')); ?>',
                        name  : 'allpages',
                        value : 'on',
                        uncheck_value : '',
                        type  : 'toggle-switch',
                        column : 'col-md-12 no-padding'
                    }
                </template>
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('All Comments', 'wishlist-member')); ?>',
                        name  : 'allcomments',
                        value : 'on',
                        uncheck_value : '',
                        type  : 'toggle-switch',
                        column : 'col-md-12 no-padding'
                    }
                </template>
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('All Categories', 'wishlist-member')); ?>',
                        name  : 'allcategories',
                        value : 'on',
                        uncheck_value : '',
                        type  : 'toggle-switch',
                        column : 'col-md-12 no-padding'
                    }
                </template>
            </div>
            <div class="col-md-8 pt-4 mt-4">
                <?php if ($this->get_option('addto_feature_moved')) : ?>
                    <span class="form-text text-danger help-block float-right mt-4">
                        <a class="float-right dismiss-addto-message" href="#">Dismiss</a>
                        <p class="m-2">
                            The "Add To" and "Remove From" feature has been moved to <a id="show-actions-tab" href="#">Actions tab</a>.
                        </p>
                    </span>
                <?php endif; ?>
            </div>
        </div>
        <div class="panel-footer -content-footer">
            <div class="row">
                <div class="col-md-12 text-right">
                    <?php echo wp_kses_post($tab_footer); ?>
                </div>
            </div>
        </div>
    </div>
</div>
