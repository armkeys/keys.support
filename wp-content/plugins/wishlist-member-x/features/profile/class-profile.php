<?php

/**
 * Profile Photo
 *
 * @package WishListMember\Features
 */

namespace WishListMember\Features;

/**
 * Propfile Photo Feature Class
 */
class Profile
{
    /**
     * Constructor
     */
    public function __construct()
    {
        // Profile Form.
        add_shortcode('wlm_profileform', [$this, 'wlm_profileform_shortcode']);

        // Profile Photo.
        add_shortcode('wlm_profilephoto', [$this, 'wlm_profilephoto_shortcode']);

        // Gravatar.
        add_filter('pre_get_avatar_data', [$this, 'pre_get_avatar_data'], 10, 2);
        add_action('edit_user_profile', [$this, 'hide_gravatar_link_in_wp_profile']);
        add_action('show_user_profile', [$this, 'hide_gravatar_link_in_wp_profile']);

        // Save profile.
        add_action('init', [$this, 'save_profile']);
    }

    /**
     * Handler for wlm_profileform shortcode
     *
     * @param array $atts {
     *     Shortcode attributes.
     *
     * @type   string $profile_photo     'show' to display profile photo
     * @type   string $list_subscription 'show' to display mailing list subscription checkbox
     *
     *     Deprecated attributes:
     * @type   string $hide_mailinglist  'yes', 'true' or 1 to hide mailing list subscription (use list_subscription instead)
     * @type   string $nologin           Do not display login form for non-logged in users
     * }
     * @return string       Profile form if user is logged in. Login form if not.
     */
    public function wlm_profileform_shortcode($atts)
    {
        global $wp;
        static $processed = false;

        /**
         * Filter the processed status of the wlm_profileform shortcode
         *
         * @param boolean $processed Whether the shortcode is already processed or not.
         */
        $processed = (bool) apply_filters('wishlistmember_profile_form_shortcode_is_processed', $processed);

        if (! empty($processed)) {
            return ''; // Process only once.
        }
        $processed = true;

        // $_GET data.
        $get = wlm_get_data(true);
        unset($get['wlmdebug']);

        // Request URL.
        $request_uri = home_url(add_query_arg($get, $wp->request));

        if (! is_user_logged_in()) { // User not logged in.
            if (empty($atts['nologin'])) { // Display login form.
                return do_shortcode(sprintf('[wlm_loginform redirect="%s"]', $request_uri));
            } else { // Display nothing.
                return '';
            }
        }

        // $user    = wp_get_current_user();
        $user    = wishlistmember_instance()->get_user_data(get_current_user_id());
        $options = [
            $user->user_login => $user->user_login,
            $user->nickname   => $user->nickname,
        ];
        if ($user->first_name) {
            $options[ $user->first_name ] = $user->first_name;
        }
        if ($user->last_name) {
            $options[ $user->last_name ] = $user->last_name;
        }
        if ($user->first_name && $user->last_name) {
            $fl             = implode(' ', [$user->first_name, $user->last_name]);
            $lf             = implode(' ', [$user->last_name, $user->first_name]);
            $options[ $fl ] = $fl;
            $options[ $lf ] = $lf;
        }

        $required = [];
        if (isset($get['wlm_required']) && is_array($get['wlm_required']) && $get['wlm_required']) {
            foreach ($get['wlm_required'] as $r) {
                switch ($r) {
                    case 'nickname':
                        $required[] = sprintf('<p>%s</p>', __('Nickname required', 'wishlist-member'));
                        break;
                    case 'user_email':
                        $required[] = sprintf('<p>%s</p>', __('Email required', 'wishlist-member'));
                        break;
                    case 'new_pass':
                                $required[] = sprintf('<p>%s</p>', __('Password not accepted', 'wishlist-member'));
                        break;
                }
            }
        }

        $fields  = '';
        $fields .= wlm_form_field(
            [
                'type'  => 'hidden',
                'name'  => '_wlm3_nonce',
                'value' => wp_create_nonce('update-profile_' . $user->ID),
            ]
        );
        $fields .= wlm_form_field(
            [
                'type'  => 'hidden',
                'name'  => 'referrer',
                'value' => $request_uri,
            ]
        );
        $fields .= wlm_form_field(
            [
                'type'  => 'hidden',
                'name'  => 'WishListMemberAction',
                'value' => 'UpdateUserProfile',
            ]
        );

        $profile_photo = 'hide' !== strtolower((string) ( wlm_arrval($atts, 'profile_photo') ?? '' ));
        if ($profile_photo) {
            $fields .= wlm_form_field(
                [
                    'type'  => 'profile_photo',
                    'name'  => 'profile_photo',
                    'value' => wlm_arrval(get_user_meta($user->ID, 'profile_photo', true), 'url'),
                ]
            );
        }

        $profile_first_name = 'hide' !== strtolower((string) ( wlm_arrval($atts, 'first_name') ?? '' ));
        if ($profile_first_name) {
            $fields .= wlm_form_field(
                [
                    'type'     => 'text',
                    'name'     => 'first_name',
                    'onchange' => 'wlm3_update_displayname(this)',
                    'value'    => $user->first_name,
                    'label'    => __(
                        'First Name',
                        'wishlist-member'
                    ),
                ]
            );
        }
        $profile_last_name = 'hide' !== strtolower((string) ( wlm_arrval($atts, 'last_name') ?? '' ));
        if ($profile_last_name) {
            $fields .= wlm_form_field(
                [
                    'type'     => 'text',
                    'name'     => 'last_name',
                    'onchange' => 'wlm3_update_displayname(this)',
                    'value'    => $user->last_name,
                    'label'    => __(
                        'Last Name',
                        'wishlist-member'
                    ),
                ]
            );
        }
        $profile_nickname = 'hide' !== strtolower((string) ( wlm_arrval($atts, 'nickname') ?? '' ));
        if ($profile_nickname) {
            $fields .= wlm_form_field(
                [
                    'type'     => 'text',
                    'name'     => 'nickname',
                    'onchange' => 'wlm3_update_displayname(this)',
                    'value'    => $user->nickname,
                    'label'    => __(
                        'Nickname',
                        'wishlist-member'
                    ),
                ]
            );
        }
        $profile_display_name = 'hide' !== strtolower((string) ( wlm_arrval($atts, 'display_name') ?? '' ));
        if ($profile_display_name) {
            $fields .= wlm_form_field(
                [
                    'type'    => 'select',
                    'name'    => 'display_name',
                    'value'   => $user->display_name,
                    'options' => $options,
                    'label'   => __(
                        'Display Name',
                        'wishlist-member'
                    ),
                ]
            );
        }
        $profile_email = 'hide' !== strtolower((string) ( wlm_arrval($atts, 'email') ?? '' ));
        if ($profile_email) {
            $fields .= wlm_form_field(
                [
                    'type'  => 'email',
                    'name'  => 'user_email',
                    'value' => $user->user_email,
                    'label' => __(
                        'Email',
                        'wishlist-member'
                    ),
                ]
            );
        }

        $profile_address = explode('|', strtolower((string) ( wlm_arrval($atts, 'address') ?? '' )));
        if (count($profile_address)) {
            if (in_array('company', $profile_address, true)) {
                $fields .= wlm_form_field(
                    [
                        'type'  => 'text',
                        'name'  => 'wpm_useraddress[company]',
                        'value' => wlm_arrval($user, 'wpm_useraddress', 'company'),
                        'label' => __(
                            'Company',
                            'wishlist-member'
                        ),
                    ]
                );
            }
            $has_address1 = false;
            if (in_array('address1', $profile_address, true)) {
                $has_address1 = true;
                $fields      .= wlm_form_field(
                    [
                        'type'  => 'text',
                        'name'  => 'wpm_useraddress[address1]',
                        'value' => wlm_arrval($user, 'wpm_useraddress', 'address1'),
                        'label' => __(
                            'Address',
                            'wishlist-member'
                        ),
                    ]
                );
            }
            if (in_array('address2', $profile_address, true)) {
                $x = [
                    'type'  => 'text',
                    'name'  => 'wpm_useraddress[address2]',
                    'value' => wlm_arrval($user, 'wpm_useraddress', 'address2'),
                ];
                if (! $has_address1) {
                    $x['label'] = __(
                        'Address',
                        'wishlist-member'
                    );
                }
                $fields .= wlm_form_field($x);
            }
            if (in_array('city', $profile_address, true)) {
                $fields .= wlm_form_field(
                    [
                        'type'  => 'text',
                        'name'  => 'wpm_useraddress[city]',
                        'value' => wlm_arrval($user, 'wpm_useraddress', 'city'),
                        'label' => __(
                            'City',
                            'wishlist-member'
                        ),
                    ]
                );
            }
            if (in_array('state', $profile_address, true)) {
                $fields .= wlm_form_field(
                    [
                        'type'  => 'text',
                        'name'  => 'wpm_useraddress[state]',
                        'value' => wlm_arrval($user, 'wpm_useraddress', 'state'),
                        'label' => __(
                            'State',
                            'wishlist-member'
                        ),
                    ]
                );
            }
            if (in_array('state', $profile_address, true)) {
                $fields .= wlm_form_field(
                    [
                        'type'  => 'text',
                        'name'  => 'wpm_useraddress[zip]',
                        'value' => wlm_arrval($user, 'wpm_useraddress', 'zip'),
                        'size'  => 10,
                        'label' => __(
                            'Zip',
                            'wishlist-member'
                        ),
                    ]
                );
            }
            if (in_array('country', $profile_address, true)) {
                $fields .= wlm_form_field(
                    [
                        'type'    => 'select',
                        'name'    => 'wpm_useraddress[country]',
                        'value'   => wlm_arrval($user, 'wpm_useraddress', 'country'),
                        'options' => array_combine(
                            require WLM_PLUGIN_DIR . '/helpers/countries.php',
                            require WLM_PLUGIN_DIR . '/helpers/countries.php'
                        ),
                        'label'   => __(
                            'Country',
                            'wishlist-member'
                        ),
                    ]
                );
            }
        }

        $show_mailinglist = true;
        if (isset($atts['hide_mailinglist'])) { // Legacy attribute. kept for the sake of backwards compatibility.
            $show_mailinglist = ! in_array(strtolower((string) ( wlm_arrval($atts, 'hide_mailinglist') ?? '' )), ['yes', 'true', 1]);
        }
        if (isset($atts['list_subscription'])) {
            $show_mailinglist = 'show' === strtolower((string) ( wlm_arrval($atts, 'list_subscription') ?? '' ));
        }

        if ($show_mailinglist) {
            $fields .= wlm_form_field(
                [
                    'type'    => 'checkbox',
                    'name'    => 'wlm_subscribe',
                    'value'   => (int) ( ! (bool) wishlistmember_instance()->Get_UserMeta($user->ID, 'wlm_unsubscribe') ),
                    'options' => [
                        '1' => __(
                            'Subscribed to Mailing List',
                            'wishlist-member'
                        ),
                    ],
                ]
            );
        }
        $profile_user_password = 'hide' !== strtolower((string) ( wlm_arrval($atts, 'user_password') ?? '' ));
        if ($profile_user_password) {
            $fields .= wlm_form_field(
                [
                    'type'  => 'password_generator',
                    'name'  => 'new_pass',
                    'value' => '',
                    'label' => __(
                        'New Password',
                        'wishlist-member'
                    ),
                ]
            );
        }
        $fields .= wlm_form_field(
            [
                'type'  => 'submit',
                'name'  => 'save-profile',
                'value' => __(
                    'Update Profile',
                    'wishlist-member'
                ),
            ]
        );

        if (apply_filters('wishlistmember_profile_form_shortcode_include_script_markup', true)) {
            $javascript = wlm_get_script_markup(plugin_dir_url(__FILE__) . 'script.js');
        }

        if ($required) {
            $required = sprintf('<div class="wlm3-profile-error">%s</div>', implode('', $required));
        } else {
            $required = '';
        }

        if ('saved' === wlm_arrval($_REQUEST, 'wlm_profile')) {
            $required = sprintf('<div class="wlm3-profile-ok"><p>%s</p></div>', __('Profile saved', 'wishlist-member'));
        } else {
            $message = '';
        }

        /**
         * Add Filterable variables on the profile form shortcode's before and after HTML content.
         *
         * $before_profile_shortcode HTML before the profile form.
         * $after_profile_shortcode  HTML after the profile form.
         */
        $before_profile_shortcode = apply_filters('wishlistmember_profile_form_shortcode_before', '');
        $after_profile_shortcode  = apply_filters('wishlistmember_profile_form_shortcode_after', '');

        return sprintf(
            '%s<form name="wishlist-member-profile-form" method="POST" action="%s" enctype="multipart/form-data"><div id="wishlist-member-profile-form" class="wlm3-form">%s%s%s</div></form>%s %s',
            $before_profile_shortcode,
            user_admin_url(),
            $message,
            $required,
            $fields,
            $after_profile_shortcode,
            $javascript
        );
    }

    /**
     * Handler for wlm_profilephoto shortcode
     *
     * @param array $atts {
     *     Shortcode attributes.
     *
     * @type   string $cropping  'circle', 'square', ''
     * @type   int    $size      Default 150
     * @type   int    $width     Width
     * @type   int    $height    Height
     * @type   string $class     Additional class names to add
     * @type   string $url_only  Truish to return just the photo URL
     * }
     * @return [type]       [description]
     */
    public function wlm_profilephoto_shortcode($atts)
    {
        extract(
            shortcode_atts(
                [
                    'cropping' => '',
                    'size'     => '150',
                    'width'    => '',
                    'height'   => '',
                    'class'    => '',
                    'url_only' => '',
                ],
                $atts
            )
        );

        $profile = wlm_or(( new \WishListMember\User(get_current_user_id()) )->get_profile_photo(), wishlistmember_instance()->plugin_url3 . '/assets/images/grey.png');
        if ($url_only) {
            return $profile;
        }

        $cropping = strtolower(wlm_trim($cropping));
        if (in_array($cropping, ['circle', 'square'], true)) {
            $style  = sprintf('width:%1$dpx;height:%1$dpx;', $size);
            $style .= 'object-fit:cover;';
            if ('circle' === $cropping) {
                $style .= 'border-radius: 50%;';
            }
        } else {
            $width  = $width ? sprintf('width:%dpx;', $width) : '';
            $height = $height ? sprintf('height:%dpx;', $height) : '';
            $style  = $width . $height;
        }

        return sprintf('<img src="%s" class="%s" style="%s">', $profile, $class, $style);
    }


    /**
     * Replace avatar URL with our profile photo URL if it exists.
     *
     * @wp-hook pre_get_vatar_data (https://developer.wordpress.org/reference/hooks/pre_get_avatar_data/)
     * @param   array $args        Data to filter. Arguments passed to get_avatar_data().
     * @param   mixed $id_or_email Gravatar to retrieve.
     * @return  array              Filtered data.
     */
    public function pre_get_avatar_data($args, $id_or_email)
    {

        // Get the user ID.
        if (is_numeric($id_or_email)) { // Numeric id.
            $id = absint($id_or_email);
        } elseif ($id_or_email instanceof WP_User) {
            $id = absint($id_or_email->id);
        } elseif ($id_or_email instanceof WP_Post) {
            $id = absint($id_or_email->post_author);
        } elseif (is_object($id_or_email) && isset($id_or_email->comment_ID)) {
            $id = absint(wlm_arrval($id_or_email, 'user_id'));
            if (! $id && ! empty($id_or_email->comment_author_email)) {
                $id_or_email = $id_or_email->comment_author_email;
            }
        }
        if (empty($id) && is_string($id_or_email)) { // Maybe it's an email address.
            $id = absint(wlm_arrval(get_user_by('email', $id_or_email), 'ID'));
        }

        if (! empty($id)) { // Id found.
            $profile = get_user_meta($id, 'profile_photo', true);
            if ($profile) { // Profile photo found.
                if ('gravatar' !== wlm_arrval($profile, 'file')) { // Profile photo not gravatar.
                    $url = wlm_arrval($profile, 'url');
                    if ($url) { // Url found.
                        $args['url'] = $url; // Replace gravatar url.
                    }
                }
            }
        }
        return $args;
    }

    /**
     * Hide gravatar in WP Profile
     *
     * @wp-hook edit_user_profile
     * @wp-hook show_user_profile
     */
    public function hide_gravatar_link_in_wp_profile($profile_user)
    {
        $photo = get_user_meta($profile_user->ID, 'profile_photo', true);
        if ($photo && 'gravatar' !== wlm_arrval($photo, 'file')) {
            echo '<style>.user-profile-picture .description { display: none; }</style>';
        }
    }

    /**
     * Save user profile submitted by the wlm_profileform shortcode
     *
     * @wp-hook init
     * @param   array $data Optional user profile data. $_POST if not set.
     */
    public function save_profile($data = [])
    {
        if ('UpdateUserProfile' !== wlm_post_data()['WishListMemberAction']) {
            return;
        }

        $user = wp_get_current_user();
        if (empty($user->ID)) { // Logged-in user required.
            return;
        }

        $data = ( $data && is_array($data) ) ? $data : wlm_post_data(true);

        if (! wp_verify_nonce($data['_wlm3_nonce'], 'update-profile_' . $user->ID)) {
            wp_nonce_ays('');
        }

        $data = wp_parse_args(
            $data,
            [
                'first_name'      => $user->first_name,
                'last_name'       => $user->last_name,
                'nickname'        => $user->nickname,
                'display_name'    => $user->display_name,
                'user_email'      => $user->user_email,
                'wlm_subscribe'   => '',
                'referrer'        => '',
                'new_pass'        => '',
                'profile_photo'   => '',
                'wpm_useraddress' => '',
            ]
        );
        $data = array_map('wlm_maybe_serialize', $data);
        $data = array_map('trim', $data);

        if (empty($data['referrer'])) {
            $data['referrer'] = admin_url('profile.php');
        }

        $error = ['wlm_required' => []];
        if (! $data['nickname']) {
            $error['wlm_required'][] = 'nickname';
        }
        if (! $data['user_email']) {
            $error['wlm_required'][] = 'user_email';
        }

        if ($error['wlm_required']) {
            wp_safe_redirect(remove_query_arg('wlm_profile', add_query_arg($error, $data['referrer'])));
            exit;
        }

        $udata       = array_intersect_key($data, array_flip(['first_name', 'last_name', 'nickname', 'display_name', 'user_email']));
        $udata['ID'] = $user->ID;

        wp_update_user($udata);
        if ($data['new_pass']) {
            $passmin = wlm_or((int) wishlistmember_instance()->get_option('min_passlength'), 8);
            if (strlen($data['new_pass']) < $passmin || ( wishlistmember_instance()->get_option('strongpassword') && ! wlm_check_password_strength($data['new_pass']) )) {
                $error['wlm_required'][] = 'new_pass';
                wp_safe_redirect(remove_query_arg('wlm_profile', add_query_arg($error, $data['referrer'])));
                exit;
            }
            wp_set_password($data['new_pass'], $user->ID);
        }
        if ($data['wlm_subscribe']) {
            wishlistmember_instance()->Delete_UserMeta($user->ID, 'wlm_unsubscribe');
        } else {
            wishlistmember_instance()->Update_UserMeta($user->ID, 'wlm_unsubscribe', 1);
        }

        if ($data['wpm_useraddress']) {
            $wpm_useraddress = wishlistmember_instance()->Get_UserMeta($user->id, 'wpm_useraddress');
            $wpm_useraddress = array_merge((array) $wpm_useraddress, wlm_maybe_unserialize($data['wpm_useraddress']));
            wishlistmember_instance()->Update_UserMeta($user->id, 'wpm_useraddress', $wpm_useraddress);
        }

        // Begin: upload profile photo.
        $this->upload_profile_photo($user, wlm_arrval($data, 'profile_photo'));
        // End: upload profile photo.
        wp_safe_redirect(remove_query_arg('wlm_required', add_query_arg('wlm_profile', 'saved', $data['referrer'])));
        exit;
    }

    /**
     * Upload profile photo
     *
     * @param object $user               WP_User object.
     * @param string $profile_photo_type 'gravatar' or 'delete'. Otherwise it's a file upload.
     */
    private function upload_profile_photo($user, $profile_photo_type)
    {

        $file = wlm_arrval($_FILES, 'profile_photo-upload', 'tmp_name');
        if (! in_array($profile_photo_type, ['gravatar', 'delete'], true) && ! file_exists($file)) {
            return;
        }

        // Generate name.
        $name = explode('.', wlm_arrval($_FILES, 'profile_photo-upload', 'name'));
        $name = 'wishlist-member-profile-photo__' . $user->ID . '.' . array_pop($name);
        $name = 'wishlist-member-profile-photo__' . $user->ID . '.jpg';

        // Get existing file from user meta.
        $existing_file = wlm_arrval(get_user_meta($user->ID, 'profile_photo', true), 'file');

        if (! $existing_file) {
            // Compute existing file path if user meta is not found.
            $existing_file = wp_upload_dir('2000/01');
            $existing_file = $existing_file['path'] . '/' . $name;
        }

        // Backup existing file.
        if (file_exists($existing_file)) {
            rename($existing_file, $existing_file . '.bak');
        }

        if ($profile_photo_type && is_uploaded_file($file) && wlm_is_image($file)) {
            // Attempt to resize and crop to 512x512.
            $img_editor = wp_get_image_editor($file);
            if (! is_wp_error($img_editor)) {
                $img_editor->resize(512, 512, true);

                // Try to save as jpeg.
                $x = $img_editor->save($file, 'image/jpeg');
                if (is_wp_error($x)) {
                    // Try to save as png if jpeg save failed.
                    $x = $img_editor->save($file, 'image/png');
                }
                if (! is_wp_error($x)) {
                    // Change $file path on succesful image editor save.
                    $file = $x['path'];
                }
            }
            // Upload.
            $upload = wp_upload_bits($name, null, file_get_contents($file), '2000/01');
            if ($upload) {
                $upload['url'] = add_query_arg('d', time(), $upload['url']);
                update_user_meta($user->ID, 'profile_photo', $upload);
                @unlink($file);
            } else {
                // Restore backup.
                if (file_exists($existing_file . '.bak')) {
                    move($existing_file . '.bak', $existing_file);
                }
            }
        } elseif ('gravatar' === $profile_photo_type) {
            update_user_meta(
                $user->ID,
                'profile_photo',
                [
                    'url'  => wlm_get_gravatar($user->user_email),
                    'file' => 'gravatar',
                ]
            );
        } elseif ('delete' === $profile_photo_type) {
            // Delete profile pic.
            if (file_exists($existing_file)) {
                @unlink($existing_file);
            }
            delete_user_meta($user->ID, 'profile_photo');
        }
        // Delete backup profile pic.
        if (file_exists($existing_file . '.bak')) {
            @unlink($existing_file . '.bak');
        }
    }
}
