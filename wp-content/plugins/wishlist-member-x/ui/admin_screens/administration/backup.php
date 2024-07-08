<div class="page-header">
    <div class="row">
        <div class="col-md-9 col-sm-9 col-xs-8">
            <h2 class="page-title">
                <?php esc_html_e('Backup', 'wishlist-member'); ?>
            </h2>
        </div>
        <div class="col-md-3 col-sm-3 col-xs-4">
            <?php require $this->plugin_dir3 . '/helpers/header-icons.php'; ?>
        </div>
    </div>
</div>
<?php
    $api_queue   = new \WishListMember\API_Queue();
    $queue       = $api_queue->get_queue('backup_queue');
    $queue_count = 0;
    $queue_left  = 0;
if (count($queue)) {
    $queue       = array_pop($queue);
    $queue_val   = wlm_maybe_unserialize($queue->value);
    $queue_count = $queue_val['tables_cnt'];
    $queue_left  = count($queue_val['tables']);
}
    delete_transient('wlm_is_doing_backup');
?>
<div class="content-wrapper">
    <h4><?php esc_html_e('Backup WishList Member', 'wishlist-member'); ?></h4>
    <div class="row">
            <div class="col-md-12 create-backup-form <?php echo esc_attr($queue_left ? 'd-none' : ''); ?>">
                <p><?php esc_html_e('Include the following within a Backup of the current WishList Member settings:', 'wishlist-member'); ?></p>
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('WishList Member Settings', 'wishlist-member')); ?>',
                        id : 'backup_include_settings',
                        value : '1',
                        checked_value : '1',
                        class: 'chk-include include-settings',
                        type  : 'checkbox',
                        column: 'mb-2',
                    }
                </template>
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Members', 'wishlist-member')); ?>',
                        id : 'backup_include_users',
                        value : '1',
                        checked_value : '<?php echo esc_js($this->get_option('backup_include_users')); ?>',
                        class: 'chk-include include-users',
                        type  : 'checkbox',
                        column: 'mb-2',
                    }
                </template>
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Content', 'wishlist-member')); ?>',
                        id : 'backup_include_posts',
                        value : '1',
                        checked_value : '<?php echo esc_js($this->get_option('backup_include_posts')); ?>',
                        class: 'chk-include include-posts',
                        type  : 'checkbox',
                        column: 'mb-4',
                    }
                </template>
                <a href="#" class="btn -primary create-backup-btn -condensed">
                    <i class="wlm-icons">baseline_save_alt</i>
                    <span class="v-align-0"><?php esc_html_e('Create Backup', 'wishlist-member'); ?></span>
                </a>
            </div>
            <?php
                $in_queue     = 0;
                $progress_bar = 0;
                $pause        = $this->get_option('backup_queue_pause');
                $pause        = 1 === (int) $pause ? true : false;
            if ($queue_left) {
                $in_queue     = $queue_count;
                $progress_bar = ( ( $in_queue - $queue_count ) / $in_queue ) * 100;
                $progress_bar = number_format($progress_bar, 2, '.', '');
            }
            ?>
            <div class="col-md-12 create-backup-queue <?php echo esc_attr($queue_left ? '' : 'd-none'); ?>">
                <p class="text-muted d-none">
                    Currently doing backup for '<span class="backup-table"></span>' table.
                </p>
                <div class="import-progress" title="<?php echo esc_attr($progress_bar); ?>%">
                    <div class="progress">
                        <div style="width: <?php echo esc_attr($progress_bar); ?>%" class="progress-bar" role="progressbar" aria-valuenow="<?php echo esc_attr($in_queue - $queue_count); ?>" aria-valuemin="0" aria-valuemax="<?php echo esc_attr($in_queue); ?>" ></div>
                    </div>
                    <div class="text-center">
                        <span class="queue-total"><?php echo (int) ( $queue_count - $queue_left ); ?></span>/<span class="queue-count"><?php echo esc_html($queue_count); ?></span>
                        <span class="import-action pull-right">
                            <a href="#" title="Cancel Backup" class="btn backup-cancel-btn no-padding"><span class="wlm-icons md-24 -icon-only text-danger">close</span></a>
                        </span>
                    </div>
                </div>
            </div>
    </div>
    <hr>
    <h4>
        <?php esc_html_e('Restore Backup', 'wishlist-member'); ?>
        <?php $this->tooltip(__('The option to Restore from a created Backup or from an External File is available.', 'wishlist-member'), 'lg'); ?>
    </h4>
    <?php
        $doing_import = isset(wlm_post_data()['WishListMemberAction']) && 'ImportSettings' === wlm_post_data()['WishListMemberAction'] ? true : false;
    ?>
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item" role="presentation" ><a href="#backup-holder" class="nav-link <?php echo esc_attr($doing_import ? '' : 'active'); ?>" aria-controls="backup-holder" role="tab" data-toggle="tab"><?php esc_html_e('From Backups', 'wishlist-member'); ?></a></li>
        <li class="nav-item" role="presentation"><a href="#fromfile-holder" class="nav-link <?php echo esc_attr($doing_import ? 'active' : ''); ?>" aria-controls="fromfile-holder" role="tab" data-toggle="tab"><?php esc_html_e('From an External File', 'wishlist-member'); ?></a></li>
    </ul>
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane <?php echo esc_attr($doing_import ? '' : 'active'); ?>" id="backup-holder">
            <?php
                require $this->plugin_dir3 . '/ui/admin_screens/administration/backup/backup_files.php';
            ?>
        </div>
        <div role="tabpanel" class="tab-pane <?php echo esc_attr($doing_import ? 'active' : ''); ?>" id="fromfile-holder">
            <?php $maxfilesize = wp_max_upload_size(); ?>
            <?php $form_action = "?page={$this->menu_id}&wl=" . ( isset(wlm_get_data()['wl']) ? wlm_get_data()['wl'] : 'administration/backup' ); ?>
            <form method="post" enctype="multipart/form-data" action="<?php echo esc_url($form_action); ?>" >
                <div class="form-group">
                    <label for=""><?php esc_html_e('Select a file from your computer', 'wishlist-member'); ?></label>
                    <?php if ($maxfilesize > 1) : ?>
                        <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo esc_attr($maxfilesize); ?>">
                    <?php endif; ?>
                    <input class="form-control -input-file " type="file" name="ImportSettingsfile" style="width: 300px">
                </div>
                <div class="form-group">
                    <template class="wlm3-form-group">
                        {
                            label : '<?php echo esc_js(__('Run Backup before Import', 'wishlist-member')); ?>',
                            name: 'backup_first',
                            id : 'backup_first',
                            value : '1',
                            type  : 'checkbox',
                            column: 'mb-2',
                        }
                    </template>
                    <?php if ($maxfilesize > 1) : ?>
                        <p><label>Maximum file size allowed is <strong><?php echo number_format($maxfilesize / 1048576, 2); ?> MB</strong>.<?php $this->tooltip(esc_html__('This file size is controlled by WordPress. It can be modified. It may be helfpul to contact your hosting company if it needs to be increased', 'wishlist-member')); ?></label> </p>
                    <?php endif; ?>
                    <input type="hidden" name="WishListMemberAction" value="ImportSettings" />
                    <?php print_wlm_nonce_field(); ?>
                    <!-- <input class="btn -primary" type="submit" value="<?php esc_attr_e('Upload and Restore WishList Member Backup', 'wishlist-member'); ?> " /> -->
                    <button class="btn -primary" value="<?php esc_attr_e('Upload and Restore WishList Member Backup', 'wishlist-member'); ?> ">
                        <span class="v-align-0"><?php esc_html_e('Upload and Restore WishList Member Backup', 'wishlist-member'); ?></span>
                    </button>
                </div>
            </form>
                <?php if ($doing_import && isset($this->err)) : ?>
                    <input type="hidden" name="import_err" value="<?php echo esc_attr($this->err); ?>" />
                <?php else : ?>
                    <?php if ($doing_import && isset($this->msg)) : ?>
                        <input type="hidden" name="import_msg" value="<?php echo esc_attr($this->msg); ?>" />
                    <?php endif; ?>
                <?php endif; ?>
        </div>
    </div>
</div>

<?php $form_action = "?page={$this->menu_id}&wl=" . ( isset(wlm_get_data()['wl']) ? wlm_get_data()['wl'] : 'administration/backup' ); ?>
<form method="post" action="<?php echo esc_url($form_action); ?>" id="download_backup_form">
    <input type="hidden" name="SettingsName" value="" />
    <input type="hidden" name="WishListMemberAction" value="ExportSettings" />
    <?php print_wlm_nonce_field(); ?>
</form>

<div id="create-backup-modal" data-id="create-backup-modal" data-label="create-backup_modal_label" data-title="Create Backup" data-classes="modal-md" style="display:none">
    <div class="body">
        <h5 class="message"><?php esc_html_e('Are you sure you want to create a backup of your WishList Member settings?', 'wishlist-member'); ?></h5>
        <input type="hidden" name="backup_include_users" value="" />
        <input type="hidden" name="backup_include_posts" value="" />
        <input type="hidden" name="backup_include_settings" value="" />
        <input type="hidden" name="action" value="admin_actions" />
        <input type="hidden" name="WishListMemberAction" value="create_backup" />
    </div>
    <div class="footer">
        <button type="button" class="btn -bare cancel-button" data-dismiss="modal"><?php esc_html_e('Cancel', 'wishlist-member'); ?></button>
        <button type="button" class="btn -primary save-button"><span><?php esc_html_e('Yes', 'wishlist-member'); ?></span></button>
    </div>
</div>

<div id="restore-backup-modal" data-id="restore-backup-modal" data-label="restore-backup_modal_label" data-title="Restore Backup" data-classes="modal-md" style="display:none">
    <div class="body">
        <h5 class="message"><?php esc_html_e('** Restore Message **', 'wishlist-member'); ?></h5>
        <input type="hidden" name="name" value="" />
        <input type="hidden" name="action" value="admin_actions" />
        <input type="hidden" name="WishListMemberAction" value="restore_backup" />
    </div>
    <div class="footer">
        <button type="button" class="btn -bare cancel-button" data-dismiss="modal"><?php esc_html_e('Cancel', 'wishlist-member'); ?></button>
        <button type="button" class="btn -primary save-button"><span class="text"><?php esc_html_e('Yes', 'wishlist-member'); ?></span></button>
    </div>
</div>

<div id="delete-backup-modal" data-id="delete-backup-modal" data-label="delete-backup_modal_label" data-title="Delete Backup" data-classes="modal-md" style="display:none">
    <div class="body">
        <h5 class="message">** <?php esc_html_e('Delete Message **', 'wishlist-member'); ?></h5>
        <input type="hidden" name="name" value="" />
        <input type="hidden" name="action" value="admin_actions" />
        <input type="hidden" name="WishListMemberAction" value="delete_backup" />
    </div>
    <div class="footer">
        <button type="button" class="btn -bare cancel-button" data-dismiss="modal"><?php esc_html_e('Cancel', 'wishlist-member'); ?></button>
        <button type="button" class="btn -primary save-button"><span class="text"><?php esc_html_e('Yes', 'wishlist-member'); ?></span></button>
    </div>
</div>
