<?php
global $current_user, $wpdb;
wlm_set_time_limit(0);
$user_search     = stripslashes((string) wlm_get_data()['wlm_search_term']);
$default_filters = [
    'usersearch'    => $user_search,
    'transactionid' => trim(stripslashes((string) wlm_get_data()['transactionid'])),
    'useraddress'   => trim(stripslashes((string) wlm_get_data()['useraddress'])),
    'level'         => stripslashes((string) wlm_get_data()['level']),
    'status'        => stripslashes((string) wlm_get_data()['status']),
    'sequential'    => stripslashes((string) wlm_get_data()['sequential']),
    'date_type'     => stripslashes((string) wlm_get_data()['date_type']),
    'from_date'     => stripslashes((string) wlm_get_data()['from_date']),
    'to_date'       => stripslashes((string) wlm_get_data()['to_date']),
];
$sort_request    = wlm_get_data()['s'];

if (empty($sort_request)) {
    $sort_request = 'r;d';
}
list($sort_request, $sortorder) = explode(';', $sort_request);
switch ($sort_request) {
    case 'n':
        $sortby = 'display_name';
        break;
    case 'u':
        $sortby = 'user_login';
        break;
    case 'e':
        $sortby = 'user_email';
        break;
    case 'r':
        $sortby = 'user_registered';
        break;
    case 's':
        $sortby = '';
        break;
    case 'p':
        $sortby = '';
        break;
    default:
        $sortby = '';
}
if ('d' !== $sortorder) {
    $sortorder = 'a';
}
$sortorderflip = ( 'd' === $sortorder ) ? ';a' : ';d';
$sortord       = 'd' === $sortorder ? 'DESC' : 'ASC';

    // Needs to be updated.
$lvl = ( isset(wlm_get_data()['level']) ? wlm_get_data()['level'] : '' );
if (! $lvl) {
    $lvl = '%';
}
switch ($lvl) {
    case 'nonmembers':
        $ids = ['-'];
        $ids = array_merge($ids, $this->member_ids());
        break;
    case 'incomplete':
        $ids = $wpdb->get_col("SELECT `ID` FROM `{$wpdb->users}` WHERE `user_login` REGEXP 'temp_[a-f0-9]{32}' AND `user_login`=`user_email`");
        break;
    default:
        if ('%' !== $lvl) {
            $ids = $this->member_ids($lvl);
        } else {
            $ids = '';
        }
}

$howmany = $this->get_option('member_page_pagination');
if (is_numeric(wlm_get_data()['howmany']) || ! $howmany || 'Show All' == wlm_get_data()['howmany']) {
    $howmany = wlm_get_data()['howmany'];
    if (! $howmany) {
        $howmany = $this->pagination_items[1];
    }
    if (! in_array($howmany, $this->pagination_items)) {
        $howmany = $this->pagination_items[1];
    }
    // We only save if not show all.
    if ('Show All' !== $howmany) {
        $this->save_option('member_page_pagination', $howmany);
    }
}
$howmany = 'Show All' === $howmany ? 999999999 : $howmany;

// Check if user wants to save the search.
$save_search = isset(wlm_get_data()['save_search']) ? wlm_get_data()['save_search'] : false;
if ($save_search) {
    $save_searchname = isset(wlm_get_data()['save_searchname']) && '' != wlm_get_data()['save_searchname'] ? wlm_get_data()['save_searchname'] : time();
    $data            = [
        'option_name'  => 'SavedSearch - ' . wlm_get_data()['save_searchname'],
        'option_value' => wlm_maybe_serialize($default_filters),
    ];
    $wpdb->insert($this->table_names->options, $data);
}

wlm_get_data()['offset'] = isset(wlm_get_data()['offset']) ? wlm_get_data()['offset'] : 0;
$wp_user_search          = new \WishListMember\User_Search($user_search, wlm_get_data()['offset'], '', $ids, $sortby, $sortord, $howmany, $default_filters);

    // Pagination.
$offset = wlm_get_data()['offset'] - 1;
if ($offset < 0) {
    $offset = 0;
}
$perpage = $wp_user_search->users_per_page;  // posts per page
if (0 === (int) $perpage) {
    $perpage = $howmany;
}
$total_users_cnt = null == $wp_user_search->total_users_for_query ? $wp_user_search->total_users : $wp_user_search->total_users_for_query;
$offset          = $offset * $perpage;
$current_page    = $offset / $perpage + 1;
++$offset;
$total_pages = ceil($total_users_cnt / $perpage);
$this->Preload_UserLevelsMeta($wp_user_search->results);

// Get Membership Levels.
$wpm_levels = $this->get_option('wpm_levels');

// Let see if the following are set, we need them to display some options.
$is_sequential_set   = false;
$is_approval_set     = false;
$is_confirmation_set = false;
foreach ($wpm_levels as $key => $value) {
    if (isset($value['upgradeMethod']) && ! empty($value['upgradeMethod'])) {
        $is_sequential_set = true;
    }
    if (isset($value['requireemailconfirmation']) && $value['requireemailconfirmation']) {
        $is_confirmation_set = true;
    }
    if (isset($value['requireadminapproval']) && $value['requireadminapproval']) {
        $is_approval_set = true;
    }
    if (isset($value['requireadminapproval_integrations']) && $value['requireadminapproval_integrations']) {
        $is_approval_set = true;
    }
    if ($is_sequential_set && $is_confirmation_set && $is_approval_set) {
        break;
    }
}

$incomplete_count = $this->get_incompleteregistration_count();
$member_count     = $this->member_ids(null, null, true);
// $member_count = count( $member_count );
$nonmember_count = $this->non_member_count();
$wpuser_count    = $member_count + $nonmember_count;

$tbl_collapse = $this->get_option('wlm_toggle_user_table');
$tbl_collapse = $tbl_collapse ? $tbl_collapse : 'expand';
$icollapse1   = 'expand' === $tbl_collapse ? '' : 'd-none';
$icollapse2   = 'expand' === $tbl_collapse ? 'd-none' : '';

$xcollapse2 = 'minimal' === $tbl_collapse ? '' : 'd-none';
$xcollapse1 = 'minimal' === $tbl_collapse ? 'd-none' : '';

$form_action  = "?page={$this->menu_id}&wl=" . ( isset(wlm_get_data()['wl']) ? wlm_get_data()['wl'] : 'members/manage' );
$url_keys     = array_intersect_key(
    wlm_get_data(true),
    [
        'wlm_search_term' => '',
        'transactionid'   => '',
        'useraddress'     => '',
        'level'           => '',
        'sequential'      => '',
        'date_type'       => '',
        'from_date'       => '',
        'to_date'         => '',
        'status'          => '',
        's'               => '',
    ]
);
$form_action .= '&' . build_query($url_keys);

$list_levels = [
    'members'    => ['name' => 'Members'],
    'nonmembers' => [
        'name'  => 'Non-Members',
        'count' => $nonmember_count,
    ],
    'incomplete' => [
        'name'  => 'Incomplete Registrations',
        'count' => $incomplete_count,
    ],
];
foreach ($wpm_levels as $key => $value) {
    $list_levels[ $key ] = ['name' => $value['name']];
}
$list_status = [
    'active'               => 'Active',
    'inactive'             => 'Inactive',
    'cancelled'            => 'Cancelled',
    'expired'              => 'Expired',
    'scheduled'            => 'Scheduled',
    'unconfirmed'          => 'Unconfirmed',
    'forapproval'          => 'Needs Approval',
    'sequential_cancelled' => 'Sequential Upgrade Stopped',
];

$list_daterange = [
    'registration_date' => 'Registration Date',
    'cancelled_date'    => 'Cancelation Date',
    'expiration_date'   => 'Expiration Date',
];


$collapsed_levels_markup = <<<string
<span class="collapsed-levels %s">
	<i class="wlm-icons md-20 -expired color-orange" title="Expired">timer_off</i>
	<i class="wlm-icons md-20 -cancelled color-red" title="Cancelled">cancelled_icon</i>
	<i class="wlm-icons md-20 -unconfirmed color-blue02" title="Unconfirmed">needs_confirm</i>
	<i class="wlm-icons md-20 -pending color-blue02" title="Needs Approval">needs_approval</i>
	<i class="wlm-icons md-20 -scheduled color-blue02" title="Scheduled">date_range</i>
	<i class="wlm-icons md-20 -active color-green" title="Active">active_icon</i>
	<span class="level-name">%s</span>
</span>
string;

$collapsed_ppp_markup = <<<string
<span class="collapsed-levels -active">
	<i class="wlm-icons md-20 -active" title="Pay Per Posts">description</i>
	<span class="level-name">Pay Per Posts</span>
</span>
string;

?>
<script type="text/javascript">
    var collapsed_levels_markup = <?php echo json_encode(str_replace('%s', '', $collapsed_levels_markup)); ?>
</script>
<div class="page-header -manage-members">
    <div class="row no-gutters">
        <div class="col-sm-6 col-md-6 col-lg-2 order-lg-0">
            <h2 class="page-title">
                <?php esc_html_e('Members', 'wishlist-member'); ?>
                <a href="#" class="btn -primary -icon-only -success -rounded add-user-btn"><i class="wlm-icons">add</i></a>
            </h2>
        </div>
        <div class="col-sm-6 col-md-6 col-lg-1 order-lg-2">
            <?php require $this->plugin_dir3 . '/helpers/header-icons.php'; ?>
        </div>
        <div class="col-sm-12 col-md-12 col-lg-9 order-lg-1 mt-sm-2 mt-lg-0 search-bar">
            <form method="get" target="_parent" id="search-form" action="<?php echo esc_url($form_action); ?>">
                <?php
                    // Lets add the querystring in hidden fields.
                    // This is needed since we are passing form tru GET.
                    $retain_keys = ['page', 'wl'];
                foreach (wlm_get_data(true) as $key => $content) {
                    if (in_array($key, $retain_keys)) {
                        printf('<input type="hidden" name="%s" value="%s">', esc_attr($key), esc_attr($content));
                    }
                }
                ?>
                <div class="input-group">
                    <label for="" class="sr-only">Search</label>
                    <input type="text" class="form-control" value="<?php echo esc_attr($user_search); ?>" name="wlm_search_term" id="post-search-input" placeholder="Search Users">
                    <div style="width: 220px" class="input-group-append">
                        <select class="form-control wlm-select" name="level" id="search-levels" tabindex="-1" aria-hidden="true">
                            <option value=""><?php esc_html_e('- All Users -', 'wishlist-member'); ?></option>
                            <?php foreach ($list_levels as $key => $value) : ?>
                                <option value="<?php echo esc_attr($key); ?>"
                                                          <?php
                                                            if (wlm_get_data()['level'] == $key) {
                                                                echo " selected='true'";
                                                            }
                                                            ?>
                                ><?php echo esc_html($value['name']); ?> <?php
                                if (isset($value['count'])) {
                                    echo esc_html("({$value['count']})");
                                }
                                ?>
</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="width: 120px" class="input-group-append">
                        <select class="form-control wlm-select" name="status" id="filter_status" tabindex="-1" aria-hidden="true">
                            <option value="">- All -</option>
                            <?php foreach ($list_status as $key => $value) : ?>
                                <option value="<?php echo esc_attr($key); ?>"
                                                          <?php
                                                            if ($default_filters['status'] == $key) {
                                                                echo " selected='true'";
                                                            }
                                                            ?>
                                ><?php echo esc_html($value); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="input-group-append">
                        <button class="btn -default -icon search-btn btn-block" type="button">
                            <i class="wlm-icons">search</i>
                        </button>
                    </div>
                    <div class="input-group-append ml-3 mr-lg-2">
                        <button type="button" class="btn -default -condensed -no-icon advancesearch-btn">
                            <span><?php esc_html_e('Advanced Search', 'wishlist-member'); ?></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Pagination Starts -->
<div class="header-tools -no-border">
    <div class="row justify-content-between">
        <div class="col-sm-12 col-md-4">
            <div class="form-group mb-sm-2">
                <label class="sr-only" for=""><?php esc_html_e('Action', 'wishlist-member'); ?></label>
                <select class="form-control wlm-select level-actions" name="" id="" style="width: 100%">
                    <option value="">- Select an Action -</option>
                    <option value="move">Move to Level</option>
                    <option value="add">Add to Level</option>
                    <option value="remove">Remove from Level</option>
                    <option disabled="disabled">------</option>
                    <option value="addpost">Add Pay Per Post</option>
                    <option value="removepost">Remove Pay Per Post</option>
                    <option disabled="disabled">------</option>
                    <option value="cancel">Cancel from Level</option>
                    <option value="uncancel">Uncancel from Level</option>
                    <?php if ($is_confirmation_set) : ?>
                        <option disabled="disabled" class="text-center">------</option>
                        <option value="confirm">Confirm Subscription to Level</option>
                        <option value="unconfirm">Unconfirm Subscription to Level</option>
                    <?php endif; ?>
                    <?php if ($is_approval_set) : ?>
                        <option disabled="disabled">------</option>
                        <option value="approve">Approve Registration to Level</option>
                        <option value="unapprove">Unapprove Registration to Level</option>
                    <?php endif; ?>
                    <?php if ($is_sequential_set) : ?>
                        <option disabled="disabled">------</option>
                        <option value="onsequential">Turn On Sequential Upgrade</option>
                        <option value="offsequential">Turn Off Sequential Upgrade</option>
                    <?php endif; ?>
                    <option disabled="disabled">------</option>
                    <option value="subscribe_email">Subscribe to Email Broadcast</option>
                    <option value="unsubscribe_email">Unsubscribe to Email Broadcast</option>
                    <option disabled="disabled">------</option>
                    <option value="clear_scheduled">Clear Scheduled Actions</option>
                    <option value="delete_member">Delete Selected Members</option>
                    <option disabled="disabled">------</option>
                    <option value="resend_email_confirmation_request"><?php esc_html_e('Resend Email Confirmation Request', 'wishlist-member'); ?></option>
                    <option value="resend_incomplete_registration_email"><?php esc_html_e('Resend Incomplete Registration Email', 'wishlist-member'); ?></option>
                </select>
            </div>
        </div>

        <div class="col-sm-12 col-md-8">
            <div class="pagination form-inline">
                <?php if ($total_users_cnt && $total_users_cnt > $this->pagination_items[0]) : ?>
                        <div class="input-group icon-group ml-lg-auto mr-4">
                            <a href="#" title="Minimal table view" class="btn -icon-only toggle-collapse-table<?php echo esc_attr('minimal' === $tbl_collapse ? ' active' : ''); ?> mr-1">
                                <i class="wlm-icons md-24">reorder</i>
                                <input type="hidden" name="wlm_toggle_user_table" value="minimal" />
                                <input type="hidden" name="action" value="admin_actions" />
                                <input type="hidden" name="WishListMemberAction" value="save" />
                            </a>
                            <a href="#" title="Collapsed table view" class="btn -icon-only toggle-collapse-table<?php echo esc_attr('collapse' === $tbl_collapse ? ' active' : ''); ?> mr-1">
                                <i class="wlm-icons md-24">dehaze</i>
                                <input type="hidden" name="wlm_toggle_user_table" value="collapse" />
                                <input type="hidden" name="action" value="admin_actions" />
                                <input type="hidden" name="WishListMemberAction" value="save" />
                            </a>
                                <a href="#" title="Expanded table view" class="btn -icon-only toggle-collapse-table<?php echo esc_attr('expand' === $tbl_collapse ? ' active' : ''); ?>">
                                    <i class="wlm-icons md-24">view_agenda</i>
                                    <input type="hidden" name="wlm_toggle_user_table" value="expand" />
                                    <input type="hidden" name="action" value="admin_actions" />
                                    <input type="hidden" name="WishListMemberAction" value="save" />
                                </a>
                        </div>
                        <?php if ($perpage <= $total_users_cnt) : ?>
                            <div class="input-group ml-sm-auto ml-lg-0">
                                <div class="input-group-prepend">
                                    <span class="text-muted pr-2">
                                        <div role="presentation" class="dropdown mt-9px">
                                            <a href="#" class="dropdown-toggle" id="drop-page" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                                <?php echo number_format($offset, 0, '.', ','); ?> - <?php echo (int) ( ( $perpage * $current_page ) > $total_users_cnt ? number_format($total_users_cnt, 0, '.', ',') : number_format($perpage * $current_page, 0, '.', ',') ); ?>
                                            </a> of <?php echo number_format($total_users_cnt, 0, '.', ','); ?>
                                            <ul class="dropdown-menu" id="menu1" aria-labelledby="drop-page">
                                                <?php foreach ($this->pagination_items as $key => $value) : ?>
                                                    <a class="dropdown-item" target="_parent" href="<?php echo esc_url($form_action . '&howmany=' . $value); ?>"><?php echo esc_html($value); ?></a>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    </span>
                                    <?php if ($current_page > 1) : ?>
                                        <a target="_parent" href="<?php echo esc_url($form_action . '&offset=1'); ?>" class="mt-6px"><i class="wlm-icons md-26">first_page</i></a>
                                    <?php else : ?>
                                        <a class="mt-6px text-muted disabled" disabled='disabled'><i class="wlm-icons md-26">first_page</i></a>
                                    <?php endif; ?>

                                    <?php
                                    if ($current_page <= 1) {
                                        $previous_link = $form_action . '&offset=' . $total_pages;
                                    } else {
                                        $previous_link = $form_action . '&offset=' . ( $current_page - 1 );
                                    }
                                    ?>
                                    <a target="_parent" href="<?php echo esc_url($previous_link); ?>" class="mt-6px"><i class="wlm-icons md-26">keyboard_arrow_left</i></a>
                                </div>
                                <input type="text" value="<?php echo esc_attr($current_page); ?>" class="form-control text-center pagination-pagenum" data-pages="<?php echo esc_attr($total_pages); ?>" data-link="<?php echo esc_attr($form_action . '&offset='); ?>" data-lpignore="true">
                                <div class="input-group-append">
                                    <span class="mt-9px"> of <?php echo (int) $total_pages; ?></span>
                                    <?php
                                    if ($current_page < $total_pages) {
                                        $next_link = $form_action . '&offset=' . ( $current_page + 1 );
                                    } else {
                                        $next_link = $form_action . '&offset=1';
                                    }
                                    ?>
                                    <a target="_parent" href="<?php echo esc_url($next_link); ?>" class="mt-6px"><i class="wlm-icons md-26">keyboard_arrow_right</i></a>

                                    <?php if ($current_page < $total_pages) : ?>
                                        <a target="_parent" href="<?php echo esc_url($form_action . '&offset=' . $total_pages); ?>" class="mt-6px"><i class="wlm-icons md-26">last_page</i></a>
                                    <?php else : ?>
                                        <a class="mt-6px text-muted disabled" disabled='disabled'><i class="wlm-icons md-26">last_page</i></a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else : ?>
                            <div class="input-group ml-sm-auto ml-lg-0">
                                <div class="input-group-prepend">
                                    <span class="text-muted pr-2">
                                        <div role="presentation" class="dropdown mt-9px">
                                            <a href="#" class="dropdown-toggle" id="drop-page" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                                <?php echo number_format($offset, 0, '.', ','); ?> - <?php echo (int) ( ( $perpage * $current_page ) > $total_users_cnt ? number_format($total_users_cnt, 0, '.', ',') : number_format($perpage * $current_page, 0, '.', ',') ); ?>
                                            </a> of <?php echo number_format($total_users_cnt, 0, '.', ','); ?>
                                            <ul class="dropdown-menu" id="menu1" aria-labelledby="drop-page">
                                                <?php foreach ($this->pagination_items as $key => $value) : ?>
                                                    <a class="dropdown-item" target="_parent" href="<?php echo esc_url($form_action . '&howmany=' . $value); ?>"><?php echo esc_html($value); ?></a>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>
                <?php else : ?>
                        <div class="input-group icon-group ml-lg-auto mr-4">
                            <a href="#" title="Minimal table view" class="btn -icon-only toggle-collapse-table<?php echo esc_attr('minimal' === $tbl_collapse ? ' active' : ''); ?> mr-1">
                                <i class="wlm-icons md-24">reorder</i>
                                <input type="hidden" name="wlm_toggle_user_table" value="minimal" />
                                <input type="hidden" name="action" value="admin_actions" />
                                <input type="hidden" name="WishListMemberAction" value="save" />
                            </a>
                            <a href="#" title="Collapsed table view" class="btn -icon-only toggle-collapse-table<?php echo esc_attr('collapse' === $tbl_collapse ? ' active' : ''); ?> mr-1">
                                <i class="wlm-icons md-24">dehaze</i>
                                <input type="hidden" name="wlm_toggle_user_table" value="collapse" />
                                <input type="hidden" name="action" value="admin_actions" />
                                <input type="hidden" name="WishListMemberAction" value="save" />
                            </a>
                                <a href="#" title="Expanded table view" class="btn -icon-only toggle-collapse-table<?php echo esc_attr('expand' === $tbl_collapse ? ' active' : ''); ?>">
                                    <i class="wlm-icons md-24">view_agenda</i>
                                    <input type="hidden" name="wlm_toggle_user_table" value="expand" />
                                    <input type="hidden" name="action" value="admin_actions" />
                                    <input type="hidden" name="WishListMemberAction" value="save" />
                                </a>
                        </div>
                        <div class="input-group ml-sm-auto ml-lg-0">
                            <div class="input-group-prepend">
                                <span class="text-muted pr-2">
                                    <div role="presentation" class="dropdown mt-9px">
                                        <a href="#" class="dropdown-toggle" id="drop-page" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                            <?php echo (int) $offset; ?> - <?php echo (int) ( ( $perpage * $current_page ) > $total_users_cnt ? $total_users_cnt : $perpage * $current_page ); ?>
                                        </a> of <?php echo (int) $total_users_cnt; ?>
                                        <ul class="dropdown-menu" id="menu1" aria-labelledby="drop-page">
                                            <?php foreach ($this->pagination_items as $key => $value) : ?>
                                                <a class="dropdown-item" target="_parent" href="<?php echo esc_url($form_action . '&howmany=' . $value); ?>"><?php echo esc_html($value); ?></a>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </span>
                            </div>
                        </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <?php
            $filter_labels   = [
                'usersearch'    => 'Keyword',
                'transactionid' => 'Transaction ID',
                'level'         => 'Level',
                'status'        => 'Status',
                'sequential'    => 'Sequential',
                'date_type'     => 'Date Type',
                'from_date'     => 'Date From',
                'to_date'       => 'Date To',
            ];
            $search_criteria = [];
            foreach ($default_filters as $k => $v) {
                $v = wlm_trim($v);
                if ($v && ! empty($v)) {
                    if ('level' === $k) {
                        if (isset($wpm_levels[ $v ])) {
                            $search_criteria[] = "{$filter_labels[$k]} = {$list_levels[$v]['name']}";
                        } else {
                            $search_criteria[] = "{$list_levels[$v]['name']}";
                        }
                    } elseif ('status' === $k) {
                        $search_criteria[] = "{$filter_labels[$k]} = {$list_status[$v]}";
                    } elseif ('date_type' === $k) {
                        $search_criteria[] = "{$filter_labels[$k]} = {$list_daterange[$v]}";
                    } else {
                        $search_criteria[] = "{$filter_labels[$k]} = {$v}";
                    }
                }
            }
            ?>
        <?php if (count($search_criteria) > 0) : ?>
            <div class="col-lg-12 col-md-12 col-sm-12">
                <p class="no-margin">
                    <label class="no-margin"><?php esc_html_e('Search Criteria:', 'wishlist-member'); ?></label> <?php echo esc_html(implode(', ', $search_criteria)); ?>
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Pagination Ends -->

<div class="table-wrapper -special table-responsive">
    <table id="members-table" class="table table-condensed mm-table">
            <thead>
                <?php $sort_icon = 'a' === $sortorder ? 'arrow_drop_up' : 'arrow_drop_down'; ?>
                <tr>
                    <th class="text-center">
                        <div class="form-check -table-check-header">
                            <input value="" type="checkbox" class="chk-all form-check-input">
                            <label for="" class="form-check-label"></label>
                        </div>
                    </th>
                    <th>
                        <a target="_parent" href="<?php echo esc_url($form_action); ?>&s=u<?php echo rawurlencode('u' === $sort_request ? $sortorderflip : ''); ?>">
                            <?php esc_html_e('Username', 'wishlist-member'); ?>
                            <?php $this->tooltip(__('The unique Username. Click the Username to view and edit that individual Member account settings.', 'wishlist-member')); ?>
                                                            <?php
                                                            if ('u' === $sort_request) :
                                                                ?>
                            <span class="wlm-icons"><?php echo esc_html($sort_icon); ?></span><?php
                                                            endif; ?></a>
                    </th>
                    <th>
                        <a target="_parent" href="<?php echo esc_url($form_action); ?>&s=n<?php echo rawurlencode('n' === $sort_request ? $sortorderflip : ''); ?>">
                            <?php esc_html_e('Name', 'wishlist-member'); ?>
                            <?php $this->tooltip(__('The full Name of the Member.', 'wishlist-member')); ?>
                                                             <?php
                                                                if ('n' === $sort_request) :
                                                                    ?>
                            <span class="wlm-icons"><?php echo esc_html($sort_icon); ?></span><?php
                                                                endif; ?></a>
                    </th>
                    <th>
                        <a target="_parent" href="<?php echo esc_url($form_action); ?>&s=e<?php echo rawurlencode('e' === $sort_request ? $sortorderflip : ''); ?>">
                            <?php esc_html_e('Email', 'wishlist-member'); ?>
                            <?php $this->tooltip(__('The email address tied to the Member account.', 'wishlist-member')); ?>
                                                             <?php
                                                                if ('e' === $sort_request) :
                                                                    ?>
                            <span class="wlm-icons"><?php echo esc_html($sort_icon); ?></span><?php
                                                                endif; ?></a>
                    </th>
                    <th>
                        <?php esc_html_e('Status', 'wishlist-member'); ?>
                    </th>
                    <th>
                        <a target="_parent" href="<?php echo esc_url($form_action); ?>&s=r<?php echo rawurlencode('r' === $sort_request ? $sortorderflip : ''); ?>">
                            <?php esc_html_e('Date Registered', 'wishlist-member'); ?>
                                                             <?php
                                                                if ('r' === $sort_request) :
                                                                    ?>
                            <span class="wlm-icons"><?php echo esc_html($sort_icon); ?></span><?php
                                                                endif; ?></a>
                    </th>
                    <th class="text-center"></th>
                </tr>
            </thead>
                <?php if ($wp_user_search->total_users) : ?>
                    <?php
                    foreach ((array) $wp_user_search->results as $uid) :
                        $user = $this->get_user_data($uid);
                        ?>
                        <?php
                            $tempuser     = 'temp_' === substr($user->user_login, 0, 5) && 'temp_' . md5($user->wlm_origemail) == $user->user_login;
                            $xemail       = $tempuser ? $user->wlm_origemail : $user->user_email;
                            $wlUser       = new \WishListMember\User($user->ID);
                            $levels_count = count($wlUser->Levels);
                            wlm_add_metadata($wlUser->Levels);
                            $levels            = $wlUser->Levels;
                            $user_mlevels_name = [];
                        foreach ($wlUser->Levels as $x => $v) {
                            if (! isset($v->Name)) {
                                continue; // Dont include payperposts.
                            }
                            $statuses = [];
                            if ($v->Expired) {
                                $statuses[] = '-expired';
                            }
                            if ($v->Cancelled) {
                                $statuses[] = '-cancelled';
                            }
                            if ($v->UnConfirmed) {
                                $statuses[] = '-unconfirmed';
                            }

                            if ($v->Pending) {
                                $statuses[] = '-pending';
                            }
                            if (in_array('Scheduled', $v->Status)) {
                                $statuses[] = '-scheduled';
                            }

                            if (empty($statuses)) {
                                $statuses[] = '-active';
                            }

                            $lname = sprintf($collapsed_levels_markup, implode(' ', $statuses), $v->Name);

                            $user_mlevels_name[] = $lname;
                        }
                        if (wlm_arrval($wlUser, 'pay_per_posts', '_all_')) {
                            $user_mlevels_name[] = $collapsed_ppp_markup;
                        }
                        ?>
                        <tbody class="outer-tbody button-hover <?php echo esc_attr(apply_filters('wishlistmember_member_manage_user_tbody_additional_classes', '', $user->ID)); ?>"<?php echo wp_kses(apply_filters('wishlistmember_member_manage_user_tbody_attributes', '', $user), []); ?>>
                            <!-- Overview -->
                            <tr class="user-details-tr user-details-tr-<?php echo esc_attr($user->ID); ?>">
                                <td class="text-center">
                                    <div class="form-check -table-check-body">
                                        <input value="<?php echo esc_attr($user->ID); ?>" type="checkbox" class="chk-userid form-check-input" title="<?php echo esc_attr($user->ID); ?>">
                                        <label class="d-none form-check-label" for=""></label>
                                    </div>
                                        <a class="level-collapse pull-right" href="#" data-target=".level-details-tr-<?php echo esc_attr($uid); ?>" data-userid="<?php echo esc_attr($uid); ?>">
                                            <span class="level-details-tr-<?php echo esc_attr($uid); ?> level-arrows -down wlm-icons <?php echo esc_attr($xcollapse1); ?>">arrow_drop_down</span>
                                            <span class="level-details-tr-<?php echo esc_attr($uid); ?> level-arrows -right wlm-icons <?php echo esc_attr($xcollapse2); ?>">arrow_right</span>
                                        </a>
                                </td>
                                <td>
                                    <?php if ($tempuser) : ?>
                                        <?php esc_html_e('Incomplete Registration', 'wishlist-member'); ?><br /><a href="<?php echo esc_url($this->get_continue_registration_url($xemail)); ?>"><?php esc_html_e('Click here to complete.', 'wishlist-member'); ?></a>
                                    <?php else : ?>
                                    <span class='wlm-user-login'>
                                        <strong>
                                            <a href="#" data-userid="<?php echo esc_attr($uid); ?>" class="edit-user-btn"><?php echo esc_html($user->user_login); ?></a>
                                        </strong>
                                    </span>
                                    <?php endif; ?>
                                </td>
                                <td><span class='wlm-user-display-name'><?php echo esc_html($user->display_name); ?></span></td>
                                <td>
                                    <span class="wlm-user-email" title="<?php echo esc_attr($xemail); ?>" style="cursor: default;">
                                        <a href="mailto:<?php echo esc_attr($xemail); ?>"><?php echo esc_html($xemail); ?></a>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php
                                        $user->wlm_unsubscribe = empty($user->wlm_unsubscribe) ? 0 : 1;
                                        $table_status          = $user->wlm_unsubscribe ? 'table-status' : '';
                                        $active_stat           = $user->wlm_unsubscribe ? __('Inactive', 'wishlist-member') : __('Active', 'wishlist-member');
                                    ?>
                                    <a href="#" class="toggle-wlm-unsubscribe" dfield="wlm_unsubscribe" dicon="email" fieldval="<?php echo esc_attr($user->wlm_unsubscribe); ?>">
                                        <span title="<?php /* translators: 1: active/inactive */ printf(esc_attr__('Email Broadcasts (%s)', 'wishlist-member'), esc_attr($active_stat)); ?>" class="<?php echo esc_attr($table_status); ?>"><i class="wlm-icons md-24">email</i></span>
                                    </a>
                                    <?php if ($is_sequential_set) : ?>
                                        <?php
                                            $table_status = $user->sequential ? '' : 'table-status';
                                            $active_stat  = $user->sequential ? __('Active', 'wishlist-member') : __('Inactive', 'wishlist-member');
                                        ?>
                                        <a href="#" class="toggle-wlm-sequential" dfield="sequential" dicon="link" fieldval="<?php echo esc_attr($user->sequential); ?>">
                                            <span title="<?php /* translators: 1: active/inactive */ printf(esc_attr__('Sequential Upgrade (%s)', 'wishlist-member'), esc_attr($active_stat)); ?>" class="<?php echo esc_attr($table_status); ?>"><i class="wlm-icons md-24">link</i></span>
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td><span class='wlm-user-registered'><?php echo esc_html(wlm_date(get_option('date_format'), $this->user_registered($user, false))); ?></span></td>
                                <td class="text-center">
                                    <div class="btn-group-action" style="width: 90px">
                                        <a href="#" title="Edit Member" data-userid="<?php echo esc_attr($uid); ?>" class="btn edit-user-btn"><span class="wlm-icons md-24 -icon-only">edit</span></a>
                                        <a href="<?php echo esc_url(admin_url('user-edit.php?user_id=' . $uid)); ?>" target="_blank" title="WordPress Profile" class="btn"><span class="wlm-icons md-24 -icon-only">levels_icon</span></a>
                                        <?php if ($current_user->ID != $uid) : ?>
                                            <a href="#" title="Delete Member" data-userid="<?php echo esc_attr($uid); ?>" class="btn delete-user-btn"><span class="wlm-icons md-24 -icon-only">delete</span></a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <!-- Levels Details -->
                            <tr class="level-details-tr level-details-tr-<?php echo esc_attr($user->ID); ?>  <?php echo esc_attr($xcollapse1); ?>">
                                <td colspan="7" class="with-table">
                                    <table class="table -inner-table wlm-user-levels wlm-user-levels-<?php echo esc_attr($uid); ?> <?php echo count($user_mlevels_name) ? '' : 'levels0'; ?>">
                                        <!-- Toggle -->
                                        <thead>
                                            <tr class="more-details -top-level">
                                                <th></th>
                                                <th colspan="4">
                                                    <a class="level-collapse" href="#" data-target=".collapse<?php echo esc_attr($uid); ?>" data-userid="<?php echo esc_attr($uid); ?>">
                                                        <span class="collapse<?php echo esc_attr($uid); ?> level-arrows -down wlm-icons <?php echo esc_attr($icollapse1); ?>">arrow_drop_down</span>
                                                        <span class="collapse<?php echo esc_attr($uid); ?> level-arrows -right wlm-icons <?php echo esc_attr($icollapse2); ?>">arrow_right</span>
                                                        <span class="collapse<?php echo esc_attr($uid); ?> levelheader text <?php echo esc_attr($icollapse1); ?>"><?php esc_html_e('Levels', 'wishlist-member'); ?>: </span>
                                                        <span class="collapse<?php echo esc_attr($uid); ?> thelevels <?php echo esc_attr($icollapse2); ?> text"><?php echo wp_kses_post(implode('', $user_mlevels_name)); ?></span>
                                                    </a>
                                                    <a class="add-level-btn" href="#" data-userid="<?php echo esc_attr($uid); ?>"><i class="wlm-icons md-18">add_circle_outline</i> <span><?php esc_html_e('Add to Level', 'wishlist-member'); ?></span></a>
                                                </th>
                                            </tr>
                                        </thead>
                                        <!-- Levels -->
                                        <tbody class="inner-tbody <?php echo esc_attr($icollapse1); ?> collapse<?php echo esc_attr($uid); ?>" data-userid="<?php echo esc_attr($uid); ?>">
                                                <?php
                                                if (count($user_mlevels_name) > 0) {
                                                    include $this->plugin_dir3 . '/ui/admin_screens/members/manage/member_levels.php';
                                                }
                                                ?>
                                            <tr class="more-details">
                                                <td></td>
                                                <td style="padding-left: 10px;">
                                                    <a class="add-level-btn" href="#" data-userid="<?php echo esc_attr($uid); ?>"><i class="wlm-icons md-20">add_circle_outline</i> <span style="vertical-align: bottom; padding-left: 3px;">Add to Level</span></a>
                                                </td>
                                                <td colspan="4"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <?php
                            do_action('wishlistmember_member_manage_user_row', $uid, $tbl_collapse);
                            ?>
                        </tbody>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr><td colspan="6" class="text-center"><?php esc_html_e('No members found', 'wishlist-member'); ?></td></tr>
                <?php endif; ?>
    </table>
</div>
<!-- Pagination Starts -->
<div class="header-tools -no-border">
    <div class="row justify-content-between">
        <div class="col-12">
            <div class="pagination pull-right">
                <?php if ($total_users_cnt && $total_users_cnt > $this->pagination_items[0]) : ?>
                    <?php if ($perpage <= $total_users_cnt) : ?>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="text-muted pr-2">
                                    <div role="presentation" class="dropdown mt-9px">
                                        <a href="#" class="dropdown-toggle" id="drop-page" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                            <?php echo number_format($offset, 0, '.', ','); ?> - <?php echo (int) ( ( $perpage * $current_page ) > $total_users_cnt ? number_format($total_users_cnt, 0, '.', ',') : number_format($perpage * $current_page, 0, '.', ',') ); ?>
                                        </a> of <?php echo number_format($total_users_cnt, 0, '.', ','); ?>
                                        <ul class="dropdown-menu" id="menu1" aria-labelledby="drop-page">
                                            <?php foreach ($this->pagination_items as $key => $value) : ?>
                                                <a class="dropdown-item" target="_parent" href="<?php echo esc_url($form_action . '&howmany=' . $value); ?>"><?php echo esc_html($value); ?></a>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </span>
                                <?php if ($current_page > 1) : ?>
                                    <a target="_parent" href="<?php echo esc_url($form_action . '&offset=1'); ?>" class="mt-6px"><i class="wlm-icons md-26">first_page</i></a>
                                <?php else : ?>
                                    <a class="mt-6px text-muted disabled" disabled='disabled'><i class="wlm-icons md-26">first_page</i></a>
                                <?php endif; ?>

                                <?php
                                if ($current_page <= 1) {
                                    $previous_link = $form_action . '&offset=' . $total_pages;
                                } else {
                                    $previous_link = $form_action . '&offset=' . ( $current_page - 1 );
                                }
                                ?>
                                <a target="_parent" href="<?php echo esc_url($previous_link); ?>" class="mt-6px"><i class="wlm-icons md-26">keyboard_arrow_left</i></a>
                            </div>
                            <input type="text" value="<?php echo esc_attr($current_page); ?>" class="form-control text-center pagination-pagenum" data-pages="<?php echo esc_attr($total_pages); ?>" data-link="<?php echo esc_attr($form_action . '&offset='); ?>" data-lpignore="true">
                            <div class="input-group-append">
                                <span class="mt-9px"> of <?php echo (int) $total_pages; ?></span>
                                <?php
                                if ($current_page < $total_pages) {
                                    $next_link = $form_action . '&offset=' . ( $current_page + 1 );
                                } else {
                                    $next_link = $form_action . '&offset=1';
                                }
                                ?>
                                <a target="_parent" href="<?php echo esc_url($next_link); ?>" class="mt-6px"><i class="wlm-icons md-26">keyboard_arrow_right</i></a>

                                <?php if ($current_page < $total_pages) : ?>
                                    <a target="_parent" href="<?php echo esc_url($form_action . '&offset=' . $total_pages); ?>" class="mt-6px"><i class="wlm-icons md-26">last_page</i></a>
                                <?php else : ?>
                                    <a class="mt-6px text-muted disabled" disabled='disabled'><i class="wlm-icons md-26">last_page</i></a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else : ?>
                            <div style="width: auto" class="input-group pull-right">
                                <div class="input-group-prepend">
                                    <span class="text-muted pr-2">
                                        <div role="presentation" class="dropdown mt-9px">
                                            <a href="#" class="dropdown-toggle" id="drop-page" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                            <?php echo number_format($offset, 0, '.', ','); ?> - <?php echo (int) ( ( $perpage * $current_page ) > $total_users_cnt ? number_format($total_users_cnt, 0, '.', ',') : number_format($perpage * $current_page, 0, '.', ',') ); ?>
                                        </a> of <?php echo number_format($total_users_cnt, 0, '.', ','); ?>
                                            <ul class="dropdown-menu" id="menu1" aria-labelledby="drop-page">
                                                <?php foreach ($this->pagination_items as $key => $value) : ?>
                                                    <a class="dropdown-item" target="_parent" href="<?php echo esc_url($form_action . '&howmany=' . $value); ?>"><?php echo esc_html($value); ?></a>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    </span>
                                </div>
                            </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<!-- Pagination Ends -->

<!-- Modal -->

<div id="add-level-modal" data-id="add-level-modal" data-label="add_level_modal_label" data-title="Add Member to Level" data-classes="modal-lg" style="display:none">
    <div class="body">
        <div class="row mx-2">
            <div class="form-group col-md-12 membership-level-select">
                <label for="">Membership Levels</label>
                <select class="form-control wlm-levels wlm-levels-select" multiple="multiple" name="wlm_levels[]" style="width: 100%" required>
                    <?php foreach ($wpm_levels as $key => $value) : ?>
                        <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($value['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="row mx-2">
            <div class="form-group date-ranger col-md-5">
                <label for="">Registration Date <small>(<?php esc_html_e('optional)', 'wishlist-member'); ?></small></label>
                <div class="date-ranger-container">
                    <input id="DateRangePicker" type="text" class="form-control wlm-datetimepicker" name="registration_date">
                    <i class="wlm-icons">date_range</i>
                </div>
            </div>
        </div>
        <div class="row mx-2 justify-content-between">
            <div class="form-group col-md-5">
                <label for="">Email Notification</label>
                <select class="form-control wlm-levels wlm-levels-notification" name="level_email" style="width: 100%" placeholder="Email Notification">
                    <option value="sendlevel">Use Level Notification Settings</option>
                    <option value="send">Send Email Notification</option>
                    <option value="dontsend">Do NOT Send Email Notification</option>
                </select>
            </div>
            <div class="form-group col-md-5">
                <label for=""><?php esc_html_e('Email Confirmation', 'wishlist-member'); ?></label>
                <select class="form-control wlm-select wlm-levels wlm-levels-emailconfirmation" data-lastvalue="<?php echo esc_attr(wlm_or($this->get_option('admin_add_member_to_level_require_email_confirmation'), 'dontrequire')); ?>" name="require_email_confirmation" style="width: 100%">
                    <option value="uselevelsettings"><?php esc_html_e('Use Level Requirements', 'wishlist-member'); ?></option>
                    <option value="require"><?php esc_html_e('Require Confirmation', 'wishlist-member'); ?></option>
                    <option value="dontrequire"><?php esc_html_e('Do NOT Require Confirmation', 'wishlist-member'); ?></option>
                </select>
            </div>
        </div>
        <input type="hidden" name="userids" value="" />
        <input type="hidden" name="action" value="admin_actions" />
        <input type="hidden" name="WishListMemberAction" value="schedule_user_level" />
        <input type="hidden" name="level_action" value="add_user_level" />
    </div>
    <div class="footer">
        <button type="button" class="btn -bare" data-dismiss="modal"><?php esc_html_e('Close', 'wishlist-member'); ?></button>
        <button type="button" class="btn -primary save-button"><i class="wlm-icons">add_circle_outline</i> <span><?php esc_html_e('Add to Level', 'wishlist-member'); ?></span></button>
    </div>
</div>

<div id="move-level-modal" data-id="move-level-modal" data-label="move_level_modal_label" data-title="Move Member to Level" data-classes="modal-lg" style="display:none">
    <div class="body">
        <div class="form-group col-md-12 membership-level-select">
            <label for=""><?php esc_html_e('From Membership Level', 'wishlist-member'); ?></label>
            <select class="form-control wlm-levels wlm-levels-from" name="wlm_level_from" id="" style="width: 100%" required="required">
                <?php foreach ($wpm_levels as $key => $value) : ?>
                <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($value['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group col-md-12 membership-level-display">
            <label for=""><?php esc_html_e('From Membership Level', 'wishlist-member'); ?></label>
            <h4 class="level-name-holder">-<?php esc_html_e('Unknown-', 'wishlist-member'); ?></h1>
        </div>
        <div class="form-group  col-md-12">
            <label for=""><?php esc_html_e('To Membership Level', 'wishlist-member'); ?></label>
            <select class="form-control wlm-levels wlm-levels-to" name="wlm_levels" id="" style="width: 100%" required="required">
                <?php foreach ($wpm_levels as $key => $value) : ?>
                <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($value['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group date-ranger col-md-5">
            <label for=""><?php esc_html_e('Schedule', 'wishlist-member'); ?> <small>(Leave blank to move now)</small></label>
            <div class="date-ranger-container">
                <input id="DateRangePicker" type="text" class="form-control wlm-datetimepicker" name="schedule_date">
                <i class="wlm-icons">date_range</i>
            </div>
        </div>
        <input type="hidden" name="userids" value="" />
        <input type="hidden" name="action" value="admin_actions" />
        <input type="hidden" name="WishListMemberAction" value="schedule_user_level" />
        <input type="hidden" name="level_action" value="move_user_level" />
    </div>
    <div class="footer">
        <button type="button" class="btn -bare" data-dismiss="modal"><?php esc_html_e('Close', 'wishlist-member'); ?></button>
        <button type="button" class="btn -primary move-level-button"><i class="wlm-icons">swap_horiz</i> <span>Move to Level</span></button>
    </div>
</div>

<div id="cancel-level-modal" data-id="cancel-level-modal" data-label="cancel_level_modal_label" data-title="Cancel Member from Level" data-classes="modal-lg" style="display:none">
    <div class="body">
        <div class="row mx-2">
            <div class="form-group col-md-12 membership-level-select">
                <label for=""><?php esc_html_e('Membership Level', 'wishlist-member'); ?></label>
                <select class="form-control wlm-levels wlm-levels-select" multiple="multiple" name="wlm_levels[]" id="" style="width: 100%">
                    <?php foreach ($wpm_levels as $key => $value) : ?>
                    <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($value['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group col-md-12 membership-level-display">
                <label for=""><?php esc_html_e('From Membership Level', 'wishlist-member'); ?></label>
                <h4 class="level-name-holder">-<?php esc_html_e('-Unknown-', 'wishlist-member'); ?></h4>
            </div>
        </div>
        <div class="row mx-2">
            <div class="form-group date-ranger col-md-5">
                <label for=""><?php esc_html_e('Schedule', 'wishlist-member'); ?> <small>(<?php esc_html_e('Leave blank to cancel now)', 'wishlist-member'); ?></small></label>
                <div class="date-ranger-container">
                    <input id="DateRangePicker" type="text" class="form-control wlm-datetimepicker wlm-datetimepicker-nopast" name="schedule_date">
                    <i class="wlm-icons">date_range</i>
                </div>
            </div>
            <div class="form-group col-md-2">&nbsp;</div>
            <div class="form-group col-md-5">
                <label for="">Email Notification</label>
                <select class="form-control wlm-levels wlm-levels-notification" name="level_email" style="width: 100%" placeholder="Email Notification">
                    <option value="sendlevel">Use Level Notification Settings</option>
                    <option value="send">Send Email Notification</option>
                    <option value="dontsend">Do NOT Send Email Notification</option>
                </select>
            </div>
        </div>
        <input type="hidden" name="userids" value="" />
        <input type="hidden" name="action" value="admin_actions" />
        <input type="hidden" name="WishListMemberAction" value="schedule_user_level" />
        <input type="hidden" name="level_action" value="cancel_user_level" />
    </div>
    <div class="footer">
        <button type="button" class="btn -bare" data-dismiss="modal"><?php esc_html_e('Close', 'wishlist-member'); ?></button>
        <button type="button" class="btn -primary save-button"><i class="wlm-icons">close</i> <span><?php esc_html_e('Cancel from Level', 'wishlist-member'); ?></span></button>
    </div>
</div>

<div id="uncancel-level-modal" data-id="uncancel-level-modal" data-label="uncancel_level_modal_label" data-title="Uncancel Member from Level" data-classes="modal-lg" style="display:none">
    <div class="body">
        <div class="row mx-2">
            <div class="form-group col-md-12 membership-level-select">
                <label for=""><?php esc_html_e('Membership Level', 'wishlist-member'); ?></label>
                <select class="form-control wlm-levels wlm-levels-select" multiple="multiple" name="wlm_levels[]" id="" style="width: 100%">
                    <?php foreach ($wpm_levels as $key => $value) : ?>
                    <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($value['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group col-md-12 membership-level-display">
                <label for=""><?php esc_html_e('From Membership Level', 'wishlist-member'); ?></label>
                <h4 class="level-name-holder">-<?php esc_html_e('Unknown-', 'wishlist-member'); ?></h4>
            </div>
        </div>
        <div class="row mx-2">
            <div class="form-group col-md-7"></div>
            <div class="form-group col-md-5">
                <label for="">Email Notification</label>
                <select class="form-control wlm-levels wlm-levels-notification" name="level_email" style="width: 100%" placeholder="Email Notification">
                    <option value="sendlevel">Use Level Notification Settings</option>
                    <option value="send">Send Email Notification</option>
                    <option value="dontsend">Do NOT Send Email Notification</option>
                </select>
            </div>
        </div>
        <input type="hidden" name="userids" value="" />
        <input type="hidden" name="action" value="admin_actions" />
        <input type="hidden" name="WishListMemberAction" value="schedule_user_level" />
        <input type="hidden" name="level_action" value="uncancel_user_level" />
    </div>
    <div class="footer">
        <button type="button" class="btn -bare" data-dismiss="modal"><?php esc_html_e('Close', 'wishlist-member'); ?></button>
        <button type="button" class="btn -primary save-button"><i class="wlm-icons">replay</i> <span><?php esc_html_e('Uncancel from Level', 'wishlist-member'); ?></span></button>
    </div>
</div>

<div id="delete-level-modal" data-id="delete-level-modal" data-label="delete_level_modal_label" data-title="Remove Member from Level" data-classes="modal-lg" style="display:none">
    <div class="body">
        <div class="form-group col-md-12 membership-level-select">
            <label for=""><?php esc_html_e('From Membership Level', 'wishlist-member'); ?></label>
            <select class="form-control wlm-levels" multiple="multiple" name="wlm_levels[]" id="" style="width: 100%">
                <?php foreach ($wpm_levels as $key => $value) : ?>
                <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($value['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group col-md-12 membership-level-display">
            <label for=""><?php esc_html_e('From Membership Level', 'wishlist-member'); ?></label>
            <h4 class="level-name-holder">-<?php esc_html_e('Unknown-', 'wishlist-member'); ?></h1>
        </div>
        <div class="form-group date-ranger col-md-5">
            <label for=""><?php esc_html_e('Schedule', 'wishlist-member'); ?> <small>(Leave blank to remove now)</small></label>
            <div class="date-ranger-container">
                <input id="DateRangePicker" type="text" class="form-control wlm-datetimepicker wlm-datetimepicker-nopast" name="schedule_date">
                <i class="wlm-icons">date_range</i>
            </div>
        </div>
        <input type="hidden" name="userids" value="" />
        <input type="hidden" name="action" value="admin_actions" />
        <input type="hidden" name="WishListMemberAction" value="schedule_user_level" />
        <input type="hidden" name="level_action" value="delete_user_level" />
    </div>
    <div class="footer">
        <button type="button" class="btn -bare" data-dismiss="modal"><?php esc_html_e('Close', 'wishlist-member'); ?></button>
        <button type="button" class="btn -primary save-button"><i class="wlm-icons">remove_circle_outline</i> <span>Remove from Level</span></button>
    </div>
</div>

<div id="confirm-level-modal" data-id="confirm-level-modal" data-label="confirm_level_modal_label" data-title="Confirm Subscription to Level" data-classes="modal-md" style="display:none">
    <div class="body">
        <div class="form-group">
            <label for=""><?php esc_html_e('Membership Level', 'wishlist-member'); ?></label>
            <select class="form-control wlm-levels" name="wlm_levels" id="" style="width: 100%">
                <?php foreach ($wpm_levels as $key => $value) : ?>
                <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($value['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <input type="hidden" name="userids" value="" />
        <input type="hidden" name="action" value="admin_actions" />
        <input type="hidden" name="WishListMemberAction" value="schedule_user_level" />
        <input type="hidden" name="level_action" value="confirm_user_level" />
    </div>
    <div class="footer">
        <button type="button" class="btn -bare" data-dismiss="modal"><?php esc_html_e('Close', 'wishlist-member'); ?></button>
        <button type="button" class="btn -primary save-button"><i class="wlm-icons">check_circle_outline</i> <span>Confirm Subscription</span></button>
    </div>
</div>

<div id="unconfirm-level-modal" data-id="unconfirm-level-modal" data-label="unconfirm_level_modal_label" data-title="Unconfirm Subscription to Level" data-classes="modal-md" style="display:none">
    <div class="body">
        <div class="form-group">
            <label for=""><?php esc_html_e('Membership Level', 'wishlist-member'); ?></label>
            <select class="form-control wlm-levels" name="wlm_levels" id="" style="width: 100%">
                <?php foreach ($wpm_levels as $key => $value) : ?>
                <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($value['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <input type="hidden" name="userids" value="" />
        <input type="hidden" name="action" value="admin_actions" />
        <input type="hidden" name="WishListMemberAction" value="schedule_user_level" />
        <input type="hidden" name="level_action" value="unconfirm_user_level" />
    </div>
    <div class="footer">
        <button type="button" class="btn -bare" data-dismiss="modal"><?php esc_html_e('Close', 'wishlist-member'); ?></button>
        <button type="button" class="btn -primary save-button"><i class="wlm-icons">cancel_circle_outline</i> <span>Unconfirm Subscription</span></button>
    </div>
</div>

<div id="approve-level-modal" data-id="approve-level-modal" data-label="approve_level_modal_label" data-title="Approve Subscription to Level" data-classes="modal-md" style="display:none">
    <div class="body">
        <div class="form-group">
            <label for=""><?php esc_html_e('Membership Level', 'wishlist-member'); ?></label>
            <select class="form-control wlm-levels" name="wlm_levels" id="" style="width: 100%">
                <?php foreach ($wpm_levels as $key => $value) : ?>
                <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($value['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <input type="hidden" name="userids" value="" />
        <input type="hidden" name="action" value="admin_actions" />
        <input type="hidden" name="WishListMemberAction" value="schedule_user_level" />
        <input type="hidden" name="level_action" value="approve_user_level" />
    </div>
    <div class="footer">
        <button type="button" class="btn -bare" data-dismiss="modal"><?php esc_html_e('Close', 'wishlist-member'); ?></button>
        <button type="button" class="btn -primary save-button"><i class="wlm-icons">check_circle_outline</i> <span>Approve Subscription</span></button>
    </div>
</div>

<div id="unapprove-level-modal" data-id="unapprove-level-modal" data-label="unapprove_level_modal_label" data-title="Unapprove Subscription to Level" data-classes="modal-md" style="display:none">
    <div class="body">
        <div class="form-group">
            <label for=""><?php esc_html_e('Membership Level', 'wishlist-member'); ?></label>
            <select class="form-control wlm-levels" name="wlm_levels" id="" style="width: 100%">
                <?php foreach ($wpm_levels as $key => $value) : ?>
                <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($value['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <input type="hidden" name="userids" value="" />
        <input type="hidden" name="action" value="admin_actions" />
        <input type="hidden" name="WishListMemberAction" value="schedule_user_level" />
        <input type="hidden" name="level_action" value="unapprove_user_level" />
    </div>
    <div class="footer">
        <button type="button" class="btn -bare" data-dismiss="modal"><?php esc_html_e('Close', 'wishlist-member'); ?></button>
        <button type="button" class="btn -primary save-button"><i class="wlm-icons">cancel_circle_outline</i> <span>Unapprove Subscription</span></button>
    </div>
</div>

<div id="ppost-modal" data-id="ppost-modal" data-label="ppost_modal_label" data-title="Pay Per Post" data-classes="modal-md" style="display:none">
    <div class="body">
        <div class="form-group -ppp">
            <label for="">Select Post</label>
            <select class="form-control wlm-payperposts" name="wlm_payperposts" style="width: 100%">
            </select>
        </div>
        <input type="hidden" name="userids" value="" />
        <input type="hidden" name="action" value="admin_actions" />
        <input type="hidden" name="WishListMemberAction" value="schedule_user_level" />
        <input type="hidden" name="level_action" value="" />
    </div>
    <div class="footer">
        <button type="button" class="btn -bare" data-dismiss="modal"><?php esc_html_e('Close', 'wishlist-member'); ?></button>
        <button type="button" class="btn -primary save-button"><i class="wlm-icons">add_circle_outline</i><span>Add Pay Per Post</span></button>
    </div>
</div>

<div id="unschedule-level-modal" data-id="unschedule-level-modal" data-label="unschedule_level_modal_label" data-title="Unschedule" data-classes="modal-md" style="display:none">
    <div class="body">
        <p class="unschedule-message">** <?php esc_html_e('Message goes here **', 'wishlist-member'); ?></p>
        <input type="hidden" name="schedule_type" value="" />
        <input type="hidden" name="wlm_levels" value="" />
        <input type="hidden" name="userids" value="" />
        <input type="hidden" name="action" value="admin_actions" />
        <input type="hidden" name="WishListMemberAction" value="schedule_user_level" />
        <input type="hidden" name="level_action" value="unschedule_user_level" />
    </div>
    <div class="footer">
        <button type="button" class="btn -bare" data-dismiss="modal"><?php esc_html_e('Cancel', 'wishlist-member'); ?></button>
        <button type="button" class="btn -primary save-button"><span><?php esc_html_e('Yes', 'wishlist-member'); ?></span></button>
    </div>
</div>

<div id="unschedule-all-level-modal" data-id="unschedule-all-level-modal" data-label="unschedule_all_level_modal_label" data-title="Clear All Schedule" data-classes="modal-md" style="display:none">
    <div class="body">
        <p class="unschedule-message"><?php esc_html_e('Are you sure you want to clear all scheduled actions for the selected members?', 'wishlist-member'); ?></p>
        <input type="hidden" name="userids" value="" />
        <input type="hidden" name="action" value="admin_actions" />
        <input type="hidden" name="WishListMemberAction" value="schedule_user_level" />
        <input type="hidden" name="level_action" value="unschedule_user_all" />
    </div>
    <div class="footer">
        <button type="button" class="btn -bare" data-dismiss="modal"><?php esc_html_e('Cancel', 'wishlist-member'); ?></button>
        <button type="button" class="btn -primary save-button"><span><?php esc_html_e('Yes', 'wishlist-member'); ?></span></button>
    </div>
</div>

<div id="sequential-modal" data-id="sequential-modal" data-label="sequential_modal_label" data-title="Turn On/Off Sequential Upgrade" data-classes="modal-md" style="display:none">
    <div class="body">
        <p class="message">** <?php esc_html_e('Sequential Message goes here **', 'wishlist-member'); ?></p>
        <input type="hidden" name="userids" value="" />
        <input type="hidden" name="on" value="" />
        <input type="hidden" name="action" value="admin_actions" />
        <input type="hidden" name="WishListMemberAction" value="schedule_user_level" />
        <input type="hidden" name="level_action" value="toggle_sequential" />
    </div>
    <div class="footer">
        <button type="button" class="btn -bare" data-dismiss="modal"><?php esc_html_e('Cancel', 'wishlist-member'); ?></button>
        <button type="button" class="btn -primary save-button"><span><?php esc_html_e('Yes', 'wishlist-member'); ?></span></button>
    </div>
</div>

<div id="subscribe-modal" data-id="subscribe-modal" data-label="subscribe_modal_label" data-title="Subscribe to Email Broadcast" data-classes="modal-md" style="display:none">
    <div class="body">
        <p class="message">** <?php esc_html_e('Sequential Message goes here **', 'wishlist-member'); ?></p>
        <input type="hidden" name="userids" value="" />
        <input type="hidden" name="subscribe" value="" />
        <input type="hidden" name="action" value="admin_actions" />
        <input type="hidden" name="WishListMemberAction" value="schedule_user_level" />
        <input type="hidden" name="level_action" value="toggle_subscribe" />
    </div>
    <div class="footer">
        <button type="button" class="btn -bare" data-dismiss="modal"><?php esc_html_e('Cancel', 'wishlist-member'); ?></button>
        <button type="button" class="btn -primary save-button"><span><?php esc_html_e('Yes', 'wishlist-member'); ?></span></button>
    </div>
</div>

<div id="advance-search-modal" data-id="advance-search-modal" data-label="advance-search_modal_label" data-title="Advanced Search" data-classes="modal-lg" style="display:none">
    <div class="body">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item" role="presentation" class="active">
                <a href="#advance-search-holder" aria-controls="advance-search-holder" role="tab" data-toggle="tab" class="nav-link advanced-search-tab active"><?php esc_html_e('Advanced Search', 'wishlist-member'); ?></a>
            </li>
            <li class="nav-item" role="presentation">
                <a href="#saved-search-holder" aria-controls="saved-search-holder" role="tab" data-toggle="tab" class="nav-link advanced-search-tab"><?php esc_html_e('Saved Searches', 'wishlist-member'); ?></a>
            </li>
        </ul>
        <div class="tab-content">
            <div role="tabpanel" class="tab-pane active" id="advance-search-holder">
                <?php $form_action = "?page={$this->menu_id}&wl=" . ( isset(wlm_get_data()['wl']) ? wlm_get_data()['wl'] : 'members/manage' ); ?>
                <form method="get" target="_parent" id="advance-search-form" action="<?php echo esc_url($form_action); ?>">
                    <?php
                        // Lets add the querystring in hidden fields.
                        // This is needed since we are passing form tru GET.
                        $retain_keys = ['page', 'wl'];
                    foreach (wlm_get_data(true) as $key => $content) {
                        if (in_array($key, $retain_keys)) {
                            printf('<input type="hidden" name="%s" value="%s">', esc_attr($key), esc_attr($content));
                        }
                    }
                    ?>
                    <div class="row">
                        <div class="col-md-6 col-sm-6 col-xs-6">
                            <div class="form-group">
                                <label for=""><?php esc_html_e('Search', 'wishlist-member'); ?></label>
                                <input type="text" class="form-control" value="<?php echo esc_attr($user_search); ?>" name="wlm_search_term"  placeholder="Search Users" />
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-6">
                            <div class="form-group">
                                <label for="">Transaction ID</label>
                                <input type="text" class="form-control" value="<?php echo esc_attr(stripslashes((string) wlm_get_data()['transactionid'])); ?>" name="transactionid" id="transaction-id" placeholder="Transaction ID" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 col-sm-4 col-xs-4">
                            <div class="form-group">
                                <label for=""><?php esc_html_e('Level', 'wishlist-member'); ?></label>
                                <select class="form-control wlm-levels" name="level" id="search-levels" style="width: 100%" tabindex="-1" aria-hidden="true">
                                    <option value=""><?php esc_html_e('- All Users -', 'wishlist-member'); ?></option>
                                    <?php foreach ($list_levels as $key => $value) : ?>
                                        <option value="<?php echo esc_attr($key); ?>"
                                                                  <?php
                                                                    if (wlm_get_data()['level'] == $key) {
                                                                        echo " selected='true'";
                                                                    }
                                                                    ?>
                                        >
                                            <?php echo esc_html($value['name']); ?>
                                            <?php
                                            if (isset($value['count'])) {
                                                echo esc_html("({$value['count']})");
                                            }
                                            ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4 col-xs-4">
                            <div class="form-group">
                                <label for=""><?php esc_html_e('Status', 'wishlist-member'); ?></label>
                                <select class="form-control wlm-levels" name="status" id="filter_status" style="width: 100%" tabindex="-1" aria-hidden="true">
                                    <option value=""><?php esc_html_e('- All -', 'wishlist-member'); ?></option>
                                    <?php foreach ($list_status as $key => $value) : ?>
                                        <option value="<?php echo esc_attr($key); ?>"
                                                                  <?php
                                                                    if ($default_filters['status'] == $key) {
                                                                        echo " selected='true'";
                                                                    }
                                                                    ?>
                                        ><?php echo esc_html($value); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4 col-xs-4">
                            <div class="form-group">
                                <label for=""><?php esc_html_e('Sequential', 'wishlist-member'); ?></label>
                                <select class="form-control wlm-levels" name="sequential" id="filter_sequential" style="width: 100%" tabindex="-1" aria-hidden="true">
                                    <option value=""><?php esc_html_e('- All -', 'wishlist-member'); ?></option>
                                    <option
                                    <?php
                                    if ('on' === $default_filters['sequential']) {
                                        echo 'selected="selected"';
                                    }
                                    ?>
                                     value="on">On</option>
                                    <option
                                    <?php
                                    if ('off' === $default_filters['sequential']) {
                                        echo 'selected="selected"';
                                    }
                                    ?>
                                     value="off">Off</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 col-sm-6 col-xs-6">
                            <div class="form-group">
                                <label for="">Date Range</label>
                                <select class="form-control wlm-levels" name="date_type" id="filter_dates" style="width: 100%" tabindex="-1" aria-hidden="true">
                                    <option value=""><?php esc_html_e('- All -', 'wishlist-member'); ?></option>
                                    <?php foreach ($list_daterange as $key => $value) : ?>
                                        <option value="<?php echo esc_attr($key); ?>"
                                                                  <?php
                                                                    if ($default_filters['date_type'] == $key) {
                                                                        echo " selected='true'";
                                                                    }
                                                                    ?>
                                        ><?php echo esc_html($value); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="form-group date-ranger" style="<?php echo isset($default_filters['from_date']) && ! empty($default_filters['from_date']) ? '' : 'display:none'; ?>">
                                <label for="">From</label>
                                <div class="date-ranger-container">
                                    <input id="DateRangePicker" type="text" class="form-control wlm-datepicker" name="from_date" value="<?php echo esc_attr($default_filters['from_date']); ?>">
                                    <i class="wlm-icons">date_range</i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="form-group date-ranger" style="<?php echo isset($default_filters['from_date']) && ! empty($default_filters['from_date']) ? '' : 'display:none'; ?>">
                                <label for="">To</label>
                                <div class="date-ranger-container">
                                    <input id="DateRangePicker" type="text" class="form-control wlm-datepicker" name="to_date" value="<?php echo esc_attr($default_filters['to_date']); ?>">
                                    <i class="wlm-icons">date_range</i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 col-sm-6 col-xs-6">
                            <div class="form-group">
                                <label for=""><?php esc_html_e('User Address', 'wishlist-member'); ?></label>
                                <input type="text" class="form-control" value="<?php echo esc_attr($default_filters['useraddress']); ?>" name="useraddress"  placeholder="Search by Address" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 col-sm-4 col-xs-4">
                            <label class="cb-container"><?php esc_html_e('Save this search', 'wishlist-member'); ?>
                                <input class="wlm_toggle-adjacent" value="1" type="checkbox" name="save_search"/>
                                <span class="checkmark"></span>
                            </label>
                        </div>
                        <div class="col-md-12" style="display: none;">
                            <div class="form-group no-margin">
                                <label for="save_searchname">Name of Saved Search</label>
                                <input type="text" class="form-control" value="" name="save_searchname" id="save_searchname" placeholder="Name of Saved Search" />
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div role="tabpanel" class="tab-pane" id="saved-search-holder">
                <div class="table-wrapper table-responsive">
                    <?php
                        $saved_search = $this->get_all_saved_search();
                    ?>
                    <table class="table">
                        <tbody class="saved-search-holder">
                            <?php if (count($saved_search) > 0) : ?>
                                <?php foreach ($saved_search as $ss) : ?>
                                    <?php
                                    $s_value = $ss['value'];
                                    if (isset($s_value['usersearch'])) {
                                        $s_value['wlm_search_term'] = $s_value['usersearch'];
                                        unset($s_value['usersearch']);
                                    }
                                    ?>
                                 <tr class="button-hover">
                                     <td class="save-search-list-name"><?php echo esc_html(str_replace('SavedSearch - ', '', $ss['name'])); ?></td>
                                     <td class="no-padding text-center">
                                         <div class="btn-group-action no-padding pull-right">
                                            <a href="?page=<?php echo esc_attr($this->menu_id); ?>&wl=members/manage&<?php echo esc_attr(http_build_query($s_value)); ?>" class="btn" target="_parent">
                                                <i class="wlm-icons">search</i>
                                            </a>
                                            <a href="#" data-search-name="<?php echo esc_attr($ss['name']); ?>" class="btn remove-savedsearch-btn"><i class="wlm-icons">delete</i></a>
                                        </div>
                                     </td>
                                 </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                 <tr><td colspan="2" class="text-center">You have no saved searches</td></tr>
                            <?php endif; ?>
                         </tbody>
                     </table>
                </div>
            </div>
        </div>
    </div>
    <div class="footer">
        <a href="#" data-userid="" class="btn -bare clear-search-fields mr-auto">
            <span><?php esc_html_e('Clear All Fields', 'wishlist-member'); ?></span>
        </a>
        <button type="button" class="btn -bare" data-dismiss="modal"><?php esc_html_e('Close', 'wishlist-member'); ?></button>
        <button type="button" class="btn -primary save-button">
            <i class="wlm-icons">search</i> <span>Search</span>
        </button>
    </div>
</div>

<div id="add-user-modal" data-id="add-user-modal" data-label="add_user_modal_label" data-title="New Member" data-classes="modal-md" style="display:none">
    <div class="body">
        <div class="row">
            <div class="col-md-6 col-sm-6">
                <div class="form-group">
                    <label for=""><?php esc_html_e('First Name', 'wishlist-member'); ?> <small><em>(<?php esc_html_e('optional', 'wishlist-member'); ?>)</em></small></label>
                    <input type="text" class="form-control" value="" name="firstname"  placeholder="<?php esc_attr_e('First Name', 'wishlist-member'); ?>" />
                </div>
            </div>
            <div class="col-md-6 col-sm-6">
                <div class="form-group">
                    <label for=""><?php esc_html_e('Last Name', 'wishlist-member'); ?> <small><em>(<?php esc_html_e('optional', 'wishlist-member'); ?>)</em></small></label>
                    <input type="text" class="form-control" value="" name="lastname"  placeholder="<?php esc_attr_e('Last Name', 'wishlist-member'); ?>" />
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 col-sm-6">
                <div class="form-group">
                    <label for=""><?php esc_html_e('Email', 'wishlist-member'); ?></label>
                    <input type="text" class="form-control" value="" name="email"  placeholder="<?php esc_attr_e('Email', 'wishlist-member'); ?>" required="true"/>
                </div>
            </div>
            <div class="col-md-6 col-sm-6">
                <div class="form-group">
                    <label for=""><?php esc_html_e('Membership Level', 'wishlist-member'); ?></label>
                    <select class="form-control wlm-levels wlm-levels-select" name="wpm_id" id="" style="width: 100%" required="true">
                        <option value="">- <?php esc_html_e('Select Level -', 'wishlist-member'); ?></option>
                        <?php foreach ($wpm_levels as $key => $value) : ?>
                        <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($value['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 col-sm-6">
                <div class="form-group">
                    <label for=""><?php esc_html_e('Username', 'wishlist-member'); ?></label>
                    <input type="text" class="form-control" value="" name="username" required="true" />
                </div>
            </div>
            <div class="col-md-6 col-sm-6">
                <label for=""><?php esc_html_e('Password', 'wishlist-member'); ?></label>
                <!-- start: v4 -->
                <div class="input-group -form-tight">
                    <div class="form-control -pw-form-control">
                        <?php $password_field = 'password_' . strtotime(wlm_date('Y-m-d H:i:s')); // Prevents autocomplete ?>
                        <input type="hidden" value="<?php echo esc_attr($password_field); ?>" name="password_field" />
                        <input type="text" class="form-control password-field" value="" name="<?php echo esc_attr($password_field); ?>" required="true" autocomplete="new-password" />
                        <span class="form-control input-group-addon pass-status text-center"></span>
                    </div>
                    <div class="input-group-append">
                        <button class="btn -default generate-password">Generate</button>
                    </div>
                </div>
                <!-- end: v4 -->
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-6">
                <label for=""><?php esc_html_e('Email Notification', 'wishlist-member'); ?></label>
                <select class="form-control wlm-levels wlm-levels-notification" name="send_welcome_email" style="width: 100%" placeholder="Email Notification">
                    <option value="sendlevel"><?php esc_html_e('Use Level Notification Settings', 'wishlist-member'); ?></option>
                    <option value="send"><?php esc_html_e('Send Email Notification', 'wishlist-member'); ?></option>
                    <option value="dontsend"><?php esc_html_e('Do NOT Send Email Notification', 'wishlist-member'); ?></option>
                </select>
            </div>
            <div class="form-group col-md-6">
                <label for=""><?php esc_html_e('Email Confirmation', 'wishlist-member'); ?></label>
                <select class="form-control wlm-select wlm-levels wlm-levels-emailconfirmation" data-lastvalue="<?php echo esc_attr(wlm_or($this->get_option('admin_add_member_require_email_confirmation'), 'dontrequire')); ?>" name="require_email_confirmation" style="width: 100%">
                    <option value="uselevelsettings"><?php esc_html_e('Use Level Requirements', 'wishlist-member'); ?></option>
                    <option value="require"><?php esc_html_e('Require Confirmation', 'wishlist-member'); ?></option>
                    <option value="dontrequire"><?php esc_html_e('Do NOT Require Confirmation', 'wishlist-member'); ?></option>
                </select>
            </div>
        </div>
        <input type="hidden" name="action" value="admin_actions" />
        <input type="hidden" name="WishListMemberAction" value="add_user" />
    </div>
    <div class="footer">
        <button type="button" class="btn -bare" data-dismiss="modal"><?php esc_html_e('Close', 'wishlist-member'); ?></button>
        <button type="button" class="btn -primary save-button"><i class="wlm-icons">add_circle_outline</i> <span class="text">Add Member</span></button>
    </div>
</div>

<div id="edit-user-modal" data-id="edit-user-modal" data-label="edit_user_modal_label" data-title="Edit Member: Jen Grey&nbsp;|&nbsp;jgrey&nbsp;|&nbsp;jgrey@gmail.com" data-classes="modal-xl" style="display:none">
    <div class="body">
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="nav-item"><a class="nav-link edit-user-nav-link nav-link-default active" href="#member-info" aria-controls="member-info" role="tab" data-toggle="tab"><?php esc_html_e('Member Info', 'wishlist-member'); ?></a></li>
            <li role="presentation" class="nav-item"><a class="nav-link edit-user-nav-link" href="#member-address" aria-controls="member-address" role="tab" data-toggle="tab"><?php esc_html_e('Address', 'wishlist-member'); ?></a></li>
            <li role="presentation" class="nav-item"><a class="nav-link edit-user-nav-link" href="#member-level" aria-controls="member-level" role="tab" data-toggle="tab"><?php esc_html_e('Levels', 'wishlist-member'); ?></a></li>
            <li role="presentation" class="nav-item"><a class="nav-link edit-user-nav-link for-pay-per-posts" href="#pay-per-posts" aria-controls="pay-per-posts" role="tab" data-toggle="tab"><?php esc_html_e('Pay Per Posts', 'wishlist-member'); ?></a></li>
            <li role="presentation" class="nav-item"><a class="nav-link edit-user-nav-link" href="#member-advance" aria-controls="member-advance" role="tab" data-toggle="tab"><?php esc_html_e('Advanced', 'wishlist-member'); ?></a></li>
            <?php if ($this->get_option('privacy_enable_consent_to_market') || $this->get_option('privacy_require_tos_on_registration')) : ?>
                <li role="presentation" class="nav-item"><a class="nav-link edit-user-nav-link" href="#data-privacy" aria-controls="data-privacy" role="tab" data-toggle="tab"><?php esc_html_e('Data Privacy', 'wishlist-member'); ?></a></li>
            <?php endif; ?>
            <li role="presentation" class="nav-item"><a class="nav-link edit-user-nav-link" href="#member-other" aria-controls="member-other" role="tab" data-toggle="tab"><?php esc_html_e('Other Fields', 'wishlist-member'); ?></a></li>
            <!-- <li role="presentation" class="nav-item"><a class="nav-link edit-user-nav-link" href="#member-history" aria-controls="member-history" role="tab" data-toggle="tab"><?php esc_html_e('History', 'wishlist-member'); ?></a></li> -->
            <?php
                $member_edit_tabs = apply_filters('wishlistmember_member_edit_tabs', []);
                /**
                 * Allow additional tabs to be added to the member edit modal
                 *
                 * @since 3.10
                 *
                 * @param array $tabs An associative array of tabs to add with Tab IDs as keys and Tab Labels as values
                 */
            foreach ($member_edit_tabs as $tab_key => $tab_label) {
                printf('<li role="presentation" class="nav-item"><a class="nav-link edit-user-nav-link" href="#member-edit-%s" arole="tab" data-toggle="tab">%s</a></li>', esc_attr($tab_key), esc_html($tab_label));
            }
                // Pass PHP "$member_edit_tabs" to JavaScript as "window.wlm_member_edit_tabs" to be used in manage.js.
            if ($member_edit_tabs) {
                printf('<script>window.wlm_member_edit_tabs = %s</script>', json_encode($member_edit_tabs));
            }
            ?>
        </ul>
        <div class="tab-content">
        </div>
        <input type="hidden" name="action" value="admin_actions" />
        <input type="hidden" name="WishListMemberAction" value="update_user" />
    </div>
    <div class="footer">
        <a href="#" data-userid="" class="btn btn-danger edituser-delete-btn mr-auto">
            <i class="wlm-icons">delete</i>
            <span><?php esc_html_e('Delete Member', 'wishlist-member'); ?></span>
        </a>
        <button type="button" class="btn -bare" data-dismiss="modal"><?php esc_html_e('Close', 'wishlist-member'); ?></button>
        <button type="button" class="btn -primary save-button"><i class="wlm-icons">save</i> <span><?php esc_html_e('Save', 'wishlist-member'); ?></span></button>
        <button type="button" class="btn -success save-button save-close"><i class="wlm-icons">save</i> <span><?php esc_html_e('Save & Close', 'wishlist-member'); ?></span></button>
    </div>
</div>

<div id="delete-modal" data-id="delete-modal" data-label="delete_modal_label" data-title="Delete Member" data-classes="modal-md" style="display:none">
    <div class="body">
        <p class="message">** <?php esc_html_e('Delete Message goes here **', 'wishlist-member'); ?></p>
        <input type="hidden" name="userids" value="" />
        <input type="hidden" name="action" value="admin_actions" />
        <input type="hidden" name="WishListMemberAction" value="delete_user" />
    </div>
    <div class="footer">
        <button type="button" class="btn -bare" data-dismiss="modal"><?php esc_html_e('Cancel', 'wishlist-member'); ?></button>
        <button type="button" class="btn -primary save-button"><span><?php esc_html_e('Yes', 'wishlist-member'); ?></span></button>
    </div>
</div>

<!-- Generic Confirmation Modal -->
<div id="generic_confirmation_modal" data-id="generic_confirmation_modal" data-label="generic_confirmation_modal_label" data-title="" data-classes="modal-md" style="display:none">
    <div class="body">
        <p class="message"></p>
        <input type="hidden" name="userids" value="" />
        <input type="hidden" name="action" value="admin_actions" />
        <input type="hidden" name="WishListMemberAction" value="" />
    </div>
    <div class="footer">
        <button type="button" class="btn -bare" data-dismiss="modal"><?php esc_html_e('Cancel', 'wishlist-member'); ?></button>
        <button type="button" class="btn -primary save-button"><span><?php esc_html_e('Yes', 'wishlist-member'); ?></span></button>
    </div>
</div>
