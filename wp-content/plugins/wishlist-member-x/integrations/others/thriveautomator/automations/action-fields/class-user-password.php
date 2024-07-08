<?php

/**
 * WishList Member - Thrive Automator Integration
 * User Password action field
 *
 * @package WishListMember/WLMTA
 */

namespace WishListMember\WLMTA\Action_Fields;

use WishListMember\WLMTA\Apps\WishList_Member_App;
use Thrive\Automator\Utils;

/**
 * User Password action field class
 */
class User_Password extends \Thrive\Automator\Items\Action_Field
{
    /**
     * Return action field ID
     *
     * @return string Action field ID.
     */
    public static function get_id()
    {
        return 'wlmta/af/user-password';
    }

    /**
     * Return action field name
     *
     * @return string Action field name.
     */
    public static function get_name()
    {
        return __('Password', 'wishlist-member');
    }

    /**
     * Return action field description
     *
     * @return string Action field description.
     */
    public static function get_description()
    {
        return __('Password (Auto-generated if left empty)', 'wishlist-member');
    }

    /**
     * Return action field placeholder
     *
     * @return string Action field placeholder.
     */
    public static function get_placeholder()
    {
        return '';
    }

    /**
     * Return action field type
     *
     * @return string Action field type.
     */
    public static function get_type()
    {
            return Utils::FIELD_TYPE_TEXT;
    }

    /**
     * Allow dynamic data for action field.
     *
     * @return true Always true.
     */
    public static function allow_dynamic_data()
    {
        return true;
    }

    /**
     * Support ajax for action field.
     *
     * @return true Always true.
     */
    public static function is_ajax_field()
    {
        return true;
    }

    /**
     * Set field as required
     *
     * @return array Array of validation strings.
     */
    public static function get_validators()
    {
        return [];
    }
}
