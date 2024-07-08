<?php

add_action('wishlistmember_autoresponder_subscribe', ['\WishListMember\Autoresponders\Ontraport', 'subscribe'], 10, 2);
add_action('wishlistmember_autoresponder_unsubscribe', ['\WishListMember\Autoresponders\Ontraport', 'unsubscribe'], 10, 2);
