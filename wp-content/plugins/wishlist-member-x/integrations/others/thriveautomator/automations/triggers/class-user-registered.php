<?php

/**
 * WishList Member - Thrive Automator Integration
 * User Registered trigger
 *
 * @package WishListMember/WLMTA
 */

namespace WishListMember\WLMTA\Triggers;

use Thrive\Automator\Items\Trigger;
use Thrive\Automator\Items\User_Data;
use WishListMember\WLMTA\Data_Objects;

/**
 * User Registered trigger class
 */
class User_Registered extends Trigger
{
    use Triggers_Trait;

    /**
     * Return Trigger ID
     *
     * @return string Trigger ID.
     */
    public static function get_id()
    {
        return 'wlmta/t/user-registered';
    }

    /**
     * Return Trigger hook
     *
     * @return string Trigger hook.
     */
    public static function get_wp_hook()
    {
        return 'wlmta_wlm_user_register';
    }

    /**
     * Return Trigger name
     *
     * @return string Trigger name.
     */
    public static function get_name()
    {
        return __('User created', 'wishlist-member');
    }

    /**
     * Return Trigger description
     *
     * @return string Triger description.
     */
    public static function get_description()
    {
        return __('When a user is created', 'wishlist-member');
    }

    private static function _get_provided_data_objects()
    {
        return [
            User_Data::get_id(),
            Data_Objects\Registration_Data::get_id(),
            Data_Objects\User_Levels::get_id(),
        ];
    }

    public function process_params($params = [])
    {
        return [
            User_Data::get_id()                      => new User_Data($params[0], $this->get_automation_id),
            Data_Objects\Registration_Data::get_id() => new Data_Objects\Registration_Data($params[1], $this->get_automation_id),
            Data_Objects\User_Levels::get_id()       => new Data_Objects\User_Levels($params[0], $this->get_automation_id),
        ];
    }
}
