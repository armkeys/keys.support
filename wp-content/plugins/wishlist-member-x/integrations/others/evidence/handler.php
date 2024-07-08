<?php

/**
 * Handler for Evidence integration
 * Author: Mike Lopez <mike@wishlistproducts.com>
 */

if (! class_exists('WLM_OTHER_INTEGRATION_EVIDENCE')) {
    class WLM_OTHER_INTEGRATION_EVIDENCE
    {
        public $levels = [];
        public $config = [];

        public function __construct()
        {
            // Load membership levels.
            $this->levels = wishlistmember_instance()->get_option('wpm_levels');
            // Load evidence settings.
            $this->config = wishlistmember_instance()->get_option('evidence_settings');

            // Hooks.
            add_action('wishlistmember_add_user_levels_shutdown', [$this, 'add_to_level'], 10, 3);
            add_action('wishlistmember_user_registered', [$this, 'new_wishlist_member'], 10, 3);
        }

        public function new_wishlist_member($user_id, $data = [], $merge_with = '')
        {
            if (! $merge_with) {
                $this->add_to_level($user_id, [$data['wpm_id']], []);
            }
        }

        /**
         * Called by `wishlistmember_add_user_levels_shutdown` hook
         */
        public function add_to_level($user_id, $new_levels, $removed_levels)
        {

            // Go through each new level.
            foreach ($new_levels as $level) {
                $webhook_url = $this->config['webhook_url'];

                $test = false;
                if ('wlm3-evidence-webhook-test' == $user_id) {
                    $user_id = get_current_user_id();
                    $test    = true;
                }
                // Skip if the level is not active in Evidence.
                if (empty($this->config['active'][ $level ]) && ! $test) {
                    continue;
                }

                // Initialize data only if empty.
                if (empty($data)) {
                    $user            = get_userdata($user_id);
                    $data            = [
                        'first_name' => $user->first_name,
                        'last_name'  => $user->last_name,
                        'email'      => $user->user_email,
                    ];
                    $address         = wishlistmember_instance()->Get_UserMeta($user_id, 'wpm_useraddress');
                    $data['city']    = (string) wlm_arrval($address, 'city');
                    $data['state']   = (string) wlm_arrval($address, 'state');
                    $data['zip']     = (string) wlm_arrval($address, 'zip');
                    $data['country'] = (string) wlm_arrval($address, 'country');
                }

                // Do not process temp emails.
                if (preg_match('/^temp_[0-9a-f]{32}$/', $data['email'])) {
                    break;
                }

                if ($test && 'wlm3-evidence-webhook-test' == $level) {
                    // Test data.
                    // Level name.
                    $data['level_name'] = 'Webhook Test Level';
                    // Add configured custom texts to data.
                    $data['custom_text_1'] = 'Custom Text #1';
                    $data['custom_text_2'] = 'Custom Text #2';
                } else {
                    // Add level name to data.
                    $data['level_name'] = (string) $this->levels[ $level ]['name'];
                    // Add configured custom texts to data.
                    $data['custom_text_1'] = (string) $this->config['custom_text_1'][ $level ];
                    $data['custom_text_2'] = (string) $this->config['custom_text_2'][ $level ];

                    if (! empty($this->config['custom_webhook_enabled'][ $level ]) && wlm_trim($this->config['custom_webhook_url'][ $level ])) {
                        $webhook_url = $this->config['custom_webhook_url'][ $level ];
                    }
                }
                // Convert data to json.
                $body = wp_json_encode($data);

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
    new WLM_OTHER_INTEGRATION_EVIDENCE();
}
