<div class="page-header">
    <div class="row">
        <div class="col-md-9 col-sm-9 col-xs-8">
            <h2 class="page-title">
                <?php esc_html_e('Blacklist', 'wishlist-member'); ?>
            </h2>
        </div>
        <div class="col-md-3 col-sm-3 col-xs-4">
            <?php require $this->plugin_dir3 . '/helpers/header-icons.php'; ?>
        </div>
    </div>
</div>
<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12">
            <p><?php esc_html_e('Specific email addresses and IP addresses can be prevented from registering by adding them to the Blacklists below.', 'wishlist-member'); ?></p>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6">
            <h4 for="">
                Email Blacklist
                <?php $this->tooltip(__('Anyone attempting to register using an Email Address included on the Email Blacklist will be denied registration.<br><br>Note: Each Email Address must be on its own line.', 'wishlist-member')); ?>
            </h4>
            <div class="form-group">
                <p><?php esc_html_e('Enter the email addresses to Blacklist. One email address per line.', 'wishlist-member'); ?></p>
                <textarea class="form-control" name="blacklist_email" cols="4" rows="5"><?php echo esc_textarea(wlm_trim($this->get_option('blacklist_email'))); ?></textarea>
                <small class="form-text text-muted" id="helpBlock">
                    <em>Example wildcards: user@domain.com, *@domain.com, *.com</em>
                    <span class="helpicon-relative"><?php $this->tooltip(__('Wildcards can be used in order to block email addresses on a more broad basis. For example anyone with an email address from a service that is used specifically for testing like dispostable.com could be blocked by entering *@dispostable.com', 'wishlist-member'), 'lg'); ?></span>                            
                </small>
            </div>
            <div class="form-group">
                <label for="">
                    Email Blacklist Message
                    <?php $this->tooltip(__('Anyone attempting to register using a Blacklisted Email Address will see the Email Blacklist Message when they are denied registration.', 'wishlist-member')); ?>
                </label>
                <input type="text" class="form-control" name="<?php $this->Option('blacklist_email_message', true); ?>" size="60" value="<?php $this->OptionValue(false, 'Your email address is blacklisted.'); ?>" />
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-6">
            <h4 for="">
                IP Blacklist
                <?php $this->tooltip(__('Anyone attempting to register using an IP Address included on the IP Blacklist will be denied registration.<br><br>Note: Each IP Address must be on its own line.', 'wishlist-member')); ?>
            </h4>
            <div class="form-group">
                <p><?php esc_html_e('Enter the IP addresses to Blacklist. One IP address per line.', 'wishlist-member'); ?></p>
                <textarea class="form-control" name="blacklist_ip" cols="30" rows="5"><?php echo esc_textarea(wlm_trim($this->get_option('blacklist_ip'))); ?></textarea>
                <small class="form-text text-muted" id="helpBlock">
                    <em><?php esc_html_e('Example wildcards: 192.168.0.1, 192.168.0.*, 192.168.*', 'wishlist-member'); ?></em>
                    <span class="helpicon-relative"><?php $this->tooltip(__('Wildcards can be used in order to block IP addresses on a more broad basis. For example anyone with an IP address from a service that may not be legitimate. This may be helpful if you experience suspicious activity from a specific block of IP addresses. All IP addresses on a specific network could be blocked by entering something like 172.16.254.*', 'wishlist-member'), 'lg'); ?></span>
                </small>
            </div>
            <div class="form-group">
                <label for="">
                    IP Blacklist Message
                    <?php $this->tooltip(__('Anyone attempting to register using a Blacklisted IP Address will see the IP Blacklist Message when they are denied registration.', 'wishlist-member')); ?>
                </label>
                <input type="text" class="form-control" name="<?php $this->Option('blacklist_ip_message', true); ?>" size="60" value="<?php $this->OptionValue(false, 'Your IP address is blacklisted.'); ?>" />
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label for="">
                    Message when on Both Blacklists
                    <?php $this->tooltip(__('Anyone attempting to register using a Blacklisted Email Address AND a Blacklisted IP Address will see the "Message when on Both Blacklists" when they are denied registration.', 'wishlist-member')); ?>
                </label>
                <input type="text" class="form-control" name="<?php $this->Option('blacklist_email_ip_message', true); ?>" size="60" value="<?php $this->OptionValue(false, 'Your email and IP addresses are blacklisted.'); ?>" />
            </div>
        </div>
    </div>
    <input type="hidden" name="action" value="admin_actions" />
    <input type="hidden" name="WishListMemberAction" value="save" />
    <div class="panel-footer -content-footer">
        <div class="row">
            <div class="col-lg-12 text-right">
                <a href="#" class="btn -primary save-settings">
                    <i class="wlm-icons">save</i>
                    <span class="text"><?php esc_html_e('Save', 'wishlist-member'); ?></span>
                </a>
            </div>
        </div>
    </div>
</div>
