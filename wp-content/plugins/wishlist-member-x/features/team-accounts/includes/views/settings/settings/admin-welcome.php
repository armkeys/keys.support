<?php

/**
 * Team invite Email template Modal View
 *
 * @package WishListMember/Features/TeamAccounts
 */

use function WishListMember\Features\Team_Accounts\default_admin_join_message;

?>
<div
    id="team-accounts-admin-welcome-modal"
    data-id="team-accounts-admin-welcome"
    data-label="team-accounts-admin-welcome"
    data-title="<?php esc_attr_e(' Team Admin Welcome Message', 'wishlist-member'); ?>"
    data-classes="modal-lg"
    data-show-default-footer="1"
    style="display:none">
    <div class="body">
        <form class="row">
            <div class="col-12">
                <p><?php esc_html_e('This message will be displayed when a user becomes a Team Admin.', 'wishlist-member'); ?></p>
            </div>
            <template class="wlm3-form-group">
                {
                    name : 'team-accounts/admin-join-message',
                    id : 'team-accounts-admin-welcome-message',
                    'data-default-message' : '<?php echo esc_js(default_admin_join_message()); ?>',
                    type : 'textarea',
                    class : 'richtext',
                    column: 'col-md-12',
                    group_class : 'mb-2',
                    value : '<?php echo esc_js(wlm_or(wishlistmember_instance()->get_option('team-accounts/admin-join-message'), default_admin_join_message())); ?>',
                }
            </template>
            <div class="col-md-12">
                <button class="btn -default -condensed admin-welcome-reset-button" type="button" data-target="team-accounts-admin-welcome-message"><?php esc_html_e('Reset to Default Message', 'wishlist-member'); ?></button>
                <template class="wlm3-form-group">
                    {
                        type : 'select',
                        column : 'col-md-5 pull-right no-margin no-padding',
                        'data-placeholder' : '<?php echo esc_js(__('Insert Merge Codes', 'wishlist-member')); ?>',
                        group_class : 'shortcode_inserter mb-0',
                        style : 'width: 100%',
                        grouped: true,
                        options : [
                            {value:'',text:''},
                            {
                                name: '<?php echo esc_js(__('Team Accounts', 'wishlist-member')); ?>',
                                options: [
                                    { value: '[wlm_managed_team_plan_name]', text: '<?php echo esc_js(__('Team Name', 'wishlist-member')); ?>' },
                                ]
                            }
                        ].concat(get_merge_codes(null,true)),
                        class : 'insert_text_at_caret',
                        'data-target' : '[name="team-accounts/admin-join-message"]'
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
        '#team-accounts-admin-welcome-modal', {
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
                            $( '.wlm-message-holder' ).show_message( { type : 'success' } );
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
