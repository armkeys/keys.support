<?php
    $doing_import = isset(wlm_post_data()['doing_import']) && 1 === (int) wlm_post_data()['doing_import'] ? true : false;
?>
<div class="content-wrapper -no-header">
    <!-- <p><?php esc_html_e('Import WishList Member Settings from one site to another.', 'wishlist-member'); ?></p> -->
    <h4>Select a WishList Member Settings file to Import:
        <?php $this->tooltip(__('A WishList Member Settings file is one that has previously been exported from WishList Member. ', 'wishlist-member')); ?>
    </h4>
    <?php $maxfilesize = wp_max_upload_size(); ?>
    <div class="row">
        <?php $form_action = "?page={$this->menu_id}&wl=" . ( isset(wlm_get_data()['wl']) ? wlm_get_data()['wl'] : 'administration/site_migration/import_settings' ); ?>
            <div class="col-md-6">
        <form method="post" enctype="multipart/form-data" action="<?php echo esc_url($form_action); ?>" class="import-form">
                <div class="form-group">
                    <?php if ($maxfilesize > 1) : ?>
                        <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo esc_attr($maxfilesize); ?>">
                    <?php endif; ?>
                    <input class="form-control -input-file" type="file" name="Settingsfile" />
                    <br>
                    <?php if ($maxfilesize > 1) : ?>
                        <p>
                            <label><?php esc_html_e('Maximum file size allowed is', 'wishlist-member'); ?>
                                <strong><?php echo number_format($maxfilesize / 1048576, 2); ?> MB</strong>.
                                <?php $this->tooltip(esc_html__('This file size is controlled by WordPress. It can be modified. It may be helfpul to contact your hosting company if it needs to be increased. ', 'wishlist-member')); ?>
                            </label>
                        </p>
                    <?php endif; ?>
                    <input type="hidden" name="doing_import" value="1" />
                    <input type="hidden" name="WishListMemberAction" id="WishListMemberAction" value="RestoreSettingsFromFile" />
                    <?php print_wlm_nonce_field(); ?>
                </div>
            </form>
        </div>

        <?php if ($doing_import && isset($this->msg)) : ?>
            <input type="hidden" name="import_msg" value="<?php echo esc_attr($this->msg); ?>" />
        <?php else : ?>
            <?php if ($doing_import && isset($this->err)) : ?>
                <input type="hidden" name="import_err" value="<?php echo esc_attr($this->err); ?>" />
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <div class="panel-footer -content-footer">
        <div class="row">
             <div class="col-md-12 text-right">
                <a href="#" class="btn -primary import-settings-btn">
                    <i class="wlm-icons">file_upload</i>
                    <span><?php esc_html_e('Import Settings', 'wishlist-member'); ?></span>
                </a>
            </div>
        </div>
    </div>
</div>
