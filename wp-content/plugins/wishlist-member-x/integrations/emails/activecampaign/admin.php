<?php

// Email Integration : ActiveCampaign.
require_once 'admin/init.php';

$all_tabs         = [
    'settings' => __('Settings', 'wishlist-member'),
    'lists'    => __('Membership Level Actions', 'wishlist-member'),
    'tags'     => __('Tag Actions', 'wishlist-member'),
    'fields'   => __('Field Mapping', 'wishlist-member'),
    'tutorial' => __('Tutorial', 'wishlist-member'),
];
$active_tab       = 'settings';
$api_not_required = ['settings', 'tutorial'];

echo '<ul class="nav nav-tabs">';
foreach ($all_tabs as $k => $v) {
    $active       = $active_tab === $k ? 'active' : '';
    $api_required = in_array($k, $api_not_required, true) ? '' : 'api-required';
    printf('<li class="%s %s nav-item"><a data-toggle="tab" class="nav-link" href="#%s_%s">%s</a></li>', esc_attr($active), esc_attr($api_required), esc_attr($config['id']), esc_attr($k), esc_html($v));
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
echo '<input type="hidden" name="action" value="admin_actions" />';
echo '<input type="hidden" name="WishListMemberAction" value="save_autoresponder" />';
printf('<input type="hidden" name="autoresponder_id" value="%s" />', esc_attr($config['id']));

echo '</div>';

wlm_print_script(plugin_dir_url(__FILE__) . 'assets/admin.js');
wlm_print_style(plugin_dir_url(__FILE__) . 'assets/admin.css');