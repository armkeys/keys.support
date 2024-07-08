<?php

add_action('wishlistmember_autoresponder_subscribe', ['\WishListMember\Autoresponders\IContact', 'subscribe'], 10, 2);
add_action('wishlistmember_autoresponder_unsubscribe', ['\WishListMember\Autoresponders\IContact', 'unsubscribe'], 10, 2);
