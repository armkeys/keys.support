<?php

// Payment Integration : Infusionsoft.
require_once 'admin/init.php';

$all_tabs = [
    'settings' => 'Settings',
    'products' => 'Products',
    'cron'     => 'Cron Job',
    'tutorial' => 'Tutorial',

];
$active_tab       = 'settings';
$api_not_required = ['settings', 'tutorial'];


if (! wishlistmember_instance()->payment_integration_is_active('infusionsoft') || ! wishlistmember_instance()->get_option('ismachine') || ! wishlistmember_instance()->get_option('isapikey')) {
}

$ar = wishlistmember_instance()->get_option('Autoresponders');
$keap_email_provider_settings = isset($ar['infusionsoft']) ? $ar['infusionsoft'] : false;

// Check if Keap Email Provider integration is enabled and is using the same App Name and Encrypted Key.
if (wishlistmember_instance()->email_integration_is_active('infusionsoft')) {
    if ($keap_email_provider_settings['iskey'] == wishlistmember_instance()->get_option('isapikey') && $keap_email_provider_settings['ismname'] == wishlistmember_instance()->get_option('ismachine')) {
        echo sprintf('<p><div class="alert alert-warning"> <b> %s </b> %s </div></p>', 'Please Note:', __('The Keap Email Provider integration with WishList Member is also enabled in the Setup > Integrations > Email Providers section and is using the same connection to your Keap account. This means the Keap account tags will be available in both of the Keap integration sections in WishList Member.', 'wishlist-member'));
    }
}

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
