<?php

/**
 * WishList Member - Thrive Automator Integration
 * User Email action field
 *
 * @package WishListMember/WLMTA
 */

namespace WishListMember\WLMTA\Action_Fields;

use WishListMember\WLMTA\Apps\WishList_Member_App;
use Thrive\Automator\Utils;

/**
 * User Email action field class
 */
class User_Email extends \Thrive\Automator\Items\Action_Field
{
    /**
     * Return action field ID
     *
     * @return string Action field ID.
     */
    public static function get_id()
    {
        return 'wlmta/af/user-email';
    }

    /**
     * Return action field name
     *
     * @return string Action field name.
     */
    public static function get_name()
    {
        return __('User Email', 'wishlist-member');
    }

    /**
     * Return action field description
     *
     * @return string Action field description.
     */
    public static function get_description()
    {
        return __('User Email', 'wishlist-member');
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
     * Support ajax for action field.
     *
     * @return true Always true.
     */
    public static function is_ajax_field()
    {
        return true;
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
     * Set field as required
     *
     * @return array Array of validation strings.
     */
    public static function get_validators()
    {
        return [static::REQUIRED_VALIDATION];
    }
}
