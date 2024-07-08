<?php
$sample_csv_link = '?' . $this->QueryString() . '&wpm_download_sample_csv=1';
$wpm_levels      = $this->get_option('wpm_levels');
?>
<div class="err_holder" style="display: none;"><?php echo isset($this->err) ? wp_kses_post($this->err) : ''; ?></div>
<div class="msg_holder" style="display: none;"><?php echo isset($this->msg) ? wp_kses_post($this->msg) : ''; ?></div>

<?php $form_action = "?page={$this->menu_id}&wl=" . ( isset(wlm_get_data()['wl']) ? wlm_get_data()['wl'] : 'members/import' ); ?>
<form method="post" id="import-form" enctype="multipart/form-data" action="<?php echo esc_url($form_action); ?>">
    <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo esc_attr(wp_max_upload_size()); ?>" />
    <div class="row">
        <div class="col-md-12">
            <p><?php esc_html_e('Use this form to import members by uploading a CSV file. It is important for the file to follow the format of the provided Sample Import CSV File.', 'wishlist-member'); ?></p>
            <div class="form-group">
                <a class="btn -primary -default -icon -stroke" href="<?php echo esc_attr($sample_csv_link); ?>">
                    <i class="wlm-icons">file_download</i><?php esc_html_e('Download a Sample Import CSV File', 'wishlist-member'); ?>
                </a>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <?php $maxfilesize = wp_max_upload_size(); ?>
                <label for=""><?php esc_html_e('Select CSV File', 'wishlist-member'); ?> </label>
                <input class="form-control -input-file" type="file" name="File" id="importml">
                <?php if ($maxfilesize > 1) : ?>
                    <p class="mt-1"><label><?php esc_html_e('Maximum file size allowed is', 'wishlist-member'); ?> <strong><?php echo number_format($maxfilesize / 1048576, 2); ?> MB</strong>.<?php $this->tooltip(__('This file size is controlled by WordPress. It can be modified. It may be helfpul to contact your hosting company if it needs to be increased. ', 'wishlist-member')); ?>
                    </label> </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="">Default Password for New Members
                    <?php
                    $this->tooltip(
                        __(
                            'A single default password can be entered into this field and applied to all the imported Members. Each Member would be issued the same password in this case. <br><br>
					Leaving this field blank will generate and apply a random password to each member.<br><br>
					Note: This field will be ignored if the imported CSV file contains passwords for each user.',
                            'wishlist-member'
                        ),
                        'lg'
                    );
                    ?>
                    </label>
                    <input type="" class="form-control" name="password">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for=""><?php esc_html_e('Import Members into the following Levels', 'wishlist-member'); ?></label>
                    <input type="hidden" id="importmlevels" name="importmlevels" value="1" />
                    <select name="wpm_to[]" multiple="multiple" class="form-control wlm-select wlm-select-selectall select_mlevels" data-placeholder="Select Membership Levels">
                        <?php foreach ($wpm_levels as $key => $value) : ?>
                        <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($value['name']); ?></option>
                        <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label for="">Required Fields <?php $this->tooltip(__('Select if the Last Name and/or First Name of Members are required in order to process the Member Import.', 'wishlist-member')); ?></label>
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('First Name', 'wishlist-member')); ?>',
                        name  : 'require_firstname',
                        id : 'require_firstname',
                        value : '1',
                        type  : 'checkbox',
                    }
                </template>
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Last Name', 'wishlist-member')); ?>',
                        name  : 'require_lastname',
                        id : 'require_lastname',
                        value : '1',
                        type  : 'checkbox',
                        column: 'mt-2',
                    }
                </template>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label for="">Registration Date</label>
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Use Registration Date specified in the CSV import file', 'wishlist-member')); ?>',
                        name  : 'use_regdate',
                        id : 'use_registration',
                        value : '1',
                        checked_value : '1',
                        type  : 'radio',
                        column: 'mb-2',
                    }
                </template>
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Use Today\'s Date', 'wishlist-member')); ?>',
                        name  : 'use_regdate',
                        id : 'use_today',
                        value : '0',
                        type  : 'radio',
                    }
                </template>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label for="">How to handle duplicate Usernames and Email Addresses</label>
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Skip rows with duplicates', 'wishlist-member')); ?>',
                        name  : 'duplicates',
                        id : 'duplicates_skip',
                        value : 'skip',
                        checked_value : 'skip',
                        type  : 'radio',
                        column: 'mb-2',
                    }
                </template>
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Replace ALL Information and Membership Levels', 'wishlist-member')); ?>',
                        name  : 'duplicates',
                        id : 'duplicates_replace',
                        value : 'replace',
                        type  : 'radio',
                        column: 'mb-2',
                    }
                </template>
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Update ALL Information and Membership Levels', 'wishlist-member')); ?>',
                        name  : 'duplicates',
                        id : 'duplicates_update',
                        value : 'update',
                        type  : 'radio',
                        column: 'mb-2',
                    }
                </template>
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Replace Membership Levels ONLY', 'wishlist-member')); ?>',
                        name  : 'duplicates',
                        id : 'duplicates_replace_levels',
                        value : 'replace_levels',
                        type  : 'radio',
                        column: 'mb-2',
                    }
                </template>
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Update Membership Levels ONLY', 'wishlist-member')); ?>',
                        name  : 'duplicates',
                        id : 'duplicates_update_levels',
                        value : 'update_levels',
                        type  : 'radio',
                        column: 'mb-2',
                    }
                </template>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label for="">Integrations to process <?php $this->tooltip(__('If any configured Email Provider Integrations and/or Webinar Integrations should be processed for the members during the Member Import.', 'wishlist-member')); ?></label>
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Process Email Provider Integrations', 'wishlist-member')); ?>',
                        name  : 'process_autoresponders',
                        id : 'process_autoresponders',
                        value : '1',
                        type  : 'checkbox',
                        column: 'mb-2',
                    }
                </template>
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Process Other Integrations', 'wishlist-member')); ?>',
                        name  : 'process_other_integrations',
                        id : 'process_other_integrations',
                        value : '1',
                        type  : 'checkbox',
                    }
                </template>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label for="">Notify New Members via Email <?php $this->tooltip(__('This option determines if imported members should receive the New Member Registration Email.<br><br>Note: The New Member Registration Email can be set/adjusted on a per Level basis in the Setup > Levels > Level Name > Notifications section of WishList Member.', 'wishlist-member'), 'md'); ?></label>
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Send email notifications to ALL new members as set in the level-based notification settings', 'wishlist-member')); ?>',
                        name  : 'notify',
                        id : 'notify_all',
                        value : 'send_email_to_all_new_users',
                        checked_value : 'send_email_to_all_new_users',
                        type  : 'radio',
                        column: 'mb-2',
                    }
                </template>
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Send email notifications ONLY to members with randomly generated passwords as set in the level-based notification settings', 'wishlist-member')); ?>',
                        name  : 'notify',
                        id : 'notify_random',
                        value : 'only_for_randomly_generated_passwords',
                        type  : 'radio',
                        column: 'mb-2',
                    }
                </template>
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Do not send any email notifications at all', 'wishlist-member')); ?>',
                        name  : 'notify',
                        id : 'notify_not',
                        value : 'do_not_send_email',
                        type  : 'radio',
                        column: 'mb-2',
                    }
                </template>
            </div>
        </div>
    </div>
    <input type="hidden" name="WishListMemberAction" value="ImportMembers" />
    <?php print_wlm_nonce_field(); ?>
    <div class="panel-footer -content-footer">
        <div class="row">
            <div class="col-lg-12 text-right">
                <a href="#" class="btn -primary import-member">
                    <i class="wlm-icons">file_upload</i>
                    <span><?php esc_html_e('Import Members', 'wishlist-member'); ?></span>
                </a>
            </div>
        </div>
    </div>
</form>
