<div class="page-header">
    <div class="row">
        <div class="col-md-9 col-sm-9 col-xs-8">
            <h2 class="page-title">
                <?php esc_html_e('Cron Settings', 'wishlist-member'); ?>
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
            <p><?php esc_html_e('A Cron Job allows WishList Member to execute scheduled tasks in a more reliable manner. Some examples of WishList Member scheduled tasks include sequential upgrades and the sending of queued email messages.', 'wishlist-member'); ?></p>
            <p><?php esc_html_e('Anyone who is unfamiliar or uncomfortable with setting up a Cron Job can contact their hosting provider and provide them with the information below. The hosting provider can then set up the Cron Job. Setting the Cron Job to once a day is recommended.', 'wishlist-member'); ?></p>
            <h4><?php esc_html_e('Cron Job Details', 'wishlist-member'); ?></h4>
            <h5><?php esc_html_e('Settings:', 'wishlist-member'); ?></h5>
            <code>0 0 * * *</code>
            <br><br>
            <h5><?php esc_html_e('Command:', 'wishlist-member'); ?></h5>
            <code>/usr/bin/wget -O - -q -t 1 <?php echo esc_html(get_bloginfo('url')); ?>/?wlmcron=1</code>
            <ul>
                <li><?php esc_html_e('Copy and paste the line above into the command line of the Cron Job.', 'wishlist-member'); ?></li>
                <li>
                    <?php esc_html_e('Note: If the above command does not work, please try the following command:', 'wishlist-member'); ?> <br>
                    <code>/usr/bin/GET -d <?php echo esc_html(get_bloginfo('url')); ?>/?wlmcron=1</code>
                </li>
            </ul>
        </div>
    </div>
</div>
