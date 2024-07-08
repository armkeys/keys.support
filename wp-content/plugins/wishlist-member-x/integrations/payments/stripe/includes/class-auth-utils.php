<?php

/**
 * Auth Helper Class
 *
 * @package WishListMember\PaymentProviders\Stripe
 */

namespace WishListMember\PaymentProviders\Stripe;

defined('ABSPATH') || die();

class Auth_Utils
{
    const OPTION_KEY_AUTH_ACCOUNT_EMAIL     = 'wlm_authenticator_account_email';
    const OPTION_KEY_AUTH_ACCOUNT_SECRET    = 'wlm_authenticator_secret_token';
    const OPTION_KEY_AUTH_ACCOUNT_SITE_UUID = 'wlm_authenticator_site_uuid';
    const OPTION_KEY_AUTH_ACCOUNT_USER_UUID = 'wlm_authenticator_user_uuid';

    public static function get_account_email()
    {
        return wishlistmember_instance()->get_option(self::OPTION_KEY_AUTH_ACCOUNT_EMAIL);
    }

    public static function get_account_secret()
    {
        return wishlistmember_instance()->get_option(self::OPTION_KEY_AUTH_ACCOUNT_SECRET);
    }

    public static function get_account_site_uuid()
    {
        return wishlistmember_instance()->get_option(self::OPTION_KEY_AUTH_ACCOUNT_SITE_UUID);
    }

    public static function get_account_user_uuid()
    {
        return wishlistmember_instance()->get_option(self::OPTION_KEY_AUTH_ACCOUNT_USER_UUID);
    }

    public static function set_account_email($value)
    {
        return wishlistmember_instance()->save_option(self::OPTION_KEY_AUTH_ACCOUNT_EMAIL, $value);
    }

    public static function set_account_secret($value)
    {
        return wishlistmember_instance()->save_option(self::OPTION_KEY_AUTH_ACCOUNT_SECRET, $value);
    }

    public static function set_account_site_uuid($value)
    {
        return wishlistmember_instance()->save_option(self::OPTION_KEY_AUTH_ACCOUNT_SITE_UUID, $value);
    }

    public static function set_account_user_uuid($value)
    {
        return wishlistmember_instance()->save_option(self::OPTION_KEY_AUTH_ACCOUNT_USER_UUID, $value);
    }

    public static function clear_connection_data()
    {
        wishlistmember_instance()->delete_option(self::OPTION_KEY_AUTH_ACCOUNT_SITE_UUID);
        wishlistmember_instance()->delete_option(self::OPTION_KEY_AUTH_ACCOUNT_EMAIL);
        wishlistmember_instance()->delete_option(self::OPTION_KEY_AUTH_ACCOUNT_SECRET);
    }

    /**
     * Returns an array to be used with wp_remote_request
     */
    public static function jwt_header($jwt, $domain)
    {
        return [
            'Authorization' => 'Bearer ' . $jwt,
            'Accept'        => 'application/json;ver=1.0',
            'Content-Type'  => 'application/json; charset=UTF-8',
            'Host'          => $domain,
        ];
    }

    /**
     * Generates a JWT, signed by the stored secret token
     *
     * @param array $payload Payload data
     * @param sring $secret  Used to sign the JWT
     *
     * @return string
     */
    public static function generate_jwt($payload, $secret = false)
    {

        if (false === $secret) {
            $secret = self::get_account_secret();
        }

        // Create token header.
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256',
        ];
        $header = json_encode($header);
        $header = self::base64url_encode($header);

        // Create token payload.
        $payload = json_encode($payload);
        $payload = self::base64url_encode($payload);

        // Create Signature Hash.
        $signature = hash_hmac('sha256', "{$header}.{$payload}", $secret);
        $signature = json_encode($signature);
        $signature = self::base64url_encode($signature);

        // Create JWT.
        $jwt = "{$header}.{$payload}.{$signature}";
        return $jwt;
    }

    /**
     * Ensure that the Base64 string is passed within URLs without any URL encoding
     *
     * @param string $value
     *
     * @return string
     */
    public static function base64url_encode($value)
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    public static function is_authorized()
    {
        // Make sure the user is authorized.
        if (current_user_can('remove_users')) {
            return true;
        }
        return false;
    }

    public static function get_stripe_modal_url()
    {
        return admin_url('admin.php') . '?page=WishListMember&wl=setup/integrations/payment_provider/stripe&wlm_strip_connect_modal=' . time();
    }
}
