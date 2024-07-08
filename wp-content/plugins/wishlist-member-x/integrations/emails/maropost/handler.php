<?php

namespace WishListMember\Autoresponders;

if (! class_exists('\WishListMember_Maropost_API')) {
    require_once __DIR__ . '/api.php';
}

class Maropost
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
        static $interface;

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

        if (! $interface) {
            $interface = new Maropost_Interface();
        }
        foreach ($levels as $level_id) {
            $interface->process($userdata, $level_id, $action);
        }
    }
}

class Maropost_Interface
{
    private $api;
    private $settings;
    public function __construct()
    {
        $this->settings = ( new \WishListMember\Autoresponder('maropost') )->settings;

        $this->api = new \WishListMember_Maropost_API($this->settings['account_id'], $this->settings['auth_token']);
    }

    public function process($userdata, $level_id, $action)
    {
        $add    = wlm_or($this->settings['list_actions'][ $level_id ][ $action ]['add'], []);
        $remove = wlm_or($this->settings['list_actions'][ $level_id ][ $action ]['remove'], []);
        try {
            if (! empty($remove)) {
                $this->remove_from_lists($remove, $userdata->user_email);
            }
            if (! empty($add)) {
                $this->add_to_lists(
                    $add,
                    [
                        'first_name' => $userdata->first_name,
                        'last_name'  => $userdata->last_name,
                        'email'      => $userdata->user_email,
                    ]
                );
            }
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }
    }

    private function add_to_lists($lists, $user)
    {
        $wp_user = get_user_by('email', $user['email']);
        foreach ($lists as $list) {
            $obj = $this->api->add_to_list($list, $user);
            if (! empty($obj)) {
                wishlistmember_instance()->Update_UserMeta($wp_user->ID, 'maropost-' . $list, $obj->id);
            }
        }
    }

    private function remove_from_lists($lists, $user)
    {
        $wp_user = get_user_by('email', $user);
        foreach ($lists as $list) {
            $contact_id = wishlistmember_instance()->Get_UserMeta($wp_user->ID, 'maropost-' . $list);
            if (! empty($contact_id)) {
                $this->api->remove_from_list($list, $contact_id);
            }
        }
    }
}
