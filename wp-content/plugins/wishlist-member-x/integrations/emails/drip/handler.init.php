<?php

add_action('wishlistmember_autoresponder_subscribe', ['\WishListMember\Autoresponders\Drip', 'subscribe'], 10, 2);
add_action('wishlistmember_autoresponder_unsubscribe', ['\WishListMember\Autoresponders\Drip', 'unsubscribe'], 10, 2);
