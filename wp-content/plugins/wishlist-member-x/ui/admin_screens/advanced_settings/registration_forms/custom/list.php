<div id="regforms-list" style="display: none">
    <div class="row">
        <div class="col-md-12">
            <p>
                <?php esc_html_e('By default, WishList Member will create a standard registration form automatically for each level.', 'wishlist-member'); ?>
            </p>
            <p>
                <?php esc_html_e('Optionally, a custom registration form can be created. Custom registration forms can be helpful when additional information needs to be collected during the registration process.', 'wishlist-member'); ?>
            </p>
            <p>
                <?php
                printf(
                    wp_kses_data(
                        // Translators: 1: URL to Setup > Levels.
                        __('Custom registration forms created can be applied to a level by going to <a href="%s" target="_blank">Setup &gt; Levels</a>. Select the Level and go to the Registrations tab. Turn on the "Enable Custom Registration Form" and select the custom registration form from the list.', 'wishlist-member')
                    ),
                    esc_url(
                        add_query_arg(
                            [
                                'page' => 'WishListMember',
                                'wl'   => 'setup/levels',
                            ],
                            admin_url('admin.php')
                        )
                    )
                );
                ?>
            </p>
            <hr>
        </div>              
        <div class="col-md-12">
            <a href="#editform-new" class="btn -success" target="_parent">
                <i class="wlm-icons">add</i>
                <span><?php esc_html_e('Add Custom Registration Form', 'wishlist-member'); ?></span>
            </a>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <br>
            <div class="table-wrapper table-responsive">
                <table class="table table-striped table-condensed d-none" id="custom-registration-forms-list">
                    <colgroup>
                        <col>
                        <col width="20">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Custom Registration Forms</th>
                            <th width="10%" class="text-center"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="2">
                                <div class="text-center"><?php esc_html_e('Loading...', 'wishlist-member'); ?></div>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2">
                                <div class="text-center"><?php esc_html_e('There are no custom registration forms', 'wishlist-member'); ?></div>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<script type="text/template" id="custom-registration-forms-list-template">
    {% _.each(data.forms, function(form) { %}
    <tr class="button-hover" data-id="{%- form.option_name %}" data-sort="{%- form.ID %}">
        <td>
            <a target="_parent" href="#editform-{%- form.option_name %}">{%- form.option_value.form_name %}</a>
        </td>
        <td class="no-padding text-right">
            <div class="btn-group-action" style="min-width: 82px">
                <a target="_parent" href="#editform-{%- form.option_name %}" title="Edit Custom Registration Form" class="btn wlm-icons md-24 -icon-only">
                    <span>edit</span>
                </a>
                <a href="#" title="Duplicate Custom Registration Form" class="btn wlm-icons md-24 -icon-only -clone-btn">
                    <span>content_copy</span>
                </a>
                {% if(!(form.option_name in wpm_used_forms)) { %}
                <a href="#" class="btn wlm-icons md-24 -icon-only -del-btn">
                    <span title="Delete Custom Registration Form">delete</span>
                </a>
                {% } else { %}
                <a href="#" class="btn wlm-icons md-24 -icon-only -no-delete -disabled" data-placement="left" title="This form cannot be deleted as it is used by the following membership level(s): {%- wpm_used_forms[form.option_name].join(', ') %}">
                    <span>delete</span>
                </a>
                {% } %}
            </div>
        </td>
    </tr>
    {% }); %}
</script>
