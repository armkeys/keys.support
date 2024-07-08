<?php

/**
 * Helper class
 *
 * @package WishListMember\PaymentProviders\Stripe
 */

namespace WishListMember\PaymentProviders\Stripe;

defined('ABSPATH') || die();
class Gateway_Utils
{
    public static function pretty_permalinks_using_index()
    {
        $permalink_structure = get_option('permalink_structure');
        return preg_match('!^/index.php!', $permalink_structure);
    }

    /**
     * This returns the structure for all of the gateway notify urls.
     * It can even account for folks unlucky enough to have to prepend
     * their URLs with '/index.php'.
     * NOTE: This function is only applicable if pretty permalinks are enabled.
     */
    public static function gateway_notify_url_structure()
    {
        $pre_slug_index = '';
        if (self::pretty_permalinks_using_index()) {
            $pre_slug_index = '/index.php';
        }

        return apply_filters(
            'wlm_gateway_notify_url_structure',
            "{$pre_slug_index}/wishlistmember/notify/%gatewayid%/%action%"
        );
    }

    /**
     * This modifies the gateway notify url structure to be matched against a uri.
     * By default it will generate this: /mmouse/notify/([^/\?]+)/([^/\?]+)/?
     * However, this could change depending on what gateway_notify_url_structure returns
     */
    public static function gateway_notify_url_regex_pattern()
    {
        return preg_replace('!(%gatewayid%|%action%)!', '([^/\?]+)', self::gateway_notify_url_structure()) . '/?';
    }

    public static function match_uri($pattern, $uri, &$matches, $include_query_string = false)
    {
        if ($include_query_string) {
            $uri = urldecode($uri);
        } else {
            // Remove query string and decode.
            $uri = preg_replace('#(\?.*)?$#', '', urldecode($uri));
        }

        // Resolve WP installs in sub-directories.
        preg_match('!^https?://[^/]*?(/.*)$!', site_url(), $m);

        $subdir = ( isset($m[1]) ? $m[1] : '' );
        $regex  = '!^' . $subdir . $pattern . '$!';
        return preg_match($regex, $uri, $matches);
    }

    public static function debug_log($message)
    {
        // Getting some complaints about using WP_DEBUG here.
        if (defined('WP_WLM_DEBUG') && WP_WLM_DEBUG) {
            error_log(sprintf(__('*** WishList Member Debug: %s', 'wishlist-member'), $message));
        }
    }

    public static function http_status_codes()
    {
        return [
            100 => 'Continue',
            101 => 'Switching Protocols',
            102 => 'Processing',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            207 => 'Multi-Status',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => 'Switch Proxy',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            418 => 'I\'m a teapot',
            422 => 'Unprocessable Entity',
            423 => 'Locked',
            424 => 'Failed Dependency',
            425 => 'Unordered Collection',
            426 => 'Upgrade Required',
            449 => 'Retry With',
            450 => 'Blocked by Windows Parental Controls',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            506 => 'Variant Also Negotiates',
            507 => 'Insufficient Storage',
            509 => 'Bandwidth Limit Exceeded',
            510 => 'Not Extended',
        ];
    }

    public static function exit_with_status($status, $message = '')
    {
        $codes = self::http_status_codes();
        header("HTTP/1.1 {$status} {$codes[$status]}", true, $status);
        exit($message);
    }

    public static function detect_stripe_testmode()
    {
        $stripetestmode = wlm_trim(wishlistmember_instance()->get_option('stripetestmode'));
        if (empty($stripetestmode)) {
            // If test mode is set, let's check the stripe key and detect the mode.
            $stripeapikey   = wlm_trim(wishlistmember_instance()->get_option('stripeapikey'));
            $stripetestmode = false === strpos($stripeapikey, 'test') ? 'no' : 'yes';
        }

        return $stripetestmode;
    }

    public static function get_stripeapikey()
    {
        $stripetestmode = self::detect_stripe_testmode();
        $live_key       = wlm_trim(wishlistmember_instance()->get_option('stripeapikey'));
        $test_key       = wlm_trim(wishlistmember_instance()->get_option('test_stripeapikey'));
        return 'yes' === $stripetestmode ? $test_key : $live_key;
    }

    public static function get_publishablekey()
    {
        $stripetestmode = self::detect_stripe_testmode();
        $live_key       = wlm_trim(wishlistmember_instance()->get_option('stripepublishablekey'));
        $test_key       = wlm_trim(wishlistmember_instance()->get_option('test_stripepublishablekey'));
        return 'yes' === $stripetestmode ? $test_key : $live_key;
    }
}
