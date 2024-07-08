<?php

require_once 'admin/init.php';

$error_messages = [];
if (! function_exists('curl_init')) {
    $error_messages[] = __('CURL PHP extension.', 'wishlist-member');
}
if (! function_exists('json_decode')) {
    $error_messages[] = __('JSON PHP extension.', 'wishlist-member');
}
if (! function_exists('mb_detect_encoding')) {
    $error_messages[] = __('Multibyte String PHP extension.', 'wishlist-member');
}

if (count($error_messages)) {
    printf(
        '<div class="form-text text-danger help-block"><p class="title">%s</p><br><ul>%s</ul><br><p>%s</p></div>',
        esc_html__('Stripe requires the following PHP extensions:', 'wishlist-member'),
        wp_kses('<li>' . implode('</li><li>', $error_messages) . '</li>', ['li' => true]),
        esc_html__('Please ask your web hosting provider to enable it.', 'wishlist-member')
    );
    return;
}

$all_tabs         = [
    'settings' => 'Settings',
    'products' => 'Products',
    'tutorial' => 'Tutorial',
];
$active_tab       = 'settings';
$api_not_required = ['settings', 'tutorial'];

echo '<ul class="nav nav-tabs">';
foreach ($all_tabs as $k => $v) {
    $active       = $active_tab === $k ? 'active' : '';
    $api_required = in_array($k, $api_not_required, true) ? '' : 'api-required';
    printf('<li class="%s nav-item"><a class="nav-link %s " data-toggle="tab" href="#%s_%s">%s</a></li>', esc_attr($api_required), esc_attr($active), esc_attr($config['id']), esc_attr($k), esc_html($v));
}
echo '</ul>';
echo '<div class="tab-content">';
foreach ($all_tabs as $k => $v) {
    $active       = $active_tab === $k ? 'active in' : '';
    $api_required = in_array($k, $api_not_required, true) ? '' : 'api-required';
    printf('<div id="%s_%s" class="tab-pane %s %s">', esc_attr($config['id']), esc_attr($k), esc_attr($active), esc_attr($api_required));
    include_once 'admin/tabs/' . $k . '.php';
    echo '</div>';
}
echo '</div>';


wlm_print_script(plugin_dir_url(__FILE__) . 'assets/admin.js');
wlm_print_style(plugin_dir_url(__FILE__) . 'assets/admin.css');
