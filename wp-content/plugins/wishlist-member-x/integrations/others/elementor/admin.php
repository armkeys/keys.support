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
printf('<p>Additional configuration is not required within this integration section. The integration is either enabled or disabled.</p>');
printf('<p>While this integration is enabled, WishList Member will add settings within Elementor related to the display of elements (sections, inner sections, widgets, etc.).</p>');
printf('<p>The settings are available in the Advanced > WishList Member section within an element in Elementor.</p>');
echo '<p>';
    echo wp_kses(
        sprintf(
            // Translators: 1 - Link to knowledgebase article.
            __('<a href="%1$s" target="_blank">Click Here</a> for additional information.', 'wishlist-member'),
            'https://wishlistmember.com/docs/elementor/',
        ),
        [
            'a' => [
                'href'   => [],
                'target' => [],
            ],
        ]
    );
    echo '</p>';
