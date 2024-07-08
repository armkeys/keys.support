<?php

/**
 * WishList Member - Thrive Automator Integration
 * Member confirmed to level trigger
 *
 * @package WishListMember/WLMTA
 */

namespace WishListMember\WLMTA\Triggers;

use Thrive\Automator\Items\Trigger;

/**
 * Member confirmed to level trigger class
 */
class Member_Confirmed extends Trigger
{
    use Triggers_Trait;

    /**
     * Return Trigger ID
     *
     * @return string Trigger ID.
     */
    public static function get_id()
    {
        return 'wlmta/t/member-confirmed';
    }

    /**
     * Return Trigger hook
     *
     * @return string Trigger hook.
     */
    public static function get_wp_hook()
    {
        return 'wlmta_confirm_user_levels';
    }

    /**
     * Return Trigger name
     *
     * @return string Trigger name.
     */
    public static function get_name()
    {
        return __('Member confirms registration to a membership level', 'wishlist-member');
    }

    /**
     * Return Trigger description
     *
     * @return string Triger description.
     */
    public static function get_description()
    {
        return __('When a member confirms registration to a membership level', 'wishlist-member');
    }
}
