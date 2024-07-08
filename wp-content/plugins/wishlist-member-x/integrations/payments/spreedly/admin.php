<?php

require_once 'admin/init.php';

$all_tabs         = [
    'api'           => 'API',
    'products'      => 'Products',
    'subscriptions' => 'Subscriptions',
    'tutorial'      => 'Tutorial',
];
$active_tab       = 'api';
$api_not_required = ['api', 'tutorial', 'products'];

printf('<div class="form-text text-danger help-block"><p class="mb-0">%s</p></div>', 'The Pin Payments Subscriptions product this integration was based on was discontinued by Pin Payments. Unfortunately, we cannot guarantee this integration will continue to function. This integration has been set as legacy in WishList Member. We recommend using a different payment provider Integration option to ensure proper functionality.');

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
    printf('<div id="%s_%s" class="tab-pane %s %s">', esc_attr($config['id']), esc_attr($k), esc_attr($active), esc_attr($api_required));
    include_once 'admin/tabs/' . $k . '.php';
    echo '</div>';
}

echo '<input type="hidden" class="-url" name="spreedlythankyou_url" />';
echo '<input type="hidden" name="action" value="admin_actions" />';
echo '<input type="hidden" name="WishListMemberAction" value="save" />';

echo '</div>';

wlm_print_script(plugin_dir_url(__FILE__) . 'assets/admin.js');
