<?php

namespace WishListMember\Autoresponders;

class Ontraport
{
    public static function subscribe($email, $level_id)
    {
        self::process($email, $level_id);
    }

    public static function unsubscribe($email, $level_id)
    {
        self::process($email, $level_id, true);
    }

    public static function process($email, $level_id, $unsub = false)
    {
        $ar = ( new \WishListMember\Autoresponder('ontraport') )->settings;

        if (! $unsub) {
            if ('yes' === $ar['addenabled'][ $level_id ]) {
                $fname = ( ! empty(wishlistmember_instance()->orig_post['firstname']) ) ? wishlistmember_instance()->orig_post['firstname'] : wishlistmember_instance()->ar_sender['first_name'];
                $lname = ( ! empty(wishlistmember_instance()->orig_post['lastname']) ) ? wishlistmember_instance()->orig_post['lastname'] : wishlistmember_instance()->ar_sender['last_name'];
                $email = ( ! empty(wishlistmember_instance()->orig_post['email']) ) ? wishlistmember_instance()->orig_post['email'] : wishlistmember_instance()->ar_sender['email'];

                // Set format to add tags.
                $tags = '*/*';
                foreach ((array) $ar['tags'][ $level_id ] as $tag) {
                    $tags .= $tag . '*/*';
                }

                // Set format for sequences.
                $sequences = '*/*';
                foreach ((array) $ar['sequences'][ $level_id ] as $sequence) {
                    $sequences .= $sequence . '*/*';
                }

                // Set the request type and construct the POST request.
                $postdata = [
                    'firstname'      => $fname,
                    'lastname'       => $lname,
                    'email'          => $email,
                    'contact_cat'    => $tags,
                    'updateSequence' => $sequences,
                ];

                wp_remote_post(
                    'https://api.ontraport.com/1/Contacts/saveorupdate',
                    [
                        'body'     => $postdata,
                        'blocking' => false,
                        'headers'  => [
                            'Content-Type' => 'application/x-www-form-urlencoded',
                            'Api-Key'      => $ar['api_key'],
                            'Api-Appid'    => $ar['app_id'],
                        ],
                    ]
                );
            }
        }
    }
}
