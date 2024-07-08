<?php

/**
 * Payment Integration Methods
 *
 * @package WishListMember
 */

namespace WishListMember;

/**
* Payment Integration Methods trait
*/
trait Payment_Integration_Methods
{
    /**
     * Payment Provider Registration
     *
     * Additional data expected in $_POST
     *
     * @param boolean $temp          (optional) TRUE if temporary account.
     * @param boolean $redir         (optional) TRUE to redirect to regisrtation form.
     * @param string  $pendingstatus (optional) Pending status.
     */
    public function shopping_cart_registration($temp = null, $redir = null, $pendingstatus = null)
    {
        /**
         * Triggered at the start of the ::shopping_cart_registration() method
         *
         * @param array $post_data Post data.
         * @param array $function_arguments Function arguments.
         */
        do_action('wishlistmember_pre_shopping_cart_registration', wlm_post_data(true), func_get_args());

        if (is_null($temp)) {
            $temp = true;
        }
        if (is_null($redir)) {
            $redir = true;
        }

        $wpm_id = wlm_post_data()['wpm_id'];

        $registration_level = new \WishListMember\Level(
            $wpm_id,
            apply_filters('wishlistmember_registration_levels', wishlistmember_instance()->get_option('wpm_levels'))
        );

        // Expects values in $_POST.
        wlm_post_data()['orig_email'] = wlm_post_data()['email'];

        if ($temp) {
            // Set temporary email because we will change things later...
            wlm_post_data()['email']    = 'temp_' . md5(wlm_post_data()['email']);
            wlm_post_data()['username'] = wlm_post_data()['email'];
            // We don't want any emails sent for temporary accounts.
            $sendmail    = false;
            $notifyadmin = false;
        } else {
            // Send emails because this is not a temporary account.
            $sendmail    = true;
            $notifyadmin = true;
        }

        $existing     = false;
        $email_exists = '';
        $payperpost   = $this->is_ppp_level($wpm_id);
        if ($registration_level->ID || $payperpost) {
            $wpm_errmsg      = '';
            $email_exists    = email_exists(wlm_post_data()['orig_email']);
            $in_another_site = false;
            if ($email_exists && is_multisite() && ! is_user_member_of_blog($email_exists, get_current_blog_id())) {
                add_user_to_blog(get_current_blog_id(), $email_exists, get_option('default_role'));
                $in_another_site = true;
                $registered      = false;
            }

            if (! $in_another_site) {
                $registered = $this->wpm_register(wlm_post_data(true), $wpm_errmsg, $sendmail, $notifyadmin, null, $pendingstatus);
            }

            $account_autocreated = apply_filters('wishlistmember_autocreate_account', false, $wpm_id, wlm_post_data()['email'], wlm_post_data()['orig_email']);

            if ($account_autocreated) {
                $registered = true;
                $temp       = false;
            }

            if (! $registered && $temp) {
                $u = new \WP_User(wlm_post_data()['username']);
                /**
                 * Do not fail registration if
                 * 1. This is a temporary account and
                 * 2. It failed registration because the same
                 * tmp account
                 * --Reuse the tmp account instead so that the user may be able
                 * to complete it.
                 */
                if (! $u) {
                    return $wpm_errmsg;
                }
                $registered = true;
                if ($redir) {
                    $location = $this->get_continue_registration_url(wlm_post_data()['orig_email']);
                    if ($email_exists && $this->get_option('redirect_existing_member')) {
                        $location .= '&existing=1';
                    }
                    if ($location) {
                        header('Location:' . $location);
                        exit;
                    }
                }
            }

            if ($registered) {
                do_action('wishlistmember_shoppingcart_register', $this);
            } else {
                $xid = email_exists(wlm_post_data()['email']);
                if (! $xid) {
                    $xid = username_exists(wlm_post_data()['username']);
                }
                if ($xid) {
                    $this->wpm_register_existing(wlm_post_data(true), $wpm_errmsg, $sendmail, $notifyadmin, true);

                    $registered = true;
                    $existing   = true;
                }
            }

            if ($registered && $existing) {
                // Uncancel "cancelled" members when they "re-pay".
                $this->shopping_cart_reactivate();
            }

            if ($registered && ! $temp) {
                if (wlm_post_data()['sc_type'] === 'Stripe') {
                    do_action('stripe_add_customer_id');
                }

                // Handle order bumps for ClickBank.
                if (wlm_post_data()['sc_type'] === 'Clickbank' && wlm_post_data()['cb_bump'] === true) {
                    do_action('wishlistmember_clickbank_process_order_bumps');
                }

                do_action('wishlistmember_after_registration');
            }

            if ($redir) {
                if (! $existing && $temp) {
                    @wlm_setcookie('wpmu', wlm_post_data()['email'], 0, '/');
                    $location = $this->get_registration_url($wpm_id, true, $dummy);
                    if ($email_exists && $this->get_option('redirect_existing_member')) {
                        $location .= '&existing=1';
                    }
                    header('Location:' . $location);
                    exit;
                }

                // Redirect to "processing" page.
                $location = $this->get_registration_url($wpm_id, false, $dummy) . '&registered=1';
                header('Location:' . $location);
                exit;
            }
        } else {
            // We got an invalid membership level ID.
            header('Location:' . get_bloginfo('url'));
            exit;
        }
    }

    /**
     * Payment Provider Membership De-activation
     *
     * Data expected in $_POST
     *
     * @return boolean TRUE on success
     */
    public function shopping_cart_deactivate()
    {
        /**
         * Triggered at the start of the ::shopping_cart_deactivate() method
         *
         * @param array $post_data Post data.
         * @param array $function_arguments Function arguments.
         */
        do_action('wishlistmember_pre_shopping_cart_deactivate', wlm_post_data(true), func_get_args());

        // Add member to level's cancelled list.
        $wpm_levels = $this->get_option('wpm_levels');

        // We search for the user who has wlm_sctxns set to the posted transaction ID.
        $user = $this->get_user_id_from_txn_id(wlm_post_data()['sctxnid']);
        if ($user) {
            $user = $this->get_user_data($user);
        }

        // Load user posts from transaction id.
        $userposts = $this->get_user_posts_from_txn_id(wlm_post_data()['sctxnid']);

        // No user still?  then load one from the posted username.
        if (! $user->ID) {
            $user = $this->get_user_data(0, wlm_post_data()['username']);
        }
        if ($user->ID || $userposts) {
            $levels = array_intersect(array_keys((array) $this->get_membership_levels_txn_ids($user->ID, wlm_post_data()['sctxnid'])), $this->get_membership_levels($user->ID));
            foreach ((array) $levels as $level) {
                if (! $wpm_levels[ $level ]['isfree']) {
                    $this->level_cancelled($level, $user->ID, true);
                } else {
                    $this->level_sequential_cancelled($level, $user->ID, true);
                }
            }

            if ($userposts) {
                foreach ($userposts as $userpost) {
                    $this->remove_post_users($userpost->type, $userpost->content_id, $userpost->level_id);
                }
            }

            do_action('wishlistmember_shoppingcart_deactivate', $this);
            return true;
        } else {
            $this->cart_integration_terminate();
        }
    }

    /**
     * Payment Provider Membership Re-activation
     *
     * @param  boolean $process_pending (optional) True to process pending registrations.
     * @return boolean TRUE on success
     */
    public function shopping_cart_reactivate($process_pending = null)
    {
        /**
         * Triggered at the start of the ::shopping_cart_reactivate() method
         *
         * @param array $post_data Post data.
         * @param array $function_arguments Function arguments.
         */
        do_action('wishlistmember_pre_shopping_cart_reactivate', wlm_post_data(true), func_get_args());

        // Remove member from level's cancelled list.
        // We search for the user who has wlm_sctxns set to the posted transaction ID.
        $user = $this->get_user_id_from_txn_id(wlm_post_data()['sctxnid']);
        if ($user) {
            $user = $this->get_user_data($user);
        }

        $wpm_levels = $this->get_option('wpm_levels');

        // No user still?  then load one from the posted username.
        if (! $user->ID) {
            $user = $this->get_user_data(0, wlm_post_data()['username']);
        }
        if ($user->ID) {
            $levels = array_intersect(array_keys((array) $this->get_membership_levels_txn_ids($user->ID, wlm_post_data()['sctxnid'])), $this->get_membership_levels($user->ID));
            foreach ((array) $levels as $level) {
                if (! is_null($process_pending)) {
                    $this->level_for_approval($level, $user->ID, false);
                } else {
                    $this->level_cancelled($level, $user->ID, false);

                    $expired = wishlistmember_instance()->level_expired($level, $user->ID);
                    if ($expired && isset($wpm_levels[ $level ]['registrationdatereset'])) {
                        $timestamp = current_time('timestamp');
                        $this->user_level_timestamp($user->ID, $level, $timestamp);
                    }
                }
            }
            do_action('wishlistmember_shoppingcart_reactivate', $this);
            return true;
        } else {
            $this->cart_integration_terminate();
        }
    }

    /**
     * Schedule cart deactivation.
     * Shopping cart deactivation will set a meta_key of deactivate_date for a membership level. Glen Barnhardt 4/15/2010
     *
     * Expects data in $_POST
     */
    public function schedule_cart_deactivation()
    {
        global $wpdb;
        // Add member to level's scheduled for cancel.
        $wpm_levels = $this->get_option('wpm_levels');

        // We search for the user who has wlm_sctxns set to the posted transaction ID.
        $user = $this->get_user_id_from_txn_id(wlm_post_data()['sctxnid']);
        if ($user) {
            $user = $this->get_user_data($user);
        }

        // No user still?  then load one from the posted username.
        if (! $user->ID) {
            $user = $this->get_user_data(0, wlm_post_data()['username']);
        }
        if ($user->ID) {
            $levels = array_intersect(array_keys((array) $this->get_membership_levels_txn_ids($user->ID, wlm_post_data()['sctxnid'])), $this->get_membership_levels($user->ID));
            // First check to see if the array has been set.
            $cancel_array = $this->Get_UserMeta($user->ID, 'wlm_schedule_member_cancel');
            foreach ((array) $levels as $level) {
                if (! $wpm_levels[ $level ]['isfree']) {
                    // If the array has been set see if the value being set is in the array.
                    if (! empty($cancel_array[ $level ])) {
                        $cancel_array[ $level ] = wlm_post_data()['ddate'];
                    } else {
                        $cancel_array[ $level ] = wlm_post_data()['ddate'];
                    }
                }
            }
            $update_status = $this->Update_UserMeta($user->ID, 'wlm_schedule_member_cancel', $cancel_array);
            return true;
        }
        header('Location:' . get_bloginfo('url'));
        exit;
    }

    /**
     * Pass control to payment provider method/function.
     * Called by 'wishlistmember_call_payment_provider_method' action.
     *
     * @param  mixed  $callable Callable function or method.
     * @param  array  $scmethod Associative array containing payment provider filename, class and method.
     * @param  string $scuri    Shopping Cart URI slug.
     * @return void
     */
    public function call_payment_provider_method($callable, $scmethod, $scuri)
    {
        call_user_func($callable);
    }
}

// Register hooks.
add_action(
    'wishlistmember_register_hooks',
    function ($wlm) {
        add_action('wishlistmember_call_payment_provider_method', [$wlm, 'call_payment_provider_method'], 10, 3);
    }
);
