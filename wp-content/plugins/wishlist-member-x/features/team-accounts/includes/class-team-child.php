<?php

/**
 * Team_Child class file
 *
 * @package WishListMember/Features/Team_Accounts
 */

namespace WishListMember\Features\Team_Accounts;

/**
 * Team_Child class
 */
class Team_Child
{
    /**
     * Child User ID.
     *
     * @var integer
     */
    private $user_id = 0;
    /**
     * Teams array.
     *
     * @var array
     */
    private $teams = [];
    /**
     * Merged Level Data.
     *
     * @var array
     */
    private $all_levels = [];
    /**
     * Original Level Data.
     *
     * @var array
     */
    private $original_all_levels = [];
    /**
     * Merged active level IDs
     *
     * @var array
     */
    private $active_levels = [];
    /**
     * Original active level IDs
     *
     * @var array
     */
    private $original_active_levels = [];
    /**
     * Original pay per posts
     *
     * @var array
     */
    private $original_pay_per_posts = [];
    /**
     * Merged pay per posts
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

        $parents = get_user_meta($this->user_id, 'team-accounts/parent');
        if (! $parents) {
            return;
        }
        foreach ($parents as $parent) {
            list($parent_id, $team_id) = explode('-', $parent, 2);
            $this->teams[ $parent ]    = compact('parent_id', 'team_id');
        }

        // Merge child and parent levels.
        $x = new \WishListMember\User($this->user_id);
        if (! $x->ID) {
            return [];
        }
        $this->all_levels             = $x->Levels;
        $this->original_all_levels    = $x->Levels;
        $this->active_levels          = $x->active_levels;
        $this->original_active_levels = $x->active_levels;

        foreach ($this->teams as $key => $team) {
            $p = new Team_Parent($team['parent_id']);
            if (! $p->teams) {
                unset($this->teams[ $key ]);
                continue;
            }
            if (empty($p->children[ $team['team_id'] ])) {
                $p->remove_children($team['team_id'], $user_id);
                unset($this->teams[ $key ]);
                continue;
            }

            $atl = wlm_or($p->all_team_levels[ $team['team_id'] ], []);

            $this->all_levels = array_filter(
                $this->all_levels,
                function ($l) use ($atl) {
                    return ! ( ! $l->Active && $atl[ $l->Level_ID ]->Active );
                }
            ) + $atl;

            $this->active_levels = array_merge($this->active_levels, wlm_or($p->active_team_levels[ $team['team_id'] ], []));
            $this->pay_per_posts = array_merge($this->pay_per_posts, wlm_or($p->pay_per_posts[ $team['team_id'] ], []));
        }
        $this->active_levels = array_unique($this->active_levels);
        $this->pay_per_posts = array_unique($this->pay_per_posts);
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
}
