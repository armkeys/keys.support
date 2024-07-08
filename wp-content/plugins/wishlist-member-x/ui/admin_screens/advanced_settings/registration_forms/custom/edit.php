<style type="text/css">
    .panel-open .hide-on-open {
        display: none;
    }
    .panel .show-on-open {
        display: none;
    }
    .panel.panel-open .show-on-open {
        display: initial;
    }
</style>
<div id="regforms-create" style="display: none">
    <form id="regform-form">
         <div class="page-header -no-background">
            <div class="large-form">
                <div class="row">
                    <div class="col-sm-auto col-12">
                        <h2 class="page-title"><?php esc_html_e('Form Name', 'wishlist-member'); ?></h2>
                    </div>
                    <div class="col-sm-7 col-12">
                        <input name="form_name" placeholder="Enter Form Name" data-initial="" required="required" class="form-control input-lg" type="text">
                        <textarea name="rfdata" style="display:none"></textarea>
                        <input type = "hidden" name="form_fields">
                        <input type = "hidden" name="form_required">
                        <input type = "hidden" name="form_id">
                        <input type="hidden" name="action" value="admin_actions" />
                        <input type="hidden" name="WishListMemberAction" value="save_custom_registration_form" />

                    </div>
                </div>
            </div>
        </div>
        <div id="all-form-data">
            <h4 class="text-center"><?php esc_html_e('Drag and drop the desired fields from the list on the left to customize the registration form.', 'wishlist-member'); ?></h4>
            <br>
            <div class="registration-form">
                <div class="row">
                    <?php require 'edit/draggable_fields.php'; ?>
                    <div class="col-md-8 col-sm-8 col-xs-8 no-padding-left">
                        <div class="chosen-fields pb-0"></div>

                        <div class="pull-right mr-3">
                            <a href="#" class="btn -bare cancel">
                                <span><?php esc_html_e('Close', 'wishlist-member'); ?></span>
                            </a>
                            <a href="#" class="save-and-continue btn -primary">
                                <i class="wlm-icons">save</i>
                                <span><?php esc_html_e('Save', 'wishlist-member'); ?></span>
                            </a>
                            &nbsp;
                            <a href="#" class="save-and-close btn -success">
                                <i class="wlm-icons">save</i>
                                <span>Save &amp; Close</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php
    require 'edit/field_template.php';
    require 'edit/field_edit_template.php';
?>
