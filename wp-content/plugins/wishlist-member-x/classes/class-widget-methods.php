<?php

/**
 * Widget Methods
 *
 * @package WishListMember
 */

namespace WishListMember;

/**
 * Widget Methods trait
 */
trait Widget_Methods
{
    /**
     * Register the WishList Member Widget
     */
    public function wishlist_widget_register_widgets()
    {
        register_widget('WishListWidget');
    }

    /**
     * Create the WishList Member Widget
     *
     * @param  array $args Widget parameters.
     * @return null|\WishListWidget
     */
    public function widget($args = [], $return = false)
    {
        $args = (array) $args;
        if ($return) {
            $args['return'] = true;
        }
        $x = new \WishListWidget();
        if ($return) {
            return $x->widget($args, []);
        } else {
            $x->widget($args, []);
        }
    }

    /**
     * Migrate old WishList Member widgets
     *
     * Added in WLM 2.9, this will update the previous
     * WLM widget (registered through wp_register_sidebar_widget)
     * to the new WLMWidget Class if it's currently active
     * on the clients widgets
     */
    public function migrate_widget()
    {

        $active_widgets = get_option('sidebars_widgets');

        foreach ((array) $active_widgets as $widget => $values) {
            if ('array_version' !== $widget) {
                $counter = 0;
                foreach ((array) $values as $value) {
                    if ('wishlist-member' === $value) {
                        $active_widgets[ $widget ][ $counter ] = 'wishlistwidget-' . 1;
                        $wlm_widget_content[1]                 = [
                            'title'                   => $this->get_option('widget_title'),
                            'title2'                  => $this->get_option('widget_title2'),
                            'wpm_widget_hiderss'      => $this->get_option('widget_hiderss'),
                            'wpm_widget_hideregister' => $this->get_option('widget_hideregister'),
                            'wpm_widget_nologinbox'   => $this->get_option('widget_nologinbox'),
                            'wpm_widget_hidelevels'   => $this->get_option('widget_hidelevels'),
                            'wpm_widget_fieldwidth'   => $this->get_option('widget_fieldwidth'),
                        ];
                        update_option('widget_wishlistwidget', $wlm_widget_content);
                    }
                    ++$counter;
                }
            }
        }
        update_option('sidebars_widgets', $active_widgets);
    }
}

// Register hooks.
add_action(
    'wishlistmember_register_hooks',
    function ($wlm) {
        global $wp_version;
        version_compare($wp_version, '2.8', '>=') && add_action('widgets_init', [$wlm, 'wishlist_widget_register_widgets']);
    }
);
