<?php

/**
 * Ajaxc login
 *
 * @package WishListMember\Features\Ajax_Login
 */

namespace WishListMember\Features\Ajax_Login;

defined('ABSPATH') || die();

add_action('wishlistmember_login_form_shortcode', __NAMESPACE__ . '\enqueue_ajax_login_script_and_styles', 10, 2);
/**
 * Enqueue ajax login scripts and styles
 * Called by 'wishlistmember_login_form_shortcode' filter.
 *
 * @param  string $form       Login form markup.
 * @param  string $form_class Form class.
 * @return string Unchanged form markup.
 */
function enqueue_ajax_login_script_and_styles($form, $form_class)
{
    if (! wp_script_is('wishlistmember_ajax_login', 'enqueued')) {
        // Enqueue JS.
        wp_enqueue_script('wishlistmember_ajax_login', plugins_url('ajax-login.js', __FILE__), 'jquery', WLM_PLUGIN_VERSION, true);
        wp_add_inline_script('wishlistmember_ajax_login', 'const wlm_ajax_login_url = ' . wp_json_encode(admin_url('admin-ajax.php')), 'before');
        wp_add_inline_script('wishlistmember_ajax_login', 'const wlm_ajax_login_nonce = ' . wp_json_encode(wp_create_nonce('wlm-ajax-login')), 'before');
        wp_add_inline_script('wishlistmember_ajax_login', 'const wlm_ajax_login_forms = []', 'before');

        // Enqueue CSS.
        wp_enqueue_style('wishlistmember_ajax_login', plugins_url('ajax-login.css', __FILE__), '', WLM_PLUGIN_VERSION);
    }

    // Add form classes to handle.
    wp_add_inline_script('wishlistmember_ajax_login', "wlm_ajax_login_forms.push('form." . esc_js($form_class) . "')", 'before');

    $additional_forms = '<form method="post" action="%1$s" class="%2$s" style="display: none;"><div class="wlm-ajax-login-notice -permanent">%3$s</div><div class="wlm3-form">%4$s<p><a href="%5$s">%6$s</a></p></div></form>';

    $otl = sprintf(
        $additional_forms,
        add_query_arg('action', 'wishlistmember-otl', wp_login_url()),
        'wlm-ajax-login-form-otl',
        __('Please enter your username or email address. You will receive an email message with a one-time login link.', 'wishlist-member'),
        wlm_form_field(
            [
                'label' => __('Username or Email Address', 'wishlist-member'),
                'type'  => 'text',
                'name'  => 'user_login',
            ]
        ) .
        wlm_form_field(
            [
                'type'  => 'submit',
                'name'  => 'wp-submit',
                'value' => __('Get One-Time Login Link', 'wishlist-member'),
            ]
        ) .
        wlm_form_field(
            [
                'type'  => 'hidden',
                'name'  => 'action',
                'value' => 'wishlistmember-otl',
            ]
        ),
        add_query_arg('action', 'login', wp_login_url()),
        __('Login using username/email and password', 'wishlist-member')
    );

    $lost = sprintf(
        $additional_forms,
        add_query_arg('action', 'lostpassword', wp_login_url()),
        'wlm-ajax-login-form-lost',
        __('Please enter your username or email address. You will receive an email message with instructions on how to reset your password.', 'wishlist-member'),
        wlm_form_field(
            [
                'label' => __('Username or Email Address', 'wishlist-member'),
                'type'  => 'text',
                'name'  => 'user_login',
            ]
        ) .
        wlm_form_field(
            [
                'type'  => 'submit',
                'name'  => 'wp-submit',
                'value' => __('Get New Password', 'wishlist-member'),
            ]
        ) .
        wlm_form_field(
            [
                'type'  => 'hidden',
                'name'  => 'action',
                'value' => 'lostpassword',
            ]
        ),
        add_query_arg('action', 'login', wp_login_url()),
        __('Log in', 'wishlist-member')
    );

    return sprintf(
        '<div class="wlm-ajax-login"><div class="wlm-ajax-login-form">%s</div>%s%s</div>',
        $form,
        $otl,
        $lost
    );
}

add_action('wp_ajax_nopriv_wishlistmember_ajax_login', __NAMESPACE__ . '\ajax_login');
/**
 * Ajax login handler.
 * Called by 'wp_ajax_nopriv_wishlistmember_ajax_login' hook.
 */
function ajax_login()
{
    $data = wlm_arrval(filter_input_array(INPUT_POST));
    if (! wp_verify_nonce(wlm_arrval($data, 'n'), 'wlm-ajax-login')) {
        wp_send_json_error([__('Invalid data.', 'wishlist-member')]);
    }
    $result = wp_authenticate(wlm_arrval($data, 'u'), wlm_arrval($data, 'p'));
    if (is_wp_error($result)) {
        // Let's Remove the <a> tags that contains the "Lost your password?" text on the error message.
        $error_message = preg_replace('~<a(.*?)</a>~Usi', '', $result->get_error_messages());
        wp_send_json_error($error_message);
    }
    wp_send_json_success();
}

add_action('wp_ajax_nopriv_wishlistmember_ajax_login_lostpassword', __NAMESPACE__ . '\ajax_lostpassword');
/**
 * Ajax lost password handler
 * Called by 'wp_ajax_nopriv_wishlistmember_ajax_login_lostpassword' hook.
 */
function ajax_lostpassword()
{
    $data = wlm_arrval(filter_input_array(INPUT_POST));
    if (! wp_verify_nonce(wlm_arrval($data, 'n'), 'wlm-ajax-login')) {
        wp_send_json_error([__('Invalid data.', 'wishlist-member')]);
    }
    $result = retrieve_password();
    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_messages());
    }
    wp_send_json_success([__('Check your email for the confirmation link', 'wishlist-member')]);
}

add_action('wp_ajax_nopriv_wishlistmember_ajax_login_otl', __NAMESPACE__ . '\ajax_otl');
/**
 * Ajax one-time login handler
 * Called by 'wp_ajax_nopriv_wishlistmember_ajax_login_otl' hook.
 */
function ajax_otl()
{
    $data = wlm_arrval(filter_input_array(INPUT_POST));
    if (! wp_verify_nonce(wlm_arrval($data, 'n'), 'wlm-ajax-login')) {
        wp_send_json_error([__('Invalid data.', 'wishlist-member')]);
    }
    if (! trim(wlm_arrval($data, 'user_login'))) {
        wp_send_json_error([__('The username field is empty.', 'wishlist-member')]);
    }
    doing_ajax_otl(true);
    do_action('login_form_wishlistmember-otl');
    doing_ajax_otl(false);
    wp_send_json_error([__('One-time login is not enabled.', 'wishlist-member')]);
}

/**
 * Sets and returns whether ajax one-time login is being performed.
 *
 * @param  boolean $status Optional status to set.
 * @return boolean
 */
function doing_ajax_otl($status = null)
{
    static $doing_ajax_otl = null;
    if (is_bool($status)) {
        $doing_ajax_otl = $status;
    }
    return $doing_ajax_otl;
}

add_action('wishlistmember_onetime_login_request_result', __NAMESPACE__ . '\ajax_otl_result', 10, 2);
/**
 * Sends one-time login request result via AJAX
 * Called by 'wishlistmember_onetime_login_request_result' hook.
 *
 * @param boolean $status  Status.
 * @param string  $message Status Message.
 */
function ajax_otl_result($status, $message)
{
    if (! doing_ajax_otl()) {
        return;
    }
    if ($status) {
        wp_send_json_success([$message]);
    } else {
        wp_send_json_error([$message]);
    }
}
