<?php

/**
 * WishList Member - Thrive Automator Integration
 * Transaction ID action field
 *
 * @package WishListMember/WLMTA
 */

namespace WishListMember\WLMTA\Action_Fields;

use WishListMember\WLMTA\Apps\WishList_Member_App;
use Thrive\Automator\Utils;

/**
 * Transaction ID action field class
 */
class Transaction_ID extends \Thrive\Automator\Items\Action_Field
{
    /**
     * Return action field ID
     *
     * @return string Action field ID.
     */
    public static function get_id()
    {
        return 'wlmta/af/transaction-id';
    }

    /**
     * Return action field name
     *
     * @return string Action field name.
     */
    public static function get_name()
    {
        return __('Transaction ID', 'wishlist-member');
    }

    /**
     * Return action field description
     *
     * @return string Action field description.
     */
    public static function get_description()
    {
        return __('Transaction ID (Auto-generated if left empty)', 'wishlist-member');
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
}
