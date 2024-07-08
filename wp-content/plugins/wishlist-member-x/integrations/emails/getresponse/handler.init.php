<?php

add_action('wishlistmember_autoresponder_subscribe', ['\WishListMember\Autoresponders\GetResponse', 'subscribe'], 10, 2);
add_action('wishlistmember_autoresponder_unsubscribe', ['\WishListMember\Autoresponders\GetResponse', 'unsubscribe'], 10, 2);
