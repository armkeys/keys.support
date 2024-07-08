<?php

class WishListXhr
{
    public $wlm;
    public function __construct($wlm)
    {
        $this->wlm = $wlm;

        // Define ajax methods.
        add_action('wp_ajax_wlm_form_membership_level', [$this, 'form_membership_level']);
        add_action('wp_ajax_wlm_del_membership_level', [$this, 'del_membership_level']);
        add_action('wp_ajax_wlm_set_protection', [$this, 'set_protection']);
        add_action('wp_ajax_wlm_set_membership_content', [$this, 'set_membership_content']);
        add_action('wp_ajax_wlm_reorder_membership_levels', [$this, 'reorder_membership_levels']);
    }
    public function reorder_membership_levels()
    {
        $sorted     = wlm_post_data()['reorder'];
        $wpm_levels = $this->wlm->get_option('wpm_levels');

        foreach ($sorted as $lid => $i) {
            $wpm_levels[ $lid ]['levelOrder'] = $i;
        }

        $this->wlm->sort_levels($wpm_levels, 'a', 'levelOrder');
        $this->wlm->save_option('wpm_levels', $wpm_levels);
        wp_send_json($wpm_levels);
    }

    public function set_membership_content()
    {
        $this->wlm->save_membership_content($data);
    }
    public function set_protection()
    {
        $id = wlm_post_data()['id'];

        $result = [];
        foreach (wlm_post_data()['posts'] as $k => $val) {
            $status       = $this->wlm->protect($k, $val);
            $result[ $k ] = $status;
        }

        echo '(' . json_encode($result) . ')';
        die();
    }

    public function del_membership_level()
    {
        $id         = wlm_post_data()['id'];
        $wpm_levels = $this->wlm->get_option('wpm_levels');
        unset($wpm_levels[ $id ]);
        $this->wlm->save_option('wpm_levels', $wpm_levels);
    }
    public function form_membership_level($id)
    {
        ob_start();
        $id         = wlm_post_data()['id'];
        $wpm_levels = $this->wlm->get_option('wpm_levels');
        $level      = $wpm_levels[ $id ];

        $pages         = get_pages('exclude=' . implode(',', $this->wlm->exclude_pages([], true)));
        $pages_options = '';
        foreach ((array) $pages as $page) {
            $pages_options .= '<option value="' . $page->ID . '">' . $page->post_title . '</option>';
        }

        $roles = $GLOBALS['wp_roles']->roles;
        $caps  = [];
        foreach ((array) $roles as $key => $role) {
            if (( isset($role['capabilities']['level_10']) && $role['capabilities']['level_10'] ) || ( isset($role['capabilities']['level_9']) && $role['capabilities']['level_9'] ) || ( isset($role['capabilities']['level_8']) && $role['capabilities']['level_8'] )) {
                unset($roles[ $key ]);
            } else {
                list($roles[ $key ]) = explode('|', $role['name']);

                $caps[ $key ] = count($role['capabilities']);
            }
        }
        array_multisort($caps, SORT_ASC, $roles);

        include $this->wlm->plugin_dir . '/resources/forms/edit_membership_level.php';
        $str = ob_get_clean();
        echo wp_kses_post($str);
        die();
    }
}
