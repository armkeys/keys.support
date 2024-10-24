<?php
$go = include_once 'admin/init.php';
if (false === $go) {
    return;
}

$the_posts = new WP_Query(
    [
        'post_type' => 'sfwd-courses',
        'nopaging'  => true,
    ]
);
$courses   = [];
if (count($the_posts->posts)) {
    foreach ($the_posts->posts as $key => $c) {
        $courses[ $c->ID ] = [
            'id'    => $c->ID,
            'title' => $c->post_title,
        ];
    }
}

$the_groups = new WP_Query(
    [
        'post_type' => 'groups',
        'nopaging'  => true,
    ]
);
$groups     = [];
if (count($the_groups->posts)) {
    foreach ($the_groups->posts as $key => $c) {
        $groups[ $c->ID ] = [
            'id'    => $c->ID,
            'title' => $c->post_title,
        ];
    }
}


$level_actions = [
    'add'     => esc_html__('Added', 'wishlist-member'),
    'remove'  => esc_html__('Removed', 'wishlist-member'),
    'cancel'  => esc_html__('Cancelled', 'wishlist-member'),
    'rereg'   => esc_html__('Uncancelled', 'wishlist-member'),
    'expired' => esc_html__('Expired', 'wishlist-member'),
];

$all_tabs         = [
    'level'    => __('Membership Level Actions', 'wishlist-member'),
    'course'   => __('Course Actions', 'wishlist-member'),
    'group'    => __('Group Actions', 'wishlist-member'),
    'settings' => __('Settings', 'wishlist-member'),
    'tutorial' => __('Tutorial', 'wishlist-member'),
];
$active_tab       = 'level';
$api_not_required = [];

$wlm_ld_group_default = $this->get_option('wlm_ld_group_default');
?>
<div class="row">
    <div class="col plugin-status pt-2">
        <div class="text-warning"></div>
    </div>
</div>


<?php
echo '<ul class="nav nav-tabs">';
foreach ($all_tabs as $k => $v) {
    $active       = $active_tab === $k ? 'active' : '';
    $api_required = in_array($k, $api_not_required, true) ? '' : 'api-required';
    printf('<li class="%s %s nav-item"><a class="nav-link" data-toggle="tab" href="#%s_%s">%s</a></li>', esc_attr($active), esc_attr($api_required), esc_attr($config['id']), esc_attr($k), esc_attr($v));
}
echo '</ul>';

echo '<div class="tab-content">';
foreach ($all_tabs as $k => $v) {
    $active       = $active_tab === $k ? 'active in' : '';
    $api_required = in_array($k, $api_not_required, true) ? '' : 'api-required';
    printf('<div id="%s_%s" class="tab-pane %s %s">', esc_attr($config['id']), esc_attr($k), esc_attr($api_required), esc_attr($active));
    include_once 'admin/tabs/' . $k . '.php';
    echo '</div>';
}
echo '</div>';

wlm_print_script(plugin_dir_url(__FILE__) . 'assets/admin.js');
?>
