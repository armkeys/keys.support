<?php

add_action('wishlistmember_autoresponder_subscribe', ['\WishListMember\Autoresponders\AWeber', 'subscribe'], 10, 2);
add_action('wishlistmember_autoresponder_unsubscribe', ['\WishListMember\Autoresponders\AWeber', 'unsubscribe'], 10, 2);
