<?php

/**
 * WishList Member - Thrive Automator Integration
 * User Levels data object
 *
 * @package WishListMember/WLMTA
 */

namespace WishListMember\WLMTA\Data_Objects;

use WishListMember\WLMTA\Data_Fields;
use WishListMember\WLMTA\Apps\WishList_Member_App;

/**
 * User Levels data object class.
 */
class User_Levels extends \Thrive\Automator\Items\Data_Object
{
    /**
     * Return Data object ID
     *
     * @return string Data object ID.
     */
    public static function get_id()
    {
        return 'wlmta/do/user-levels';
    }

    /**
     * Return Data object nice name
     *
     * @return string Data object nice name.
     */
    public static function get_nice_name()
    {
        error_log(print_r(debug_backtrace(0), true));
        return __('User Levels', 'wishlist-member');
    }

    /**
     * Return data object fields
     *
     * @return array Array of Data field IDs.
     */
    public static function get_fields()
    {
        return [Data_Fields\User_Levels::get_id()];
    }

    /**
     * Create the object
     *
     * @param  string $registration_data Registration Data.
     * @return array Data object.
     */
    public static function create_object($user_id)
    {
        $u = new \WishListMember\User($user_id);
        return [Data_Fields\User_Levels::get_id() => $u->Levels];
    }
}
