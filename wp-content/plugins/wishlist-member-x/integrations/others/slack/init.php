<?php

if (! class_exists('WLM3_Slack_Hooks')) {
    class WLM3_Slack_Hooks
    {
        public function __construct()
        {
            add_action('wp_ajax_wlm3_slack_test_webhook', [$this, 'test_webhook']);
        }

        public function test_webhook()
        {
            if (! is_user_logged_in() || ! is_admin()) {
                wp_send_json_error();
            }

            do_action('wishlistmember_slack_test_webhook', 'wlm3-slack-webhook-test', [wlm_post_data()['level'] ? wlm_post_data()['level'] : 'wlm3-slack-webhook-test'], wlm_post_data()['trigger']);
                wp_send_json_success();
        }
    }
    new WLM3_Slack_Hooks();
}
