<?php

$class_name = '\WishListMember\Autoresponders\FluentCRM';


add_action('wishlistmember_user_registered', [$class_name, 'NewUserTagsHook'], 99, 2);
add_action('wishlistmember_add_user_levels_shutdown', [$class_name, 'AddUserTagsHook'], 10, 3);

add_action('wishlistmember_confirm_user_levels', [$class_name, 'ConfirmApproveLevelsTagsHook'], 99, 2);
add_action('wishlistmember_approve_user_levels', [$class_name, 'ConfirmApproveLevelsTagsHook'], 99, 2);

add_action('wishlistmember_pre_remove_user_levels', [$class_name, 'RemoveUserTagsHook'], 99, 2);
add_action('wishlistmember_cancel_user_levels', [$class_name, 'CancelUserTagsHook'], 99, 2);
add_action('wishlistmember_uncancel_user_levels', [$class_name, 'ReregUserTagsHook'], 99, 2);

add_action('fluentcrm_contact_added_to_tags', [$class_name, 'TagsAddedHook'], 99, 2);
add_action('fluentcrm_contact_removed_from_tags', [$class_name, 'TagsRemovedHook'], 99, 2);
add_action('fluentcrm_contact_added_to_lists', [$class_name, 'ListsAddedHook'], 99, 2);
add_action('fluentcrm_contact_removed_from_lists', [$class_name, 'ListsRemovedHook'], 99, 2);

add_action('wp_ajax_wishlistmember_fluentcrm_delete_tag_action', [$class_name, 'delete_tag_action']);
