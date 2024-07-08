<?php

/**
 * API Auth digest
 *
 * @package WishListMember
 */

namespace WishListMember;

/**
 * HTTP Digest Authentication for WLM API
 *
 * @since 3.7
 */
class API_Auth_Digest
{
    /**
     * Authentication status.
     *
     * @var boolean
     */
    private $status = true;
    /**
     * HTTP auth realm.
     *
     * @var string
     */
    private $realm = '';
    /**
     * Nonce transient name prefix.
     *
     * @var string
     */
    private $nonce_transient_prefix = 'wlm_api_auth_digest_nonce_';
    /**
     * Nonce time to live.
     *
     * @var integer
     */
    private $nonce_ttl = 30;

    /**
     * Constructor
     * - Generates the $this->realm
     * - Checks valid HTTP digest auth data
     * - sets $this->status
     */
    public function __construct()
    {
        // Generate realm name.
        $this->realm = 'WishList Member @ ' . preg_replace('/^.+?:\/\//', '', get_bloginfo('url'));

        // Grab all valid api keys.
        $users = ['wishlist' => wishlistmember_instance()->GetAPIKey()] + ( new APIKey() )->get_all_keys();

        // Request for digest auth if there is none.
        if (empty(wlm_server_data()['PHP_AUTH_DIGEST'])) {
            $this->unauthorized();
            return;
        }

        // Check for valid username.
        $data = $this->http_digest_parse(wlm_server_data()['PHP_AUTH_DIGEST']);
        if (! ( $data ) || ! isset($users[ $data['username'] ])) {
            $this->unauthorized();
            return;
        }

        // Generate the valid response.
        $a1    = md5(wlm_arrval($data, 'username') . ':' . $this->realm . ':' . $users[ wlm_arrval($data, 'username') ]);
        $a2    = md5(wlm_server_data()['REQUEST_METHOD'] . ':' . $data['uri']);
        $valid = md5($a1 . ':' . wlm_arrval($data, 'nonce') . ':' . wlm_arrval($data, 'nc') . ':' . wlm_arrval($data, 'cnonce') . ':' . wlm_arrval($data, 'qop') . ':' . $a2);
        // Response do not match.
        if ($data['response'] !== $valid) {
            $this->unauthorized();
            return;
        }

        // Verify nonce validity.
        if (! $this->verify_nonce($data['nonce'], $data['nc'])) {
            $this->unauthorized(true);
            return;
        }
    }

    /**
     * Return the value of $this->status
     *
     * @return boolean
     */
    public function status()
    {
        return (bool) $this->status;
    }

    /**
     * Saves nonce to database
     *
     * @param string $name  Nonce name.
     * @param array  $value Nonce value.
     */
    private function set_nonce($name, $value = [])
    {
        return set_transient($this->nonce_transient_prefix . $name, $value, $this->nonce_ttl) ? $name : false;
    }

    /**
     * Verifies the nonce by ensuring that it exists and that the counter has not yet been used
     *
     * @param  string  $name nonce name.
     * @param  integer $nc   nonce counter.
     * @return boolean
     */
    private function verify_nonce($name, $nc)
    {
        $nc         = (int) $nc;
        $nonce_data = get_transient($this->nonce_transient_prefix . $name);
        if (false === $nonce_data) {
            return false;
        }
        $nonce_data = (array) $nonce_data;
        if (in_array($nc, $nonce_data, true)) {
            return false;
        }
        $nonce_data[] = $nc;
        return $this->set_nonce($name, $nonce_data) ? true : false;
    }

    /**
     * Sends 401 and WWW-Authenticate headers
     * and sets status to false
     *
     * @param boolean $stale True if stale. Default false.
     */
    private function unauthorized($stale = false)
    {
        $nonce = $this->set_nonce(uniqid());
        if ($nonce) {
            header('HTTP/1.1 401 Unauthorized');
            $stale = $stale ? 'stale="true" ' : '';
            header('WWW-Authenticate: Digest ' . $stale . 'realm="' . $this->realm . '",qop="auth",nonce="' . esc_attr($nonce) . '",opaque="' . md5($this->realm) . '"');
        }
        $this->status = false;
    }

    /**
     * Parses the digest
     * (sourced from https://www.php.net/manual/en/features.http-auth.php)
     *
     * @param  string $digest Digest data.
     * @return array|false
     */
    private function http_digest_parse($digest)
    {
        // Protect against missing data.
        $needed_parts = [
            'nonce'    => 1,
            'nc'       => 1,
            'cnonce'   => 1,
            'qop'      => 1,
            'username' => 1,
            'uri'      => 1,
            'response' => 1,
        ];
        $data         = [];
        $keys         = implode('|', array_keys($needed_parts));

        preg_match_all('@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', stripslashes($digest), $matches, PREG_SET_ORDER);

        foreach ($matches as $m) {
            $data[ $m[1] ] = $m[3] ? $m[3] : $m[4];
            unset($needed_parts[ $m[1] ]);
        }

        return $needed_parts ? false : $data;
    }
}
