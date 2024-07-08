<?php

/**
 * WishList Member functions
 *
 * @package WishListMember
 */

/**
 * Converts $value to an absolute integer
 *
 * @param  mixed $value Value to convert to an absolute integer.
 * @return integer
 */
function wlm_abs_int($value)
{
    return abs((int) $value);
}

/**
 * Adds a metadata to the user levels
 * Note: right now only supports adding is_latest_registration
 *
 * Metadata implementations
 * - is_latest_registration - if the current level is the latest level
 *   the user has registered in, that level will have $obj->is_latest_registration = 1
 *
 * @param string[] $user_levels Array of user levels.
 * @param string   $meta_name   Meta name. Default is is_latest_registration.
 */
function wlm_add_metadata(&$user_levels, $meta_name = 'is_latest_registration')
{
    if (! is_array($user_levels) || count($user_levels) <= 0) {
        return;
    }
    switch ($meta_name) {
        case 'is_latest_registration':
            $idx    = 0;
            $ref_ts = 0;
            foreach ($user_levels as $i => $item) {
                if (is_object($item)) {
                    $item->is_latest_registration = 0;
                    if ($item->Timestamp > $ref_ts) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase.
                        $idx    = $i;
                        $ref_tx = $item->Timestamp; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase.
                    }
                }
            }
            if (isset($user_levels[ $idx ]) && is_object($user_levels[ $idx ])) {
                $user_levels[ $idx ]->is_latest_registration = 1;
            }
            break;
        // More can be added if needed.
        default:
    }
}

/**
 * Dissects the form part of a custom registration form
 * and returns an array of dissected field entries
 *
 * @param  string $custom_registration_form_data Custom registration form data.
 * @return array Custom registration form field entries
 */
function wlm_dissect_custom_registration_form($custom_registration_form_data)
{

    /**
     * Fetch field label
     *
     * @param  string $string Markup.
     * @return string
     */
    function fetch_label($string)
    {
        if (preg_match('#<td class="label".*?>(.*?)</td>#', $string, $match)) {
            return $match[1];
        } elseif (preg_match('#<td class="label ui-sortable-handle".*?>(.*?)</td>#', $string, $match)) {
            return $match[1];
        } else {
            return false;
        }
    }

    /**
     * Fetch field description
     *
     * @param  string $string Markup.
     * @return string
     */
    function fetch_desc($string)
    {
        if (preg_match('#<div class="desc".*?>(.*?)</div></td>#s', $string, $match)) {
            return $match[1];
        } else {
            return false;
        }
    }

    /**
     * Fetch field attributes
     *
     * @param  string $tag    HTML Tag name.
     * @param  string $string Markup.
     * @return string[]       Array of attribute values keyed by attribute names/
     */
    function fetch_attributes($tag, $string)
    {
        preg_match('#<' . $tag . '.+?>#', $string, $match);
        preg_match_all('# (.+?)="([^"]*?)"#', $match[0], $matches);
        $attrs         = array_combine($matches[1], $matches[2]);
        $attrs['name'] = preg_replace('/ +/', '_', wlm_trim($attrs['name']));
        unset($attrs['class']);
        unset($attrs['id']);
        return $attrs;
    }

    /**
     * Fetch options for select, checkbox and radio fields.
     *
     * @param  string $type   Field type. Accepts 'select', 'checkbox', and 'radio'.
     * @param  string $string Markup.
     * @return false|array[] {
     * @type   string  $value    Option value.
     * @type   string  $text     Option text.
     * @type   integer $checked  Truthy "checked" value for radios and checkboxes.
     * @type   integer $selected Truthy "selected" value for select boxes.
     * }
     */
    function wlm_fetch_options($type, $string)
    {
        $string = str_replace(["\n", "\r"], '', $string);
        switch ($type) {
            case 'checkbox':
            case 'radio':
                preg_match_all('#<label[^>]*?>\s*<input.+?value="([^"]*?)"[^>]*?>(.*?)\s*</label>#', $string, $matches);
                $xc      = count($matches[0]);
                $options = [];
                for ($i = 0; $i < $xc; $i++) {
                    $option    = [
                        'value'   => $matches[1][ $i ],
                        'text'    => $matches[2][ $i ],
                        'checked' => (int) preg_match('#checked="checked"#', $matches[0][ $i ]),
                    ];
                    $options[] = $option;
                }
                break;
            case 'select':
                preg_match_all('#<option value="([^"]*?)".*?>(.*?)</option>#', $string, $matches);
                $xc      = count($matches[0]);
                $options = [];
                for ($i = 0; $i < $xc; $i++) {
                    $option    = [
                        'value'    => $matches[1][ $i ],
                        'text'     => $matches[2][ $i ],
                        'selected' => (int) preg_match('#selected="selected"#', $matches[0][ $i ]),
                    ];
                    $options[] = $option;
                }
                break;
            default:
                $options = false;
        }

        return $options;
    }

    $form = wlm_maybe_unserialize($custom_registration_form_data);

    $form_data = $form['form'];

    preg_match_all('#<tr class="(.*?li_(fld|submit).*?)".*?>(.+?)</tr>#is', $form_data, $fields);

    $field_types = $fields[1];
    $fields      = $fields[3];

    foreach ($fields as $key => $value) {
        $fields[ $key ] = [
            'fields' => $value,
            'types'  => explode(' ', $field_types[ $key ]),
        ];

        if (in_array('required', $fields[ $key ]['types'], true)) {
            $fields[ $key ]['required'] = 1;
        }
        if (in_array('systemFld', $fields[ $key ]['types'], true)) {
            $fields[ $key ]['required']     = 1;
            $fields[ $key ]['system_field'] = 1;
        }
        if (in_array('wp_field', $fields[ $key ]['types'], true)) {
            $fields[ $key ]['wp_field'] = 1;
        }

        $fields[ $key ]['description'] = fetch_desc($fields[ $key ]['fields']);

        if (in_array('field_special_paragraph', $fields[ $key ]['types'], true)) {
            $fields[ $key ]['type'] = 'paragraph';
            $fields[ $key ]['text'] = $fields[ $key ]['description'];
            unset($fields[ $key ]['description']);
        } elseif (in_array('field_special_header', $fields[ $key ]['types'], true)) {
            $fields[ $key ]['type'] = 'header';
            $fields[ $key ]['text'] = fetch_label($fields[ $key ]['fields']);
        } elseif (in_array('field_tos', $fields[ $key ]['types'], true)) {
            $fields[ $key ]['attributes'] = fetch_attributes('input', $fields[ $key ]['fields']);
            unset($fields[ $key ]['attributes']['value']);
            unset($fields[ $key ]['attributes']['checked']);
            $options                               = wlm_fetch_options('checkbox', $fields[ $key ]['fields']);
            $fields[ $key ]['attributes']['value'] = wlm_trim($options[0]['value']);
            $fields[ $key ]['text']                = trim(preg_replace('#<[/]{0,1}a.*?>#', '', html_entity_decode($options[0]['value'])));
            $fields[ $key ]['type']                = 'tos';
            $fields[ $key ]['required']            = 1;
            $fields[ $key ]['lightbox']            = (int) in_array('lightbox_tos', $fields[ $key ]['types'], true);
        } elseif (in_array('field_radio', $fields[ $key ]['types'], true)) {
            $fields[ $key ]['attributes'] = fetch_attributes('input', $fields[ $key ]['fields']);
            unset($fields[ $key ]['attributes']['checked']);
            unset($fields[ $key ]['attributes']['value']);
            $fields[ $key ]['options'] = wlm_fetch_options('radio', $fields[ $key ]['fields']);
            $fields[ $key ]['type']    = 'radio';
            $fields[ $key ]['label']   = fetch_label($fields[ $key ]['fields']);
        } elseif (in_array('field_checkbox', $fields[ $key ]['types'], true)) {
            $fields[ $key ]['attributes'] = fetch_attributes('input', $fields[ $key ]['fields']);
            unset($fields[ $key ]['attributes']['checked']);
            unset($fields[ $key ]['attributes']['value']);
            $fields[ $key ]['options'] = wlm_fetch_options('checkbox', $fields[ $key ]['fields']);
            $fields[ $key ]['type']    = 'checkbox';
            $fields[ $key ]['label']   = fetch_label($fields[ $key ]['fields']);
        } elseif (in_array('field_select', $fields[ $key ]['types'], true)) {
            $fields[ $key ]['attributes'] = fetch_attributes('select', $fields[ $key ]['fields']);
            $fields[ $key ]['options']    = wlm_fetch_options('select', $fields[ $key ]['fields']);
            $fields[ $key ]['type']       = 'select';
            $fields[ $key ]['label']      = fetch_label($fields[ $key ]['fields']);
        } elseif (in_array('field_textarea', $fields[ $key ]['types'], true) || in_array('field_wp_biography', $fields[ $key ]['types'], true)) {
            $fields[ $key ]['attributes'] = fetch_attributes('textarea', $fields[ $key ]['fields']);
            preg_match('#<textarea.+?>(.*?)</textarea>#', $fields[ $key ]['fields'], $match);
            $fields[ $key ]['attributes']['value'] = $match[1];
            $fields[ $key ]['type']                = 'textarea';
            $fields[ $key ]['label']               = fetch_label($fields[ $key ]['fields']);
        } elseif (in_array('field_hidden', $fields[ $key ]['types'], true)) {
            $fields[ $key ]['attributes'] = fetch_attributes('input', $fields[ $key ]['fields']);
            $fields[ $key ]['type']       = 'hidden';
        } elseif (in_array('li_submit', $fields[ $key ]['types'], true)) {
            preg_match('#<input .+?value="(.+?)".*?>#', $fields[ $key ]['fields'], $match);
            $submit_label = $match[1];
            unset($fields[ $key ]);
        } else {
            $fields[ $key ]['attributes'] = fetch_attributes('input', $fields[ $key ]['fields']);
            $fields[ $key ]['type']       = 'input';
            $fields[ $key ]['label']      = fetch_label($fields[ $key ]['fields']);
        }

        unset($fields[ $key ]['fields']);
        unset($fields[ $key ]['types']);
    }

    ksort($fields);
    $fields = [
        'fields' => $fields,
        'submit' => $submit_label,
    ];

    return $fields;
}

/**
 * Checks if the requested array index is set and returns its value
 *
 * @param  array|null     $array_or_object Array or object. Null to return the last result.
 * @param  string|integer ...$indexes      Index. More than one index can be provided for multidimensional arrays and objects.
 * @return mixed
 */
function wlm_arrval($array_or_object, ...$indexes)
{
    static $last_result = null;
    if (is_string($array_or_object) && 'lastresult' === $array_or_object) {
        return $last_result;
    }
    foreach ($indexes as $index) {
        $type = false;
        if (is_array($array_or_object) && isset($array_or_object[ $index ])) {
            $array_or_object = $array_or_object[ $index ];
        } elseif (is_object($array_or_object) && isset($array_or_object->$index)) {
            $array_or_object = $array_or_object->$index;
        } else {
            $last_result = null;
            return;
        }
    }
    $last_result = $array_or_object;
    return $array_or_object;
}

/**
 * Function to correctly interpret boolean representations
 * - interprets false , 0, n and no as FALSE
 * - interprets true, 1, y and yes as TRUE
 *
 * ALl interpretations are case-insensitive.
 *
 * @param  mixed $value          Representation to interpret.
 * @param  mixed $no_match_value Value to return if representation does not match any of the expected representations.
 * @return boolean|mixed|null   Returns boolean if a matching interpration is found. Otherwise returns the value of $no_match_value.
 */
function wlm_boolean_value($value, $no_match_value = false)
{
    $value = trim(strtolower($value));
    if (in_array($value, [false, 0, 'false', '0', 'n', 'no'], true)) {
        return false;
    }
    if (in_array($value, [true, 1, 'true', '1', 'y', 'yes'], true)) {
        return true;
    }
    return $no_match_value;
}

/**
 * Checks if an admin is in the WP admin area.
 *
 * @return boolean
 */
function wlm_admin_in_admin()
{
    return ( ( current_user_can('manage_options') || current_user_can('wishlist_admin') ) && is_admin() );
}

/*
 * `wlm_cache_` functions
 */

/**
 * Sets or resets the WishList Member cache group suffix
 *
 * @param  boolean $reset True to reset the cache group suffix.
 * @return string
 */
function wlm_cache_group_suffix($reset = false)
{
    static $wlm_cache_group_suffix;
    if (is_null($wlm_cache_group_suffix) && empty($reset)) {
        $wlm_cache_group_suffix = get_option('wlm_cache_group_suffix');
    }
    if (empty($wlm_cache_group_suffix) || ! empty($reset)) {
        $wlm_cache_group_suffix = microtime(true);
        update_option('wlm_cache_group_suffix', $wlm_cache_group_suffix);
    }
    return $wlm_cache_group_suffix;
}

/**
 * Flushes the WishList Member group cache by resetting the group suffix
 */
function wlm_cache_flush()
{
    wlm_cache_group_suffix(true);
}

/**
 * Saves data to cache in a group suffixed by the value of `wlm_cache_group_suffix()`
 *
 * @uses wp_cache_set()
 * See https://developer.wordpress.org/reference/functions/wp_cache_set/ for function parameters.
 */
function wlm_cache_set()
{
    $args     = func_get_args();
    $args[2] .= wlm_cache_group_suffix();
    return wp_cache_set(...array_values($args));
}

/**
 * Retrieves data from cache in a group suffixed by the value of `wlm_cache_group_suffix()`
 *
 * @uses wp_cache_get()
 * See https://developer.wordpress.org/reference/functions/wp_cache_get/ for function parameters.
 */
function wlm_cache_get()
{
    $args     = func_get_args();
    $args[1] .= wlm_cache_group_suffix();
    return wp_cache_get(...array_values($args));
}

/**
 * Deletes data from cache in a group suffixed by the value of `wlm_cache_group_suffix()`
 *
 * @uses wp_cache_delete()
 * See https://developer.wordpress.org/reference/functions/wp_cache_delete/ for function parameters.
 */
function wlm_cache_delete()
{
    $args     = func_get_args();
    $args[1] .= wlm_cache_group_suffix();
    return wp_cache_delete(...array_values($args));
}

/**
 * Calls the WishList Member API 2 Internally
 *
 * @param  string $request Requested resource (i.e. "/levels").
 * @param  string $method  Accepts 'GET', 'POST', 'PUT' and 'DELETE'.
 * @param  array  $data    Associative array of data to pass to the request.
 * @return array          WishList Member API2 Result
 */
function WishListMemberAPIRequest($request, $method = 'GET', $data = null)  //phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
{
    require_once WLM_PLUGIN_DIR . '/legacy/core/API2.php';
    $api = new \WLMAPI2($request, strtoupper($method), $data);
    return $api->result;
}

/**
 * Get a category's top-most ancestor.
 *
 * @param  integer $id Category ID.
 * @return integer     ID of top-most ancestor category.
 */
function wlm_get_category_root($id)
{
    $cat = get_category($id);
    if ($cat->parent) {
        $ancestors = get_ancestors($cat->term_id, 'category');
        $root      = count($ancestors) - 1;
        $root      = $ancestors[ $root ];
        return $root;
    } else {
        return $cat->term_id;
    }
}

/**
 * Get a category's posts or sub-categories depending on $type
 *
 * @param  integer $id   Category ID.
 * @param  string  $type Accepts 'post' and 'category'.
 * @return \WP_Post[]|integer[] Array of WP_Post objects or category IDs depending on $type
 */
function wlm_get_category_children($id, $type = 'category')
{
    $categories = [];
    $posts      = [];

    $categories = get_categories('child_of=' . $id);

    $cats = [];
    foreach ($categories as $c) {
        $cats[] = $c->term_id;
    }

    if ('category' === $type) {
        return $cats;
    }

    $args = [
        'category'       => $id,
        'posts_per_page' => -1,
    ];
    return get_posts($args);
}

/**
 * Get a post's root categories.
 *
 * @param  integer $id Post ID.
 * @return integer[]   Array of category IDs
 */
function wlm_get_post_roots($id)
{
    $cats  = get_the_category($id);
    $roots = [];
    foreach ($cats as $c) {
        $roots[] = wlm_get_category_root($c);
    }
    return $roots;
}

/**
 * Get a page's top-most parent page
 *
 * @param  integer $id Page ID.
 * @return integer     Top post parent page ID.
 */
function wlm_get_page_root($id)
{
    $post = get_post($id);
    if ($post->post_parent) {
        $ancestors = get_post_ancestors($id);
        $root      = count($ancestors) - 1;
        $root      = $ancestors[ $root ];
    } else {
        $root = $post->ID;
    }
    return $root;
}

/**
 * Get a page's child pages.
 *
 * @param  integer $page_id Page ID.
 * @return int[]            Array of child page IDs.
 */
function wlm_get_page_children($page_id)
{
    $children    = [];
    $descendants = get_children(
        [
            'post_parent' => $page_id,
            'post_types'  => get_post_types(),
        ]
    );
    foreach ($descendants as $d) {
        $children[] = $d->ID;
    }
    return $children;
}

/**
 * Build a payment form.
 *
 * @param  array   $data               Data to use to build the payment form.
 * @param  string  $additional_classes Additional classes to use for the payment form.
 * @param  boolean $on_page            Whether to build the payment form for an on-page registration form. Default is false.
 * @return string                     Payment form markup.
 */
function wlm_build_payment_form($data, $additional_classes = '', $on_page = false)
{
    ob_start();
    extract((array) $data); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract.
    include WLM_PLUGIN_DIR . '/legacy/resources/forms/popup-regform.php';
    $str = ob_get_clean();
    $str = preg_replace('/\s+/', ' ', $str);
    return $str;
}

/**
 * Generate the tutorial video URL
 *
 * @param  string ...$args Strings used to help generate the URL.
 * @return string          Video URL.
 */
function wlm_video_tutorial(...$args)
{
    $version = explode('.', wishlistmember_instance()->version);

    /*
     * we only take the first digit of minor to comply
     * with john's URL format for tutorial video links
     */
    $version = wlm_arrval($version, 0) . '-' . substr((string) wlm_arrval($version, 1), 0, 1);
    $parts   = strtolower(implode('-', $args));
    $url     = 'http://go.wlp.me/wlm:%s:vid:%s';

    return sprintf($url, $version, $parts);
}

/**
 * Sanitizes a string by removing all tags then passing it to `esc_html()`
 *
 * @param string $string String to sanitize.
 */
function wlm_xss_sanitize(&$string)
{
    $string = esc_html(wp_strip_all_tags($string));
}

/**
 * Scrutinize a password's strength based on WishList Member settings
 *
 * @param  string $password Password to scrutinize.
 * @return true|string      TRUE if password passed scrutiny or error message if not
 */
function wlm_scrutinize_password($password)
{
    $passmin  = wlm_or((int) wishlistmember_instance()->get_option('min_passlength'), 8);
    $password = wlm_trim($password);
    // Validate password length.
    if (strlen($password) < $passmin) {
        // Translators: 1: Minimum password length.
        return sprintf(__('Password has to be at least %1$d characters long and must not contain spaces.', 'wishlist-member'), $passmin);
    }
    // Validate password strength (if enabled)
    if (wishlistmember_instance()->get_option('strongpassword') && ! wlm_check_password_strength($password)) {
        return __('Please provide a strong password. Password must contain at least one uppercase letter, one lowercase letter, one number and one special character.', 'wishlist-member');
    }
    return true;
}

/**
 * Check password strength.
 *
 * @param  string $password Password.
 * @return boolean
 */
function wlm_check_password_strength($password)
{
    if (! preg_match('/[a-z]/', $password)) {
        return false;
    }
    if (! preg_match('/[A-Z]/', $password)) {
        return false;
    }
    if (! preg_match('/[0-9]/', $password)) {
        return false;
    }
    $chars = preg_quote('`~!@#$%^&*()-_=+[{]}|;:",<.>\'\?', '/');
    if (! preg_match('/[' . $chars . ']/', $password)) {
        return false;
    }
    return true;
}

/**
 * Checks is $email is valid.
 *
 * @param  string $email Email address.
 * @return boolean
 */
function wlm_is_email($email)
{
    return is_email(stripslashes($email));
}

/**
 * Sets a cookie using PHP's `setcookie()` with the cookie name prefixed with the WishList Member 'CookiePrefix' option.
 *
 * @uses   setcookie()
 * @param  string                 $name    Cookie name.
 * @param  string|integer|boolean ...$args Arguments as passed to `setcookie()` from its 2nd parameter onwards.
 * @return boolean
 */
function wlm_setcookie($name, ...$args)
{
    $prefix = trim(wishlistmember_instance()->get_option('CookiePrefix'));
    $name   = $prefix ? $prefix . $name : $name;
    return @setcookie($name, ...array_values($args)); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged.
}

/**
 * Get cookie prefixed with the WishList Member 'CookiePrefix' option
 *
 * @param  string $name Cookie name.
 * @return mixed        Cookie value.
 */
function wlm_getcookie($name)
{
    $prefix = trim(wishlistmember_instance()->get_option('CookiePrefix'));
    $name   = $prefix ? $prefix . $name : $name;
    return wlm_arrval($_COOKIE, $name);
}

/**
 * Inject a WishList Member prefixed cookie directly into the $_COOKIE global variable
 * then attempt to set it as a an actual cookie if possible
 *
 * @uses   wlm_setcookie()
 * @param  string                 $name    Cookie name.
 * @param  string|integer|boolean ...$args Argument as passed to `setcookie()`.
 * @return boolean
 */
function wlm_inject_cookie($name, ...$args)
{
    $prefix = trim(wishlistmember_instance()->get_option('CookiePrefix'));
    // Inject the cookie.
    $_COOKIE[ $prefix ? $prefix . $name : $name ] = wlm_arrval($args, 0);

    // Attempt to set the cookie.
    if (! headers_sent()) {
        return wlm_setcookie($name, ...array_values($args));
    }
    return false;
}

/**
 * Attempt to set the PHP time limit.
 *
 * @param string $time_limit Time limit.
 */
function wlm_set_time_limit($time_limit = '')
{
    $disabled = 'disable_functions';
    $disabled = explode(',', ini_get($disabled));
    if (! in_array('set_time_limit', $disabled, true)) {
        @set_time_limit($time_limit); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged.
    }
}

/**
 * Wrapper function for wp_insert_user that takes multisites
 * into consideration
 *
 * If the user being added already exists in the multisite
 * then simply add the same user to the current blog instead
 * of attempting to create a new one
 *
 * @uses wp_insert_user()
 *
 * @param  array $userdata array compatible with WordPress' `wp_insert_user()`.
 * @return integer|WP
 */
function wlm_insert_user($userdata)
{
    global $wishlist_member_inserting_user;
    if (is_multisite()) {
        $blog_id = get_current_blog_id();
        $mu_user = get_user_by('email', $userdata['user_email']);
        if ($mu_user) {
            if (is_user_member_of_blog($mu_user->ID, $blog_id)) {
                return false;
            } else {
                add_user_to_blog($blog_id, $mu_user->ID, get_option('default_role'));
                return $mu_user->ID;
            }
        }
    }
    $wishlist_member_inserting_user = true;
    $result                         = wp_insert_user($userdata);
    $wishlist_member_inserting_user = false;
    return $result;
}

/**
 * Replacement function for wp_create_user that
 * takes multisites into consideration.
 *
 * @uses wlm_insert_user
 *
 * @param  string $username Username.
 * @param  string $password Password.
 * @param  string $email    Email.
 * @return integer|WP
 */
function wlm_create_user($username, $password, $email = '')
{
    $user_login = wp_slash($username);
    $user_email = wp_slash($email);
    $user_pass  = $password;

    $userdata = compact('user_login', 'user_email', 'user_pass');
    return wlm_insert_user($userdata);
}

/**
 * Get the numeric value of a string size (ie. 1M to 1048576)
 *
 * @param  string $size Size.
 * @return integer
 */
function wlm_parse_size($size)
{
    return wp_convert_hr_to_bytes($size);
}

/**
 * Get the max upload file size in bytes.
 *
 * @return integer
 */
function wlm_get_file_upload_max_size()
{
    return wp_max_upload_size();
}

/**
 * Attempt to get the actual public facing IP of the client.
 *
 * @return string IP Address.
 */
function wlm_get_client_ip()
{
    $sources = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR'];
    foreach ($sources as $ip) {
        if (false !== filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return $ip;
        }
    }
    return wp_unslash(wlm_server_data()['REMOTE_ADDR']);
}

/**
 * Special wrapper to WP's `wp_enqueue_scripts()`
 *
 * @uses wp_enqueue_script() - https://developer.wordpress.org/reference/functions/wp_enqueue_script/
 */
function wlm_enqueue_script()
{
    global $current_screen;
    $args = func_get_args();

    wp_deregister_script($args[0]);
    if (empty($args[5])) {
        $args[0] = 'wishlistmember3-js-' . $args[0];
    }

    list( $url, $key, $data ) = array_pad(explode('|', $args[1], 3), 3, '');
    if (! strpos($url, '://') && false === strpos($url, '/wp-content/')) {
        $args[1] = wishlistmember_instance()->get_js($url);
    }

    if (empty($args[2])) {
        $args[2] = [];
    }
    array_walk(
        $args[2],
        function (&$value) {
            if ('-' === substr($value, 0, 1)) {
                $value = 'wishlistmember3-js' . $value;
            }
        }
    );

    if (empty($args[3])) {
        $args[3] = wishlistmember_instance()->version;
    }
    call_user_func_array('wp_enqueue_script', $args);

    if (! empty($key) && ! empty($data) && function_exists('wp_script_add_data')) {
        wp_script_add_data($args[0], $key, $data);
    }
}

/**
 * Special wrapper to WP's `wp_enqueue_style()`
 *
 * @uses wp_enqueue_style() - https://developer.wordpress.org/reference/functions/wp_enqueue_style/
 */
function wlm_enqueue_style()
{
    global $current_screen;
    $args = func_get_args();
    if (empty($args[5])) {
        $args[0] = 'wishlistmember3-css-' . $args[0];
    }

    list( $url, $key, $data ) = array_pad(explode('|', $args[1], 3), 3, '');
    if (! strpos($url, '://') && false === strpos($url, '/wp-content/')) {
        $args[1] = wishlistmember_instance()->get_css($url);
    }

    if (empty($args[2])) {
        $args[2] = [];
    }
    array_walk(
        $args[2],
        function (&$value) {
            if ('-' === substr($value, 0, 1)) {
                $value = 'wishlistmember3-css' . $value;
            }
        }
    );

    if (empty($args[3])) {
        $args[3] = wishlistmember_instance()->version;
    }
    call_user_func_array('wp_enqueue_style', $args);

    if (! empty($key) && ! empty($data)) {
        wp_style_add_data($args[0], $key, $data);
    }
}

/**
 * Generate and return standardized form field markup
 *
 * @param  array $attributes An associatiove array of attributes as supported by the input element.
 *                           Special markup is generated for type=textarea|select|checkbox|radio|submit|reset|button.
 *                           options=array supported for type=select|checkbox|radio.
 * @return string            Standardized form field markup
 */
function wlm_form_field($attributes)
{
    static $password_generator = false;
    static $password_metered   = false;
    wp_enqueue_style('wlm3_form_css');

    $defaults = [
        'label'       => '',
        'name'        => '',
        'type'        => 'text',
        'value'       => '',
        'options'     => [],
        'class'       => '',
        'id'          => '',
        'description' => '',
        'text'        => '',
        'lightbox'    => '',
    ];

    $hide   = __('Hide', 'wishlist-member');
    $show   = __('Show', 'wishlist-member');
    $cancel = __('Cancel', 'wishlist-member');

    $attributes = wp_parse_args($attributes, $defaults);

    $label = wlm_trim($attributes['label']);
    unset($attributes['label']);

    $value = $attributes['value'];
    unset($attributes['value']);

    $options = (array) $attributes['options'];
    unset($attributes['options']);

    $type = $attributes['type'];
    unset($attributes['type']);

    $text = $attributes['text'];
    unset($attributes['text']);

    $lightbox = $attributes['lightbox'];
    unset($attributes['lightbox']);

    if (! $attributes['id'] && $attributes['name']) {
        $attributes['id'] = 'wlm_form_field_' . $attributes['name'];
    }

    $description = wlm_trim($attributes['description']);
    unset($attributes['description']);
    if ($description) {
        $description = sprintf('<div class="wlm3-form-description">%s</div>', $description);
    }

    switch ($type) {
        case 'paragraph':
            $field = sprintf('<div class="wlm3-form-text">%s</div>', $text);
            break;
        case 'header':
            $field = sprintf('<div class="wlm3-form-header">%s</div>', $text);
            break;
        case 'tos':
            if ($lightbox) {
                wp_enqueue_script('wlm-jquery-fancybox');
                wp_enqueue_style('wlm-jquery-fancybox');
            }
            $field                = ['input'];
            $attributes['class'] .= ' form-checkbox fld';
            $attributes['type']   = 'checkbox';
            $attributes['value']  = $value;
            foreach ($attributes as $k => $v) {
                $field[] = sprintf('%s="%s"', $k, htmlentities($v));
            }
            if (! preg_match('#((<p|div|br>)|</[a-zA-Z]+[0-9]*>)#', $description)) { // Convert to html.
                $description = nl2br($description);
            }
            if ($lightbox) {
                $description = sprintf('<div style="display:none;"><div id="%s-lightbox">%s</div></div>', $attributes['id'], $description);
                $text        = sprintf('<a class="wlm3-tos-fancybox" href="#%s-lightbox">%s</a>', $attributes['id'], $text);
            } else {
                $description = sprintf('<div class="wlm3-form-tos">%s</div>', $description);
            }

            $field = str_replace(['%%%field%%%', '%%%label%%%'], [implode(' ', $field), wlm_trim($text)], '<label><%%%field%%%> %%%label%%%</label>');
            break;
        case 'textarea':
            $attributes['class'] .= ' wlm3-form-field fld';
            $field                = ['textarea'];
            foreach ($attributes as $k => $v) {
                $field[] = sprintf('%s="%s"', $k, htmlentities($v));
            }
            $field = '<' . implode(' ', $field) . '>' . $value . '</textarea>';
            break;
        case 'select':
            $attributes['class'] .= ' wlm3-form-field fld';
            if (isset($attributes['multiple']) && ! preg_match('/\[\]$/', $attributes['name'])) {
                $attributes['name'] .= '[]';
            }
            $field = ['select'];
            foreach ($attributes as $k => $v) {
                $field[] = sprintf('%s="%s"', $k, htmlentities($v));
            }
            foreach ($options as $k => &$v) {
                $selected = (string) $k === (string) $value ? ' selected="selected"' : '';
                $v        = sprintf('<option value="%s"%s>%s</option>', htmlentities($k), $selected, $v);
            }
            unset($v);
            $field = '<' . implode(' ', $field) . '>' . implode('', $options) . '</select>';
            break;
        case 'checkbox':
            if (count($options) > 1 && ! preg_match('/\[\]$/', $attributes['name'])) {
                $attributes['name'] .= '[]';
            }
            // Proceed to radio...
        case 'radio':
            $attributes['class'] .= ' form-checkbox fld';
            $field                = '';
            $checkbox             = ['input'];
            $attributes['type']   = $type;
            foreach ($attributes as $k => $v) {
                $checkbox[] = sprintf('%s="%s"', $k, htmlentities($v));
            }
            foreach ($options as $k => $v) {
                $checkbox['c'] = (string) $k === (string) $value ? 'checked="checked"' : '';
                $checkbox['v'] = sprintf('value="%s"', htmlentities($k));
                $field        .= str_replace(['%%%field%%%', '%%%label%%%'], [implode(' ', $checkbox), $v], '<label><%%%field%%%> %%%label%%%</label>');
            }
            break;
        case 'button':
            $field = ['button'];
            foreach ($attributes as $k => $v) {
                $field[] = sprintf('%s="%s"', $k, htmlentities($v));
            }
            $field = '<' . implode(' ', $field) . '>' . $value . '</button>';
            break;
        case 'rawhtml':
            $field = $value;
            break;
        case 'profile_photo':
            $str = __('Select File', 'wishlist-member');

            $gravatar = wlm_get_gravatar();
            $src      = $value ? $value : wishlistmember_instance()->plugin_url3 . '/assets/images/grey.png';

            $upload_icon   = sprintf('<img src="%s" height="30" width="30">', wishlistmember_instance()->plugin_url3 . '/ui/images/cloud_upload-24px.svg');
            $delete_icon   = sprintf('<img src="%s" height="30" width="30">', wishlistmember_instance()->plugin_url3 . '/ui/images/highlight_off-24px.svg');
            $undo_icon     = sprintf('<img src="%s" height="30" width="30">', wishlistmember_instance()->plugin_url3 . '/ui/images/restore-24px.svg');
            $gravatar_logo = sprintf('<img src="%s" height="30" width="30">', wishlistmember_instance()->plugin_url3 . '/ui/images/gravatar-logo.svg');
            $kses_allowed  = [
                'img' => [
                    'src'    => [],
                    'height' => [],
                    'width'  => [],
                ],
            ];

            $field = '<div class="wlm3-profile-photo-container -clean"><div class="wlm3-profile-photo"><input type="hidden" name="' . esc_attr($attributes['name'])
            . '" old-value="' . esc_attr($value)
            . '" value="' . esc_attr($value)
            . '"><img gravatar-src="' . esc_attr($gravatar)
            . '" old-src="' . esc_attr($src)
            . '" src="' . esc_attr($src)
            . '" class="profile-photo" /></div><div class="wlm3-profile-photo-icons"><div><label title="Upload Photo"><span class="wlm3-profile-photo-uploader">' . wp_kses($upload_icon, $kses_allowed)
            . '</span><input type="file" name="' . esc_attr($attributes['name'])
            . '-upload" accept="image/jpeg,image/jpg,image/png"></label><label title="Gravatar"><span class="wlm3-profile-photo-gravatar">' . wp_kses($gravatar_logo, $kses_allowed)
            . '</span></label><label title="Delete"><span class="wlm3-profile-photo-clear">' . wp_kses($delete_icon, $kses_allowed)
            . '</span></label><label title="Undo unsaved changes" class="-undo"><span class="wlm3-profile-photo-undo">' . wp_kses($undo_icon, $kses_allowed)
            . '</span></label></div></div></div>';

            // Enqueue the needed wlm3_form_js and JQuery for updating the profile picture.
            wp_enqueue_script('wlm3_form_js');
            wp_enqueue_script('jquery');

            break;
        case 'password_generator':
            if (! $password_generator) {
                $type = 'text';

                $id               = '_' . md5(wp_rand() . microtime());
                $attributes['id'] = 'wlm3-password-field' . $id;

                $attributes['onkeyup'] = sprintf('wlm3_password_strength(this, \'%1$s\')', $id);
                $attributes['style']  .= ' display: none;';

                $append = sprintf('<div id="wlm3-password-generator-strength%1$s"></div>', $id);

                $prepend            = sprintf('<button id="wlm3-password-generator-button%1$s" type="button" onclick="wlm3_generate_password(\'%1$s\'); return false">%2$s</button>', $id, __('Generate Password', 'wishlist-member'));
                $prepend           .= sprintf('<div id="wlm3-password-generator-buttons%1$s" style="display: none;"><button id="wlm3-password-generator-toggle%1$s" onclick="wlm3_generate_password_toggle(this, \'%1$s\'); return false;" data-hide="%2$s" data-show="%3$s">%2$s</button> <button id="wlm3-password-generator-cancel" onclick="wlm3_generate_password_hide(\'%1$s\'); return false;">%4$s</button></div>', $id, $hide, $show, $cancel);
                $password_generator = true;
            } else {
                $type = 'password';
            }
            $from_passgen = true;
            // Proceed to password_metered...
        case 'password_metered':
            if (empty($from_passgen)) {
                $type = 'password';

                $id               = '_' . md5(wp_rand() . microtime());
                $attributes['id'] = 'wlm3-password-field' . $id;

                $attributes['onkeyup'] = sprintf('wlm3_password_strength(this, \'%1$s\')', $id);

                $append  = sprintf('<div id="wlm3-password-generator-strength%1$s"></div>', $id);
                $prepend = sprintf('<div id="wlm3-password-generator-buttons%1$s" style="display: none;"><button id="wlm3-password-generator-toggle%1$s" onclick="wlm3_generate_password_toggle(this, \'%1$s\'); return false;" data-hide="%2$s" data-show="%3$s">%3$s</button></div>', $id, $hide, $show);
            }

            wp_enqueue_script('wlm3_form_js');
            wp_enqueue_script('jquery');
            // Proceed to password...
        case 'password':
            $password_toggle = (bool) wlm_arrval($attributes, 'toggle');
            unset($attributes['toggle']);
            $value = '';
            // Proceed to default...
        default:
            if (! in_array($type, ['submit', 'reset', 'image'], true)) {
                $attributes['class'] .= ' wlm3-form-field fld';
            }
            $attributes['type']  = $type;
            $attributes['value'] = $value;
            $field               = ['input'];
            foreach ($attributes as $k => $v) {
                $field[] = sprintf('%s="%s"', $k, htmlentities($v));
            }
            $field = '<' . implode(' ', $field) . '>';
            if (! empty($prepend)) {
                $field = $prepend . $field;
            }
            if (! empty($append)) {
                $field .= $append;
            }
    }

    switch ($type) {
        case 'submit':
        case 'button':
        case 'image':
        case 'reset':
            $markup = '<p>%%%field%%%</p>';
            break;
        case 'hidden':
            $markup = '%%%field%%%';
            break;
        case 'password':
            if ($password_toggle) {
                $field = sprintf('<span class="wishlist-member-login-password">%s<a href="#" class="dashicons dashicons-visibility" aria-hidden="true"></a></span>', $field);
            }
            // Proceed to default...
        default:
            $markup = $label ? '<div class="wlm3-form-group"><label>%%%label%%%</label>%%%field%%%%%%description%%%</div>' : '<div class="wlm3-form-group">%%%field%%%%%%description%%%</div>';
    }

    $code = str_replace(['%%%label%%%', '%%%field%%%', '%%%description%%%'], [$label, $field, $description], $markup);

    return $code;
}

/**
 * Attempt to auto-detect the separator used in a CSV file
 * Important: This function rewinds the file pointer to the beginning of the file
 *
 * @param  resource $file_resource File handle.
 * @return string
 */
function wlm_detect_csv_separator($file_resource)
{
    $separators = [
        ','  => 0,
        ';'  => 0,
        '|'  => 0,
        "\t" => 0,
    ];

    rewind($file_resource);
    $line = fgets($file_resource);
    rewind($file_resource);

    foreach ($separators as $sep => &$count) {
        $count = count(str_getcsv($line, $sep));
    }
    unset($count);

    return array_search(max($separators), $separators, true);
}

if (! function_exists('wlm_get_active_plugins')) {
    /**
     * Get active plugins
     *
     * @return string[] Associative array of plugin names keyed by their relative paths.
     */
    function wlm_get_active_plugins()
    {
        $active         = get_option('active_plugins');
        $plugins        = get_plugins();
        $active_plugins = [];
        foreach ($active as $a) {
            if (isset($plugins[ $a ])) {
                $active_plugins[ $a ] = isset($plugins[ $a ]['Name']) ? $plugins[ $a ]['Name'] : $a;
            }
        }
        return $active_plugins;
    }
}

/**
 * Checks whether a post type is excluded from WishList Member protection
 *
 * @param  string $post_type Post type.
 * @return boolean
 */
function wlm_post_type_is_excluded($post_type)
{
    /**
     * Filters post types includes from WishList Member protection
     *
     * @param array Array of post types
     */
    $excluded_post_types = apply_filters('wishlistmember_excluded_post_types', []);
    return in_array($post_type, (array) $excluded_post_types, true);
}

/**
 * Generates a base64 encoded random string of $length characters
 *
 * @param  integer $length Maximum length.
 * @return string
 */
function wlm_generate_key($length = 128)
{
    if ($length < 1) {
        $length = 128;
    }
    return substr(base64_encode(openssl_random_pseudo_bytes($length * 5)), 0, $length); //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode.
}

/**
 * Load select2 into jQuery.fn.wlmselect2
 *
 * To be used in areas where we need select2 outside of WishList Member's screen
 */
function wlm_select2()
{
    // Styles.
    wlm_enqueue_style('select2', 'select2.min.css');
    wlm_enqueue_style('select2-bootstrap', 'select2-bootstrap.min.css');

    // Scripts.
    wp_register_script('wlmselect2', wishlistmember_instance()->plugin_url3 . '/assets/js/wlmselect2.js', '', wishlistmember_instance()->version, true);
    if (function_exists('wp_add_inline_script')) {
        wp_add_inline_script('wlmselect2', 'var wlmselect2src=' . wp_json_encode(wishlistmember_instance()->plugin_url3 . '/assets/js/select2.full.min.js') . ';', 'before');
    } else {
        wp_localize_script('wlmselect2', 'wlmselect2src', wishlistmember_instance()->plugin_url3 . '/assets/js/select2.full.min.js');
    }

    wp_enqueue_script('wlmselect2');
}

/**
 * Replaces the first data with the second data
 * and does so recursively if both are arrays
 *
 * Replace $data1 with $data2 if any of the following is true
 * - $data1 is not an array
 * - $data1 is a sequentially indexed array
 * - $data2 is not an array
 *
 * If $data1 is an associative array and $data2 is an array
 * then recursively replace $data1 with $data2
 *
 * @param  mixed $data1 Data to be replaced.
 * @param  mixed $data2 Data to replace it with.
 * @return mixed
 */
function wlm_replace_recursive($data1, $data2)
{
    // $data1 is not an array.
    if (! is_array($data1)) {
        return $data2;
    }
    // $data1 is a sequentially indexed array.
    if (array_keys($data1) === range(0, count($data1) - 1)) {
        return $data2;
    }
    // $data2 is not an array.
    if (! is_array($data2)) {
        return $data2;
    }

    /*
     * at this point we can be sure of two things:
     * $data1 is an associative array
     * $data2 is an array (associative or not)
     */
    foreach (array_keys($data2) as $key) {
        if (isset($data1[ $key ])) {
            // If there's a matching $key between $data1 and $data2 then recursively replace it.
            $data1[ $key ] = wlm_replace_recursive($data1[ $key ], $data2[ $key ]);
        } else {
            // If $data1 has not matching $key then create it.
            $data1[ $key ] = $data2[ $key ];
        }
    }

    return $data1;
}

/**
 * Minimal checks if $string contains HTML code
 *
 * @param  string $string String to check.
 * @return boolean
 */
function wlm_has_html($string)
{
    return preg_match('#(</p>|</div>|</a>|</span>|<br\b.*?>)#i', $string) ? true : false;
}

/**
 * Wrapper function for wp_generate_password
 * Checks if wp_generate_password exists and if not, include wp-includes/pluggable.php to create the function
 *
 * @uses wp_generate_password()
 *
 * @param  integer $length              The length of password to generate.
 * @param  boolean $special_chars       Whether to include standard special characters.
 * @param  boolean $extra_special_chars Whether to include other special characters. Used when generating secret keys and salts.
 * @return string                       The random password.
 */
function wlm_generate_password($length = 12, $special_chars = true, $extra_special_chars = false)
{
    if (! function_exists('wp_generate_password')) {
        include_once ABSPATH . '/' . WPINC . '/pluggable.php';
    }
    return call_user_func_array('wp_generate_password', func_get_args());
}

/**
 * Remove inactive levels from the passed array of Level IDs
 *
 * @param  integer $user_id User ID.
 * @param  array   $levels  Array of Level IDs.
 * @return array            $levels with all the inactive levels for the user removed
 */
function wlm_remove_inactive_levels($user_id, $levels)
{
    $user_levels = wishlistmember_instance()->get_membership_levels($user_id, false, true, null, false, false);
    return array_intersect((array) $levels, $user_levels);
}

/**
 * JSON encodes data if it's non-scalar or if it's boolean
 * Accepts all parameters supported by php.net/json_encode
 * Note: Use json_last_error() if you want to check for actual encoding error
 *
 * @param  mixed $data Required. Data to encode into JSON.
 * @return mixed       JSON Encoded string if $data is non-scalar or boolean. Otherwise return unchanged $data
 */
function wlm_maybe_json_encode($data)
{
    if (! is_scalar($data) || is_boolean($data)) {
        $data = wp_json_encode(...func_get_args());
    }
    return $data;
}

/**
 * Attempts to decode $data as JSON
 * Accepts all parameters supported by php.net/json_decode
 * Note: Use json_last_error() if you want to check for actual decoding error
 *
 * @param  string $data Required. Data to decode as JSON.
 * @return mixed        Decoded JSON if $data is string and no error occured during decoding. Otherwise return original $data
 */
function wlm_maybe_json_decode($data)
{
    if (is_string($data)) {
        $x = json_decode(...func_get_args());
        if (JSON_ERROR_NONE === json_last_error()) {
            $data = $x;
        }
    }
    return $data;
}

/**
 * Attempt to fix corrupted serialized data if necessary
 * Returns $serialized_string if it doesn't even look like serialized data
 *
 * @param  string $serialized_string Broken serialized data to fix.
 * @return string                    Repaired serialized data
 */
function wlm_serialize_corrector($serialized_string)
{
    if (! is_string($serialized_string)) {
        // Strings only.
        return $serialized_string;
    }

    // Serialized arrays, objects and strings only and if it's actually broken.
    if (preg_match('/^([aos]):\d+:/i', $serialized_string, $match) && ! is_serialized($serialized_string)) {
        // Make sure string lengths are correct.
        $fixed_string = preg_replace_callback(
            '/s\:(\d+)\:\"(.*?)\";/s',
            function ($matches) {
                return 's:' . strlen($matches[2]) . ':"' . $matches[2] . '";';
            },
            $serialized_string
        );

        if ('o' === $match[1]) { // Objects must begin with uppercase 'O'.
            $fixed_string[0] = 'O';
        } elseif (in_array($match[1], ['A', 'S'], true)) { // Arrays and strings must begin with lowercase 'a' and 's'.
            $fixed_string[0] = strtolower($match[1]);
        }

        if (is_serialized($fixed_string)) {
            return $fixed_string;
        }
    }

    // Return original string.
    return $serialized_string;
}

/**
 * Serializes data using WordPress' `maybe_serialize()` function
 * BUT only if $data is not already serialized.
 *
 * Note: Calling `maybe_serialize()` alone will re-serialize an
 * already serialized string thus the need for this function
 *
 * @param  mixed $data Data to serialize.
 * @return string      Serialized data
 */
function wlm_maybe_serialize($data)
{
    if (! is_serialized($data)) {
        $data = maybe_serialize($data);
    }
    return $data;
}

/**
 * Unserializes data using WordPress' `maybe_unserialize()` function
 * but also ensures to take care of possible double serialization caused
 * by WordPress' `maybe_serialize()` weird behavior of re-serializing
 * already serialized data
 *
 * This also caches the result of unserialized data to improve performance.
 *
 * @param  string  $data     Data to unserialize.
 * @param  boolean $no_cache True to prevent checking cache. Default is false.
 * @return mixed        Unserialized data
 */
function wlm_maybe_unserialize($data, $no_cache = false)
{
    static $unserialized = [];
    if (! is_serialized($data)) {
        return $data;
    }

    if (! $no_cache) {
        // Return cached data if it exists.
        if (isset($unserialized[ $data ])) {
            return $unserialized[ $data ];
        }
    }

    // Unserialize...
    $orig_data = $data;
    do {
        $data = maybe_unserialize(wlm_serialize_corrector($data));
    } while (is_serialized($data));

    // Save to cache...
    $unserialized[ $orig_data ] = $data;

    return $data;
}

/**
 * Returns gravatar URL from email
 *
 * @param  string $email  Email address. Default value: Current logged-in user's email address.
 * @param  array  $params Array of arguments to pass to gravatar.
 *                        Default value: [ 's' => 512, 'd' => 'mm', 'r' => '' ]. See https://codex.wordpress.org/Using_Gravatars.
 * @return string         Gravatar URL.
 */
function wlm_get_gravatar($email = null, $params = [])
{
    $data   = isset($data) ? $data : null;
    $params = wp_parse_args(
        $data,
        [
            's' => 512,
            'd' => 'mm',
            'r' => '',
        ]
    );

    if (empty($email)) {
        $email = (string) wlm_arrval(wp_get_current_user(), 'user_email');
    }
    return sprintf('//www.gravatar.com/avatar/%s?%s', md5(strtolower(wlm_trim($email))), http_build_query($params));
}

/**
 * Checks if a file is a valid png, jpeg or gif image
 * by calling finfo_open() and finfo_file() or exif_imagetype()
 * or getimagesize() - whichever is available in that order
 *
 * Returns false if none of the above functions is available
 *
 * @since  3.9
 * @param  string $path_to_image Path of image to check.
 * @return boolean
 */
function wlm_is_image($path_to_image)
{
    // Check using finfo functions.
    if (function_exists('finfo_open') && function_exists('finfo_file')) {
        $info = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path_to_image);
        return in_array($info, ['image/png', 'image/jpeg', 'image/gif'], true);
    }

    // Check using exif_imagetype.
    if (function_exists('exif_imagetype')) {
        $info = exif_imagetype($path_to_image);
        return in_array((int) $info, [IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF], true);
    }

    // Check using getimagesize.
    if (function_exists('getimagesize')) {
        $info = getimagesize($path_to_image);
        return is_array($info) && in_array((int) wlm_arrval($info, 2), [IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF], true);
    }

    // No available functions to check, return false.
    return false;
}

/**
 * Generates a username based on the userdata and format provided
 *
 * Returns false if data provided isnt enough
 *
 * @since  3.9
 * @param  array $userdata        Array of user's data.
 * @param  array $username_format The format of the username you want.
 * @return mixed                  The username string generated or False if some data are missing.
 */
function wlm_generate_username($userdata, $username_format)
{

    if (! isset($userdata['first_name']) && ! isset($userdata['last_name']) && ! isset($userdata['email'])) {
        return false;
    }
    if (empty($username_format)) {
        return false;
    }

    // Step 1. replace {name}, {fname}, {lname} and {email} shortcodes.
    $shortcodes = [
        '{name}'  => wlm_trim($userdata['first_name'] . ' ' . $userdata['last_name']),
        '{fname}' => wlm_trim($userdata['first_name']),
        '{lname}' => wlm_trim($userdata['last_name']),
        '{email}' => wlm_trim($userdata['email']),
    ];
    $username   = str_replace(array_keys($shortcodes), $shortcodes, $username_format);

    // Step 2. replace {rand_ltr n}, {rand_num n} and {rand_mix n} shortcodes.
    if (preg_match_all('/{rand_(ltr|num|mix)\s+(\d+)}/', $username_format, $matches)) {
        $ltr = implode('', range('a', 'z') + range('A', 'Z'));
        $num = implode(range(0, 9));
        foreach ($matches[0] as $index => $code) {
            $pos = strpos($username, $code);
            if (false !== $pos) {
                $length = $matches[2][ $index ];
                switch ($matches[1][ $index ]) {
                    case 'ltr':
                        $ltr     = str_shuffle($ltr);
                        $replace = substr($ltr, 0, $length);
                        break;
                    case 'num':
                        $num     = str_shuffle($num);
                        $replace = substr($num, 0, $length);
                        break;
                    default:
                        $ltr     = str_shuffle($ltr);
                        $replace = substr($ltr, 0, ceil($length / 2));

                        $num      = str_shuffle($num);
                        $replace .= substr($num, 0, floor($length / 2));

                        $replace = str_shuffle($replace);
                }
                $username = substr_replace($username, $replace, $pos, strlen($code));
            }
        }
    }

    // Step 3. sanitize the generated shortcode and trim it to WP's 60-character limit.
    $username = substr(trim(sanitize_user(preg_replace('/\s+/', ' ', $username), true)), 0, 60);

    if (empty($username)) {
        return false;
    }

    // Step 4. make sure the username is unique. if not keep appending -n until it's unique.
    $counter = 2;
    while (get_user_by('login', str_replace(',', '-', $username))) {
        $replace = ',' . $counter;
        if (strlen($username) >= 60) {
            $username = substr($username, 0, 60 - strlen($replace));
        }
        $username  = preg_replace('/\,\d+$/', '', $username);
        $username .= $replace;
        ++$counter;
    }
    $username = str_replace(',', '-', $username);

    return $username;
}

/**
 * Get version of Apache
 * Uses `apache_get_version()` if available.
 * Otherwise use `wlm_server_data()['SERVER_SOFTWARE']`
 *
 * @return string|false Apache version number if it's found or false otherwise
 */
function wlm_get_apache_version()
{
    if (function_exists('apache_get_version')) {
        $version = apache_get_version();
    } else {
        $version = wlm_server_data()['SERVER_SOFTWARE'];
    }
    if (preg_match('/Apache\/([0-9\.]+)/i', $version, $version)) {
        return $version[1];
    }
    return false;
}

/**
 * Triggers a E_USER_DEPRECATED error for methods
 *
 * @param string $old_method_name Old method name.
 * @param string $new_method_name New method name.
 */
function wlm_deprecated_method_error_log($old_method_name, $new_method_name)
{
    static $msgs = [];

    $debug = debug_backtrace(0, 2); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_debug_backtrace.
    // Translators: 1: old method name, 2: new method name, 3: File path, 4: Line number.
    $msg = sprintf('Deprecated: Call to deprecated method %1$s() at %3$s:%4$d. Use %2$s() instead.', $old_method_name, $new_method_name, $debug[1]['file'], $debug[1]['line']);

    // Prevent multiple log entries of the same error.
    if (in_array($msg, $msgs, true)) {
        return;
    }
    $msgs[] = $msg;

    error_log($msg); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log.
}

/**
 * Triggers a E_USER_DEPRECATED error for properties
 *
 * @param string $old_property_name Old property name.
 * @param string $new_property_name New property name.
 */
function wlm_deprecated_property_error_log($old_property_name, $new_property_name)
{
    $debug = debug_backtrace(0, 2); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_debug_backtrace.
    // Translators: 1: old property name, 2: new property name, 3: File path, 4: Line number.
    $msg = sprintf('Deprecated: Access to deprecated property "%1$s" at %3$s:%4$d. Use "%2$s" instead.', $old_property_name, $new_property_name, $debug[1]['file'], $debug[1]['line']);
    error_log($msg); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log.
}

/**
 * Return Unix timestamp a date/time string
 *
 * @param  string $date_string Date/time string.
 * @return integer|false Unix timestamp or false on error
 */
function wlm_strtotime($date_string, $from_wp_format = false)
{
    if ('d/m/Y' === get_option('date_format')) {
        $date_string = str_replace('/', '-', $date_string);
    }

    if ($from_wp_format) {
        $dt = date_create_immutable_from_format(get_option('date_format') . ' ' . get_option('time_format'), $date_string, wp_timezone());
    } else {
        $dt = date_create_immutable($date_string, wp_timezone());
    }

    return is_object($dt) ? (int) $dt->format('U') : false;
}

/**
 * Wrapper for date to return date based on WordPress timezone.
 *
 * Uses WP's wp_date() if it's defined, else
 * Uses PHP's gmdate() if $timestamp is specified, else
 * Uses WP's current_time().
 *
 * @since 3.12
 *
 * @param  string  $format    Date format. Default 'Y-m-d H:i:s'.
 * @param  integer $timestamp Timestamp. Optional.
 * @return string|false      Formatted date or false on error.
 */
function wlm_date($format = 'Y-m-d H:i:s', $timestamp = null)
{
    if (! is_null($timestamp)) {
        $timestamp = (int) $timestamp;
    }
    if (function_exists('wp_date')) {
        return wp_date($format, $timestamp);
    }

    if ($timestamp) {
        return gmdate($format, $timestamp + get_option('gmt_offset') * HOUR_IN_SECONDS);
    }

    return current_time($format);
}

/**
 * Retrieves the timezone from WP site settings as a string.
 *
 * Uses WP's wp_timezone_string() if defined, else
 * Uses get_option('timezone_string') if defined, else
 * Computes timezone from get_option('gmt_offset')
 *
 * @since 3.12
 *
 * @return string timezone
 */
function wlm_timezone_string()
{
    if (function_exists('wp_timezone_string')) {
        return wp_timezone_string();
    }

    // Code below copied from WP 5.3.0 wp_timezone_string().
    $timezone_string = get_option('timezone_string');

    if ($timezone_string) {
        return $timezone_string;
    }

    $offset  = (float) get_option('gmt_offset');
    $hours   = (int) $offset;
    $minutes = ( $offset - $hours );

    $sign      = ( $offset < 0 ) ? '-' : '+';
    $abs_hour  = abs($hours);
    $abs_mins  = abs($minutes * 60);
    $tz_offset = sprintf('%s%02d:%02d', $sign, $abs_hour, $abs_mins);

    return $tz_offset;
}

/**
 * Enqueues and prints CSS stylesheet immediately.
 *
 * @param string $url Stylesheet URL.
 */
function wlm_print_style($url)
{
    if (false !== strpos($url, '/')) {
        $handle = md5($url);
        wp_enqueue_style($handle, $url, [], defined('WLM_PLUGIN_VERSION') ? WLM_PLUGIN_VERSION : gmdate('YmdH'));
    } else {
        $handle = $url;
    }
    wp_print_styles($handle);
}

/**
 * Enqueues and returns CSS stylesheet markup.
 *
 * @param  string $url Stylesheet URL.
 * @return string Stylesheet markup.
 */
function wlm_get_style_markup($url)
{
    ob_start();
    wlm_print_style($url);
    return ob_get_clean();
}

/**
 * Enqueues and prints pr returns a script immediately.
 *
 * @param string  $url    Script URL.
 * @param boolean $return True to return instead of printing.
 */
function wlm_print_script($url, $return = false)
{
    if (false !== strpos($url, '/')) {
        $handle = md5($url);
        wp_enqueue_script($handle, $url, [], defined('WLM_PLUGIN_VERSION') ? WLM_PLUGIN_VERSION : gmdate('YmdH'), false);
    } else {
        $handle = $url;
    }
    wp_print_scripts($handle);
}

/**
 * Enqueues and returns script markup.
 *
 * @param  string $url Script URL.
 * @return string Script markup.
 */
function wlm_get_script_markup($url)
{
    ob_start();
    wlm_print_script($url);
    return ob_get_clean();
}

/**
 * Return the first parameter if it's non-empty.
 * Otherwise, return the second value.
 *
 * This function is meant as an alternative to short ternaries which
 * is not allowed by the WordPress.PHP.DisallowShortTernary.Found sniff
 *
 * @param  mixed          $first_value            First value.
 * @param  mixed|callback $second_value           Second value.
 * @param  array          ...$callback_parameters Callback parameters if $second_value is callable.
 * @return mixed
 */
function wlm_or($first_value, $second_value, ...$callback_parameters)
{
    if ($first_value) {
        return $first_value;
    }
    if (is_callable($second_value)) {
        $second_value = call_user_func_array($second_value, $callback_parameters);
    }
    return $second_value;
}

if (! function_exists('esc_js_e')) {
    /**
     * Display translated text that has been escaped for safe use in Javascript output.
     *
     * @param string $string String to translate and escape.
     */
    function esc_js_e($string)
    {
        echo esc_js(__($string, 'wishlist-member'));
    }
}

if (! function_exists('esc_js__')) {
    /**
     * Retrieve the translation of $text and escapes it for safe use in HTML output.
     *
     * @param  string $string String to translate and escape.
     * @return string Escaped translated string.
     */
    function esc_js__($string)
    {
        return esc_js(__($string, 'wishlist-member'));
    }
}

/**
 * Create and return POST \WishListMember\Input_Array object or $_POST array
 *
 * @param  boolean $full True to return full $_POST array instead of Input_Array object. Default false.
 * @return \WishListMember\Input_Array|array
 */
function wlm_post_data($full = false)
{
    static $data = null;
    if (is_null($data)) {
        $data = new \WishListMember\Input_Array('post');
    }
    return $full ? $data() : $data;
}

/**
 * Create and return GET \WishListMember\Input_Array object or $_GET array
 *
 * @param  boolean $full True to return full $_GET array instead of Input_Array object. Default false.
 * @return \WishListMember\Input_Array|array
 */
function wlm_get_data($full = false)
{
    static $data = null;
    if (is_null($data)) {
        $data = new \WishListMember\Input_Array('get');
    }
    return $full ? $data() : $data;
}

/**
 * Create and return REQUEST \WishListMember\Input_Array object or $_REQUEST array
 *
 * @param  boolean $full True to return full $_REQUEST array instead of Input_Array object. Default false.
 * @return \WishListMember\Input_Array|array
 */
function wlm_request_data($full = false)
{
    static $data = null;
    if (is_null($data)) {
        $data = new \WishListMember\Input_Array('request');
    }
    return $full ? $data() : $data;
}

/**
 * Create and return SERVER \WishListMember\Input_Array object or $_SERVER array
 *
 * @param  boolean $full True to return full $_SERVER array instead of Input_Array object. Default false.
 * @return \WishListMember\Input_Array|array
 */
function wlm_server_data($full = false)
{
    static $data = null;
    if (is_null($data)) {
        $data = new \WishListMember\Input_Array('server');
    }
    return $full ? $data() : $data;
}

/**
 * Wrapper to PHP trim() that casts the $string parameter to string
 *
 * @param  string $string     The string that will be trimmed.
 * @param  string $characters Optionally, the stripped characters can also be specified
 *                            using the characters parameter. Simply list all characters
 *                            that you want to be stripped. With .. you can specify a
 *                            range of characters.
 * @return string             The trimmed string.
 */
function wlm_trim($string, $characters = " \n\r\t\v\x00")
{
    return trim((string) $string, $characters);
}

/**
 * Retrieves our nonce value. Creates it if needed.
 *
 * @return string
 */
function get_wlm_nonce()
{
    static $nonce;
    if (empty($nonce)) {
        $nonce = wp_create_nonce('wishlist-member');
    }
    return $nonce;
}

/**
 * Retrieves our nonce field name.
 *
 * @return string Nonce field name.
 */
function get_wlm_nonce_field_name()
{
    return '_wishlist-member_nonce';
}

/**
 * Returns our nonce hidden input field
 *
 * @return string
 */
function get_wlm_nonce_field()
{
    return sprintf('<input type="hidden" name="%s" value="%s">', get_wlm_nonce_field_name(), get_wlm_nonce());
}

/**
 * Prints out our nonce field
 */
function print_wlm_nonce_field() {
    echo wp_kses(
        get_wlm_nonce_field(),
        [
            'input' => [
                'type'  => true,
                'name'  => true,
                'value' => true,
            ],
        ]
    );
}

/**
 * Verify nonces for $_POST and $_GET if they exist
 * Defines 'WLM_GET_NONCED' and 'WLM_POST_NONCED' constants.
 */
function verify_wlm_nonces()
{
    $get  = wp_unslash($_GET);
    $post = wp_unslash($_POST);
    if (! defined('WLM_GET_NONCED')) {
        define('WLM_GET_NONCED', wp_verify_nonce(wlm_arrval($get,get_wlm_nonce_field_name()), 'wishlist-member'));
    }
    if (! defined('WLM_POST_NONCED')) {
        define('WLM_POST_NONCED', wp_verify_nonce(wlm_arrval($post,get_wlm_nonce_field_name()), 'wishlist-member'));
    }
}

/**
 * Make a request to Caseproof's Mothership API and return the response.
 *
 * @throws \Exception If there's an error.
 * @param  string  $endpoint Endpoint to request.
 * @param  array   $args     Arguments to pass to the endpoint.
 * @param  string  $method   HTTP method to use. Default 'get'.
 * @param  boolean $blocking True to block until response is received. Default true.
 * @return boolean|array     True if $blocking is false. False if $blocking is true and there's an error. Response if $blocking is true and there's no error.
 */
function wlm_mothership_request($endpoint, $args = [], $method = 'get', $blocking = true)
{
    $resp = wp_remote_request(
        WLM_MOTHERSHIP_URI . '/' . trim(trim($endpoint, '/')),
        [
            'method'   => strtoupper($method),
            'body'     => $args,
            'timeout'  => 15,
            'blocking' => $blocking,
        ]
    );

    if (false === $blocking) {
        return true;
    }

    if (is_wp_error($resp)) {
        throw new \Exception(__('You had an HTTP error connecting to Caseproof\'s Mothership API', 'wishlist-member'));
    } else {
        $json_res = json_decode($resp['body']);
        if (null !== $json_res) {
            if (isset($json_res->error)) {
                throw new \Exception($json_res->error);
            } else {
                return $json_res;
            }
        } else {
            throw new \Exception(__('Your License Key is invalid.', 'wishlist-member'));
        }
    }
    return false;
}
