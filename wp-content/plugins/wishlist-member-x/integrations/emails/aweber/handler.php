<?php

namespace WishListMember\Autoresponders;

class AWeber
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
        $ar = ( new \WishListMember\Autoresponder('aweber') )->settings;

        $headers = "Content-type: text/plain; charset=us-ascii\r\n";
        if ($ar['email'][ $level_id ]) {
            $sendto = $ar['email'][ $level_id ];
            if (false === strpos($sendto, '@')) {
                $sendto .= '@aweber.com';
            }
            if ($subscribe) {
                $name                                = wishlistmember_instance()->ar_sender['name'];
                $message                             = "{$email}\n{$name}";
                wishlistmember_instance()->ar_sender = [
                    'name'  => 'Aweber Subscribe Parser',
                    'email' => wishlistmember_instance()->get_option('email_sender_address'),
                ];
                wp_mail($sendto, 'A New Member has Registered', $message, $headers);
            } else {
                wishlistmember_instance()->ar_sender = [
                    'name'  => 'Aweber Remove',
                    'email' => $ar['remove'][ $level_id ],
                ];
                $subject                             = 'REMOVE#' . $email . '#WLMember';
                wp_mail($sendto, $subject, 'AWEBER UNSUBSCRIBE', $headers);
            }
        }
    }
}
