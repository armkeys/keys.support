<?php

/**
 * WishList Member - Thrive Automator Integration
 * Member added to level trigger
 *
 * @package WishListMember/WLMTA
 */

namespace WishListMember\WLMTA\Triggers;

use Thrive\Automator\Items\Trigger;

/**
 * Member added to level trigger class
 */
class Member_Added extends Trigger
{
    use Triggers_Trait;

    /**
     * Return Trigger ID
     *
     * @return string Trigger ID.
     */
    public static function get_id()
    {
        return 'wlmta/t/member-added';
    }

    /**
     * Return Trigger hook
     *
     * @return string Trigger hook.
     */
    public static function get_wp_hook()
    {
        return 'wlmta_add_user_levels';
    }

    /**
     * Return Trigger name
     *
     * @return string Trigger name.
     */
    public static function get_name()
    {
        return __('Member added to one or more membership levels', 'wishlist-member');
    }

    /**
     * Return Trigger description
     *
     * @return string Triger description.
     */
    public static function get_description()
    {
        return __('When a member is added to one or more membership levels', 'wishlist-member');
    }
}
