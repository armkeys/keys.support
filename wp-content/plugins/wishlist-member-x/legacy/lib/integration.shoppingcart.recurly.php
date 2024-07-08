<?php

/*
 * Generic Payment Provider Integration Functions
 * Original Author : Erwin Atuli
 */

// $__classname__ = 'WLM_INTEGRATION_RECURLY';
// $__optionname__ = 'recurlythankyou';
// $__methodname__ = 'recurly';
if (! class_exists('WLM_INTEGRATION_RECURLY')) {

    class WLM_INTEGRATION_RECURLY
    {
        public function recurly($that)
        {
            require_once $that->plugin_dir . '/extlib/WP_RecurlyClient.php';
            $client = new WP_RecurlyClient($that->get_option('recurlyapikey'));
            if ('reg' === wlm_get_data()['act']) {
                $plan_code     = wlm_get_data()['plan_code'];
                $account_code  = wlm_get_data()['account_code'];
                $account       = $client->get_account($account_code);
                $subscriptions = $client->get_subscriptions($account_code);

                if (empty($account) || empty($subscriptions)) {
                    // Maybe redirect to cancel url?
                    return;
                }

                // Check that this subscription is actually in the users subscriptions.
                $current_subscription = null;
                $found                = false;
                foreach ($subscriptions as $s) {
                    if ($s['plan_code'] == $plan_code) {
                        $found                = true;
                        $current_subscription = $s;
                    }
                }

                if (! $found) {
                    // Cheatin huh?
                    return;
                }

                if ('active' !== $current_subscription['state']) {
                    return;
                }

                $plan                        = $client->get_plan($plan_code);
                wlm_post_data()['lastname']  = $account['last_name'];
                wlm_post_data()['firstname'] = $account['first_name'];
                wlm_post_data()['action']    = 'wpm_register';
                wlm_post_data()['wpm_id']    = $plan['accounting_code'];
                wlm_post_data()['username']  = $account['email'];
                wlm_post_data()['email']     = $account['email'];
                wlm_post_data()['password1'] = $that->pass_gen(null, true);
                wlm_post_data()['password2'] = wlm_post_data()['password1'];
                wlm_post_data()['sctxnid']   = $current_subscription['uuid'];
                $that->shopping_cart_registration();
            } else {
                $listen = [
                    'canceled_subscription_notification',
                    'expired_subscription_notification',
                    'renewed_subscription_notification',
                    'updated_subscription_notification',
                ];

                $notif = file_get_contents('php://input');
                $type  = $client->get_notification_type($notif);

                if (in_array($type, $listen)) {
                    $subscription              = $client->get_subscription_from_notif($notif);
                    wlm_post_data()['sctxnid'] = $subscription['uuid'];
                    if ('active' === $subscription['state']) {
                        $that->shopping_cart_reactivate();
                    } else {
                        $that->shopping_cart_deactivate();
                    }
                }
            }
        }
    }

}
