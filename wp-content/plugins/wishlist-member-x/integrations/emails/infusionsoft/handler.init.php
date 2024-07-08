<?php

$ifs_class_name = '\WishListMember\Autoresponders\Infusionsoft';

// Tags.
add_action('wishlistmember_user_registered', [$ifs_class_name, 'NewUserTagsHookQueue'], 99, 2);
add_action('wishlistmember_add_user_levels_shutdown', [$ifs_class_name, 'AddUserTagsHookQueue'], 10, 3);

add_action('wishlistmember_confirm_user_levels', [$ifs_class_name, 'ConfirmApproveLevelsTagsHook'], 99, 2);
add_action('wishlistmember_approve_user_levels', [$ifs_class_name, 'ConfirmApproveLevelsTagsHook'], 99, 2);

add_action('wishlistmember_pre_remove_user_levels', [$ifs_class_name, 'RemoveUserTagsHookQueue'], 99, 2);
add_action('wishlistmember_cancel_user_levels', [$ifs_class_name, 'CancelUserTagsHookQueue'], 99, 2);
add_action('wishlistmember_uncancel_user_levels', [$ifs_class_name, 'UnCancelUserTagsHookQueue'], 99, 2);
add_action('wishlistmember_expire_user_levels', [$ifs_class_name, 'ExpireUserTagsHookQueue'], 99, 2);
add_action('wishlistmember_unexpire_user_levels', [$ifs_class_name, 'UnexpireUserTagsHookQueue'], 99, 2);
add_action('delete_user', [$ifs_class_name, 'DeleteUserHookQueue'], 9, 1);

// We only process the following methods if they're not handled by the Infusionsofy payment integration.
if (! wishlistmember_instance()->payment_integration_is_active('infusionsoft') || ! wishlistmember_instance()->get_option('ismachine') || ! wishlistmember_instance()->get_option('isapikey')) {
    add_action('edit_user_profile', [$ifs_class_name, 'ProfileForm']);
    add_action('show_user_profile', [$ifs_class_name, 'ProfileForm']);
    add_action('profile_update', [$ifs_class_name, 'UpdateProfile'], 9, 2);

    add_filter('wishlist_member_user_custom_fields', [$ifs_class_name, 'add_ifs_field'], 99, 2);
    add_filter('wishlistmember_post_update_user', [$ifs_class_name, 'save_ifs_field'], 99, 1);
}

add_action('wishlistmember_api_queue', [$ifs_class_name, 'ifarProcessQueue']);

// Legacy.
add_action('wishlistmember_autoresponder_subscribe', [$ifs_class_name, 'subscribe'], 10, 2);
add_action('wishlistmember_autoresponder_unsubscribe', [$ifs_class_name, 'unsubscribe'], 10, 2);
