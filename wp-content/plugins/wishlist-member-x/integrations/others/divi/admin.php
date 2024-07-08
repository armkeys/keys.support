<?php

$go = include_once 'admin/init.php';
if (false === $go) {
    return;
}

$all_tabs         = [
    'tutorial' => 'Tutorial',
];
$active_tab       = '';
$api_not_required = ['tutorial'];

echo '<ul class="nav nav-tabs">';
foreach ($all_tabs as $k => $v) {
    $active       = $active_tab === $k ? 'active' : '';
    $api_required = in_array($k, $api_not_required, true) ? '' : 'api-required';
    printf('<li class="%s %s nav-item"><a class="nav-link" data-toggle="tab" href="#%s_%s">%s</a></li>', esc_attr($active), esc_attr($api_required), esc_attr($config['id']), esc_attr($k), esc_attr($v));
}
echo '</ul>';

printf('<p>%s</p>', esc_html__('Configuration is not required within WishList Member for this integration. The integration is either enabled or disabled.', 'wishlist-member'));
printf('<p>%s</p>', esc_html__('While the integration is enabled, WishList Member will add a setting within Divi related to the display of Divi Sections and Rows.', 'wishlist-member'));
printf('<p>%s</p>', esc_html__('The setting is available in the Advanced Options tab of all Sections and Rows within Divi.', 'wishlist-member'));
