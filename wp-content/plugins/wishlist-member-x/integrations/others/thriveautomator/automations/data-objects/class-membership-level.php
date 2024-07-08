<?php

/**
 * WishList Member - Thrive Automator Integration
 * Membership level data object
 *
 * @package WishListMember/WLMTA
 */

namespace WishListMember\WLMTA\Data_Objects;

use WishListMember\WLMTA\Data_Fields\Membership_Level_Name;
use WishListMember\WLMTA\Data_Fields\Membership_Level_ID;
use WishListMember\WLMTA\Apps\WishList_Member_App;

/**
 * Membership level data object class.
 */
class Membership_Level extends \Thrive\Automator\Items\Data_Object
{
    /**
     * Return Data object ID
     *
     * @return string Data object ID.
     */
    public static function get_id()
    {
        return 'wlmta/do/membership-levels';
    }

    /**
     * Return Data object nice name
     *
     * @return string Data object nice name.
     */
    public static function get_nice_name(): string
    {
        return __('Membership Level Data', 'wishlist-member');
    }

    /**
     * Return data object fields
     *
     * @return array Array of Data field IDs.
     */
    public static function get_fields()
    {
        return [Membership_Level_Name::get_id(), Membership_Level_ID::get_id()];
    }

    /**
     * Create the object
     *
     * @param  string $level Level ID.
     * @return array Data object.
     */
    public static function create_object($level)
    {
        $_levels = WishList_Member_App::get_membership_levels();

        // Default.
        $object = [
            Membership_Level_ID::get_id()   => null,
            Membership_Level_Name::get_id() => null,
        ];

        // Set value.
        if (! empty($_levels[ $level ])) {
            $object[ Membership_Level_Name::get_id() ] = $_levels[ $level ]['name'];
            $object[ Membership_Level_ID::get_id() ]   = $_levels[ $level ]['id'];
        }

        return $object;
    }
}
