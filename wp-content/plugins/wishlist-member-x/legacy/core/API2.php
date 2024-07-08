<?php

/**
 * WishList Member Application Programming Interface
 *
 * @package WishList Member
 */

/**
 * WishList Member API 2.0
 */
class WLMAPI2
{
    const MARKER = '/wlmapi/2.0/';

    /**
     * Request
     *
     * @var string
     */
    public $request;

    /**
     * Actual Request
     *
     * @var string
     */
    public $actual_request;

    /**
     * Method
     *
     * @var string
     */
    public $method;

    /**
     * Data
     *
     * @var array
     */
    public $data;

    /**
     * Return Type
     *
     * @var integer
     */
    public $return_type;

    /**
     * The result
     *
     * @var boolean
     */
    public $result;

    /**
     * Original Request
     *
     * @var string
     */
    public $selfdoc;

    /**
     * The base
     *
     * @var array
     */
    public $base;

    /**
     * Membership Levels
     *
     * @var array
     */
    public $wpm_levels;

    /**
     * Custom post Types
     *
     * @var array
     */
    public $custom_post_types;

    /**
     * Page
     *
     * @var boolean
     */
    public $__page__;

    /**
     * Per Page
     *
     * @var boolean
     */
    public $__per_page__;

    /**
     * Pagination
     *
     * @var boolean
     */
    public $__pagination__;

    /**
     * The original request
     *
     * @var boolean
     */
    public $original_request;
    /**
     * Request Aliases
     *
     * @var array
     */
    public $request_aliases = [
        ['/^protected\/([^\/]+)\/{0,1}$/', 'levels/protected/\1'],
        ['/^protected\/([^\/]+)\/(\d+)\/{0,1}$/', 'levels/protected/\1/\2'],
        ['/^content\/([^\/]+)\/(\d+)\/{0,1}$/', 'content/\1/protection/\2'],
        ['/^content\/([^\/]+)\/(\d+)\/protection\/{0,1}$/', 'content/\1/protection/\2'],
        ['/^categories\/([^\/]+)\/(\d+)\/{0,1}$/', 'taxonomies/\1/protection/\2'],
        ['/^taxonomies\/([^\/]+)\/(\d+)\/{0,1}$/', 'taxonomies/\1/protection/\2'],
    ];

    const ERROR_ACCESS_DENIED             = 0x00010000;
    const ERROR_INVALID_AUTH              = 0x00010001;
    const ERROR_INVALID_REQUEST           = 0x00010002;
    const ERROR_INVALID_RETURN_FORMAT     = 0x00010004;
    const ERROR_INVALID_RESOURCE          = 0x00010008;
    const ERROR_FORMAT_NOT_SUPPORTED_JSON = 0x00020001;
    const ERROR_FORMAT_NOT_SUPPORTED_XML  = 0x00020002;
    const ERROR_METHOD_NOT_SUPPORTED      = 0x00040001;

    /**
     * Constructor
     *
     * @global WishListMember $WishListMemberInstance
     * @param  string $request Resource.
     * @param  string $method  (optional) POST, GET, PUT, DELETE. Default GET.
     * @param  array  $data    (optional) Data to pass.
     */
    public function __construct($request, $method = 'GET', $data = null)
    {
        global $WishListMemberInstance;

        /*
         * special processing for external (remote) requests
         */
        $external = 'EXTERNAL' === $request;
        if ($external) {
            // Get the requested resource.
            $request                = urldecode(wlm_server_data()['REQUEST_URI']);
            $this->original_request = $request;

            // Get the requested method.
            $method = wlm_server_data()['REQUEST_METHOD'];
            if ('POST' === $method && ( 'PUT' === wlm_post_data()['____FAKE____'] || 'PUT' === wlm_post_data()['____METHOD_EMULATION____'] )) {
                $method = 'PUT';
            }
            if ('POST' === $method && ( 'DELETE' === wlm_post_data()['____FAKE____'] || 'DELETE' === wlm_post_data()['____METHOD_EMULATION____'] )) {
                $method = 'DELETE';
            }

            /*
             * set $data
             */
            switch ($method) {
                case 'GET':
                    $data = wlm_get_data(true);
                    break;
                case 'POST':
                    $data = wlm_post_data(true);
                    break;
                default:
                    if (! empty(wlm_post_data(true))) {
                        $data = wlm_post_data(true);
                    } else {
                        /*
                         * if $method is neither POST or GET then we get the data
                         * from the raw post data in php://input
                         */
                        parse_str(file_get_contents('php://input'), $data);
                    }
                    break;
            }
            list($request) = explode('&', $request);

            // Handling pagination and query limits.
            if ('GET' === $method) {
                if (! empty($data['__page__'])) {
                    $this->__page__ = (int) $data['__page__'];
                }
                if (! empty($data['__per_page__'])) {
                    $this->__per_page__ = (int) $data['__per_page__'];
                }
                if (! empty($data['__pagination__']) && ! empty($this->__page__)) {
                    $this->__pagination__ = (bool) $data['__pagination__'];
                }
                unset($data['__page__']);
                unset($data['__per_page__']);
                unset($data['__pagination__']);
            }
        }

        /*
         * split the requested resource by forward slash
         */
        $request_to_explode = explode(self::MARKER, $request, 2);
        $request            = explode('/', array_pop($request_to_explode));

        /*
         * the first part is the return type
         */
        $return_type = strtoupper(array_shift($request));

        /*
         * return type verification
         */
        $accepted_return_types = ['XML', 'JSON', 'PHP', 'RAW'];

        /*
         * return error for invalid return type format
         */
        if ($external && ! in_array($return_type, $accepted_return_types)) {
            $this->process_result($this->error(self::ERROR_INVALID_RETURN_FORMAT));
        }

        /*
         * check if the JSON return type requested is supported by the server
         * return error if not and it's requested
         */
        if ('JSON' === $return_type) {
            if (! function_exists('json_encode')) {
                $this->process_result($this->error(self::ERROR_FORMAT_NOT_SUPPORTED_JSON));
            }
        }

        /*
         * check if the XML return type requested is supported by the server
         * return error if not and it's requested
         */
        if ('XML' !== $return_type) {
            if (! class_exists('SimpleXMLElement')) {
                $this->process_result($this->error(self::ERROR_FORMAT_NOT_SUPPORTED_XML));
            }
        }

        /*
         * set $request and $actual_request properties
         */
        $this->request        = implode('/', $request);
        $this->actual_request = $this->request;

        /*
         * set $method property
         */
        $this->method = $method;

        /*
         * set $data property
         */
        $this->data = $data;

        /*
         * set $return_type property
         */
        $this->return_type = $return_type;

        /*
         * set $base property
         */
        $this->base = get_bloginfo('url') . '/?' . self::MARKER . $this->return_type . '/';

        /*
         * set $wpm_levels property
         */
        $this->wpm_levels = $WishListMemberInstance->get_option('wpm_levels');

        /*
         * add custom post types to aliases
         */
        $this->custom_post_types = array_keys(get_post_types(['_builtin' => false], 'object'));
        foreach ($this->custom_post_types as $custom_post_type) {
            $this->request_aliases[] = [
                '/^levels\/([^\/]+)\/' . $custom_post_type . '\/{0,1}$/',
                'levels/\1/posts',
            ];
            $this->request_aliases[] = [
                '/^levels\/([^\/]+)\/' . $custom_post_type . '\/(\d+)\/{0,1}$/',
                'levels/\1/posts/\2',
            ];
        }

        /*
         * process request aliases
         */
        foreach ($this->request_aliases as $alias) {
            if (preg_match($alias[0], $this->request)) {
                $this->actual_request = preg_replace($alias[0], $alias[1], $this->request);
                $request              = explode('/', $this->actual_request);
            }
        }

        /*
         * assemble the function name and the parameters to pass based
         * on the structure of the requested resource
         */
        $functions  = [];
        $parameters = [];
        while (! empty($request)) {
            $functions[] = trim(strtolower(array_shift($request)));
            if (! empty($request)) {
                $parameters[] = trim(array_shift($request));
            }
        }
        $functions = array_diff($functions, ['']);
        $function  = '_' . implode('_', $functions);

        /*
         * *********************************************** *
         * AT THIS POINT, THE FUNCTION NAME IS NOW IN $function
         * AND THE PARAMETERS IN $parameters
         * *********************************************** *
         */

        /*
         * if $function is a valid resource method then we call it
         */
        if (method_exists($this, $function)) {
            /*
             * authentication processing
             *
             * if we're not making an authentication request
             * then we check if we are already authenticated
             *
             * an exception to this is /resources
             */
            if ('_resources' === $function) {
                $result = call_user_func([$this, $function]);
            } else {
                $auth = true;
                if ($external && '_auth' !== $function) {
                    // Legacy api authentication.
                    $key    = $this->auth_key();
                    $cookie = $this->auth_cookie();
                    if (empty(wlm_getcookie($cookie)) || wlm_getcookie($cookie) != $key) {
                        $auth = false;

                        // Let's try http digest authentication first.
                        $digest_auth = new \WishListMember\API_Auth_Digest();
                        if ($digest_auth->status()) {
                            $auth = true;
                        }
                    }
                }

                /*
                 * if we're authenticated then we call $function
                 * if not, we return an ACCESS DENIED error
                 */
                if ($auth || ! $external) {
                    $WishListMemberInstance->api2_running = true;
                    $result                               = call_user_func_array([$this, $function], $parameters);
                    $WishListMemberInstance->api2_running = false;
                } else {
                    $result = $this->error(self::ERROR_ACCESS_DENIED);
                }
            }

            /*
             * let's process the request
             */
            $this->process_result($result);
        } else {
            /*
             * why on earth are we here?
             *
             * this means that the requested resource is invalid
             * so we return appropriate error message
             */
            $this->process_result($this->error(self::ERROR_INVALID_REQUEST));
        }
    }

    /**
     * Error Processing
     *
     * @param  mixed $error Can be any of the WLMAPI2 defined ERROR constants or an error message.
     * @return apiResult
     */
    private function error($error)
    {
        switch ($error) {
            case self::ERROR_ACCESS_DENIED:
            case self::ERROR_INVALID_AUTH:
                header('Status: 401', false, 401);
                break;
            case self::ERROR_INVALID_RETURN_FORMAT:
            case self::ERROR_INVALID_REQUEST:
            case self::ERROR_INVALID_RESOURCE:
                header('Status: 404', false, 404);
                break;
            case self::ERROR_FORMAT_NOT_SUPPORTED_JSON:
            case self::ERROR_FORMAT_NOT_SUPPORTED_XML:
            case self::ERROR_METHOD_NOT_SUPPORTED:
                header('Status: 415', false, 415);
                break;
        }
        return [
            'ERROR_CODE' => $error,
            'ERROR'      => $this->get_error_msg($error),
        ];
    }

    /**
     * Fetch the correct error message if specified
     *
     * @staticvar string $error_messages
     * @param     mixed $error Can be any of the WLMAPI2 defined ERROR constants or an error message.
     * @return    string Error Message
     */
    private function get_error_msg($error)
    {
        static $error_messages = [
            self::ERROR_ACCESS_DENIED             => 'Access Denied - Not authenticated',
            self::ERROR_INVALID_AUTH              => 'Access denied - Invalid authentication',
            self::ERROR_INVALID_REQUEST           => 'Page not found - Invalid method',
            self::ERROR_INVALID_RETURN_FORMAT     => 'Page not found - Invalid return format requested',
            self::ERROR_INVALID_RESOURCE          => 'Page not found - Invalid resource',
            self::ERROR_FORMAT_NOT_SUPPORTED_XML  => 'Unsupported Media Type - Server configuration does not support XML encoding',
            self::ERROR_FORMAT_NOT_SUPPORTED_JSON => 'Unsupported Media Type - Server configuration does not support JSON encoding',
            self::ERROR_METHOD_NOT_SUPPORTED      => 'Method Not Supported',
        ];
        if (isset($error_messages[ $error ])) {
            $error = $error_messages[ $error ];
        }
        return $error;
    }

    /**
     * Format apiResult based on $return_type
     *
     * @param  apiResult $result apiResult array.
     * @return string formatted apiResult
     */
    private function process_result($result)
    {
        $success = empty($result['ERROR_CODE']) ? 1 : 0;
        if (empty($result)) {
            $result = [];
        }
        $result = ['success' => $success] + $result;

        $pagination = isset($this->__pagination__) ? isset($this->__pagination__) : '';

        if ($pagination && $this->paginate_total_pages) {
            $result['pagination'] = [
                'page'           => $this->paginate_page,
                'total_pages'    => $this->paginate_total_pages,
                'items_per_page' => $this->paginate_per_page,
                'total_items'    => $this->paginate_total_items,
            ];
        }
        if (! empty($this->selfdoc) && $success) {
            $result['supported_verbs'] = $this->selfdoc;
        }
        switch ($this->return_type) {
            case 'JSON':
                $result = json_encode($result);
                break;
            case 'PHP':
                $result = serialize($result);
                break;
            case 'XML':
                $xml    = $this->toXML($result);
                $result = $xml->asXML();
                break;
        }
        $this->result = $result;
        return $result;
    }

    /**
     * Converts array to XML
     *
     * @param  array            $array Array to convert.
     * @param  SimpleXMLElement $xml   XML Object.
     * @param  string           $xname Node Name.
     * @return SimpleXMLElement
     */
    private function toXML($array, $xml = null, $xname = null)
    {
        $array = (array) $array;
        if (is_null($xml)) {
            $xml = new SimpleXMLElement('<root/>');
        }
        foreach ($array as $name => $value) {
            $name = preg_replace('/[^a-zA-Z_]/i', '', $name);
            if (empty($name)) {
                $name = $xname;
            }
            if (is_object($value)) {
                $value = (array) $value;
            }
            if (is_array($value)) {
                $this->toXML($value, is_numeric(key($value)) ? $xml : $xml->addChild($name), $name);
            } else {
                $xml->addChild($name, $value);
            }
        }
        return $xml;
    }

    /**
     * Generates the Public Authentication Key
     *
     * @global    WishListMember $WishListMemberInstance
     * @staticvar string $hash
     * @return    string Hash (Auth Key)
     */
    private function auth_key()
    {
        global $WishListMemberInstance;
        static $hash = 0;
        if (empty($hash)) {
            $key  = $WishListMemberInstance->GetAPIKey();
            $lock = wlm_getcookie('lock');
            if (empty($lock)) {
                return false;
            }
            $hash = md5($lock . $key);
        }
        return $hash;
    }

    /**
     * Returns valid hashes
     *
     * @return array
     */
    private function valid_hashes()
    {
        static $hashes = null;
        if (is_null($hashes)) {
            $keys   = ( new \WishListMember\APIKey() )->get_all_keys();
            $lock   = wlm_getcookie('lock');
            $hashes = [];
            foreach ($keys as $key) {
                $hashes[] = md5($lock . $key);
            }
            $hashes[] = $this->auth_key();
        }
        return $hashes;
    }

    /**
     * Returns name of Cookie to use
     *
     * @staticvar string $cookie
     * @return    string Cookie name
     */
    private function auth_cookie()
    {
        static $cookie = 0;
        if (empty($cookie)) {
            $cookie = md5('WLMAPI2' . $this->auth_key());
        }
        return $cookie;
    }

    /**
     * Returns the current user's IP address
     *
     * @return string IP Address
     */
    private function prepare_found_rows_stuff(&$__limit__, &$__found_rows__)
    {
        $__limit__      = '';
        $__found_rows__ = '';
        if (empty($this->__page__)) {
            return;
        }
        if (empty($this->__per_page__)) {
            $this->__per_page__ = 50;
        }
        $per_page  = $this->__per_page__;
        $page      = ( $this->__page__ - 1 ) * $per_page;
        $__limit__ = sprintf(' LIMIT %d,%d ', $page, $per_page);
        if (! empty($this->__pagination__)) {
            $__found_rows__ = ' SQL_CALC_FOUND_ROWS ';
        }
    }

    /**
     * Sets the found rows
     */
    private function set_found_rows()
    {
        global $wpdb;
        unset($this->paginate_page);
        unset($this->paginate_per_page);
        unset($this->paginate_total_pages);
        unset($this->paginate_total_items);
        $pagination = isset($this->__pagination__) ? $this->__pagination__ : '';
        if ($pagination) {
            $rows                       = $wpdb->get_var('SELECT FOUND_ROWS()');
            $this->paginate_page        = $this->__page__;
            $this->paginate_per_page    = $this->__per_page__;
            $this->paginate_total_items = $rows;
            $this->paginate_total_pages = ceil($rows / $this->paginate_per_page);
        }
    }

    /*
     * *********************************************** *
     * API Methods Start at this Point
     * IMPORTANT;
     * None of these methods can be called publicly
     * *********************************************** *
     */

    /**
     * Lists all available resources and their accepted methods
     *
     * Resource:
     *  /resources : GET
     *
     * @return apiResult
     */
    private function _resources()
    {
        if ('GET' === $this->method) {
            $resources = get_class_methods($this);
            foreach ($resources as $k => $v) {
                if (! ( '_' == substr($v, 0, 1) && '_' != substr($v, 1, 1) ) || '_resources' === $v || '_auth' === $v) {
                    unset($resources[ $k ]);
                }
            }
            $resources         = array_values($resources);
            $classname         = get_class($this);
            $resource_variants = [];
            foreach ($resources as $key => $resource) {
                $reflection     = new ReflectionMethod($classname, $resource);
                $resource_parts = explode('_', substr($resource, 1));
                $params         = [];
                foreach ($reflection->getParameters() as $param) {
                    $params[] = '{$' . $param->name . '}';
                }
                $required = $reflection->getNumberOfRequiredParameters();
                $variant  = '';
                foreach ($resource_parts as $ctr => $part) {
                    $variant .= '/' . $part;
                    if ($required <= $ctr) {
                        $resource_variants[] = $variant;
                    }
                    if ($params[ $ctr ]) {
                        $variant            .= '/' . $params[ $ctr ];
                        $resource_variants[] = $variant;
                    }
                }
            }
            $resources    = array_unique($resource_variants);
            $this->method = 'INFO';
            foreach ($resources as $key => $resource) {
                $function       = [];
                $params         = [];
                $resource_split = explode('/', substr($resource, 1));
                while ($resource_split) {
                    $function[] = array_shift($resource_split);
                    if ($resource_split) {
                        $params[] = array_shift($resource_split);
                    }
                }
                $function          = '_' . implode('_', $function);
                $methods           = call_user_func_array([$this, $function], $params);
                $resource          = [
                    'name'            => $resource,
                    'supported_verbs' => ['verb' => $methods],
                ];
                $resources[ $key ] = $resource;
            }

            $this->selfdoc = [];
            return ['resources' => ['resource' => array_values($resources)]];
        } else {
            return $this->error(self::ERROR_METHOD_NOT_SUPPORTED);
        }
    }

    /**
     * Resource:
     *   /auth : GET or POST
     *
     * @return apiResult
     */
    private function _auth()
    {
        $data        = $this->data;
        $hash        = $this->auth_key();
        $cookiestuff = parse_url(home_url());
        if (empty($cookiestuff['path'])) {
            $cookiestuff['path'] = '/';
        }
        switch ($this->method) {
            case 'GET':
                $lock = md5(strrev(md5(wlm_server_data()['REMOTE_ADDR'] . microtime())));
                wlm_setcookie('lock', $lock, 0, $cookiestuff['path']); // <- set cookie path to make it work with bugged cURL versions.
                $response = [
                    'lock' => $lock,
                ];
                return $response;
            case 'POST':
                if (! wlm_trim($data['key']) || ! in_array($data['key'], $this->valid_hashes())) {
                    return $this->error(self::ERROR_INVALID_AUTH);
                }
                $cookie_name = $this->auth_cookie();
                // <- set cookie path to make it work with bugged cURL versions.
                wlm_setcookie($cookie_name, $hash, 0, $cookiestuff['path']);
                $response = [
                    'key' => $hash,
                ];
                if (! empty($data['support_emulation'])) {
                    $response['support_emulation'] = 1;
                }
                return $response;
            default:
                return $this->error(self::ERROR_METHOD_NOT_SUPPORTED);
        }
    }

    /**
     * Protected Content
     *
     * @param  string  $type       Content Type.
     * @param  integer $content_id Content ID.
     * @return apiResult
     */
    public function protected_content($type, $content_id = null)
    {
        global $WishListMemberInstance, $wpdb;
        if (empty($content_id)) {
            $content_id = null;
        }
        $this->selfdoc = is_null($content_id) ? ['GET', 'POST'] : ['DELETE'];
        if ('INFO' === $this->method) {
            return $this->selfdoc;
        }
        $types = array_merge(['post', 'page', 'category'], $this->custom_post_types);
        if (! in_array($type, $types)) {
            return $this->error(self::ERROR_INVALID_RESOURCE);
        }
        if (! in_array($type, $this->custom_post_types)) {
            $otype = 'category' === $type ? 'categories' : $type . 's';
        } else {
            $otype = $type;
        }
        if (is_null($content_id)) {
            switch ($this->method) {
                case 'GET':
                    $content_ids = [];
                    switch ($type) {
                        case 'category':
                            $content_ids = $wpdb->get_col('SELECT `content_id` FROM `' . esc_sql(wishlistmember_instance()->table_names->contentlevels) . "` WHERE `level_id`='Protection' AND `type`='~CATEGORY'");
                            $content     = get_categories(
                                [
                                    'include'    => $content_ids,
                                    'hide_empty' => 0,
                                ]
                            );
                            foreach ($content as $k => $v) {
                                $content[ $k ] = [
                                    'ID'   => $v->term_id,
                                    'name' => $v->name,
                                ];
                            }
                            break;
                        default:
                            $content_ids   = $wpdb->get_col($wpdb->prepare('SELECT `content_id` FROM `' . esc_sql(wishlistmember_instance()->table_names->contentlevels) . "` WHERE `level_id`='Protection' AND `type`=%s", $type));
                            $content_ids[] = 0;
                            $this->prepare_found_rows_stuff($__limit__, $__found_rows__);
                            $content = $wpdb->get_results(
                                $wpdb->prepare(
                                    'SELECT %0s `ID`,`post_title` AS `name` FROM `'
                                    . $wpdb->posts
                                    . '` WHERE `post_type`=%s AND `ID` IN ('
                                    . implode(', ', array_fill(0, count($content_ids), '%d'))
                                    . ') %0s',
                                    $__found_rows__,
                                    $type,
                                    ...array_values($content_ids),
                                    ...[$__limit__]
                                )
                            );
                            $this->set_found_rows();
                            break;
                    }
                    return [$otype => [$type => $content]];
                    break;
                case 'POST':
                    $ids = (array) $this->data['ContentIds'];
                    switch ($type) {
                        case 'category':
                            foreach ($ids as $content_id) {
                                if (get_cat_name($content_id)) {
                                    $WishListMemberInstance->cat_protected($content_id, 'Y');
                                }
                            }
                            break;
                        case 'page':
                        case 'post':
                            foreach ($ids as $content_id) {
                                if (in_array(get_post_type($content_id), ['post', 'page'])) {
                                    $WishListMemberInstance->protect($content_id, 'Y');
                                }
                            }
                            break;
                    }
                    $this->method = 'GET';
                    return $this->protected_content($type);
                    break;
                default:
                    return $this->error(self::ERROR_METHOD_NOT_SUPPORTED);
            }
        } else {
            switch ($this->method) {
                case 'DELETE':
                    switch ($type) {
                        case 'category':
                            $WishListMemberInstance->cat_protected($content_id, 'N');
                            break;
                        case 'page':
                        case 'post':
                            $WishListMemberInstance->protect($content_id, 'N');
                            break;
                    }
                    break;
                default:
                    return $this->error(self::ERROR_METHOD_NOT_SUPPORTED);
            }
        }
    }

    /**
     * Resource:
     *   /levels : GET, POST
     *   /levels/{id} : GET, PUT, DELETE
     *
     * @global WishListMember $WishListMemberInstance
     * @param  integer $level_id Optional Membership Level ID.
     * @return apiResult
     */
    private function _levels($level_id = null)
    {
        global $WishListMemberInstance;
        $this->selfdoc = is_null($level_id) ? ['GET', 'POST'] : ['GET', 'PUT', 'DELETE'];
        if ('INFO' === $this->method) {
            return $this->selfdoc;
        }

        /*
         * map internal variable names to "beautiful" external ones
         */
        $level_map = [
            'id'                          => 'id',
            'name'                        => 'name',
            'url'                         => 'registration_url',
            'login_page'                  => 'after_login_redirect',
            'afterreg_page'               => 'after_registration_redirect',
            'allpages'                    => 'access_all_pages',
            'allcategories'               => 'access_all_categories',
            'allposts'                    => 'access_all_posts',
            'allcomments'                 => 'access_all_comments',
            'noexpire'                    => 'no_expiry',
            'expire'                      => 'expiry',
            'calendar'                    => 'expiry_period',
            'upgradeTo'                   => 'sequential_upgrade_to',
            'upgradeAfter'                => 'sequential_upgrade_after',
            'upgradeMethod'               => 'sequential_upgrade_method',
            'count'                       => 'member_count',
            'requirecaptcha'              => 'require_captcha',
            'requireemailconfirmation'    => 'require_email_confirmation',
            'requireadminapproval'        => 'require_admin_approval',
            'isfree'                      => 'grant_continued_access',
            'disableexistinglink'         => 'disable_existing_users_link',
            'registrationdatereset'       => 'registration_date_reset',
            'registrationdateresetactive' => 'registration_date_reset_active',
            'uncancelonregistration'      => 'uncancel_on_registration',
            'role'                        => 'wordpress_role',
            'levelOrder'                  => 'level_order',
            'removeFromLevel'             => 'remove_from_levels',
            'expire_date'                 => 'expire_date',
            'expire_option'               => 'expire_option'
        ];

        /*
         * flip $level_map so we also have a mirror copy
         */
        $level_map_flip = array_flip($level_map);

        /*
         * level data default values
         */
        $level_defaults = $WishListMemberInstance->level_defaults;

        /*
         * go through each membership level and
         * re-format the values for outputting
         */
        $wpm_levels = $this->wpm_levels;
        foreach ($wpm_levels as $id => $level) {
            $xlevel       = array_fill_keys($level_map, '');
            $xlevel['id'] = $id;
            foreach ($level_map as $key => $value) {
                $xkey = $value;

                if (isset($level[ $key ])) {
                    $value = $level[ $key ];
                }

                switch ($xkey) {
                    case 'access_all_pages':
                    case 'access_all_categories':
                    case 'access_all_posts':
                    case 'access_all_comments':
                        $value = 'on' === $value ? 1 : 0;
                        break;
                    case 'require_captcha':
                    case 'require_email_confirmation':
                    case 'require_admin_approval':
                    case 'grant_continued_access':
                    case 'disable_existing_users_link':
                    case 'registration_date_reset':
                    case 'uncancel_on_registration':
                    case 'no_expiry':
                        $value = empty($value) ? 0 : 1;
                        break;
                    case 'after_login_redirect':
                    case 'after_registration_redirect':
                        switch ($value) {
                            case '':
                                $value = 'homepage';
                                break;
                            case '---':
                                $value = 'global';
                                break;
                        }
                        break;
                    case 'remove_from_levels':
                        if (is_array($value) && ! empty($value)) {
                            $value = ['remove_from_level' => array_keys($value)];
                        }
                        break;
                }
                $xlevel[ $xkey ] = $value;
            }
            $wpm_levels[ $id ] = $xlevel;
        }

        /*
         * if $level_id parameter is not passed then we
         * expect either a GET or a POST
         */
        if (empty($level_id)) {
            switch ($this->method) {
                /*
                 * list all levels
                 */
                case 'GET':
                    $levels  = array_keys($wpm_levels);
                    $xlevels = [];
                    foreach ($levels as $level) {
                        $xlevels[] = [
                            'id'     => $level,
                            'name'   => $wpm_levels[ $level ]['name'],
                            '_more_' => '/levels/' . $level,
                        ];
                    }
                    $wpm_levels = ['levels' => ['level' => $xlevels]];
                    return $wpm_levels;

                /*
                 * add new level
                 */
                case 'POST':
                    $wpm_levels = $this->wpm_levels;
                    $level      = $level_defaults;
                    if (empty($this->data['name'])) {
                        return $this->error('You must specify at least the name of the level that you wish to add');
                    }
                    foreach ($wpm_levels as $xxx) {
                        if ($xxx['name'] == $this->data['name']) {
                            return $this->error('The name of the level that you wish to add is already in use. Please specify a different one');
                        }
                        if ($xxx['url'] == $this->data['registration_url']) {
                            return $this->error('The registration URL of the level that you wish to add is already in use. Please specify a different one OR leave it blank to have it auto-generated');
                        }
                    }
                    while (isset($wpm_levels[ $id = time() ])) {
                        sleep(1);
                    }

                    // Set noexpire to 1 so that levels will no have expiration by default.
                    if (! isset($level['noexpire'])) {
                        $level['noexpire'] = (int) $this->data['no_expiry'];

                        if (! $level['noexpire']) {
                            if ((int) $this->data['expire_option'] === 2) {
                                $level['expire_date'] = $this->data['expire_date'];
                            } else {
                                $level['expire']   = $this->data['expiry'];
                                $level['calendar'] = $this->data['expiry_period'];
                            }
                        }
                    }

                    $ldata = array_intersect_key($this->data, $level);
                    foreach ($ldata as $key => $value) {
                        switch ($key) {
                            case 'after_login_redirect':
                            case 'after_registration_redirect':
                                switch (strtolower($value)) {
                                    case 'global':
                                    case '':
                                        $value = '---';
                                        break;
                                    case 'homepage':
                                        $value = '';
                                        break;
                                }
                                break;
                        }
                        $key           = $level_map_flip[ $key ];
                        $level[ $key ] = $value;
                    }
                    if (empty($level['url'])) {
                        $level['url'] = $WishListMemberInstance->make_reg_url();
                    }
                    if (! empty($level['removeFromLevel'])) {
                        $r                        = array_intersect((array) $level['removeFromLevel'], array_keys($wpm_levels));
                        $level['removeFromLevel'] = empty($r) ? '' : array_fill_keys($r, 1);
                    }
                    if (! empty($level['addToLevel'])) {
                        $r                   = array_intersect((array) $level['addToLevel'], array_keys($wpm_levels));
                        $level['addToLevel'] = empty($r) ? '' : array_fill_keys($r, 1);
                    }
                    if (! empty($level['expire_option']) && (int) $level['expire_option'] === 2) {
						$level['expire_date'] = wlm_date('F j, Y H:i:s', strtotime($level['expire_date']));
					}
                    $level             = array_diff($level, ['']);
                    $wpm_levels[ $id ] = $level;
                    $this->wpm_levels  = $wpm_levels;
                    $WishListMemberInstance->save_option('wpm_levels', $wpm_levels);
                    $this->method = 'GET';
                    return $this->_levels($id);

                /*
                 * error because it's neither GET or POST and there's no $level_id
                 */
                default:
                    return $this->error(self::ERROR_METHOD_NOT_SUPPORTED);
            }

            /*
             * if $level_id is specified then we expect either
             * GET, PUT, or DELETE
             */
        } else {
            /*
             * return error if $level_id is not valid
             */
            if (! isset($this->wpm_levels[ $level_id ])) {
                return $this->error('Invalid Level ID');
            }
            switch ($this->method) {
                /*
                 * update membership level
                 */
                case 'PUT':
                    $wpm_levels = $this->wpm_levels;
                    $level      = array_merge($level_defaults, $wpm_levels[ $level_id ]);
                    foreach ($this->data as $key => $value) {
                        switch ($key) {
                            case 'after_login_redirect':
                            case 'after_registration_redirect':
                                switch (strtolower($value)) {
                                    case 'global':
                                    case '':
                                        $value = '---';
                                        break;
                                    case 'homepage':
                                        $value = '';
                                        break;
                                }
                                break;
                        }
                        $key = $level_map_flip[ $key ];
                        if (isset($level[ $key ])) {
                            $level[ $key ] = $value;
                        }
                    }
                    if (! empty($level['removeFromLevel'])) {
                        $r                        = array_intersect((array) $level['removeFromLevel'], array_keys($wpm_levels));
                        $level['removeFromLevel'] = empty($r) ? '' : array_fill_keys($r, 1);
                    }
                    if (! empty($level['expire_option']) && (int) $level['expire_option'] === 2) {
						$level['expire_date'] = wlm_date('F j, Y H:i:s', strtotime($level['expire_date']));
					}
                    $level                   = array_diff($level, ['']);
                    $wpm_levels[ $level_id ] = $level;
                    $this->wpm_levels        = $wpm_levels;
                    $WishListMemberInstance->save_option('wpm_levels', $wpm_levels);
                    $this->method = 'GET';
                    return $this->_levels($level_id);

                /*
                 * delete level (only if it does not have any members in it)
                 */
                case 'DELETE':
                    if ($this->wpm_levels[ $level_id ]['count'] < 1) {
                        unset($this->wpm_levels[ $level_id ]);
                        $WishListMemberInstance->save_option('wpm_levels', $this->wpm_levels);
                        $this->method = 'GET';
                        return $this->_levels();
                    } else {
                        return $this->error('Cannot delete levels that have members');
                    }
                    break;

                /*
                 * get full information for a level
                 */
                case 'GET':
                    $level           = ['id' => $level_id] + $wpm_levels[ $level_id ];
                    $level['_more_'] = [
                        "/levels/{$level_id}/members",
                        "/levels/{$level_id}/posts",
                        "/levels/{$level_id}/pages",
                        "/levels/{$level_id}/comments",
                        "/levels/{$level_id}/taxonomies",
                    ];
                    foreach ($this->custom_post_types as $custom_post_type) {
                        $level['_more_'][] = "/levels/{$level_id}/{$custom_post_type}";
                    }
                    $wpm_levels = ['level' => $level];
                    return $wpm_levels;

                /*
                 * return error if method is neither GET, PUT or DELETE
                 */
                default:
                    return $this->error(self::ERROR_METHOD_NOT_SUPPORTED);
            }
        }
    }

    /**
     * Resource:
     *   /levels/{level_id}/members : GET, POST
     *   /levels/{level_id}/members/{member_id} : GET, PUT, DELETE
     *
     * @global WishListMember $WishListMemberInstance
     * @param  integer $level_id  Membership Level ID.
     * @param  integer $member_id User ID.
     * @return apiResult
     */
    private function _levels_members($level_id, $member_id = null)
    {
        global $WishListMemberInstance, $wpdb;
        static $last_query = []; // Cached $wpdb->last_query indexed by $level_id.

        $this->selfdoc = is_null($member_id) ? ['GET', 'POST'] : ['GET', 'PUT', 'DELETE'];
        if ('INFO' === $this->method) {
            return $this->selfdoc;
        }
        switch ($this->method) {
            case 'GET': // list members for level.
                if (! empty($member_id)) {
                    $x = $WishListMemberInstance->get_membership_levels($member_id);
                    if (in_array($level_id, $x)) {
                        $member_ids = [(int) $member_id];
                        $full       = true;
                    } else {
                        return $this->error(self::ERROR_INVALID_RESOURCE);
                    }
                } else {
                    if (empty($this->data['filter']['ID'])) {
                        $filter_status = isset($this->data['filter']['status']) ? $this->data['filter']['status'] : '';
                        if ($filter_status) {
                            $member_ids = $WishListMemberInstance->member_ids_by_status($this->data['filter']['status'], explode(',', $level_id));
                        } else {
                            $member_ids = $WishListMemberInstance->member_ids(explode(',', $level_id));
                        }

                        if (! isset($last_query[ $level_id ]) && $wpdb->last_query) {
                            $last_query[ $level_id ] = $wpdb->last_query;
                            $member_ids = false;
                        } else {
                            $member_ids[] = 0;
                        }
                    } else {
                        $member_ids = (array) $this->data['filter']['ID'];
                        foreach ($member_ids as &$member_id) {
                            $member_id += 0;
                        }
                        unset($member_id);
                        $member_ids[] = 0;
                    }
                    $full = false;
                }
                $this->prepare_found_rows_stuff($__limit__, $__found_rows__);

                if (false === $member_ids && isset($last_query[ $level_id ])) {
                    // Let's use the cached $wpdb->last_query.
                    $member_ids = $wpdb->get_results(
                        $wpdb->prepare(
                            'SELECT %0s `ID` AS `id`,`user_login`,`user_email` FROM '
                            . $wpdb->users
                            . ' WHERE `ID` IN ('
                            . $last_query[ $level_id ]
                            . ') %0s',
                            $__found_rows__,
                            $__limit__
                        ),
                        ARRAY_A
                    );
                } else {
                    // Member ids are in the $member_ids array.
                    // Make sure $member_ids is an array and not empty.
                    if (! is_array($member_ids) || empty($member_ids)) {
                        $member_ids = [0];
                    }

                    $member_ids = $wpdb->get_results(
                        $wpdb->prepare(
                            'SELECT %0s `ID` AS `id`,`user_login`,`user_email` FROM '
                            . $wpdb->users
                            . ' WHERE `ID` IN ('
                            . implode(', ', array_fill(0, count($member_ids), '%d'))
                            . ') %0s',
                            $__found_rows__,
                            ...array_values($member_ids),
                            ...[$__limit__]
                        ),
                        ARRAY_A
                    );
                }

                $this->set_found_rows();
                $members = ['members' => ['member' => []]];
                foreach ($member_ids as $member) {
                    $uid = $member['id'];
                    if ($full) {
                        $user            = new \WishListMember\User($uid);
                        $member['level'] = $user->Levels[ $level_id ];
                        unset($member['level']->Level_ID);
                        unset($member['level']->Name);
                        $members = ['member' => $member];
                    } else {
                        if (! empty($this->data['additional_data'])) {
                            $additional_data = $wpdb->get_results(
                                $wpdb->prepare(
                                    "SELECT um.meta_key, um.meta_value FROM {$wpdb->usermeta} um WHERE um.user_id=%d AND meta_key IN (" . implode(', ', array_fill(0, count($this->data['additional_data']), '%s')) . ')',
                                    $member['id'],
                                    ...array_values($this->data['additional_data'])
                                )
                            );
                            // Format.
                            $fmt_additional_data = [];
                            foreach ($additional_data as $d) {
                                $fmt_additional_data[ $d->meta_key ] = wlm_maybe_unserialize($d->meta_value);
                            }
                            $member['additional_data'] = $fmt_additional_data;
                        }
                        $member['_more_']               = "/levels/{$level_id}/members/{$uid}";
                        $members['members']['member'][] = $member;
                    }
                }
                return $members;

            case 'POST':
                foreach ((array) $this->data['Users'] as $uid) {
                    if ($wpdb->get_var($wpdb->prepare("SELECT `ID` FROM {$wpdb->users} WHERE `ID`=%d", $uid))) {
                        $levels = $WishListMemberInstance->get_membership_levels($uid);

                        // Check if the user is already registered to the level and use this later for adjusting registration/expiration dates.
                        $inlevel                             = in_array($level_id, $levels);
                        $this->data['in_registration_level'] = $inlevel ? true : false;

                        // Set membership level.
                        $levels[] = $level_id;
                        $WishListMemberInstance->set_membership_levels($uid, $levels);

                        // Only send the registration emails if the user is not yet registered to the level.
                        if (! $inlevel) {
                            $this->send_emails(['member_id' => $uid], [$level_id => $this->wpm_levels[ $level_id ]['name']]);
                        }

                        $this->method = 'PUT';
                        $this->_levels_members($level_id, $uid);

                        // If multisite then check if user is a member of the multisite, if not add them to it...
                        if (is_multisite()) {
                            $blog_id = get_current_blog_id();

                            if (! is_user_member_of_blog($uid, $blog_id)) {
                                add_user_to_blog($blog_id, $uid, get_option('default_role'));
                            }
                        }
                    }
                }
                $this->method = 'GET';
                $data         = ['filter' => ['ID' => $this->data['Users']]];
                $this->data   = $data;
                return $this->_levels_members($level_id);

            case 'PUT':
                if ($wpdb->get_var($wpdb->prepare("SELECT `ID` FROM {$wpdb->users} WHERE `ID`=%d", $member_id))) {
                    extract($this->data, EXTR_OVERWRITE | EXTR_PREFIX_ALL, 'data');
                    if (isset($data_Cancelled)) {
                        if ($data_Cancelled && $data_ShoppingCartRequest && ! empty($this->wpm_levels[ $level_id ]['isfree'])) {
                            $WishListMemberInstance->level_sequential_cancelled($level_id, $member_id, true);
                        } else {
                            $sendmail          = (bool) wlm_arrval($this->data, 'SendMail');
                            $sendmail_perlevel = (bool) wlm_arrval($this->data, 'SendMailPerLevel');

                            if ($sendmail || $sendmail_perlevel) {
                                if (! $sendmail_perlevel) {
                                    add_filter(
                                        'wishlistmember_per_level_templates',
                                        function ($templates) {
                                            unset($templates['membership_cancelled']);
                                            unset($templates['membership_uncancelled']);
                                            return $templates;
                                        }
                                    );
                                }
                            } else {
                                // Disable cancel / uncancel email notif.
                                add_filter('wishlistmember_pre_email_template', '__return_false', 11, 2);
                            }
                            $WishListMemberInstance->level_cancelled($level_id, $member_id, (bool) $data_Cancelled);
                            remove_filter('wishlistmember_pre_email_template', '__return_false', 11, 2);
                        }
                    }

                    if (isset($data_CancelDate)) {
                        if (is_numeric($data_CancelDate)) {
                            $data_CancelDate = (int) $data_CancelDate;
                        } else {
                            $data_CancelDate = strtotime($data_CancelDate);
                        }
                        $WishListMemberInstance->schedule_level_deactivation($level_id, (array) $member_id, $data_CancelDate);
                    }
                    if (isset($data_Pending)) {
                        $WishListMemberInstance->level_for_approval($level_id, $member_id, (bool) $data_Pending);
                    }
                    if (isset($data_UnConfirmed)) {
                        $WishListMemberInstance->level_unconfirmed($level_id, $member_id, (bool) $data_UnConfirmed);
                    }
                    if (isset($data_Timestamp)) {
                        $WishListMemberInstance->user_level_timestamp($member_id, $level_id, $data_Timestamp, true);
                    }

                    // Let's adjust registration dates if ObeyLevelsAdditionalSettings was passed and it's enabled in the additional settings.
                    if (
                        ( isset($this->data['ObeyLevelsAdditionalSettings']) && $this->data['ObeyLevelsAdditionalSettings'] ) &&
                        isset($this->wpm_levels[ $level_id ])
                    ) {
                        $wpm_levels = $this->wpm_levels;
                        if ($data_in_registration_level) {
                            // For Expired Members that is a member of registration level.
                            $expired      = $WishListMemberInstance->level_expired($level_id, $member_id);
                            $resetexpired = 1 == $wpm_levels[ $level_id ]['registrationdatereset'];
                        }
                        if ($expired && $resetexpired) {
                            $WishListMemberInstance->user_level_timestamp($member_id, $level_id, time());
                        } else {
                            // If levels has expiration and allow reregistration for active members.
                            $levelexpires     = isset($wpm_levels[ $level_id ]['expire']) ? (int) $wpm_levels[ $level_id ]['expire'] : false;
                            $levelexpires_cal = isset($wpm_levels[ $level_id ]['calendar']) ? $wpm_levels[ $level_id ]['calendar'] : false;
                            $level_is_ongoing = isset($wpm_levels[ $level_id ]['noexpire']) ? (int) $wpm_levels[ $level_id ]['noexpire'] : false;

                            $resetactive = 1 == $wpm_levels[ $level_id ]['registrationdateresetactive'];
                            if ($levelexpires && $resetactive && ! $level_is_ongoing) {
                                if ($data_in_registration_level) {
                                    // Get the registration date before it gets updated because we will use it later.
                                    $levelexpire_regdate = $WishListMemberInstance->Get_UserLevelMeta($member_id, $level_id, 'registration_date');
                                }
                                $levelexpires_cal = in_array($levelexpires_cal, ['Days', 'Weeks', 'Months', 'Years']) ? $levelexpires_cal : false;

                                if ($levelexpires_cal && $levelexpire_regdate) {
                                    list( $xdate, $xfraction )                                 = explode('#', $levelexpire_regdate);
                                    list( $xyear, $xmonth, $xday, $xhour, $xminute, $xsecond ) = preg_split('/[- :]/', $xdate);
                                    if ('Days' === $levelexpires_cal) {
                                        $xday = $levelexpires + $xday;
                                    }
                                    if ('Weeks' === $levelexpires_cal) {
                                        $xday = ( $levelexpires * 7 ) + $xday;
                                    }
                                    if ('Months' === $levelexpires_cal) {
                                        $xmonth = $levelexpires + $xmonth;
                                    }
                                    if ('Years' === $levelexpires_cal) {
                                        $xyear = $levelexpires + $xyear;
                                    }
                                    $WishListMemberInstance->user_level_timestamp($member_id, $level_id, mktime($xhour, $xminute, $xsecond, $xmonth, $xday, $xyear));
                                }
                            }
                        }
                    }

                    if (! empty($data_TxnID)) {
                        $WishListMemberInstance->set_membership_level_txn_id($member_id, $level_id, $data_TxnID);
                    }
                    $this->method = 'GET';
                    return $this->_levels_members($level_id, $member_id);
                } else {
                    return $this->error(self::ERROR_INVALID_RESOURCE);
                }
                $this->method = 'GET';
                return $this->_levels_members($level_id, $member_id);

            case 'DELETE':
                $levels = array_diff($WishListMemberInstance->get_membership_levels($member_id), [$level_id]);
                $WishListMemberInstance->set_membership_levels($member_id, $levels, ['sync' => false]);
                $WishListMemberInstance->schedule_sync_membership(true);
                return;
        }
    }

    /**
     * Resource:
     *   /txnid/{txn_id}/members : GET
     *
     * @param  string $txn_id Transaction Id.
     * @return apiResult
     */
    private function _txnid($txn_id = null)
    {
        $txn['txn'] = [];
        if (is_null($txn_id)) {
            return null;
        }
        global $WishListMemberInstance, $wpdb;
        $this->prepare_found_rows_stuff($__limit__, $__found_rows__);
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, ( $__found_rows__, $__limit__ )
        $trans = $wpdb->get_results(
            $wpdb->prepare(
                'SELECT %0s `userlevel_id`,`option_value` FROM `'
                . esc_sql(wishlistmember_instance()->table_names->userlevel_options)
                . '` WHERE `option_value` LIKE %s %0s',
                $__found_rows__,
                $txn_id . '%',
                $__limit__
            )
        );
        $this->set_found_rows();
        foreach ($trans as $tran) {
            $userlvl      = $wpdb->get_row($wpdb->prepare('SELECT `user_id`,`level_id` FROM `' . esc_sql(wishlistmember_instance()->table_names->userlevels) . '` WHERE ID=%d', $tran->userlevel_id));
            $txn['txn'][] = [
                'txnid'    => $tran->option_value,
                'user_id'  => $userlvl->user_id,
                'level_id' => $userlvl->level_id,
            ];
        }
        return $txn;
    }

    /**
     * Resource:
     *   /levels/{level_id}/posts : GET
     *   /levels/{level_id}/posts/{post_id} : GET, DELETE
     *
     * @param  integer $level_id Membership Level ID.
     * @param  integer $post_id  Post ID.
     * @return apiResult
     */
    private function _levels_posts($level_id, $post_id = null)
    {
        $x    = explode('/', $this->request);
        $type = 'levels' === $x[0] ? $x[2] : $x[1];
        if ('posts' === $type) {
            $type = 'post';
        }
        if ('protected' === $level_id) {
            return $this->protected_content($type, $post_id);
        } else {
            return $this->level_content($type, $level_id, $post_id);
        }
    }

    /**
     * Resource:
     *   /levels/{level_id}/pages : GET
     *   /levels/{level_id}/pages/{page_id} : GET, DELETE
     *
     * @param  integer $level_id Membership Level ID.
     * @param  integer $page_id  Page ID.
     * @return apiResult
     */
    private function _levels_pages($level_id, $page_id = null)
    {
        if ('protected' === $level_id) {
            return $this->protected_content('page', $page_id);
        } else {
            return $this->level_content('page', $level_id, $page_id);
        }
    }

    /**
     * Resource:
     *   /levels/{level_id}/comments : GET
     *   /levels/{level_id}/comments/{comment_id} : GET, DELETE
     *
     * @param  integer $level_id Membership Level ID.
     * @param  integer $post_id  Comment ID.
     * @return apiResult
     */
    private function _levels_comments($level_id, $post_id = null)
    {
        return $this->level_content('comment', $level_id, $post_id);
    }

    /**
     * Resource:
     *   /levels/{level_id}/categories : GET
     *   /levels/{level_id}/categories/{category_id} : GET, DELETE
     *
     * @param  integer $level_id    Membership Level ID.
     * @param  integer $category_id Category ID.
     * @return apiResult
     */
    private function _levels_categories($level_id, $category_id = null)
    {
        if ('protected' === $level_id) {
            return $this->protected_content('category', $category_id);
        } else {
            return $this->level_content('category', $level_id, $category_id);
        }
    }

    /**
     * Resource:
     *   /levels/{level_id}/{custom_post_type} : GET
     *   /levels/{level_id}/{custom_post_type}/{post_id} : GET, DELETE
     *
     * @param  integer $type       Custom Post Type.
     * @param  string  $level_id   Membership Level ID.
     * @param  integer $content_id Content ID.
     * @return apiResult
     */
    public function level_content($type, $level_id, $content_id = null)
    {
        global $WishListMemberInstance, $wpdb;
        if (empty($content_id)) {
            $content_id = null;
        }
        $this->selfdoc = is_null($content_id) ? ['GET', 'POST'] : ['GET', 'DELETE'];
        if ('INFO' === $this->method) {
            return $this->selfdoc;
        }
        $types = array_merge(['post', 'page', 'comment', 'category'], $this->custom_post_types);
        if (! in_array($type, $types, true)) {
            return $this->error(self::ERROR_INVALID_RESOURCE);
        }
        if (! in_array($type, $this->custom_post_types, true)) {
            $otype = 'category' === $type ? 'categories' : $type . 's';
        } else {
            $otype = $type;
        }
        if ('comment' === $type) {
            $type = 'post';
        }
        $wpm_levels = $this->wpm_levels;
        if (! isset($wpm_levels[ $level_id ]) && $WishListMemberInstance->is_ppp_level($level_id)) {
            return $this->error(self::ERROR_INVALID_RESOURCE);
        }
        if (is_null($content_id) || 'GET' === $this->method) {
            switch ($this->method) {
                case 'GET':
                    $content_ids = $WishListMemberInstance->get_membership_content($otype, $level_id);
                    if (! is_null($content_id)) {
                        $content_ids = array_intersect($content_ids, (array) $content_id);
                    }
                    $content_ids[] = 0;
                    if ('category' === $type) {
                        $content = get_categories(
                            [
                                'include'    => $content_ids,
                                'hide_empty' => 0,
                            ]
                        );
                        foreach ($content as $k => $v) {
                            $content[ $k ] = [
                                'ID'   => $v->term_id,
                                'name' => $v->name,
                            ];
                            if (is_null($content_id)) {
                                $content[ $k ]['_more_'] = "/levels/{$level_id}/{$otype}/{$v->term_id}";
                            }
                        }
                    } else {
                        $this->prepare_found_rows_stuff($__limit__, $__found_rows__);
                        $content = $wpdb->get_results(
                            $wpdb->prepare(
                                'SELECT %0s `ID`,`post_title` AS `name`, CONCAT("/levels/%0s/%0s/",`ID`) AS `%0s` FROM `'
                                . $wpdb->posts
                                . '` WHERE %0s `post_type`=%s AND `post_status`="publish" ORDER BY `post_date` DESC %0s',
                                $__found_rows__,
                                $level_id,
                                $otype,
                                is_nulL($content_id) ? '_more_' : '_x_',
                                empty($wpm_levels[ $level_id ][ 'all' . $otype ]) ? $wpdb->prepare('`ID` IN (' . implode(', ', array_fill(0, count($content_ids), '%d')) . ') AND ', ...array_values($content_ids)) : '',
                                $type,
                                $__limit__
                            ),
                            ARRAY_A
                        );
                        $this->set_found_rows();
                    }
                    return [$otype => [$type => $content]];
                    break;
                case 'POST':
                    if (! empty($this->data['ContentIds'])) {
                        $Ids  = array_values((array) $this->data['ContentIds']);
                        $data = [
                            'Checked'     => array_combine(array_values($Ids), array_fill(0, count($Ids), 1)),
                            'ID'          => array_combine(array_values($Ids), array_fill(0, count($Ids), 0)),
                            'ContentType' => $otype,
                            'Level'       => $level_id,
                        ];
                        $WishListMemberInstance->save_membership_content($data);
                        $WishListMemberInstance->sync_content($otype);
                    }
                    $this->method = 'GET';
                    return $this->level_content($type, $level_id);
                default:
                    return $this->error(self::ERROR_METHOD_NOT_SUPPORTED);
            }
        } else {
            switch ($this->method) {
                case 'DELETE':
                    $data = [
                        'Checked'     => [],
                        'ID'          => [$content_id => 0],
                        'ContentType' => $otype,
                        'Level'       => $level_id,
                    ];
                    $WishListMemberInstance->save_membership_content($data);
                    $WishListMemberInstance->sync_content($otype);
                    break;
                default:
                    return $this->error(self::ERROR_METHOD_NOT_SUPPORTED);
                    break;
            }
        }
    }

    /**
     * Resource:
     *   /members : GET, POST
     *   /members/{id} : GET, PUT, DELETE
     *
     * @global <type> $wpdb
     * @global WishListMember $WishListMemberInstance
     * @param  integer $member_id User ID.
     * @return apiResult
     */
    private function _members($member_id = null)
    {
        global $wpdb, $WishListMemberInstance;
        $this->selfdoc = is_null($member_id) ? ['GET', 'POST'] : ['GET', 'PUT', 'DELETE'];
        if ('INFO' === $this->method) {
            return $this->selfdoc;
        }
        $data = $this->data;

        /*
         * separate Levels, RemoveLevels and Sequential
         * from user data if method is either POST or PUT
         */
        if ('POST' === $this->method || 'PUT' === $this->method) {
            $nlevels    = [];
            $rlevels    = [];
            $sequential = null;
            if (isset($data['Levels'])) {
                $nlevels = empty($data['Levels']) ? [] : (array) $data['Levels'];
                unset($data['Levels']);
            }
            if (isset($data['RemoveLevels'])) {
                $rlevels = empty($data['RemoveLevels']) ? [] : (array) $data['RemoveLevels'];
                unset($data['RemoveLevels']);
            }
            if (isset($data['Sequential'])) {
                $sequential = $data['Sequential'];
                if (empty($sequential) && ! is_numeric($sequential)) {
                    $sequential = 1;
                }
                unset($data['Sequential']);
            }

            /*
             * determine if transaction ID and timestamp
             * is specified for each level to be added
             * and add each to $txns and $times respectively
             */
            if (! empty($nlevels)) {
                $levels = [];
                $times  = [];
                $txns   = [];
                foreach ($nlevels as $level) {
                    if (! empty($level)) {
                        $level                                       = array_pad(array_values((array) $level), 3, 0);
                        list($level_id, $transaction_id, $timestamp) = $level;
                        if ($level_id) {
                            $levels[] = $level_id;

                            /*
                             * a value of -1 for transaction_id and timestamp
                             * means that we just leave the current one in database
                             *
                             * a value of 0 generates an internal WishList Member transaction ID for
                             * transaction ID and current timestamp for timestamp
                             */
                            if (-1 != $transaction_id) {
                                $txns[ $level_id ] = $transaction_id;
                            }
                            if (-1 != $timestamp) {
                                if (empty($timestamp)) {
                                    $timestamp = time();
                                }
                                $times[ $level_id ] = $timestamp;
                            }
                        }
                    }
                }
                $nlevels = $levels;
            }
        }

        /*
         * let's go through the methods
         */
        switch ($this->method) {
            /*
             * List members
             */
            case 'GET':
                /*
                 * list all members if $member_id no specified
                 */
                if (empty($member_id)) {
                    $filter           = isset($this->data['filter']) ? $this->data['filter'] : [];
                    $filter_sql       = [];
                    $filter_sql_count = [];
                    if (is_array($filter) && ! empty($filter)) {
                        // Accepted filters.
                        $accepted_filters = array_flip(['user_login', 'user_email']);
                        $filter           = array_intersect_key($filter, $accepted_filters);
                        foreach ($filter as $k => $v) {
                            $filter_sql[] = $k;
                            $filter_sql[] = $v;
                        }
                        // Create dummy array for $wpdb->prepare.
                        $filter_sql_count = range(1, count($filter_sql) / 2);
                    }

                    if (! $filter_sql_count) {
                        $filter_sql_count = [];
                    }

                    $this->prepare_found_rows_stuff($__limit__, $__found_rows__);

                    // Create a separate query if Multisite is enabled.
                    if (is_multisite()) {
                        $meta_key = $wpdb->get_blog_prefix(get_current_blog_id()) . 'capabilities';

						// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, ( $__found_rows__, $filter_sql, $__limit__ )
                        $result = $wpdb->get_results(
                            $wpdb->prepare(
                                "SELECT %0s `ID` AS `id`, `user_login`, `user_email`, CONCAT('/members/',`ID`) AS `_more_` FROM "
                                . $wpdb->users
                                . ', '
                                . $wpdb->usermeta
                                . " WHERE 1=1 AND {$wpdb->users}.ID = {$wpdb->usermeta}.user_id AND meta_key = %s"
                                . implode(' ', array_fill(0, count($filter_sql_count), 'AND `%0s`=%s'))
                                . ' %0s',
                                $__found_rows__,
                                $meta_key,
                                ...$filter_sql,
                                ...[$__limit__]
                            ),
                            ARRAY_A
                        );
                    } else {
                        $result = $wpdb->get_results(
                            $wpdb->prepare(
                                'SELECT %0s `ID` AS `id`, `user_login`, `user_email`, CONCAT("/members/",`ID`) AS `_more_` FROM '
                                . $wpdb->users
                                . ' WHERE 1=1 '
                                . implode(' ', array_fill(0, count($filter_sql_count), 'AND `%0s`=%s'))
                                . ' %0s',
                                $__found_rows__,
                                ...$filter_sql,
                                ...[$__limit__]
                            ),
                            ARRAY_A
                        );
                    }

                    $this->set_found_rows();
                    if (count($result)) {
                        return ['members' => ['member' => $result]];
                    }

                    /*
                     * get full user information if $member_id is specified
                     */
                } else {
                    $user = new \WishListMember\User($member_id, true);
                    if (isset($this->data['return_fields']) && is_array($this->data['return_fields'])) {
                        foreach ($this->data['return_fields'] as $fld) {
                            $user->user_info->$fld = $user->user_info->$fld;
                        }
                    }
                    $user->user_info = array_merge((array) $user->user_info, (array) $user->user_info->data);
                    unset($user->user_info['data']);
                    unset($user->user_info['user_pass']);
                    unset($user->user_info['wlm_reg_post']);
                    unset($user->user_info['wlm_reg_get']);
                    unset($user->WL);
                    $result = [(array) $user];
                    return ['member' => $result];
                }
                break;

            /*
             * create new user
             */
            case 'POST':
                $user_login = wlm_trim($data['user_login']);
                $user_email = wlm_trim($data['user_email']);
                $user_pass  = wlm_trim($data['user_pass'] ?? '');
                if (empty($user_login)) {
                    return $this->error('Empty username');
                }
                if (empty($user_email)) {
                    return $this->error('Empty email');
                }

                // If multisite then check if user is a member of the multisite, if not add them to it...
                if (is_multisite()) {
                    $blog_id = get_current_blog_id();

                    // Check if username or email exists.
                    if (username_exists($user_login) || email_exists($user_email)) {
                        // If it exists then get the userID and see if the user is already in the multisite, if not add the user ID.
                        // To the Multisite.
                        $user_data = get_user_by('login', $user_login);
                        if (! $user_data) {
                            $user_data = get_user_by('email', $user_email);
                        }

                        if (! is_user_member_of_blog($user_data->ID, $blog_id)) {
                            add_user_to_blog($blog_id, $user_data->ID, get_option('default_role'));

                            $member_id = $user_data->ID;
                            if ($member_id) {
                                unset($data['user_login']);
                                unset($data['user_email']);
                                unset($data['user_pass']);
                            }
                        }

                        if (username_exists($user_login)) {
                            return $this->error('Username already exists');
                        }
                        if (email_exists($user_email)) {
                            return $this->error('Email already exists');
                        }
                    } else { // If user doesn't exist in the network then add the user.
                        if (empty($user_pass)) {
                            $user_pass = wlm_generate_password(12, false);
                        }

                        $member_id = wlm_create_user($user_login, $user_pass, $user_email);

                        $WishListMemberInstance->is_sequential($member_id, true);

                        // Added to prevent WP from sending the password change email (since WP 4.3).
                        add_filter('send_password_change_email', '__return_false');
                        if (is_wp_error($member_id)) {
                            return $this->error('Cannot create user. ' . $member_id->get_error_message());
                        }
                        if ($member_id) {
                            unset($data['user_login']);
                            unset($data['user_email']);
                            unset($data['user_pass']);
                        }
                    }
                } else { // This Is for the Normal WordPress Installs (not Multisite).
                    if (username_exists($user_login)) {
                        return $this->error('Username already exists');
                    }
                    if (email_exists($user_email)) {
                        return $this->error('Email already exists');
                    }

                    if (empty($user_pass)) {
                        $pass_length = $WishListMemberInstance->get_option('min_passlength') + 0;
                        if (! $pass_length) {
                            $pass_length = 8;
                        }
                        $user_pass = wlm_generate_password($pass_length, false);
                    }

                    $member_id = wlm_create_user($user_login, $user_pass, $user_email);

                    $WishListMemberInstance->is_sequential($member_id, true);

                    // Added to prevent WP from sending the password change email (since WP 4.3).
                    add_filter('send_password_change_email', '__return_false');
                    if (is_wp_error($member_id)) {
                        return $this->error('Cannot create user. ' . $member_id->get_error_message());
                    }
                    if ($member_id) {
                        unset($data['user_login']);
                        unset($data['user_email']);
                        unset($data['user_pass']);
                    }
                }

                /*
                 * we now pass control to PUT to handle the rest of the data
                 * Update existing user
                 */
            case 'PUT':
                $uid = $wpdb->get_var($wpdb->prepare("SELECT `ID` FROM `{$wpdb->users}` WHERE `ID`=%d", $member_id));
                if (empty($uid)) {
                    return $this->error(self::ERROR_INVALID_RESOURCE);
                }
                $data       = $this->data;
                $data['ID'] = (int) $member_id;
                if (! user_can($member_id, 'administrator') || 'POST' === $this->method) {
                    // Set user role from $nlevels.
                    if (is_array($nlevels) && ! empty($nlevels)) {
                        $new_levels = array_intersect_key($this->wpm_levels, array_flip($nlevels));
                        $levelOrder = [];
                        foreach ($new_levels as $key => $val) {
                            $levelOrder[ $key ] = $val['levelOrder'] + 0;
                        }
                        $new_level_keys = array_keys($new_levels);
                        array_multisort($levelOrder, SORT_ASC, $new_level_keys, SORT_ASC, $new_levels);
                        $level = array_pop($new_levels);
                        if ($level['role']) {
                            $data['role'] = $level['role'];
                        }
                    }
                }
                if (! empty($data['new_user_login'])) {
                    if (username_exists($data['new_user_login'])) {
                        return $this->error(sprintf('Cannot change username to %s because it already exists', $data['new_user_login']));
                    }

                    $where  = [
                        'user_login' => $data['user_login'],
                        'ID'         => $data['ID'],
                    ];
                    $update = ['user_login' => $data['new_user_login']];
                    $status = $wpdb->update($wpdb->users, $update, $where);
                    if (false === $status) {
                        return $this->error(sprintf('An error occured while trying to change the username to %s', $data['new_user_login']));
                    }
                }

                // Attempt to auto-create display_name from a combination of first and last name.
                if (! isset($data['display_name'])) {
                    $data['display_name'] = wlm_trim(wlm_arrval($data, 'first_name') . ' ' . wlm_arrval($data, 'last_name'));
                    if (empty($data['display_name'])) {
                        unset($data['display_name']);
                    }
                }

                if ('POST' === $this->method) {
                    // Auto-create nickname if not provided.
                    $nickname = wlm_trim(wlm_arrval($data, 'nickname'));
                    if (! $nickname) {
                        // Get nickname from first_name, display_name or user_login in that order.
                        // Also strip @ and everything that from it.
                        $nickname = preg_replace('/@.*$/', '', wlm_arrval($data, 'first_name') ? wlm_arrval('lastresult') : ( wlm_arrval($data, 'display_name') ? wlm_arrval('lastresult') : wlm_arrval($data, 'user_login') ));
                    }

                    // Check if nickname is unique and generate a unique one (by appending an incremental numnber) if it's not.
                    $nicknames = $wpdb->get_col(
                        $wpdb->prepare(
                            "SELECT `meta_value` FROM `{$wpdb->usermeta}` WHERE `user_id` <> %d AND `meta_key`='nickname' AND `meta_value` REGEXP %s",
                            $data['ID'],
                            sprintf('^%s[0-9]*$', $nickname)
                        )
                    );

                    $ctr = '';
                    while (in_array($nickname . $ctr, $nicknames, true)) {
                        $ctr = (int) $ctr + 1;
                    }
                    $data['nickname'] = $nickname .= $ctr;

                    // Set first_name to nickname if first_name is not provided, this prevents the first_name from displaying the username.
                    if (empty(wlm_arrval($data, 'first_name'))) {
                        $data['first_name'] = $data['nickname'];
                    }

                    // Set display_name to first_name if display_name is not provided, this prevents the display_name from displaying the username.
                    if (empty(wlm_arrval($data, 'display_name'))) {
                        $data['display_name'] = $data['first_name'];
                    }
                }

                $member_id = wp_update_user($data);
                if (is_wp_error($member_id)) {
                    return $this->error($member_id->get_error_message());
                }
                $wpm_useraddress = $WishListMemberInstance->Get_UserMeta($member_id, 'wpm_useraddress');
                foreach ($data as $meta => $value) {
                    if ('custom_' === substr($meta, 0, 7) || 'wpm_login_limit' === $meta || 'wpm_registration_ip' === $meta || 'stripe_cust_id' === $meta) {
                        $WishListMemberInstance->Update_UserMeta($member_id, $meta, $value);
                    }
                    if (in_array($meta, ['company', 'address1', 'address2', 'city', 'state', 'zip', 'country'])) {
                        // If empty $wpm_useraddress, initialize it as an array.
                        if (empty($wpm_useraddress)) {
                            $wpm_useraddress = [];
                        }
                        $wpm_useraddress[ $meta ] = $value;
                    }
                }
                $WishListMemberInstance->Update_UserMeta($member_id, 'wpm_useraddress', $wpm_useraddress);
                if (isset($sequential)) {
                    $WishListMemberInstance->is_sequential($member_id, (bool) $sequential);
                }
                if (! empty($nlevels) || ! empty($rlevels)) {
                    $clevels = array_merge(
                        $WishListMemberInstance->get_membership_levels($member_id),
                        array_map(
                            function ($id) {
                                return 'payperpost-' . $id;
                            },
                            wishlistmember_instance()->get_user_pay_per_post($member_id, false, null, true)
                        )
                    );
                    $clevels = array_diff($clevels, (array) $rlevels);
                    $clevels = array_unique(array_merge($clevels, (array) $nlevels));

                    $WishListMemberInstance->set_membership_levels(
                        $member_id,
                        $clevels,
                        [
                            'set_timestamp'             => false,
                            'set_transaction_id'        => false,
                            'keep_existing_payperposts' => false,
                        ]
                    );

                    $WishListMemberInstance->set_membership_level_txn_ids($member_id, $txns);
                    $WishListMemberInstance->user_level_timestamps($member_id, $times);
                }
                $all_levels     = [];
                $new_levels     = [];
                $confirm_levels = [];
                $pending_levels = [];
                if (isset($this->data['ObeyRegistrationRequirements']) && $this->data['ObeyRegistrationRequirements']) {
                    foreach ($nlevels as $key => $_nlevel) {
                        if (! isset($this->wpm_levels[ $_nlevel ])) {
                            continue;
                        }
                        $_lname                 = wlm_trim($this->wpm_levels[ $_nlevel ]['name']);
                        $nlevels[ $key ]        = $_lname;
                        $new_levels[ $_nlevel ] = $_lname;
                        $all_levels[ $_nlevel ] = $_lname;
                        if (isset($this->wpm_levels[ $_nlevel ]['requireemailconfirmation']) && $this->wpm_levels[ $_nlevel ]['requireemailconfirmation']) {
                            $WishListMemberInstance->level_unconfirmed($_nlevel, $member_id, true);

                            $email_confirmation_reminder = [
                                'count'    => 0,
                                'lastsend' => time(),
                                'wpm_id'   => $_nlevel,
                            ];
                            add_user_meta($member_id, 'wlm_email_confirmation_reminder', $email_confirmation_reminder);

                            $confirm_levels[ $_nlevel ] = $_lname;
                            unset($nlevels[ $key ]);
                        }
                        if (isset($this->wpm_levels[ $_nlevel ]['requireadminapproval']) && $this->wpm_levels[ $_nlevel ]['requireadminapproval']) {
                            $WishListMemberInstance->level_for_approval($_nlevel, $member_id, true);
                            $pending_levels[ $_nlevel ] = $_lname;
                            unset($nlevels[ $key ]);
                        }
                    }
                } else { // Just set the level names or post title if it's a PPP.
                    foreach ($nlevels as $key => $_nlevel) {
                        // Iff PPP then get the post_title.
                        $is_level_ppp = $WishListMemberInstance->is_ppp_level($_nlevel);
                        if ($is_level_ppp) {
                            $_lname                 = wlm_trim($is_level_ppp->post_title);
                            $nlevels[ $key ]        = $_lname;
                            $new_levels[ $_nlevel ] = $_lname;
                            $all_levels[ $_nlevel ] = $_lname;
                            continue;
                        }

                        if (! isset($this->wpm_levels[ $_nlevel ])) {
                            continue;
                        }
                        $_lname                 = wlm_trim($this->wpm_levels[ $_nlevel ]['name']);
                        $nlevels[ $key ]        = $_lname;
                        $new_levels[ $_nlevel ] = $_lname;
                        $all_levels[ $_nlevel ] = $_lname;
                    }
                }

                // Send registration emails.
                $this->send_emails(
                    [
                        'member_id' => $member_id ?? 0,
                        'user_pass' => $user_pass ?? '',
                    ],
                    $new_levels,
                    $confirm_levels,
                    $pending_levels
                );

                // Trigger sequential upgrade.
                $WishListMemberInstance->do_sequential_for_user($member_id);

                $this->method = 'GET';
                return $this->_members($data['ID']);

            /*
             * Delete existing user except for #1 admin
             */
            case 'DELETE':
                if (1 === (int) $member_id) {
                    return $this->error(self::ERROR_INVALID_RESOURCE);
                }
                if (! function_exists('wp_delete_user')) {
                    require_once ABSPATH . '/wp-admin/includes/user.php';
                }
                wp_delete_user($member_id);
                return;
        }
    }

    /**
     * Send level registration transactional emails
     *
     * @param array $member_data    {
     *     Associative array of member information.
     *
     * @type  int     $member_id  The Member ID
     * @type  string  user_pass   Password. Default empty.
     * }
     * @param array $new_levels     Associative array of new levels with level id as key and level name as value.
     * @param array $confirm_levels Associative array of levels for confirmation with level id as key and level name as value.
     * @param array $pending_levels Associative array of levels for approval with level id as key and level name as value.
     */
    private function send_emails($member_data, $new_levels = [], $confirm_levels = [], $pending_levels = [])
    {
        extract(
            array_merge(
                [
                    'member_id' => 0,
                    'user_pass' => '',
                ],
                $member_data
            ),
            EXTR_SKIP
        );

        $all_levels = array_merge($new_levels, $confirm_levels, $pending_levels);

        if (
            ( isset($this->data['SendMail']) && $this->data['SendMail'] ) ||
            ( isset($this->data['SendMailPerLevel']) && $this->data['SendMailPerLevel'] )
        ) {
            $email_macros = [
                '[password]' => $user_pass ? $user_pass : '********',
            ];

            // The welcome email.
            if (! empty($new_levels)) {
                if ($this->data['SendMailPerLevel']) {
                    foreach ($new_levels as $lkey => $email_macros['[memberlevel]']) {
                        // Skip sending welcome email if email confirmation is required.
                        if (isset($confirm_levels[ $lkey ])) {
                            continue;
                        }
                        // Skip sending of per level email if SendMailPerLevel is an array and the current level is not in the array.
                        if (is_array($this->data['SendMailPerLevel']) && ! in_array($lkey, $this->data['SendMailPerLevel'])) {
                            continue;
                        }

                        wishlistmember_instance()->email_template_level = $lkey;
                        wishlistmember_instance()->send_email_template('registration', $member_id, $email_macros);
                    }
                } else {
                    $email_macros['[memberlevel]'] = implode(', ', array_unique($new_levels));
                    wishlistmember_instance()->send_email_template('registration', $member_id, $email_macros, null, null, true);
                }
            }

            // Email confirmation email.
            if (! empty($confirm_levels)) {
                if ($this->data['SendMailPerLevel']) {
                    $user = get_userdata($member_id);
                    foreach ($confirm_levels as $lkey => $email_macros['[memberlevel]']) {
                        // Skip sending of per level email if SendMailPerLevel is an array and the current level is not in the array.
                        if (is_array($this->data['SendMailPerLevel']) && ! in_array($lkey, $this->data['SendMailPerLevel'])) {
                            continue;
                        }

                        $email_macros['[confirmurl]'] = get_bloginfo('url') . '/index.php?wlmconfirm=' . $member_id . '/' . md5($user->user_email . '__' . $user->user_login . '__' . $lkey . '__' . wishlistmember_instance()->GetAPIKey());

                        wishlistmember_instance()->email_template_level = $lkey;
                        wishlistmember_instance()->send_email_template('email_confirmation', $member_id, $email_macros);
                    }
                } else {
                    $email_macros['[memberlevel]'] = implode(', ', array_unique($confirm_levels));
                    wishlistmember_instance()->send_email_template('email_confirmation', $member_id, $email_macros, null, null, true);
                }
            }

            // The Pending email.
            if (! empty($pending_levels)) {
                $pending_levels = array_unique($pending_levels);
                // Split paid and free levels so we can determine what emails to send.
                $free = [];
                $paid = [];
                foreach ($pending_levels as $pl => $plname) {
                    if (isset($txns[ $pl ])) {
                        $paid[ $pl ] = $plname;
                    } else {
                        $free[ $pl ] = $plname;
                    }
                }

                // Pending email for free registration.
                if ($free) {
                    if ($this->data['SendMailPerLevel']) {
                        foreach ($confirm_levels as $lkey => $email_macros['[memberlevel]']) {
                            // Skip sending of per level email if SendMailPerLevel is an array and the current level is not in the array.
                            if (is_array($this->data['SendMailPerLevel']) && ! in_array($lkey, $this->data['SendMailPerLevel'])) {
                                continue;
                            }

                            wishlistmember_instance()->email_template_level = $lkey;
                            wishlistmember_instance()->send_email_template('require_admin_approval', $member_id, $email_macros);
                        }
                    } else {
                        $email_macros['[memberlevel]'] = implode(', ', $free);
                        wishlistmember_instance()->send_email_template('require_admin_approval', $member_id, $email_macros, null, null, true);
                    }
                }

                // Pending email for paid registration.
                if ($paid) {
                    if ($this->data['SendMailPerLevel']) {
                        foreach ($paid as $lkey => $email_macros['[memberlevel]']) {
                            // Skip sending of per level email if SendMailPerLevel is an array and the current level is not in the array.
                            if (is_array($this->data['SendMailPerLevel']) && ! in_array($lkey, $this->data['SendMailPerLevel'])) {
                                continue;
                            }

                            wishlistmember_instance()->email_template_level = $lkey;
                            wishlistmember_instance()->send_email_template('require_admin_approval_paid_admin', $member_id, $email_macros, wishlistmember_instance()->get_option('email_sender_address'));
                        }
                    } else {
                        $email_macros['[memberlevel]'] = implode(', ', $paid);
                        wishlistmember_instance()->send_email_template('require_admin_approval_paid_admin', $member_id, $email_macros, wishlistmember_instance()->get_option('email_sender_address'), null, true);
                    }
                }
            }

            // New member email notification for admin.
            $email_macros['[password]'] = '********';

            if (! empty($new_levels)) {
                if ($this->data['SendMailPerLevel']) {
                    foreach ($new_levels as $lkey => $email_macros['[memberlevel]']) {
                        // Skip sending of per level email if SendMailPerLevel is an array and the current level is not in the array.
                        if (is_array($this->data['SendMailPerLevel']) && ! in_array($lkey, $this->data['SendMailPerLevel'])) {
                            continue;
                        }

                        wishlistmember_instance()->email_template_level = $lkey;
                        wishlistmember_instance()->send_email_template('admin_new_member_notice', $member_id, $email_macros, wishlistmember_instance()->get_option('email_sender_address'));
                    }
                } else {
                    $email_macros['[memberlevel]'] = implode(', ', array_unique($new_levels));
                    wishlistmember_instance()->send_email_template('admin_new_member_notice', $member_id, $email_macros, wishlistmember_instance()->get_option('email_sender_address'), null, true);
                }
            }
        }
    }

    /*
     * *********************************************** *
     * CONTENT METHODS
     * *********************************************** *
     */

    /**
     * Resource:
     *   /content : GET
     *   /content/{post_type} : GET
     *
     * @param  string $content_type Post Type.
     * @return apiResult
     */
    private function _content($content_type = null)
    {
        return $this->content($content_type);
    }

    /**
     * Resource:
     *   /content/{post_type}/{post_id} : GET, PUT
     *
     * @param  string  $content_type User ID.
     * @param  integer $content_id   Post ID.
     * @return apiResult
     */
    private function _content_protection($content_type, $content_id)
    {
        if (empty($content_id)) {
            return $this->error(self::ERROR_INVALID_RESOURCE);
        }
        return $this->content($content_type, $content_id);
    }

    /**
     * Called by _content and _content_protection
     *
     * @global type $wpdb
     * @global WishListMember $WishListMemberInstance
     * @param  type    $content_type The content type.
     * @param  integer $content_id   The content ID.
     * @return apiResult
     */
    public function content($content_type = null, $content_id = null)
    {
        global $wpdb;
        global $WishListMemberInstance;

        $this->selfdoc = is_null($content_id) ? ['GET'] : ['GET', 'PUT'];
        if ('INFO' === $this->method) {
            return $this->selfdoc;
        }
        if (! in_array($this->method, $this->selfdoc)) {
            return $this->error(self::ERROR_METHOD_NOT_SUPPORTED);
        }

        /*
         * get all custom post types
         * and also append posts and pages
         */
        $valid_content_types = array_values(get_post_types(['_builtin' => false]));
        array_unshift($valid_content_types, 'posts', 'pages', 'post', 'page');

        /*
         * no content type specified?
         * let's give all possible content types
         */
        if (empty($content_type)) {
            foreach ($valid_content_types as &$c) {
                $c = [
                    'name'   => $c,
                    '_more_' => sprintf('/content/%s', $c),
                ];
            }
            unset($c);
            return ['content' => ['type' => $valid_content_types]];
        }

        /*
         * this section only runs if content type is specified
         */
        if (! in_array($content_type, $valid_content_types)) {
            // Abort for invalid content types.
            return $this->error(self::ERROR_INVALID_RESOURCE);
        }
        $ct = $content_type;
        if ('posts' === strtolower($content_type)) {
            $ct = 'post';
        }
        if ('pages' === strtolower($content_type)) {
            $ct = 'page';
        }

        /*
         * by this point, we're sure that the content type is valid
         * no content id specified?
         * let's return all posts for the specified content type
         */

        $content_id += 0;
        if (empty($content_id)) {
            $this->prepare_found_rows_stuff($__limit__, $__found_rows__);
            $content = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT %0s `ID`,`post_title` AS `name`, CONCAT('/content/%0s','/protection/',`ID`) AS `_more_` FROM `{$wpdb->posts}` WHERE `post_type`=%s AND `post_status`='publish' ORDER BY `post_date` DESC %0s",
                    $__found_rows__,
                    $ct,
                    $ct,
                    $__limit__
                ),
                ARRAY_A
            );
            $this->set_found_rows();
            return ['content' => [$content_type => $content]];
        }

        /*
         * if we get to this point then we know that a non-empty content id was passed
         */
        $content_id += 0;
        $content     = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT `ID`,`post_title` AS `name` FROM `{$wpdb->posts}` WHERE `post_type`=%s AND `post_status`='publish' AND `ID`=%d ORDER BY `post_date` DESC",
                $ct,
                $content_id
            ),
            ARRAY_A
        );
        if (! $content) {
            return $this->error(self::ERROR_INVALID_RESOURCE);
        }
        $protection = $WishListMemberInstance->get_content_levels($ct, $content_id);

        // Let's also include levels that has allposts/allpages enabled.
        $wpm_levels = (array) $WishListMemberInstance->get_option('wpm_levels');
        foreach ($wpm_levels as $level_id => $level) {
            if ('page' === $ct) {
                if ($level['allpages']) {
                    if (! in_array($level_id, $protection)) {
                        $protection[] = $level_id;
                    }
                }
            }
            if ('post' === $ct) {
                if ($level['allposts']) {
                    if (! in_array($level_id, $protection)) {
                        $protection[] = $level_id;
                    }
                }
            }
        }

        $_Protected       = (int) in_array('Protection', $protection);
        $_PayPerPost      = (int) in_array('PayPerPost', $protection);
        $protection       = array_diff($protection, ['Protection', 'PayPerPost']);
        $_Levels          = preg_grep('/^\d+$/', $protection);
        $_PayPerPostUsers = preg_grep('/^U-\d+$/', $protection);
        if ('PUT' === $this->method) {
            $data = $this->data;
            if (isset($data['Protected'])) {
                $WishListMemberInstance->protect($content_id, $data['Protected'] + 0 ? 'Y' : 'N');
            }
            if (isset($data['PayPerPost'])) {
                $WishListMemberInstance->pay_per_post($content_id, $data['PayPerPost'] + 0 ? 'Y' : 'N');
            }
            $setlevels = false;
            $levels    = $_Levels;
            if (isset($data['Levels'])) {
                $levels    = array_unique(array_merge($levels, preg_grep('/^\d+$/', (array) $data['Levels'])));
                $setlevels = true;
            }
            if (isset($data['RemoveLevels'])) {
                $levels    = array_diff($levels, (array) $data['RemoveLevels']);
                $setlevels = true;
            }
            if ($setlevels) {
                $WishListMemberInstance->set_content_levels($ct, $content_id, $levels);
            }
            $n_ppp_users = [];
            if (isset($data['PayPerPostUsers'])) {
                $n_ppp_users = (array) $data['PayPerPostUsers'];
            }
            $r_ppp_users = [];
            if (isset($data['RemovePayPerPostUsers'])) {
                $r_ppp_users = (array) $data['RemovePayPerPostUsers'];
            }
            $n_ppp_users = array_diff($n_ppp_users, $r_ppp_users);
            if (count($n_ppp_users)) {
                $WishListMemberInstance->add_post_users($ct, $content_id, $n_ppp_users);
            }
            if (count($r_ppp_users)) {
                $WishListMemberInstance->remove_post_users($ct, $content_id, $r_ppp_users);
            }
            $this->method = 'GET';
            return $this->content($content_type, $content_id);
        }
        $content['Protected']       = $_Protected;
        $content['Levels']          = array_values($_Levels);
        $content['PayPerPost']      = $_PayPerPost;
        $content['PayPerPostUsers'] = array_values($_PayPerPostUsers);
        return ['content' => [$content_type => [$content]]];
    }

    /**
     * Resource:
     *   /taxonomies : GET
     *   /taxonomies/{taxononmy} : GET
     *
     * @param  string $taxonomy Taxonomy.
     * @return apiResult
     */
    private function _taxonomies($taxonomy = null)
    {
        return $this->categories($taxonomy);
    }

    /**
     * Resource:
     *   /categories : GET, PUT
     *
     * @param  string $taxonomy User ID.
     * @return apiResult
     */
    private function _categories($taxonomy = null)
    {
        return $this->categories($taxonomy);
    }

    /**
     * Resource:
     *   /taxonomies/{taxonomy}/{taxonomy_id} : GET, PUT
     *
     * @param  string  $taxonomy    User ID.
     * @param  integer $taxonomy_id Post ID.
     * @return apiResult
     */
    private function _taxonomies_protection($taxonomy, $taxonomy_id)
    {
        if (empty($taxonomy_id)) {
            return $this->error(self::ERROR_INVALID_RESOURCE);
        }
        return $this->categories($taxonomy, $taxonomy_id);
    }

    /**
     * Called by _categories and _categories_protection
     *
     * @global type $wpdb
     * @global WishListMember $WishListMemberInstance
     * @param  type    $taxonomy    The taxonomy.
     * @param  integer $taxonomy_id The taxonomy ID.
     * @return apiResult
     */
    public function categories($taxonomy = null, $taxonomy_id = null)
    {
        global $wpdb;
        global $WishListMemberInstance;

        $this->selfdoc = is_null($taxonomy_id) ? ['GET'] : ['GET', 'PUT'];
        if ('INFO' === $this->method) {
            return $this->selfdoc;
        }
        if (! in_array($this->method, (array) $this->selfdoc)) {
            return $this->error(self::ERROR_METHOD_NOT_SUPPORTED);
        }

        /*
         * get all custom taxonomies
         * and also append category
         */
        $valid_taxonomies = array_values(get_taxonomies(['_builtin' => false]));
        array_unshift($valid_taxonomies, 'category');

        /*
         * no taxonomy specified?
         * let's give all possible taxonomies
         */
        if (empty($taxonomy)) {
            foreach ($valid_taxonomies as &$c) {
                $c = [
                    'name'   => $c,
                    '_more_' => sprintf('/taxonomies/%s', $c),
                ];
            }
            unset($c);
            return ['content' => ['type' => $valid_taxonomies]];
        }

        /*
         * this section only runs if taxonomy is specified
         */
        if (! in_array($taxonomy, $valid_taxonomies)) {
            // Abort for invalid content types.
            return $this->error(self::ERROR_INVALID_RESOURCE);
        }

        /*
         * by this point, we're sure that the taxonomy is valid
         * no taxonomy id specified?
         * let's return all categories (terms) for the specified taxonomy
         */
        if (empty($taxonomy_id)) {
            $content = get_terms($taxonomy, ['hide_empty' => false]);
            if (is_wp_error($content)) {
                $content = [];
            }
            foreach ($content as &$c) {
                $c = (array) $c;
                $c = [
                    'ID'     => $c['term_id'],
                    'name'   => $c['name'],
                    '_more_' => sprintf('/taxonomies/%s/%d', $taxonomy, $c['term_id']),
                ];
            }
            unset($c);
            return ['content' => [$taxonomy => $content]];
        }

        /*
         * if we get to this point then we know that a non-empty taxonomy id was passed
         */
        $taxonomy_id += 0;
        $content      = get_term($taxonomy_id, $taxonomy);
        if (! $content) {
            return $this->error(self::ERROR_INVALID_RESOURCE);
        }
        $content    = (array) $content;
        $protection = $WishListMemberInstance->get_content_levels('categories', $taxonomy_id);
        $_Protected = (int) in_array('Protection', $protection);
        $protection = array_diff($protection, ['Protection', 'PayPerPost']);
        $_Levels    = preg_grep('/^\d+$/', $protection);
        if ('PUT' === $this->method) {
            $data = $this->data;
            if (isset($data['Protected'])) {
                $WishListMemberInstance->cat_protected($taxonomy_id, $data['Protected'] + 0 ? 'Y' : 'N');
            }
            $setlevels = false;
            $levels    = $_Levels;
            if (isset($data['Levels'])) {
                $levels    = array_unique(array_merge($levels, preg_grep('/^\d+$/', (array) $data['Levels'])));
                $setlevels = true;
            }
            if (isset($data['RemoveLevels'])) {
                $levels    = array_diff($levels, (array) $data['RemoveLevels']);
                $setlevels = true;
            }
            if ($setlevels) {
                $WishListMemberInstance->set_content_levels('categories', $taxonomy_id, $levels);
            }
            $this->method = 'GET';
            return $this->categories($taxonomy, $taxonomy_id);
        }
        $content['Protected'] = $_Protected;
        $content['Levels']    = array_values($_Levels);
        return ['content' => [$taxonomy => [$content]]];
    }

    /**
     * For API v1.0
     *
     * @param  string $api1function The function.
     * @return apiResult
     */
    private function _api1($api1function)
    {
        if (! class_exists('WLMAPI')) {
            return $this->error(self::ERROR_INVALID_RESOURCE);
        }
        $this->selfdoc = ['GET'];
        if ('INFO' === $this->method) {
            return $this->selfdoc;
        }
        if ('GET' === $this->method) {
            if (method_exists(WLMAPI, $api1function) && '_' != substr($api1function, 0, 1)) {
                $data   = (array) $this->data['Params'];
                $output = call_user_func_array([WLMAPI, $api1function], $data);
                return ['Result' => $output];
            } else {
                return $this->error(self::ERROR_INVALID_RESOURCE);
            }
        } else {
            return $this->error(self::ERROR_METHOD_NOT_SUPPORTED);
        }
    }
}
