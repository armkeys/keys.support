<?php
// Other Integration : BuddyBoss.
require_once 'admin/init.php';

$groups         = [];
$member_types   = [];
$active_plugins = wlm_get_active_plugins();

if (in_array('BuddyBoss Platform', $active_plugins) || isset($active_plugins['buddyboss-platform/bp-loader.php']) || is_plugin_active('buddyboss-platform/bp-loader.php')) {
    if (bp_is_active('groups')) {
        $g = BP_Groups_Group::get(
            [
                'type'        => 'alphabetical',
                'per_page'    => 9999,
                'show_hidden' => 'true',
            ]
        );
        $g = isset($g['groups']) ? $g['groups'] : [];
        foreach ($g as $key => $value) {
            $groups[ $value->id ] = [
                'id'    => $value->id,
                'title' => $value->name,
            ];
        }
    }

    $is_member_type_enabled = bp_member_type_enable_disable();
    if ($is_member_type_enabled) {
        $the_posts = new WP_Query(
            [
                'post_type'   => bp_get_member_type_post_type(),
                'post_status' => 'publish',
                'nopaging'    => true,
            ]
        );
        if (count($the_posts->posts)) {
            foreach ($the_posts->posts as $key => $c) {
                $member_types[ $c->post_name ] = [
                    'id'      => $c->ID,
                    'title'   => $c->post_title,
                    'post_id' => $c->ID,
                ];
            }
        }
    }
}

$level_actions = [
    'add'     => esc_html__('Added', 'wishlist-member'),
    'remove'  => esc_html__('Removed', 'wishlist-member'),
    'cancel'  => esc_html__('Cancelled', 'wishlist-member'),
    'rereg'   => esc_html__('Uncancelled', 'wishlist-member'),
    'expired' => esc_html__('Expired', 'wishlist-member'),
];

$all_tabs = [
    'level' => __('Membership Level Actions', 'wishlist-member'),
    'group' => __('Group Actions', 'wishlist-member'),
];
if ($is_member_type_enabled) {
    $all_tabs['type'] = __('Profile Type Actions', 'wishlist-member');
}
$all_tabs['settings'] = __('Settings', 'wishlist-member');
$all_tabs['tutorial'] = __('Tutorial', 'wishlist-member');

$active_tab       = 'level';
$api_not_required = [];

$wlm_bb_group_default = $this->get_option('wlm_bb_group_default');
$wlm_bb_ptype_default = $this->get_option('wlm_bb_ptype_default');
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
?>
