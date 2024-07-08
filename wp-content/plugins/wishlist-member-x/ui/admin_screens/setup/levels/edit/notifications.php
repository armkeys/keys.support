<div role="tabpanel" class="tab-pane" id="" data-id="levels_notifications">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-4 col-sm-5">
                <label class="-standalone">
                    <?php esc_html_e('New Member Registration', 'wishlist-member'); ?>
                    <a href="" class="wlm-icons help-icon" title="" data-html="true" data-original-title="Click the Edit Notification button to enable and configure the User Notification and Admin Notification email messages that can be sent when a member registers for this membership level.">help</a>
                </label>
            </div>
            <div class="col-md-8 col-sm-7">
                <button data-toggle="modal" data-target="#email-notification-settings" class="btn -primary -condensed" data-notif-setting="newuser" data-notif-title="New Member Notifications">
                    <i class="wlm-icons">settings</i>
                    <span class="text"><?php esc_html_e('Edit Notifications', 'wishlist-member'); ?></span>
                </button>
                <br>
                <br>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 col-sm-5">
                <label class="-standalone">
                    <?php esc_html_e('Incomplete Registration', 'wishlist-member'); ?>
                    <a href="" class="wlm-icons help-icon" title="" data-html="true" data-original-title="Click the Edit Notification button to enable and configure the User Notification email messages that can be sent when a member fails to complete the registration process after being added to this membership level.">help</a>
                </label>
            </div>
            <div class="col-md-8 col-sm-7">
                <button data-toggle="modal" data-target="#email-notification-settings" class="btn -primary -condensed" data-notif-setting="incomplete" data-notif-title="Incomplete Registration Notifications">
                    <i class="wlm-icons">settings</i>
                    <span class="text"><?php esc_html_e('Edit Notifications', 'wishlist-member'); ?></span>
                </button>
                <br>
                <br>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 col-sm-5">
                <label class="-standalone">
                    <?php esc_html_e('Membership Cancelled', 'wishlist-member'); ?>
                    <a href="" class="wlm-icons help-icon" title="" data-html="true" data-original-title="Click the Edit Notification button to enable and configure the User Notification email message that will be sent when a member is cancelled from this membership level.">help</a>
                </label>
            </div>
            <div class="col-md-8 col-sm-7">
                <button data-toggle="modal" data-target="#email-notification-settings" class="btn -primary -condensed" data-notif-setting="cancel" data-notif-title="Membership Cancelled Notification">
                    <i class="wlm-icons">settings</i>
                    <span class="text"><?php esc_html_e('Edit Notifications', 'wishlist-member'); ?></span>
                </button>
                <br>
                <br>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 col-sm-5">
                <label class="-standalone">
                    <?php esc_html_e('Membership Uncancelled', 'wishlist-member'); ?>
                    <a href="" class="wlm-icons help-icon" title="" data-html="true" data-original-title="Click the Edit Notification button to enable and configure the User Notification email message that will be sent when a member is uncancelled from this membership level.">help</a>
                </label>
            </div>
            <div class="col-md-8 col-sm-7">
                <button data-toggle="modal" data-target="#email-notification-settings" class="btn -primary -condensed" data-notif-setting="uncancel" data-notif-title="Membership Uncancelled Notification">
                    <i class="wlm-icons">settings</i>
                    <span class="text"><?php esc_html_e('Edit Notifications', 'wishlist-member'); ?></span>
                </button>
                <br>
                <br>
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
