<?php

// Migrate old settings to new.
add_action(
    'wishlistmember_version_changed',
    function ($old = '', $new = '') {
        $ar = ( new \WishListMember\Autoresponder('sendfox') );
        if (empty($ar->settings['lists'])) {
            return;
        }
        if (! empty($ar->settings['list_actions'])) {
            return;
        }

        if (is_array($ar->settings['lists'])) {
            foreach ($ar->settings['lists'] as $level => $listid) {
                if ($listid) {
                    $ar->settings['list_actions'][ $level ]['added'] = ['add' => (array) $listid];
                    if (! empty($ar->settings['unsubscribe'][ $level ])) {
                        $ar->settings['list_actions'][ $level ]['removed'] = ['remove' => (array) $listid];
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

$class_name = '\WishListMember\Autoresponders\SendFox';

add_action('wishlistmember_user_registered', [$class_name, 'user_registered'], 99, 2);
add_action('wishlistmember_add_user_levels_shutdown', [$class_name, 'added_to_level'], 99, 2);
add_action('wishlistmember_confirm_user_levels', [$class_name, 'added_to_level'], 99, 2);
add_action('wishlistmember_approve_user_levels', [$class_name, 'added_to_level'], 99, 2);

add_action('wishlistmember_remove_user_levels', [$class_name, 'removed_from_level'], 99, 2);
add_action('wishlistmember_cancel_user_levels', [$class_name, 'cancelled_from_level'], 99, 2);
add_action('wishlistmember_uncancel_user_levels', [$class_name, 'uncancelled_from_level'], 99, 2);
