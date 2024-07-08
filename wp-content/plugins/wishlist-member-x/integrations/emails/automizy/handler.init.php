<?php

$class_name = '\WishListMember\Autoresponders\Automizy';

add_action('wishlistmember_user_registered', [$class_name, 'NewUserTagsHookQueue'], 99, 2);
add_action('wishlistmember_add_user_levels_shutdown', [$class_name, 'AddUserTagsHookQueue'], 10, 3);
add_action('wishlistmember_pre_remove_user_levels', [$class_name, 'RemoveUserTagsHookQueue'], 99, 2);
add_action('wishlistmember_cancel_user_levels', [$class_name, 'CancelUserTagsHookQueue'], 99, 2);
add_action('wishlistmember_uncancel_user_levels', [$class_name, 'ReregUserTagsHookQueue'], 99, 2);
add_action('delete_user', [$class_name, 'DeleteUserHookQueue'], 9, 1);

add_action('wishlistmember_api_queue', [$class_name, 'ProcessQueue']);
