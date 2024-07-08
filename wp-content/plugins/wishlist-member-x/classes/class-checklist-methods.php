<?php

/**
 * Checklist Methods
 *
 * @package WishListMember
 */

namespace WishListMember;

/**
 * Member Methods trait
 */
trait Checklist_Methods
{
    /**
     * Display checklist on dashboard
     * Called by wishlistmember_dashboard_second_column_start action
     */
    public function dashboard_checklist()
    {
        include wishlistmember_instance()->plugin_dir3 . '/ui/admin_screens/dashboard/checklist/checklist.php';
    }

    /**
     * Marks dashboard page checklist as checked.
     * Called by `save_post` hook.
     *
     * @param integer $post_id Post ID.
     */
    public function checklist_dashboard_page_updated($post_id)
    {
        $post_id = (int) $post_id;
        if ((int) wishlistmember_instance()->get_option('wizard/membership-pages/dashboard/configure') === $post_id) {
            wishlistmember_instance()->save_option('checklist/done/customize-member-dashboard-page', $post_id);
        }
    }

    /**
     * Marks onboarding page checklist as checked.
     * Called by `save_post` hook.
     *
     * @param integer $post_id Post ID.
     */
    public function checklist_onboarding_page_updated($post_id)
    {
        $post_id = (int) $post_id;
        if ((int) wishlistmember_instance()->get_option('wizard/membership-pages/onboarding/configure') === $post_id) {
            wishlistmember_instance()->save_option('checklist/done/customize-member-welcome-page', $post_id);
        }
    }

    /**
     * Marks free registration page checklist as checked.
     * Called by `save_post` hook.
     *
     * @param integer $post_id Post ID.
     */
    public function checklist_free_registration_page_updated($post_id)
    {
        $post_id = (int) $post_id;
        if (get_post_meta($post_id, 'wishlist-member/wizard/pages/registration/free')) {
            wishlistmember_instance()->save_option('checklist/done/customize-member-free-registration', $post_id);
        }
    }

    /**
     * Marks paid registration page checklist as checked.
     * Called by `save_post` hook.
     *
     * @param integer $post_id Post ID.
     */
    public function checklist_paid_registration_page_updated($post_id)
    {
        $post_id = (int) $post_id;
        if (get_post_meta($post_id, 'wishlist-member/wizard/pages/registration/paid')) {
            wishlistmember_instance()->save_option('checklist/done/customize-member-paid-registration', $post_id);
        }
    }

    /**
     * Save display state of dashboard checklist.
     * Called by 'wp_ajax_wishlistmember_toggle_dashboard_checklist' action.
     */
    public function toggle_dashboard_checklist()
    {
        $key   = wlm_or(wlm_post_data()['key'], 'dashboard_checklist_closed');
        $value = 'hide' === wlm_post_data()['value'] ? 'closed' : 'opened';
        wishlistmember_instance()->save_option($key, $value);
        wp_send_json_success();
    }

    /**
     * Generate checklist item mark-up
     *
     * @param array $args {
     *     Checklist item arguments.
     *
     * @type   string  $label             Label.
     * @type   boolean $status_done       Done status.
     * @type   boolean $status_archived   Archived status.
     * @type   string  $link_config       Config link.
     * @type   string  $link_config_text  Config link text.
     * @type   string  $link_info         Info link.
     * @type   string  $link_info_text    Info link text.
     * @type   boolean $auto_done         False.
     * @type   integer $importance        Sort order importance. Lower numbers have higher importance.
     * }
     * @return string Checklist item mark-up
     */
    public function generate_checklist_item_markup($args)
    {
        static $default_importance = PHP_INT_MAX - 100000;
        $defaults                  = [
            'label'            => '',
            'id'               => '',
            'status_done'      => false,
            'link_config'      => '',
            'link_config_text' => esc_html__('Configure', 'wishlist-member'),
            'link_info'        => '',
            'link_info_text'   => esc_html__('More Info', 'wishlist-member'),
            'auto_done'        => false,
            'importance'       => 0,
        ];
        $item                      = array_map('wlm_trim', wp_parse_args($args, $defaults));
        if (empty($item['importance'])) {
            $item['importance'] = $default_importance++;
        }
        if (! $item['label']) {
            return '';
        }
        if (! $item['id']) {
            return '';
        }

        // Save done status as 0 if no checklist entry yet in the database.
        $done = wishlistmember_instance()->get_option('checklist/done/' . $item['id']);
        if (false === $done) {
            $done = 0;
            wishlistmember_instance()->save_option('checklist/done/' . $item['id'], 0);
        }
        $item['status_done'] = (bool) ( $item['auto_done'] ? $item['status_done'] : $done );
        // Update database done status if database value doesn't match final value.
        if (empty($done) && $item['status_done']) {
            wishlistmember_instance()->save_option('checklist/done/' . $item['id'], 1);
        }
        // Archived status.
        $item['status_archived'] = (bool) wishlistmember_instance()->get_option('checklist/archived/' . $item['id']);
        $html                    = sprintf(
            '<h4 data-importance="%d" id="%s" class="checklist-item font-weight-normal button-hover mb-2 %s %s"><a class="btn -icon-only -outline -bare mr-1 checklist-checkbox %s" title="%s" href="#" style="height: 24px; width: 24px; padding: 2px 0 0 0 !important;"><i class="wlm-icons md-18 checklist-done">check</i></a> <span class="align-middle">%s</span> &nbsp; <span class="align-middle">%s%s</span><span class="pull-right btn-group-action">
			<a href="#" title="%s" class="btn -icon-only -bare h-auto w-auto p-0 checklist-archive-btn checklist-archive"><i class="wlm-icons">close</i></a>
			<a href="#" title="%s" class="btn -icon-only -bare h-auto w-auto p-0 checklist-archive-btn checklist-restore"><i class="wlm-icons">outline_unarchive</i></a></span></h4>',
            esc_attr($item['importance']),
            esc_attr(sanitize_key($item['id'])),
            $item['status_done'] ? 'checklist-done' : '',
            $item['status_archived'] ? 'checklist-archived' : '',
            $item['auto_done'] ? 'checklist-checkbox-disabled' : '',
            $item['auto_done'] ? esc_html__('Item is checked or unchecked automatically based on if the action has been completed or not.', 'wishlist-member') : '',
            $item['link_config'] ? sprintf('<a href="%s" title="%s" target="_blank">%s</a>', esc_url($item['link_config']), esc_attr($item['link_config_text']), esc_html($item['label'])) : esc_html($item['label']),
            $item['link_info'] ? sprintf('<a href="%s" title="%s" class="btn -icon-only -bare h-auto w-auto p-0" target="_blank" style="color: #3E7CB1"><i class="wlm-icons">info_outline</i></a>', esc_url($item['link_info']), esc_attr($item['link_info_text'])) : '',
            $item['link_config'] ? sprintf('<a href="%s" title="%s" class="btn -icon-only -bare h-auto w-auto p-0" target="_blank" style="color: #3E7CB1"><i class="wlm-icons">settings</i></a>', esc_url($item['link_config']), esc_attr($item['link_config_text'])) : '',
            esc_attr__('Skip'),
            esc_attr__('Restore')
        );
        return $html;
    }

    /**
     * Print checklist markup
     *
     * @param string|array $html_or_args Markup or arguments. See Wizard_Methods::generate_checklist_item_markup() for valid array parameter.
     */
    public function print_checklist_item($html_or_args)
    {
        static $called = false;
        $called || $this->checklist_scripts_and_styles();
        $called = true;
        if (is_array($html_or_args)) {
            $html_or_args = $this->generate_checklist_item_markup($html_or_args);
        }
        echo wp_kses_post($html_or_args);
    }

    /**
     * Print checklist inline scripts and styles
     */
    public function checklist_scripts_and_styles()
    {
        wp_enqueue_style('wlm-checklist', wishlistmember_instance()->plugin_url3 . '/ui/admin_screens/dashboard/checklist/assets/checklist.css', [], WLM_PLUGIN_VERSION);
        wp_enqueue_script('wlm-checklist', wishlistmember_instance()->plugin_url3 . '/ui/admin_screens/dashboard/checklist/assets/checklist.js', 'jquery', WLM_PLUGIN_VERSION, true);
        wp_doing_ajax() && wp_print_styles('wlm-checklist');
        wp_doing_ajax() && wp_print_scripts('wlm-checklist');
    }

    /**
     * Toggle checklist status
     * Called by 'wp_ajax_wishlistmember_toggle_checklist_done' action
     * Called by 'wp_ajax_wishlistmember_toggle_checklist_archived' action
     *
     * @param  string $name   Status name.
     * @param  mixed  $status Truish/Falsish status value.
     * @param  string $id     Item ID.
     * @return null|boolean
     */
    public function toggle_checklist_status($name = '', $status = '', $id = '')
    {
        if (wp_doing_ajax()) {
            $status = wlm_post_data()['status'];
            $id     = wlm_post_data()['id'];
        }
        switch (current_action()) {
            case 'wp_ajax_wishlistmember_toggle_checklist_done':
                $name = 'done';
                break;
            case 'wp_ajax_wishlistmember_toggle_checklist_archived':
                $name = 'archived';
                break;
        }
        if (empty($name) || empty($id)) {
            wp_doing_ajax() && wp_send_json_error();
            return false;
        }

        $id = 'checklist/' . $name . '/' . wlm_or($id, wlm_post_data()['id']);
        wishlistmember_instance()->save_option($id, $status ? 1 : 0);
        wp_doing_ajax() && wp_send_json_success();
        return true;
    }
}

// Register hooks.
add_action(
    'wishlistmember_register_hooks',
    function ($wlm) {
        add_action('wishlistmember_dashboard_second_column_start', [$wlm, 'dashboard_checklist']);
        add_action('save_post', [$wlm, 'checklist_free_registration_page_updated']);
        add_action('save_post', [$wlm, 'checklist_paid_registration_page_updated']);
        add_action('save_post', [$wlm, 'checklist_dashboard_page_updated']);
        add_action('save_post', [$wlm, 'checklist_onboarding_page_updated']);
        add_action('wp_ajax_wishlistmember_toggle_dashboard_checklist', [$wlm, 'toggle_dashboard_checklist']);
        add_action('wp_ajax_wishlistmember_toggle_checklist_done', [$wlm, 'toggle_checklist_status']);
        add_action('wp_ajax_wishlistmember_toggle_checklist_archived', [$wlm, 'toggle_checklist_status']);
    }
);
