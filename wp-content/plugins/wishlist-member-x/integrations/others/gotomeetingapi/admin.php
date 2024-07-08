<?php

$go = include_once 'admin/init.php';
if (false === $go) {
    return;
}

$all_tabs         = [
    'api'      => 'API',
    'settings' => 'Settings',
    'tutorial' => 'Tutorial',
];
$active_tab       = 'api';
$api_not_required = ['api', 'tutorial'];

$x = wlm_arrval($data, 'authorizationcode');
if ($x && strlen($x) <= 10) {
    printf(
        '<div class="alert alert-warning"><p>%s</p><p>%s</p></div>',
        esc_html__('Important: The previous Authentication method of GotoWebinar will be deprecated on August 14, 2018. This means that after the said date your GoToWebinar integration will stop working.', 'wishlist-member'),
        wp_kses_data(
            sprintf(
                // Translators: 1: <a> tag pointing to oauth auth URL.
                __('Please reauthenticate your GoToWebinar Integration by getting a new Authentication Code using %s and paste it in the Authorization Code box below and then click the "Update Webinar Settings" button.', 'wishlist-member'),
                '<a target="_blank" href="' . esc_url($oauth->getApiAuthorizationUrl()) . '">' . esc_html__('this link', 'wishlist-member') . '</a>'
            )
        )
    );
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
echo '<input type="hidden" name="action" value="admin_actions" />';
echo '<input type="hidden" name="WishListMemberAction" value="save_webinar" />';
printf('<input type="hidden" name="webinar_id" value="%s">', esc_attr($config['id']));

echo '</div>';

wlm_print_script(plugin_dir_url(__FILE__) . 'assets/admin.js');
