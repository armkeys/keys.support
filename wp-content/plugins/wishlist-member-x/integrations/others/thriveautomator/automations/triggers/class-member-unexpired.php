<?php

/**
 * WishList Member - Thrive Automator Integration
 * Member unexpired from level trigger
 *
 * @package WishListMember/WLMTA
 */

namespace WishListMember\WLMTA\Triggers;

use Thrive\Automator\Items\Trigger;

/**
 * Member unexpired from level trigger class
 */
class Member_Unexpired extends Trigger
{
    use Triggers_Trait;

    /**
     * Return Trigger ID
     *
     * @return string Trigger ID.
     */
    public static function get_id()
    {
        return 'wlmta/t/member-unexpired';
    }

    /**
     * Return Trigger hook
     *
     * @return string Trigger hook.
     */
    public static function get_wp_hook()
    {
        return 'wlmta_unexpire_user_levels';
    }

    /**
     * Return Trigger name
     *
     * @return string Trigger name.
     */
    public static function get_name()
    {
        return __('Member unexpired from one or more membership levels', 'wishlist-member');
    }

    /**
     * Return Trigger description
     *
     * @return string Triger description.
     */
    public static function get_description()
    {
        return __('When a member is unexpired from one or more membership levels', 'wishlist-member');
    }
}
