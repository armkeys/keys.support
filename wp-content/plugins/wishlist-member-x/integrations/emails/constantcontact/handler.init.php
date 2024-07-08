<?php

/**
 * Constant Contact integration handler init.
 *
 * @package WishListMember/Autoresponders
 */

$class_name = '\WishListMember\Autoresponders\ConstantContact';

// Migrate old settings to new.
add_action(
    'wishlistmember_version_changed',
    function ($old = '', $new = '') {
        $ar = ( new \WishListMember\Autoresponder('constantcontact') );
        if (empty($ar->settings['ccID'])) {
            return;
        }
        if (! empty($ar->settings['list_actions'])) {
            return;
        }

        if (is_array($ar->settings['ccID'])) {
            foreach ($ar->settings['ccID'] as $level => $listid) {
                if ($listid) {
                    $ar->settings['list_actions'][ $level ]['added']['add'] = $listid;
                    if (! empty($ar->settings['ccUnsub'][ $level ])) {
                        $ar->settings['list_actions'][ $level ]['removed']['remove'] = $listid;
                    }
                }
            }
            $ar->settings['list_actions'] = array_diff((array) $ar->settings['list_actions'], ['', false, null, 0]);
        } else {
            $ar->settings['list_actions'] = [];
        }
        $ar->save_settings();
    },
    10,
    2
);

add_action(
    'admin_init',
    function () {
        $_get = filter_input_array(INPUT_GET);

        if (isset($_get['wlmcc_code'])) {
            if (! empty($_get['error_description'])) {
                delete_transient('wlm_constantcontact_token');
                $ar           = ( new \WishListMember\Autoresponder('constantcontact') );
                $err          = [
                    'access_token'      => null,
                    'ccusername'        => null,
                    'ccpassword'        => null,
                    'refresh_token'     => null,
                    'error_description' => $_get['error_description'],
                ];
                $ar->settings = array_merge($err, $ar->settings);
                $ar->save_settings();
            } else {
                if (! class_exists('ConstantContact\API_V3')) {
                    require_once __DIR__ . '/wlm-constantcontact-v3.php';
                }
                $constant_contact_v3 = new \WishListMember\Autoresponders\ConstantContact\API_V3(admin_url());
                delete_transient('wlm_constantcontact_token');
                if (false === $constant_contact_v3->get_last_error()) {
                    $acces_token = $constant_contact_v3->get_access_token($_get['wlmcc_code']);
                    $acces_token = json_decode($acces_token, true);
                    if (isset($acces_token['access_token'])) {
                        $ar = ( new \WishListMember\Autoresponder('constantcontact') );
                        unset($ar->settings['ccusername']);
                        unset($ar->settings['ccpassword']);
                        unset($ar->settings['error_description']);
                        $ar->settings = array_merge($acces_token, $ar->settings);
                        $ar->save_settings();
                        set_transient('wlm_constantcontact_token', $acces_token['access_token'], 2 * HOUR_IN_SECONDS);
                    }
                }
            }
            header('Location: ' . admin_url() . '?page=WishListMember&wl=setup%2Fintegrations%2Femail_provider%2Fconstantcontact#constantcontact_settings');
        }
    },
    10,
    2
);

add_action(
    'admin_notices',
    function () {
        $ar = ( new \WishListMember\Autoresponder('constantcontact') );
        if (empty($ar->settings['refresh_token'])) {
            if (! empty($ar->settings['ccusername']) && ! empty($ar->settings['ccpassword'])) {
                $url      = admin_url() . '?page=WishListMember&wl=setup%2Fintegrations%2Femail_provider%2Fconstantcontact#constantcontact_settings';
                $class    = 'notice notice-error';
                $message  = __('WishList Member Constant Contact Integration needs to be reauthenticated.', 'wishlist-member');
                $message2 = __('Click here to reauthenticate.', 'wishlist-member');
                printf('<div class="%1$s"><p>%2$s <a href="%3$s">%4$s</a></p></div>', esc_attr($class), esc_html($message), esc_attr($url), esc_html($message2));
            }
        }
    }
);

add_action(
    'wishlistmember_admin_screen_notices',
    function ($wl, $base) {
        if ('setup/integrations/email_provider/constantcontact' === $wl) {
            return;
        }
        $ar = ( new \WishListMember\Autoresponder('constantcontact') );
        if (empty($ar->settings['refresh_token'])) {
            if (! empty($ar->settings['ccusername']) && ! empty($ar->settings['ccpassword'])) {
                $url      = admin_url() . '?page=WishListMember&wl=setup%2Fintegrations%2Femail_provider%2Fconstantcontact#constantcontact_settings';
                $class    = 'form-text text-danger help-block mb-1';
                $message  = __('WishList Member Constant Contact Integration needs to be reauthenticated.', 'wishlist-member');
                $message2 = __('Click here to reauthenticate.', 'wishlist-member');
                printf('<div class="%1$s"><p class="mb-0">%2$s <a href="%3$s">%4$s</a></p></div>', esc_attr($class), esc_html($message), esc_attr($url), esc_html($message2));
            }
        }
    },
    10,
    2
);

add_action('wishlistmember_user_registered', [$class_name, 'user_registered'], 99, 2);
add_action('wishlistmember_add_user_levels_shutdown', [$class_name, 'added_to_level'], 99, 2);
add_action('wishlistmember_confirm_user_levels', [$class_name, 'added_to_level'], 99, 2);
add_action('wishlistmember_approve_user_levels', [$class_name, 'added_to_level'], 99, 2);

add_action('wishlistmember_remove_user_levels', [$class_name, 'removed_from_level'], 99, 2);
add_action('wishlistmember_cancel_user_levels', [$class_name, 'cancelled_from_level'], 99, 2);
add_action('wishlistmember_uncancel_user_levels', [$class_name, 'uncancelled_from_level'], 99, 2);
