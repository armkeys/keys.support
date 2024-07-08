<?php
    $enable_as_default_label   = __('Enable as Default', 'wishlist-member');
    $enable_as_default_tooltip = __('The Default Status will be applied to all new levels created in the future. If you would like to apply it to existing levels click the option at the bottom of this window that says "Apply Settings to Membership Levels" then select the membership levels you want the new settings applied to.', 'wishlist-member');

    $enable_as_default = <<<STRING
	<div class="col-md-12">
		<template class="wlm3-form-group">{
		label : '$enable_as_default_label', name : '%s',
		type : 'toggle-switch', value: 1, uncheck_value: 0,
		tooltip : '$enable_as_default_tooltip',
		tooltip_size: 'xl'
		}</template>
	</div>
STRING;

?>

<div id="<?php echo esc_attr($_the_id); ?>" class="content-wrapper">
    <div class="row">
        <?php $option_val = $this->get_option('dont_send_reminder_email_when_unsubscribed'); ?>
        <div class="col-md-12">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Do not send reminder emails when a member unsubscribes', 'wishlist-member')); ?>',
                    name  : 'dont_send_reminder_email_when_unsubscribed',
                    value : '1',
                    checked_value : '<?php echo esc_js($option_val); ?>',
                    uncheck_value : '0',
                    class : 'wlm_toggle-switch notification-switch',
                    type  : 'checkbox',
                    tooltip : '<?php echo esc_js(__('If enabled, WishList Member will stop sending reminder emails when the user unsubscribes.', 'wishlist-member')); ?>'
                }
            </template>
            <input type="hidden" name="action" value="admin_actions" />
            <input type="hidden" name="WishListMemberAction" value="save" />
        </div>
    </div>
    <div class="row">
        <?php $option_val = $this->get_option('unsubscribe_expired_members'); ?>
        <div class="col-md-12">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Unsubscribe expired members', 'wishlist-member')); ?>',
                    name  : 'unsubscribe_expired_members',
                    value : '1',
                    checked_value : '<?php echo esc_js($option_val); ?>',
                    uncheck_value : '0',
                    class : 'wlm_toggle-switch notification-switch',
                    type  : 'checkbox',
                    tooltip : '<?php echo esc_js(__('If enabled, WishList Member will unsubscribe the members that have expired membership levels from configured autoresponders.', 'wishlist-member')); ?>'
                }
            </template>
            <input type="hidden" name="action" value="admin_actions" />
            <input type="hidden" name="WishListMemberAction" value="save" />
        </div>
    </div>

    <!-- Default Sender Info -->
    <hr>
    <div class="row">
        <div class="col-md-12">
            <h4><?php esc_html_e('Global Sender Info Defaults', 'wishlist-member'); ?> <?php $this->tooltip('The Sender Name and Sender Email are pulled from the Name and Email set in the Advanced Options > Global Defaults > Admin Info section of WishList Member.'); ?></h4>
        </div>


        <div class="col-lg-4 col-md-12">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Sender Name', 'wishlist-member')); ?>',
                    name : '<?php $this->Option('email_sender_name'); ?>',
                    value : '<?php $this->OptionValue(); ?>',
                    disabled : 'disabled',
                    group_class : 'mb-1'
                }
            </template>
        </div>
        <div class="col-lg-4 col-md-12">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Sender Email', 'wishlist-member')); ?>',
                    name : '<?php $this->Option('email_sender_address'); ?>',
                    value : '<?php $this->OptionValue(); ?>',
                    disabled : 'disabled',
                    group_class : 'mb-1'
                }
            </template>
        </div>
        <div class="col-lg-4 col-md-12">
            <label class="d-none d-lg-inline-block">&nbsp;</label>
            <div class="mt-3 mt-lg-0">
                <button id="reset-email-sender" class="btn -primary -condensed"><i class="wlm-icons pull-left">sync_problem</i><span><?php esc_html_e('Apply to All Levels', 'wishlist-member'); ?></span></button>
            </div>
        </div>
    </div>
    <!-- Default Email Templates -->
    <hr>
    <div class="row">
        <div class="col-md-12">
            <h4><?php esc_html_e('Global Email Notification Defaults', 'wishlist-member'); ?></h4>
        </div>
        <div class="col-lg-5 col-md-6 col-sm-6">
            <label class="-standalone"><?php esc_html_e('New Member Registration', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('The email a New Member receives after joining a Level.', 'wishlist-member')); ?></label>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 mb-sm-3 text-right">
            <button data-toggle="modal" data-target="#email-notification-settings" class="btn -primary -condensed" data-notif-setting="newuser" data-notif-title="New Member Registration" data-lpignore="true"><i class="wlm-icons">settings</i><span class="text"><?php esc_html_e('Edit Notifications', 'wishlist-member'); ?></span></button>
        </div>

        <div class="col-lg-5 col-md-6 col-sm-6">
            <label class="-standalone"><?php esc_html_e('Incomplete Registration', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('The email a Member receives if they do not completely fill out the registration form. A link to complete the registration is included in the email.', 'wishlist-member')); ?></label>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 mb-sm-3 text-right">
            <button data-toggle="modal" data-target="#email-notification-settings" class="btn -primary -condensed" data-notif-setting="incomplete" data-notif-title="Incomplete Registration" data-lpignore="true"><i class="wlm-icons">settings</i><span class="text"><?php esc_html_e('Edit Notifications', 'wishlist-member'); ?></span></button>
        </div>

        <div class="col-lg-5 col-md-6 col-sm-6">
            <label class="-standalone"><?php esc_html_e('Membership Cancelled', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('The email a Member receives if their access to the Level is Cancelled.', 'wishlist-member')); ?></label>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 mb-sm-3 text-right">
            <button data-toggle="modal" data-target="#email-notification-settings" class="btn -primary -condensed" data-notif-setting="cancel" data-notif-title="Membership Cancelled" data-lpignore="true"><i class="wlm-icons">settings</i><span class="text"><?php esc_html_e('Edit Notifications', 'wishlist-member'); ?></span></button>
        </div>

        <div class="col-lg-5 col-md-6 col-sm-6">
            <label class="-standalone"><?php esc_html_e('Membership Uncancelled', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('The email a Member receives if their access to the Level is Uncancelled.', 'wishlist-member')); ?></label>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 mb-sm-3 text-right">
            <button data-toggle="modal" data-target="#email-notification-settings" class="btn -primary -condensed" data-notif-setting="uncancel" data-notif-title="Membership Uncancelled" data-lpignore="true"><i class="wlm-icons">settings</i><span class="text"><?php esc_html_e('Edit Notifications', 'wishlist-member'); ?></span></button>
        </div>

        <div class="col-lg-5 col-md-6 col-sm-6">
            <label class="-standalone"><?php esc_html_e('Require Admin Approval for Free Registrations', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('The emails that are sent to the Member and to the site Admin during the approval process for a free Level.', 'wishlist-member')); ?></label>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 mb-sm-3 text-right">
            <button data-toggle="modal" data-target="#email-notification-settings" class="btn -primary -condensed" data-notif-setting="requireadminapproval-free" data-notif-title="Require Admin Approval for Free Registrations" data-lpignore="true"><i class="wlm-icons">settings</i><span class="text"><?php esc_html_e('Edit Notifications', 'wishlist-member'); ?></span></button>
        </div>

        <div class="col-lg-5 col-md-6 col-sm-6">
            <label class="-standalone"><?php esc_html_e('Require Admin Approval for Payment Provider Integrations', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('The emails sent to the Member and to the site Admin during the approval process for a paid Level.', 'wishlist-member')); ?></label>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 mb-sm-3 text-right">
            <button data-toggle="modal" data-target="#email-notification-settings" class="btn -primary -condensed" data-notif-setting="requireadminapproval-paid" data-notif-title="Require Admin Approval for Payment Provider Integrations" data-lpignore="true"><i class="wlm-icons">settings</i><span class="text"><?php esc_html_e('Edit Notifications', 'wishlist-member'); ?></span></button>
        </div>

        <div class="col-lg-5 col-md-6 col-sm-6">
            <label class="-standalone"><?php esc_html_e('Require Members to Confirm Email', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('The emails sent to the Member during the Email Confirmation process for a Level.', 'wishlist-member')); ?></label>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 mb-sm-3 text-right">
            <button data-toggle="modal" data-target="#email-notification-settings" class="btn -primary -condensed" data-notif-setting="requireemailconfirmation" data-notif-title="Require Members to Confirm Email" data-lpignore="true"><i class="wlm-icons">settings</i><span class="text"><?php esc_html_e('Edit Notifications', 'wishlist-member'); ?></span></button>
        </div>

        <div class="col-lg-5 col-md-6 col-sm-6">
            <label class="-standalone"><?php esc_html_e('Expiring Member', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('The emails sent to the Member and the site Admin if a Level is set to expire and the expiration date is approaching.', 'wishlist-member')); ?></label>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 mb-sm-3 text-right">
            <button data-toggle="modal" data-target="#email-notification-settings" class="btn -primary -condensed" data-notif-setting="expiring" data-notif-title="Expiring Member" data-lpignore="true"><i class="wlm-icons">settings</i><span class="text"><?php esc_html_e('Edit Notifications', 'wishlist-member'); ?></span></button>
        </div>

    </div>
</div>

<div
    id="email-notification-settings-modal"
    data-id="email-notification-settings"
    data-label="email-notification-settings"
    data-title="<span></span>"
    data-classes="modal-lg"
    data-show-default-footer=""
    style="display:none">
    <div class="body">
        <?php
            require_once 'email/notifications/newuser.php';
            require_once 'email/notifications/incomplete.php';
            require_once 'email/notifications/cancel.php';
            require_once 'email/notifications/uncancel.php';
            require_once 'email/notifications/require_admin_approval_free.php';
            require_once 'email/notifications/require_admin_approval_paid.php';
            require_once 'email/notifications/require_email_confirmation.php';
            require_once 'email/notifications/expiring.php';

            $wpm_levels = $this->get_option('wpm_levels');
        ?>
    </div>
    <div class="footer">
        <button class="btn -bare notif-modal-cancel">
            <span><?php esc_html_e('Close', 'wishlist-member'); ?></span>
        </button>
        <button class="notif-modal-save btn -primary">
            <i class="wlm-icons">save</i>
            <span><?php esc_html_e('Save', 'wishlist-member'); ?></span>
        </button>
        &nbsp;
        <button class="notif-modal-save -and-close btn -success">
            <i class="wlm-icons">save</i>
            <span><?php esc_html_e('Save & Close', 'wishlist-member'); ?></span>
        </button>
    </div>
    <div class="footer">
        <div class="row flex-grow apply-to-all-levels-content-hide" id="apply-to-all-levels-content-hide">
            <div class="col pr-0 apply-to-all-levels-content">
                <div class="form-group apply-to-all-levels-toggle">
                    <label class="apply-to-all-levels-toggle">
                        <i class="wlm-icons d-inline-block align-middle" style="margin-left: -10px; margin-top: -5px; height: 20px; overflow: hidden"></i>
                        <span class="d-inline-block align-middle">
                            <?php esc_html_e('Apply Settings to Membership Levels', 'wishlist-member'); ?>
                        </span>
                        <?php $this->tooltip(__('There is an option to apply these specific settings to Levels on this site. Select the desired Level(s) and click the blue Apply button to apply these specific settings to the selected Level(s).', 'wishlist-member'), 'lg'); ?>
                    </label>
                    <select class="form-control mr-1" id="apply-to-all-levels" style="min-width: 150px; width: 100%" multiple data-placeholder="<?php esc_attr_e('No Membership Levels Selected', 'wishlist-member'); ?>">
                        <?php
                        foreach ($wpm_levels as $lid => $l) {
                            printf('<option value="%s">%s</option>', esc_attr($lid), esc_html($l['name']));
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-auto apply-to-all-levels-content">
                <label>&nbsp;</label><br>
                <button class="btn -primary -condensed" id="apply-to-all-levels-btn">
                    <span><?php esc_html_e('Apply', 'wishlist-member'); ?></span>
                </button>
            </div>
        </div>
    </div>
</div>

<?php
$pristine = true;
require $this->plugin_dir3 . '/helpers/level-email-defaults.php';

?>
<script type="text/javascript">
    WLM3VARS.original_email_values = <?php echo json_encode($level_email_defaults); ?>;
</script>
<style type="text/css">
    #email-notification-settings textarea {
        min-height: 5rem;
        max-width: 100%;
    }
    #email-notification-settings .nav-tabs {
        margin-top: 0;
        margin-bottom: 20px;
    }
    #email-notification-settings .form-inline.pull-right .form-group {
        margin-left: 1em;
    }
    #email-notification-settings .form-inline.pull-left .form-group {
        margin-right: 1em;
    }
    #email-notification-settings .modal-body .-holder {
        display: none;
    }
    #email-notification-settings .modal-body.newuser .-holder.newuser,
    #email-notification-settings .modal-body.cancel .-holder.cancel,
    #email-notification-settings .modal-body.uncancel .-holder.uncancel,
    #email-notification-settings .modal-body.requireemailconfirmation .-holder.requireemailconfirmation,
    #email-notification-settings .modal-body.requireadminapproval-free .-holder.requireadminapproval-free,
    #email-notification-settings .modal-body.requireadminapproval-paid .-holder.requireadminapproval-paid,
    #email-notification-settings .modal-body.incomplete .-holder.incomplete,
    #email-notification-settings .modal-body.expiring .-holder.expiring {
        display: block;
    }

    #custom-redirects .modal-body .-holder {
        display: none;
    }
    #custom-redirects .modal-body.afterreg-redirect .-holder.afterreg-redirect,
    #custom-redirects .modal-body.login-redirect .-holder.login-redirect,
    #custom-redirects .modal-body.logout-redirect .-holder.logout-redirect {
        display: block;
    }

    .shortcode_inserter {
        margin: 0;
        padding: 0;
        min-height: auto;
    }

    .CodeMirror { border: 1px solid #ddd; }
    .CodeMirror pre { padding-left: 8px; line-height: 1.25; }

    #apply-to-all-levels-content-hide i.wlm-icons::after{
        content: "arrow_drop_down";
    }
    #apply-to-all-levels-content-hide.apply-to-all-levels-content-hide i.wlm-icons::after{
        content: "arrow_right";
    }
    .apply-to-all-levels-content-hide .apply-to-all-levels-content *:not(.apply-to-all-levels-toggle) {
        display: none;
    }
    label.apply-to-all-levels-toggle {
        cursor: pointer;
    }
</style>
