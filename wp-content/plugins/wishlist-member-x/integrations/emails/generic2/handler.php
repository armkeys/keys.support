<?php

namespace WishListMember\Autoresponders;

class Generic2
{
    public static function user_registered($user_id, $data)
    {
        self::added_to_level($user_id, [$data['wpm_id']]);
    }

    public static function added_to_level($user_id, $level_id)
    {
        $level_id = wlm_remove_inactive_levels($user_id, $level_id);
        self::process($user_id, $level_id, 'added');
    }

    public static function removed_from_level($user_id, $level_id)
    {
        self::process($user_id, $level_id, 'removed');
    }

    public static function uncancelled_from_level($user_id, $levels)
    {
        self::process($user_id, $levels, 'uncancelled');
    }

    public static function cancelled_from_level($user_id, $levels)
    {
        self::process($user_id, $levels, 'cancelled');
    }

    public static function process($email_or_id, $levels, $action)
    {
        static $ar;

        if (empty($ar)) {
            $ar = ( new \WishListMember\Autoresponder('generic2') )->settings;
        }

        // Get email address.
        if (is_numeric($email_or_id)) {
            $userdata = get_userdata($email_or_id);
            if (! $userdata) {
                return; // Invalid user_id.
            }
        } elseif (filter_var($email_or_id, FILTER_VALIDATE_EMAIL)) {
            $userdata = get_user_by('email', $email_or_id);
            if (! $userdata) {
                return; // Invalid user_id.
            }
        } else {
            return; // Email_or_id is neither a valid ID or email address.
        }
        $email = $userdata->user_email;
        $fname = $userdata->first_name;
        $lname = $userdata->last_name;

        // Make sure email is not temp.
        if (! wlm_trim($email) || preg_match('/^temp_[0-9a-f]+/i', $email)) {
            return;
        }

        // Make sure levels is an array.
        if (! is_array($levels)) {
            $levels = [$levels];
        }

        foreach ($levels as $level_id) {
            $add    = wlm_or($ar['list_actions'][ $level_id ][ $action ]['add'], '');
            $remove = wlm_or($ar['list_actions'][ $level_id ][ $action ]['remove'], '');
            if ($add) {
                self::send_data($add, $email, $fname, $lname, true);
            }
            if ($remove) {
                self::send_data($remove, $email, $fname, $lname, false);
            }
        }
    }

    public static function send_data($post_url, $email, $fname, $lname, $subscribe)
    {

        if ($post_url) {
            $httpAgent = 'WLM_GENERIC_AGENT';
            $postData  = [
                'email_address' => $email,
                'first_name'    => $fname,
                'last_name'     => $lname,
            ];
            if ($subscribe) {
                $postData['unsubscribe'] = 0;
            } else {
                $postData['unsubscribe'] = 1;
            }

            if ($subscribe || isset($postData['unsubscribe']) && ! $subscribe) {
                wp_remote_post(
                    $post_url,
                    [
                        'blocking'   => false,
                        'user-agent' => $httpAgent,
                        'body'       => $postData,
                    ]
                );
            }
        }
    }
}
