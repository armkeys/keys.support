<?php

$class_name = '\WishListMember\Autoresponders\ActiveCampaign';

// Migrate old settings to new.
add_action(
    'init',
    function ($old = '', $new = '') {
        $ar = ( new \WishListMember\Autoresponder('activecampaign') );

        // End if no old settings defined.
        if (empty($ar->settings['maps'])) {
            return;
        }
        // End if new settings are already defined so we don't overwrite them.
        if (! empty($ar->settings['level_actions'])) {
            return;
        }

        if (is_array($ar->settings['maps'])) {
            foreach ($ar->settings['maps'] as $level => $list_ids) {
                if ($list_ids) {
                    // Migrate add to lists setting.
                    $ar->settings['level_actions'][ $level ]['added'] = ['add' => (array) $list_ids];
                    if (! empty(wlm_arrval($ar, 'settings', $level, 'autoremove'))) {
                        // Migrate remove from lists setting.
                        $ar->settings['level_actions'][ $level ]['removed']['remove'] = 1;
                    }
                }
            }
            // Clean data.
            $ar->settings['level_actions'] = array_diff((array) $ar->settings['level_actions'], ['', false, null, 0]);
        } else {
            // No settings configured.
            $ar->settings['level_actions'] = [];
        }
        // Save settings.
        $ar->save_settings();
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

add_action('wp_ajax_wishlistmember_activecampaign_delete_tag_action', [$class_name, 'delete_tag_action']);
add_action('wishlistmember_save_email_provider', [$class_name, 'add_tag_webhooks'], 10, 3);

add_action('init', [$class_name, 'process_webhooks'], 10, 3);
