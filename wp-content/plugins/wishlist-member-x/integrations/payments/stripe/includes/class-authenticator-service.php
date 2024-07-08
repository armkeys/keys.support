<?php

/**
 * Authenticator class
 *
 * @package WishListMember\PaymentProviders\Stripe
 */

namespace WishListMember\PaymentProviders\Stripe;

defined('ABSPATH') || die();

class Authenticator
{
    public static function clear_connection_data()
    {
        if (isset($_GET['wlm-clear-connection-data'])) {
            // Admins only.
            if (current_user_can('manage_options')) {
                Auth_Utils::clear_connection_data();
            }
        }
    }

    /**
     * Assembles a URL for connecting to our Authentication service
     *
     * @param boolean     $Connect           Will add a query string that is used to redirect to Stripe Connect after returning from Auth service
     * @param array       $additional_params
     * @param string|null $return_url
     *
     * @return string
     */
    public static function get_auth_connect_url($connect = false, $payment_method_id = false, $additional_params = [], $return_url = null)
    {
        $return_url = is_null($return_url) ? admin_url('admin.php') . '?page=WishListMember&wl=account_login' : $return_url;

        $connect_params = [
            'return_url' => urlencode(add_query_arg('wlm-connect', 'true', $return_url)),
            'nonce'      => wp_create_nonce('wlm-connect'),
        ];

        $site_uuid = Auth_Utils::get_account_site_uuid();

        if ($site_uuid) {
            $connect_params['site_uuid'] = $site_uuid;
        }

        if (true === $connect && ! empty($payment_method_id)) {
            $connect_params['stripe_connect'] = 'true';
            $connect_params['method_id']      = $payment_method_id;
        }

        if (! empty($additional_params)) {
            $connect_params = array_merge($connect_params, $additional_params);
        }

        return add_query_arg($connect_params, WLM_AUTH_SERVICE_URL . '/connect/wishlistmember');
    }

    /**
     * Process a Connect
     *
     * @return void
     */
    public static function process_connect()
    {

        // Make sure we've entered our Authenticator process.
        if (! isset($_GET['wlm-connect']) || 'true' !== wlm_get_data()['wlm-connect']) {
            return;
        }

        // Validate the nonce on the WP side of things.
        if (! isset($_GET['nonce']) || ! wp_verify_nonce(wlm_get_data()['nonce'], 'wlm-connect')) {
            return;
        }

        // Make sure the user is authorized.
        if (! Auth_Utils::is_authorized()) {
            return;
        }

        $site_uuid = isset($_GET['site_uuid']) ? sanitize_text_field(wlm_get_data()['site_uuid']) : '';
        $auth_code = isset($_GET['auth_code']) ? sanitize_text_field(wlm_get_data()['auth_code']) : '';

        // GET request to obtain token.
        $response = wp_remote_get(
            WLM_AUTH_SERVICE_URL . "/api/tokens/{$site_uuid}",
            [
                'sslverify' => false,
                'headers'   => [
                    'accept' => 'application/json',
                ],
                'body'      => [
                    'auth_code' => $auth_code,
                ],
            ]
        );

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['account_email']) && ! empty($body['account_email'])) {
            $email_saved = Auth_Utils::set_account_email(sanitize_text_field($body['account_email']));
        }

        if (isset($body['secret_token']) && ! empty($body['secret_token'])) {
            $token_saved = Auth_Utils::set_account_secret(sanitize_text_field($body['secret_token']));
        }

        if (isset($body['user_uuid']) && ! empty($body['user_uuid'])) {
            $user_uuid_saved = Auth_Utils::set_account_user_uuid(sanitize_text_field($body['user_uuid']));
        }

        if ($site_uuid) {
            Auth_Utils::set_account_site_uuid($site_uuid);
        }

        if (isset($_GET['stripe_connect']) && 'true' === wlm_get_data()['stripe_connect'] && isset($_GET['method_id']) && ! empty($_GET['method_id'])) {
            wp_redirect(Connect::get_stripe_connect_url(wlm_get_data()['method_id']));
            exit;
        }

        $redirect_url = remove_query_arg(['wlm-connect', 'nonce', 'site_uuid', 'user_uuid', 'auth_code', 'license_key']);

        wp_redirect($redirect_url);
        exit;
    }


    /**
     * Process a Disconnect
     *
     * @return void
     */
    public static function process_disconnect()
    {

        // Make sure we've entered our Authenticator process.
        if (! isset($_GET['wlm-disconnect']) || 'true' !== wlm_get_data()['wlm-disconnect']) {
            return;
        }

        // Validate the nonce on the WP side of things.
        if (! isset($_GET['nonce']) || ! wp_verify_nonce(wlm_get_data()['nonce'], 'wlm-disconnect')) {
            return;
        }

        // Make sure the user is authorized.
        if (! Auth_Utils::is_authorized()) {
            return;
        }

        $site_email = Auth_Utils::get_account_email();
        $site_uuid  = Auth_Utils::get_account_site_uuid();

        do_action('wlm_auth_service_pre_disconnect', $site_uuid, $site_email);

        // Create token payload.
        $payload = [
            'email'     => $site_email,
            'site_uuid' => $site_uuid,
        ];

        // Create JWT.
        $jwt = Auth_Utils::generate_jwt($payload);

        // DELETE request to obtain token.
        $response = wp_remote_request(
            WLM_AUTH_SERVICE_URL . '/api/disconnect/wishlistmember',
            [
                'method'    => 'DELETE',
                'sslverify' => false,
                'headers'   => Auth_Utils::jwt_header($jwt, WLM_AUTH_SERVICE_DOMAIN),
            ]
        );

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['disconnected']) && true === $body['disconnected']) {
            Auth_Utils::clear_connection_data();
        }

        wp_redirect(remove_query_arg(['wlm-disconnect', 'nonce']));
        exit;
    }

    public static function wlm_stripe_disconnect_notice()
    {
        $account_email = Auth_Utils::get_account_email();
        $secret        = Auth_Utils::get_account_secret();
        $site_uuid     = Auth_Utils::get_account_site_uuid();

        $is_stripe_active  = Connect::is_stripe_active();
        $connection_status = Connect::connect_status();

        if (true === $is_stripe_active && ! $account_email && ! $secret && ! $site_uuid && 'connected' === $connection_status) {
            ?>
          <div class="notice notice-error is-dismissible">
            <p><?php esc_html_e('Your WishListMember.com account and Stripe gateway have been disconnected. Please re-connect the Stripe gateway by clicking the button below in order to start taking payments again.', 'wishlist-member'); ?></p>
            <p><a href="<?php echo Auth_Utils::get_stripe_modal_url(); ?>" class="button button-primary"><?php esc_html_e('Re-connect Stripe', 'wishlist-member'); ?></a></p>
          </div>

            <?php
        }
    }
}
