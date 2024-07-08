<?php

/**
 * Groundhogg admin
 *
 * @package WishListMember/Autoresponders
 */

// Initialize.
require_once 'admin/init.php';

$groundhogg_settings = isset($ar_data[ $config['id'] ]['groundhogg_settings']) ? $ar_data[ $config['id'] ]['groundhogg_settings'] : [];
$tags                = [];
$active_plugins      = wlm_get_active_plugins();

if (in_array('Groundhogg', $active_plugins, true) || isset($active_plugins['groundhogg/groundhogg.php']) || is_plugin_active('groundhogg/groundhogg.php')) {
    $tags = array_map(
        function ($tag) {
            return [
                'id'    => $tag->tag_id,
                'title' => $tag->tag_name,
            ];
        },
        \Groundhogg\get_db('tags')->query()
    );

    $admin_tabs = [
        'level'    => __('Membership Level Actions', 'wishlist-member'),
        'tag'      => __('Tag Actions', 'wishlist-member'),
        'tutorial' => __('Tutorial', 'wishlist-member'),
    ];

    $active_tab       = 'level';
    $api_not_required = [];

    echo '<ul class="nav nav-tabs">';
    foreach ($admin_tabs as $k => $v) {
        $active       = $active_tab === $k ? 'active' : '';
        $api_required = in_array($k, $api_not_required, true) ? '' : 'api-required';
        printf('<li class="%s %s nav-item"><a class="nav-link" data-toggle="tab" href="#%s_%s">%s</a></li>', esc_attr($active), esc_attr($api_required), esc_attr($config['id']), esc_attr($k), esc_html($v));
    }
    echo '</ul>';

    echo '<div class="tab-content">';
    foreach ($admin_tabs as $k => $v) {
        $active       = $active_tab === $k ? 'active in' : '';
        $api_required = in_array($k, $api_not_required, true) ? '' : 'api-required';
        printf('<div id="%s_%s" class="tab-pane %s %s">', esc_attr($config['id']), esc_attr($k), esc_attr($api_required), esc_attr($active));
        include_once 'admin/tabs/' . $k . '.php';
        echo '</div>';
    }
    echo '</div>';

    wlm_print_script(plugin_dir_url(__FILE__) . 'assets/admin.js');
    wlm_print_style(plugin_dir_url(__FILE__) . 'assets/admin.css');
    return;
}
?>
<div class="tab-content">
    <div class="row">
        <div class="col pt-2">
            <p>
                <?php
                printf(
                    wp_kses(
                        // Translators: %s Link.
                        __('This integration requires the <a href="%s" target="_blank">Groundhogg</a> plugin.', 'wishlist-member'),
                        [
                            'a' => [
                                'href'   => true,
                                'target' => true,
                            ],
                        ]
                    ),
                    esc_url($config['link'])
                );
                ?>
            </p>
        </div>
    </div>
</div>
