<?php

/**
 * WishList Member - Thrive Automator Integration
 * Level action methods
 *
 * @package WishListMember/WLMTA
 */

namespace WishListMember\WLMTA\Actions;

use WishListMember\WLMTA\Apps\WishList_Member_App;
use Thrive\Automator\Items\User_Data;
use WishListMember\WLMTA\Data_Objects\Membership_Level;
use WishListMember\WLMTA\Action_Fields;

/**
 * Level action trait
 */
trait Level_Actions_Trait
{
    /**
     * Return app ID
     *
     * @return string App ID.
     */
    public static function get_app_id()
    {
        return WishList_Member_App::get_id();
    }

    /**
     * Return app name
     *
     * @return string App Name.
     */
    public static function get_app_name()
    {
        return WishList_Member_App::get_name();
    }

    /**
     * Action image
     *
     * @return string URL to action image.
     */
    public static function get_image()
    {
        return WishList_Member_App::get_logo();
    }

    /**
     * Return required objects
     *
     * @return array Array of object IDs.
     */
    public static function get_required_data_objects()
    {
        switch (self::get_id()) {
            case 'wlmta/a/register-user':
                return [];
                break;
            default:
                return [User_Data::get_id()];
        }
    }

    /**
     * Executes requested level actions.
     *
     * @param \Thrive\Automator\Items\Automation_Data Automation data object.
     */
    private function do_level_action($data)
    {
        // Get user data.
        $user_data = $data->get(User_Data::get_id()) ?? null;

        // Get level id.
        $level_id = $this->get_automation_data(Action_Fields\Membership_Level::get_id())['value'] ?? null;

        // Check required fields.
        if (! $level_id || ! $user_data) {
            return;
        }

        // Process action based on action ID.
        switch (self::get_id()) {
            // Add member to level action.
            case 'wlmta/a/add-member-to-level':
                $txn_id = $this->get_automation_data(Action_Fields\Transaction_ID::get_id())['value'] ?? '';
                $txn_id = array_values((array) $txn_id)[0];
                $args   = [
                    'Users' => $user_data->user_id,
                ];
                if ($txn_id) {
                    $args['TxnID'] = $txn_id;
                }
                wlmapi_add_member_to_level(
                    $level_id,
                    $args
                );
                break;

            // Remove member from level action.
            case 'wlmta/a/remove-member-from-level':
                wlmapi_remove_member_from_level($level_id, $user_data->user_id);
                break;

            // Cancel member from level action.
            case 'wlmta/a/cancel-member-from-level':
                wlmapi_update_level_member_data($level_id, $user_data->user_id, ['Cancelled' => '1']);
                break;

            // Uncancel member from level action.
            case 'wlmta/a/uncancel-member-from-level':
                wlmapi_update_level_member_data($level_id, $user_data->user_id, ['Cancelled' => '0']);
                break;
        }
    }
}
