<?php

/**
 * WishList Member - Thrive Automator Integration
 * Level trigger methods
 *
 * @package WishListMember/WLMTA
 */

namespace WishListMember\WLMTA\Triggers;

use WishListMember\WLMTA\Apps\WishList_Member_App;
use WishListMember\WLMTA\Data_Objects\Membership_Level;
use Thrive\Automator\Items\User_Data;

/**
 * Level trigger trait
 */
trait Triggers_Trait
{
    /**
     * Return app ID
     *
     * @return string App ID.
     */
    public static function get_app_id()
    {
        return WishList_Member_App::get_id();
    }


    /**
     * Trigger image
     *
     * @return string URL to action image.
     */
    public static function get_image()
    {
        return WishList_Member_App::get_logo();
    }

    /**
     * Return provided objects
     *
     * @return array Array of object IDs.
     */
    public static function get_provided_data_objects()
    {
        if (method_exists(__CLASS__, '_get_provided_data_objects')) {
            return self::_get_provided_data_objects();
        }
        return [
            User_Data::get_id(),
            Membership_Level::get_id(),
        ];
    }

    /**
     * Return number of parameters the hook accepts.
     *
     * @return integer Always 2.
     */
    public static function get_hook_params_number()
    {
        return 2;
    }
}
