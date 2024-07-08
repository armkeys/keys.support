<?php
// Other Integration : FluentCRM.
require_once 'admin/init.php';

$fluentcrm_settings = isset($ar_data[ $config['id'] ]['fluentcrm_settings']) ? $ar_data[ $config['id'] ]['fluentcrm_settings'] : [];
$lists              = [];
$tags               = [];
$active_plugins     = wlm_get_active_plugins();

if (in_array('FluentCRM - Marketing Automation For WordPress', $active_plugins) || isset($active_plugins['fluent-crm/fluent-crm.php']) || is_plugin_active('fluent-crm/fluent-crm.php')) {
    $listApi  = FluentCrmApi('lists');
    $allLists = $listApi->all();
    foreach ($allLists as $key => $value) {
        $lists[ $value->id ] = [
            'id'    => $value->id,
            'title' => $value->title,
        ];
    }

    $tagApi  = FluentCrmApi('tags');
    $allTags = $tagApi->all();
    foreach ($allTags as $key => $value) {
        $tags[ $value->id ] = [
            'id'    => $value->id,
            'title' => $value->title,
        ];
    }
}

?>
<div class="row">
    <div class="col plugin-status pt-2">
        <div class="text-warning"><p><em></em></p></div>
    </div>
</div>

<?php
$all_tabs = [
    'level'    => __('Membership Level Actions', 'wishlist-member'),
    'list'     => __('List Actions', 'wishlist-member'),
    'tag'      => __('Tag Actions', 'wishlist-member'),
    'tutorial' => __('Tutorial', 'wishlist-member'),
];

$active_tab       = 'level';
$api_not_required = [];

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
wlm_print_style(plugin_dir_url(__FILE__) . 'assets/admin.css');
?>
