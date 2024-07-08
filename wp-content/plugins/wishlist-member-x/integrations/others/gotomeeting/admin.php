<?php

$go = include_once 'admin/init.php';
if (false === $go) {
    return;
}

$all_tabs   = [
    'settings' => 'Settings',
    'tutorial' => 'Tutorial',
];
$active_tab = 'settings';

printf('<div class="form-text text-danger help-block"><p>%s</p><p><a href="?page=WishListMember&wl=setup/integrations/other_provider/gotomeetingapi">%s</a></p></div>', 'This GoToWebinar integration is now deprecated. It is strongly recommended to use the updated GoToWebinar integration.', 'Click here to do this now.');

echo '<ul class="nav nav-tabs">';
foreach ($all_tabs as $k => $v) {
    $active = $active_tab === $k ? 'active' : '';
    printf('<li class="%s nav-item"><a class="nav-link" data-toggle="tab" href="#%s_%s">%s</a></li>', esc_attr($active), esc_attr($config['id']), esc_attr($k), esc_html($v));
}
echo '</ul>';
echo '<div class="tab-content">';
foreach ($all_tabs as $k => $v) {
    $active = $active_tab === $k ? 'active in' : '';
    printf('<div id="%s_%s" class="tab-pane %s">', esc_attr($config['id']), esc_attr($k), esc_attr($active));
    include_once 'admin/tabs/' . $k . '.php';
    echo '</div>';
}
echo '<input type="hidden" name="action" value="admin_actions" />';
echo '<input type="hidden" name="WishListMemberAction" value="save_webinar" />';
printf('<input type="hidden" name="webinar_id" value="%s">', esc_attr($config['id']));

echo '</div>';
