<?php

/**
 * Content Protection Page
 *
 * @package Wishlist_Member
 */

$perpage = wishlistmember_instance()->get_option('content-tab-perpage');
if (is_numeric(wlm_get_data()['perpage']) || ! $perpage || 'Show All' === wlm_get_data()['perpage']) {
    $perpage = wlm_get_data()['perpage'];
    if (! $perpage) {
        $perpage = wishlistmember_instance()->pagination_items[1];
    }
    if (! in_array($perpage, wishlistmember_instance()->pagination_items)) {
        $perpage = wishlistmember_instance()->pagination_items[1];
    }
    // We only save if not show all.
    if ('Show All' !== $perpage) {
        wishlistmember_instance()->save_option('content-tab-perpage', $perpage);
    }
}
$perpage = 'Show All' === $perpage ? 999999999 : $perpage;

$s_status    = wlm_get_data()['s_status'] . '';
$post_status = ['publish', 'pending', 'draft', 'future', 'private'];
if ('attachment' === $content_type) {
    $post_status[] = 'inherit';
    $s_status      = ( 'publish' === $s_status ) ? $s_status : 'inherit';
}
if ($s_status && in_array($s_status, $post_status)) {
    $post_status = [$s_status];
}
$s_level       = wlm_trim(wlm_get_data()['s_level']);
$s_currentpage = isset(wlm_get_data()['paged']) ? wlm_get_data()['paged'] - 1 : -1;
if ($s_currentpage < 0) {
    $s_currentpage = 0;
}
$s_offset      = $s_currentpage * $perpage;
$s_currentpage = $s_offset / $perpage + 1;

$exclude_pages = wishlistmember_instance()->exclude_pages([]);

$args = [
    'post_type' => $content_type,
];

$args['post_status'] = $post_status;
if ($s_level) {
    $args['post__in']   = wishlistmember_instance()->get_membership_content($content_type, $s_level);
    $args['post__in'][] = 0;
}
$args['offset'] = $s_offset;

$args['orderby'] = wlm_or(wlm_trim(wlm_get_data()['orderby']), 'post_title');
$args['order']   = wlm_or(wlm_trim(wlm_get_data()['order']), 'asc');

$sort_name      = 'desc';
$sort_name_icon = '';
if ('post_title' === $args['orderby']) {
    $sort_name      = 'desc' === strtolower($args['order']) ? 'asc' : 'desc';
    $sort_name_icon = 'desc' === $sort_name ? 'arrow_drop_up' : 'arrow_drop_down';
}

$sort_date      = 'desc';
$sort_date_icon = '';
if ('post_date' === $args['orderby']) {
    $sort_date      = 'desc' === strtolower($args['order']) ? 'asc' : 'desc';
    $sort_date_icon = 'desc' === $sort_date ? 'arrow_drop_up' : 'arrow_drop_down';
}

$args['posts_per_page'] = $perpage;
if (isset(wlm_request_data()['wlm_post_search_term'])) {
    $args['s'] = wlm_request_data()['wlm_post_search_term'];
}

// Used for $page_href below.
$url_keys = array_intersect_key(
    wlm_get_data(true),
    [
        'wlm_post_search_term' => '',
        's_level'              => '',
        's_status'             => '',
        'orderby'              => '',
        'order'                => '',
    ]
);
$url_keys = array_filter($url_keys, 'strlen');


$is_custom_posttype = ! in_array($content_type, ['page', 'attachment', 'post']);

// Check if content is heirarchical, including custom post type.
$is_heirarchical    = 'page' === $content_type ? true : false;
$is_heirarchical    = $is_custom_posttype ? is_post_type_hierarchical($content_type) : $is_heirarchical;
$content_taxonomies = get_object_taxonomies($content_type);
$has_categories     = in_array('category', $content_taxonomies);

$post_children = [];
$post_parents  = [];
$post_ids      = [];
if ($is_heirarchical && ! count($url_keys)) {
    $args['sort_order']  = $args['order'];
    $args['sort_column'] = $args['orderby'];
    $the_posts           = new WP_Query($args);
    $content_items       = $the_posts->posts;
    $total_items         = $the_posts->found_posts;

    // Lets do it heirarchically.
    if (null === wlm_get_data()['orderby']) {
        // Get all the parents.
        foreach ($content_items as $key => $value) {
            if ($value->post_parent) {
                $post_parents[] = $value->post_parent;

                $post_children[ $value->post_parent ][] = $value;
                // Remove the children from list, they are added seperately.
                unset($content_items[ $key ]);
            }
            // Take note of all post id and its key.
            $post_ids[ $key ] = $value->ID;
        }
        // Lets get the unique parent id.
        $post_parents = array_unique($post_parents);

        // Lets make sure that the children will be listed with their parent.
        foreach ($post_parents as $parent_id) {
            // If parent has children.
            if (array_key_exists($parent_id, $post_children)) {
                // And is not yet in the content_items, add it.
                if (! in_array($parent_id, $post_ids)) {
                    array_unshift($content_items, get_post($parent_id));
                }
            }
        }
    }
} else { // For non heirarchical post types.
    $the_posts       = new WP_Query($args);
    $content_items   = $the_posts->posts;
    $total_items     = $the_posts->found_posts;
    $is_heirarchical = count($url_keys);
}

$total_pages = ceil($total_items / $perpage);
++$s_offset;

// Get Membership Levels.
$wpm_levels = wishlistmember_instance()->get_option('wpm_levels');
$page_href  = '?page=' . wishlistmember_instance()->menu_id . '&wl=' . ( isset(wlm_get_data()['wl']) ? wlm_get_data()['wl'] : "content_protection/{$content_type}/content" );
$page_href .= '&' . build_query($url_keys);

$checkbox_check = false;

/**
 * Display items function.
 *
 * @param object  $that            WLM main class object.
 * @param object  $item            Content object.
 * @param array   $post_children   Array of post children.
 * @param string  $content_type    Content type.
 * @param boolean $content_comment Content comment.
 * @param boolean $checkbox_check  Checkbox check.
 * @param boolean $is_heirarchical Is heirarchical.
 * @param boolean $is_system_page  Is system page.
 */
function display_items($that, $item, $post_children, $content_type, $content_comment, $checkbox_check, $is_heirarchical, $has_categories = false, $is_system_page = false)
{
    $exclude_pages = $that->exclude_pages([]);
    if (isset($post_children[ $item->ID ])) {
        include $that->plugin_dir3 . '/ui/admin_screens/content_protection/post_page_files/content-item.php';
        foreach ($post_children[ $item->ID ] as $key => $value) {
            $system_page = false;
            if (in_array($value->ID, $exclude_pages)) {
                $system_page = true;
            }
            display_items($that, $value, $post_children, $content_type, $content_comment, $checkbox_check, $is_heirarchical, $has_categories, $system_page);
        }
    } else {
        include $that->plugin_dir3 . '/ui/admin_screens/content_protection/post_page_files/content-item.php';
    }
}
?>

<?php if (! empty($custom_post_type) && ! $content_comment) : ?>
    <div class="header-tools" style="border: none">
        <div class="row">
            <div class="col-md-6">
                <template class="wlm3-form-group">
                    {
                        label : '<?php echo esc_js(__('Enable protection', 'wishlist-member')); ?>',
                        name  : '<?php echo esc_js($custom_post_type); ?>',
                        value : '1',
                        checked_value : '<?php echo esc_js($enabled_custom_post_types); ?>',
                        uncheck_value : '0',
                        class : 'wlm_toggle-switch enable-custom-post-type',
                        type  : 'checkbox',
                    }
                </template>
                <input type="hidden" name="action" value="admin_actions" />
                <input type="hidden" name="WishListMemberAction" value="enable_custom_post_types" />
            </div>
        </div>
    </div>
<?php endif; ?>
<?php
if (! empty($custom_post_type) && ! $enabled_custom_post_types) {
    return;
}
?>

<div class="header-tools -no-border no-padding">
    <div class="row">
        <div class="col-sm-12 col-md-4 col-lg-4 mb-sm-1">
            <div class="form-group">
                <label class="sr-only" for=""><?php esc_html_e('Actions', 'wishlist-member'); ?></label>
                <select class="form-control wlm-select blk-actions" name="" id="" style="width: 100%">
                    <option value="">- Select an Action -</option>
                    <option value="protection">Edit Content Protection Status</option>
                    <option value="add_level">Add Level(s) to Content</option>
                    <option value="remove_level">Remove Level(s) from Content</option>
                    <?php if (! $content_comment && 'attachment' !== $content_type) : ?>
                        <option value="ppp">Edit Per Member Access</option>
                        <option value="addpppusers">Add Pay Per Post Members</option>
                        <option value="removepppusers">Remove Pay Per Post Members</option>
                    <?php endif; ?>
                </select>
            </div>
        </div>
        <div id="AdvancedSearchForm" class="search-bar col-sm-12 col-lg-<?php echo esc_attr('attachment' === $content_type ? '6' : '8'); ?> col-md-<?php echo esc_attr('attachment' === $content_type ? '6' : '8'); ?>">
            <form method="get" target="_parent" id="search-form" action="?<?php echo esc_attr(wishlistmember_instance()->QueryString()); ?>">
                <?php
                    // Lets add the querystring in hidden fields.
                    // This is needed since we are passing form tru GET.
                    $retain_keys = ['page', 'wl'];
                foreach (wlm_get_data(true) as $key => $content) {
                    if (in_array($key, $retain_keys)) {
                        echo '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($content) . '" />';
                    }
                }
                ?>
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search Text" name="wlm_post_search_term" value="<?php echo esc_attr(stripslashes((string) wlm_get_data()['wlm_post_search_term'])); ?>">
                    <div class="input-group-append" style="width: 130px">
                        <select class="form-control wlm-select" name="s_level">
                            <option value="">- <?php esc_html_e('All Levels -', 'wishlist-member'); ?></option>
                            <?php foreach ($wpm_levels as $key => $value) : ?>
                                <option value="<?php echo esc_attr($key); ?>"
                                <?php
                                if (wlm_get_data()['s_level'] === $key) {
                                    echo " selected='true'";
                                }
                                ?>
                                ><?php echo esc_html($value['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php
                        $post_statuses = [
                            'publish' => 'Published',
                            'future'  => 'Scheduled',
                            'draft'   => 'Draft',
                            'pending' => 'Pending',
                            'private' => 'Private',
                        ];
                        ?>
                    <div class="input-group-append" style="width: 130px">
                        <select class="form-control wlm-select" name="s_status" id="">
                            <option value="">- <?php esc_html_e('All Statuses -', 'wishlist-member'); ?></option>
                            <?php foreach ($post_statuses as $key => $value) : ?>
                                <option value="<?php echo esc_attr($key); ?>"
                                <?php
                                if (wlm_get_data()['s_status'] === $key) {
                                    echo " selected='true'";
                                }
                                ?>
                                ><?php echo esc_html($value); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="input-group-append">
                        <button class="btn -default -icon search-btn btn-block">
                            <i class="wlm-icons">search</i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <?php if ('attachment' === $content_type) : ?>
            <div class="col-md-2 col-lg-2 mt-sm-2 mt-lg-0 mt-md-0">
                <a href="#" class="btn -primary -condensed settings-btn">
                    <i class="wlm-icons">settings</i>
                    <span><?php esc_html_e('Settings', 'wishlist-member'); ?></span>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <ul class="list-inline list-legend pull-left">
            <li class="list-inline-item"><?php esc_html_e('Regular text = Protected Level', 'wishlist-member'); ?></li>
            <li class="list-inline-item"><em>Italics</em> <?php esc_html_e('text = Level has access to all posts.', 'wishlist-member'); ?></li>
            <li class="list-inline-item"><strike>Strikethrough</strike> <?php esc_html_e('text = Level added but post is unprotected.', 'wishlist-member'); ?></li>
        </ul>
        <div class="pagination pull-right mt-3">
            <?php if ($total_items && $total_items > wishlistmember_instance()->pagination_items[0]) : ?>
                    <?php if ($perpage <= $total_items) : ?>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="text-muted pr-2">
                                    <div role="presentation" class="dropdown mt-9px">
                                        <a href="#" class="dropdown-toggle" id="drop-page" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                            <?php echo number_format($s_offset, 0, '.', ','); ?>
                                            -
                                            <?php
                                            $x = $perpage * $s_currentpage;
                                            echo number_format($x > $total_items ? $total_items : $x, 0, '.', ',');
                                            ?>
                                        </a> of <?php echo number_format($total_items, 0, '.', ','); ?>
                                        <ul class="dropdown-menu" id="menu1" aria-labelledby="drop-page">
                                            <?php foreach (wishlistmember_instance()->pagination_items as $key => $value) : ?>
                                                <a class="dropdown-item" target="_parent" href="<?php echo esc_url($page_href . '&perpage=' . $value); ?>"><?php echo esc_html($value); ?></a>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </span>
                                <?php if ($s_currentpage > 1) : ?>
                                    <a target="_parent" href="<?php echo esc_url($page_href . '&paged=1'); ?>" class="mt-6px"><i class="wlm-icons md-26">first_page</i></a>
                                <?php else : ?>
                                    <a class="mt-6px text-muted disabled" disabled='disabled'><i class="wlm-icons md-26">first_page</i></a>
                                <?php endif; ?>

                                <?php
                                if ($s_currentpage <= 1) {
                                    $previous_link = $page_href . '&paged=' . $total_pages;
                                } else {
                                    $previous_link = $page_href . '&paged=' . ( $s_currentpage - 1 );
                                }
                                ?>
                                <a target="_parent" href="<?php echo esc_url($previous_link); ?>" class="mt-6px"><i class="wlm-icons md-26">keyboard_arrow_left</i></a>
                            </div>
                            <input type="text" value="<?php echo esc_attr($s_currentpage); ?>" class="form-control text-center pagination-pagenum" data-pages="<?php echo esc_attr($total_pages); ?>" data-link="<?php echo esc_attr($page_href . '&paged='); ?>" data-lpignore="true">
                            <div class="input-group-append">
                                <span class="mt-9px"> of <?php echo (int) $total_pages; ?></span>
                                <?php
                                if ($s_currentpage < $total_pages) {
                                    $next_link = $page_href . '&paged=' . ( $s_currentpage + 1 );
                                } else {
                                    $next_link = $page_href . '&paged=1';
                                }
                                ?>
                                <a target="_parent" href="<?php echo esc_url($next_link); ?>" class="mt-6px"><i class="wlm-icons md-26">keyboard_arrow_right</i></a>

                                <?php if ($s_currentpage < $total_pages) : ?>
                                    <a target="_parent" href="<?php echo esc_url($page_href . '&paged=' . $total_pages); ?>" class="mt-6px"><i class="wlm-icons md-26">last_page</i></a>
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
                                                <?php echo number_format($s_offset, 0, '.', ','); ?>
                                                -
                                                <?php
                                                $x = $perpage * $s_currentpage;
                                                echo number_format($x > $total_items ? $total_items : $x, 0, '.', ',');
                                                ?>
                                            </a> of <?php echo number_format($total_items, 0, '.', ','); ?>
                                            <ul class="dropdown-menu" id="menu1" aria-labelledby="drop-page">
                                                <?php foreach (wishlistmember_instance()->pagination_items as $key => $value) : ?>
                                                    <a class="dropdown-item" target="_parent" href="<?php echo esc_url($page_href . '&perpage=' . $value); ?>"><?php echo esc_html($value); ?></a>
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

<div class="row">
    <div class="col-md-12">
        <div class="table-wrapper -special table-responsive -cp-table">
            <table class="table table-condensed">
                <thead>
                    <tr class="button-hover">
                        <th style="width: 40px" class="text-center">
                            <div class="form-check -for-tables">
                                <input value="" type="checkbox" class="chk-all form-check-input">
                                <label for="" class="form-check-label d-none"></label>
                            </div>
                        </th>
                        <th>
                            <a href="
                            <?php
                            echo esc_url(
                                add_query_arg(
                                    [
                                        'orderby' => 'post_title',
                                        'order'   => $sort_name,
                                    ],
                                    admin_url('admin.php' . $page_href)
                                )
                            );
                            ?>
                            "><?php esc_html_e('Name', 'wishlist-member'); ?><span class="wlm-icons"><?php echo esc_html($sort_name_icon); ?></span></a>
                        </th>
                        <?php if (! $content_comment) : ?>
                            <th>
                                <?php esc_html_e('Status', 'wishlist-member'); ?>
                                <?php wishlistmember_instance()->tooltip(__('Status can be Protected, Unprotected or Inherited.', 'wishlist-member'), 'lg'); ?>
                            </th>
                        <?php endif; ?>
                        <?php if (! $content_comment && 'attachment' !== $content_type) : ?>
                            <th style="width: 120px" class="text-center">
                                <?php esc_html_e('Per Member Access', 'wishlist-member'); ?>
                                <?php wishlistmember_instance()->tooltip(__('Per Member Access can be Free, Paid or Disabled.', 'wishlist-member'), 'lg'); ?>
                            </th>
                            <th style="width: 100px" class="text-center">
                                <?php esc_html_e('Post Members', 'wishlist-member'); ?>
                                <?php wishlistmember_instance()->tooltip(__('Post Members shows the number of Members with Pay Per Post Access.', 'wishlist-member'), 'lg'); ?>
                            </th>
                            <?php if ($has_categories) : ?>
                                <th class="text-center" style="width: 20%">
                                    <?php esc_html_e('Categories', 'wishlist-member'); ?>
                                    <?php wishlistmember_instance()->tooltip(__('The Category that contains the Post.', 'wishlist-member'), 'lg'); ?>
                                </th>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if ('attachment' === $content_type || ! $has_categories) : ?>
                            <th style="width: 120px;"><?php esc_html_e('Parent', 'wishlist-member'); ?></th>
                        <?php endif; ?>
                        <th style="width: 100px" class="text-center"><a href="
                        <?php
                        echo esc_url(
                            add_query_arg(
                                [
                                    'orderby' => 'post_date',
                                    'order'   => $sort_date,
                                ],
                                admin_url('admin.php' . $page_href)
                            )
                        );
                        ?>
                        ">
                            <?php esc_html_e('Date', 'wishlist-member'); ?>
                            <?php wishlistmember_instance()->tooltip(__('Publish Date', 'wishlist-member'), 'lg'); ?>
                            <span class="wlm-icons"><?php echo esc_html($sort_date_icon); ?></span></a></th>
                        <?php if ('attachment' !== $content_type) : ?>
                            <th style="width: 100px" class="text-center"></th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <?php
                foreach ($content_items as $item) :
                    $system_page = false;
                    if (in_array($item->ID, $exclude_pages)) {
                        $system_page = true;
                    }
                    display_items(wishlistmember_instance(), $item, $post_children, $content_type, $content_comment, $checkbox_check, $is_heirarchical, $has_categories, $system_page);
                endforeach;
                ?>
            </table>
        </div>
    </div>
</div>

<div class="pagination pull-right">
    <?php if ($total_items && $total_items > wishlistmember_instance()->pagination_items[0]) : ?>
            <?php if ($perpage <= $total_items) : ?>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="text-muted pr-2">
                            <div role="presentation" class="dropdown mt-9px">
                                <a href="#" class="dropdown-toggle" id="drop-page" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                    <?php echo number_format($s_offset, 0, '.', ','); ?>
                                    -
                                    <?php
                                    $x = $perpage * $s_currentpage;
                                    echo number_format($x > $total_items ? $total_items : $x, 0, '.', ',');
                                    ?>
                                </a> of <?php echo number_format($total_items, 0, '.', ','); ?>
                                <ul class="dropdown-menu" id="menu1" aria-labelledby="drop-page">
                                    <?php foreach (wishlistmember_instance()->pagination_items as $key => $value) : ?>
                                        <a class="dropdown-item" target="_parent" href="<?php echo esc_url($page_href . '&perpage=' . $value); ?>"><?php echo esc_html($value); ?></a>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </span>
                        <?php if ($s_currentpage > 1) : ?>
                            <a target="_parent" href="<?php echo esc_url($page_href . '&paged=1'); ?>" class="mt-6px"><i class="wlm-icons md-26">first_page</i></a>
                        <?php else : ?>
                            <a class="mt-6px text-muted disabled" disabled='disabled'><i class="wlm-icons md-26">first_page</i></a>
                        <?php endif; ?>

                        <?php
                        if ($s_currentpage <= 1) {
                            $previous_link = $page_href . '&paged=' . $total_pages;
                        } else {
                            $previous_link = $page_href . '&paged=' . ( $s_currentpage - 1 );
                        }
                        ?>
                        <a target="_parent" href="<?php echo esc_url($previous_link); ?>" class="mt-6px"><i class="wlm-icons md-26">keyboard_arrow_left</i></a>
                    </div>
                    <input type="text" value="<?php echo esc_attr($s_currentpage); ?>" class="form-control text-center pagination-pagenum" data-pages="<?php echo esc_attr($total_pages); ?>" data-link="<?php echo esc_attr($page_href . '&paged='); ?>" data-lpignore="true">
                    <div class="input-group-append">
                        <span class="mt-9px"> of <?php echo (int) $total_pages; ?></span>
                        <?php
                        if ($s_currentpage < $total_pages) {
                            $next_link = $page_href . '&paged=' . ( $s_currentpage + 1 );
                        } else {
                            $next_link = $page_href . '&paged=1';
                        }
                        ?>
                        <a target="_parent" href="<?php echo esc_url($next_link); ?>" class="mt-6px"><i class="wlm-icons md-26">keyboard_arrow_right</i></a>

                        <?php if ($s_currentpage < $total_pages) : ?>
                            <a target="_parent" href="<?php echo esc_url($page_href . '&paged=' . $total_pages); ?>" class="mt-6px"><i class="wlm-icons md-26">last_page</i></a>
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
                                        <?php echo number_format($s_offset, 0, '.', ','); ?>
                                        -
                                        <?php
                                        $x = $perpage * $s_currentpage;
                                        echo number_format($x > $total_items ? $total_items : $x, 0, '.', ',');
                                        ?>
                                    </a> of <?php echo number_format($total_items, 0, '.', ','); ?>
                                    <ul class="dropdown-menu" id="menu1" aria-labelledby="drop-page">
                                        <?php foreach (wishlistmember_instance()->pagination_items as $key => $value) : ?>
                                            <a class="dropdown-item" target="_parent" href="<?php echo esc_url($page_href . '&perpage=' . $value); ?>"><?php echo esc_html($value); ?></a>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </span>
                        </div>
                    </div>
            <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Modal -->
<div id="protection-modal" data-id="protection-modal" data-label="protection_modal_label" data-title="Edit Protection Status" data-classes="modal-sm" style="display:none">
    <div class="body">
        <div class="form-group">
            <label for=""><?php esc_html_e('Protection Status', 'wishlist-member'); ?></label>
            <select class="form-control wlm-levels wlm-protection" name="protection" style="width: 100%" required>
                <option><?php esc_html_e('Unprotected', 'wishlist-member'); ?></option>
                <option><?php esc_html_e('Protected', 'wishlist-member'); ?></option>
                <option><?php esc_html_e('Inherited', 'wishlist-member'); ?></option>
            </select>
        </div>
        <?php if ($content_comment) : ?>
            <input type="hidden" name="content_comment" value="1" />
        <?php endif; ?>
        <input type="hidden" name="content_type" value="<?php echo esc_attr($content_type); ?>" />
        <input type="hidden" name="contentids" value="" />
        <input type="hidden" name="action" value="admin_actions" />
        <input type="hidden" name="WishListMemberAction" value="update_content_protection" />
    </div>
    <div class="footer">
        <button type="button" class="btn -bare" data-dismiss="modal"><?php esc_html_e('Close', 'wishlist-member'); ?></button>
        <button type="button" class="btn -primary save-button"><i class="wlm-icons">lock</i>  <span>Update Protection</span></button>
    </div>
</div>

<div id="add-level-modal" data-id="add-level-modal" data-label="add_level_modal_label" data-title="Add Levels" data-classes="modal-sm" style="display:none">
    <div class="body">
        <div class="form-group membership-level-select">
            <label for="">Membership Levels</label>
            <select class="form-control wlm-levels" multiple="multiple" name="wlm_levels[]" id="" style="width: 100%" data-placeholder="Select Membership Levels" required>
                <?php foreach ($wpm_levels as $key => $value) : ?>
                    <?php $disabled = isset($value[ $allprotection ]) && ! empty($value[ $allprotection ]) ? 'disabled' : ''; ?>
                    <option value="<?php echo esc_attr($key); ?>" <?php echo esc_attr($disabled); ?>><?php echo esc_html($value['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php if ($content_comment) : ?>
            <input type="hidden" name="content_comment" value="1" />
        <?php endif; ?>
        <input type="hidden" name="content_type" value="<?php echo esc_attr($content_type); ?>" />
        <input type="hidden" name="contentids" value="" />
        <input type="hidden" name="level_action" value="add" />
        <input type="hidden" name="action" value="admin_actions" />
        <input type="hidden" name="WishListMemberAction" value="update_content_protection" />
    </div>
    <div class="footer">
        <button type="button" class="btn -bare" data-dismiss="modal"><?php esc_html_e('Close', 'wishlist-member'); ?></button>
        <button type="button" class="btn -primary save-button"><i class="wlm-icons">add_circle_outline</i> <span>Add Level</span></button>
    </div>
</div>

<div id="remove-level-modal" data-id="remove-level-modal" data-label="remove_level_modal_label" data-title="Remove Levels" data-classes="modal-sm" style="display:none">
    <div class="body">
        <div class="form-group">
            <label for="">Membership Levels</label>
            <select class="form-control wlm-levels" multiple="multiple" name="wlm_levels[]" id="" style="width: 100%" data-placeholder="Select Membership Levels" required>
                <?php foreach ($wpm_levels as $key => $value) : ?>
                    <?php $disabled = isset($value[ $allprotection ]) && ! empty($value[ $allprotection ]) ? 'disabled' : ''; ?>
                    <option value="<?php echo esc_attr($key); ?>" <?php echo esc_attr($disabled); ?>><?php echo esc_html($value['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php if ($content_comment) : ?>
            <input type="hidden" name="content_comment" value="1" />
        <?php endif; ?>
        <input type="hidden" name="content_type" value="<?php echo esc_attr($content_type); ?>" />
        <input type="hidden" name="contentids" value="" />
        <input type="hidden" name="level_action" value="remove" />
        <input type="hidden" name="action" value="admin_actions" />
        <input type="hidden" name="WishListMemberAction" value="update_content_protection" />
    </div>
    <div class="footer">
        <button type="button" class="btn -bare" data-dismiss="modal"><?php esc_html_e('Close', 'wishlist-member'); ?></button>
        <button type="button" class="btn -primary save-button"><i class="wlm-icons">remove_circle_outline</i> <span>Remove Level</span></button>
    </div>
</div>

<div id="ppp-modal" data-id="ppp-modal" data-label="ppp_modal_label" data-title="Edit Per Member Access" data-classes="modal-sm" style="display:none">
    <div class="body">
        <div class="form-group">
            <label for="">Access</label>
            <select class="form-control wlm-levels wlm-useraccess" name="useraccess" style="width: 100%" required>
                <option selected><?php esc_html_e('Disabled', 'wishlist-member'); ?></option>
                <option><?php esc_html_e('Free', 'wishlist-member'); ?></option>
                <option><?php esc_html_e('Paid', 'wishlist-member'); ?></option>
            </select>
        </div>
        <?php if ($content_comment) : ?>
            <input type="hidden" name="content_comment" value="1" />
        <?php endif; ?>
        <input type="hidden" name="content_type" value="<?php echo esc_attr($content_type); ?>" />
        <input type="hidden" name="contentids" value="" />
        <input type="hidden" name="action" value="admin_actions" />
        <input type="hidden" name="WishListMemberAction" value="update_content_protection" />
    </div>
    <div class="footer">
        <button type="button" class="btn -bare" data-dismiss="modal"><?php esc_html_e('Close', 'wishlist-member'); ?></button>
        <button type="button" class="btn -primary save-button"><i class="wlm-icons">person</i>  <span><?php esc_html_e('Update Member Access', 'wishlist-member'); ?></span></button>
    </div>
</div>

<div id="edit-modal" data-id="edit-modal" data-label="edit_modal_label" data-title="Edit Content Protection" data-classes="modal-lg" style="display:none">
    <div class="body">
        <div class="edit-content">
        </div>
        <?php if ($content_comment) : ?>
            <input type="hidden" name="content_comment" value="1" />
        <?php endif; ?>
        <input type="hidden" name="content_type" value="<?php echo esc_attr($content_type); ?>" />
        <input type="hidden" name="contentids" value="" />
        <input type="hidden" name="checkbox_check" value="0" />
        <input type="hidden" name="level_action" value="set" />
        <input type="hidden" name="action" value="admin_actions" />
        <input type="hidden" name="WishListMemberAction" value="update_content_protection" />
    </div>
    <div class="footer">
        <button type="button" class="btn -bare" data-dismiss="modal"><?php esc_html_e('Close', 'wishlist-member'); ?></button>
        <!-- <button type="button" class="btn -primary save-button"><i class="wlm-icons">save</i>  <span>Save</span></button> -->
    </div>
</div>

<div id="ppp-user-modal" data-id="ppp-user-modal" data-label="ppp-user_modal_label" data-title="Add Member to Pay Per Post" data-classes="modal-md" style="display:none">
    <div class="body">
        <div class="form-group">
            <label for="">Select a Member</label>
            <select class="form-control wlm-payperpost-users" name="wlm_payperpost_users" style="width: 100%">
            </select>
        </div>
        <input type="hidden" name="content_type" value="<?php echo esc_attr($content_type); ?>" />
        <input type="hidden" name="contentids" value="" />
        <input type="hidden" name="action" value="admin_actions" />
        <input type="hidden" name="WishListMemberAction" value="update_content_protection" />
        <input type="hidden" name="operation" value="" />
    </div>
    <div class="footer">
        <button type="button" class="btn -bare" data-dismiss="modal"><?php esc_html_e('Close', 'wishlist-member'); ?></button>
        <button type="button" class="btn -primary save-button"><i class="wlm-icons">add_circle_outline</i><span><?php esc_html_e('Add Member', 'wishlist-member'); ?></span></button>
    </div>
</div>
