<?php

/**
 * WebHooks Integration Handler
 *
 * @package WishListMember
 */

namespace WishListMember\Integrations\Others;

/**
 * WebHooks Integration Class
 */
class WebHooks
{
    /**
     * Incoming webhooks
     *
     * @var array
     */
    private $incoming = [];

    /**
     * Outgoing webhooks
     *
     * @var array
     */
    private $outgoing = [];

    const SUCCESS                  = 1;
    const ERR_NOT_FOUND            = 1001;
    const ERR_INVALID_EMAIL        = 1002;
    const ERR_ACCOUNT_EXISTS       = 1003;
    const ERR_ALREADY_A_MEMBER     = 1004;
    const ERR_CANNOT_CREATE_MEMBER = 1005;

    /**
     * Terminate codes
     *
     * @var array
     */
    public static $terminate_codes = [
        self::SUCCESS                  => [200, ['success' => 1]],
        self::ERR_NOT_FOUND            => [404, ['error' => 'Not Found']],
        self::ERR_INVALID_EMAIL        => [400, ['error' => 'Invalid Email Address']],
        self::ERR_ACCOUNT_EXISTS       => [409, ['error' => 'Account Already Exists']],
        self::ERR_ALREADY_A_MEMBER     => [409, ['error' => 'Already a Member']],
        self::ERR_CANNOT_CREATE_MEMBER => [409, ['error' => 'Cannot Create Member']],
    ];

    /**
     * Constructor
     */
    public function __construct()
    {

        $settings       = wishlistmember_instance()->get_option('webhooks_settings');
        $this->incoming = wlm_arrval($settings, 'incoming');
        $this->outgoing = wlm_arrval($settings, 'outgoing');

        $this->add_hooks();
    }

    /**
     * Add hooks.
     */
    public function add_hooks()
    {
        // Hook to handle incoming webhooks.
        add_action('init', [$this, 'receive_webhook']);

        // Hooks when a user is added to or removed from membership levels.
        add_action('wishlistmember_user_registered', [$this, 'new_wishlist_member'], 10, 3);
        add_action('wishlistmember_add_user_levels_shutdown', [$this, 'levels_added'], 10, 2);
        add_action('wishlistmember_pre_remove_user_levels', [$this, 'levels_removed'], 10, 2);

        // Hooks when a user is added to or removed from pay per posts.
        add_action('wishlistmember_payperpost_added', [$this, 'levels_added'], 10, 2);
        add_action('wishlistmember_payperpost_removed', [$this, 'levels_removed'], 10, 2);

        // Hooks when a user is cancelled or uncancelled from membership levels.
        add_action('wishlistmember_cancel_user_levels', [$this, 'levels_cancelled'], 10, 2);
        add_action('wishlistmember_uncancel_user_levels', [$this, 'levels_uncancelled'], 10, 2);

        // Hooks when a user is expired or unexpired from membership levels.
        add_action('wishlistmember_expire_user_levels', [$this, 'levels_expired'], 10, 2);
        add_action('wishlistmember_unexpire_user_levels', [$this, 'levels_unexpire'], 10, 2);
    }

    /**
     * Remove hooks.
     */
    public function remove_hooks()
    {
        // Hook to handle incoming webhooks.
        remove_action('init', [$this, 'receive_webhook']);

        // Hooks when a user is added to or removed from membership levels.
        remove_action('wishlistmember_user_registered', [$this, 'new_wishlist_member'], 10, 3);
        remove_action('wishlistmember_add_user_levels_shutdown', [$this, 'levels_added'], 10);
        remove_action('wishlistmember_pre_remove_user_levels', [$this, 'levels_removed'], 10);

        // Hooks when a user is added to or removed from pay per posts.
        remove_action('wishlistmember_payperpost_added', [$this, 'levels_added'], 10, 2);
        remove_action('wishlistmember_payperpost_removed', [$this, 'levels_removed'], 10, 2);

        // Hooks when a user is cancelled or uncancelled from membership levels.
        remove_action('wishlistmember_cancel_user_levels', [$this, 'levels_cancelled'], 10);
        remove_action('wishlistmember_uncancel_user_levels', [$this, 'levels_uncancelled'], 10);

        // Suppress if WLM settings tells us so.
        remove_action('wishlistmember_suppress_other_integrations', [$this, 'remove_hooks']);
    }

    /**
     * Outputs http response code with json data
     * or redirects to $this->post_data[redirect] if set
     *
     * @param integer $terminate_code Terminate Code.
     * @param string  $reason         Terminate Reason.
     */
    private function terminate($terminate_code = 1, $reason = '')
    {

        if (empty(self::$terminate_codes[ $terminate_code ])) {
            $terminate_code = 1;
        }

        list( $http_response_code, $return_data) = self::$terminate_codes[ $terminate_code ];

        $return_data['code'] = $terminate_code;
        if ($reason) {
            $return_data['reason'] = $reason;
        }

        $redirect = wlm_arrval($this->post_data, 'redirect');
        if ($redirect) {
            wp_safe_redirect(add_query_arg($return_data, $redirect));
        } else {
            http_response_code($http_response_code);
            wp_send_json($return_data);
        }
        exit;
    }

    /**
     * Hook: init
     *
     * Receives and processes webhooks
     */
    public function receive_webhook()
    {

        // Query parameter wlm_webhook required.
        $hook = wlm_get_data()['wlm_webhook'];
        if (! $hook) {
            return;
        }

        // $hook must be valid.
        $data = wlm_arrval($this->incoming, $hook);
        if (! $data) {
            $this->terminate(self::ERR_NOT_FOUND);
        }

        $_post_data = wlm_post_data(true);
        $_get_data  = wlm_get_data(true);

        // $_POST required.
        if (0 === strpos(strtolower(trim(wlm_server_data()['CONTENT_TYPE'])), 'application/json')) {
            $this->post_data = json_decode(file_get_contents('php://input'), true);
        } elseif ($_post_data) {
            $this->post_data = $_post_data;
        } elseif ($_get_data && wlm_arrval($data, 'process_get_requests')) {
            $this->post_data = $_get_data;
            unset($this->post_data['wlm_webhook']);
            unset($this->post_data['wlmdebug']);
        } else {
            $this->post_data = '';
        }

        if (! $this->post_data) {
            return;
        }

        $map = array_merge(
            [
                'email'     => 'email',
                'username'  => 'username',
                'password'  => 'password',
                'firstname' => 'firstname',
                'lastname'  => 'lastname',
            ],
            (array) wlm_arrval($data, 'map')
        );

        array_walk(
            $map,
            function (&$val, $key, $post_data) {
                $key  = preg_replace('/["\'\s]/', '', wlm_or(wlm_trim($val), $key));
                $keys = preg_split('/[\[\]]/', $key);
                $val  = $post_data;
                while ($keys) {
                    $key = wlm_trim(array_shift($keys));
                    if (strlen($key)) {
                        $val = wlm_arrval($val, $key);
                    }
                }
                $val = (string) $val;
            },
            $this->post_data
        );

        $email = $map['email'];
        if (! is_email($email)) {
            $this->terminate(self::ERR_INVALID_EMAIL); // Bad request, invalid email.
        }

        unset($map['email']);
        $username = $map['username'];
        unset($map['username']);
        $password = $map['password'];
        unset($map['password']);
        $firstname = $map['firstname'];
        unset($map['firstname']);
        $lastname = $map['lastname'];
        unset($map['lastname']);

        $custom_fields = array_filter(
            $this->post_data,
            function ($key) {
                return 'custom_' === substr($key, 0, 7);
            },
            ARRAY_FILTER_USE_KEY
        );

        $actions = array_merge(
            [
                'add'      => [],
                'remove'   => [],
                'cancel'   => [],
                'uncancel' => [],
            ],
            (array) wlm_arrval($data, 'actions')
        );

        $id = get_user_by('email', $email);
        if ($id) {
            $id = $id->ID;
        }

        // Add.
        if ($actions['add'] && is_array($actions['add'])) {
            if ($id) {
                $x = wlm_arrval($this->post_data, 'new_users_only');
                if ($x) {
                    // Redirect to "new_users_only" value if it looks like a URL.
                    if (preg_match('#(http|https)://.+#i', $x)) {
                        $this->post_data['redirect'] = $x;
                    }
                    $this->terminate(self::ERR_ACCOUNT_EXISTS);
                }

                /*
                 * if the user exists and 'new_members_only' is true in the post data then
                 * we only allow registrations if there is at least one level in the webhook's
                 * 'add' configuration that the user is not yet a member of
                 */
                $x = wlm_arrval($this->post_data, 'new_members_only');
                if ($x) {
                    if (! array_diff($actions['add'], array_keys(wlmapi_get_member_levels($id)))) {
                        // Redirect to "new_members_only" value if it looks like a URL.
                        if (preg_match('#(http|https)://.+#i', $x)) {
                            $this->post_data['redirect'] = $x;
                        }
                        $this->terminate(self::ERR_ALREADY_A_MEMBER);
                    }
                }

                wlmapi_update_member(
                    $id,
                    [
                        'Levels'                       => $actions['add'],
                        'ObeyRegistrationRequirements' => 1,
                        'SendMailPerLevel'             => 1,
                    ] + $custom_fields
                );
            } else {
                $member = [
                    'user_email'                   => $email,
                    'user_login'                   => $this->generate_username($username, $email, $firstname, $lastname, wlm_or(wlm_trim(wlm_arrval($data, 'username_format')), '{email}')),
                    'user_pass'                    => wlm_or($password, [wishlistmember_instance(), 'pass_gen'], 12),
                    'first_name'                   => $firstname,
                    'last_name'                    => $lastname,
                    'Levels'                       => $actions['add'],
                    'ObeyRegistrationRequirements' => 1,
                    'SendMailPerLevel'             => 1,
                ] + $custom_fields;

                $x = wlmapi_add_member($member);
                if ($x['success']) {
                    $id = $x['member'][0]['ID'];
                } else {
                    $this->terminate(self::ERR_CANNOT_CREATE_MEMBER, $x['ERROR']);
                }
            }
        }

        if ($id) {
            // Remove.
            if ($actions['remove'] && is_array($actions['remove'])) {
                wlmapi_update_member($id, ['RemoveLevels' => $actions['remove']]);
            }

            // Cancel.
            if ($actions['cancel'] && is_array($actions['cancel'])) {
                foreach ($actions['cancel'] as $level) {
                    wlmapi_update_level_member_data(
                        $level,
                        $id,
                        [
                            'Cancelled'        => 1,
                            'SendMailPerLevel' => 1,
                        ]
                    );
                }
            }

            // Uncancel.
            if ($actions['uncancel'] && is_array($actions['uncancel'])) {
                foreach ($actions['uncancel'] as $level) {
                    wlmapi_update_level_member_data(
                        $level,
                        $id,
                        [
                            'Cancelled'        => 0,
                            'SendMailPerLevel' => 1,
                        ]
                    );
                }
            }
        }

        $this->terminate(self::SUCCESS);

        exit;
    }

    /**
     * Send outgoing webhook.
     *
     * @param integer $uid    User ID.
     * @param array   $levels Level IDs.
     * @param string  $action Trigger action.
     */
    private function send_webhook($uid, $levels, $action)
    {
        $user = new \WishListMember\User($uid, true);
        if (empty($user->ID)) {
            return;
        }
        $levels = array_values($levels);

        $data = [
            'id'             => $user->ID,
            'email'          => $user->user_info->user_email,
            'login'          => $user->user_info->user_login,
            'firstname'      => $user->user_info->first_name,
            'lastname'       => $user->user_info->last_name,
            'nicename'       => $user->user_info->user_nicename,
            'display_name'   => $user->user_info->display_name,
            'levels'         => $user->Levels,
            'pay_per_posts'  => array_diff_key((array) $user->pay_per_posts, ['_all_' => '']),
            'trigger'        => $action,
            'trigger_levels' => $levels,
        ];
        if (is_array($user->user_info->wpm_useraddress)) {
            $data += $user->user_info->wpm_useraddress;
        }
        if (is_object($user->user_info->data->wldata)) {
            $data += array_filter(
                (array) $user->user_info->data->wldata,
                function ($key) {
                    return strpos($key, 'custom_') === 0;
                },
                ARRAY_FILTER_USE_KEY
            );
        }

        foreach ((array) $levels as $level) {
            $actions = (array) wlm_arrval($this->outgoing, $level);
            $urls    = (string) wlm_arrval($actions, $action);
            if (preg_match_all('/\bhttp[s]{0,1}:\/\/[^\s]+/', $urls, $matches)) {
                foreach ($matches[0] as $url) {
                    wp_remote_post(
                        $url,
                        [
                            'body'       => $data + [
                                'trigger_level' => $level,
                                'level'         => $user->Levels[ $level ],
                            ],
                            'user-agent' => 'WishList Member/' . wishlistmember_instance()->version,
                            'timeout'    => 1,
                            'blocking'   => false,
                        ]
                    );
                }
            }
        }
    }

    /**
     * Prepend payperpost- to level IDs if $action is either wishlistmember_payperpost_added or wishlistmember_payperpost_removed
     *
     * @param  array|integer $levels Level ID(s).
     * @param  string        $action Current action. Default is value of current_action().
     * @return array
     */
    private function ppp_levels($levels, $action = null)
    {
        switch ($action ? $action : current_action()) {
            case 'wishlistmember_payperpost_added':
            case 'wishlistmember_payperpost_removed':
                $levels = array_map(
                    function ($level) {
                        return 'payperpost-' . $level;
                    },
                    (array) $levels
                );
                break;
        }
        return $levels;
    }

    /**
     * Hook: wishlistmember_user_registered
     *
     * Action called when a new member is registered to WishList Member
     *
     * @param integer $user_id    User ID.
     * @param array   $data       Data.
     * @param mixed   $merge_with Merge width ID.
     */
    public function new_wishlist_member($user_id, $data, $merge_with = '')
    {
        if (! $merge_with) {
            $this->levels_added($user_id, [$data['wpm_id']]);
        }
    }

    /**
     * Hook: wishlistmember_add_user_levels_shutdown
     *
     * @uses  WebHooks::send_webhook
     * @param integer $uid    User ID.
     * @param array   $levels Membership Level IDs.
     */
    public function levels_added($uid, $levels)
    {
        if ($this->is_temp_user($uid)) {
            return;
        }
        $this->send_webhook($uid, $this->ppp_levels($levels, current_action()), 'add');
    }

    /**
     * Hook: wishlistmember_remove_user_levels
     *
     * @uses  WebHooks::send_webhook
     * @param integer $uid    User ID.
     * @param array   $levels Membership Level IDs.
     */
    public function levels_removed($uid, $levels)
    {
        if ($this->is_temp_user($uid)) {
            return;
        }
        $this->send_webhook($uid, $this->ppp_levels($levels, current_action()), 'remove');
    }

    /**
     * Hook: wishlistmember_cancel_user_levels
     *
     * @uses  WebHooks::send_webhook
     * @param integer $uid    User ID.
     * @param array   $levels Membership Level IDs.
     */
    public function levels_cancelled($uid, $levels)
    {
        if ($this->is_temp_user($uid)) {
            return;
        }
        $this->send_webhook($uid, $levels, 'cancel');
    }

    /**
     * Hook: wishlistmember_uncancel_user_levels
     *
     * @uses  WebHooks::send_webhook
     * @param integer $uid    User ID.
     * @param array   $levels Membership Level IDs.
     */
    public function levels_uncancelled($uid, $levels)
    {
        if ($this->is_temp_user($uid)) {
            return;
        }
        $this->send_webhook($uid, $levels, 'uncancel');
    }

    /**
     * Hook: wishlistmember_expire_user_levels
     *
     * @uses  WebHooks::send_webhook
     * @param integer $uid    User ID.
     * @param array   $levels Membership Level IDs.
     */
    public function levels_expired($uid, $levels)
    {
        if ($this->is_temp_user($uid)) {
            return;
        }
        $this->send_webhook($uid, $levels, 'expire');
    }

    /**
     * Hook: wishlistmember_unexpire_user_levels
     *
     * @uses  WebHooks::send_webhook
     * @param integer $uid    User ID.
     * @param array   $levels Membership Level IDs.
     */
    public function levels_unexpire($uid, $levels)
    {
        if ($this->is_temp_user($uid)) {
            return;
        }
        $this->send_webhook($uid, $levels, 'unexpire');
    }

    /**
     * Generate username
     *
     * @param  string $username   Default username.
     * @param  string $email      Email address.
     * @param  string $first_name First Name.
     * @param  string $last_name  Last Name.
     * @param  string $format     Username format.
     * @return string             Generated username
     */
    private function generate_username($username, $email, $first_name, $last_name, $format)
    {
        $username = wlm_trim($username);
        if ($username) {
            // $format is verbatim $username if provided.
            $format = $username;
        } else {
            // Default $format is {email}.
            $format = wlm_or(wlm_trim($format), '{email}');
        }

        // If $first_name is not set, set it to $username or $email.
        if (! $first_name) {
            $first_name = wlm_or($username, $email);
        }

        return wlm_generate_username(compact('email', 'first_name', 'last_name'), $format);
    }

    /**
     * Checks whether a user is a temporary user.
     *
     * @param  integer $user_id User ID.
     * @return boolean          True if user is a temporary user, false otherwise.
     */
    private function is_temp_user($user_id)
    {
        return preg_match('/^temp_[0-9a-f]{32}$/', wlm_arrval(get_userdata($user_id), 'user_email'));
    }
}

// Initialize.
new WebHooks();
