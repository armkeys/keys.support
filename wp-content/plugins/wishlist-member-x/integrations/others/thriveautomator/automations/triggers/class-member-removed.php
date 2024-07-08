<?php

/**
 * WishList Member - Thrive Automator Integration
 * Member removed from level trigger
 *
 * @package WishListMember/WLMTA
 */

namespace WishListMember\WLMTA\Triggers;

use Thrive\Automator\Items\Trigger;

/**
 * Member removed from level trigger class
 */
class Member_Removed extends Trigger
{
    use Triggers_Trait;

    /**
     * Return Trigger ID
     *
     * @return string Trigger ID.
     */
    public static function get_id()
    {
        return 'wlmta/t/member-removed';
    }

    /**
     * Return Trigger hook
     *
     * @return string Trigger hook.
     */
    public static function get_wp_hook()
    {
        return 'wlmta_remove_user_levels';
    }

    /**
     * Return Trigger name
     *
     * @return string Trigger name.
     */
    public static function get_name()
    {
        return __('Member removed from one or more membership levels', 'wishlist-member');
    }

    /**
     * Return Trigger description
     *
     * @return string Triger description.
     */
    public static function get_description()
    {
        return __('When a member is removed from one or more membership levels', 'wishlist-member');
    }
}
