<?php

/**
 * Helper class for WishList Member API 2.0
 *
 * @version 1.8.0
 *
 * @package WishListMember/API
 */

if (! class_exists('WLM_API_Class')) {
    /**
     * The WLM_API_Class.
     */
    class WLM_API_Class
    {
        /**
         * WordPress URL.
         *
         * @var string
         */
        public $url;

        /**
         * WishList Member API Key.
         *
         * @var string
         */
        public $key;

        /**
         * Return format. Can be any of xml, json or php
         *
         * @var string
         */
        public $return_format = 'xml';

        /**
         * Authentication status. 1 or 0.
         *
         * @var integer
         */
        public $authenticated = 0;

        /**
         * Authentication error
         *
         * @var string
         */
        public $auth_error = '';

        /**
         * Make a fake PUT/DELETE request. The same as $method_emulation
         *
         * @var integer
         */
        public $fake = 0;

        /**
         * User method emulation. 1 or 0.
         *
         * @var integer
         */
        public $method_emulation = 0;

        /**
         * Use Digest authentication. Default faulse.
         *
         * @var integer
         */
        public $digest_auth = false;

        /**
         * Path to cookie file.
         *
         * @var string
         */
        public $cookie_file;

        /**
         * Initailize wlmapi
         *
         * @param string  $url         WordPress URL.
         * @param string  $key         API Key.
         * @param string  $tempdir     Temporary directory.
         * @param boolean $digest_auth Use Digest Authentication.
         */
        public function __construct($url, $key, $tempdir = null, $digest_auth = false)
        {
            if ('/' !== substr($url, -1)) {
                $url .= '/';
            }
            if (is_null($tempdir)) {
                if (function_exists('sys_get_temp_dir')) {
                    $tempdir = sys_get_temp_dir();
                }
                if (! $tempdir) {
                    $tempdir = '/tmp';
                }
            }
            $this->tempdir = $tempdir;
            $this->url     = $url . '?/wlmapi/2.0/';
            $this->key     = $key;

            if (empty($this->cookie_file)) {
                $this->cookie_file = tempnam($this->tempdir, 'wlmapi');
            }

            $this->digest_auth = (bool) $digest_auth;
        }

        /**
         * Destructor
         */
        public function __destruct()
        {
            if (file_exists($this->cookie_file)) {
                unlink($this->cookie_file);
            }
        }

        /**
         * Make API request
         *
         * @param  string $method   Request method. Can be PUT, DELETE, POST or GET.
         * @param  string $resource API resource being requested.
         * @param  array  $data     Data to pass to resource.
         * @return string API result
         */
        private function request($method, $resource, $data = [])
        {
            static $ch = null;
            if (defined('WLMAPICLASS_PASS_NOCACHE_DATA') && WLMAPICLASS_PASS_NOCACHE_DATA) {
                usleep(1);
                if (! is_array($data)) {
                    $data = [];
                }
                $data['__nocache__'] = md5(microtime());
            }

            $data = empty($data) ? '' : http_build_query($data);
            $url  = $this->url . $this->return_format . $resource;
            if (empty($ch)) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                if ($this->digest_auth) {
                    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
                    curl_setopt($ch, CURLOPT_USERPWD, 'wishlist:' . $this->key);
                } else {
                    curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file);
                    curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file);
                }
            }

            switch ($method) {
                case 'PUT':
                case 'DELETE':
                    if (! empty($this->fake) || ! empty($this->method_emulation) || $this->digest_auth) {
                        $fake  = rawurlencode('____FAKE____') . '=' . $method;
                        $fake2 = rawurlencode('____METHOD_EMULATION____') . '=' . $method;
                        if (empty($data)) {
                            $data = $fake . '&' . $fake2;
                        } else {
                            $data .= '&' . $fake . '&' . $fake2;
                        }
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                    } else {
                        curl_setopt($ch, CURLOPT_POST, 0);
                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Length: ' . strlen($data)]);
                    }
                    break;
                case 'POST':
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                    break;
                case 'GET':
                    curl_setopt($ch, CURLOPT_POST, 0);
                    if ($data) {
                        $url .= '/&' . $data;
                    }
                    break;
                default:
                    fwrite(fopen(( 'php:' ) . '//output', 'w'), 'Invalid Method: ' . $method);
                    die;
            }
            // Set the curl URL.
            curl_setopt($ch, CURLOPT_URL, $url);

            // Set user agent.
            curl_setopt($ch, CURLOPT_USERAGENT, 'WLMAPIClass');

            // Execute and grab the return data.
            $out = trim(curl_exec($ch));

            if (defined('WLMAPICLASS_DEBUG')) {
                $log = "-- WLMAPICLASS_DEBUG_START --\nURL: {$url}\nMETHOD: {$method}\nDATA: {$data}\nRESULT: {$out}\n-- WLMAPICLASS_DEBUG_END --\n";

                if (filter_var(WLMAPICLASS_DEBUG, FILTER_VALIDATE_EMAIL)) {
                    $log_type = 1;
                } elseif (file_exists(WLMAPICLASS_DEBUG)) {
                    $log_type = 3;
                } else {
                    $log_type = 0;
                }
                $log_dest = $log_type ? WLMAPICLASS_DEBUG : null;

                error_log($log, $log_type, $log_dest);
            }

            // Remove \0 characters if return format is json.
            if ('json' === strtolower($this->return_format)) {
                $out = str_replace('\\u0000', '', $out);
            }

            return $out;
        }

        /**
         * Prepends / to resource if it does not have it.
         *
         * @param  string $resource Resource.
         * @return string Fixed resource.
         */
        private function resource_fix($resource)
        {
            if ('/' !== substr($resource, 0, 1)) {
                $resource = '/' . $resource;
            }
            return $resource;
        }

        /**
         * API authentication.
         */
        private function authenticate()
        {
            if (! empty($this->authenticated)) {
                return true;
            }
            if ($this->digest_auth) {
                $m                   = $this->return_format;
                $this->return_format = 'json';
                $x                   = json_decode($this->request('GET', '/members/x'));
                $this->authenticated = (int) ( is_object($x) && ! empty($x->success) );
                $this->return_format = $m;
                return $this->authenticated;
            }
            $m                   = $this->return_format;
            $this->return_format = 'json';

            $output = json_decode($this->request('GET', '/auth'));
            if (1 !== (int) $output->success || empty($output->lock)) {
                $this->auth_error = 'No auth lock to open';
                return false;
            }

            $hash   = md5($output->lock . $this->key);
            $data   = [
                'key'               => $hash,
                'support_emulation' => 1,
            ];
            $output = json_decode($this->request('POST', '/auth', $data));
            if (1 === (int) $output->success) {
                $this->authenticated = 1;
                if (! empty($output->support_emulation)) {
                    $this->method_emulation = 1;
                }
            } else {
                $this->auth_error = ( (array) $output )['ERROR'];
                return false;
            }

            $this->return_format = $m;
            return true;
        }

        /**
         * Send a POST request to WishList Member API (add new data)
         * Returns API result on success or false on error.
         * If an error occured, a short description of the error will be
         * stored in the object's auth_error property
         *
         * @param  string $resource API resource being requsted.
         * @param  array  $data     Request data.
         * @return xml|php|json|false
         */
        public function post($resource, $data)
        {
            if (! $this->authenticate()) {
                return false;
            }
            return $this->request('POST', $this->resource_fix($resource), $data);
        }

        /**
         * Send a GET request to WishList Member API (retrieve data)
         * Returns API result on success or false on error.
         * If an error occured, a short description of the error will be
         * stored in the object's auth_error property
         *
         * @param  string $resource API resource being requsted.
         * @param  array  $data     Optional request data.
         * @return xml|php|json|false
         */
        public function get($resource, $data = [])
        {
            if (! $this->authenticate()) {
                return false;
            }
            return $this->request('GET', $this->resource_fix($resource), $data);
        }

        /**
         * Send a PUT request to WishList Member API (update existing data)
         * Returns API result on success or false on error.
         * If an error occured, a short description of the error will be
         * stored in the object's auth_error property
         *
         * @param  string $resource API resource being requsted.
         * @param  array  $data     Request data.
         * @return xml|php|json|false
         */
        public function put($resource, $data)
        {
            if (! $this->authenticate()) {
                return false;
            }
            return $this->request('PUT', $this->resource_fix($resource), $data);
        }

        /**
         * Send a DELETE to WishList Member API (delete the resource)
         * Returns API result on success or false on error.
         * If an error occured, a short description of the error will be
         * stored in the object's auth_error property
         *
         * @param  string $resource API resource being requsted.
         * @param  array  $data     Optional request data.
         * @return xml|php|json|false
         */
        public function delete($resource, $data = [])
        {
            if (! $this->authenticate()) {
                return false;
            }
            return $this->request('DELETE', $this->resource_fix($resource), $data);
        }
    }

    if (! class_exists('wlmapiclass')) {
        class_alias('WLM_API_Class', 'wlmapiclass');
    }
}
