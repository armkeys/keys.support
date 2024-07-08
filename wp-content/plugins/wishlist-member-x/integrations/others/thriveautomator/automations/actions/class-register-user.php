<?php

/**
 * WishList Member - Thrive Automator Integration
 * Register a new user
 *
 * @package WishListMember/WLMTA
 */

namespace WishListMember\WLMTA\Actions;

use Thrive\Automator\Items\User_Data;
use Thrive\Automator\Items\Action_Field;
use WishListMember\WLMTA\Action_Fields;

/**
 * Register a new user action class
 */
class Register_User extends \Thrive\Automator\Items\Action
{
    use Level_Actions_Trait;

    /**
     * Return action ID
     *
     * @return string Action ID.
     */
    public static function get_id()
    {
        return 'wlmta/a/register-user';
    }

    /**
     * Return action name
     *
     * @return string Action name.
     */
    public static function get_name()
    {
        return __('Register a user', 'wishlist-member');
    }

    /**
     * Return action description
     *
     * @return string Action description.
     */
    public static function get_description()
    {
        return __('Registers a user to WishList Member', 'wishlist-member');
    }

    /**
     * Return action required fields
     *
     * @return array Array of action field IDs
     */
    public static function get_required_action_fields()
    {
        return [
            Action_Fields\User_Login::get_id(),
            Action_Fields\User_Email::get_id(),
            Action_Fields\User_Password::get_id(),
            Action_Fields\Membership_Level::get_id(),
            Action_Fields\Transaction_ID::get_id(),
        ];
    }

    public function do_action($data)
    {
        $level_id   = $this->get_automation_data(Action_Fields\Membership_Level::get_id())['value'] ?? null;
        $user_login = $this->get_automation_data(Action_Fields\User_Login::get_id())['value'] ?? null;
        $user_email = $this->get_automation_data(Action_Fields\User_Email::get_id())['value'] ?? null;
        if (! $level_id || ! $user_login || ! $user_email) {
            return;
        }
        $args = [
            'user_login'    => $user_login,
            'user_email'    => $user_email,
            'user_password' => $this->get_automation_data(Action_Fields\User_Password::get_id())['value'] ?: wlm_generate_password(),
            'Sequential'    => true,
            'Levels'        => [
                [
                    $level_id,
                    $this->get_automation_data(Action_Fields\Transaction_ID::get_id())['value'] ?? '',
                ],
            ],
        ];
        wlmapi_add_member($args);
    }
}
