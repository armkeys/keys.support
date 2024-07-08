<?php

/**
 * Member Edit - Team - Team Members Sub-pane
 *
 * @package WishListMember/Features/TeamAccounts
 */

namespace WishListMember\Features\Team_Accounts;

?>
<div role="tabpanel" class="tab-pane team-parent-tab" id="wlm-team-accounts-team-members">
    <?php echo wp_kses_post($no_teams_message); ?>
    <p class="teams-required">
        <?php esc_html_e('The members in the Team Plans being managed by the Team Admin are listed below.', 'wishlist-member'); ?>
    </p>
    <form id="team-member-search" class="teams-required">
        <div class="row">
            <template class="wlm3-form-group">
            [
                {
                    column: 'col-md',
                    group_class: 'mb-3',
                    class: 'team-members-dropdown -with-usage',
                    id:'team-member-search-team',
                    name: 'team_id',
                    type:'select',
                    style:'width:100%',
                    'data-placeholder': '<?php echo esc_js(__('Choose a Team Plan to view it\'s Members', 'wishlist-member')); ?>',
                    options: []
                }
            ]
            </template>
        </div>
    </form>
    <div class="row teams-required">
        <div class="col-12 wlm-teams-msg-holder" id="add-member-msg"></div>
        <div class="col-md-12">
            <form id="add-team-member" class="collapse panel">
                <div class="panel-heading">
                    <small class="pull-right align-middle">
                        <a href="#add-team-member" data-toggle="collapse" class="btn text-muted -icon-only wlm-icons p-0" style="margin-right:-5px">close</a>
                    </small>
                    <h3 class="panel-title">
                        <?php esc_html_e('Add Team Member', 'wishlist-member'); ?>
                        <?php wishlistmember_instance()->tooltip(__('A member can be added to team using the fields below.', 'wishlist-member')); ?>
                    </h3>
                </div>
                <div class="panel-body">
                    <p>
                        <?php
                        echo sprintf(
                            // Translators: %s - Team Plan.
                            esc_html__('The member will be added to %s.', 'wishlist-member'),
                            '<span class="team-name"></span>'
                        );
                        ?>
                    </p>
                    <div class="row">
                        <template class="wlm3-form-group">
                            [
                                {
                                    name: 'team_id',
                                    type: 'hidden',
                                },
                                {
                                    label:'<?php echo esc_js(__('Email', 'wishlist-member')); ?>',
                                    name:'email',
                                    group_class:'mb-3',
                                    placeholder:'<?php echo esc_js(__('Email address', 'wishlist-member')); ?>',
                                    type:'text',
                                    column:'col-md',
                                    tooltip:'<?php echo esc_js(__('Enter the email address for the member.', 'wishlist-member')); ?>'
                                },
                                {
                                    label:'<?php echo esc_js(__('Method', 'wishlist-member')); ?>',
                                    name: 'method',
                                    group_class:'mb-3',
                                    type: 'select',
                                    style:'width:100%',
                                    column: 'col-md-4 pl-md-0',
                                    tooltip: '<?php echo '<p>' . esc_js(__('Send Invite to User. If selected, an invite email will be sent to both existing users and new users.', 'wishlist-member')) . '</p><p>' . esc_js(__('Add if Existing User. If selected, existing users will automatically be added to the Team Plan and new users will be sent the invite email to join the Team Plan.', 'wishlist-member')) . '</p><p>' . esc_js(__('The Team Invite email can be configured in the Setup > Team Accounts > Email Templates section of WishList Member.', 'wishlist-member')) . '</p>'; ?>',
                                    options: 
                                    <?php
                                    echo wp_json_encode(
                                        [
                                            [
                                                'value' => 'invite',
                                                'text'  => __(
                                                    'Send Invite to User',
                                                    'wishlist-member'
                                                ),
                                            ],
                                            [
                                                'value' => 'add',
                                                'text'  => __(
                                                    'Add if Existing User',
                                                    'wishlist-member'
                                                ),
                                            ],
                                        ]
                                    );
                                    ?>
                                     
                                },
                            ]
                        </template>
                        <div class="col-md-12">
                            <button type="submit" class="btn -primary -condensed">
                                <i class="wlm-icons">add</i>
                                <span class="text"><?php esc_html_e('Add Team Member', 'wishlist-member'); ?></span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
            <a href="#add-team-member" id="add-team-member-toggle" data-toggle="collapse" class="btn -default -condensed -success mb-3 d-none">
                <i class="wlm-icons">add</i>
                <span class="text"><?php esc_html_e('Add Team Member', 'wishlist-member'); ?></span>
            </a>
        </div>
    </div>
    <div class="table-wrapper table-responsive teams-required">
        <table class="table table-condensed" id="team-members-search-result">
            <tbody></tbody>
            <thead>
                <tr>
                    <th>
                        <?php esc_html_e('Name', 'wishlist-member'); ?>
                        <?php wishlistmember_instance()->tooltip(__('The name of the member.', 'wishlist-member')); ?>
                    </th>
                    <th>
                        <?php esc_html_e('Email', 'wishlist-member'); ?>
                        <?php wishlistmember_instance()->tooltip(__('The email address of the member.', 'wishlist-member')); ?>
                    </th>
                    <th>
                        <?php esc_html_e('Status', 'wishlist-member'); ?>
                        <?php wishlistmember_instance()->tooltip(__('The current status of the member. The status can be Active or Invited.', 'wishlist-member')); ?>
                    </th>
                    <th></th>
                </tr>
            </thead>
            <tfoot><tr><td colspan="10"><p class="text-center"><?php esc_html_e('Search for Team Members using the form above', 'wishlist-member'); ?></p></td></tr></tfoot>
        </table>
    </div>
</div>
<script type="text/template" id="team-members-search-results">
    {% _.each(data.members, function(member) { %}
    <tr data-team-member-id="{%- member.id %}" data-team-invited="{%- +!member.active %}" data-team-id="{%- data.team_id %}" data-team-member-email="{%- member.email %}" class="button-hover">
        <td>
            {%- member.name %}
        </td>
        <td><a href="mailto:{%- member.email %}">{%- member.email %}</a></td>
        <td>
            {%- member.active ? wp.i18n.__('Active', 'wishlist-member') : wp.i18n.__('Invited', 'wishlist-member') %}
        </td>
        <td width="1">
            <div class="btn-group-action">
                <a href="#" class="remove-team-member-btn wlm-icons md-24 d-lg-inline d-md-inline" title="<?php esc_attr_e('Remove', 'wishlist-member'); ?>">delete</a>
            </div>
        </td>
    </tr>
    {% }) %}
</script>
<script>
$('body').on('wishlistmember-team-accounts:data-refreshed',function() {
    var options=[$('<option value=""></option>')];
    _.each(wlm_team_accounts_teams, function(teams){
        options.push($('<option value="'+teams[0].id+'" data-usage="('+wlm_team_accounts_children[teams[0].id].length+'/'+teams[0].max_children+')">'+teams[0].name+'</option>'));
    });
    $('select.team-members-dropdown').empty().append(options);
    $('select.team-members-dropdown.-with-usage option').each(function() {
        $(this).text($(this).text() + ' ' + $(this).data('usage'));
    });
    $('select.team-members-dropdown[data-selected]').each(function() {
        $(this).find('option[value="'+$(this).attr('data-selected')+'"]').attr('selected',true);
        $('#add-team-member')[0].team_id.value = $(this).attr('data-selected');
        $('.team-name').text(wlm_team_accounts_teams[$(this).attr('data-selected')][0].name);
    });
    $('select.team-members-dropdown').trigger('change.select2');
});
$('body').on('wishlistmember-team-accounts:member-edit-loaded',function() {
    $('#team-member-search-team').off('change.wlmta');
    $('#team-member-search-team').on('change.wlmta',function(e) {
        if(this.value) {
            $(this).attr('data-selected',this.value);
            $('#add-team-member [name="team_id"]').val(this.value);
            $('.team-name').text(wlm_team_accounts_teams[this.value][0].name);
            $('form#team-member-search').trigger('submit');
            $('#add-team-member-toggle').removeClass('d-none');
        }
    });
});
$('#team-member-search').on('submit', function(e) {
    e.preventDefault();
    var tbody =$('#team-members-search-result tbody');
    tbody.empty();
    var team_id = $('#team-member-search-team').val();
    $.post(
        WLM3VARS.ajaxurl,
        {
            action: 'wishlistmember_team_accounts_search_member',
            team_id: team_id,
            search: '',
            user_id: wlm_team_user,
            [WLM3VARS.nonce_field]: WLM3VARS.nonce
        },
        function(r) {
            if(r.success && r.data.members.length) {
                var tmpl = _.template( $( 'script#team-members-search-results' ).html(), {variable: 'data'} );
                tbody.append(
                    tmpl(
                        {
                            members : r.data.members,
                            team_id : team_id
                        }
                    )
                );
                $( 'tr[data-team-invited="0"] .remove-team-member-btn' ).do_confirm(
                    {
                        confirm_message : wp.i18n.__( 'Remove Team Member?', 'wishlist-member' ),
                        yes_button : wp.i18n.__( 'Remove', 'wishlist-member' ),
                        placement: 'right'
                    }
                ).on(
                    'yes.do_confirm',
                    function (e) {
                        var team_id = $(this).closest('tr').data('team-id');
                        var member_id = $(this).closest('tr').data('team-member-id');
                        $.post(
                            WLM3VARS.ajaxurl,
                            {
                                action: 'wishlistmember_team_accounts_remove_member',
                                team_id: team_id,
                                user_id: wlm_team_user,
                                member_id: member_id,
                                [WLM3VARS.nonce_field]: WLM3VARS.nonce
                            },
                            function(r) {
                                if(r.success) {
                                    wlm_team_accounts_get_team_data();
                                    $('#team-member-search').submit();
                                    $('#add-member-msg').show_message({message:r.data.message});
                                } else{
                                    $('#add-member-msg').show_message({message:r.data.message, type:'danger'});
                                }
                            }
                        )
                    }
                );
                $( 'tr[data-team-invited="1"] .remove-team-member-btn' ).do_confirm(
                    {
                        confirm_message : wp.i18n.__( 'Cancel invite?', 'wishlist-member' ),
                        yes_button : wp.i18n.__( 'Cancel', 'wishlist-member' ),
                        placement: 'right'
                    }
                ).on(
                    'yes.do_confirm',
                    function (e) {
                        var tr =$(this).closest('tr');
                        var team_id = tr.data('team-id');
                        var member_email = tr.data('team-member-email');
                        $.post(
                            WLM3VARS.ajaxurl,
                            {
                                action: 'wishlistmember_team_accounts_cancel_invite',
                                team_id: team_id,
                                user_id: wlm_team_user,
                                member_email: member_email,
                                [WLM3VARS.nonce_field]: WLM3VARS.nonce
                            },
                            function(r) {
                                if(r.success) {
                                    wlm_team_accounts_get_team_data();
                                    $('#team-member-search').submit();
                                    $('#add-member-msg').show_message({message:r.data.message});
                                } else{
                                    $('#add-member-msg').show_message({message:r.data.message, type:'danger'});
                                }
                            }
                        )
                    }
                );

            } else {
                tbody.append(
                    '<tr><td colspan="10">'+
                    '<p class="text-center">'+wp.i18n.__('No Team Members found', 'wishlist-member')+'</p>'+
                    '</td></tr>'
                );
            }
        }
    )
    return false;
});
$('#add-team-member').on('submit',function(e) {
    e.preventDefault();
    $.post(
        WLM3VARS.ajaxurl,
        {
            action: 'wishlistmember_team_accounts_add_member',
            team_id: this.team_id.value.trim(),
            email: this.email.value.trim(),
            method: this.method.value.trim(),
            user_id: wlm_team_user,
            [WLM3VARS.nonce_field]: WLM3VARS.nonce
        },
        function(r) {
            if(r.success) {
                $('#add-member-msg').show_message({message:r.data.message});
                $('#add-team-member')[0].reset();
                $('#add-team-member').collapse('hide').find(':input').trigger('change');
                wlm_team_accounts_get_team_data();
                $('#team-member-search').submit();
            } else {
                $('#add-member-msg').show_message({message:r.data.message, type:'danger'});
            }
        }
    );
    return false;
})
</script>
<style>
    #team-members-search-result tbody:not(:empty) ~ tfoot { display: none; }
    #team-members-search-result tbody:empty ~ thead { display: none; }
    #add-team-member.collapsing ~ a[href="#add-team-member"],
    #add-team-member.show ~ a[href="#add-team-member"] {
        display: none;
    }
</style>
