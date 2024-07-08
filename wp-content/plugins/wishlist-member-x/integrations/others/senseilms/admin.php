<?php
// Other Integration : SenseiLMS.
require_once 'admin/init.php';

$the_posts = new WP_Query(
    [
        'post_type' => 'course',
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

$level_actions = [
    'add'    => esc_html__('Added', 'wishlist-member'),
    'remove' => esc_html__('Removed', 'wishlist-member'),
    'cancel' => esc_html__('Cancelled', 'wishlist-member'),
    'rereg'  => esc_html__('Uncancelled', 'wishlist-member'),
];

$all_tabs         = [
    // 'settings' => 'Settings',
    'level'  => 'Membership Level Actions',
    'course' => 'Course Actions',
];
$active_tab       = 'level';
$api_not_required = [];
?>
<div class="row">
    <div class="col plugin-status pt-2">
        <div class="text-warning"><p><em></em></p></div>
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
