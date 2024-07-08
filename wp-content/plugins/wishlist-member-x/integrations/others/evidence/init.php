<?php

if (! class_exists('WLM3_Evidence_Hooks')) {
    class WLM3_Evidence_Hooks
    {
        public function __construct()
        {
            add_action('wp_ajax_wlm3_evidence_test_webhook', [$this, 'test_webhook']);
        }

        public function test_webhook()
        {
            if (! is_user_logged_in() || ! is_admin()) {
                wp_send_json_error();
            }

            do_action('wishlistmember_add_user_levels', 'wlm3-evidence-webhook-test', [wlm_post_data()['level'] ? wlm_post_data()['level'] : 'wlm3-evidence-webhook-test'], []);
                wp_send_json_success();
        }
    }
    new WLM3_Evidence_Hooks();
}
