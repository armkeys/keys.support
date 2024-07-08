<?php

/**
 * Sidebar
 *
 * @package WishListMember/UI
 */

?>
<!-- Start: v4 -->
<?php
    $menus = wishlistmember_instance()->get_menus(1);
if (empty($menus)) {
    return 0;
}

$menu_on_top = ( wishlistmember_instance()->get_option('menu_on_top') ) ? 'd-md-none' : '';
?>
<div class="sidebar">
    <div class="ap-side">
        <a href="#" class="wlm-brand d-none d-sm-block <?php echo esc_attr($menu_on_top); ?>" id="wlm3-hamburger-toggle">
            <img src="<?php echo esc_attr(wishlistmember_instance()->plugin_url3); ?>/ui/images/WishListMember-logomark-16px-wp.svg" style="min-width: 24px">
            <span class="logo-text"><?php esc_html_e('WishList Member', 'wishlist-member'); ?></span>
        </a>
        <ul id="wlm3-sidebar" class="nav nav-sidebar flex-column">
            <?php
            foreach ($menus as $menu_item) {
                if ($menu_item['legacy']) {
                    continue;
                }
                $icon      = empty($menu_item['icon']) ? 'settings' : esc_html($menu_item['icon']);
                $menu_link = wishlistmember_instance()->get_menu_link($menu_item['key'], 1);
                $active    = wishlistmember_instance()->is_menu_active($menu_link, 1) ? ' active' : '';
                $devonly   = wlm_is_unpackaged() ? '' : 'd-none';

                printf(
                    '<li class="item nav-item %s"><a data-title="%s" class="%s nav-link" href="%s"><i class="wlm-icons pull-left">%s</i><span>%s</span></a></li>',
                    ( empty($menu_item['devonly']) ? '' : esc_attr($devonly) ),
                    esc_attr(wishlistmember_instance()->format_title($menu_item['title'])),
                    esc_attr($active),
                    esc_attr($menu_link),
                    esc_attr($icon),
                    esc_html($menu_item['name'])
                );
            }
            ?>
            <li class="item nav-item toggle-sidebar">
                <a class="nav-link" href="">
                    <i class="wlm-icons md-26 left">keyboard_arrow_left</i>
                    <i class="wlm-icons md-26 right">keyboard_arrow_right</i>
                    <span><?php esc_html_e('Collapse menu', 'wishlist-member'); ?></span>
                </a>
            </li>
        </ul>
    </div>
</div>
<?php return count($menus); ?>
