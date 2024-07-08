<?php

/**
 * Member Edit - Team - Team Plans Sub-pane
 *
 * @package WishListMember/Features/TeamAccounts
 */

namespace WishListMember\Features\Team_Accounts;

?>
<div role="tabpanel" class="tab-pane team-parent-tab active" id="wlm-team-accounts-managed-teams">
    <?php echo wp_kses_post($no_teams_message); ?>
    <p class="teams-required">
        <?php esc_html_e('The Team Plans managed by the Team Admin are listed below. Any member can be made a Team Admin by assigning them a Team Plan to manage. A Team Admin can only manage Team Plans they have been assigned.', 'wishlist-member'); ?>
    </p>
    <div class="row">
        <div class="col-12 wlm-teams-msg-holder" id="add-team-msg"></div>
    </div>
    <div>
        <form id="add-team" class="collapse panel">
            <div class="panel-heading">
                <small class="pull-right align-middle">
                    <a href="#add-team" data-toggle="collapse" class="btn text-muted -icon-only wlm-icons p-0" style="margin-right:-5px">close</a>
                </small>
                <h3 class="panel-title">
                    <?php esc_html_e('Add Team Plan', 'wishlist-member'); ?>
                    <?php wishlistmember_instance()->tooltip(__('A Team Plan can be added to a Team Admin using the fields below.', 'wishlist-member')); ?>
                </h3>
            </div>
            <div class="panel-body">
                <div class="row add-team-form">
                    <h3 class="col-12 mb-3 d-md-none"><?php esc_html_e('Add Team Plan to User', 'wishlist-member'); ?></h3>
                    <template class="wlm3-form-group">
                    [
                        {
                            column: 'col-md-4',
                            name: 'team_id',
                            group_class: 'mb-3',
                            type:'select',
                            style:'width:100%',
                            label: '<?php echo esc_js(__('Team Plan', 'wishlist-member')); ?>',
                            'data-placeholder': '<?php echo esc_js(__('Choose a Team Plan', 'wishlist-member')); ?>',
                            tooltip:'<?php echo esc_js(__('Choose a Team Plan to assign to the Team Admin. Team Plans can be created and managed in the Settings > Team Accounts > Team Plans section in WishList Member.', 'wishlist-member')); ?>',
                            options: 
                            <?php
                            echo wp_json_encode(
                                array_merge(
                                    [
                                        [
                                            'value' => '',
                                            'text'  => '',
                                        ],
                                    ],
                                    array_values(
                                        array_map(
                                            function ($team) {
                                                return [
                                                    'value' => $team->id,
                                                    'text' => $team->name,
                                                ];
                                            },
                                            Team_Account::get_all()
                                        )
                                    )
                                )
                            );
                            ?>
                        },
                        {
                            column: 'col-md-2 px-md-0',
                            name: 'quantity',
                            group_class: 'mb-3',
                            type:'number',
                            min:1,
                            value:1,
                            label: '<?php echo esc_js(__('Team Members', 'wishlist-member')); ?>',
                            tooltip:'<?php echo esc_js(__('Set the total number of members a Team Admin can add to the Team Plan.', 'wishlist-member')); ?>',
                        },
                        {
                            column: 'col-md pr-md-0',
                            name: 'transaction_id',
                            group_class: 'mb-3',
                            type:'text',
                            label: '<?php echo esc_js(__('Transaction ID', 'wishlist-member')); ?>',
                            tooltip:'<?php echo esc_js(__('The unique Transaction ID tied to the transaction of adding a Team Plan to a Team Admin.', 'wishlist-member')); ?>',
                        }
                    ]
                    </template>
                    <div class="col-md-auto">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn -primary -condensed btn-block mb-sm-3">
                            <i class="wlm-icons">add</i>
                            <span class="text"><?php esc_html_e('Add Team Plan', 'wishlist-member'); ?></span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
        <a class="btn -success -condensed mb-3" data-toggle="collapse" href="#add-team">
            <i class="wlm-icons">add</i>
            <span class="text"><?php esc_html_e('Add Team Plan', 'wishlist-member'); ?></span>
        </a>
    </div>
    <div class="table-wrapper table-responsive -special teams-required">
        <table class="table table-condensed" id="managed-teams">
            <thead>
                    <tr>
                        <th>
                            <?php esc_html_e('Team Plan', 'wishlist-member'); ?>
                            <?php wishlistmember_instance()->tooltip(__('The unique Team Plan name.', 'wishlist-member')); ?>
                        </th>
                        <th>
                            <?php esc_html_e('Transaction ID', 'wishlist-member'); ?>
                            <?php wishlistmember_instance()->tooltip(__('The unique Transaction ID tied to the transaction of adding a Team Plan to a Team Admin.', 'wishlist-member')); ?>
                        </th>
                        <th>
                            <?php esc_html_e('Transaction Date', 'wishlist-member'); ?>
                            <?php wishlistmember_instance()->tooltip(__('The Transaction Date tied to the transaction of adding a Team Plan to a Team Admin.', 'wishlist-member')); ?>
                        </th>
                        <th>
                            <?php esc_html_e('Team Members', 'wishlist-member'); ?>
                            <?php wishlistmember_instance()->tooltip(__('The total number of members a Team Admin can add to the Team Plan.', 'wishlist-member')); ?>
                        </th>
                        <th>
                            <?php esc_html_e('Status', 'wishlist-member'); ?>
                            <?php wishlistmember_instance()->tooltip(__('The current Status of the Team Plan. Status can be Active or Cancelled.', 'wishlist-member')); ?>
                        </th>
                        <th colspan="2"></th>
                    </tr>
            </thead>
        </table>
    </div>
</div>
<script type="text/template" id="team-accounts-teams">
{% _.each(data.teams, function(teams) { %}
    {% first = true; %}
    <tbody class="outer-tbody">
    {% _.each(teams, function(team) { %}
        <tr class="button-hover {% if ( !first ) { %}collapse team-{%- team.id %}{% } %}" data-team-id="{%- team.id %}" data-transaction-id="{%- team.transaction_id %}" data-team-name="{%- team.name %}">
            <td>{%- team.name %}</td>
            <td>{%- team.transaction_id %}</td>
            <td>{%- wlm.date(+moment.utc(team.date)/1000, {format:WLM3VARS.js_date_format})  %}</td>
            <td class="text-center">
                <span class="{% if ( teams.length > 1 ) { %}collapse team-{%- team.id %}{% } %}">{%- +team.quantity %}</span>
                {% if ( teams.length > 1 ) { %}
                <span class="collapse-reverse">{%- +team.max_children %}</span>
                {% } %}
            </td>
            <td>
                {%- !team.status ? wp.i18n.__('Active', 'wishlist-member') : wp.i18n.__('Cancelled') %}
            </td>
            <td class="text-right">
                <div class="btn-group-action">
                        {% if (!team.status) { %}
                        <a href="#" title="<?php esc_attr_e('Cancel', 'wishlist-member'); ?>" data-action="cancel" class="wlm-icons md-24 cancel-team-btn d-lg-inline d-md-inline">close</a>
                        {% } else { %}
                        <a href="#" title="<?php esc_attr_e('Uncancel', 'wishlist-member'); ?>" data-action="uncancel" class="wlm-icons md-24 uncancel-team-btn d-lg-inline d-md-inline">replay</a>
                        {% } %}
                        <a href="#" title="<?php esc_attr_e('Remove', 'wishlist-member'); ?>" data-action="remove" class="wlm-icons md-24 remove-team-btn d-lg-inline d-md-inline">remove_circle_outline</a>
                    </div>
                </td>
            <td width="50">
                {% if ( teams.length > 1 && first ) { %}
                <a id="collapse-{%- team.id %}" class="collapsed" data-toggle="collapse" href=".team-{%- team.id %}"><i class="wlm-icons"></i></a>
                {% } %}
            </td>
        </tr>   
    {% first = false; %}
    {% }) %}
    </tbody>
{% }) %}
</script>

<style>
    #managed-teams a[data-toggle="collapse"] .wlm-icons:before {
        content: 'arrow_drop_up';
    }
    #managed-teams a[data-toggle="collapse"].collapsed .wlm-icons:before {
        content: 'arrow_drop_down';
    }
    .collapsing ~ .collapse-reverse,
    .collapse.show ~ .collapse-reverse {
        display: none;
    }

    #add-team.collapsing ~ a[href="#add-team"],
    #add-team.show ~ a[href="#add-team"] {
        display: none !important;
    }

</style>
<script>
function wlm_team_accounts_load_managed_teams(team_to_highlight) {
    $('#managed-teams tbody').empty();
    function remove_cancel_uncancel(e) {
        e.preventDefault();
        var action = '';
        switch($(this).data('action')) {
            case 'remove':
                action = 'wishlistmember_team_accounts_remove_team';
                break;
            case 'cancel':
                action = 'wishlistmember_team_accounts_cancel_team';
                break;
            case 'uncancel':
                action = 'wishlistmember_team_accounts_uncancel_team_from_parent';
                break;
        }
        if(!action) {
            return;
        }
        var team_id = $(this).closest('tr').data('team-id');
        var team_name = $(this).closest('tr').data('team-name');
        var transaction_id = $(this).closest('tr').data('transaction-id');

        $.post(
            WLM3VARS.ajaxurl,
            {
                action: action,
                user_id: wlm_team_user,
                team_id: team_id,
                team_name: team_name,
                transaction_id: transaction_id,
                [WLM3VARS.nonce_field]: WLM3VARS.nonce
            },
            function(r) {
                if(r.success) {
                    $('#add-team-msg').show_message( { message:r.data.message } );
                    wlm_team_accounts_load_managed_teams(team_id);
                } else {
                    $('#add-team-msg').show_message( { message:r.data.message, type: 'danger' } );
                }
            }
        )
    }
    wlm_team_accounts_get_team_data(
        function() {
            var tmpl = _.template( $( 'script#team-accounts-teams' ).html(), {variable: 'data'} );
            $( '#managed-teams' ).remove('tbody').append(
                tmpl(
                    {
                        teams : wlm_team_accounts_teams,
                        time_offset: (WLM3VARS.js_time_offset>=0 ? '+' : '') + WLM3VARS.js_time_offset
                    }
                )
            );
            $('.cancel-team-btn, .uncancel-team-btn').on('click', remove_cancel_uncancel);
            $('.remove-team-btn').do_confirm(
                {
                    confirm_message : wp.i18n.__( 'Remove?', 'wishlist-member' ),
                    yes_button : wp.i18n.__( 'Remove', 'wishlist-member' ),
                    placement: 'right'
                }
            ).on('yes.do_confirm', remove_cancel_uncancel)

            if(team_to_highlight) {
                $('.team-' + team_to_highlight).collapse('show');
            }

            $('#wishlist-member-team-accounts .tab-content').toggleClass('is-managing-teams', Object.keys(wlm_team_accounts_teams).length > 0);
        }
    );
}
$(function() {
    wlm_team_accounts_load_managed_teams();
});
$('#add-team').on('submit', function(e) {
    e.preventDefault();
    var team_id= this.team_id.selectedOptions[0].value;
    $('#add-team button').prop('disabled',true).addClass('disabled');
    $.post(
        WLM3VARS.ajaxurl,
        {
            action: 'wishlistmember_team_accounts_add_team_to_parent',
            team_id: team_id,
            transaction_id: this.transaction_id.value.trim(),
            quantity: +this.quantity.value,
            user_id: wlm_team_user,
            [WLM3VARS.nonce_field]: WLM3VARS.nonce
        },
        function(r) {

            if(r.success) {
                $('#add-team')[0].reset();
                $('#add-team').collapse('hide').find(':input').trigger('change');
                $('#add-team-msg').show_message( { message:wp.i18n.__('Team Plan added to user', 'wishlist-member' ) } );
                wlm_team_accounts_load_managed_teams(team_id);
            } else {
                $('#add-team-msg').show_message( { message:r.data.message, type:'danger' } );
            }
            $('#add-team button').prop('disabled',false).removeClass('disabled');
        }
    )
    return false;
});
</script>
