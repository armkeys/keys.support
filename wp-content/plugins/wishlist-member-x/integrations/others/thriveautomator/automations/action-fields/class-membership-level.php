<?php

/**
 * WishList Member - Thrive Automator Integration
 * Membership level action field
 *
 * @package WishListMember/WLMTA
 */

namespace WishListMember\WLMTA\Action_Fields;

use WishListMember\WLMTA\Apps\WishList_Member_App;
use Thrive\Automator\Utils;

/**
 * Membership level action field class
 */
class Membership_Level extends \Thrive\Automator\Items\Action_Field
{
    /**
     * Return action field ID
     *
     * @return string Action field ID.
     */
    public static function get_id()
    {
        return 'wlmta/af/membership-level';
    }

    /**
     * Return action field name
     *
     * @return string Action field name.
     */
    public static function get_name()
    {
        return __('Membership Level', 'wishlist-member');
    }

    /**
     * Return action field description
     *
     * @return string Action field description.
     */
    public static function get_description()
    {
        return __('Membership Level', 'wishlist-member');
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
            return Utils::FIELD_TYPE_SELECT;
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
        return [static::REQUIRED_VALIDATION];
    }

    /**
     * Return options for select.
     *
     * @param  string $action_id   Action ID.
     * @param  array  $action_data Action Data.
     * @return array Associative array of select options.
     */
    public static function get_options_callback($action_id, $action_data)
    {
        static $options;
        if (is_null($options)) {
            $options = [];
            foreach (WishList_Member_App::get_membership_levels() as $id => $l) {
                $options[] = [
                    'id'    => $id,
                    'label' => $l['name'],
                ];
            }
        }
        return $options;
    }
}
