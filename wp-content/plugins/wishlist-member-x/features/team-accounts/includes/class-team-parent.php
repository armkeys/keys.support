<?php

/**
 * Team Parent class file.
 *
 * @package WishListMember/Features/Team_Accounts
 */

namespace WishListMember\Features\Team_Accounts;

/**
 * Team_Parent class.
 */
class Team_Parent
{
    const STATUS_ACTIVE   = 0;
    const STATUS_INACTIVE = -1;

    /**
     * User ID.
     *
     * @var integer
     */
    private $user_id = 0;

    /**
     * Array of teams
     *
     * @var  array {
     * @type string  $transaction_id  Transaction ID.
     * @type string  $date            Date added.
     * @type integer $id              Team ID.
     * @type string  $name            Team name.
     * @type boolean $mirrored_access True if parent access is mirrored, false if not.
     * @type array   $levels          Specific levels to grant children access to if $mirrored_access is false.
     * @type array   $pay_per_posts   Specific pay per posts to grant children access to if $mirrored_access is false.
     * @type integer $quantity        Number of children allowed.
     * }
     */
    private $teams = [];

    /**
     * Multi-dimensional array of children IDs
     *
     * @var  array {
     * @type array $team_id {
     *       ...$children_ids
     *    }
     * }
     */
    private $children = [];

    /**
     * Multi-dimensional array of configured team levels with team ID as key and \WishListMember\User->Levels as values.
     *
     * @var  array {
     * @type array $team_id {
     *      ...filtered level data as returned by \WishListMember\User->Levels
     *   }
     * }
     */
    private $all_team_levels = [];

    /**
     * Active team level IDs per team.
     *
     * @var  array {
     * @type array $team_id {
     *     ...filtered active level IDs
     *   }
     * }
     */
    private $active_team_levels = [];

    /**
     * Pay per post IDs.
     *
     * @var array
     */
    private $pay_per_posts = [];

    /**
     * Constructor
     *
     * @param  integer $user_id User ID.
     * @return void
     */
    public function __construct($user_id)
    {
        global $wpdb;
        $this->user_id = (int) $user_id;
        if (! $this->user_id) {
            return;
        }
        $this->teams = (array) wlm_or(
            json_decode(wishlistmember_instance()->Get_UserMeta($this->user_id, 'team-accounts/teams'), true),
            []
        );

        $team_ids = array_keys(Team_Account::get_all(true));

        $processed_teams = [];
        foreach ($this->teams as $key => $team) {
            $children = $wpdb->get_col(
                $wpdb->prepare(
                    'SELECT DISTINCT `user_id` FROM `' . $wpdb->usermeta . '` WHERE `meta_key`=%s AND `meta_value`=%s',
                    'team-accounts/parent',
                    $this->user_id . '-' . $team['id']
                )
            );
            if (in_array($team['id'], $team_ids)) {
                if (in_array((int) $team['id'], $processed_teams, true)) {
                    continue;
                }
                $processed_teams[]             = (int) $team['id'];
                $this->children[ $team['id'] ] = $children;
            } else {
                unset($this->teams[ $key ]);
                $this->remove_team($team['id']);
                $this->remove_children($team['id'], $children);
            }
        }

        $this->refresh_team_levels();
    }

    public function teams_grouped()
    {
        $teams        = [];
        $max_children = [];
        foreach ($this->teams as $team) {
            if (! isset($max_children[ $team['id'] ])) {
                $max_children[ $team['id'] ] = $this->get_max_allowed_children($team['id']);
            }
            $team['max_children']   = $max_children[ $team['id'] ];
            $teams[ $team['id'] ][] = $team;
        }
        foreach ($teams as &$team) {
            $status = array_column($team, 'status');
            $date   = array_column($team, 'date');
            array_multisort($status, SORT_DESC, $date, SORT_DESC, $team);
        }
        unset($team);
        uasort(
            $teams,
            function ($a, $b) {
                if ($a[0]['date'] == $b[0]['date']) {
                    return 0;
                }
                return ( $a[0]['date'] < $b[0]['date'] ) ? 1 : -1;
            }
        );
        return $teams;
    }

    /**
     * Add team to parent.
     *
     * @param  integer $team_id        Team ID.
     * @param  string  $transaction_id Transaction ID.
     * @param  integer $quantity       Quantity (number or children) to add. If 0 then use Team Account's initial quantity.
     * @return boolean
     */
    public function add_team($team_id, $transaction_id, $quantity = 0)
    {
        $transaction_id = wlm_trim($transaction_id);
        $team_id        = (int) $team_id;
        $quantity       = (int) $quantity;
        if (! $transaction_id || ! $team_id) {
            return false;
        }
        $team_account = Team_Account::get($team_id);
        if (! $team_account) {
            return false;
        }
        $team = [
            'id'                  => $team_account->id,
            'name'                => $team_account->name,
            'transaction_id'      => $transaction_id,
            'status'              => 0,
            'date'                => gmdate('Y-m-d H:i:s'),
            'mirrored_access'     => $team_account->mirrored_access,
            'access_levels'       => $team_account->access_levels,
            'access_payperposts'  => $team_account->access_payperposts,
            'exclude_levels'      => $team_account->exclude_levels,
            'exclude_payperposts' => $team_account->exclude_payperposts,
            'quantity'            => $quantity ? $quantity : $team_account->default_children,
        ];
        if (! $team['quantity']) {
            return false;
        }
        $index = sprintf('%d-%s', $team['id'], $team['transaction_id']);
        $this->teams[ $index ] = $team;
        $this->refresh_team_levels();
        return wishlistmember_instance()->Update_UserMeta($this->user_id, 'team-accounts/teams', wp_json_encode($this->teams));
    }

    /**
     * Remove team from parent.
     * - Removes all matching $team_id if $transaction_id is empty.
     * - Removes all matching $transaction_id if $team_id is empty.
     *
     * @param  integer $team_id        Team ID.
     * @param  string  $transaction_id Transaction ID.
     * @return boolean
     */
    public function remove_team($team_id = null, $transaction_id = null)
    {
        if (! $team_id && ! $transaction_id) {
            return false;
        }
        $id = $team_id;
        foreach ($this->get_matching_teams(array_filter(compact('id', 'transaction_id'))) as $key) {
            unset($this->teams[ $key ]);
        }
        $this->refresh_team_levels();
        return wishlistmember_instance()->Update_UserMeta($this->user_id, 'team-accounts/teams', wp_json_encode($this->teams));
    }

    /**
     * Refresh team level data.
     *
     * @return void
     */
    public function refresh_team_levels()
    {
        $u = new \WishListMember\User($this->user_id);
        if (! $u->ID) {
            return;
        }
        $this->all_team_levels    = [];
        $this->active_team_levels = [];
        foreach ($this->teams as $team) {
            if (self::STATUS_ACTIVE !== (int) $team['status']) {
                continue;
            }
            if (isset($this->all_team_levels[ $team['id'] ])) {
                continue;
            }
            $team = Team_Account::get($team['id'], true);
            if ($team['mirrored_access']) {
                $this->all_team_levels[ $team['id'] ]    = array_diff_key(wlm_or($u->Levels, []), array_flip(wlm_or($team['exclude_levels'], [])));
                $this->active_team_levels[ $team['id'] ] = array_diff(wlm_or($u->active_levels, []), wlm_or($team['exclude_levels'], []));
                $this->pay_per_posts[ $team['id'] ]      = array_diff(wlm_or($u->pay_per_posts['_all_'], []), wlm_or($team['exclude_payperposts'], []));
            } else {
                $this->all_team_levels[ $team['id'] ]    = array_intersect_key(wlm_or($u->Levels, []), array_flip(wlm_or($team['access_levels'], [])));
                $this->active_team_levels[ $team['id'] ] = array_intersect(wlm_or($u->active_levels, []), wlm_or($team['access_levels'], []));
                $this->pay_per_posts[ $team['id'] ]      = array_intersect(wlm_or($u->pay_per_posts['_all_'], []), wlm_or($team['access_payperposts'], []));
            }
        }
    }

    /**
     * Add children to team
     *
     * @param  integer       $team_id  Team ID.
     * @param  integer|int[] $children Child ID or array of Children IDs to add to team.
     * @return integer Number of children added.
     */
    public function add_children($team_id, $children)
    {
        if (! isset($this->children[ $team_id ]) || ! is_array($this->children[ $team_id ])) {
            return false;
        }
        $children = array_diff(array_unique((array) $children), $this->children[ $team_id ]);
        $added    = [];
        $value    = $this->user_id . '-' . $team_id;
        foreach ($children as $child) {
            if (! in_array($value, (array) get_user_meta($child, 'team-accounts/parent'))) {
                if (add_user_meta($child, 'team-accounts/parent', $this->user_id . '-' . $team_id)) {
                    $added[] = $child;
                }
            }
        }
        $this->children[ $team_id ] = array_merge($this->children[ $team_id ], $added);
        return count($added);
    }

    /**
     * Removed children from team
     *
     * @param  integer       $team_id  Team ID.
     * @param  integer|int[] $children Child ID or array of Children IDs to remove from team.
     * @return integer Number of children removed.
     */
    public function remove_children($team_id, $children)
    {
        if (! isset($this->children[ $team_id ]) || ! is_array($this->children[ $team_id ])) {
            return false;
        }
        $children = array_intersect(array_unique((array) $children), $this->children[ $team_id ]);
        $removed  = [];
        foreach ($children as $child) {
            if (delete_user_meta($child, 'team-accounts/parent', $this->user_id . '-' . $team_id)) {
                $removed[] = $child;
            }
        }
        $this->children[ $team_id ] = array_diff($this->children[ $team_id ], $removed);
        return count($removed);
    }

    /**
     * Search for all users with matching $transaction_id and remove the team from them.
     *
     * @param  string $transaction_id
     * @return void
     */
    public static function remove_team_by_transaction_id($transaction_id)
    {
        $users = self::search_user_by_transaction_id($transaction_id);
        foreach ($users as $user_id) {
            $parent = new self($user_id);
            if ($parent->teams) {
                $parent->remove_team(null, $transaction_id);
            }
        }
    }

    /**
     * Search for all users with matching $transaction_id and cancel the team.
     *
     * @param  string $transaction_id
     * @return void
     */
    public static function cancel_team_by_transaction_id($transaction_id)
    {
        $users = self::search_user_by_transaction_id($transaction_id);
        foreach ($users as $user_id) {
            $parent = new self($user_id);
            if ($parent->teams) {
                $parent->set_team_status(self::STATUS_INACTIVE, null, $transaction_id);
            }
        }
    }

    /**
     * Search for all users with matching $transaction_id and cancel the team.
     *
     * @param  string $transaction_id
     * @return void
     */
    public static function uncancel_team_by_transaction_id($transaction_id)
    {
        $users = self::search_user_by_transaction_id($transaction_id);
        foreach ($users as $user_id) {
            $parent = new self($user_id);
            if ($parent->teams) {
                $parent->set_team_status(self::STATUS_ACTIVE, null, $transaction_id);
            }
        }
    }

    /**
     * Remove team from parent.
     * - Removes all matching $team_id if $transaction_id is empty.
     * - Removes all matching $transaction_id if $team_id is empty.
     *
     * @param  integer $status.        Values can be Team_Parent::STATUS_ACTIVE and Team_Parent::STATUS_INACTIVE
     * @param  integer $team_id        Team ID.
     * @param  string  $transaction_id Transaction ID.
     * @return boolean
     */
    public function set_team_status($status, $team_id = null, $transaction_id = null)
    {
        if (! $team_id && ! $transaction_id) {
            return false;
        }
        $id = $team_id;
        foreach ($this->get_matching_teams(array_filter(compact('id', 'transaction_id'))) as $key) {
            $this->teams[ $key ]['status'] = $status;
        }

        $this->refresh_team_levels();
        return wishlistmember_instance()->Update_UserMeta($this->user_id, 'team-accounts/teams', wp_json_encode($this->teams));
    }

    /**
     * Search team parent by transaction ID
     *
     * @param  string $transaction_id Transaction ID.
     * @return array
     */
    public static function search_user_by_transaction_id($transaction_id)
    {
        global $wpdb;
        return wlm_or(
            $wpdb->get_col(
                $wpdb->prepare(
                    'select `user_id` from `' . esc_sql(wishlistmember_instance()->table_names->user_options) . '` where option_value like %s',
                    '%"transaction_id":"' . $transaction_id . '"%'
                )
            ),
            []
        );
    }

    /**
     * Get matching teams
     *
     * @param  array $args Associative array of team information.
     * @return array Matching keys.
     */
    public function get_matching_teams($args)
    {
        $keys = array_keys(
            array_filter(
                $this->teams,
                function ($team) use ($args) {
                    return count($args) === count(array_intersect_assoc($team, $args));
                }
            )
        );
        return $keys;
    }

    /**
     * Return Team_Parent object for current user.
     *
     * @return object Team_Parent object for the current user.
     */
    public static function current_user()
    {
        static $parent;
        if (empty($parent)) {
            $parent = new self(get_current_user_id());
        }
        return $parent;
    }

    /**
     * Get team members
     *
     * @param  string  $search   Search string.
     * @param  boolean $ids_only Optional. True to return IDs only. Default false.
     * @return array  Array of member_ids and invite status.
     */
    public function search_members($team_id, $search = '', $ids_only = false)
    {
        global $wpdb;
        $parent_id = (int) $this->user_id;
        if (! $parent_id) {
            return [];
        }
        $team_members = (
            new \WishListMember\User_Search(
                [
                    'search_term' => wlm_trim($search),
                    'meta_query'  => [
                        'relation' => 'OR',
                        [
                            'key'     => 'team-accounts/parent',
                            'value'   => $team_id ? $parent_id . '-' . $team_id : '^' . $parent_id . '-',
                            'compare' => $team_id ? '=' : 'RLIKE',
                        ],
                    ],
                ]
            )
        )->results;

        // Get invited users.
        $invited_emails   = wlm_or($this->get_team_invites($team_id), []);
        $invited_user_ids = [];
        if ($invited_emails) {
            $invited_emails   = array_unique($invited_emails);
            $invited_user_ids = $wpdb->get_results(
                $wpdb->prepare(
                    'SELECT `ID`, `user_email` FROM `' . $wpdb->users . '` WHERE `user_email` IN (' . implode(', ', array_fill(0, count($invited_emails), '%s')) . ')',
                    ...array_values($invited_emails)
                ),
                ARRAY_A
            );

            if ($invited_user_ids) {
                $invited_user_ids = array_column($invited_user_ids, 'user_email', 'ID');
                $invited_emails   = array_diff($invited_emails, $invited_user_ids);
                $invited_user_ids = array_diff(array_keys($invited_user_ids), $team_members);
                array_push($team_members, ...$invited_user_ids);
            }
        }

        $team_members = array_unique($team_members);

        if ($ids_only) {
            return array_merge($team_members, $invited_emails);
        }

        foreach ($team_members as &$v) {
            list( $name, $email ) = format_member_name($v, true);
            $v                    = [
                'id'     => $v,
                'name'   => $name,
                'email'  => $email,
                'active' => ! in_array($v, $invited_user_ids),
            ];
        }
        unset($v);

        foreach ($invited_emails as $invite) {
            $team_members[] = [
                'id'     => $invite,
                'name'   => $invite,
                'email'  => $invite,
                'active' => false,
            ];
        }

        return $team_members;
    }

    /**
     * Get max number of allowed children.
     *
     * @param  integer $team_id Team ID.
     * @return integer
     */
    public function get_max_allowed_children($team_id)
    {
        return array_sum(
            array_column(
                array_filter(
                    $this->teams,
                    function ($team) use ($team_id) {
                        return (int) $team_id === (int) $team['id'] && self::STATUS_ACTIVE === (int) $team['status'];
                    }
                ),
                'quantity'
            )
        );
    }

    /**
     * Get invites sent by team.
     *
     * @param  integer $team_id Team ID.
     * @return array|false Array of emails or false.
     */
    public function get_team_invites($team_id)
    {
        if ($team_id) {
            $team_ids = [$team_id];
        } else {
            $team_ids = array_unique(array_column($this->teams, 'id'));
        }
        $x = [];
        foreach ($team_ids as $team_id) {
            $x = array_merge($x, wlm_or(get_user_meta($this->user_id, 'team-accounts/invite/' . $team_id), []));
        }
        return $x ? $x : false;
    }

    /**
     * Delete team invite
     *
     * @param  integer $team_id Team ID.
     * @param  string  $email   Email address.
     * @return void
     */
    public function delete_team_invite($team_id, $email)
    {
        // Specific invite.
        delete_user_meta($this->user_id, 'team-accounts/invite/' . $team_id, $email);
        delete_user_meta($this->user_id, 'team-accounts/invite-key/' . $team_id . '/' . strtolower($email));
    }

    /**
     * Send a team invite
     *
     * @param  integer $team_id Team ID.
     * @param  string  $email   Email address.
     * @return boolean
     */
    public function send_team_invite($team_id, $email)
    {
        if (! isset($this->children[ $team_id ]) || count($this->children[ $team_id ]) >= $this->get_max_allowed_children($team_id)) {
            return false;
        }
        if (! add_user_meta($this->user_id, 'team-accounts/invite/' . $team_id, $email)) {
            return false;
        }

        $headers = [
            sprintf(
                'From: %s <%s>',
                wishlistmember_instance()->get_option('team-accounts/team_invite_email_sender_name'),
                wishlistmember_instance()->get_option('team-accounts/team_invite_email_sender_email')
            ),
            'Content-Type: text/html',
        ];

        $args = [
            $email,
            $this->process_email_shortcodes(wishlistmember_instance()->get_option('team-accounts/team_invite_email_subject'), $team_id, $email),
            $this->process_email_shortcodes(wishlistmember_instance()->get_option('team-accounts/team_invite_email_message'), $team_id, $email),
            $headers,
        ];
        wp_mail(...$args);
        return true;
    }

    /**
     * Process email template shortcodes
     *
     * @param  string  $content Email content.
     * @param  integer $team    Team ID.
     * @param  string  $email   Email address.
     * @return string
     */
    public function process_email_shortcodes($content, $team, $email)
    {

        $join_key = wlm_trim(get_user_meta($this->user_id, 'team-accounts/invite-key/' . $team . '/' . strtolower($email), true));
        if (! $join_key) {
            $join_key = md5($email . $team . time() . wp_rand());
            if (! add_user_meta($this->user_id, 'team-accounts/invite-key/' . $team . '/' . strtolower($email), $join_key, true)) {
                return '';
            }
        }

        $link = add_query_arg('wlm-team-accounts-team-registration', $join_key, wishlistmember_instance()->magic_page());

        $shortcodes = ['site_name', 'accept_invite', 'team_plan_name'];

        preg_match_all('/' . get_shortcode_regex($shortcodes) . '/', $content, $matches, PREG_SET_ORDER);

        foreach ($matches as &$match) {
            $match[3] = shortcode_parse_atts($match[3]);
            switch ($match[2]) {
                case 'site_name':
                    $content = str_replace($match[0], get_bloginfo('name'), $content);
                    break;
                case 'team_plan_name':
                    $content = str_replace($match[0], wlm_arrval($this->teams_grouped(), $team, 0, 'name'), $content);
                    break;
                case 'accept_invite':
                    $content = str_replace(
                        $match[0],
                        sprintf(
                            '<a href="%s" style="%s">%s</a>',
                            esc_url($link),
                            esc_attr(wlm_arrval($match, 3, 'style')),
                            esc_html(wlm_arrval($match, 3, 'text'))
                        ),
                        $content
                    );
                    break;
            }
        }
        unset($match);

        return $content;
    }

    /**
     * Get team data from invite key.
     *
     * @param  string $invite_key Invite key.
     * @return array {
     *     // Associative array containing invite data.
     * @type   integer $parent_id Parent ID.
     * @type   integer $team_id   Team ID.
     * @type   string  $email     Email address.
     * }
     */
    public static function get_invite_data($invite_key)
    {
        global $wpdb;
        static $results = [];
        if (isset($results[ $invite_key ])) {
            return $results[ $invite_key ];
        }
        $key  = $wpdb->get_row($wpdb->prepare('SELECT `user_id`,`meta_key` FROM `' . $wpdb->usermeta . '` WHERE `meta_key` LIKE %s AND `meta_value`=%s', 'team-accounts/invite-key/%', $invite_key));
        $data = [
            'parent_id' => 0,
            'team_id'   => 0,
            'email'     => '',
        ];
        if ($key) {
            $data['parent_id'] = (int) $key->user_id;
            $key               = explode('/', $key->meta_key, 4);
            $data['team_id']   = (int) $key[2];
            $data['email']     = $key[3];
        }
        $results[ $invite_key ] = $data;
        return $data;
    }

    /**
     * Getter
     *
     * @param  string $prop Property name.
     * @return mixed
     */
    public function __get($prop)
    {
        return $this->{$prop};
    }

    /**
     * Setter
     *
     * Only sets valid object properties
     *
     * @param  string $prop  Property name.
     * @param  mixed  $value Property value.
     * @return void
     */
    public function __set($prop, $value)
    {
        switch ($prop) {
            case 'teams':
                $this->teams = is_array($value) ? $value : [];
                break;
            case 'children':
                $this->children = is_array($value) ? $value : [];
                break;
        }
    }
}
