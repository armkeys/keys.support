<?php
$post_types    = get_post_types('', 'objects');
$post_statuses = get_post_statuses();

$admin_page   = admin_url('admin.php?page=' . $this->menu_id . '&wl=setup/payperposts/posts/');
$wp_edit_link = admin_url('post.php?action=edit&post=');

$pay_per_post = false;
// Get requested post.
if ($virtual_path) {
    $pay_per_post = get_post(array_pop($virtual_path));
}

$modal_footer = <<<STRING
	<button class="btn -bare modal-cancel">
		<span>Close</span>
	</button>
	<button class="modal-save-and-continue btn -primary">
		<i class="wlm-icons">save</i>
		<span>Save</span>
	</button>
	&nbsp;
	<button class="modal-save-and-close btn -success">
		<i class="wlm-icons">save</i>
		<span>Save &amp; Close</span>
	</button>
STRING;

$tab_footer = <<<STRING
	<a href="{$admin_page}" class="btn -primary -ajax-btn">
		<i class="wlm-icons">levels_icon</i>
		<span>Return to Pay Per Posts</span>
	</a>
STRING;


if (! $pay_per_post) {
    $ppps_grouped = $this->get_pay_per_posts(['post_title', 'post_status'], true, '%' . wlm_trim(wlm_get_data()['search']) . '%');
    // Filter out invalid post types.
    $ppps_grouped = array_intersect_key($ppps_grouped, $post_types);

    $filter = wlm_trim(wlm_get_data()['filter']);
    if (! isset($ppps_grouped[ $filter ])) {
        $filter = '';
    }
    if ($filter) {
        $ppps = $ppps_grouped[ $filter ];
    } else {
        $ppps = [];
        foreach ($ppps_grouped as $x) {
            $ppps = array_merge($ppps, $x);
        }
    }

    $x = wlm_get_data()['howmany'];
    if ($x) {
        $this->save_option('payperposts-pagination-size', $x);
    }
    $pagination_size = wlm_or($this->get_option('payperposts-pagination-size'), 10);

    $pagination = new \WishListMember\Pagination(count($ppps), $pagination_size, wlm_get_data()['offset'], 'offset', sprintf('%s&search=%s&filter=%s', $admin_page, wlm_get_data()['search'], wlm_get_data()['filter']), $this->pagination_items);

    $ppps = array_slice($ppps, $pagination->from - 1, $pagination->per_page);

    include 'posts/list.php';
} else {
    include 'posts/edit.php';
}

?>
<script type="text/javascript">
    var $ppp_admin_page = <?php echo json_encode($admin_page); ?>;
</script>
<style type="text/css">
    #ppp-email-notification-settings .modal-body .-holder {
        display: none;
    }
    #ppp-email-notification-settings .modal-body.newuser .-holder.newuser,
    #ppp-email-notification-settings .modal-body.incomplete .-holder.incomplete {
        display: block;
    }

</style>
