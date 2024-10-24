<?php

namespace WishListMember\Autoresponders;

class SendFox
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
            $interface = new SendFox_Interface();
        }

        foreach ($levels as $level_id) {
            $interface->process($userdata, $level_id, $action);
        }
    }
}

class SendFox_Interface
{
    private $ar;

    public function __construct()
    {
        $this->ar = ( new \WishListMember\Autoresponder('sendfox') )->settings;
    }

    public function process($userdata, $level_id, $action)
    {
        $add    = wlm_or($this->ar['list_actions'][ $level_id ][ $action ]['add'], []);
        $remove = wlm_or($this->ar['list_actions'][ $level_id ][ $action ]['remove'], []);

        if ($add) {
            $this->subscribe($add, $userdata->user_email, wlm_trim($userdata->first_name), wlm_trim($userdata->last_name));
        }
        if ($remove) {
            $this->unsubscribe($remove, $userdata->user_email);
        }
    }

    private function subscribe($lists, $email, $fname, $lname)
    {
        if (! is_array($lists)) {
            $lists = [$lists];
        }
        $this->api_request(
            'contacts',
            [
                'first_name' => $fname,
                'last_name'  => $lname,
                'email'      => $email,
                'lists'      => $lists,
            ],
            'POST'
        );
    }

    private function unsubscribe($lists, $email)
    {
        if (! is_array($lists)) {
            $lists = [$lists];
        }

        $user = $this->api_request(sprintf('contacts?email=%s', urlencode($email)), [], 'GET', true);
        if (is_wp_error($user)) {
            return;
        }
        $user = json_decode($user['body']);

        if ($user && ! empty($user->data)) {
            foreach ($lists as $list) {
                $this->api_request(
                    sprintf('lists/%s/contacts/%s', $list, $user->data[0]->id),
                    [],
                    'DELETE'
                );
            }
        }
    }

    private function api_request($endpoint, $data = [], $method = 'GET', $return_data = false)
    {
        $method = strtoupper($method);
        if (! in_array($method, ['GET', 'POST', 'DELETE', 'PUT'])) {
            $method = 'GET';
        }

        $params = [
            'body'       => $data,
            'headers'    => [
                'Authorization' => 'Bearer ' . $this->ar['personal_access_token'],
            ],
            'user-agent' => 'WishList Member/' . wishlistmember_instance()->version,
            'method'     => $method,
            'blocking'   => (bool) $return_data,
        ];

        return wp_remote_request(
            'https://api.sendfox.com/' . $endpoint,
            $params
        );
    }
}
