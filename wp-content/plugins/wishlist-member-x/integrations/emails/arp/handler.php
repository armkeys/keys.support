<?php

namespace WishListMember\Autoresponders;

class ARP
{
    public static function subscribe($email, $level_id)
    {
        self::process($email, $level_id, true);
    }

    public static function unsubscribe($email, $level_id)
    {
        self::process($email, $level_id, false);
    }

    public static function process($email, $level_id, $subscribe)
    {
        $ar = ( new \WishListMember\Autoresponder('arp') )->settings;

        $postURL = $ar['arpurl'];
        $arUnsub = ( 1 == $ar['arUnsub'][ $level_id ] ? true : false );

        if ($postURL && $ar['arID'][ $level_id ]) {
            $emailAddress = wishlistmember_instance()->ar_sender['email'];
            $fullName     = wishlistmember_instance()->ar_sender['name'];

            $httpAgent = 'ARPAgent';
            $postData  = [
                'id'                => $ar['arID'][ $level_id ],
                'full_name'         => $fullName,
                'split_name'        => $fullName,
                'email'             => $emailAddress,
                'subscription_type' => 'E',
            ];
            if (! $subscribe) {
                if ($arUnsub) {
                    $postData['arp_action'] = 'UNS';
                } else {
                    return;
                }
            }

            wp_remote_post(
                $postURL,
                [
                    'blocking'   => false,
                    'user-agent' => $httpAgent,
                    'body'       => $postData,
                ]
            );
        }
    }
}
