<?php

namespace WishListMember\Autoresponders;

class MailerLite
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
            return;
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
            $interface = new MailerLite_Interface();
        }

        foreach ($levels as $level_id) {
            $interface->process($userdata, $level_id, $action);
        }
    }
}

class MailerLite_Interface
{
    private $ar;

    public function __construct()
    {
        $this->ar = ( new \WishListMember\Autoresponder('mailerlite') )->settings;
    }

    public function process($userdata, $level_id, $action)
    {
        $add    = wlm_or($this->ar['list_actions'][ $level_id ][ $action ]['add'], []);
        $remove = wlm_or($this->ar['list_actions'][ $level_id ][ $action ]['remove'], []);

        if ($add) {
            $this->subscribe($add, $userdata->user_email, $userdata->first_name, $userdata->last_name);
        }
        if ($remove) {
            $this->unsubscribe($remove, $userdata->user_email);
        }
    }

    private function subscribe($lists, $email, $first_name, $last_name)
    {
        if (! is_array($lists)) {
            $lists = [$lists];
        }
        foreach ($lists as $list) {
            // Check if it's the new API key version, if so let's do a different.
            // API Call that supports it.
            if (strlen($this->ar['api_key']) > 32) {
                $this->api_request(
                    'subscribers/',
                    [
                        'email'  => $email,
                        'name'   => $first_name,
                        'fields' => [
                            'last_name' => $last_name,
                        ],
                        'groups' => [$list],
                    ],
                    'POST'
                );
            } else {
                $this->api_request(
                    sprintf('groups/%s/subscribers', $list),
                    [
                        'email'  => $email,
                        'name'   => $first_name,
                        'fields' => [
                            'last_name' => $last_name,
                        ],
                    ],
                    'POST'
                );
            }
        }
    }

    private function unsubscribe($lists, $email)
    {
        if (! is_array($lists)) {
            $lists = [$lists];
        }
        foreach ($lists as $list) {
            $this->api_request(
                sprintf('groups/%s/subscribers/%s', $list, $email),
                [],
                'DELETE'
            );
        }
    }

    private function api_request($endpoint, $data = [], $method = 'GET')
    {
        $base = 'https://api.mailerlite.com/api/v2/';
        $url  = $base . $endpoint;
        switch ($method) {
            case 'POST':
                $result = wp_remote_post(
                    $url,
                    [
                        'body'       => json_encode($data),
                        'headers'    => [
                            'X-MailerLite-ApiKey' => $this->ar['api_key'],
                            'Content-Type'        => 'application/json',
                            'Accept'              => 'application/json',
                        ],
                        'user-agent' => 'WishList Member/' . wishlistmember_instance()->version,
                        'blocking'   => false,
                    ]
                );
                break;
            case 'DELETE':
                $result = wp_remote_request(
                    $url,
                    [
                        'headers'    => [
                            'X-MailerLite-ApiKey' => $this->ar['api_key'],
                        ],
                        'user-agent' => 'WishList Member/' . wishlistmember_instance()->version,
                        'method'     => 'DELETE',
                        'blocking'   => false,
                    ]
                );
                break;
            default:
                $result = wp_remote_get(
                    $url,
                    [
                        'headers'    => [
                            'X-MailerLite-ApiKey' => $this->ar['api_key'],
                        ],
                        'user-agent' => 'WishList Member/' . wishlistmember_instance()->version,
                    ]
                );
        }
        return $result;
    }
}
