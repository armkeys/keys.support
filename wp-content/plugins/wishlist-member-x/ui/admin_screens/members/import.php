<?php
    $api_queue   = new \WishListMember\API_Queue();
    $queue_count = $api_queue->count_queue('import_member_queue', 0);
?>
<div class="page-header">
    <div class="row">
        <div class="col-md-9 col-sm-9 col-xs-8">
            <h2 class="page-title">
                <?php esc_html_e('Import Members', 'wishlist-member'); ?>
            </h2>
        </div>
        <div class="col-md-3 col-sm-3 col-xs-4">
            <?php require $this->plugin_dir3 . '/helpers/header-icons.php'; ?>
        </div>
    </div>
</div>
<div class="content-wrapper">
<?php
if ($queue_count > 0) {
    $in_queue = $this->get_option('import_member_queue_count');
    if (! $in_queue || $in_queue <= 0) {
        $this->save_option('import_member_queue_count', $queue_count);
        $in_queue = $queue_count;
    }
    include $this->plugin_dir3 . '/ui/admin_screens/members/import/queue.php';
}
    require $this->plugin_dir3 . '/ui/admin_screens/members/import/form.php';
?>
</div>
