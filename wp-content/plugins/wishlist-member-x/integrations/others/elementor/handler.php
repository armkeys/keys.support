<?php

/*
 * Elementor Integration File
 * Elementor Site: http://learndash.com/
 * Original Integration Author : Fel Jun Palawan
 * Version: $Id$
 */
use Elementor\Element_Base;

if (! class_exists('WLM_OTHER_INTEGRATION_ELEMENTOR')) {

    class WLM_OTHER_INTEGRATION_ELEMENTOR
    {
        private $settings        = [];
        public $elementor_active = false;

        public function __construct()
        {
            // Check if Elementor LMS is active.
            $active_plugins = wlm_get_active_plugins();
            if (in_array('Elementor', $active_plugins) || isset($active_plugins['elementor/elementor.php']) || is_plugin_active('elementor/elementor.php')) {
                $this->elementor_active = true;
            }
        }

        public function load_hooks()
        {
            if ($this->elementor_active) {
                // Add element section in Advance Tab.
                add_action('elementor/element/common/_section_style/after_section_end', [$this, 'add_wlm_controls'], 1, 2);
                add_action('elementor/element/section/section_advanced/after_section_end', [$this, 'add_wlm_controls'], 1, 2);
                add_action('elementor/element/container/section_layout/after_section_end', [$this, 'add_wlm_controls'], 1, 2);

                // Do the filter to implement the conditions.
                add_filter('elementor/frontend/widget/should_render', [$this, 'do_wlm_condition'], 999, 2);
                add_filter('elementor/frontend/section/should_render', [$this, 'do_wlm_condition'], 999, 2);
                add_filter('elementor/frontend/container/should_render', [$this, 'do_wlm_condition'], 999, 2);
            }
        }

        public function do_wlm_condition($should_render, Element_Base $element)
        {
            static $current_user;
            static $user_levels;
            if (! $element) {
                return $should_render;
            }
            if (! function_exists('wlmapi_get_member_levels')) {
                return $should_render;
            }

            $settings = $element->get_settings();

            if (is_null($current_user)) {
                $current_user = wp_get_current_user();
            }

            if (isset($current_user->ID) && $current_user->ID) {
                if (is_null($user_levels)) {
                    $user_levels = wlmapi_get_member_levels($current_user->ID);
                    $user_levels = ! is_array($user_levels) ? [] : array_keys(
                        array_filter(
                            $user_levels,
                            function ($user_levels) {
                                return $user_levels->Active;
                            }
                        )
                    );
                }
            }
            if (! is_array($user_levels)) {
                $user_levels = [];
            }

            $condition_list = ['all', 'nonmembers', 'logged_in', 'ina', 'notin'];
            $condition_type = isset($settings['wlm_level_condition']) ? $settings['wlm_level_condition'] : 'all';
            $condition_type = ! in_array($condition_type, $condition_list) ? 'all' : $condition_type;

            $section_level = isset($settings['wlm_level']) ? $settings['wlm_level'] : [];

            $display = true;
            if ('nonmembers' === $condition_type) {
                // Nonmembers means not logged-in.
                $should_render = empty($current_user->ID);
            } elseif ('logged_in' === $condition_type) {
                // Logged-in.
                $should_render = ! empty($current_user->ID);
            } elseif ('ina' === $condition_type) {
                // Member logged-in and in a level.
                if (empty($current_user->ID)) {
                    $should_render = false;
                } else {
                    $section_level = ! is_array($section_level) ? (array) $section_level : $section_level;
                    $in_levels     = array_intersect($section_level, $user_levels);
                    if (! empty($section_level) && count($in_levels) <= 0) {
                        $should_render = false;
                    }
                }
            } elseif ('notin' === $condition_type) {
                // Member logged-in and not in levels.
                if (empty($current_user->ID)) {
                    $should_render = false;
                } else {
                    $section_level = ! is_array($section_level) ? (array) $section_level : $section_level;
                    $in_levels     = array_intersect($section_level, $user_levels);
                    if (( ! empty($section_level) && count($in_levels) >= 1 )) {
                        $should_render = false;
                    }
                }
            }
            return $should_render;
        }

        public function add_wlm_controls($element)
        {
            if (function_exists('wlmapi_get_levels')) {
                $el_type = $element->get_type();
                $levels  = wlmapi_get_levels();
                $levels  = isset($levels['levels']['level']) ? $levels['levels']['level'] : [];

                $level_options = [];
                foreach ($levels as $key => $value) {
                    $level_options[ $value['id'] ] = $value['name'];
                }

                $element->start_controls_section(
                    'custom_section',
                    [
                        'tab'   => \Elementor\Controls_Manager::TAB_ADVANCED,
                        'label' => __('WishList Member', 'wishlist-member-elements'),
                    ]
                );
                $element->add_control(
                    'wlm_level_condition',
                    [
                        // Translators: 1: Element type.
                        'label'       => sprintf(__('Show this %s to:', 'wishlist-member-elements'), $el_type),
                        'label_block' => true,
                        'type'        => \Elementor\Controls_Manager::SELECT,
                        'default'     => 'all',
                        'options'     => [
                            'all'        => __('Everybody', 'wishlist-member'),
                            'nonmembers' => __('Not Logged-in', 'wishlist-member'),
                            'logged_in'  => __('Logged-in', 'wishlist-member'),
                            'ina'        => __('Members in Membership Level(s)', 'wishlist-member'),
                            'notin'      => __('Members not in Membership Level(s)', 'wishlist-member'),
                        ],
                    ]
                );
                $element->add_control(
                    'wlm_level',
                    [
                        'label'       => __('Membership Level(s)', 'wishlist-member-elements'),
                        'show_label'  => false,
                        'label_block' => true,
                        'type'        => \Elementor\Controls_Manager::SELECT2,
                        'multiple'    => true,
                        'options'     => $level_options,
                        'condition'   => [
                            'wlm_level_condition' => ['ina', 'notin'],
                        ],
                    ]
                );

                $element->end_controls_section();
            }
        }
    }
}

$WLMElementorInstance = new WLM_OTHER_INTEGRATION_ELEMENTOR();
$WLMElementorInstance->load_hooks();
