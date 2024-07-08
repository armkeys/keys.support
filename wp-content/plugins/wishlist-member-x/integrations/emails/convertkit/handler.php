<?php

/**
 * ConvertKit handler
 *
 * @package WishListMember/Autoresponders
 */

namespace WishListMember\Autoresponders;

if (! class_exists('ConvertKit\SDK')) {
    include_once __DIR__ . '/lib/sdk.php';
}

/**
 * Integration class for ConvertKit
 */
class ConvertKit
{
    /**
     * Process incoming webhooks from ConvertKit
     */
    public static function process_webhooks()
    {
        // Slug.
        $webhook_slug = wlm_get_data()['wishlist-member-convertkit-webhook'];
        if ($webhook_slug) {
            // Json data.
            $data = json_decode(file_get_contents('php://input'));
            if ($data) {
                // User.
                $user = get_user_by('email', $data->subscriber->email_address);
                if (false === $user) {
                    return;
                }
                // Convertkit settings.
                $convertkit_settings = new \WishListMember\Autoresponder('convertkit');

                // Find tag action settings for the webhook slug.
                foreach (wlm_arrval($convertkit_settings, 'settings', 'tag_actions') as $tag_settings) {
                    foreach (['add', 'remove'] as $action) {
                        $webhook_url = wlm_arrval($tag_settings, $action, 'webhook_url');
                        if ($webhook_url) {
                            if (strpos($webhook_url, '?wishlist-member-convertkit-webhook=' . $webhook_slug)) {
                                // Url slug found, process actions.
                                self::process_tag_actions($user, $tag_settings[ $action ]);
                                break 2; // We're done.
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Register webhooks to Convertkit for tag actions when necessary
     *
     * @wp-hook wishlistmember_save_email_provider
     *
     * @param array  $post_data Original Post Data.
     * @param string $ar_id     Autoresponder ID.
     * @param array  $ar_data   Autoresponder Data.
     */
    public static function add_tag_webhooks($post_data, $ar_id, $ar_data)
    {
        static $_running = false;

        // Let's not double process.
        if ($_running) {
            return;
        }

        // Process convertkit only.
        if ('convertkit' !== $ar_id) {
            return;
        }

        if (empty(wlm_arrval($ar_data, 'tag_actions'))) {
            // Process tag actions only.
            return;
        }

        // Ok so we're running...
        $_running = true;

        $ck       = self::_interface()->cksdk();
        $_changed = false;
        foreach ((array) wlm_arrval($ar_data, 'tag_actions') as $tag_id => $settings) {
            foreach (['add', 'remove'] as $action) {
                // Get all tag action levels, merge them, and remove empty strings.
                $tag_action_levels = array_diff(
                    array_merge(
                        wlm_arrval($settings, $action, 'add_level'),
                        wlm_arrval($settings, $action, 'remove_level'),
                        wlm_arrval($settings, $action, 'cancel_level'),
                        wlm_arrval($settings, $action, 'uncancel_level'),
                        wlm_arrval($settings, $action, 'add_ppp'),
                        wlm_arrval($settings, $action, 'remove_ppp')
                    ),
                    ['']
                );

                // Check if webhook is set.
                $webhook_set = ! empty(wlm_arrval($settings, $action, 'webhook_url')) && ! empty(wlm_arrval($settings, $action, 'webhook_id'));

                // Get the current webhook error.
                $webhook_error = wlm_arrval($settings, $action, 'webhook_error');

                if (! empty($tag_action_levels) && ! $webhook_set) {
                    // Create webhook because tag action levels are set but not webhook URL or webhook ID.
                    try {
                        $_changed = true;
                        // Slug for webhook URL.
                        $slug = md5($ar_data['ckapi'] . $tag_id . $action . microtime());

                        // Generate webhook event type.
                        switch ($action) {
                            case 'add':
                                $webhook_event_type = 'subscriber.tag_add';
                                break;
                            case 'remove':
                                $webhook_event_type = 'subscriber.tag_remove';
                                break;
                            default:
                                $webhook_event_type = '';
                        }
                        if ($webhook_event_type) {
                            // Create webhook.
                            $result = $ck->create_webhook(
                                [
                                    'name'   => $webhook_event_type,
                                    'tag_id' => $tag_id,
                                ],
                                $slug
                            );
                            if (! $ck->last_error) {
                                $settings[ $action ]['webhook_url']   = wlm_arrval($result, 'rule', 'target_url');
                                $settings[ $action ]['webhook_id']    = wlm_arrval($result, 'rule', 'id');
                                $settings[ $action ]['webhook_error'] = '';
                            } else {
                                // Save the error.
                                $settings[ $action ]['webhook_url']   = '';
                                $settings[ $action ]['webhook_id']    = '';
                                $settings[ $action ]['webhook_error'] = $ck->last_error;
                            }
                            $ar_data['tag_actions'][ $tag_id ] = $settings;
                        }
                    } catch (\Exception $e) {
                        null;
                    }
                } elseif (empty($tag_action_levels) && ( $webhook_set || $webhook_error )) {
                    // Delete webhook if it's set but there are no tag level actions.
                    try {
                        $ck->delete_webhook(wlm_arrval($settings, $action, 'webhook_id'));
                        $settings[ $action ]['webhook_url']   = '';
                        $settings[ $action ]['webhook_id']    = '';
                        $settings[ $action ]['webhook_error'] = '';
                        $ar_data['tag_actions'][ $tag_id ]    = $settings;

                        $_changed = true;
                    } catch (\Exception $e) {
                        null;
                    }
                }
            }
        }

        if ($_changed) {
            $convertkit_settings           = new \WishListMember\Autoresponder('convertkit');
            $convertkit_settings->settings = $ar_data;
            $convertkit_settings->save_settings();
        }

        // End run.
        $_running = false;
    }

    /**
     * Delete tag action
     *
     * @wp-hook wp_ajax_wishlistmember_convertkit_delete_tag_action
     */
    public static function delete_tag_action()
    {
        $convertkit_settings = new \WishListMember\Autoresponder('convertkit');
        $tag_id              = wlm_post_data()['tag_id'];
        try {
            foreach (['add', 'remove'] as $action) {
                $webhook_id = wlm_arrval($convertkit_settings, 'settings', 'tag_actions', $tag_id, $action, 'webhook_id');
                if ($webhook_id) {
                    self::_interface()->cksdk()->delete_webhook($webhook_id);
                }
            }
            unset($convertkit_settings->settings['tag_actions'][ $tag_id ]);
        } catch (\Exception $e) {
            null;
        }
        $convertkit_settings->save_settings();
        wp_send_json_success();
    }

    /**
     * Process ConvertKit tag actions
     *
     * @param \WP_User $user         WP_User object.
     * @param array    $tag_settings Tag action settings.
     */
    public static function process_tag_actions($user, $tag_settings)
    {
        // Go through each tag action.
        foreach (['add_level', 'remove_level', 'cancel_level', 'uncancel_level', 'add_ppp', 'remove_ppp'] as $action) {
            $levels = array_diff($tag_settings[ $action ], ['']);
            if ($levels) {
                // Prepend "payperpost-" to level IDs if we're dealing with payperpost.
                if ('_ppp' === substr($action, -4)) {
                    array_walk(
                        $levels,
                        function (&$v) {
                            $v = 'payperpost-' . $v;
                        }
                    );
                }

                switch ($action) {
                    case 'add_ppp':
                    case 'add_level':
                        $add_args = [
                            'ObeyRegistrationRequirements' => 'add_level' === $action,
                            'Levels'                       => $levels,
                            'SendMailPerLevel'             => 1,
                        ];
                        wlmapi_update_member($user->ID, $add_args);
                        unset($add_args);
                        break;
                    case 'remove_ppp':
                    case 'remove_level':
                        wlmapi_update_member($user->ID, ['RemoveLevels' => $levels]);
                        break;
                    case 'cancel_level':
                    case 'uncancel_level':
                        foreach ($levels as $level) {
                            wlmapi_update_level_member_data($level, $user->ID, ['Cancelled' => 'cancel_level' === $action]);
                        }
                        break;
                }
            }
        }
    }


    /**
     * Triggers add to level action when user is registered
     *
     * @wp-hook wishlistmember_user_registered
     * @param   integer $user_id User ID.
     * @param   array   $data    Registration data.
     */
    public static function user_registered($user_id, $data)
    {
        self::added_to_level($user_id, [$data['wpm_id']]);
    }

    /**
     * Process ConvertKit actions when a user is added, confirmed or approved to a level
     *
     * @wp-hook wishlistmember_add_user_levels_shutdown
     * @wp-hook wishlistmember_confirm_user_levels
     * @wp-hook wishlistmember_approve_user_levels
     *
     * @param integer $user_id  User ID.
     * @param string  $level_id Membership Level ID.
     */
    public static function added_to_level($user_id, $level_id)
    {
        $level_id = wlm_remove_inactive_levels($user_id, $level_id);
        self::process_level_actions($user_id, $level_id, 'added');
    }

    /**
     * Process ConvertKit actions when a user is removed from a level
     *
     * @wp-hook wishlistmember_remove_user_levels
     *
     * @param integer $user_id  User ID.
     * @param string  $level_id Membership Level ID.
     */
    public static function removed_from_level($user_id, $level_id)
    {
        self::process_level_actions($user_id, $level_id, 'removed');
    }

    /**
     * Process ConvertKit actions when a user is uncancelled from a level
     *
     * @wp-hook wishlistmember_uncancel_user_levels
     * @param   integer $user_id User ID.
     * @param   array   $levels  Membership Level IDs.
     */
    public static function uncancelled_from_level($user_id, $levels)
    {
        self::process_level_actions($user_id, $levels, 'uncancelled');
    }

    /**
     * Process ConvertKit actions when a user is cancelled from a level
     *
     * @wp-hook wishlistmember_cancel_user_levels
     * @param   integer $user_id User ID.
     * @param   array   $levels  Membership Level IDs.
     */
    public static function cancelled_from_level($user_id, $levels)
    {
        self::process_level_actions($user_id, $levels, 'cancelled');
    }

    /**
     * Process level actions
     *
     * @param string|integer $email_or_id Email address or User ID.
     * @param string|array   $levels      Membership Level ID or array of Membership Level IDs.
     * @param string         $action      Action to process.
     */
    public static function process_level_actions($email_or_id, $levels, $action)
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

        if (! $interface) {
            $interface = new ConvertKit_Interface();
        }

        foreach ($levels as $level_id) {
            $interface->process($email, $fname, $lname, $level_id, $action);
        }
    }

    /**
     * Create and return ConvertKit API Interface
     *
     * @return object ConvertKit_Interface
     */
    public static function _interface()
    {
        static $interface;
        if (! $interface) {
            $interface = new ConvertKit_Interface();
        }
        return $interface;
    }
}

/**
 * ConvertKit API Interface class
 */
class ConvertKit_Interface
{
    /**
     * API Secret
     *
     * @var string
     */

    private $api_secret = '';
    /**
     * Convertkit AR settings
     *
     * @var array
     */
    private $ar;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->ar         = ( new \WishListMember\Autoresponder('convertkit') )->settings;
        $this->api_secret = $this->ar['ckapi'];
    }

    /**
     * Initialize and return the Convertkit SDK object
     *
     * @return \ConvertKit\SDK
     */
    public function cksdk()
    {
        static $cksdk;
        if (! $cksdk) {
            $cksdk = new ConvertKit\SDK($this->api_secret);
        }
        return $cksdk;
    }

    /**
     * Subscribes or unsubscribes a user to or from a list
     *
     * @param string $email    Email address.
     * @param string $fname    First name.
     * @param string $lname    Last name.
     * @param string $level_id Membership Level ID.
     * @param string $action   Level Action.
     */
    public function process($email, $fname, $lname, $level_id, $action)
    {
        // Subscribe to form.
        $x = wlm_or($this->ar['list_actions'][ $level_id ][ $action ]['add'], []);
        if ($x) {
            $this->subscribe($x, $email, $fname, $lname);
        }

        // Unsubscribe from form.
        if (wlm_or($this->ar['list_actions'][ $level_id ][ $action ]['remove'], [])) {
            $this->unsubscribe($email);
        }

        // Add tags.
        $x = wlm_or($this->ar['level_tag_actions'][ $level_id ][ $action ]['add'], []);
        if ($x) {
            $this->cksdk()->add_tags(
                $x,
                $email,
                [
                    'first_name' => $fname,
                    'fields'     => ['last_name' => $lname],
                ]
            );
        }

        // Remove tags.
        $x = wlm_or($this->ar['level_tag_actions'][ $level_id ][ $action ]['remove'], []);
        if ($x) {
            $this->cksdk()->remove_tags($x, $email);
        }
    }

    /**
     * Subscribes user to a list
     *
     * @param  string $formid Form ID.
     * @param  string $email  Email address.
     * @param  string $fname  First name.
     * @param  string $lname  Last name.
     * @return true|string    True on success of error message on failure
     */
    public function subscribe($formid, $email, $fname, $lname)
    {
        $ck = $this->cksdk();
        if ($ck->last_error) {
            return $ck->last_error;
        }
        $args = [
            'email'      => $email,
            'first_name' => $fname,
            'fields'     => ['last_name' => $lname],
        ];

        // If the $formid is in array (saw these in some cases from clients) then let's get the first value.
        if (is_array($formid)) {
            $formid = $formid[0];
        }

        $f = $ck->form_subscribe($formid, $args);
        if (! $f) {
            return $ck->last_error;
        }
        return true;
    }

    /**
     * Unsubscribes email
     *
     * @param  string $email Email address.
     * @return true|string   True on success, error message on failure
     */
    public function unsubscribe($email)
    {
        $ck = $this->cksdk();
        if ($ck->last_error) {
            return $ck->last_error;
        }
        $f = $ck->form_unsubscribe($email);
        if (! $f) {
            return $ck->last_error;
        }
        return true;
    }
    // End of Functions.
}
