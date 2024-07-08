<?php

/**
 * WishList Member - Thrive Automator Integration
 * Delete a user
 *
 * @package WishListMember/WLMTA
 */

namespace WishListMember\WLMTA\Actions;

use Thrive\Automator\Items\User_Data;
use Thrive\Automator\Items\Action_Field;
use WishListMember\WLMTA\Action_Fields;

/**
 * Delete a user action class
 */
class Delete_User extends \Thrive\Automator\Items\Action
{
    use Level_Actions_Trait;

    /**
     * Return action ID
     *
     * @return string Action ID.
     */
    public static function get_id()
    {
        return 'wlmta/a/delete-user';
    }

    /**
     * Return action name
     *
     * @return string Action name.
     */
    public static function get_name()
    {
        return __('Delete a user', 'wishlist-member');
    }

    /**
     * Return action description
     *
     * @return string Action description.
     */
    public static function get_description()
    {
        return __('Deletes a user from WordPress', 'wishlist-member');
    }

    /**
     * Return action required fields
     *
     * @return array Array of action field IDs
     */
    public static function get_required_action_fields()
    {
        return [
            Action_Fields\User_Email::get_id(),
        ];
    }

    public function do_action($data)
    {
        $user_email = $this->get_automation_data(Action_Fields\User_Email::get_id())['value'] ?? null;
        if (! $user_email) {
            return;
        }
        $user = get_user_by('email', $user_email);
        if (! $user) {
            return;
        }

        if (! function_exists('wp_delete_user')) {
            require_once ABSPATH . '/wp-admin/includes/user.php';
        }

        wp_delete_user($user->ID);
    }
}
