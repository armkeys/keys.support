<script type="text/template" id="regform-edit-item-fields-template">
<h4><?php esc_html_e('Edit Field', 'wishlist-member'); ?></h4>
<div class="row">
    {% if ("label" in data && "name" in data) { %}
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Label</label>
            <input type="text" class="form-control -edit-label" value="{%- data.label %}">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Name</label>
            <input type="text" class="form-control -edit-name" value="{%- data.name %}" {% if(data.name_editable === false) { %} readonly="readonly" {% } %}>
        </div>
    </div>
    {% } else if ("label" in data) { %}
    <div class="col-md-12">
        <div class="form-group">
            <label for="">
                {%
                    switch (data.type) {
                        case 'field_special_header':
                            print('Header Text');
                            break;
                        default:
                            print('Label');
                    }
                %}
            </label>
            <input type="text" class="form-control -edit-label" value="{%- data.label %}">
        </div>
    </div>
    {% } else if ("name" in data) { %}
    <div class="col-md-12">
        <div class="form-group">
            <label for="">Name</label>
            <input type="text" class="form-control -edit-name" value="{%- data.name %}" {% if(data.name_editable === false) { %} readonly="readonly" {% } %}>
        </div>
    </div>
    {% } %}
</div>
<div class="row">
    {% if ("height" in data) { %}

    <div class="col-md-8">
        <div class="form-group">
            <label for="">Default</label>
            <input type="text" class="form-control -edit-default" value="{%- data.default %}">
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label for="">Width</label>
            <input type="text" class="form-control -edit-width" value="{%- data.width %}">
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label for="">Height</label>
            <input type="text" class="form-control -edit-height" value="{%- data.height %}">
        </div>
    </div>
    {% } else if ("width" in data) { %}
    
    {% if(['password1','username','email'].indexOf(data.name) < 0) { %}
    <div class="col-md-10">
        <div class="form-group">
            <label for="">Default</label>
            <input type="text" class="form-control -edit-default" value="{%- data.default %}">
        </div>
    </div>
    {% } %}

    <div class="col-md-2">
        <div class="form-group">
            <label for="">Width</label>
            <input type="text" class="form-control -edit-width" value="{%- data.width %}">
        </div>
    </div>
    {% } else if ("default" in data) { %}
    <div class="col-md-12">
        <div class="form-group">
            <label for="">
                {%
                    switch(data.type) {
                        case 'submit':
                            print ('Text for Submit Button');
                            break;
                        case 'field_tos':
                            print ('Text for Checkbox');
                            break;
                        case 'hidden':
                            print ('Value');
                            break;
                        default:
                            print ('Default Value');
                    }
                %}
            </label>
            <input type="text" class="form-control -edit-default" value="{%- data.default %}">
        </div>
    </div>
    {% } %}
</div>
{% if ("items" in data && data.type != 'field_tos') { %}
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label for="">Items</label>
            <textarea class="form-control -edit-items" name="" id="" cols="10" rows="5" {% if(data.options_editable === false) { %} readonly="readonly" {% } %}>{%= data.items.join("\n") %}</textarea>
        </div>
    </div>
</div>
{% } %}
{% if ("description" in data && data.type != 'hidden') { %}
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label for="">
                {%
                    switch (data.type) {
                        case 'field_special_paragraph':
                            print('Paragraph HTML');
                            break;
                        case 'field_tos':
                            print('Terms of Service HTML');
                            break;
                        default:
                            print('Description');
                    }
                %}
            </label>
            <textarea class="form-control -edit-description" name="" id="" cols="10" rows="5">{%- data.description %}</textarea>
        </div>
    </div>
</div>
{% } %}
{% if (data.has_required === true) { %}
<div class="row">
    <div class="col-md-12">
        <div class="checkbox">
            <label><input type="checkbox" class="-edit-required" value="{%- data.name %}" {% if(data.required) print('checked="checked"') %}> Required Field</label>
        </div>
    </div>
</div>
{% } %}
{% if (data.type === 'field_tos') { %}
<div class="row">
    <div class="col-md-12">
        <div class="checkbox">
            <label><input type="checkbox" class="-edit-lightbox" value="{%- data.name %}" {% if(data.lightbox) print('checked="checked"') %}> Show Terms of Service in Lightbox</label>
        </div>
    </div>
</div>
{% } %}

</script>
