<?php

add_action('wishlistmember_autoresponder_subscribe', ['\WishListMember\Autoresponders\Generic', 'subscribe'], 10, 2);
add_action('wishlistmember_autoresponder_unsubscribe', ['\WishListMember\Autoresponders\Generic', 'unsubscribe'], 10, 2);
