<div class="page-header">
    <div class="row">
        <div class="col-md-9 col-sm-9 col-xs-8">
            <h2 class="page-title">
                <?php esc_html_e('RSS', 'wishlist-member'); ?>
            </h2>
        </div>
        <div class="col-md-3 col-sm-3 col-xs-4">
            <?php require $this->plugin_dir3 . '/helpers/header-icons.php'; ?>
        </div>
    </div>
</div>
<div class="content-wrapper">
    <div class="row">
        <?php
            $rss_secret_key = $this->get_option('rss_secret_key');
        if (! $rss_secret_key) {
            $rss_secret_key = md5(microtime());
            $this->get_option('rss_secret_key', $rss_secret_key);
        }
        ?>
        <div class="col-md-12">
            <label for="">
                RSS Key
                <?php $this->tooltip(__('This RSS Key will be used to generate a unique RSS Feed URL for each member. Be very careful if changing this key. It will affect all individual RSS feeds that have been issued to current members. <br><br>(Individual RSS feeds are located in the Member Profile under the Advanced Tab).', 'wishlist-member'), 'lg'); ?>
            </label>
            <div class="row">
                <div class="col-xxxl-3 col-xxl-4 col-md-6 no-margin">
                    <template class="wlm3-form-group">
                        {
                            name  : 'rss_secret_key',
                            value : '<?php echo esc_js($rss_secret_key); ?>',
                            group_class : 'no-margin',
                            'data-initial' : '<?php echo esc_js($rss_secret_key); ?>',
                            class : 'rss-secret-key-apply',
                        }
                    </template>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <small class="form-text text-muted mb-3" id="helpBlock"><em><?php esc_html_e('This key will be used to generate the unique RSS Feed URL for each member. Do not share this key.', 'wishlist-member'); ?></em></small>
        </div>
    </div>
    <div class="row">
        <?php $option_val = $this->get_option('disable_rss_enclosures'); ?>
        <div class="col-md-12">
            <template class="wlm3-form-group">
                {
                    label : '<?php echo esc_js(__('Disable RSS Enclosures for non-authenticated feeds', 'wishlist-member')); ?>',
                    name  : 'disable_rss_enclosures',
                    value : '1',
                    checked_value : '<?php echo esc_js($option_val); ?>',
                    uncheck_value : '0',
                    class : 'wlm_toggle-switch notification-switch',
                    type  : 'checkbox',
                    tooltip: '<?php echo esc_js(__('RSS enclosures are a way of attaching multimedia content to RSS feeds by providing the URL of a file associated with an entry. For example, rather than attaching a file like an mp3 or PDF in an email message, the RSS feed would include a URL to where the file was located. <br><br>When this setting is enabled, the link to the file will not be included in the RSS Feed.', 'wishlist-member')); ?>',
                    tooltip_size : 'lg'
                }
            </template>
            <input type="hidden" name="action" value="admin_actions" />
            <input type="hidden" name="WishListMemberAction" value="save" />
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-md-12">
            <label for="">
                <?php esc_html_e('Maximum Number of IPs per Day', 'wishlist-member'); ?>
                <?php $this->tooltip(__('This is the number of unique IPs that are allowed to access a member\'s protected RSS Feed in a day', 'wishlist-member')); ?>
            </label>
            <div class="row">
                <div class="col-sm-6 col-md-3 col-xxxl-2 col-xxl-3 no-margin">
                    <template class="wlm3-form-group">
                        {
                            name  : 'rss_ip_limit',
                            type  : 'number',
                            min   : '0',
                            value : '<?php echo esc_js($this->get_option('rss_ip_limit') + 0); ?>',
                            addon_right : 'IPs per day',
                            group_class : 'no-margin',
                            'data-initial' : '<?php echo esc_js($this->get_option('rss_ip_limit') + 0); ?>',
                            class : 'text-center rss-ip-limit',
                            help_block : '<?php echo esc_js(__('Set the field to 0 to disable.', 'wishlist-member')); ?>',
                        }
                    </template>
                </div>
            </div>
        </div>
    </div></div>
