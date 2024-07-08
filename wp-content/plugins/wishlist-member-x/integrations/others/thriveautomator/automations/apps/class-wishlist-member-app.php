<?php

/**
 * WishList Member Thrive Automator App
 * WishList Member App
 *
 * @package WishListMember/WLMTA
 */

namespace WishListMember\WLMTA\Apps;

use Thrive\Automator\Items\App;

/**
 * WishList Member App class
 */
class WishList_Member_App extends App
{
    /**
     * Return App ID
     *
     * @return string App ID.
     */
    public static function get_id()
    {
        return 'wlmta';
    }

    /**
     * Return App name
     *
     * @return string App name.
     */
    public static function get_name()
    {
        return 'WishList Member';
    }

    /**
     * Return App description
     *
     * @return string App description.
     */
    public static function get_description()
    {
        return __('WishList Member for Thrive Automator', 'wishlist-member');
    }

    /**
     * Return URL to App logo
     *
     * @return string URL to App logo.
     */
    public static function get_logo()
    {
        return wishlistmember_instance()->plugin_url3 . '/ui/images/WishListMember-logomark-32px-wp.svg';
    }

    /**
     * Enable access to this App.
     *
     * @return true Always true.
     */
    public static function has_access()
    {
        return true;
    }

    /**
     * Return access URL
     *
     * @return string URL to knowledgebase article.
     */
    public static function get_acccess_url()
    {
        return 'https://wishlistmember.com/docs/thrive-automator/';
    }

    /**
     * Return membership levels
     *
     * @return array Associative array of membership levels.
     */
    public static function get_membership_levels()
    {
        static $levels;
        if (is_null($levels)) {
            $levels = wishlistmember_instance()->get_option('wpm_levels');
        }
        return $levels;
    }
}
