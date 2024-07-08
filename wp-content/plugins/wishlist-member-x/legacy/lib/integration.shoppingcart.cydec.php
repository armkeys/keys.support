<?php

/*
 * Cydec Payment Provider Integration Functions
 * Original Author : Mike Lopez
 * Version: $Id$
 */

// $__classname__ = 'WLM_INTEGRATION_CYDEC';
// $__optionname__ = 'cydecthankyou';
// $__methodname__ = 'Cydec';
if (! class_exists('WLM_INTEGRATION_CYDEC')) {

    class WLM_INTEGRATION_CYDEC
    {
        public function Cydec($that)
        {
            $cmd                         = wlm_post_data()['cmd']['cmd'];
            $hash                        = wlm_post_data()['hash']['hash'];
            $secret                      = $that->get_option('cydecsecret');
            $myhash                      = md5($cmd . '__' . $secret);
            wlm_post_data()['ddate']     = wlm_post_data()['cmd']['date'];
            wlm_post_data()['action']    = 'wpm_register';
            wlm_post_data()['processor'] = 'cydec';
            wlm_post_data()['lastname']  = wlm_post_data()['info']['last_name'];
            wlm_post_data()['firstname'] = wlm_post_data()['info']['first_name'];
            wlm_post_data()['wpm_id']    = wlm_post_data()['info']['level'];
            wlm_post_data()['username']  = wlm_post_data()['info']['email'];
            wlm_post_data()['email']     = wlm_post_data()['info']['email'];
            if (empty(wlm_post_data()['info']['password'])) {
                wlm_post_data()['password1'] = $that->pass_gen(null, true);
            } else {
                wlm_post_data()['password1'] = wlm_post_data()['info']['password'];
            }
            wlm_post_data()['password2'] = wlm_post_data()['password1'];

            wlm_post_data()['sctxnid'] = wlm_post_data()['info']['transaction_id'] ? wlm_post_data()['info']['transaction_id'] : 'CYDEC_' . wlm_post_data()['info']['email'];

            $trans_id = wlm_post_data()['info']['transaction_id'];
            $trans_id = str_replace('||', '', $trans_id);

            if (false == $that->check_member_trans_id($trans_id) && 'add' !== $cmd) {
                $order_id                  = explode('||', wlm_post_data()['sctxnid']);
                wlm_post_data()['sctxnid'] = $order_id[0];
            } else {
                wlm_post_data()['sctxnid'] = $trans_id;
            }

            if ($hash == $myhash) {
                // Add_filter('rewrite_rules_array',array(&$that,'rewrite_rules'));
                // $GLOBALS['wp_rewrite']->flush_rules();
                switch ($cmd) {
                    case 'add':
                        $that->shopping_cart_registration(false); // We ALWAYS auto-create account for CYDEC because they can't redirect.
                        break;
                    case 'delete':
                    case 'deactivate':
                        if (! empty(wlm_post_data()['ddate']) && 'NOW' !== wlm_post_data()['ddate']) {
                            $that->schedule_cart_deactivation();
                            // $that->cancel_scheduled_cancelations();
                        } else {
                            $that->shopping_cart_deactivate();
                        }
                        break;
                    case 'activate':
                        $that->shopping_cart_reactivate();
                        break;
                    default:
                        header('Location:' . get_bloginfo('url'));
                        exit;
                }
            }
        }
    }

}
