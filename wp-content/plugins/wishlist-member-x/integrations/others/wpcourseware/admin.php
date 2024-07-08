<?php
// Other Integration : LearnDash.
require_once 'admin/init.php';

$the_posts      = [];
$active_plugins = wlm_get_active_plugins();
if (in_array('WP Courseware', $active_plugins) || isset($active_plugins['wp-courseware/wp-courseware.php'])) {
    $the_posts = WPCW_courses_getCourseList(false);
}
$courses = [];
if (count($the_posts)) {
    foreach ($the_posts as $key => $c) {
        $courses[ $key ] = [
            'id'    => $key,
            'title' => $c,
        ];
    }
}

$all_tabs         = [
    'level'    => __('Membership Level Actions', 'wishlist-member'),
    'course'   => __('Course Actions', 'wishlist-member'),
    'tutorial' => __('Tutorial', 'wishlist-member'),
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
