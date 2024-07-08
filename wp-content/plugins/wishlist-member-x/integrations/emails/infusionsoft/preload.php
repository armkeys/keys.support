<?php

add_action(
    'wishlistmember_toggle_email_provider_infusionsoft',
    function ($state) {
        if ($state && ! wishlistmember_instance()->get_option('infusionsoft_email_tags_migrate')) {
            $ars  = wishlistmember_instance()->get_option('Autoresponders');
            $tags = [];
            foreach (['istags_add_app', 'istags_add_rem', 'istags_remove_app', 'istags_remove_rem', 'istags_cancelled_app', 'istags_cancelled_rem'] as $option) {
                $x = wlm_maybe_unserialize(wishlistmember_instance()->get_option('auto_' . $option));
                if (is_array($x) && $x) {
                    $tags[ $option ] = $x;
                }
                wishlistmember_instance()->save_option('infusionsoft_email_tags_migrate', 1);
            }
            if (! is_array($ars['infusionsoft'])) {
                $ars['infusionsoft'] = [];
            }
            $ars['infusionsoft'] = array_merge($ars['infusionsoft'], $tags);
            wishlistmember_instance()->save_option('Autoresponders', $ars);
        }
    },
    10,
    1
);
