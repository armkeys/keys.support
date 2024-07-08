<!-- start: v4 -->
<script type="text/template" id="regform-edit-item-template">
<div class="panel-group" id="regform-edit-accordion" role="tablist" aria-multiselectable="true">
{% _.each(data.fields, function(field) { %}
    <div class="panel panel-default field_{%- (field.type).replace('field_', '') %} {%- (field.lightbox) ? 'lightbox_tos' : '' %}" {% if('required' in data && data.required.indexOf(field.name) > -1) { print ('data-required="'+field.name+'"'); } %} data-field-type="{%- field.type %}" data-name-editable="{%= Math.abs(!!field.name_editable) %}">
        <div class="panel-heading button-hover" role="tab" id="{%- field.heading_id %}">
            <div class="row">
                {% switch(field.type) { case 'field_special_header': %}
                <div class="col-md-9">
                    <h3 class="the-label">{%- field.label %}</h3>
                </div>
                {% break; case 'field_special_paragraph': %}
                <div class="col-md-9">
                    <small class="desc form-text text-muted">{%= field.description %}</small>
                </div>
                {% break; case 'field_tos': %}
                <div class="col-md-9">
                    <div class="form-element-container">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="{%- field.name %}" class="fld" value="{%- field.default %}">
                                {%- field.default %}
                            </label>
                        </div>
                    </div>
                    <small class="desc form-text text-muted mb-2" style="background: #fff; max-height:200px; overflow: auto; padding: 0.5em; border: 1px solid #DBE4EE">{%= field.description %}</small>
                </div>              
                {% break; default: %}
                <div class="col-md-3 col-sm-4">
                    <label class="pull-right the-label" for="">{%- field.label %}{%- field.labelh %}</label>
                </div>
                <div class="col-md-6 col-sm-5">
                    <div class="form-element-container">
                        {%= field.input %}
                        {% if(field.type == 'password') { %}
                        <br>
                        {%= field.input.replace('password1', 'password2') %}
                        {% } %}
                    </div>
                    <label class="inputh">{%= field.inputh %}</label>
                    <small class="desc form-text text-muted">{%= field.description %}</small>
                </div>
                {% } %}
                <div class="col-md-3 col-sm-3">
                    <div class="btn-group-action pull-right hide-on-open">
                        <a role="button" data-toggle="collapse" data-parent="#regform-edit-accordion" href="#{%= field.collapse_id %}" aria-expanded="true" aria-controls="{%= field.collapse_id %}" title="Edit Field">
                            <i class="wlm-icons">edit</i>
                        </a>
                        {% if(['username','email','password1'].indexOf(field.name) < 0 && field.type != 'submit') { %}
                        {% if(field.name_editable) { %}
                        <a href="#" role="button" class="-clone-field" title="Duplicate Field">
                            <i class="wlm-icons">queue</i>
                        </a>
                        {% } %}
                        <a href="#" role="button" class="-delete-field">
                            <i class="wlm-icons">delete</i>
                        </a>
                        {% } %}
                    </div>
                    <div class="btn-group-action pull-right show-on-open" style="visibility: visible">
                        <a role="button" data-toggle="collapse" data-parent="#regform-edit-accordion" href="#{%= field.collapse_id %}"><i class="wlm-icons">close</i></a>
                    </div>
                </div>
                {% if ("helper" in field) { %}
                <div class="col-md-12 small text-muted">
                    {%- field.helper %}
                </div>
                {% } %}

            </div>
        </div>
        <div id="{%= field.collapse_id %}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="{%- field.heading_id %}">
            <!-- Panel Body -->
            <div class="panel-body"></div>
            <!-- Panel Footer -->
            <div class="panel-footer -content-footer">
                <div class="row">
                    <div class="col-lg-12 text-center">
                        <a data-toggle="collapse" data-parent="#regform-edit-accordion" href="#{%= field.collapse_id %}" class="btn -default">
                            <i class="wlm-icons">close</i>
                            <span><?php esc_html_e('Close', 'wishlist-member'); ?></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
{% }) %}
</div>
</script>
<!-- end: v4 -->
