<?php

$class_name = '\WishListMember\Autoresponders\AweberAPI';

add_action('wishlistmember_api_queue', [$class_name, 'process_queue']);

add_action('wishlistmember_autoresponder_subscribe', [$class_name, 'subscribe'], 10, 2);
add_action('wishlistmember_autoresponder_unsubscribe', [$class_name, 'unsubscribe'], 10, 2);
