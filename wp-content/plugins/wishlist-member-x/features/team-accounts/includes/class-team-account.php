<?php

/**
 * Team class file
 *
 * @package WishListMember/Features/Team_Accounts
 */

namespace WishListMember\Features\Team_Accounts;

use Exception;

/**
 * Team Class
 */
class Team_Account
{
    /**
     * Team ID.
     *
     * @var integer
     */
    private $id = 0;

    /**
     * Team name.
     *
     * @var string
     */
    private $name = '';

    /**
     * Initial children issued when assigning team to parent.
     *
     * @var integer
     */
    private $default_children = 0;

    /**
     * Levels that trigger adding to the team.
     *
     * @var array
     */
    private $triggers = [];

    /**
     * If true, mirror access of parent to children.
     *
     * @var boolean
     */
    private $mirrored_access = true;

    /**
     * Specific membership levels to mirror from parent to children.
     * Applicable only if $mirrored_access is false.
     *
     * @var array
     */
    private $access_levels = [];

    /**
     * Specific pay per posts to mirror from parent to children.
     * Applicable only if $mirrored_access is false.
     *
     * @var array
     */
    private $access_payperposts = [];

    /**
     * Specific membership levels to exclude from parent to children.
     * Applicable only if $mirrored_access is true.
     *
     * @var array
     */
    private $exclude_levels = [];

    /**
     * Specific pay per posts to exclude from parent to children.
     * Applicable only if $mirrored_access is true.
     *
     * @var array
     */
    private $exclude_payperposts = [];

    /**
     * Get a team from the database
     *
     * @param  integer $id                   Team ID.
     * @param  boolean $as_associative_array True to return results as associative array instead of Team_Account objects. Default is false.
     * @return Team_Account|array|false  Team object or associative array on success or false on failure.
     */
    public static function get($id, $as_associative_array = false)
    {
        $team = json_decode(wishlistmember_instance()->get_option('team-accounts/team/' . (int) $id), true);
        if (! $team) {
            return false;
        }
        $team_object = new self();
        $assoc       = [];
        foreach ($team as $key => $value) {
            $team_object->{$key} = $value;
            $assoc[ $key ]       = $team_object->{$key};
        }
        if (! $team_object->id) {
            return false;
        }
        if ($as_associative_array) {
            $assoc = array_filter(
                $assoc,
                function ($value) {
                    return ! is_null($value);
                }
            );
            return count($assoc) ? $assoc : false;
        }
        return $team_object;
    }

    /**
     * Delete a team from the database
     *
     * @return true
     */
    public function delete()
    {
        wishlistmember_instance()->delete_option('team-accounts/team/' . $this->id);
        return true;
    }

    /**
     * Create a new team.
     *
     * @param array $args {
     *   Associative array of team data.
     *
     * @type   string  $name                Team name.
     * @type   integer $default_children    Initial children issued when assigning team to parent.
     * @type   array   $triggers            Levels that trigger adding to the team..
     * @type   boolean $mirrored_access     If true, mirror access of parent to children. Default is true.
     * @type   array   $access_levels       Specific membership levels to mirror from parent to children if $mirrored_access if false.
     * @type   array   $access_payperposts  Specific pay per posts to mirror from parent to children if $mirrored_access if false.
     * @type   array   $exclude_levels      Specific membership levels to exclude from parent to children if $mirrored_access if true.
     * @type   array   $exclude_payperposts Specific pay per posts to exclude from parent to children if $mirrored_access if true.
     * }
     * @return Team|false Team object on success or false on failure.
     */
    public static function create($args)
    {
        if (! is_array($args)) {
            return false;
        }

        // Set defaults.
        if (empty($args['id'])) {
            $args['id'] = (int) ( microtime(true) * 1000 );
        }
        if (! isset($args['mirrored_access'])) {
            $args['mirrored_access'] = true;
        }

        // Create object.
        $team_object = new self();
        foreach ($args as $key => $value) {
            $team_object->{$key} = $value;
        }

        // Save and return result.
        return $team_object->save() ? $team_object : false;
    }

    /**
     * Saves the current Team object.
     *
     * @return boolean
     */
    public function save()
    {
        if (! $this->id || ! $this->name) {
            return false;
        }
        if (! $this->mirrored_access && ! $this->access_levels && ! $this->access_payperposts) {
            return false;
        }

        $data = [
            'id'                  => (int) $this->id,
            'name'                => (string) $this->name,
            'default_children'    => (int) $this->default_children,
            'triggers'            => (array) $this->triggers,
            'mirrored_access'     => (bool) $this->mirrored_access,
            'access_levels'       => (array) $this->access_levels,
            'access_payperposts'  => (array) $this->access_payperposts,
            'exclude_levels'      => (array) $this->exclude_levels,
            'exclude_payperposts' => (array) $this->exclude_payperposts,
        ];
        return wishlistmember_instance()->save_option('team-accounts/team/' . (int) $this->id, wp_json_encode($data));
    }

    /**
     * Get all team accounts
     *
     * @param  boolean $as_associative_array True to return results as associative array instead of Team_Account objects. Default is false.
     * @return array
     */
    public static function get_all($as_associative_array = false)
    {
        global $wpdb;
        $teams = $wpdb->get_col('SELECT `option_name` FROM `' . esc_sql(wishlistmember_instance()->table_names->options) . '` WHERE `option_name` LIKE "team-accounts/team/%"');
        foreach ($teams as &$team) {
            $team = self::get(explode('/', $team, 3)[2], $as_associative_array);
        }
        unset($team);
        if ($as_associative_array) {
            $teams = array_combine(array_column($teams, 'id'), $teams);
        }
        return $teams;
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
     * Only sets valid object properties
     *
     * @param  string $prop  Property name.
     * @param  mixed  $value Property value.
     * @return void
     */
    public function __set($prop, $value)
    {
        switch ($prop) {
            case 'id':
                $this->id = (int) $value;
                break;
            case 'name':
                $this->name = wlm_trim($value);
                break;
            case 'default_children':
                $this->default_children = (int) $value;
                break;
            case 'triggers':
                $this->triggers = (array) $value;
                break;
            case 'mirrored_access':
                $this->mirrored_access = (bool) $value;
                break;
            case 'access_levels':
                $this->access_levels = (array) $value;
                break;
            case 'access_payperposts':
                $this->access_payperposts = (array) $value;
                break;
            case 'exclude_levels':
                $this->exclude_levels = (array) $value;
                break;
            case 'exclude_payperposts':
                $this->exclude_payperposts = (array) $value;
                break;
        }
    }
}
