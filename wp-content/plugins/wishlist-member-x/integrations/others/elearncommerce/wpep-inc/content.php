<?php

class WPEPAddOnWishListContent extends WPEP_Content_Library_Integration
{
    public $meta_box_active = true;
    public $meta_box_title  = 'WishList Integration';

    public $default_purchase_cta       = 'Upgrade Membership Plan';
    public $redirect_meta_field_suffix = '_upgrade_link';

    public $prefix         = 'wpep_wish_list';
    public $slug           = 'wpep-wish-list';
    public $options_prefix = 'wpep_addon_wish_list';

    public function __construct()
    {
        $this->init();
    }

    public function has_access($post_id)
    {
        return $this->member_can_access(get_current_user_id(), $post_id);
    }

    public function before_grid_posts_query()
    {
        if (method_exists(wishlistmember_instance(), 'only_show_content_for_level')) {
            remove_action('pre_get_posts', [wishlistmember_instance(), 'only_show_content_for_level']);
        }
    }

    public function after_grid_posts_query()
    {

        if (method_exists(wishlistmember_instance(), 'only_show_content_for_level')) {
            add_action('pre_get_posts', [wishlistmember_instance(), 'only_show_content_for_level']);
        }
    }

    /**
     * For posts they do a get_post_type
     *
     * @param  $user_id
     * @param  $post_id
     * @param  string  $post_type
     * @return boolean
     */
    public function member_can_access($user_id, $post_id, $post_type = 'courses')
    {

        $protection       = wishlistmember_instance()->get_content_levels($post_type, $post_id);
        $_protected       = (int) in_array('Protection', $protection);
        $_payperpost      = (int) in_array('PayPerPost', $protection);
        $protection       = array_diff($protection, ['Protection', 'PayPerPost']);
        $_levels          = preg_grep('/^\d+$/', $protection);
        $_payperpostusers = preg_grep('/^U-\d+$/', $protection);

        // If not protected, then user has access.
        if (! $_protected) {
            return true;
        }

        $user_active_levels = wishlistmember_instance()->get_member_active_levels($user_id);

        // Check if the user has access to object via levels.
        foreach ($_levels as $level) {
            if (in_array($level, $user_active_levels)) {
                return true;
            }
        }

        // Check if the user has access to object via Pay Per Post.
        if ($_payperpost) {
            if (in_array('U-' . $user_id, $_payperpostusers)) {
                return true;
            }
        }

        return false;
    }
}
