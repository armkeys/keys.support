<?php

/**
 * Team invite Email template Modal View
 *
 * @package WishListMember/Features/TeamAccounts
 */

?>
<div
    id="email-notification-team-invite-modal"
    data-id="email-notification-team-invite"
    data-label="email-notification-team-invite"
    data-title="<?php esc_attr_e('Team Invite Email Template', 'wishlist-member'); ?>"
    data-classes="modal-lg"
    data-show-default-footer="1"
    style="display:none">
    <div class="body">
        <form class="row">
            <template class="wlm3-form-group">
                {
                    addon_left : '<?php echo esc_js(__('Sender Email', 'wishlist-member')); ?>',
                    group_class : '-label-addon mb-2',
                    type : 'text',
                    name : 'team-accounts/team_invite_email_sender_email',
                    column: 'col-md-12',
                    value : '<?php echo esc_js(wishlistmember_instance()->get_option('team-accounts/team_invite_email_sender_email')); ?>',
                }
            </template>
            <template class="wlm3-form-group">
                {
                    addon_left : '<?php echo esc_js(__('Sender Name', 'wishlist-member')); ?>',
                    group_class : '-label-addon mb-2',
                    type : 'text',
                    name : 'team-accounts/team_invite_email_sender_name',
                    column: 'col-md-12',
                    value : '<?php echo esc_js(wishlistmember_instance()->get_option('team-accounts/team_invite_email_sender_name')); ?>',
                }
            </template>
            <template class="wlm3-form-group">
                {
                    addon_left : '<?php echo esc_js(__('Subject', 'wishlist-member')); ?>',
                    group_class : '-label-addon mb-2',
                    type : 'text',
                    name : 'team-accounts/team_invite_email_subject',
                    column: 'col-md-12',
                    class: 'email-subject',
                    value : '<?php echo esc_js(wishlistmember_instance()->get_option('team-accounts/team_invite_email_subject')); ?>',
                }
            </template>
            <template class="wlm3-form-group">
                {
                    name : 'team-accounts/team_invite_email_message',
                    id : 'team-accounts-email-message',
                    'data-default-message' : '<?php echo esc_js(file_get_contents(WLM_PRO_TEAM_ACCOUNTS_DIR . '/templates/invite-email.txt')); ?>',
                    type : 'textarea',
                    class : 'richtext',
                    column: 'col-md-12',
                    group_class : 'mb-2',
                    value : '<?php echo esc_js(wishlistmember_instance()->get_option('team-accounts/team_invite_email_message')); ?>',
                }
            </template>
            <div class="col-md-12">
            <button class="btn -default -condensed team-invite-email-reset-button" type="button" data-target="team-accounts-email-message"><?php esc_html_e('Reset to Default Message', 'wishlist-member'); ?></button>
                <template class="wlm3-form-group">
                    {
                        type : 'select',
                        column : 'col-md-5 pull-right no-margin no-padding',
                        'data-placeholder' : '<?php echo esc_js(__('Insert Merge Codes', 'wishlist-member')); ?>',
                        group_class : 'shortcode_inserter mb-0',
                        style : 'width: 100%',
                        options : [
                            { value: '', text: ''},
                            { value: '<?php echo esc_js(__('[team_plan_name]', 'wishlist-member')); ?>', text: '<?php echo esc_js(__('Team Plan', 'wishlist-member')); ?>' },
                            { value: '<?php echo esc_js(__('[site_name]', 'wishlist-member')); ?>', text: '<?php echo esc_js(__('Site Name', 'wishlist-member')); ?>' },
                            { value: '<?php echo esc_js(__('[email]', 'wishlist-member')); ?>', text: '<?php echo esc_js(__('Email', 'wishlist-member')); ?>' },
                            { value: '<?php echo esc_js(__('[accept_invite text="Accept Invite"]', 'wishlist-member')); ?>', text: '<?php echo esc_js(__('Accept Invite Link', 'wishlist-member')); ?>' },
                        ],
                        class : 'insert_text_at_caret',
                        'data-target' : '[name="team-accounts/team_invite_email_message"]'
                    }
                </template>
            </div>

            <input type="hidden" name="action" value="wishlistmember_team_accounts_save_settings">
            <?php print_wlm_nonce_field(); ?>
        </form>
    </div>
</div>
<script>
$(function() {
    new wlm3_modal(
        '#email-notification-team-invite-modal', {
            save_handler: function(event) {
                $form = $(this).closest('.modal').find('form');
                var data = $form.serializeArray();
                $.post(
                    ajaxurl,
                    data,
                    function(r) {
                        if(r.success) {
                            for(const x in r.data) {
                                $form.find(':input[name="' + x + '"]').attr('value',r.data[x]).trigger('change');
                            }
                        }
                    }
                ).done(function() {
                    if($(event.target).hasClass('-close')) {
                        event.data.modal.close();
                    }
                })
            },
            before_close: function(event){
                $(this).find('form')[0].reset();
            }
        }
    );
});
</script>
