<?php

    require $this->legacy_wlm_dir . '/core/InitialValues.php';
    $keys         = [
        'privacy_email_template_delete_subject',
        'privacy_email_template_delete',
        'privacy_email_template_download_subject',
        'privacy_email_template_download',
        'member_unsub_notification_subject',
        'member_unsub_notification_body',
        'privacy_email_template_request_subject',
        'privacy_email_template_request',
    ];
    $default_data = [];
    $form_data    = [];
    foreach ($keys as $key) {
        $default_data[ $key ] = $wishlist_member_initial_data[ $key ];
        $form_data[ $key ]    = $this->get_option($key);
    }
    printf("\n<script type='text/javascript'>var default_data = %s;\nvar form_data = %s;\n</script>\n", json_encode($default_data), json_encode($form_data));
    ?>
<div class="content-wrapper">
    <div class="row">
        <div class="col-xxxl-2 col-md-3 col-sm-5">
            <label class="-standalone">
                <?php esc_html_e('User Request', 'wishlist-member'); ?>
                <?php $this->tooltip(__('The email a member receives when you’ve initiated a data privacy request for them. This template is used for both downloads and erasures. They will click the confirmation link in the email to confirm the request.', 'wishlist-member'), 'lg'); ?>
            </label>
        </div>
        <div class="col-md-9 col-sm-7">
            <button data-toggle="modal" data-target="#data-privacy_user-request" class="btn -primary -condensed">
                <i class="wlm-icons">settings</i>
                <span class="text"><?php esc_html_e('Edit', 'wishlist-member'); ?></span>
            </button>
            <br>
            <br>
        </div>
    </div>
    <div class="row">
        <div class="col-xxxl-2 col-md-3 col-sm-5">
            <label class="-standalone">
                <?php esc_html_e('Download Fulfilled', 'wishlist-member'); ?>
                <?php $this->tooltip(__('The email a member receives when their data download request is fulfilled. This email contains the link for them to download their data.', 'wishlist-member'), 'lg'); ?>
            </label>
        </div>
        <div class="col-md-9 col-sm-7">
            <button data-toggle="modal" data-target="#data-privacy_download-fulfilled" class="btn -primary -condensed">
                <i class="wlm-icons">settings</i>
                <span class="text"><?php esc_html_e('Edit', 'wishlist-member'); ?></span>
            </button>
            <br>
            <br>
        </div>
    </div>
    <div class="row">
        <div class="col-xxxl-2 col-md-3 col-sm-5">
            <label class="-standalone">
                <?php esc_html_e('Erasure Fulfilled', 'wishlist-member'); ?>
                <?php $this->tooltip(__('The email a member receives when their data erasure request is fulfilled.', 'wishlist-member'), 'lg'); ?>
            </label>
        </div>
        <div class="col-md-9 col-sm-7">
            <button data-toggle="modal" data-target="#data-privacy_erasure-fulfilled" class="btn -primary -condensed">
                <i class="wlm-icons">settings</i>
                <span class="text"><?php esc_html_e('Edit', 'wishlist-member'); ?></span>
            </button>
            <br>
            <br>
        </div>
    </div>
    <div class="row">
        <div class="col-xxxl-2 col-md-3 col-sm-5">
            <label class="-standalone">
                <?php esc_html_e('Unsubscribe Notification', 'wishlist-member'); ?>
                <?php $this->tooltip(__('The email a member receives when their request to no longer receive email broadcasts is fulfilled. Note: This only applied to the Email Broadcast feature within WishList Member and does not affect any Email Provider integrations. Unsubscribes within an Email Provider would need to be handled in that specific system separately.', 'wishlist-member'), 'lg'); ?>
            </label>
        </div>
        <div class="col-md-9 col-sm-7">
            <button data-toggle="modal" data-target="#data-privacy_unsubscribe" class="btn -primary -condensed">
                <i class="wlm-icons">settings</i>
                <span class="text"><?php esc_html_e('Edit', 'wishlist-member'); ?></span>
            </button>
            <br>
            <br>
        </div>
    </div>
</div>
<?php
$modal_footer = <<<STRING
	<button class="btn -bare modal-cancel">
		<span>Close</span>
	</button>
	<button class="modal-save-and-continue modal-save-button btn -primary">
		<i class="wlm-icons">save</i>
		<span>Save</span>
	</button>
	&nbsp;
	<button class="modal-save-and-close modal-save-button btn -success">
		<i class="wlm-icons">save</i>
		<span>Save &amp; Close</span>

	</button>
STRING;

require_once 'emailtemplates/modal/download_fulfilled.php';
require_once 'emailtemplates/modal/erasure_fulfilled.php';
require_once 'emailtemplates/modal/unsubscribe.php';
require_once 'emailtemplates/modal/user_request.php';
?>
<style type="text/css">
    .shortcode_inserter {
        margin: 0;
        padding: 0;
        min-height: auto;
    }
</style>
