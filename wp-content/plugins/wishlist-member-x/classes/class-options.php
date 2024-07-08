<?php

/**
 * Options Trait file.
 *
 * @package WishListMember
 */

namespace WishListMember;

/**
 * Options trait
 */
trait Options
{
    /**
     * Retrieves an option's value
     *
     * @param  string  $option   Option name.
     * @param  boolean $dec      Truthy to decrypt the return value.
     * @param  boolean $no_cache Truthy to skip cache data.
     * @param  mixed   $default  Default value.
     * @return string           The option value
     */
    public function get_option($option, $dec = false, $no_cache = false, $default = false)
    {
        global $wpdb;

        $option = wlm_trim($option);
        if (empty($option)) {
            return false;
        }

        // Todo: this block of code needs to go somewhere else.
        if (wlm_get_data()['fv'] && 'FormVersion' === $option) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended.
            return wlm_arrval('lastresult');
        }

        /**
         * Filters the value of an option before it is retrieved.
         *
         * The dynamic portion of the hook name, `$option`, refers to the option name.
         *
         * Returning a truthy value from filter will effectively return its value right away
         * effectively short-circuiting this method.
         *
         * @param mixed  $pre     The value to return.
         * @param string $option  The option name.
         * @param mixed  $default The default value.
         */
        $pre = apply_filters('wishlistmember_pre_get_option_' . $option, false, $option, $default);
        if (false !== $pre) {
            return $pre;
        }

        $dec      = (bool) $dec;
        $no_cache = (bool) $no_cache;

        $cache_group = $this->options_table;

        // Preload options.
        $all_options = $this->preload_options();

        // Determine if a default value was passed?
        $passed_default = func_num_args() > 3;

        $not_options = wlm_cache_get('not_options', $cache_group);
        if (isset($not_options[ $option ])) {
            // Option cached as not existing so set value to default.
            $value = $default;
        } elseif (isset($all_options[ $option ])) {
            // Option already preloaded.
            $value = $all_options[ $option ];
        } else {
            $value = $no_cache ? false : wlm_cache_get($option, $cache_group);
            if (false === $value) {
                $row = $wpdb->get_row($wpdb->prepare('SELECT `option_value` FROM `' . esc_sql($this->options_table) . '` WHERE `option_name`=%s LIMIT 1', $option));
                if (is_object($row)) {
                    $value = $row->option_value;
                    wlm_cache_set($option, $value, $cache_group);
                } else {
                    // Cache the non-existence of the option. (learned this from WP Core).
                    if (! is_array($not_options)) {
                        $not_options = [];
                    }
                    $not_options[ $option ] = true;
                    wlm_cache_set('not_options', $not_options, $cache_group);
                    // Option not found. set value to default.
                    $value = $default;
                }
            }
        }
        if ($dec) {
            $value = $this->WLMDecrypt($value);
        }

        /**
         * Filters the final option value
         *
         * @param mixed  $unserialized_value Option value after being processed by wlm_maybe_unserialize
         * @param string $option             Name of the option being requested
         * @param mixed  $value              Raw value returned by $wpdb->get_row
         */
        $value = apply_filters('wishlistmember_get_option', wlm_maybe_unserialize($value), $option, $value);

        /**
         * Filter the final option value
         *
         * The dynamic portion of the hook name, `$option`, refers to the option name.
         *
         * @param mixed  $unserialized_value Option value after being processed by wlm_maybe_unserialize
         * @param string $option             Name of the option being requested
         * @param mixed  $value              Raw value returned by $wpdb->get_row
         */
        return apply_filters('wishlistmember_get_option_' . $option, wlm_maybe_unserialize($value), $option, $value);
    }

    /**
     * Deletes the option names passed as parameters
     *
     * @param string ...$option_names Option names to delete.
     */
    public function delete_option(...$option_names)
    {
        global $wpdb;

        $cache_group = $this->options_table;

        $not_options = wlm_cache_get('not_options', $cache_group);
        if (! is_array($not_options)) {
            $not_options = [];
        }
        foreach ($option_names as $option) {
            $wpdb->query($wpdb->prepare('DELETE FROM `' . esc_sql($this->options_table) . '` WHERE `option_name`=%s', $option));
            wlm_cache_delete($option, $cache_group);
            $not_options[ $option ] = true;
            do_action('wishlistmember_delete_option_' . $option);
        }
        wlm_cache_set('not_options', $not_options, $cache_group);
    }

    /**
     * Saves an option
     *
     * @param  string  $option Name of the option.
     * @param  mixed   $value  Value of option.
     * @param  boolean $enc    Truthy to encrypt $value.
     * @return boolean
     */
    public function save_option($option, $value, $enc = false)
    {
        global $wpdb;
        $cache_group = $this->options_table;
        if ($enc) {
            $value = $this->WLMEncrypt($value);
        }

        $old_value = $this->get_option($option);
        if (false === $old_value) {
            $add_result = $this->add_option($option, $value, $enc);
            $this->OptionSaveHook($option, $value); // Todo: refactor.
            return $add_result ? true : false;
        } else {
            // Data to save.
            $data = [
                'option_name'  => $option,
                'option_value' => wlm_maybe_serialize($value),
            ];
            // Option name to update.
            $where = [
                'option_name' => $option,
            ];

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.
            if (! $wpdb->update($this->options_table, $data, $where, ['%s', '%s'], ['%s'])) {
                return false;
            }

            $all_options = $this->preload_options();
            if (isset($all_options[ $option ])) {
                $all_options[ $option ] = $value;
                wlm_cache_set('all_options', $all_options, $cache_group);
            } else {
                wlm_cache_set($option, $value, $cache_group);
            }

            // Remove $option from `not_options` cache.
            $not_options = wlm_or(wlm_cache_get('not_options', $cache_group), []);
            unset($not_options[ $option ]);
            wlm_cache_set('not_options', $not_options, $cache_group);

            /**
             * Runs an action when an option is updated.
             *
             * The dynamic part of the hook name, `$option`, refers to the option name.
             *
             * @param mixed $value Value of the updated option.
             * @param mixed $value Old option value.
             */
            do_action('wishlistmember_update_option_' . $option, $value, $old_value);

            /**
             * Filters option data before saving.
             *
             * @param mixed $value Option value.
             * @param string $option Option name.
             */
            $value = apply_filters('wishlistmember_update_option', $value, $option);
            $this->OptionSaveHook($option, $value); // Todo: refactor.

            return true;
        }
    }

    /**
     * Adds a new option. Will not add it if the option already exists.
     *
     * @param string  $option Option name.
     * @param mixed   $value  Option value.
     * @param boolean $enc    True to encrypt $value.
     */
    public function add_option($option, $value, $enc = false)
    {
        global $wpdb;
        $cache_group = $this->options_table;

        /*
         *  Make sure the option doesn't already exist.
         *  We first check the `not_options` cache then the actual option.
         *  (inspired by WP Core)
         */
        $not_options = wlm_cache_get('not_options', $cache_group);
        if (! is_array($not_options) || ! isset($not_options[ $option ])) {
            if (false !== $this->get_option($option)) {
                return false;
            }
        }

        if ($enc) {
            $value = $this->WLMEncrypt($value);
        }

        $data = [
            'option_name'  => $option,
            'option_value' => wlm_maybe_serialize($value),
        ];
        if (! $wpdb->insert($this->options_table, $data, ['%s', '%s'])) { //phpcs:ignore WordPress.DB.DirectDatabaseQuery.
            return false;
        }

        $all_options = $this->preload_options();
        if (isset($all_options[ $option ])) {
            $all_options[ $option ] = $value;
            wlm_cache_set('all_options', $all_options, $cache_group);
        } else {
            wlm_cache_set($option, $value, $cache_group);
        }

        // Remove $option from `not_options` cache.
        $not_options = wlm_cache_get('not_options', $cache_group);
        unset($not_options[ $option ]);
        wlm_cache_set('not_options', $not_options, $cache_group);

        /**
         * Runs an action when an option is added.
         *
         * The dynamic part of the hook name, `$option`, refers to the option name.
         *
         * @param mixed $value Value of the updated option.
         */
        do_action('wishlistmember_add_option_' . $option, $value);
        return true;
    }

    /**
     * Preloads options from the database.
     *
     * @return mixed[] Associative array of option values keyed by option names.
     */
    public function preload_options()
    {
        global $wpdb;
        $cache_group = $this->options_table;

        $all_options = wlm_cache_get('all_options', $cache_group);

        if (! $all_options) {
            $result = $wpdb->get_results('SELECT `option_name`,`option_value` FROM `' . esc_sql($this->options_table) . '`', ARRAY_N);

            if ($result) {
                $all_options = array_column($result, 1, 0);
            } else {
                $all_options = [];
            }

            wlm_cache_set('all_options', $all_options, $cache_group);
        }
        /**
         * Filters the final all_options value.
         *
         * @param mixed[] $all_options Associative array of option values keyed by option names.
         */
        return apply_filters('wishlistmember_all_options', $all_options);
    }
}
