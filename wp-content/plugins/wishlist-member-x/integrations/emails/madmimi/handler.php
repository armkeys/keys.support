<?php

namespace WishListMember\Autoresponders;

if (! class_exists('\WPMadMimi')) {
    require_once wishlistmember_instance()->plugin_dir . '/extlib/madmimi/madmimi.php';
}

class MadMimi
{
    public static function user_registered($user_id, $data)
    {
        self::added_to_level($user_id, [$data['wpm_id']]);
    }

    public static function added_to_level($user_id, $level_id)
    {
        $level_id = wlm_remove_inactive_levels($user_id, $level_id);
        self::pre_process($user_id, $level_id, 'added');
    }

    public static function removed_from_level($user_id, $level_id)
    {
        self::pre_process($user_id, $level_id, 'removed');
    }

    public static function uncancelled_from_level($user_id, $levels)
    {
        self::pre_process($user_id, $levels, 'uncancelled');
    }

    public static function cancelled_from_level($user_id, $levels)
    {
        self::pre_process($user_id, $levels, 'cancelled');
    }

    public static function pre_process($email_or_id, $levels, $action)
    {
        // Get email address.
        if (is_numeric($email_or_id)) {
            $userdata = get_userdata($email_or_id);
        } elseif (filter_var($email_or_id, FILTER_VALIDATE_EMAIL)) {
            $userdata = get_user_by('email', $email_or_id);
        } else {
            return; // Email_or_id is neither a valid ID or email address.
        }
        if (! $userdata) {
            return; // Invalid user_id.
        }

        // Make sure email is not temp.
        if (! wlm_trim($userdata->user_email) || preg_match('/^temp_[0-9a-f]+/i', $userdata->user_email)) {
            return;
        }

        // Make sure levels is an array.
        if (! is_array($levels)) {
            $levels = [$levels];
        }

        foreach ($levels as $level_id) {
            self::process($userdata, $level_id, $action);
        }
    }

    public static function process($userdata, $level_id, $action)
    {
        static $ar;
        if (! $ar) {
            $ar = ( new \WishListMember\Autoresponder('madmimi') )->settings;
        }

        $add    = wlm_or($ar['list_actions'][ $level_id ][ $action ]['add'], []);
        $remove = wlm_or($ar['list_actions'][ $level_id ][ $action ]['remove'], []);

        $username = $ar['username'];
        $api_key  = $ar['api_key'];

        $mmm = new \WPMadmimi($username, $api_key);

        try {
            if ($remove) {
                $mmm->remove_from_lists($remove, $userdata->user_email);
            }
            if ($add) {
                $mmm->add_to_lists($add, $userdata->user_email, $userdata->first_name, $userdata->last_name);
            }
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }
    }
}
