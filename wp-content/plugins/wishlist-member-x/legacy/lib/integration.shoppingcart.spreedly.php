<?php

/*
 * Pin Payments Payment Provider Integration Functions (formerly known as Spreedly)
 * Original Author : Fel Jun Palawan
 * Version: $Id$
 */

if (! class_exists('Spreedly')) {
    global $WishListMemberInstance;
    include_once $WishListMemberInstance->plugin_dir . '/extlib/class.spreedly.inc';
}

if (! class_exists('WLMSpreedly')) { // This is not the class being called by WLM, refer to the class below.

    class WLMSpreedly
    {
        public $spreedlytoken = '';
        public $spreedlyname  = '';
        public $athenticated  = false;
        public $plans         = [];

        public function __construct($that)
        {
            $this->spreedlytoken = $that->get_option('spreedlytoken');
            $this->spreedlyname  = $that->get_option('spreedlyname');

            if ($this->spreedlytoken && $this->spreedlyname) {
                Spreedly::configure($this->spreedlyname, $this->spreedlytoken);
                $plans = SpreedlySubscriptionPlan::get_all();
                if (isset($plans['ErrorCode'])) {
                    $this->athenticated = false;
                } else {
                    $this->athenticated = true;
                    $dum                = [];
                    foreach ($plans as $id => $data) {
                        $dum[ $data->id ] = $data;
                    }
                    $this->plans = $dum;
                }
            }
        }

        public function get_subscriber($id)
        {
            if (! $this->athenticated) {
                return null;
            }
            return SpreedlySubscriber::find($id);
        }

        public function add_subscriber($id, $screen_name, $email)
        {
            if (! $this->athenticated) {
                return null;
            }
            return SpreedlySubscriber::create($id, $email, $screen_name);
        }

        public function get_plan_sku($plan_id)
        {
            $plans = $this->plans;
            if ($this->is_valid_plan($plan_id)) {
                return $plans[ $plan_id ]->feature_level;
            } else {
                return '';
            }
        }

        public function is_valid_plan($plan_id)
        {
            return array_key_exists($plan_id, $this->plans);
        }
    }

}

if (! class_exists('WLM_INTEGRATION_SPREEDLY')) {

    class WLM_INTEGRATION_SPREEDLY
    {
        private $that = false;

        public function Spreedly($that)
        {
            $this->that    = $that;
            $wlmspreedly   = new WLMSpreedly($that);
            $wpm_levels    = $that->get_option('wpm_levels');
            $current_user  = wp_get_current_user();
            $spreedly_user = '';

            // LETS END EVERYTHING IF WE CANT CONNECT TO SPREEDLY.
            if (! $wlmspreedly->athenticated) {
                die('Oppss!! Something went wrong.Theres an error connecting to Pin Payments, please try again.');
            }

            // REDIRECT AFTER REGISTRATION.
            if (isset(wlm_get_data()['reg_id'])) {
                $plan_id = wlm_get_data()['reg_id'];
                $sku     = $wlmspreedly->get_plan_sku($plan_id);
                // Get/Create Spreedly user.
                if (array_key_exists($sku, $wpm_levels)) { // Make sure that its a correct membership level id.
                    if (0 != $current_user->ID) { // Make sure that the user is logged in.
                        $wl_user        = new \WishListMember\User($current_user->ID); // Get wlm user details.
                        $wl_user_levels = $wl_user->Levels; // Get user levels.
                        // Check if he has a for approval membership level using spreedly.
                        if (array_key_exists($sku, $wl_user_levels) && 'Pin Payments Confirmation' == $wl_user_levels[ $sku ]->Pending) {
                            // Get user spreedly account.
                            $spreedly_user = $wlmspreedly->get_subscriber($wl_user_levels[ $sku ]->TxnID);
                            // If no user, lets create.
                            if (is_null($spreedly_user)) {
                                $spreedly_user = $wlmspreedly->add_subscriber($wl_user_levels[ $sku ]->TxnID, $current_user->user_login, $current_user->user_email);
                            }
                        }
                    }
                }

                // Now we have our spreedly user account for this member.
                if (! is_null($spreedly_user) && isset($spreedly_user->customer_id)) {
                    $name      = explode(' ', $current_user->display_name, 2);
                    $user_data = [
                        'id'         => $spreedly_user->customer_id,
                        'email'      => $current_user->user_email,
                        'first_name' => $name[0],
                        'last_name'  => $name[1],
                    ];

                    // Redirect to spreedly payment form.
                    header('Location:' . $this->generate_subscription_url($wlmspreedly->spreedlyname, $plan_id, $user_data));
                    exit(0);
                }
            }

            // REDIRECT AFTER MEMBER PAYS FROM SPREEDLY AND CLICK "CONTINUE" LINK.
            if (isset(wlm_get_data()['sku']) && array_key_exists(wlm_get_data()['sku'], $wpm_levels)) {// make sure that its a correct membership level id
                if (0 != $current_user->ID) { // Make sure that the user is logged in.
                    // Get user membership levels.
                    $wl_user        = new \WishListMember\User($current_user->ID);
                    $wl_user_levels = $wl_user->Levels;

                    // Based on the txnid, get the spreedly user for this member.
                    $user = $wlmspreedly->get_subscriber($wl_user_levels[ wlm_get_data()['sku'] ]->TxnID);

                    if (! is_null($user)) {
                        $txn_detail = [
                            'txnid'    => $wl_user_levels[ wlm_get_data()['sku'] ]->TxnID,
                            'user_id'  => $current_user->ID,
                            'level_id' => wlm_get_data()['sku'],
                        ];

                        $this->process_membership($txn_detail, $user);

                        $afterreg = $this->get_after_reg_url($wpm_levels, wlm_get_data()['sku']);
                        header('Location:' . $afterreg);
                        exit(0);
                    }
                }
            }

            // SPREEDLY NOTIFICATION FOR CHANGES IN USERS AND THERE TRANSACTIONS.
            if (isset(wlm_post_data()['subscriber_ids'])) {
                $ids = wlm_post_data()['subscriber_ids'];
                $ids = explode(',', $ids);

                foreach ($ids as $id) {
                    $user        = $wlmspreedly->get_subscriber($id);
                    $txn_details = $this->get_txn_details($id);
                    foreach ($txn_details as $txn_detail) {
                        $this->process_membership($txn_detail, $user);
                    }
                }
            }
        }

        private function generate_subscription_url($spreedlyname, $spreedlyplan, $user_data)
        {
            $user_spreedly_id    = $user_data['id'];
            $user_spreedly_email = $user_data['email'];
            $user_spreedly_fname = $user_data['first_name'];
            $user_spreedly_lname = $user_data['last_name'];
            return "https://subs.pinpayments.com/{$spreedlyname}/subscribers/{$user_spreedly_id}/subscribe/{$spreedlyplan}?email={$user_spreedly_email}&first_name={$user_spreedly_fname}&last_name={$user_spreedly_lname}";
        }

        private function get_txn_details($txnid)
        {
            global $wpdb;
            $txn_details = [];
            $users       = $wpdb->get_results($wpdb->prepare('SELECT `userlevel_id`,`option_value` FROM `' . esc_sql($this->that->table_names->userlevel_options) . '` WHERE `option_value`=%s', $txnid));
            foreach ((array) $users as $user) {
                $userlvl = $wpdb->get_row($wpdb->prepare('SELECT `user_id`,`level_id` FROM `' . esc_sql($this->that->table_names->userlevels) . '` WHERE ID=%d', $user->userlevel_id));
                if ($userlvl) {
                    $txn_details[] = [
                        'txnid'    => $user->option_value,
                        'user_id'  => $userlvl->user_id,
                        'level_id' => $userlvl->level_id,
                    ];
                }
            }
            return $txn_details;
        }

        private function process_membership($txn_detail, $user = null)
        {
            if (! is_null($user)) {
                if ($user->active) {
                    $x = $this->that->level_for_approval($txn_detail['level_id'], $txn_detail['user_id']);
                    if ($x && 'Pin Payments Confirmation' === $x) { // If for approval and status is for comfirmation.
                        $this->that->level_for_approval($txn_detail['level_id'], $txn_detail['user_id'], false); // Approve user.
                    } else { // If active in spreedly and cancelled in our membership level, lets un-cancel him.
                        $x = $this->that->level_cancelled($txn_detail['level_id'], $txn_detail['user_id']);
                        if ($x) {
                            $this->that->level_cancelled($txn_detail['level_id'], $txn_detail['user_id'], false);
                        }
                    }
                } else {
                    if ($user->lifetime_subscription) {
                        $this->that->level_cancelled($txn_detail['level_id'], $txn_detail['user_id'], true);
                    } else {
                        $this->that->level_cancelled($txn_detail['level_id'], $txn_detail['user_id'], true, $user->active_until);
                    }
                }
            } else { // If the user does not have account with spreedly anymore, lets cancel him from our membership level.
                $this->that->level_cancelled($txn_detail['level_id'], $txn_detail['user_id'], true);
            }
        }

        private function get_after_reg_url($wpm_levels, $sku)
        {
            $wpm_level = $wpm_levels[ $sku ];
            return $this->get_after_reg_redirect($sku);
        }
    }

}
