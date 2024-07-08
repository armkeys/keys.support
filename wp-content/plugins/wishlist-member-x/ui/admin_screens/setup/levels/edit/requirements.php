<div role="tabpanel" class="tab-pane" id="" data-id="levels_requirements">
<div class="content-wrapper">

<div class="row">
    <div class="col-xxxl-4 col-md-7 col-sm-7">
        <template class="wlm3-form-group">
            {
                label : '<?php echo esc_js(__('Require Admin Approval for Free Registrations', 'wishlist-member')); ?>',
                name  : 'requireadminapproval',
                value : '1',
                type : 'toggle-adjacent-disable',
                tooltip_size: 'md',
                tooltip : '<?php echo esc_js(__('When checked, an admin must confirm the registration BEFORE a Member is given access to the Membership Level.<br><br>This setting only applies to Registrations that did not come through a Payment Integration.', 'wishlist-member')); ?>'
            }
        </template>
    </div>
    <div class="col-md-5 col-sm-5 mb-sm-2">
        <button data-toggle="modal" data-target="#email-notification-settings" class="btn -primary -condensed" data-notif-setting="requireadminapproval-free" data-notif-title="Require Admin Approval for Free Registrations Notifications">
            <i class="wlm-icons">settings</i>
            <span class="text"><?php esc_html_e('Edit Notifications', 'wishlist-member'); ?></span>
        </button>
        <br class="d-block d-sm-none">
        <br class="d-block d-sm-none">
        <br class="d-block d-sm-none">
    </div>
</div>
<div class="row">
    <div class="col-xxxl-4 col-md-7 col-sm-7">
        <template class="wlm3-form-group">
            {
                label : '<?php echo esc_js(__('Require Admin Approval for Payment Provider Integrations', 'wishlist-member')); ?>',
                name  : 'requireadminapproval_integrations',
                value : '1',
                type  : 'toggle-adjacent-disable',
                tooltip_size: 'md',
                tooltip : '<?php echo esc_js(__('When enabled, an Admin must confirm the registration BEFORE the Member is given access to the Membership Level. This only applies to registrations which take place after a payment integration was processed.', 'wishlist-member')); ?>'
            }
        </template>
    </div>
    <div class="col-md-5 col-sm-5 mb-sm-2">
        <button data-toggle="modal" data-target="#email-notification-settings" class="btn -primary -condensed" data-notif-setting="requireadminapproval-paid" data-notif-title="Require Admin Approval for Payment Provider Registrations Notifications">
            <i class="wlm-icons">settings</i>
            <span class="text"><?php esc_html_e('Edit Notifications', 'wishlist-member'); ?></span>
        </button>
        <br class="d-block d-sm-none">
        <br class="d-block d-sm-none">
        <br class="d-block d-sm-none">
    </div>
</div>
<div class="row">
    <div class="col-xxxl-4 col-md-7 col-sm-7">
        <template class="wlm3-form-group">
            {
                label : '<?php echo esc_js(__('Require Members to Confirm Email', 'wishlist-member')); ?>',
                name  : 'requireemailconfirmation',
                value : '1',
                type  : 'toggle-adjacent-disable',
                tooltip_size: 'md',
                tooltip : '<?php echo esc_js(__('When enabled, an email will be sent to the Member after registration requiring they confirm their registration BEFORE they are given access to the Membership Level.', 'wishlist-member')); ?>'
            }
        </template>
    </div>
    <div class="col-md-5 col-sm-5 mb-sm-2">
        <button data-toggle="modal" data-target="#email-notification-settings" class="btn -primary -condensed" data-notif-setting="requireemailconfirmation" data-notif-title="Require Members to Confirm Email Notifications">
            <i class="wlm-icons">settings</i>
            <span class="text"><?php esc_html_e('Edit Notifications', 'wishlist-member'); ?></span>
        </button>
        <br class="d-block d-sm-none">
        <br class="d-block d-sm-none">
        <br class="d-block d-sm-none">
    </div>
</div>
<div class="row">
    <div class="col-xxxl-4 col-md-7 col-sm-7">
        <template class="wlm3-form-group">
            {
                label : '<?php echo esc_js(__('Require Terms and Conditions', 'wishlist-member')); ?>',
                name  : 'enable_tos',
                value : '1',
                tooltip_size: 'md',
                tooltip : '<?php echo esc_js(__('After enabling this option members will be required to click a checkbox acknowledging they accept the terms and conditions before registering for a membership level.', 'wishlist-member')); ?>',
                type  : 'toggle-adjacent-disable'
            }
        </template>
    </div>
    <div class="col-md-5 col-sm-5 mb-sm-2">
        <button data-toggle="modal" data-target="#terms-and-conditions" href="" class="btn -primary -condensed">
            <i class="wlm-icons">settings</i>
            <span><?php esc_html_e('Configure', 'wishlist-member'); ?></span>
        </button>
    </div>
</div>
<div class="row">
    <div class="col-xxxl-4 col-md-7 col-sm-7">
        <template class="wlm3-form-group">
            {
                label : '<?php echo esc_js(__('Require reCAPTCHA', 'wishlist-member')); ?>',
                name  : 'requirecaptcha',
                value : '1',
                type  : 'toggle-adjacent-disable',
                tooltip_size: 'md',
                tooltip : '<?php echo esc_js(__('When checked, a random word will be displayed on the registration form that must be identified by the user and typed into a field to help reduce spam.', 'wishlist-member')); ?>'
            }
        </template>
    </div>
    <div class="col-md-5 col-sm-5 mb-sm-2">
        <button data-toggle="modal" data-target="#recaptcha-settings" class="btn -primary -condensed">
            <i class="wlm-icons">settings</i>
            <span class="text"><?php esc_html_e('Configure', 'wishlist-member'); ?></span>
        </button>
        <br class="d-block d-sm-none">
        <br class="d-block d-sm-none">
        <br class="d-block d-sm-none">  
    </div>
</div>
<br>
<div class="panel-footer -content-footer">
    <div class="row">
        <div class="col-md-12 text-right">
            <?php echo wp_kses_post($tab_footer); ?>
        </div>
    </div>
</div>
</div>
</div>
