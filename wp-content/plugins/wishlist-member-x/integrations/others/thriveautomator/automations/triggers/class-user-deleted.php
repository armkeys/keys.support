<?php

/**
 * WishList Member - Thrive Automator Integration
 * User deleted trigger
 *
 * @package WishListMember/WLMTA
 */

namespace WishListMember\WLMTA\Triggers;

use Thrive\Automator\Items\Trigger;
use Thrive\Automator\Items\User_Data;
use WishListMember\WLMTA\Data_Objects;

/**
 * User deleted trigger class
 */
class User_Deleted extends Trigger
{
    use Triggers_Trait;

    /**
     * Return Trigger ID
     *
     * @return string Trigger ID.
     */
    public static function get_id()
    {
        return 'wlmta/t/user-deleted';
    }

    /**
     * Return Trigger hook
     *
     * @return string Trigger hook.
     */
    public static function get_wp_hook()
    {
        return 'wlmta_delete_user';
    }

    /**
     * Return Trigger name
     *
     * @return string Trigger name.
     */
    public static function get_name()
    {
        return __('User deleted', 'wishlist-member');
    }

    /**
     * Return Trigger description
     *
     * @return string Triger description.
     */
    public static function get_description()
    {
        return __('When a user is deleted', 'wishlist-member');
    }

    private static function _get_provided_data_objects()
    {
        return [
            User_Data::get_id(),
            Data_Objects\User_Levels::get_id(),
        ];
    }

    public function process_params($params = [])
    {
        return [
            User_Data::get_id()                => new User_Data($params[0], $this->get_automation_id),
            Data_Objects\User_Levels::get_id() => new Data_Objects\User_Levels($params[0], $this->get_automation_id),
        ];
    }
}
