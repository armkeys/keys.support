<div class="page-header">
    <div class="row">
        <div class="col-md-9 col-sm-9 col-xs-8">
            <h2 class="page-title">
                <?php esc_html_e('Reset Settings', 'wishlist-member'); ?>
            </h2>
        </div>
        <div class="col-md-3 col-sm-3 col-xs-4">
            <?php require $this->plugin_dir3 . '/helpers/header-icons.php'; ?>
        </div>
    </div>
</div>
<div class="content-wrapper">
    <h4><?php esc_html_e('Reset WishList Member Settings', 'wishlist-member'); ?></h4>
    <h5 class="danger-text"><span class=""><strong>WARNING:</strong> </span></h5>

    <p><?php esc_html_e('This option will completely reset your copy of WishList Member to exactly the way it was when first installed. ', 'wishlist-member'); ?></p>

    <p><?php esc_html_e('This will delete all membership levels, clear all settings and ANY other customization you may have done with your site through WishList Member.', 'wishlist-member'); ?></p>

    <p><?php esc_html_e('It should be used with EXTREME Caution. ', 'wishlist-member'); ?></p>

    <p><?php esc_html_e('Before using this option, we recommend making a complete backup of your site and downloading it to your computer.', 'wishlist-member'); ?></p>
    <div class="panel-footer -content-footer">
        <div class="row">
            <div class="col-md-12 text-right">
                <a href="#" class="btn -danger reset-settings-btn">
                    <i class="wlm-icons">sync_problem</i>
                    <span><?php esc_html_e('Reset Settings', 'wishlist-member'); ?></span>
                </a>
            </div>
        </div>
    </div>  
</div>

<div id="reset-modal" data-id="reset-modal" data-label="reset_modal_label" data-title="Reset Settings" data-classes="modal-md" style="display:none">
    <div class="body">
        <h5 class="message"><?php esc_html_e('Do you want to reset ALL current WishList Member settings?', 'wishlist-member'); ?> <br> This action CANNOT be undone.</h5>
        <input type="hidden" name="action" value="admin_actions" />
        <input type="hidden" name="WishListMemberAction" value="settings_reset" />
    </div>
    <div class="footer">
        <button type="button" class="btn -bare cancel-button" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn -primary save-button"><span class="text"><?php esc_html_e('Yes', 'wishlist-member'); ?></span></button>
    </div>
</div>
