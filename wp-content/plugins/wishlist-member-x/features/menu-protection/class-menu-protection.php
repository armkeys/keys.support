<?php

/**
 * Menu Protection
 *
 * @package WishListMember\Features
 */

namespace WishListMember\Features;

/**
 * Class for Menu Protection feature
 *
 * @since 3.8
 */
class Menu_Protection
{
    /**
     * <option> tags for membership levels dropdown
     * populated by constructor
     *
     * @var string
     */
    private $levels_options = '';

    /**
     * <option> tags for protection options dropdown
     * populated by constructor
     *
     * @var string
     */
    private $protection_options = '';

    /**
     * Constructor
     */
    public function __construct()
    {
        // Action to add our fields to each menu item.
        add_action('wp_nav_menu_item_custom_fields', [$this, 'custom_fields'], 99999, 2);
        // Action to handle saves.
        add_action('wp_update_nav_menu_item', [$this, 'update_nav_menu'], 10, 2);
        // Action to print our scripts and styles inline.
        add_action('admin_print_footer_scripts-nav-menus.php', [$this, 'print_scripts']);
        // Filter menus according to wishlist member menu protection.
        add_filter('wp_nav_menu_objects', [$this, 'filter_menu_items']);

        // Generate <option> tags for membership levels dropdown.
        $options = [];
        foreach (\WishListMember\Level::get_all_levels(true) as $level) {
            $options[] = sprintf('<option value="%s">%s</option>', $level->ID, $level->name);
        }
        $this->levels_options = implode('', $options);

        // Generate <option> tags for protection options dropdown.
        $this->protection_options .= sprintf('<option value="">%s</option>', __('No Protection', 'wishlist-member'));
        $this->protection_options .= sprintf('<option value="logged-out">%s</option>', __('Logged-out Users', 'wishlist-member'));
        $this->protection_options .= sprintf('<option value="logged-in">%s</option>', __('All Logged-in Users', 'wishlist-member'));
        $this->protection_options .= sprintf('<option value="member-of-levels">%s</option>', __('Logged-in and Member of any of the Selected Levels', 'wishlist-member'));
        $this->protection_options .= sprintf('<option value="non-member-of-levels">%s</option>', __('Logged-in and Non-Member of any of the Selected Levels', 'wishlist-member'));
    }

    /**
     * Filter out menus according to WishList Member menu protection
     *
     * @used-by wp_nav_menu_objects WordPress action
     * @param   array $items Menu items.
     * @return  array        Filtered menu items.
     */
    public function filter_menu_items($items)
    {
        $logged_in_user = get_current_user_id();
        foreach ($items as $key => $item) {
            $protection = wishlistmember_instance()->get_option('wlm_menu_protection-' . $item->ID);
            switch ($protection) {
                case 'logged-out':
                    if ($logged_in_user) {
                        // Remove menu: menu is for logged-out but user is logged-in.
                        unset($items[ $key ]);
                    }
                    break;

                case 'logged-in':
                case 'member-of-levels':
                case 'non-member-of-levels':
                    if (! $logged_in_user) {
                        // Remove menu: menu is for logged-in but user is logged-out.
                        unset($items[ $key ]);
                        continue 2;
                    }

                    // Get user's active levels.
                    $active_levels = (array) ( new \WishListMember\User($logged_in_user) )->active_levels;

                    // Get configured levels, with invalid or duplicates ids removed.
                    $protection_levels = array_unique(array_intersect((array) wishlistmember_instance()->get_option('wlm_menu_protection_levels-' . $item->ID), \WishListMember\Level::get_all_levels()));

                    // Remove inactive levels.
                    $protection_levels = array_intersect($protection_levels, $active_levels);

                    if ('member-of-levels' === $protection) {
                        if (! $protection_levels) {
                            // Remove menu: user is not a member of any of the levels.
                            unset($items[ $key ]);
                        }
                    } else { // Non-member-of-levels.
                        if ($protection_levels) {
                            // Remove menu: user is a member of at least of the levels.
                            unset($items[ $key ]);
                        }
                    }
                    break;
            }
        }
        return $items;
    }

    /**
     * Inserts our fields to each menu item
     *
     * @used-by wp_nav_menu_item_custom_fields WordPress action
     * @param   integer $menu_item_id Menu Item ID.
     * @param   object  $menu_item    Menu Item data object.
     */
    public function custom_fields($menu_item_id, $menu_item)
    {
        // Do not show our fields to our own login/logout menu item.
        if ('wlm_login_logout_navs22' === $menu_item->classes[0]) {
            return;
        }

        // Get protection value and preg_quote.
        $protection = preg_quote((string) wishlistmember_instance()->get_option('wlm_menu_protection-' . $menu_item_id), '/');
        // Get protection levels value, implode it and preg_quote it.
        $protection_levels = str_replace(' ', '|', preg_quote(implode(' ', (array) wishlistmember_instance()->get_option('wlm_menu_protection_levels-' . $menu_item_id)), '/'));
        ?>
        <p class="description description-wide">
            <label for="wishlistmember-menu-protection-<?php echo esc_attr($menu_item_id); ?>">
                <?php esc_html_e('WishList Member Protection', 'wishlist-member'); ?>
                <select id="wishlistmember-menu-protection-<?php echo esc_attr($menu_item_id); ?>" class="wishlistmember-menu-protection widefat" data-menu_item_id="<?php echo esc_attr($menu_item_id); ?>"
                    name="wishlistmember_menu_protection[<?php echo esc_attr($menu_item_id); ?>]">
                    <?php
                    echo wp_kses(
                        preg_replace(
                            '/(value="' . esc_attr($protection) . '")/',
                            '$1 selected',
                            $this->protection_options
                        ),
                        [
                            'option' => [
                                'value' => true,
                                'selected' => true,
                            ],
                        ]
                    );
                    ?>
                </select>
            </label>
        </p>
        <p id="wishlistmember-menu-protection-levels-container-<?php echo esc_attr($menu_item_id); ?>" class="description description-wide">
            <label for="wishlistmember-menu-protection-levels-<?php echo esc_attr($menu_item_id);?>">
                <select
                    data-placeholder="<?php esc_attr__('Select Membership Level(s)', 'wishlist-member'); ?>"
                    id="wishlistmember-menu-protection-levels-<?php echo esc_attr($menu_item_id); ?>"
                    multiple="multiple"
                    class="wishlistmember-menu-protection widefat"
                    name="wishlistmember_menu_protection_levels[<?php echo esc_attr($menu_item_id); ?>][]">
                    <?php
                    echo wp_kses(
                        preg_replace(
                            '/(value="(' . $protection_levels . ')")/',
                            '$1 selected',
                            $this->levels_options
                        ),
                        [
                            'option' => [
                                'value' => true,
                                'selected' => true,
                            ],
                        ]
                    );
                    ?>
                </select>
            </label>
        </p>
        <?php
    }

    /**
     * Saves our options
     *
     * @used-by wp_update_nav_menu_item WordPress action
     * @param   integer $menu_id      Menu ID.
     * @param   integer $menu_item_id Menu Item ID.
     */
    public function update_nav_menu($menu_id, $menu_item_id)
    {
        $val = isset(wlm_post_data()['wishlistmember_menu_protection'][$menu_item_id]) ? wlm_post_data()['wishlistmember_menu_protection'][$menu_item_id] : false;
        wishlistmember_instance()->save_option('wlm_menu_protection-' . $menu_item_id, wlm_or($val, ''));
        $val = isset(wlm_post_data()['wishlistmember_menu_protection_levels'][$menu_item_id]) ? wlm_post_data()['wishlistmember_menu_protection_levels'][$menu_item_id] : false;
        wishlistmember_instance()->save_option('wlm_menu_protection_levels-' . $menu_item_id, wlm_or($val, []));
    }

    /**
     * Print inline scripts and styles in the footer
     *
     * @used-by admin_print_footer_scripts-nav-menus.php WordPress action
     */
    public function print_scripts()
    {
        ?>
        <script>
            jQuery(function($) {
                $('.wishlistmember-menu-protection').on('change wlmchange', function() {
                    var target = '#wishlistmember-menu-protection-levels-container-' + $(this).data('menu_item_id');
                    switch ($(this).val()) {
                        case 'member-of-levels':
                        case 'non-member-of-levels':
                            $(target).show();
                            break;
                        default:
                            $(target).hide();
                    }
                }).trigger('wlmchange');
                $('select[name^="wishlistmember_menu_protection_levels"]').chosen({
                    width: '100%'
                });
            });
        </script>
        <style>
            div[id^="wishlistmember_menu_protection_levels_"] .chosen-choices li.search-field {
                width: 100% !important;
            }

            div[id^="wishlistmember_menu_protection_levels_"] .chosen-choices li.search-field input.default {
                width: 100% !important;
            }
        </style>
        <?php
    }
}
