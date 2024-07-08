<?php

/**
 * WishList Member - Thrive Automator Integration
 * Registration data object
 *
 * @package WishListMember/WLMTA
 */

namespace WishListMember\WLMTA\Data_Objects;

use WishListMember\WLMTA\Data_Fields;
use WishListMember\WLMTA\Apps\WishList_Member_App;

/**
 * Registration data object class.
 */
class Registration_Data extends \Thrive\Automator\Items\Data_Object
{
    /**
     * Return Data object ID
     *
     * @return string Data object ID.
     */
    public static function get_id()
    {
        return 'wlmta/do/registration-data';
    }

    /**
     * Return Data object nice name
     *
     * @return string Data object nice name.
     */
    public static function get_nice_name()
    {
        return __('WishList Member Registration Data', 'wishlist-member');
    }

    /**
     * Return data object fields
     *
     * @return array Array of Data field IDs.
     */
    public static function get_fields()
    {
        return [Data_Fields\Registration_Data::get_id()];
    }

    /**
     * Create the object
     *
     * @param  string $registration_data Registration Data.
     * @return array Data object.
     */
    public static function create_object($registration_data)
    {
        $registration_data['level_id'] = $registration_data['wpm_id'];
        unset(
            $registration_data['wpm_id'],
            $registration_data['wlm_form_id'],
            $registration_data['action'],
            $registration_data['password1'],
            $registration_data['password2'],
            $registration_data['user_pass'],
            $registration_data['reg_date'],
            $registration_data['cookiehash'],
            $registration_data['reg_page'],
            $registration_data['required_fields'],
            $registration_data['orig_firstname'],
            $registration_data['orig_lastname'],
            $registration_data['orig_email'],
        );
        return [
            Data_Fields\Registration_Data::get_id() => $registration_data,
        ];
    }
}
