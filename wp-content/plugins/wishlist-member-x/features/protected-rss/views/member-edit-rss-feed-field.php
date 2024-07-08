<div class="row">
    <div class="col pr-0">
            <div class="form-group -url-group">
            <label class="form-label" for=""><?php esc_html_e('WishList Member RSS Feed URL', 'wishlist-member'); ?></label>
            <input type="text" value="<?php echo esc_attr($profileuser->wlm_feed_url); ?>" id="wlm_feed_url" class="form-control copyable" readonly="readonly" tooltip_size="md" data-lpignore="true">
        </div>
    </div>
    <div class="col-auto">
        <label>&nbsp;</label>
        <button type="button" class="btn btn-success form-control -condensed" id="reset-rss-feed">
            <?php esc_html_e('Change Feed URL', 'wishlist-member'); ?>
        </button>
    </div>
</div>
