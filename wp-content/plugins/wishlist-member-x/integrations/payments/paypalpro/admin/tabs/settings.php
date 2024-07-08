<form>
    <div class="row">
    <?php include_once WLM_PLUGIN_DIR . '/integrations/payments/paypalec/assets/common.php'; ?>
        <div class="col-auto mb-4"><?php echo wp_kses_post($config_button); ?></div>
    </div>
    <input type="hidden" class="-url" name="paypalprothankyou" />
    <input type="hidden" name="action" value="admin_actions" />
    <input type="hidden" name="WishListMemberAction" value="save" />
</form>
