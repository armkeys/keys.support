<?php

/**
 * WishList Member - Thrive Automator Integration
 * Member approved to level trigger
 *
 * @package WishListMember/WLMTA
 */

namespace WishListMember\WLMTA\Triggers;

use Thrive\Automator\Items\Trigger;

/**
 * Member approved to level trigger class
 */
class Member_Approved extends Trigger
{
    use Triggers_Trait;

    /**
     * Return Trigger ID
     *
     * @return string Trigger ID.
     */
    public static function get_id()
    {
        return 'wlmta/t/member-approved';
    }

    /**
     * Return Trigger hook
     *
     * @return string Trigger hook.
     */
    public static function get_wp_hook()
    {
        return 'wlmta_approve_user_levels';
    }

    /**
     * Return Trigger name
     *
     * @return string Trigger name.
     */
    public static function get_name()
    {
        return __('Admin approves member registration for a membership level', 'wishlist-member');
    }

    /**
     * Return Trigger description
     *
     * @return string Triger description.
     */
    public static function get_description()
    {
        return __('When an admin approves member registration for a membership level', 'wishlist-member');
    }
}
