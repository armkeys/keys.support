<div
    id="ppp-email-notification-settings-modal"
    data-id="ppp-email-notification-settings"
    data-label="ppp-email-notification-settings"
    data-title="<span></span>"
    data-classes="modal-lg"
    data-show-default-footer=""
    style="display:none">
    <div class="body">
        <?php
            require_once 'email_notifications/incomplete.php';
            require_once 'email_notifications/newuser.php';
        ?>
    </div>
    <div class="footer">
        <?php echo wp_kses_post($modal_footer); ?>
    </div>
</div>

<style type="text/css">
    #ppp-email-notification-settings textarea {
        min-height: 5rem;
        max-width: 100%;
    }
    #ppp-email-notification-settings .nav-tabs {
        margin-top: 0;
        margin-bottom: 20px;
    }
    #ppp-email-notification-settings .form-inline.pull-right .form-group {
        margin-left: 1em;
    }
    #ppp-email-notification-settings .form-inline.pull-left .form-group {
        margin-right: 1em;
    }
</style>
