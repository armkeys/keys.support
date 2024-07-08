<?php

// Email Integration : arp.
require_once 'admin/init.php';

$all_tabs   = [
    'settings' => 'Settings',
    'lists'    => 'Mailing Lists',
    'tutorial' => 'Tutorial',
];
$active_tab = 'settings';

echo '<ul class="nav nav-tabs">';
foreach ($all_tabs as $k => $v) {
    $active = $active_tab === $k ? 'active' : '';
    printf('<li class="%s nav-item"><a data-toggle="tab" class="nav-link" href="#%s_%s">%s</a></li>', esc_attr($active), esc_attr($config['id']), esc_attr($k), esc_attr($v));
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
echo '<input type="hidden" name="WishListMemberAction" value="save_autoresponder" />';
printf('<input type="hidden" name="autoresponder_id" value="%s" />', esc_attr($config['id']));

echo '</div>';
