<div class="content-wrapper">
    <div id="legacy_reg_fields">
        <div class="row">
            <div class="col">
                <p><?php esc_html_e('The options below can be used if the Legacy style is set using the Registration Form Style dropdown in the Advanced Options > Registrations > Registration Settings section of WishList Member.', 'wishlist-member'); ?></p>
            </div>
        </div>
        <div class="row">
            <template class="wlm3-form-group">{
                name : 'reg_instructions_new',
                type : 'textarea',
                column: 'col-md-12',
                label: '<?php echo esc_js(__('New Member Registration > Full Instructions', 'wishlist-member')); ?>',
                tooltip : '<?php echo esc_js(__('These instructions appear on the top of the Registration Form when the Legacy style is applied.', 'wishlist-member')); ?>',
            }</template>
        </div>
        <div class="row mb-5">
            <div class="col-sm-5 col-md-5">
                <button class="btn -default -condensed legacy-fields-reset" data-target="reg_instructions_new"><?php esc_html_e('Reset to Default', 'wishlist-member'); ?></button>
            </div>
            <template class="wlm3-form-group">{
                type : 'select',
                column : 'col-sm-7 col-md-7',
                'data-placeholder' : '<?php echo esc_js(__('Insert Merge Codes', 'wishlist-member')); ?>',
                group_class : 'shortcode_inserter mb-0',
                style : 'width: 100%',
                options : [{value : '', text : ''}, { value : '[level]', text : 'Membership Level' }, { value : '[newlink]', text : 'New Member Link' }, { value : '[existinglink]', text : 'Existing Member Link' }],
                class : 'insert_text_at_caret',
                'data-target' : '[name=reg_instructions_new]'
            }</template>            
        </div>
        <div class="row">
            <template class="wlm3-form-group">{
                name : 'reg_instructions_new_noexisting',
                type : 'textarea',
                column: 'col-md-12',
                label: '<?php echo esc_js(__('New Member Registration > Instructions if "Existing Users Link" is Disabled', 'wishlist-member')); ?>',
                tooltip : '<?php echo esc_js(__('These instructions appear on the top of the Registration Form when the Legacy style is applied if the Existing Users Link is disabled.', 'wishlist-member')); ?>',
            }</template>            
        </div>
        <div class="row mb-5">
            <div class="col-sm-5">
                <button class="btn -default -condensed legacy-fields-reset" data-target="reg_instructions_new_noexisting"><?php esc_html_e('Reset to Default', 'wishlist-member'); ?></button>
            </div>
            <template class="wlm3-form-group">{
                type : 'select',
                column : 'col-sm-7',
                'data-placeholder' : '<?php echo esc_js(__('Insert Merge Codes', 'wishlist-member')); ?>',
                group_class : 'shortcode_inserter mb-0',
                style : 'width: 100%',
                options : [{value : '', text : ''}, { value : '[level]', text : 'Membership Level' }, { value : '[newlink]', text : 'New Member Link' }, { value : '[existinglink]', text : 'Existing Member Link' }],
                class : 'insert_text_at_caret',
                'data-target' : '[name=reg_instructions_new_noexisting]'
            }</template>
        </div>
        <div class="row">
            <template class="wlm3-form-group">{
                name : 'reg_instructions_existing',
                type : 'textarea',
                column: 'col-md-12',
                label: '<?php echo esc_js(__('Existing Member Registration > Full Instructions', 'wishlist-member')); ?>',
                tooltip : '<?php echo esc_js(__('These instructions appear on the top of the Registration Form when the Legacy style is applied if the Existing Users Link is used.', 'wishlist-member')); ?>',
            }</template>            
        </div>
        <div class="row mb-5">
            <div class="col-sm-5">
                <button class="btn -default -condensed legacy-fields-reset" data-target="reg_instructions_existing"><?php esc_html_e('Reset to Default', 'wishlist-member'); ?></button>
            </div>
            <template class="wlm3-form-group">{
                type : 'select',
                column : 'col-sm-7',
                'data-placeholder' : '<?php echo esc_js(__('Insert Merge Codes', 'wishlist-member')); ?>',
                group_class : 'shortcode_inserter mb-0',
                style : 'width: 100%',
                options : [{value : '', text : ''}, { value : '[level]', text : 'Membership Level' }, { value : '[newlink]', text : 'New Member Link' }, { value : '[existinglink]', text : 'Existing Member Link' }],
                class : 'insert_text_at_caret',
                'data-target' : '[name=reg_instructions_existing]'
            }</template>            
        </div>
        <input type="hidden" name="action" value="admin_actions" />
        <input type="hidden" name="WishListMemberAction" value="save" />
        <div class="panel-footer -content-footer">
            <div class="row">
                <div class="col-lg-12 text-right">
                    <a href="#" class="btn -primary save-settings">
                        <i class="wlm-icons">save</i>
                        <span class="text"><?php esc_html_e('Save', 'wishlist-member'); ?></span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
    require $this->legacy_wlm_dir . '/core/InitialValues.php';
?>
<script type="text/javascript">
    var $reset_data = {
        reg_instructions_new : <?php echo json_encode($wishlist_member_initial_data['reg_instructions_new']); ?>,
        reg_instructions_new_noexisting : <?php echo json_encode($wishlist_member_initial_data['reg_instructions_new_noexisting']); ?>,
        reg_instructions_existing : <?php echo json_encode($wishlist_member_initial_data['reg_instructions_existing']); ?>
    }
    var $data = {
        reg_instructions_new : <?php echo json_encode($this->get_option('reg_instructions_new')); ?>,
        reg_instructions_new_noexisting : <?php echo json_encode($this->get_option('reg_instructions_new_noexisting')); ?>,
        reg_instructions_existing : <?php echo json_encode($this->get_option('reg_instructions_existing')); ?>
    }
    $(function() {
        $( '#legacy_reg_fields' ).set_form_data( $data );
        $( 'body' ).off( '.wlm3legacyfields' );
        $( 'body' ).on( 'click.wlm3legacyfields', '.save-settings', function() {
            var $btn = $( this );
            $( '#legacy_reg_fields' ).save_settings( {
        on_init: function( $me, $data) {
            $btn.disable_button( { disable : true, icon : 'update' } );
        },
        on_done: function( $me, $data) {
            $btn.disable_button( { disable : false, icon : 'save' } );
              $( '.wlm-message-holder' ).show_message();
        }
            } );
        } );
        $( '.legacy-fields-reset' ).do_confirm( { placement : 'right', confirm_message : '<?php echo esc_js(__('Reset to Default?', 'wishlist-member')); ?>', yes_button : '<?php echo esc_js(__('Reset', 'wishlist-member')); ?>' } ).on( 'yes.do_confirm', function() {
            var target = $( this ).data( 'target' );
            $( '[name="' + target + '"]' ).val( $reset_data[ target ] );
            $( '.save-settings' ).trigger( 'click.wlm3legacyfields' );
            return false;
        } );
    });
</script>
