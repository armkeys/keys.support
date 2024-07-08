<?php

/**
 * WishList Member - Thrive Automator Integration
 * Membership level name data field
 *
 * @package WishListMember/WLMTA
 */

namespace WishListMember\WLMTA\Data_Fields;

use WishListMember\WLMTA\Apps\WishList_Member_App;

class Membership_Level_ID extends \Thrive\Automator\Items\Data_Field
{
    /**
     * Return Data field ID
     *
     * @return string Data field ID.
     */
    public static function get_id()
    {
        return 'wlmta/df/membership-level-id';
    }

    /**
     * Return Data field name
     *
     * @return string Data field name.
     */
    public static function get_name()
    {
        return __('Membership Level ID', 'wishlist-member');
    }

    /**
     * Return Data field description
     *
     * @return string Data field description.
     */
    public static function get_description()
    {
        return __('Membership Level ID', 'wishlist-member');
    }

    /**
     * Return Data field placeholder
     *
     * @return string Data field placeholder.
     */
    public static function get_placeholder()
    {
        return '';
    }

    /**
     * Return Data field support filters
     *
     * @return array Array of supported filters.
     */
    public static function get_supported_filters()
    {
        return ['dropdown'];
    }

    /**
     * Return field value type
     *
     * @return true Always true.
     */
    public static function get_field_value_type()
    {
        return true;
    }

    /**
     * Support ajax for data field
     *
     * @return true Always true.
     */
    public static function is_ajax_field()
    {
        return true;
    }

    /**
     * Return data field select options
     *
     * @return array Array of select options.
     */
    public static function get_options_callback()
    {
        static $levels;
        if (is_null($levels)) {
            $levels = [];
            foreach (WishList_Member_App::get_membership_levels() as $id => $l) {
                $levels[] = [
                    'id'    => $id,
                    'label' => $id . ' (' . $l['name'] . ')',
                ];
            }
        }
        return $levels;
    }
}
