<?php

/**
 * Content Methods Hooks
 *
 * @package WishListMember
 */

namespace WishListMember;

/**
 * Content Methods Hooks trait
 */
trait Content_Methods_Hooks
{
    /**
     * Show protection fields when adding/editing a category.
     * Called by '{$taxonomy}_edit_form_fields' and '{$taxonomy}_add_form_fields' hooks.
     *
     * @param \WP_Term $tag WP_Term object.
     */
    public function category_form($tag)
    {
        $add       = empty($tag->term_id);
        $tax       = get_taxonomy($add ? $tag : $tag->taxonomy);
        $tax_label = $tax->labels->singular_name;
        if (! $tax_label) {
            $tax_label = $tax->labels->name;
        }

        $checked = isset($tag->term_id) && $tag->term_id ? (int) $this->cat_protected($tag->term_id) : (int) $this->get_option('default_protect');

        $chkyes = $checked ? 'checked="checked"' : '';
        $chkno  = $checked ? '' : 'checked="checked"';

        // Translators: 1: Taxonomy label.
        $lbl = sprintf(__('Protect this %1$s?', 'wishlist-member'), $tax_label);
        $yes = __('Yes', 'wishlist-member');
        $no  = __('No', 'wishlist-member');
        if ($add) {
            ?>
            <div class="form-field">
                <label><?php echo esc_html($lbl); ?></label>
                <label style="display:inline">
                    <input style="width:auto" type="radio" name="wlmember_protect_category" <?php echo esc_html($chkyes); ?> value="yes">
                    <?php echo esc_html($yes); ?>
                </label>
                &nbsp;
                <label style="display:inline">
                    <input style="width:auto" type="radio" name="wlmember_protect_category" <?php echo esc_html($chkno); ?> value="no"> <?php echo esc_html($no); ?>
                </label>
            </div>
            <?php
        } else {
            ?>
            <tr class="form-field">
                <th scope="row"><?php echo esc_html($lbl); ?></th>
                <td>
                    <label style="display:inline"><input style="width:auto" type="radio" name="wlmember_protect_category" <?php echo esc_html($chkyes); ?> value="yes">
                        <?php echo esc_html($yes); ?>
                    </label>
                    &nbsp;
                    <label style="display:inline"><input style="width:auto" type="radio" name="wlmember_protect_category" <?php echo esc_html($chkno); ?> value="no">
                        <?php echo esc_html($no); ?>
                    </label>
                </td>
            </tr>
            <?php
        }
    }

    /**
     * Save taxonomy (category) hook
     * Called by 'edit_{$taxonomy}' and 'edit_{$taxonomy}' hooks.
     *
     * @param integer $id Category ID.
     */
    public function save_category($id)
    {
        global $wpdb;
        $id = abs($id);
        switch (wlm_arrval($this->post_data, 'wlmember_protect_category')) {
            case 'yes':
                $this->cat_protected($id, 'Y');
                break;
            case 'no':
                $this->cat_protected($id, 'N');
                break;
        }
    }

    /**
     * Only list terms (categories) for level.
     * Called by 'list_terms_exclusions' hook.
     *
     * @param  string $not_in NOT IN clause of the terms query.
     * @return string
     */
    public function only_list_cats_for_level($not_in)
    {
        global $current_user;

        if ($this->get_option('only_show_content_for_level') && ! wlm_arrval($current_user->caps, 'administrator')) {
            $wpm_levels = $this->get_option('wpm_levels');
            $levels     = $this->get_membership_levels($current_user->ID, false, true);

            $notallowed = ( isset($this->taxonomy_ids) ) ? (array) $this->taxonomy_ids : [];
            $allowed    = $this->get_membership_content('categories', $levels);

            foreach ($notallowed as $i => $cat) {
                if (in_array($cat, $allowed) || ! $this->cat_protected($cat)) {
                    unset($notallowed[ $i ]);
                }
            }

            if (count((array) $notallowed)) {
                $notallowed[] = 0; // WP 2.8 fix?
                $notallowed   = implode(',', $notallowed);
                $not_in      .= " AND t.term_id NOT IN ({$notallowed}) ";
            }
        }
        return $not_in;
    }

    /**
     * Check if we are in category view.
     *
     * @global type $wp_query
     * @return boolean
     */
    public function wlm_is_category()
    {
            global $wp_query;

        if (is_page() || is_single()) {
            return false;
        }

        if (isset($wp_query->query['category_name']) && '' !== $wp_query->query['category_name']) {
                return true;
        } else {
                return false;
        }
    }

    /**
     * Filter protected recent comments.
     *
     * Called by 'the_comments' hook
     *
     * @param  \WP_Comment[]     $comments Array of WP_Comment objects.
     * @param  \WP_Comment_Query $obj      WP_Comment_Query object.
     * @return [type]           [description]
     */
    public function recent_comments($comments = null, $obj = null)
    {
        if (false === is_active_widget(false, false, 'recent-comments', true)) {
            return $comments;
        }
        if (empty($comments)) {
            return $comments;
        }
        if (current_user_can('moderate_comments')) {
            return $comments;
        }

        global $current_user;
        $levels = $this->get_membership_levels($current_user->ID);
        remove_filter('the_comments', [$this, 'recent_comments'], 10, 2);

        // We only limit the number if no post_id is specified.
        if (! $obj->query_vars['post_id']) {
            $limit                     = $obj->query_vars['number'];
            $obj->query_vars['number'] = 30;
        }

        $all_comments = $obj->query($obj->query_vars);

        if (! empty($current_user->ID)) {
            // Get posts/pages logged in member has access to.
            $user_comments = $this->get_membership_content('posts', $levels);
            $user_comments = array_merge($user_comments, (array) $this->get_membership_content('pages', $levels));

            $protected_types = (array) $this->get_option('protected_custom_post_types');
            $protected_types = is_array($protected_types) ? $protected_types : [];
            foreach ($protected_types as $protected_type) {
                $user_comments = array_merge($user_comments, (array) $this->get_membership_content($protected_type, $levels));
            }

            $protect  = $this->protected_ids();
            $comments = [];
            foreach ($protect as $pc) {
                if (! in_array($pc, (array) $user_comments)) {
                    $comments = array_merge($comments, (array) $pc);
                }
            }
        } else {
            $protect  = $this->protected_ids();
            $comments = [];
            foreach ($protect as $pc) {
                $comments = array_merge($comments, (array) $pc);
            }
        }

        $the_comments = [];
        foreach ($all_comments as $c) {
            if (! in_array($c->comment_post_ID, $comments)) {
                $the_comments[] = $c;
            }
            // We only check limit if limit is set.
            if (! empty($limit) && count($the_comments) >= $limit) {
                break;
            }
        }

        add_filter('the_comments', [$this, 'recent_comments'], 10, 2);
        return $the_comments;
    }

    /**
     * Prepare WishList Member metabox for the WP post edit screen
     */
    public function prepare_post_page_options()
    {
        // Only allow specific roles to access post/page options.
        $wlmpageoptions_role_access = $this->get_option('wlmpageoptions_role_access');
        $wlmpageoptions_role_access = false === $wlmpageoptions_role_access ? false : $wlmpageoptions_role_access;
        $wlmpageoptions_role_access = is_string($wlmpageoptions_role_access) ? [] : $wlmpageoptions_role_access;
        if (is_array($wlmpageoptions_role_access)) {
            $wlmpageoptions_role_access[] = 'administrator';
            $wlmpageoptions_role_access   = array_unique($wlmpageoptions_role_access);
            $user                         = wp_get_current_user();
            $access                       = array_intersect($wlmpageoptions_role_access, (array) $user->roles);
            if (count($access) <= 0) {
                return false; // Only roles with access can use this.
            }
        }

        $post_types = ['post', 'page', 'attachment'] + get_post_types(['_builtin' => false]);
        foreach ($post_types as $post_type) {
            if (wlm_post_type_is_excluded($post_type)) {
                    continue;
            }
                add_meta_box('wlm_postpage_metabox', __('WishList Member', 'wishlist-member'), [$this, 'post_page_options'], $post_type);
        }
    }

    /**
     * Load WishList Member metabox in the WP post edit screen
     * Called by 'edit_attachment' and 'wp_insert_post' actions
     *
     * @param \WP_Post $post \WP_Post object.
     */
    public function post_page_options($post = null)
    {
        if (is_object($post) && (int) wlm_arrval($post, 'ID') !== (int) wlm_get_data()['post']) {
            $post = get_post(wlm_get_data()['post']);
        }
        if (empty($post)) {
            $post = $GLOBALS['post'] ?? null;
        }

        if (empty($post) || is_wp_error($post)) {
            return;
        }

        if (isset($post->post_type) && 'page' === $post->post_type) {
            $allindex     = 'allpages';
            $content_type = 'pages';
        } elseif (isset($post->post_type) && 'post' === $post->post_type) {
            $allindex     = 'allposts';
            $content_type = 'posts';
        } else {
            $content_type = isset($post->post_type) ? $post->post_type : '';
            $allindex     = 'all' . $content_type;
        }

        $wlm_inherit_protection = false;
        $wpm_levels             = $this->get_option('wpm_levels');
        $wpm_access             = $this->get_content_levels($content_type, $post->ID);
        if (! $post->ID) {
            $wpm_protect            = (bool) $this->get_option('default_protect');
            $wlm_payperpost         = (bool) $this->get_option('default_ppp');
            $wlm_payperpost_free    = false;
            $wlm_protection_inherit = false;
        } else {
            $wpm_protect = $this->protect($post->ID);
            if ('auto-draft' === $post->post_status) {
                $wlm_payperpost = (bool) $this->get_option('default_ppp');
            } else {
                $wlm_payperpost = $this->pay_per_post($post->ID);
            }

            $wlm_payperpost_free       = $this->free_pay_per_post($post->ID);
            $wlm_inherit_protection    = $this->special_content_level($post->ID, 'Inherit');
            $wlm_payperpost_afterlogin = (int) $this->special_content_level($post->ID, 'PayPerPost_AfterLogin');
        }

        // If post is  fully new, we follow defualt protection by force.
        if (isset($post->post_status) && 'auto-draft' === $post->post_status) {
            $wpm_protect = (bool) $this->get_option('default_protect');
        }

        // If post is new but saved, we follow   user selected option to protect.
        if (isset($post->post_status) && 'draft' === $post->post_status) {
            $wpm_protect = $this->protect($post->ID);
        }

        $protection_settings = (int) $wpm_protect;
        if ($wlm_inherit_protection) {
            $protection_settings = 2;
        }

        // Grab levels and protection of parent/s.
        $parent_levels = [];

        $taxonomy_names = get_taxonomies(['_builtin' => false], 'names');
        array_unshift($taxonomy_names, 'category');
        $taxonomies = wp_get_object_terms($post->ID, $taxonomy_names, ['fields' => 'ids']);

        $parent_protect       = false;
        $protected_taxonomies = [];
        if (! is_wp_error($taxonomies) && ! empty($taxonomies)) {
            $parent_protect = false;
            foreach ($taxonomies as $taxonomy) {
                if ($this->cat_protected($taxonomy)) {
                    $parent_protect         = true;
                    $protected_taxonomies[] = $taxonomy;
                    $parent_levels          = array_merge($parent_levels, $this->get_content_levels('categories', $taxonomy, null, null, $immutable));
                }
            }
        }

        $ancestor = get_post_ancestors($post->ID);
        if (! empty($ancestor)) {
            $parent_protect = $this->protect($ancestor[0]);
            $parent_levels  = array_merge($parent_levels, $this->get_content_levels(get_post_type($ancestor[0]), $ancestor[0], null, null, $immutable));
        }

        include $this->plugin_dir . '/admin/post_page_options/main.php';
    }

    /**
     * Save Post Page Options
     * Action: wp_insert_post, edit_attachment
     *
     * @used-by WishListMember3_Hooks::save_postpage_settings()
     *
     * @param integer $pid   Post ID         @since 3.7.
     * @param object  $xpost Post Object     @since 3.7.
     */
    public function save_post_page($pid = null, $xpost = null)
    {
        $post_data = wlm_post_data(true);

        switch (wlm_arrval($post_data, 'post_type')) {
            case 'page':
                $content_type = 'pages';
                break;
            case 'post':
                $content_type = 'posts';
                break;
            default:
                $content_type = isset($post_data['post_type']) ? $post_data['post_type'] : '';
        }

        /**
         * Save if set pass protection settings
         *
         * @since 3.7
         */
        $x = wlm_arrval($post_data, 'pass_content_protection');
        if ($x) {
            $this->special_content_level($post_data['post_ID'], 'Pass_Content_Protection', $x);
        }

        /**
         * Parent changed, find an ancestor that passes protection
         *
         * @since 3.7
         */
        if (
            'Y' !== wlm_arrval($post_data, 'wlm_inherit_protection') && // current post does not inherit.
            ( ! isset($post_data['wlm_old_post_parent']) || ! empty($post_data['wlm_old_post_parent']) ) && // old post parent is not empty.
            ! empty($xpost) && (int) wlm_arrval($post_data, 'post_ID') === (int) $xpost->ID && // $xpost is not empty $xpost is the current state of the post.
            (int) wlm_arrval($post_data, 'wlm_old_post_parent') !== (int) $xpost->post_parent // old post parent is not the same as the $xpost post parent.
        ) {
            $ancestors = get_post_ancestors($post_data['post_ID']);
            foreach ($ancestors as $ancestor) { // Find an ancestor that qualifies.
                // Ancestors that inherits protection are not qualified.
                if ($this->special_content_level($ancestor, 'Inherit')) {
                    continue;
                }
                if (
                    // And ancestor must be set to pass protection.
                    $this->special_content_level($ancestor, 'Protection') &&
                    // Ancestor must be protected.
                    $this->special_content_level($ancestor, 'Pass_Content_Protection')
                ) {
                    $post_data['wlm_inherit_protection'] = 'Y'; // Set inheritance to true and let protection inheritance take care of the rest.
                }
                break;
            }
        }

        if (wlm_arrval($post_data, 'wpm_protect') || wlm_arrval($post_data, 'wlm_inherit_protection')) {
            $this->pay_per_post($post_data['post_ID'], wlm_arrval($post_data, 'wlm_payperpost'));
            $this->free_pay_per_post($post_data['post_ID'], wlm_arrval($post_data, 'wlm_payperpost_free'));
            $this->special_content_level($post_data['post_ID'], 'PayPerPost_AfterLogin', wlm_arrval($post_data, 'wlm_payperpost_afterlogin'));

            // Specific system pages.
            $option_names = [
                'non_members_error_page_internal'     => 'non_members_error_page_internal_' . $post_data['post_ID'],
                'non_members_error_page'              => 'non_members_error_page_' . $post_data['post_ID'],
                'wrong_level_error_page_internal'     => 'wrong_level_error_page_internal_' . $post_data['post_ID'],
                'wrong_level_error_page'              => 'wrong_level_error_page_' . $post_data['post_ID'],
                'membership_cancelled_internal'       => 'membership_cancelled_internal_' . $post_data['post_ID'],
                'membership_cancelled'                => 'membership_cancelled_' . $post_data['post_ID'],
                'membership_expired_internal'         => 'membership_expired_internal_' . $post_data['post_ID'],
                'membership_expired'                  => 'membership_expired_' . $post_data['post_ID'],
                'membership_forapproval_internal'     => 'membership_forapproval_internal_' . $post_data['post_ID'],
                'membership_forapproval'              => 'membership_forapproval_' . $post_data['post_ID'],
                'membership_forconfirmation_internal' => 'membership_forconfirmation_internal_' . $post_data['post_ID'],
                'membership_forconfirmation'          => 'membership_forconfirmation_' . $post_data['post_ID'],
            ];

            // Save specific system pages.
            foreach (array_keys($option_names) as $index) {
                if ('_internal' === substr($index, -9)) {
                    continue;
                }
                $index_internal = $index . '_internal';
                $pdata_index    = isset($option_names[ $index ]) ? $option_names[ $index ] : '';
                $value          = isset($post_data[ $pdata_index ]) ? wlm_trim($post_data[ $pdata_index ]) : '';

                $pdata_index_internal = isset($option_names[ $index_internal ]) ? $option_names[ $index_internal ] : '';
                $value_internal       = isset($post_data[ $pdata_index_internal ]) ? (int) $post_data[ $pdata_index_internal ] : '';
                if (empty($value_internal) && empty($value)) {
                    $this->delete_option($pdata_index);
                    $this->delete_option($pdata_index_internal);
                } elseif ($value_internal > 0) {
                    $this->delete_option($pdata_index);
                    $this->save_option($pdata_index_internal, $value_internal);
                } else {
                    $this->save_option($pdata_index, $value);
                    $this->save_option($pdata_index_internal, $value_internal);
                }
            }

            // Content protection.
            $inherit_protection = isset($post_data['wlm_inherit_protection']) && 'Y' === $post_data['wlm_inherit_protection'];
            if ($inherit_protection) {
                $this->inherit_protection($post_data['post_ID']);
            } else {
                $this->special_content_level($post_data['post_ID'], 'Inherit', 'N');
                $this->protect($post_data['post_ID'] ?? 0, $post_data['wpm_protect'] ?? '');
                $this->set_content_levels($content_type, $post_data['post_ID'] ?? '', $post_data['wpm_access'] ?? []);
            }
            $this->pass_protection($post_data['post_ID'], 'categories' === $content_type);
        }

        // Comment protection wil be off for new post.
        if ('/wp-admin/post-new.php' === wlm_arrval($post_data, '_wp_http_referer')) {
            $oldlevels = $this->get_content_levels('comments', $id);
            $levels    = array_unique(array_merge($oldlevels, $post_data['wpm_access'] ? array_keys((array) $post_data['wpm_access']) : []));
            $this->set_content_levels('comments', $post_data['post_ID'], $levels);
        }
    }
}

// Register hooks.
add_action(
    'wishlistmember_register_hooks',
    function ($wlm) {
        add_action('admin_init', [$wlm, 'prepare_post_page_options'], 1);
        add_action('edit_attachment', [$wlm, 'save_post_page']);
        add_action('wp_insert_post', [$wlm, 'save_post_page'], 10, 2);
        add_filter('list_terms_exclusions', [$wlm, 'only_list_cats_for_level']);
        add_filter('the_comments', [$wlm, 'recent_comments'], 10, 2);
    }
);
