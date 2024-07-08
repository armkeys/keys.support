<?php

namespace WishListMember\Autoresponders;

class GetResponse
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
        $ar = ( new \WishListMember\Autoresponder('getresponse') )->settings;

        $headers = "Content-type: text/plain; charset=us-ascii\r\n";

        if ($subscribe && $ar['email'][ $level_id ]) {
            wp_mail($ar['email'][ $level_id ], 'Subscribe', '.', $headers);
        }
        if (! $subscribe && $ar['remove'][ $level_id ]) {
            wp_mail($ar['remove'][ $level_id ], 'Unsubscribe', '.', $headers);
        }
    }
}
