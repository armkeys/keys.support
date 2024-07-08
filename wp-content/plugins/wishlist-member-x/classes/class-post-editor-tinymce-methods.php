<?php

/**
 * Post Editor Tinymce Methods Feature
 *
 * @package WishListMember
 */

namespace WishListMember;

/**
 * Post Editor Tinymce Methods trait
 */
trait Post_Editor_Tinymce_Methods
{
    /**
     * Inserts WishList Member Button on tinymce editor
     */
    public function tmce_insert_button()
    {
        // On the post area only.
        $pagenow = $GLOBALS['pagenow'];
        // Add the button when editing or adding post.
        if ('post.php' !== $pagenow && 'post-new.php' !== $pagenow) {
            return false;
        }
        // For users who can edit only.
        if (! current_user_can('edit_posts') && ! current_user_can('edit_pages')) {
            return false;
        }
        // For rich editing only.
        if ('true' === get_user_option('rich_editing')) {
            add_filter('mce_external_plugins', [&$this, 'tnmce_register_plugin']);
            add_filter('tiny_mce_before_init', [&$this, 'tnmce_register_button']);
        }
    }

    /**
     * Add the plugin button on tinymce menu
     *
     * @param array $in Array of all buttons in tinymce editor.
     */
    public function tnmce_register_button($in)
    {
        // Where would you like to put the new dropdown?
        $advance_button_place = 1; // 1,2,3,4.
        $key                  = 'theme_advanced_buttons' . $advance_button_place;
        $holder               = explode(',', $in[ $key ]);
        $holder[]             = 'wlm_shortcodes'; // Add our plugin on the menu.
        $in[ $key ]           = implode(',', $holder);
        return $in;
    }

    /**
     * Register our Tinymce Plugin
     *
     * @param array $plugin_array Array of registered tinymce plugins.
     */
    public function tnmce_register_plugin($plugin_array)
    {
        $url                            = admin_url('admin.php') . '?WLMTNMCEPlugin=1';
        $plugin_array['wlm_shortcodes'] = $url;
        return $plugin_array;
    }

    /**
     * Ganerate JS Code for WishList Member Tinymce Plugin
     *
     * @param string  $title       The title of tinymce plugin.
     * @param string  $plugin_name The name of tinymce plugin.
     * @param integer $max_width   The width of tinymce plugin.
     */
    public function tnmce_generate_plugin($title, $plugin_name, $max_width)
    {
        header('Content-type: text/javascript');
        if (! current_user_can('edit_posts') && ! current_user_can('edit_pages')) {
            exit(0);
        }

        $shortcodes = "\n";
        $icon_path  = $this->plugin_url . '/images/WishListIcon.png';
        foreach ($this->WLPShortcodes as $wlp_shortcodes) {
            // For the Title.
            if ($wlp_shortcodes['name']) {
                $shortcodes .= "sub = m.addMenu({title : '{$wlp_shortcodes['name']}'})\n";
            }
            // For shortcodes.
            if ($wlp_shortcodes['shortcode']) {
                $shortcodes .= "sub2 = sub.addMenu({title : 'Shortcodes'})\n";
                foreach ($wlp_shortcodes['shortcode'] as $index => $scode) {
                    $shortcodes .= "sub2.add({title : '{$index}', onclick : function() {\n";
                    $shortcodes .= "  tinyMCE.activeEditor.execCommand('mceInsertContent', false, '{$scode}');\n";
                    $shortcodes .= "}});\n";
                }
            }
            // For mergecodes.
            if ($wlp_shortcodes['mergecode']) {
                ;
                $shortcodes .= "sub2 = sub.addMenu({title : 'Mergecodes'})\n";
                foreach ($wlp_shortcodes['mergecode'] as $index => $scode) {
                    $scode2      = substr_replace($scode, '/', 1, 0);
                    $shortcodes .= "sub2.add({title : '{$index}', onclick : function() {\n";
                    $shortcodes .= "  var t = tinyMCE.activeEditor.selection.getContent();\n";
                    $shortcodes .= "  t = t != '' ? '{$scode }' +t +'{$scode2}' : '';\n";
                    $shortcodes .= "  tinyMCE.activeEditor.selection.setContent(t);\n";
                    $shortcodes .= "}});\n";
                }
            }
        }
        printf(
            'tinymce.create("tinymce.plugins.%1$s", {'
            . '	createControl: function(n, cm) {'
            . '		switch (n) {'
            . '			case "%1$s":'
            . '				var c = cm.createMenuBxutton("%1$s", {'
            . '					title : "%2$s",'
            . '					image : "%3$s",'
            . '					icons : false'
            . '				});'
            . '				c.onRenderMenu.add(function(c, m) {'
            . '					var sub;'
            . '					m.settings["max_width"] = {$max_width};'
            . '					//add our shortcodes.'
            . '					{$shortcodes}'
            . '				});'
            . '				// Return the new menu button instance.'
            . '				return c;'
            . '		}'
            . '		return null;'
            . '	}'
            . '});'
            . '// Register plugin with a short name'
            . 'tinymce.PluginManager.add( "%1$s", tinymce.plugins.%1$s );',
            esc_html($plugin_name),
            esc_html($title),
            esc_url($icon_path)
        );
    }
    /**
     * Adds WP Editor TinyMCE ligbox content
     */
    public function add_editor_light_box_markup()
    {
        global $current_screen;
        if ('post' !== $current_screen->base) {
            return;
        }
        $page = isset(wlm_get_data()['page']) ? wlm_get_data()['page'] : '';
        if (current_user_can('edit_posts') && current_user_can('edit_pages') && is_admin()) {
            wishlistmember_instance()->wlmshortcode->enqueue_shortcode_inserter_js();
            include wishlistmember_instance()->plugin_dir3 . '/helpers/tinymce-shortcode-inserter-lightbox.php';
        }
    }
}

// Register hooks.
add_action(
    'wishlistmember_register_hooks',
    function ($wlm) {
        add_action('admin_footer', [$wlm, 'add_editor_light_box_markup']);
    }
);
