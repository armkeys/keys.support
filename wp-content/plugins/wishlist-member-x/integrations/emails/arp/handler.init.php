<?php

add_action('wishlistmember_autoresponder_subscribe', ['\WishListMember\Autoresponders\ARP', 'subscribe'], 10, 2);
add_action('wishlistmember_autoresponder_unsubscribe', ['\WishListMember\Autoresponders\ARP', 'unsubscribe'], 10, 2);
