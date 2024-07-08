<div
    id="recaptcha-settings-modal"
    data-id="recaptcha-settings"
    data-label="recaptcha-settings"
    data-title="reCAPTCHA Settings"
    data-show-default-footer=""
    style="display:none">
    <div class="body">
        <form id="recaptcha-settings-form">
            <div class="row">
                <template class="wlm3-form-group">{
                    label : '<?php echo esc_js(__('reCAPATCHA Site Key', 'wishlist-member')); ?>',
                    name : 'recaptcha_public_key',
                    type : 'text',
                    column : 'col-md-12'
                }</template>
                <template class="wlm3-form-group">{
                    label : '<?php echo esc_js(__('reCAPTCHA Secret Key', 'wishlist-member')); ?>',
                    name : 'recaptcha_private_key',
                    type : 'text',
                    column : 'col-md-12'
                }</template>
                <div class="col-md-12">
                    <p>No reCAPTCHA Key? <a href="https://www.google.com/recaptcha/admin" target="_blank"><?php esc_html_e('Click here to get one for free.', 'wishlist-member'); ?></a></p>
                </div>
            </div>
            <input type="hidden" name="action" value="admin_actions" />
            <input type="hidden" name="WishListMemberAction" value="save" />
        </form>
    </div>
    <div class="footer">
        <a data-toggle="modal" data-target="#recaptcha-settings" data-btype="cancel" href="#" class="save-button btn -bare">
            <span><?php esc_html_e('Close', 'wishlist-member'); ?></span>
        </a>
        <a data-toggle="modal" data-target="#recaptcha-settings" data-btype="save" href="" class="save-button btn -primary">
            <i class="wlm-icons">save</i>
            <span><?php esc_html_e('Save', 'wishlist-member'); ?></span>
        </a>
        &nbsp;
        <a data-toggle="modal" data-target="#recaptcha-settings" data-btype="save-close" href="" class="save-button btn -success">
            <i class="wlm-icons">save</i>
            <span>Save &amp; Close</span>
        </a>
    </div>
</div>
