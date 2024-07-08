<?php

/**
 * User_Search class file
 *
 * @package WishListMember
 */

namespace WishListMember;

defined('ABSPATH') || die();

/**
 * User_Search class
 * Extends \WP_User_Query to allow for better member (a.k.a. user) search
 */
class User_Search extends \WP_User_Query
{
    /**
     * Additional filters
     *
     * @var array
     */
    public $additional_filters;

    /**
     * Sort Order
     *
     * @var string
     */
    public $sort_order;

    /**
     * Search Term
     *
     * @var string
     */
    public $search_term;

    /**
     * Users Per Page
     *
     * @var integer
     */
    public $users_per_page;

    /**
     * Total Users For Query
     *
     * @var integer
     */
    public $total_users_for_query;

    /**
     * Whether we're filtering by membership level or not
     *
     * @var boolean
     */
    private $filtering_by_membership_level = false;

    /**
     * Our own copy of the search term.
     *
     * @var string
     */
    public $wlm_search_term = '';

    /**
     * Constructor
     *
     * @param string|array $search_term  Search term or array of parameters. If array then all the other parameters are ignored.
     * @param string       $page         Page.
     * @param string       $role         Role.
     * @param string       $ids          Unused.
     * @param string       $sortby       Sort by.
     * @param string       $sortorder    Sort order.
     * @param integer      $howmany      Limit.
     * @param array        $more_filters Additional filters.
     */
    public function __construct($search_term = '', $page = '', $role = '', $ids = 'no longer used', $sortby = '', $sortorder = '', $howmany = 15, $more_filters = [])
    {

        $additional_param_defaults = [
            'exclude'      => [],
            'include'      => [],
            'meta_key'     => '',
            'meta_value'   => '',
            'meta_compare' => '',
            'meta_query'   => [],
        ];

        if (is_array($search_term)) {
            $args = wp_parse_args(
                $search_term,
                [
                    'search_term'  => '',
                    'page'         => '',
                    'role'         => '',
                    'ids'          => 'no longer used',
                    'sortby'       => '',
                    'sortorder'    => '',
                    'howmany'      => 15,
                    'more_filters' => [],
                ] + $additional_param_defaults
            );
            extract($args);
        } else {
            extract($additional_param_defaults);
        }

        if (empty($page)) {
            $page = 1;
        }

        $query = [
            'offset'  => ( $page - 1 ) * $howmany,
            'role'    => $role,
            'number'  => $howmany,
            'orderby' => $sortby,
            'order'   => $sortorder,
            'fields'  => 'ID',
        ];
        // Add search term if not empty.
        if ('' !== wlm_trim($search_term)) {
            $query['search']         = '*' . wlm_trim($search_term) . '*';
            $query['search_columns'] = ['user_login', 'user_nicename', 'user_email'];
        }
        if (is_array($exclude) && $exclude) {
            $query['exclude'] = array_map('intval', $exclude);
        }
        if (is_array($exclude) && $exclude) {
            $query['include'] = array_map('intval', $include);
        }

        if (! empty($meta_key)) {
            $query['meta_key'] = (string) $meta_key;
        }
        if (! empty($meta_value)) {
            $query['meta_value'] = (string) $meta_value;
        }
        if (! empty($meta_compare)) {
            $query['meta_compare'] = (string) $meta_compare;
        }
        if (! empty($meta_query)) {
            $query['meta_query'] = $meta_query;
        }

        $this->sort_order      = $sortorder ? $sortorder : 'ASC';
        $this->search_term     = $search_term;
        $this->wlm_search_term = $search_term;
        $this->users_per_page  = $howmany;
        $this->total_users     = $this->total_users_for_query;

        $this->additional_filters = $more_filters;
        parent::__construct($query);
    }

    /**
     * Filter to add display name in the search
     *
     * @param  array $cols Columns.
     * @return array.
     */
    public function user_search_columns_filter($cols)
    {
        if (! in_array('display_name', $cols, true)) {
            $cols[] = 'display_name';
        }
        return $cols;
    }


    /**
     * Our own prepare_query
     * first, we call the original one
     * then do our own stuff for
     * levels, statuses, etc.
     *
     * @param array $query Query data.
     */
    public function prepare_query($query = [])
    {
        global $wpdb;

        $this->additional_filters['level']  = wlm_trim(wlm_arrval($this->additional_filters, 'level'));
        $this->additional_filters['status'] = isset($this->additional_filters['status']) ? wlm_trim($this->additional_filters['status']) : false;

        $this->filtering_by_membership_level = ! empty($this->additional_filters['level']) && ! in_array($this->additional_filters['level'], ['incomplete', 'nonmembers', 'members'], true);

        // Add display_name to search columns.
        add_filter('user_search_columns', [$this, 'user_search_columns_filter'], 10);
        parent::prepare_query($query);

        $search_sql = [];
        $in_queries = [];

        /**
         * Filters By Transaction ID
         */
        $transactionid = isset($this->additional_filters['transactionid']) ? $this->additional_filters['transactionid'] : false;
        if ($transactionid) {
            $search_sql[] = $wpdb->prepare('( ulo.option_name=%s AND ulo.option_value LIKE %s )', 'transaction_id', '%' . $wpdb->esc_like($transactionid) . '%');
        }

        /**
         * Filters By User Address
        */
        $useraddress = isset($this->additional_filters['useraddress']) ? $this->additional_filters['useraddress'] : false;
        if ($useraddress) {
            $record_count = $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM ' . wishlistmember_instance()->table_names->user_options . ' WHERE option_name=%s AND option_value LIKE %s', 'wpm_useraddress', '%' . $wpdb->esc_like($useraddress) . '%'));
            if ((int) $record_count > 0) { // We only add the query if there are records to be found.
                $in_queries[] = $wpdb->prepare('SELECT user_id FROM ' . wishlistmember_instance()->table_names->user_options . ' WHERE option_name=%s AND option_value LIKE %s', 'wpm_useraddress', '%' . $wpdb->esc_like($useraddress) . '%');
            }
        }

        /**
         * Filters By Membership Level
         * incomplete/nonmembers has special handling
         */
        $level = $this->additional_filters['level'];
        if (! empty($level)) {
            if ('incomplete' === $level) {
                $search_sql[] = "$wpdb->users.user_login REGEXP '^temp_[a-f0-9]{32}'";
                $search_sql[] = "$wpdb->users.user_login = $wpdb->users.user_email";
            } elseif ('nonmembers' === $level) {
                $search_sql[]                       = '(ul.level_id  IS NULL)';
                $this->additional_filters['status'] = '';
            } elseif ('members' === $level) {
                /**
 * For WLM 3.0 Members are users with at least 1 membership level regardless of the status
*/
                $mlevels        = \WishListMember\Level::get_all_levels();
                $levels_implode = "'" . implode("','", $mlevels) . "'";
                $user_query     = new \WP_User_Query(
                    [
                        'fields'      => 'ID',
                        'count_total' => false,
                        'blog_id'     => $GLOBALS['blog_id'],
                    ]
                );
                $query          = 'SELECT DISTINCT `user_id` FROM `' . wishlistmember_instance()->table_names->userlevels . "` WHERE `level_id` IN ($levels_implode) AND `user_id` IN ({$user_query->request}) ORDER BY `user_id`";
                $search_sql[]   = " ( $wpdb->users.ID IN  (" . $query . ') ) ';
                $search_sql[]   = " ( $wpdb->users.ID NOT IN  (0) ) ";
            } else {
                $search_sql[] = $wpdb->prepare('ul.level_id=%s', $this->additional_filters['level']);
            }
        }

        /**
 * Filters By Sequential Status
*/
        $sequential_filter = isset($this->additional_filters['sequential']) ? $this->additional_filters['sequential'] : false;
        if ($sequential_filter) {
            $filter       = 'on' === $sequential_filter ? 1 : 0;
            $record_count = $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM ' . wishlistmember_instance()->table_names->user_options . ' WHERE option_name=%s AND option_value=%d', 'sequential', $filter));
            if ((int) $record_count > 0) { // We only add the query if there are records to be found.
                $in_queries[] = $wpdb->prepare('SELECT user_id FROM ' . wishlistmember_instance()->table_names->user_options . ' WHERE option_name=%s AND option_value=%d', 'sequential', $filter);
            }
        }

        /**
         * Filters By Status
         * Note that expired members are handled differently
         */
        $status = isset($this->additional_filters['status']) ? $this->additional_filters['status'] : false;
        if ($status) {
            $expired_sql = [];
            $inactive_sql = [];

            // Expired members are specially handled.
            $ids = [];

            if (isset($level) && ! in_array($level, ['nonmembers', 'incomplete', 'members'], true)) {
                $expiredmembers = wishlistmember_instance()->expired_members_id(false, $level);
            } else {
                $expiredmembers = wishlistmember_instance()->expired_members_id();
            }

            // Flatten the result.
            $ids = $expiredmembers ? call_user_func_array('array_merge', array_values($expiredmembers)) : [];
            if (empty($ids)) {
                $ids = [-1];
            }
            $expired_sql[] = "$wpdb->users.ID IN (" . implode(',', $ids) . ')';

            $inactives = ['cancelled', 'unconfirmed', 'forapproval'];
            foreach ($inactives as $i) {
                $inactive_sql[] = $wpdb->prepare('( ulo.option_name=%s AND ulo.option_value=%d )', $i, 1);
            }

            switch ($status) {
                case 'active':
                    /**
 * For WLM 3.0 Active members are users with at least 1 active membership level
*/
                    if (! empty($level)) {
                        $activeids = wishlistmember_instance()->active_member_ids($level, false, false);
                    } else {
                        $activeids = wishlistmember_instance()->active_member_ids(null, false, false);
                    }

                    $activeids    = count($activeids) > 0 ? $activeids : [0];
                    $search_sql[] = " ( $wpdb->users.ID IN  (" . implode(',', $activeids) . ') ) ';
                    $search_sql[] = " ( $wpdb->users.ID NOT IN  (0) ) ";

                    break;
                case 'expired':
                    $search_sql = array_merge($search_sql, $expired_sql);
                    break;
                case 'inactive':
                    $or_sql       = array_merge($expired_sql, $inactive_sql);
                    $search_sql[] = '(' . implode(' OR ', $or_sql) . ')';
                    break;
                case 'scheduled':
                        $search_sql[] = "( ulo.option_name LIKE 'scheduled_%' )";
                    break;
                case 'cancelled':
                case 'unconfirmed':
                case 'forapproval':
                case 'sequential_cancelled':
                    if (! $transactionid) {
                        $search_sql[] = $wpdb->prepare('( ulo.option_name=%s AND ulo.option_value=%d )', $status, 1);
                    }
                    break;
            }
        }

        /**
         * Filter by Date Ranges
         * Again, due to expired being computed on the fly
         * it has to be handled in a specific way
         */
        $date_meta = ! empty($this->additional_filters['date_type']) ? $this->additional_filters['date_type'] : false;
        if ($date_meta) {
            // No real option rather than initiate a sub-query since dates are stored as strings.
            if ('expiration_date' === $date_meta) {
                $ids             = [];
                $expired_ts_from = strtotime($this->additional_filters['from_date']);
                $expired_ts_to   = strtotime($this->additional_filters['to_date']);
                if ($expired_ts_to <= 0) {
                    $expired_ts_to = time();
                }
                $expiredmembers = wishlistmember_instance()->expired_members_id();
                foreach ($expiredmembers as $level_id => $expired_per_level) {
                    foreach ($expired_per_level as $user_id) {
                        $expired_ts = wishlistmember_instance()->level_expire_date($level_id, $user_id);
                        if (( $expired_ts >= $expired_ts_from ) && ( $expired_ts <= $expired_ts_to )) {
                            $ids[] = $user_id;
                        }
                    }
                }
            } else {
                $level_filter = '';
                if (isset($level) && ! in_array($level, ['nonmembers', 'incomplete', 'members'], true)) {
                    $level_filter = $level;
                }

                $ids = wishlistmember_instance()->get_members_id_by_date_range($date_meta, $this->additional_filters['from_date'], $this->additional_filters['to_date'], $level_filter);
            }
            // Nothing found? force to return nothing.
            if (empty($ids)) {
                $ids = [-1];
            }
            $search_sql[] = "$wpdb->users.ID IN (" . implode(',', $ids) . ')';
        }

        if ($this->wlm_search_term) {
            $record_count = $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM ' . wishlistmember_instance()->table_names->user_options . ' AS uo LEFT JOIN  ' . $wpdb->users . ' as u ON uo.user_id=u.ID  WHERE ( uo.option_name=%s AND uo.option_value LIKE %s AND concat( "temp_", md5( uo.option_value ) ) IN ( u.user_email, u.user_login) )', 'wlm_origemail', '%' . $wpdb->esc_like($this->wlm_search_term) . '%'));
            if ((int) $record_count > 0) { // We only add the query if there are records to be found.
                $in_queries[] = $wpdb->prepare('SELECT user_id FROM ' . wishlistmember_instance()->table_names->user_options . ' AS uo LEFT JOIN  ' . $wpdb->users . ' as u ON uo.user_id=u.ID  WHERE ( uo.option_name=%s AND uo.option_value LIKE %s AND concat( "temp_", md5( uo.option_value ) ) IN ( u.user_email, u.user_login) )', 'wlm_origemail', '%' . $wpdb->esc_like($this->wlm_search_term) . '%');
            }
        }

        if (is_array($in_queries) && count($in_queries) > 0) {
            foreach ($in_queries as $in_query) {
                $search_sql[] = "{$wpdb->users}.ID IN ({$in_query})";
            }
        }

        if (! empty($search_sql)) {
            $search_sql         = implode(' AND ', $search_sql);
            $this->query_where .= " AND $search_sql";
        }

        $this->query_orderby = "GROUP BY $wpdb->users.ID $this->query_orderby";

        if ($this->filtering_by_membership_level) {
            if (! is_multisite()) {
                $this->query_from .= " LEFT JOIN $wpdb->usermeta on ($wpdb->users.ID=$wpdb->usermeta.user_id)";
            }
        }
        $this->query_from .= ''
        . ' LEFT JOIN ' . wishlistmember_instance()->table_names->userlevels . " AS ul on ($wpdb->users.ID=ul.user_id)"
        . ' LEFT JOIN ' . wishlistmember_instance()->table_names->userlevel_options . ' AS ulo on (ulo.userlevel_id=ul.ID)';
    }

    /**
     * Query function
     */
    public function query()
    {
        global $wpdb;

        if ($this->filtering_by_membership_level) {
            $this->query_vars['fields'] = [
                "DISTINCT($wpdb->users.ID)",
                'ulo.option_value',
                'ulo.option_name',
            ];

            $this->query_fields = "SQL_CALC_FOUND_ROWS DISTINCT($wpdb->users.ID), ulo.option_value, ulo.option_name";

            parent::query();

            $levels_data = [];

            // Loop through results and convert date to timestamp for easier sorting.
            foreach ($this->results as $data) {
                /*
                 * if there's no registration_date, it means the level is not active, all non active members will
                 * be at the bottom of the result, will be on top if sorted reveresed based on registration date
                 */
                if ('registration_date' === $data->option_name) {
                    $date      = explode('#', $data->option_value);
                    $timestamp = strtotime($date[0]);
                } else {
                    $timestamp = strtotime(time());
                }
                $levels_data[ $timestamp . '-' . $data->ID ] = $data->ID;
            }

            $this->results = $levels_data;
        } else {
            parent::query();
        }
    }
}
