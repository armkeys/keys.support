<?php

/**
 * Stripe Connect
 *
 * @package WishListMember\PaymentProviders\Stripe
 */

namespace WishListMember\PaymentProviders\Stripe;

defined('ABSPATH') || die();

class Connect
{
    private static $notifiers = [
        'whk'                 => 'listener',
        'stripe-service-whk'  => 'service_listener',
        'update-billing.html' => 'churn_buster',
    ];

    public static function get_method_id()
    {
        return wishlistmember_instance()->get_option('stripethankyou');
    }

    /**
     * Assembles the URL for redirecting to Stripe Connect
     *
     * @param  string  $id         Payment ID
     * @param  boolean $onboarding True if we are onboarding
     * @return string
     */
    public static function get_stripe_connect_url($method_id = '')
    {

        if ('' === $method_id) {
            $method_id = self::get_method_id();
        }

        $args = [
            'action'   => 'wlm_stripe_connect_update_creds',
            '_wpnonce' => wp_create_nonce('stripe-update-creds'),
        ];

        $base_return_url = add_query_arg($args, admin_url('admin-ajax.php'));

        $error_url = add_query_arg(
            [
                'wlm-action' => 'error',
            ],
            $base_return_url
        );

        $site_uuid = Auth_Utils::get_account_site_uuid();

        if (empty($site_uuid)) {
            return false;
        }

        $payload = [
            'method_id'           => $method_id,
            'site_uuid'           => $site_uuid,
            'user_uuid'           => Auth_Utils::get_account_user_uuid(),
            'return_url'          => $base_return_url,
            'error_url'           => $error_url,
            'webhook_url'         => self::notify_url($method_id, 'whk'),
            'service_webhook_url' => self::notify_url($method_id, 'stripe-service-whk'),
            'mp_version'          => WLM_PLUGIN_VERSION,
        ];

        $jwt = Auth_Utils::generate_jwt($payload);
        return WLM_STRIPE_SERVICE_URL . "/connect/{$site_uuid}/{$method_id}/{$jwt}";
    }

    /**
     * Returns the url of a given notifier for the current gateway
     */
    public static function notify_url($method_id, $action, $force_ssl = false)
    {
        if (isset(self::$notifiers[ $action ])) {
            $permalink_structure = get_option('permalink_structure');
            $force_ugly_urls     = get_option('wlm_force_ugly_gateway_notify_urls');

            if ($force_ugly_urls || empty($permalink_structure)) {
                $url = WLM_SCRIPT_URL . "&pmt={self::get_method_id()}&action={$action}";
            } else {
                $notify_url = preg_replace('!%gatewayid%!', self::get_method_id(), Gateway_Utils::gateway_notify_url_structure());
                $notify_url = preg_replace('!%action%!', $action, $notify_url);

                $url = site_url($notify_url);
            }

            if ($force_ssl) {
                $url = preg_replace('/^http:/', 'https:', $url);
            }

            $slug = self::get_method_id();
            $url  = apply_filters('wlm_gateway_notify_url', $url, $slug, $action);
            return apply_filters("wlm_gateway_{$slug}_{$action}_notify_url", $url);
        }

        return false;
    }

    /**
     * Process a request to retrieve credentials after a connection
     *
     * @return void
     */
    public static function process_update_creds()
    {

        // Security check.
        if (! isset($_GET['_wpnonce']) || ! wp_verify_nonce($_GET['_wpnonce'], 'stripe-update-creds')) {
            wp_die(__('Sorry, updating your credentials failed. (security)', 'wishlist-member'));
        }

        // Check for the existence of any errors passed back from the service.
        if (isset($_GET['error'])) {
            if ('access_denied' === (string) $_GET['error']) {
                $redirect_url = add_query_arg(
                    [
                        'wlm-action-message' => esc_html($_GET['error']),
                    ],
                    Auth_Utils::get_stripe_modal_url()
                );
                wp_safe_redirect($redirect_url);
                exit;
            } else {
                wp_die(sanitize_text_field(urldecode($_GET['error'])));
            }
        }

        // Make sure we have a method ID.
        if (! isset($_GET['pmt'])) {
            wp_die(__('Sorry, updating your credentials failed. (pmt)', 'wishlist-member'));
        }

        // Make sure the user is authorized.
        if (! Auth_Utils::is_authorized()) {
            wp_die(__('Sorry, you don\'t have permission to do this.', 'wishlist-member'));
        }

        self::update_connect_credentials();

        $stripe_action = ( ! empty($_GET['stripe-action']) ? sanitize_text_field($_GET['stripe-action']) : 'updated' );

        $redirect_url = add_query_arg(
            [
                'stripe-action' => $stripe_action,
            ],
            Auth_Utils::get_stripe_modal_url()
        );

        wp_redirect($redirect_url);
        exit;
    }

    /**
     * Fetches the credentials from Stripe-Connect and updates them in the payment method.
     */
    private static function update_connect_credentials()
    {

        $site_uuid = Auth_Utils::get_account_site_uuid();

        $payload = [
            'site_uuid' => $site_uuid,
        ];

        $method_id = self::get_method_id();
        $jwt       = Auth_Utils::generate_jwt($payload);

        // Make sure the request came from the Connect service.
        $response = wp_remote_get(
            WLM_STRIPE_SERVICE_URL . '/api/credentials/' . $method_id,
            [
                'headers' => Auth_Utils::jwt_header($jwt, WLM_STRIPE_SERVICE_DOMAIN),
            ]
        );

        $creds = json_decode(wp_remote_retrieve_body($response), true);

        wishlistmember_instance()->save_option('stripeapikey', $creds['live_secret_key']);
        wishlistmember_instance()->save_option('stripepublishablekey', $creds['live_publishable_key']);
        wishlistmember_instance()->save_option('test_stripepublishablekey', $creds['test_publishable_key']);
        wishlistmember_instance()->save_option('test_stripeapikey', $creds['test_secret_key']);

        wishlistmember_instance()->save_option('wlm_stripe_connect_status', 'connected');
        wishlistmember_instance()->save_option('wlm_stripe_service_account_id', sanitize_text_field($creds['service_account_id']));
        wishlistmember_instance()->save_option('wlm_stripe_service_account_name', sanitize_text_field($creds['service_account_name']));
    }

    public static function connect_status()
    {
        return wishlistmember_instance()->get_option('wlm_stripe_connect_status', false, false, 'not-connected');
    }

    /**
     * Process a request to refresh tokens
     *
     * @return void
     */
    public static function process_refresh_tokens()
    {

        // Security check.
        if (! isset($_GET['_wpnonce']) || ! wp_verify_nonce($_GET['_wpnonce'], 'stripe-refresh')) {
            wp_die(__('Sorry, the refresh failed.', 'wishlist-member'));
        }

        // Make sure we have a method ID.
        if (! isset($_GET['method-id'])) {
            wp_die(__('Sorry, the refresh failed.', 'wishlist-member'));
        }

        // Make sure the user is authorized.
        if (! Auth_Utils::is_authorized()) {
            wp_die(__('Sorry, you don\'t have permission to do this.', 'wishlist-member'));
        }

        $method_id = self::get_method_id();
        $site_uuid = Auth_Utils::get_account_site_uuid();

        $payload = [
            'site_uuid' => $site_uuid,
        ];

        $jwt = Auth_Utils::generate_jwt($payload);

        // Send request to Connect service.
        $response = wp_remote_post(
            WLM_STRIPE_SERVICE_URL . "/api/refresh/{$method_id}",
            [
                'headers' => Auth_Utils::jwt_header($jwt, WLM_STRIPE_SERVICE_DOMAIN),
            ]
        );

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (! isset($body['connect_status']) || 'refreshed' !== $body['connect_status']) {
            wp_die(__('Sorry, the refresh failed.', 'wishlist-member'));
        }

        wishlistmember_instance()->save_option('stripeapikey', $body['live_secret_key']);
        wishlistmember_instance()->save_option('stripepublishablekey', $body['live_publishable_key']);
        wishlistmember_instance()->save_option('test_stripepublishablekey', $body['test_publishable_key']);
        wishlistmember_instance()->save_option('test_stripeapikey', $body['test_secret_key']);

        wishlistmember_instance()->save_option('wlm_stripe_connect_status', 'connected');
        wishlistmember_instance()->save_option('wlm_stripe_service_account_id', sanitize_text_field($body['service_account_id']));
        wishlistmember_instance()->save_option('wlm_stripe_service_account_name', sanitize_text_field($body['service_account_name']));

        $redirect_url = add_query_arg(
            [
                'stripe-action' => 'refreshed',
            ],
            Auth_Utils::get_stripe_modal_url()
        );

        wp_redirect($redirect_url);
        exit;
    }

    /**
     * Process a request to disconnect
     *
     * @return void
     */
    public static function process_disconnect()
    {

        // Security check.
        if (! isset($_GET['_wpnonce']) || ! wp_verify_nonce($_GET['_wpnonce'], 'stripe-disconnect')) {
            wp_die(__('Sorry, the disconnect failed.', 'wishlist-member'));
        }

        // Make sure the user is authorized.
        if (! Auth_Utils::is_authorized()) {
            wp_die(__('Sorry, you don\'t have permission to do this.', 'wishlist-member'));
        }

        $res = self::disconnect();

        if (! $res) {
            wp_die(__('Sorry, the disconnect failed.', 'wishlist-member'));
        }

        wishlistmember_instance()->save_option('wlm_stripe_connect_status', 'disconnected');

        // Once disconnected, empty the keys.
        wishlistmember_instance()->save_option('stripeapikey', '');
        wishlistmember_instance()->save_option('stripepublishablekey', '');
        wishlistmember_instance()->save_option('test_stripepublishablekey', '');
        wishlistmember_instance()->save_option('test_stripeapikey', '');

        $redirect_url = add_query_arg(
            [
                'stripe-action' => 'disconnected',
            ],
            Auth_Utils::get_stripe_modal_url()
        );

        wp_redirect($redirect_url);
        exit;
    }

    private static function disconnect()
    {

        $site_uuid = Auth_Utils::get_account_site_uuid();
        $method_id = self::get_method_id();

        // Attempt to disconnect at the service.
        $payload = [
            'method_id' => $method_id,
            'site_uuid' => $site_uuid,
        ];

        $jwt = Auth_Utils::generate_jwt($payload);

        // Send request to Connect service.
        $response = wp_remote_request(
            WLM_STRIPE_SERVICE_URL . "/api/disconnect/{$method_id}",
            [
                'method'  => 'DELETE',
                'headers' => Auth_Utils::jwt_header($jwt, WLM_STRIPE_SERVICE_DOMAIN),
            ]
        );

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (! isset($body['connect_status']) || 'disconnected' !== $body['connect_status']) {
            return false;
        }

        return true;
    }

    public static function is_stripe_active()
    {
        return wishlistmember_instance()->payment_integration_is_active('stripe');
    }

    protected static function has_method_with_connect_status($target_status)
    {

        if (false === wishlistmember_instance()->get_option('wizard_ran')) {
            return false;
        }

        if (! self::is_stripe_active()) {
            return false; // Bailout.
        }

        $status = self::connect_status();
        if ($target_status === $status) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Display an admin notice for upgrading Stripe payment methods to Stripe Connect
     *
     * @return void
     */
    public static function upgrade_notice()
    {
        if (
            self::has_method_with_connect_status('not-connected') &&
            ! empty(wlm_trim(wishlistmember_instance()->get_option('stripeapikey')))
        ) {
            ?>
            <style>
            .wlm-warning-notice-icon {
              color: #dc3232 !important;
              font-size: 32px !important;
              vertical-align: top !important;
            }

            .wlm-warning-notice-title {
              vertical-align: top !important;
              margin-left: 18px !important;
              font-size: 18px !important;
              font-weight: bold !important;
              line-height: 32px !important;
            }
          </style>
            <div class="mx-0 my-3 notice notice-error wlm-notice" id="wlm_stripe_connect_upgrade_notice">
              <p>
                <p><span class="dashicons dashicons-warning wlm-warning-notice-icon"></span><strong class="wlm-warning-notice-title"><?php _e('WishList Member Security Notice', 'wishlist-member'); ?></strong></p>
                <p><strong><?php esc_html_e('You are using an older legacy Stripe integration connection that may become insecure. Click the button below to use the current and recommended method to re-connect your Stripe integration now.', 'wishlist-member'); ?></strong></p>
                <p><a href="<?php echo Auth_Utils::get_stripe_modal_url(); ?>" class="button button-primary"><?php _e('Re-connect Stripe Payments to Fix this Error Now', 'wishlist-member'); ?></a></p>
              </p>
              <?php wp_nonce_field('wlm_stripe_connect_upgrade_notice_dismiss', 'wlm_stripe_connect_upgrade_notice_dismiss'); ?>
            </div>
            <?php
        }
    }

    public static function add_site_health_test($tests)
    {
        $tests['direct']['wlm_stripe_connecttest'] = [
            'label' => __('WishList Member - Stripe Connect Security', 'wishlist-member'),
            'test'  => '\WishListMember\PaymentProviders\Stripe\Connect::run_site_health_test',
        ];

        return $tests;
    }

    /**
     * Run a site health check and return the result
     *
     * @return array
     */
    public static function run_site_health_test()
    {

        $result = [
            'label'       => __('WishList Member is securely connected to Stripe', 'wishlist-member'),
            'status'      => 'good',
            'badge'       => [
                'label' => __('Security', 'wishlist-member'),
                'color' => 'blue',
            ],
            'description' => sprintf(
                '<p>%s</p>',
                __('Your WishList Member Stripe connection is complete and secure.', 'wishlist-member')
            ),
            'actions'     => '',
            'test'        => 'run_site_health_test',
        ];

        if (self::has_method_with_connect_status('not-connected')) {
            $result = [
                'label'       => __('WishList Member is not securely connected to Stripe', 'wishlist-member'),
                'status'      => 'critical',
                'badge'       => [
                    'label' => __('Security', 'wishlist-member'),
                    'color' => 'red',
                ],
                'description' => sprintf(
                    '<p>%s</p>',
                    __('You are using an older legacy Stripe integration connection that may become insecure. Click the button below to use the current and recommended method to re-connect your Stripe integration now.', 'wishlist-member')
                ),
                'actions'     => '<a href="' . Auth_Utils::get_stripe_modal_url() . '" class="button button-primary">' . __('Re-connect Stripe Payments to Fix this Error Now', 'wishlist-member') . '</a>',
                'test'        => 'wlm_run_site_health_test',
            ];
        }

        return $result;
    }

    public static function parse_standalone_request()
    {
        global $user_ID;

        $request_uri = $_SERVER['REQUEST_URI'];

        // Pretty WLM Notifier ... prevents POST vars from being mangled.
        $notify_url_pattern = Gateway_Utils::gateway_notify_url_regex_pattern();
        if (Gateway_Utils::match_uri($notify_url_pattern, $request_uri, $m)) {
            $plugin          = 'wlm';
            $_REQUEST['pmt'] = $m[1];
            $action          = $m[2];
        }

        try {
            if (
                ! empty($plugin)
                && $plugin == 'wlm'
                && isset($_REQUEST['pmt'])
                && ! empty($_REQUEST['pmt'])
                && self::get_method_id() == $_REQUEST['pmt']
                && ! empty($action)
            ) {
                $notifiers = self::$notifiers;
                if (isset($notifiers[ $action ])) {
                    call_user_func([__CLASS__, $notifiers[ $action ]]);
                    exit;
                }
            }
        } catch (Exception $e) {
            ?>
          <div class="wlm_error"><?php printf(__('There was a problem with our system: %s. Please come back soon and try again.', 'wishlist-member'), $e->getMessage()); ?></div>
            <?php
            exit;
        }
    }

    /**
     * Process an incoming webhook from the Stripe Connect service
     *
     * @return void
     */
    public static function service_listener()
    {

        // Retrieve the request's body and parse it as JSON.
        $body = @file_get_contents('php://input');

        Gateway_Utils::debug_log('********* WEBHOOK CONTENTS: ' . $body);
        $header_signature = Gateway_Utils::get_http_header('Signature');

        if (empty($header_signature)) {
            Gateway_Utils::debug_log('*** Exiting with no signature');
            Gateway_Utils::exit_with_status(403, __('No Webhook Signature', 'wishlist-member'));
        }

        $secret    = Auth_Utils::get_account_secret();
        $signature = hash_hmac('sha256', $body, $secret);

        Gateway_Utils::debug_log('********* WEBHOOK SECRETS -- SERVICE: [' . $header_signature . '] LOCAL: [' . $signature . ']');

        if ($header_signature != $signature) {
            Gateway_Utils::debug_log('*** Exiting with incorrect signature');
            Gateway_Utils::exit_with_status(403, __('Incorrect Webhook Signature', 'wishlist-member'));
        }

        $body = json_decode($body, true);

        if (! isset($body['event']) || empty($body['event'])) {
            Gateway_Utils::exit_with_status(403, __('No `event` set', 'wishlist-member'));
        }

        $event = sanitize_text_field($body['event']);

        $auth_site_uuid = Auth_Utils::get_account_site_uuid();

        if ($event == 'update-credentials') {
            $site_uuid = sanitize_text_field($body['data']['site_uuid']);
            if ($auth_site_uuid != $site_uuid) {
                Gateway_Utils::exit_with_status(404, __('Request was sent to the wrong site?', 'wishlist-member'));
            }

            self::update_connect_credentials();

            wp_send_json(['credentials' => 'saved']);
        }

        Gateway_Utils::exit_with_status(404, __('Webhook not supported', 'wishlist-member'));
    }

    public static function listener()
    {
        Stripe_Integration::instance()->sync(wlm_post_data(true));
    }

    public static function is_rest_api_request()
    {

        if (empty($_SERVER['REQUEST_URI'])) {
            return false;
        }

        $rest_prefix         = trailingslashit(rest_get_url_prefix());
        $is_rest_api_request = ( false !== strpos($_SERVER['REQUEST_URI'], $rest_prefix) ); // phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized.

        return $is_rest_api_request;
    }

    public static function maybe_swap_stripe_keys()
    {

        if (defined('DOING_AJAX') || defined('DOING_CRON') || self::is_rest_api_request()) {
            return;
        }

        $stripetestmode                   = wlm_trim(wishlistmember_instance()->get_option('stripetestmode'));
        $stripeconnect_migration_complete = wlm_trim(wishlistmember_instance()->get_option('stripeconnect_migration_complete'));
        if (empty($stripetestmode) && empty($stripeconnect_migration_complete)) {
            $detected_test_mode   = Gateway_Utils::detect_stripe_testmode();
            $stripeapikey         = wlm_trim(wishlistmember_instance()->get_option('stripeapikey'));
            $stripepublishablekey = wlm_trim(wishlistmember_instance()->get_option('stripepublishablekey'));

            if ('yes' === $detected_test_mode) { // Test mode.
                wishlistmember_instance()->save_option('stripeapikey', '');
                wishlistmember_instance()->save_option('stripepublishablekey', '');
                wishlistmember_instance()->save_option('test_stripeapikey', $stripeapikey);
                wishlistmember_instance()->save_option('test_stripepublishablekey', $stripepublishablekey);
            } else { // Live mode.
                wishlistmember_instance()->save_option('stripeapikey', $stripeapikey);
                wishlistmember_instance()->save_option('stripepublishablekey', $stripepublishablekey);
                wishlistmember_instance()->save_option('test_stripeapikey', '');
                wishlistmember_instance()->save_option('test_stripepublishablekey', '');
            }

            wishlistmember_instance()->save_option('stripetestmode', $detected_test_mode);
            wishlistmember_instance()->save_option('stripeconnect_migration_complete', time());
        }

        return $stripetestmode;
    }

    public static function stripe_connect_save_settings()
    {
        check_ajax_referer('stripe-connect-save-testmode', 'security');

        $form_data = urldecode(wlm_post_data()['form_data']);

        $data = [];
        parse_str($form_data, $data);

        if (empty($data)) {
            echo wp_json_encode(
                [
                    'status'  => 'error',
                    'message' => __(
                        'Invalid request.',
                        'wishlist-member'
                    ),
                ]
            );
            exit;
        }

        if (! isset($data['stripetestmode'])) {
            $data['stripetestmode'] = 'no';
        }

        wishlistmember_instance()->save_option('stripetestmode', $data['stripetestmode']);
        echo wp_json_encode(
            [
                'status'  => 'success',
                'message' => __(
                    'Done.',
                    'wishlist-member'
                ),
            ]
        );
        exit;
    }
}
