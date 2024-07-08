<?php

/**
 * Wishlist Member Content Control Settings.
 *
 * @package WishListMember/Features/ContentControl
 */

?>

<div class="page-header">
    <div class="row">
        <div class="col-md-9 col-sm-9 col-xs-8">
            <h2 class="page-title">
                <?php esc_html_e('Content Control Settings', 'wishlist-member'); ?>
            </h2>
        </div>
        <div class="col-md-3 col-sm-3 col-xs-4">
            <?php require wishlistmember_instance()->plugin_dir3 . '/helpers/header-icons.php'; ?>
        </div>
    </div>
</div>
<div class="content-wrapper">
<?php if (wishlistmember_instance()->content_control->old_contentcontrol_active) : ?>
    <div class="row">
        <div class="col-sm-12 col-md-12 col-xl-12 col-xxl-12 col-xxxl-12">
            <p class="alert alert-danger">
                Please deactivate the <strong>WishList Content Control</strong> plugin in order to use this feature.<br />
                Once the plugin has been deactivated, all settings will be retained and transferred to the Content Control section of WishList Member.
            </p>
        </div>
    </div>
<?php else : ?>
    <div class="row">
        <?php $option_val = wishlistmember_instance()->get_option('enable_content_scheduler'); ?>
        <div class="col-sm-8 col-md-5 col-xl-6 col-xxl-4 col-xxxl-3">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Enable Content Scheduler', 'wishlist-member')); ?>',
                    name  : 'enable_content_scheduler',
                    value : '1',
                    checked_value : '<?php echo esc_js($option_val); ?>',
                    uncheck_value : '0',
                    class : 'wlm_toggle-switch enable-content-control-switch',
                    type  : 'toggle-adjacent-disable',
                    option_type: 'scheduler',
                    option_value : '<?php echo esc_js($option_val); ?>',
                    tooltip: '<?php echo esc_js(__('Content Scheduler can be used for setting content to be delivered to members on a schedule that you determine. Similar to an auto-responder, you have the ability to set which piece(s) of content you want made available to each Membership Level, and on which wlm_date(s). ', 'wishlist-member')); ?>',
                    tooltip_size: 'lg',
                }
            </template>
            <input type="hidden" name="action" value="admin_actions" />
            <input type="hidden" name="WishListMemberAction" value="save" />
        </div>
        <div class="col-sm-4 col-md-5">
            <button href="#" id="content_scheduler_settings" key="scheduler" class="btn -primary -condensed contentcontrol-settings <?php echo esc_attr($option_val && 1 === (int) $option_val ? '' : '-disable'); ?>">
                <i class="wlm-icons">settings</i>
                <span><?php esc_html_e('Settings', 'wishlist-member'); ?></span>
            </button>
        </div>
    </div>
    <div class="row">
        <?php $option_val = wishlistmember_instance()->get_option('enable_content_archiver'); ?>
        <div class="col-sm-8 col-md-5 col-xl-6 col-xxl-4 col-xxxl-3">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Enable Content Archiver', 'wishlist-member')); ?>',
                    name  : 'enable_content_archiver',
                    value : '1',
                    checked_value : '<?php echo esc_js($option_val); ?>',
                    uncheck_value : '0',
                    class : 'wlm_toggle-switch enable-content-control-switch',
                    type  : 'toggle-adjacent-disable',
                    option_type: 'archiver',
                    option_value : '<?php echo esc_js($option_val); ?>',
                    tooltip: '<?php echo esc_js(__('Content Archiver allows you to specify an Expiration Date for content that is assigned to a Membership Level. This gives you the ability to archive content so that only members who are assigned to that Membership Level during the time it was released will continue to have ongoing access to the archived content. New members who were not a part of the Membership Level when the archived content was released would not be able to access the content. This will become non-accessible archived content.', 'wishlist-member')); ?>',
                    tooltip_size: 'lg',
                }
            </template>
            <input type="hidden" name="action" value="admin_actions" />
            <input type="hidden" name="WishListMemberAction" value="save" />
        </div>
        <div class="col-sm-4 col-md-5">
            <button href="#" id="content_archiver_settings" key="archiver" class="btn -primary -condensed contentcontrol-settings <?php echo esc_attr($option_val && 1 === (int) $option_val ? '' : '-disable'); ?>">
                <i class="wlm-icons">settings</i>
                <span><?php esc_html_e('Settings', 'wishlist-member'); ?></span>
            </button>
        </div>
    </div>
    <div class="row">
        <?php $option_val = wishlistmember_instance()->get_option('enable_content_manager'); ?>
        <div class="col-sm-8 col-md-5 col-xl-6 col-xxl-4 col-xxxl-3">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Enable Content Manager', 'wishlist-member')); ?>',
                    name  : 'enable_content_manager',
                    value : '1',
                    checked_value : '<?php echo esc_js($option_val); ?>',
                    uncheck_value : '0',
                    class : 'wlm_toggle-switch enable-content-control-switch',
                    type  : 'toggle-adjacent-disable',
                    option_type: 'manager',
                    option_value : '<?php echo esc_js($option_val); ?>',
                    tooltip: '<?php echo esc_js(__('Content Manager gives you the ability to have additional control over your content by providing the options of setting it to delete, move to a different category, repeat the post, or re-post it on a specifically set date. The Content Manager feature can be used to help automate the ongoing management of content. Note: Content Manager doesn’t need a general Settings option because it gets applied directly to selected pieces of content and is not reliant on Levels.', 'wishlist-member')); ?>',
                    tooltip_size: 'lg',
                }
            </template>
            <input type="hidden" name="action" value="admin_actions" />
            <input type="hidden" name="WishListMemberAction" value="save" />
        </div>
    </div>
<?php endif; ?>
</div>

<?php $_pages = get_pages('exclude=' . implode(',', wishlistmember_instance()->exclude_pages([], true))); ?>
<div id="configure-scheduler" data-id="configure-scheduler" data-label="configure-scheduler-label" data-title="Content Scheduler Settings" data-classes="modal-lg" style="display:none">
    <div class="body">
        <div class="row settings-content">
            <div class="col-md-12">
                <h4 class="mb-3"><?php esc_html_e('Redirect Page', 'wishlist-member'); ?></h4>
                <p><?php esc_html_e('Please specify the page or url you want to redirect the people who wants to access your content.', 'wishlist-member'); ?></p>
                <h5 class="mb-3"><?php esc_html_e('Select one of the following options:', 'wishlist-member'); ?></h5>
            </div>
            <div class="col-md-2 col-border-right">
                <template class="wlm3-form-group">
                    {
                    label : '<?php echo esc_js(__('Page', 'wishlist-member')); ?>',
                    name  : 'sp',
                    value : 'internal',
                    type  : 'radio',
                    id : 'sp-internal',
                    tooltip : '<?php echo esc_js(__('This option can be used in order to select a specific page created in WordPress.', 'wishlist-member')); ?>'
                    }
                </template>
                <template class="wlm3-form-group">
                    {
                    label : '<?php echo esc_js(__('Message', 'wishlist-member')); ?>',
                    name  : 'sp',
                    value : 'text',
                    type  : 'radio',
                    id : 'sp-text',
                    tooltip : '<?php echo esc_js(__('This option can be used in order to specify a message that will automatically be displayed by WishList Member.', 'wishlist-member')); ?>'
                    }
                </template>
                <template class="wlm3-form-group">
                    {
                    label : '<?php echo esc_js(__('URL', 'wishlist-member')); ?>',
                    name  : 'sp',
                    value : 'url',
                    type  : 'radio',
                    id : 'sp-url',
                    tooltip : '<?php echo esc_js(__('This option can be used in order to redirect to a specific URL that may be located or hosted outside of your WordPress site.', 'wishlist-member')); ?>'
                    }
                </template>
            </div>
            <div class="col-md-10">
                <input type="hidden" name="type" class="system-page-type" value="" />
                <div class="sp-text-content">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group mb-2">
                                <textarea class="form-control system-page-text" name="text" cols="30" rows="5" placeholder="Your message" required></textarea>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <button class="btn -default -condensed page-message-reset-button">Reset to Default</button>
                            <template class="wlm3-form-group">{
                                type : 'select',
                                column : 'col-md-5 pull-right no-margin no-padding',
                                'data-placeholder' : '<?php echo esc_js(__('Insert Merge Codes', 'wishlist-member')); ?>',
                                group_class : 'shortcode_inserter mb-0',
                                style : 'width: 100%',
                                options : get_merge_codes([{value : '[password]', text : 'Password'}]),
                                grouped: true,
                                class : 'insert_text_at_caret',
                                'data-target' : '.system-page-text',
                            }</template>
                        </div>
                    </div>
                </div>
                <div class="sp-page-content" style="display:none">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                            <select class="form-control wlm-select wlm-select-pages system-page-internal" name="internal" style="width: 100%" data-placeholder="Select a page">
                                <option></option>
                                <?php foreach ($_pages as $p) : ?>
                                <option value="<?php echo esc_attr($p->ID); ?>" ><?php echo esc_html($p->post_title); ?></option>
                                <?php endforeach; ?>
                            </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <a href="javascript:void(0);" class="btn -success -icon-only add-page-btn" style="margin-bottom: 15px" title="Add a page">
                            <i class="wlm-icons">add</i>
                            </a>
                        </div>
                    </div>
                    <div class="row create-page-holder">
                        <div class="col-md-8">
                            <div class="form-group">
                                <input type="text" class="form-control " name="page_title" value="" placeholder="Page title" required="required" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <a href="javascript:void(0);" class="btn -primary -condensed -no-icon create-page-btn" title="Create Page">
                                <span><?php esc_html_e('Create Page', 'wishlist-member'); ?></span>
                            </a>
                            <a href="javascript:void(0);" class="btn -bare -condensed -no-icon hide-create-page-btn" title="Create Page">
                                <i class="wlm-icons">close</i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="sp-url-content" style="display:none">
                    <div class="row">
                        <div class="col-md-10">
                            <div class="form-group ">
                                <input type="text" class="form-control system-page-url" name="url" value="" placeholder="Specify the URL" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-10 scheduler-settings mt-2">
                <div class="col-10 pt-4">
                    <h4 class="mb-3"><?php esc_html_e('Scheduled Content Visibility', 'wishlist-member'); ?></h4>
                    <div class="row">
                        <?php $option_val = (int) wishlistmember_instance()->get_option('scheduler_show_post_listing'); ?>
                        <div class="col-md-12">
                            <template class="wlm3-form-group">
                                {
                                    label : '<?php echo esc_js(__('Hide non-accessible scheduled content in page and post listings', 'wishlist-member')); ?>',
                                    name  : 'scheduler_show_post_listing',
                                    value : '0',
                                    checked_value : '<?php echo esc_js($option_val); ?>',
                                    uncheck_value : '1',
                                    class : 'wlm_toggle-switch notification-switch',
                                    type  : 'checkbox',
                                    tooltip: '<?php echo esc_js(__('Enabling this setting will hide content from members who joined the level before the scheduled date (non-accessible scheduled content). It will not hide it from members who had access to the level after the schedule date (accessible scheduled content).', 'wishlist-member')); ?>',
                                    tooltip_size: 'lg',
                                }
                            </template>
                            <input type="hidden" name="action" value="admin_actions" />
                            <input type="hidden" name="WishListMemberAction" value="save" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-10 archiver-settings mt-2">
                <div class="col-10 pt-4">
                    <h4 class="mb-3"><?php esc_html_e('Archived Content Visibility', 'wishlist-member'); ?></h4>
                    <div class="row">
                        <?php $option_val = (int) wishlistmember_instance()->get_option('archiver_hide_post_listing'); ?>
                        <div class="col-md-12">
                            <template class="wlm3-form-group">
                                {
                                    label : '<?php echo esc_js(__('Hide non-accessible archived content in page and post listings', 'wishlist-member')); ?>',
                                    name  : 'archiver_hide_post_listing',
                                    value : '1',
                                    checked_value : '<?php echo esc_js($option_val); ?>',
                                    uncheck_value : '0',
                                    class : 'wlm_toggle-switch notification-switch',
                                    type  : 'checkbox',
                                    tooltip: '<?php echo esc_js(__('Enabling this setting will hide content from members who joined the level after the archived date (non-accessible archived content). It will not hide it from members who had access to the level before the archive date (accessible archived content).', 'wishlist-member')); ?>',
                                    tooltip_size: 'lg',
                                }
                            </template>
                            <input type="hidden" name="action" value="admin_actions" />
                            <input type="hidden" name="WishListMemberAction" value="save" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="footer">
    <button type="button" class="btn -bare" data-dismiss="modal">
        <span><?php esc_html_e('Close', 'wishlist-member'); ?></span>
    </button>
    <button type="button" class="btn -primary save-button">
        <i class="wlm-icons">save</i>
        <span><?php esc_html_e('Save', 'wishlist-member'); ?></span>
    </button>
    <button class="-close btn -success -modal-btn save-button">
        <i class="wlm-icons">save</i>
        <span><?php esc_html_e('Save & Close', 'wishlist-member'); ?></span>
    </button>
    </div>
</div>
