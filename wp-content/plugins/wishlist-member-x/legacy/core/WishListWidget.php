<?php

if (! class_exists('WishListWidget')) {
    class WishListWidget extends WP_Widget
    {
        public function __construct()
        {
            parent::__construct('WishListWidget', 'WishList Member', ['description' => 'WishList Member.']);
        }

        // Widget main.
        public function widget($args = [], $instance = [])
        {

            global $WishListMemberInstance;
            wp_enqueue_style('wishlistmember-frontend-styles-sidebar_widget');
            wp_enqueue_style('wishlistmember-frontend-styles-combined');

            // If $instance is false then this means that the widget is being called directly by other functions.
            // We then set the $instance.
            $is_active = is_active_widget(false, false, 'wishlistwidget', true);

            if (! $instance) {
                // If there are active WLM widgets, we get the settings of the first one.
                if ($is_active) {
                    $wlm_widget_settings = get_option('widget_wishlistwidget');

                    foreach ((array) $wlm_widget_settings as $setting) {
                        $instance['wpm_widget_hiderss']      = $setting['wpm_widget_hiderss'];
                        $instance['wpm_widget_hideregister'] = $setting['wpm_widget_hideregister'];
                        $instance['wpm_widget_nologinbox']   = $setting['wpm_widget_nologinbox'];
                        $instance['wpm_widget_hidelevels']   = $setting['wpm_widget_hidelevels'];
                        $instance['wpm_widget_fieldwidth']   = $setting['wpm_widget_fieldwidth'];

                        break;
                    }
                } else {
                    // Check if there's an inactive wishlistwidget.
                    $is_there_an_inactive = is_active_widget(false, false, 'wishlistwidget', false);

                    if ($is_there_an_inactive) {
                        // Check if our wlm_wishlistwidge has data.
                        $wlm_widget_settings = get_option('widget_wishlistwidget');
                        if (! empty($wlm_widget_settings)) {
                            foreach ((array) $wlm_widget_settings as $setting) {
                                $instance['wpm_widget_hiderss']      = $setting['wpm_widget_hiderss'];
                                $instance['wpm_widget_hideregister'] = $setting['wpm_widget_hideregister'];
                                $instance['wpm_widget_nologinbox']   = $setting['wpm_widget_nologinbox'];
                                $instance['wpm_widget_hidelevels']   = $setting['wpm_widget_hidelevels'];
                                $instance['wpm_widget_fieldwidth']   = $setting['wpm_widget_fieldwidth'];

                                break;
                            }
                        } else {
                            // If no WLM widget settings set then set default settings.
                            $instance['wpm_widget_hiderss']      = 0;
                            $instance['wpm_widget_hideregister'] = 0;
                            $instance['wpm_widget_nologinbox']   = 0;
                            $instance['wpm_widget_hidelevels']   = 0;
                            $instance['wpm_widget_fieldwidth']   = 0;
                        }
                    } else {
                        // If no WLM widget inactive then set default settings.
                        $instance['wpm_widget_hiderss']      = 0;
                        $instance['wpm_widget_hideregister'] = 0;
                        $instance['wpm_widget_nologinbox']   = 0;
                        $instance['wpm_widget_hidelevels']   = 0;
                        $instance['wpm_widget_fieldwidth']   = 0;
                    }
                }
            }

            extract($args);
            $return           = isset($return) ? $return : false;
            $wpm_levels       = $WishListMemberInstance->get_option('wpm_levels');
            $wpm_current_user = wp_get_current_user();
            if (1 != $instance['wpm_widget_nologinbox'] || $wpm_current_user->ID) {
                if (! wlm_get_data()['reg']) {
                    $output = '';
                    if (! $return) {
                        echo wp_kses_post($before_widget . $before_title);
                        if ($wpm_current_user->ID) {
                            if (isset($args['title'])) {
                                echo esc_html($args['title']);
                            } else {
                                echo esc_html($instance['title']);
                            }
                        } else {
                            if (isset($args['title2'])) {
                                echo esc_html($args['title2']);
                            } else {
                                echo esc_html($instance['title2']);
                            }
                        }
                        echo wp_kses_post($after_title);
                        echo "<div id='wlmember_loginwidget'>";
                    }
                    if ($wpm_current_user->ID) {
                        $name = $wpm_current_user->first_name;
                        if (
                            ! $name
                        ) {
                            $name = $wpm_current_user->user_nicename;
                        }
                        if (
                            ! $name
                        ) {
                            $name = $wpm_current_user->user_login;
                        }
                        // Translators: 1: Name (can be first name, nice name or login)
                        $output        .= '<p>' . trim(sprintf(__('Welcome %1$s', 'wishlist-member'), $name)) . '</p>';
                        $levels         = $WishListMemberInstance->get_membership_levels($wpm_current_user->ID, null, null, null, true);
                        $inactivelevels = $WishListMemberInstance->get_member_inactive_levels($wpm_current_user->ID);
                        sort($levels); // <- we sort the levels.
                        if (! $instance['wpm_widget_hidelevels']) {
                            $clevels = count($levels);

                            $clevels_with_prio = [];

                            // Get level's priority.
                            foreach ($levels as $clvl) {
                                $clevels_with_prio[ $clvl ] = $wpm_levels[ $clvl ]['levelOrder'];
                            }

                            // Sort by priority.
                            asort($clevels_with_prio);

                            // Remove priority in array.
                            foreach ($clevels_with_prio as $key => $clevel_w_prio) {
                                $sorted_levels[] = $key;
                            }

                            if ($clevels) {
                                // $output.=__("&raquo; Level", "&raquo; Levels", $clevels, 'wishlist-member');
                                if (1 === (int) $clevels) {
                                    $output .= __('&raquo; Level', 'wishlist-member');
                                } else {
                                    $output .= __('&raquo; Levels', 'wishlist-member');
                                }

                                $output .= ': ';
                                if ($clevels > 1) {
                                    $output .= '<br /><div id="" style="margin-left:1em">';
                                }
                                $morelevels    = false;
                                $maxmorelevels = $return ? 1000000000 : 0;
                                for ($i = 0; $i < $clevels; $i++) {
                                    if ($i > $maxmorelevels && ! $morelevels) {
                                        $output    .= '<div id="wlm_morelevels" style="display:none">';
                                        $morelevels = true;
                                    }
                                    if (
                                        $clevels > 1
                                    ) {
                                        $output .= '&middot; ';
                                    }
                                    $strike = '';
                                    if (in_array($sorted_levels[ $i ], $inactivelevels)) {
                                        $output .= '<strike>';
                                        $strike  = '</strike>';
                                    }
                                    $output .= $wpm_levels[ $sorted_levels[ $i ] ]['name'];
                                    $output .= $strike;
                                    $output .= '<br />';
                                }
                                if ($morelevels) {
                                    $output .= '</div>';
                                    $output .= '&middot; <label style="display: inline; cursor:pointer;" data-morelevels="' . __('More levels', 'wishlist-member') . ' &lt;small&gt;&nabla;&lt;/small&gt;" data-lesslevels="' . __('Less levels', 'wishlist-member') . ' &lt;small&gt;&Delta;&lt;/small&gt;" onclick="wlmml=document.getElementById(\'wlm_morelevels\');wlmml.style.display=wlmml.style.display==\'none\'?\'block\':\'none\';this.innerHTML=wlmml.style.display==\'none\'?this.dataset.morelevels:this.dataset.lesslevels;this.blur()">' . esc_html__('More levels', 'wishlist-member') . ' <small>&nabla;</small></label>';
                                }
                                if ($clevels > 1) {
                                    $output .= '</div>';
                                }
                            }
                        }

                        if ($WishListMemberInstance->get_option('members_can_update_info')) {
                            $output .= '&raquo; <a href="' . get_edit_profile_url() . '">' . esc_html__('Membership Details', 'wishlist-member') . '</a><br />';
                        }
                        if (1 != $instance['wpm_widget_hiderss']) {
                            $output .= '&raquo; <a href="' . get_bloginfo('rss2_url') . '">' . esc_html__('RSS Feed', 'wishlist-member') . '</a><br />';
                        }
                        if (function_exists('wp_logout_url')) {
                            // $logout = wp_logout_url(get_bloginfo('url'));
                            $logout = wp_logout_url();
                            if ($WishListMemberInstance->get_option('enable_logout_redirect_override')) {
                                $logout = wp_nonce_url(site_url('wp-login.php?action=logout', 'login'), 'log-out');
                            };
                        } else {
                            // $logout = wp_nonce_url(site_url('wp-login.php?action=logout&redirect_to=' . urlencode(get_bloginfo('url')), 'login'), 'log-out');
                            $logout = wp_nonce_url(site_url('wp-login.php?action=logout', 'login'), 'log-out');
                        }
                        $output .= '&raquo; <a href="' . esc_url($logout) . '">' . esc_html__('Logout', 'wishlist-member') . '</a><br />';
                        if ($return) {
                            return $output;
                        }
                        echo wp_kses(
                            $output,
                            [
                                'a'     => ['href' => true],
                                'br'    => [],
                                'div'   => [
                                    'id'    => true,
                                    'style' => true,
                                ],
                                'label' => [
                                    'style'           => true,
                                    'onclick'         => true,
                                    'data-morelevels' => true,
                                    'data-lesslevels' => true,
                                ],
                                'p'     => [],
                                'small' => [],
                            ]
                        );
                    } else {
                        $register          = wishlistmember_instance()->non_members_url();
                        $widget_fieldwidth = (int) $instance['wpm_widget_fieldwidth'];
                        $login_url         = esc_url(site_url('wp-login.php', 'login_post'));
                        if (
                            ! $widget_fieldwidth
                        ) {
                            $widget_fieldwidth = 15;
                        }

                        echo '<form method="post" action="' . esc_url($login_url) . '"><p>' . esc_html__('You are not currently logged in.', 'wishlist-member') . '</p>';
                        echo '<span class="wlmember_loginwidget_input_username_holder"><label>' . esc_html__('Username or Email Address', 'wishlist-member') . ':</label><br /><input class="wlmember_loginwidget_input_username"  type="text" name="log" size="' . esc_attr($widget_fieldwidth) . '" /></span><br />';
                        echo '<span class="wlmember_loginwidget_input_password_holder"><label>' . esc_html__('Password', 'wishlist-member') . ':</label><br /><span class="wishlist-member-login-password"><input class="wlmember_loginwidget_input_password" type="password" name="pwd" size="' . esc_attr($widget_fieldwidth) . '" /><a href="#" class="dashicons dashicons-visibility" aria-hidden="true"></a></span></span><br />';
                        echo '<span class="wlmember_loginwidget_input_checkrememberme_holder"><input  class="wlmember_loginwidget_input_checkrememberme" type="checkbox" name="rememberme" value="forever" /> <label>' . esc_html__('Remember Me', 'wishlist-member') . '</label></span><br />';
                        echo '<span class="wlmember_loginwidget_input_submit_holder"><input class="wlmember_loginwidget_input_submit" type="submit" name="wp-submit" value="' . esc_html__('Login', 'wishlist-member') . '" /></span><br /><br />';
                        if ($WishListMemberInstance->get_option('show_onetime_login_option')) {
                            echo '<p class="wlmember_loginwidget_otl_request"><a href="' . esc_url(add_query_arg('action', 'wishlistmember-otl', wp_login_url())) . '">' . esc_html(wishlistmember_instance()->get_option('onetime_login_link_label')) . '</a></p>';
                        }
                        if (1 != $instance['wpm_widget_hideregister']) {
                            echo '<span class="wlmember_loginwidget_link_register_holder">&raquo; <a href="' . esc_url($register) . '">' . esc_html__('Register', 'wishlist-member') . '</a></span><br />';
                        }
                        echo '<span class="wlmember_loginwidget_link_lostpassword_holder">&raquo; <a href="' . esc_url(wp_lostpassword_url()) . '">' . esc_html__('Lost your Password?', 'wishlist-member') . '</a></span>';
                        if ($WishListMemberInstance->get_option('enable_login_redirect_override')) {
                            $redirect = ! empty(wlm_get_data()['wlfrom']) ? esc_attr(stripslashes(wlm_get_data()['wlfrom'])) : 'wishlistmember';
                        } else {
                            $redirect = '';
                        }
                        echo '<input type="hidden" name="wlm_redirect_to" value="' . esc_attr($redirect) . '" />';
                        echo '<input type="hidden" name="redirect_to" value="' . esc_attr($redirect) . '" /></form>';
                    }
                    if (! $return) {
                        echo '</div>';
                        echo wp_kses_post($after_widget);
                    }
                }
            }
        }

        public function form($instance)
        {

            $title             = ! empty($instance['title']) ? $instance['title'] : '';
            $title2            = ! empty($instance['title2']) ? $instance['title2'] : '';
            $rsschecked        = $instance['wpm_widget_hiderss'] ? ' checked ' : '';
            $registerchecked   = $instance['wpm_widget_hideregister'] ? ' checked ' : '';
            $nologinboxchecked = $instance['wpm_widget_nologinbox'] ? ' checked ' : '';
            $hidelevelschecked = $instance['wpm_widget_hidelevels'] ? ' checked ' : '';
            $widget_fieldwidth = (int) $instance['wpm_widget_fieldwidth'];
            if (
                ! $widget_fieldwidth
            ) {
                $widget_fieldwidth = 15;
            }

            ?>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_html_e('Title when logged in::', 'wishlist-member'); ?></label>
                <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
            </p>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('title2')); ?>"><?php esc_html_e('Title when logged out::', 'wishlist-member'); ?></label>
                <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title2')); ?>" name="<?php echo esc_attr($this->get_field_name('title2')); ?>" type="text" value="<?php echo esc_attr($title2); ?>">
            </p>
            <?php
                echo '<p><b>' . esc_html__('Advanced Options', 'wishlist-member') . '</b></p>';
            ?>
            <p>
                <input id="<?php echo esc_attr($this->get_field_id('wpm_widget_hiderss')); ?>" name="<?php echo esc_attr($this->get_field_name('wpm_widget_hiderss')); ?>" type="checkbox" value="1" <?php echo esc_attr($rsschecked); ?> >
                <label for="<?php echo esc_attr($this->get_field_id('wpm_widget_hiderss')); ?>"><?php esc_html_e('Hide RSS Link', 'wishlist-member'); ?></label>
            </p>
            <p>
                <input id="<?php echo esc_attr($this->get_field_id('wpm_widget_hideregister')); ?>" name="<?php echo esc_attr($this->get_field_name('wpm_widget_hideregister')); ?>" type="checkbox" value="1" <?php echo esc_attr($registerchecked); ?> >
                <label for="<?php echo esc_attr($this->get_field_id('wpm_widget_hideregister')); ?>"><?php esc_html_e('Hide Register Link', 'wishlist-member'); ?></label>
            </p>
            <p>
                <input id="<?php echo esc_attr($this->get_field_id('wpm_widget_nologinbox')); ?>" name="<?php echo esc_attr($this->get_field_name('wpm_widget_nologinbox')); ?>" type="checkbox" value="1" <?php echo esc_attr($nologinboxchecked); ?> >
                <label for="<?php echo esc_attr($this->get_field_id('wpm_widget_nologinbox')); ?>"><?php esc_html_e('Only display if member is logged in', 'wishlist-member'); ?></label>
            </p>

                <input id="<?php echo esc_attr($this->get_field_id('widget_hidelevels')); ?>" name="<?php echo esc_attr($this->get_field_name('wpm_widget_hidelevels')); ?>" type="checkbox" value="1" <?php echo esc_attr($hidelevelschecked); ?> >
                <label for="<?php echo esc_attr($this->get_field_id('widget_hidelevels')); ?>"><?php esc_html_e('Hide membership levels', 'wishlist-member'); ?></label>
            </p>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('wpm_widget_fieldwidth')); ?>"><?php esc_html_e('Width of Login Fields', 'wishlist-member'); ?></label> <br />
                <input id="<?php echo esc_attr($this->get_field_id('wpm_widget_fieldwidth')); ?>" name="<?php echo esc_attr($this->get_field_name('wpm_widget_fieldwidth')); ?>" size="4" type="text" value="<?php echo esc_attr($widget_fieldwidth); ?>">
            </p>
            <?php
        }

        public function update($new_instance, $old_instance)
        {
            $instance           = [];
            $instance['title']  = ( ! empty($new_instance['title']) ) ? strip_tags($new_instance['title']) : '';
            $instance['title2'] = ( ! empty($new_instance['title2']) ) ? strip_tags($new_instance['title2']) : '';

            $instance['wpm_widget_hiderss']      = ( ! empty($new_instance['wpm_widget_hiderss']) ) ? strip_tags($new_instance['wpm_widget_hiderss']) : '';
            $instance['wpm_widget_hideregister'] = ( ! empty($new_instance['wpm_widget_hideregister']) ) ? strip_tags($new_instance['wpm_widget_hideregister']) : '';
            $instance['wpm_widget_nologinbox']   = ( ! empty($new_instance['wpm_widget_nologinbox']) ) ? strip_tags($new_instance['wpm_widget_nologinbox']) : '';
            $instance['wpm_widget_hidelevels']   = ( ! empty($new_instance['wpm_widget_hidelevels']) ) ? strip_tags($new_instance['wpm_widget_hidelevels']) : '';
            $instance['wpm_widget_fieldwidth']   = ( ! empty($new_instance['wpm_widget_fieldwidth']) ) ? strip_tags($new_instance['wpm_widget_fieldwidth']) : '';

            return $instance;
        }
    }
}
