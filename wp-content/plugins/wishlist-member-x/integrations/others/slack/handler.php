<?php

/**
 * Handler for Slack integration
 * Author: Mike Lopez <mike@wishlistproducts.com>
 */

if (! class_exists('WLM_OTHER_INTEGRATION_SLACK')) {
    class WLM_OTHER_INTEGRATION_SLACK
    {
        public $levels           = [];
        public $config           = [];
        public $default_messages = [
            'added'     => '{name} added to {level} at {sitename}',
            'removed'   => '{name} removed from {level} at {sitename}',
            'cancelled' => '{name} cancelled from {level} at {sitename}',
        ];

        public function __construct()
        {
            // Load membership levels.
            $this->levels = wishlistmember_instance()->get_option('wpm_levels');
            // Load slack settings.
            $this->config = wishlistmember_instance()->get_option('slack_settings');

            // Hooks.
            add_action('wishlistmember_add_user_levels_shutdown', [$this, 'add_levels'], 10, 3);
            add_action('wishlistmember_remove_user_levels', [$this, 'remove_levels'], 10, 3);
            add_action('wishlistmember_cancel_user_levels', [$this, 'cancel_levels'], 10, 3);
            add_action('wishlistmember_slack_test_webhook', [$this, 'send_webhook'], 10, 3);
            add_action('wishlistmember_user_registered', [$this, 'new_wishlist_member'], 10, 3);
        }

        /**
         * Action: wishlistmember_user_registered
         *
         * @param integer $user_id
         * @param array   $data
         * @param integer $merge_with
         */
        public function new_wishlist_member($user_id, $data = [], $merge_with = '')
        {
            if (! $merge_with) {
                $this->add_levels($user_id, [$data['wpm_id']]);
            }
        }

        public function add_levels($user_id, $levels)
        {
            // If there's a flag that a notification has already been sent, return.
            if (true === wlm_post_data()['slack_notification_sent']) {
                return;
            }
            // Set the flag that a notification has been sent.
            wlm_post_data()['slack_notification_sent'] = true;
            $this->send_webhook($user_id, $levels, 'added');
        }
        public function remove_levels($user_id, $levels)
        {
            $this->send_webhook($user_id, $levels, 'removed');
        }
        public function cancel_levels($user_id, $levels)
        {
            $this->send_webhook($user_id, $levels, 'cancelled');
        }

        /**
         * Called by `wishlistmember_add_user_levels_shutdown` hook
         */
        public function send_webhook($user_id, $levels, $trigger)
        {
            // Go through each new level.
            foreach ($levels as $level) {
                $webhook_url = $this->config['webhook_url'];

                $test = false;
                if ('wlm3-slack-webhook-test' == $user_id) {
                    $user_id = get_current_user_id();
                    $test    = true;
                }
                // Skip if the level is not active in Slack.
                if (empty($this->config[ $trigger ]['active'][ $level ]) && ! $test) {
                    continue;
                }

                // Initialize data only if empty.
                if (empty($data)) {
                    $user     = get_userdata($user_id);
                    $data     = [
                        '{fname}'    => $user->first_name,
                        '{lname}'    => $user->last_name,
                        '{name}'     => wlm_trim($user->first_name . ' ' . $user->last_name),
                        '{email}'    => $user->user_email,
                        '{sitename}' => get_bloginfo('name'),
                        '{siteurl}'  => get_bloginfo('url'),
                    ];
                    $username = wlm_or(wlm_trim($this->config['username']), 'WishList Member');
                }

                // Do not process temp emails.
                if (preg_match('/^temp_[0-9a-f]{32}$/', $data['{email}'])) {
                    break;
                }

                $text    = '';
                $channel = '';
                if ($test && 'wlm3-slack-webhook-test' == $level) {
                    // Test message.
                    $text = 'Test Message';
                } else {
                    // Add level name to data.
                    $data['{level}'] = (string) $this->levels[ $level ]['name'];
                    // Add configured custom texts to data.
                    $text = wlm_or(wlm_trim($this->config[ $trigger ]['text'][ $level ]), $this->default_messages[ $trigger ]);

                    if (! empty($this->config[ $trigger ]['custom_channel_enabled'][ $level ]) && wlm_trim($this->config[ $trigger ]['custom_channel'][ $level ])) {
                        $channel = $this->config[ $trigger ]['custom_channel'][ $level ];
                    }
                }

                $body['text'] = str_replace(array_keys($data), $data, $text);

                if ($channel) {
                    $body['channel'] = $channel;
                }
                if ($username) {
                    $body['username'] = $username;
                }

                // Convert data to json.
                $body = wp_json_encode($body);

                // Prepare post options.
                $options = [
                    'body'        => $body,
                    'headers'     => ['Content-Type' => 'application/json'],
                    'blocking'    => false,
                    'data_format' => 'body',
                ];

                // Send post.
                wp_remote_post($webhook_url, $options);
            }
        }
    }
    new WLM_OTHER_INTEGRATION_SLACK();
}
