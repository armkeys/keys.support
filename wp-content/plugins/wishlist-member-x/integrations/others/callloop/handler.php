<?php

/*
 * Call Loop Autoresponder Integration Functions
 * Original Author : Andy Depp
 * Version: $Id:
 */

// $__classname__ = 'WLM_OTHER_INTEGRATION_CALLLOOP';
// $__optionname__ = 'callloop';
// $__methodname__ = 'Callloop';
if (! class_exists('WLM_OTHER_INTEGRATION_CALLLOOP')) {

    class WLM_OTHER_INTEGRATION_CALLLOOP
    {
        public static function Callloop($user_id, $wpm_id, $unsub = false)
        {
            if (false !== strpos($wpm_id, 'U-')) {
                return false;
            }

            $callloop_settings = (array) wishlistmember_instance()->get_option('callloop_settings');
            $callloopURL       = $callloop_settings['URL'][ $wpm_id ];

            if ($callloopURL) {
                $user_id          = (int) $user_id;
                $userCustomFields = wishlistmember_instance()->get_user_custom_fields($user_id);

                if (array_key_exists('phone', $userCustomFields)) {
                    $phone = $userCustomFields['phone'];
                    if (self::ValidatePhoneNumber($phone)) {
                        $callloop_autoresponder_id = str_replace('https://www.callloop.com/r/?', '', $callloop_settings['URL'][ $wpm_id ]);
                        $arUnsub                   = ( 1 == $callloop_settings['callloopUnsub'][ $wpm_id ] ? true : false );
                        if (function_exists('curl_init')) {
                            if ($callloop_autoresponder_id) {
                                $user_data = wishlistmember_instance()->get_user_data($user_id);
                                $fName     = $user_data->user_firstname;
                                $lName     = $user_data->user_lastname;

                                if (( empty($fName) ) || ( empty($lName) )) {
                                    list($fName, $lName) = explode(' ', $user_data->display_name, 2);
                                }

                                $emailAddress = $user_data->user_email;

                                if ($unsub) {
                                    if ($arUnsub) {
                                        // Remove  phone from call loop list.
                                        $UnsubURL = "http://www.callloop.com/s/?{$callloop_autoresponder_id}&phone={$phone}";
                                        self::NavigateURL($UnsubURL);
                                    }
                                } else {
                                    // Add phone to call loop list.
                                    $subURL = "http://www.callloop.com/r/?{$callloop_autoresponder_id}&first={$fName}&last={$lName}&email={$emailAddress}&phone={$phone}";
                                    self::NavigateURL($subURL);
                                }
                            } //end if
                        } //end if
                    } //end if
                } //end if
            } //end if
        }

        public static function ValidatePhoneNumber($phone)
        {
            return true;
        }

        public static function NavigateURL($url)
        {
            $ch = curl_init();
            // Set query data here with the URL.
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, '60');
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            $content = trim(curl_exec($ch));
            curl_close($ch);
            // Print $content;
        }

        // Hooks.
        public static function AddLevels($user_id, $levels)
        {
            foreach ($levels as $level) {
                self::Callloop($user_id, $level);
            }
        }

        public static function RemoveLevels($user_id, $levels)
        {
            foreach ($levels as $level) {
                self::Callloop($user_id, $level, true);
            }
        }

        public static function remove_hooks()
        {
            remove_action('wishlistmember_remove_user_levels', ['WLM_OTHER_INTEGRATION_CALLLOOP', 'RemoveLevels'], 10);
            remove_action('wishlistmember_add_user_levels_shutdown', ['WLM_OTHER_INTEGRATION_CALLLOOP', 'AddLevels'], 10);
        }

        public static function set_hooks()
        {
            add_action('wishlistmember_remove_user_levels', ['WLM_OTHER_INTEGRATION_CALLLOOP', 'RemoveLevels'], 10, 2);
            add_action('wishlistmember_add_user_levels_shutdown', ['WLM_OTHER_INTEGRATION_CALLLOOP', 'AddLevels'], 10, 2);
            add_action('wishlistmember_suppress_other_integrations', ['WLM_OTHER_INTEGRATION_CALLLOOP', 'remove_hooks']);
        }
    }

    WLM_OTHER_INTEGRATION_CALLLOOP::set_hooks();
}
