<?php

/**
 * Member Edit - Team tab pane
 *
 * @package WishListMember/Features/TeamAccounts
 */

namespace WishListMember\Features\Team_Accounts;

$parent             = new Team_Parent($user_id);
$teams_grouped      = $parent->teams_grouped();
$child              = new Team_Child($user_id);
$level_names        = array_column(wishlistmember_instance()->get_option('wpm_levels'), 'name', 'id');
$no_teams_message   = sprintf('<p class="text-center no-teams-message">%s</p>', esc_html__('This user is not managing any team plans.', 'wishlist-member'));
$not_member_message = sprintf('<p class="text-center not-member-message">%s</p>', esc_html__('This user has not joined any team plans.', 'wishlist-member'));
?>
<div id="wishlist-member-team-accounts">
    <div class="horizontal-tabs">
        <div class="row no-gutters">
            <div class="col-12 col-md-auto">
                <!-- Nav tabs -->
                <div class="horizontal-tabs-sidebar" style="min-width: 100px; min-height: 200px;">
                    <ul class="nav nav-tabs -h-tabs flex-column" role="tablist">
                        <li role="presentation" class="nav-item">
                            <a href="#wlm-team-accounts-managed-teams" class="nav-link pp-nav-link active" aria-controls="wlm-team-accounts-managed-teams" role="tab" data-type="wlm-team-accounts-managed-teams" data-title="Team Plans" data-toggle="tab"><?php esc_html_e('Team Plans', 'wishlist-member'); ?></a>
                        </li>
                        <li role="presentation" class="nav-item">
                            <a href="#wlm-team-accounts-team-members" class="nav-link pp-nav-link" aria-controls="wlm-team-accounts-team-members" role="tab" data-type="wlm-team-accounts-team-members" data-title="Team Members" data-toggle="tab"><?php esc_html_e('Team Members', 'wishlist-member'); ?></a>
                        </li>
                        <li role="presentation" class="nav-item">
                            <a href="#wlm-team-accounts-teams" class="nav-link pp-nav-link" aria-controls="wlm-team-accounts-teams" role="tab" data-type="wlm-team-accounts-teams" data-title="Teams" data-toggle="tab"><?php esc_html_e('Teams Joined', 'wishlist-member'); ?></a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col">
                <!-- Tab panes -->
                <div class="tab-content <?php echo $teams_grouped ? 'is-managing-teams' : ''; ?> <?php echo $child->teams ? 'is-team-member' : ''; ?>">
                    <?php
                        require __DIR__ . '/managed-teams.php';
                        require __DIR__ . '/team-members.php';
                        require __DIR__ . '/teams-joined.php';
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
var wlm_team_id = '#wishlist-member-team-accounts';
$(wlm_team_id).transform_form_groups();
$(wlm_team_id).transform_toggle_switches();
$(wlm_team_id).transform_select2();
var wlm_team_user = <?php echo (int) $user_id; ?>;
var wlm_team_accounts_teams = {};
var wlm_team_accounts_children = {};

function wlm_team_accounts_get_team_data(callback) {
    $.post(
        WLM3VARS.ajaxurl,
        {
            action: 'wishlistmember_team_accounts_get_parent_team_data',
            user_id: wlm_team_user,
            [WLM3VARS.nonce_field]: WLM3VARS.nonce
        },
        function(r) {
            if(r.success) {
                wlm_team_accounts_teams = r.data.teams;
                wlm_team_accounts_children = r.data.children;
                'function' === typeof callback && callback();
                $('body').trigger('wishlistmember-team-accounts:data-refreshed');
            }
        }
    );
}
wlm_team_accounts_get_team_data();
$('body').trigger('wishlistmember-team-accounts:member-edit-loaded');
</script>
<style>
    /* Hide/show content based on whether the user is managing teams or not */
    #wishlist-member-team-accounts .tab-content.is-managing-teams .tab-pane.team-parent-tab .no-teams-message,
    #wishlist-member-team-accounts .tab-content:not(.is-managing-teams) .tab-pane.team-parent-tab .teams-required {
        display: none   ;
    }

    #wishlist-member-team-accounts .tab-content:not(.is-managing-teams) a.btn.-success[href="#add-team"] {
        margin: auto;
        display: block;
        width: 150px;
    }

    /* Hide/show content based on whether the user is a team member or not */
    #wishlist-member-team-accounts .tab-content.is-team-member .tab-pane.team-child-tab .not-member-message,
    #wishlist-member-team-accounts .tab-content:not(.is-team-member) .tab-pane.team-child-tab .team-membership-required {
        display: none;
    }

    /* Message Holder */
    .wlm-teams-msg-holder br {
        display: none !important;
    }
    .wlm-teams-msg-holder p {
        font-size: 15px !important;
        margin: 0 !important;
    }
    .wlm-teams-msg-holder button {
        margin: 0 !important;
        padding: 10px !important;
    }
    .wlm-teams-msg-holder div.alert {
        margin: 0 0 1em 0 !important;
        padding: 6px 40px 10px 10px !important;
    }
</style>
