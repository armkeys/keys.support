<?php

/**
 * Form template processor.
 *
 * @package WishListMember/UI
 */

?>
<script type="text/template" id="wlm3-template-fg-default">
{% if(data.column != '') { %}
<div class="{%= data.column %}">
{% } %}
<div class="form-group {%= data.group_class %}">
    {% if(data.label != '') { %}
    <label for="{%= data.id %}">
        {%= data.label %}
        {% if(data.label_extra != '') { %}
            &nbsp;<small>{%= data.label_extra %}</small>
        {% } %}
        {% if(data.tooltip) { %}
            <?php $this->tooltip(__('___tooltip___', 'wishlist-member'), '___tooltip_size___'); ?>
        {% } %}
    </label>
    {% } %}

    {% if(data.has_addon) { %}
    <div class="input-group">
    {% } %}
    {% if(data.addon_left != '') { %}
    <div class="input-group-prepend">
        <div class="input-group-text">{%= data.addon_left %}</div>
    </div>
    {% } %}
    ___input___
    {% if(data.addon_right != '') { %}
    <div class="input-group-append">
        <div class="input-group-text">{%= data.addon_right %}</div>
    </div>
    {% } %}
    {% if(data.has_addon) { %}
    </div>
    {% } %}

    {% if(data.help_block != '') { %}
    <!-- Start: v4 -->
    <small class="form-text text-muted">{%= data.help_block %}</small>
    <!-- End: v4 -->
    {% } %}
</div>
{% if(data.column != '') { %}
</div>
{% } %}
</script>

<script type="text/template" id="wlm3-template-fg-radio-or-checkbox">
{% if(data.column != '') { %}
<div class="{%= data.column %}">
{% } %}
        <div class="form-check -with-tooltip {%= data.group_class %}">
            <label for="{%= data.id %}" class="cb-container">
                ___input___
                {% if(data.type == 'checkbox') { %}
                    <span class="marker checkmark"></span>
                {% } %}
                {% if(data.type == 'radio') { %}
                    <span class="marker btn-radio"></span>
                {% } %}
                    <span class="text-content">{%= data.label %}</span>
                {% if(data.tooltip) { %}
                    <?php $this->tooltip(__('___tooltip___', 'wishlist-member'), '___tooltip_size___'); ?>
                {% } %}
                {% if(data.more_link) { %}
                    &nbsp;&nbsp;<a href="{%= data.more_link %}" target="_blank">{% if(data.more_text) { %}{%= data.more_text %}{% } else { %}{%= data.more_link %}{% } %}</a>
                {% } %}
            </label>
        </div>
{% if(data.column != '') { %}
</div>
{% } %}
</script>

<script type="text/template" id="wlm3-template-fg-media">
{% if(data.column != '') { %}
<div class="{%= data.column %}">
{% } %}
<div class="form-group {%= data.group_class %}">
    {% if(data.label != '') { %}
    <label for="{%= data.id %}">
        {%= data.label %}
        {% if(data.tooltip) { %}
            <?php $this->tooltip(__('___tooltip___', 'wishlist-member'), '___tooltip_size___'); ?>
        {% } %}
    </label>
    {% } %}
    <div class="img-uploader {%- data.media_type %}">
        <div class="row">
            <div class="col text-center">
                {% if(data.media_type == 'file') { %}
                    <div class="file-container form-control text-muted text-left mt-1">{%- data.placeholder %}</div>
                {% } else { %}
                    <div class="img-container">
                        <img class="img-fluid" src="" alt="{%- data.placeholder %}">
                    </div>
                {% } %}
            </div>
            {% if(data.media_type == 'file') { %}
                <div class="col-12 col-md-auto text-center">
                    ___input___
                    <div class="content m-auto h-100 w-auto">
                        <button class="btn -primary -condensed float-right float-sm-none"><?php esc_html_e('Choose File', 'wishlist-member'); ?></button>
                        <a href="#" class="btn -bare -condensed img-clear-button float-left float-sm-none mt-sm-2 mr-sm-0 mr-3"><?php esc_html_e('Clear', 'wishlist-member'); ?></a>
                    </div>
                </div>
            {% } else { %}
                <div class="col-12 col-md-12">
                    ___input___
                    <div class="content m-auto h-100 w-auto pt-3 pt-md-2">
                        <button class="btn -primary -condensed float-right float-sm-none"><?php esc_html_e('Choose...', 'wishlist-member'); ?></button>
                        <a href="#" class="btn -bare -condensed img-clear-button float-right mt-sm-2 mr-sm-0 mr-3"><?php esc_html_e('Clear', 'wishlist-member'); ?></a>
                    </div>
                </div>
            {% } %}
        </div>
    </div>
</div>
{% if(data.column != '') { %}
</div>
{% } %}

</script>

<script type="text/javascript">
var wlm3_form_group = Backbone.View.extend({

    data: {},
    html: '',
    not_an_attribute: ['label', 'label_extra', 'group_class', 'help_block', 'column', 'addon_left', 'addon_right', 'options', 'grouped', 'tooltip', 'has_addon', 'more_link', 'more_text'],

    initialize: function(data) {
        this.data = $.extend({
            type : 'text',
            id : '',
            label : '',
            label_extra : '',
            value : '',
            class : '',
            group_class : '',
            help_block : '',
            column : '',
            addon_left : '',
            addon_right : '',
            checked_value : null,
            options : [],
            grouped : false,
            tooltip : '',
            more_link : '',
            more_text : '',
        }, data);
        this.data.class = 'form-control ' + this.data.class;
        if(this.data.id === '') this.data.id = this.data.name + '-id-' + (Math.random().toString(36).substring(2) + (new Date()).getTime().toString(36));
        this.render();
    },

    selectoptions: function(options, val, el) {
        if(typeof val == 'object') {
            val = val ? Object.values(val) : [];
        } else {
            val = [val];
        }
        $.each(options,function(i,o) {
            var option = $('<option/>');
            option.attr('value',o.value);
            option.append(o.text);
            if(val.includes(o.value) || val.includes(o.value.toString()) ) {
                option.attr('selected','selected');
            }
            el.append(option);
        });
    },

    render: function() {
        var tid = 'wlm3-template-fg-default';
        this.data.has_addon = this.data.addon_left || this.data.addon_right;
        switch(this.data.type) {
            case 'wlm3media':
                this.data.type = 'text';
                // This.data.class += ' copyable hidden-xs img-uploader-field';
                this.data.class += ' d-none img-uploader-field';
                if(!this.data.placeholder) {
                    this.data.placeholder = '<?php esc_html_e('Choose an Image', 'wishlist-member'); ?>';
                }
                var el = $('<input/>');
                el.prop('readonly', true);
                tid = 'wlm3-template-fg-media';
            break;
            case 'richtext':
                this.data.class += 'richtext';
                this.data.type = 'textarea';
            case 'textarea':
                var el = $('<textarea/>');
                el.append(this.data.value);
                delete this.data.value;
                delete this.data.type;
            break;
            case 'select':
                var el = $('<select/>');
                var options = this.data.options;
                delete this.data.options;
                var val = this.data.value;
                delete this.data.value;

                var _this = this;

                if(this.data.grouped) {
                    var first = true;
                    $.each(options, function(g, group){
                        if(group.options){
                            if(group.options.length) {
                                var optgroup = $('<optgroup/>').attr('label', group.name);
                                _this.selectoptions(group.options, val, optgroup);
                                el.append(optgroup);
                            }
                        }else if ('value' in group && 'text' in group){
                            _this.selectoptions([group], val, el);
                        }
                    });
                } else {
                    _this.selectoptions(options, val, el);
                }
                this.data.class += ' wlm-select';
            break;
            case 'toggle-adjacent-disable':
                this.data.class += ' disable-adjacent';
            case 'toggle-adjacent':
                this.data.class += ' wlm_toggle-adjacent';
            case 'toggle-switch':
                this.data.class += ' wlm_toggle-switch';
                this.data.type = 'checkbox';
            case 'checkbox':
            case 'radio':
                tid = 'wlm3-template-fg-radio-or-checkbox';
                this.data.class = this.data.class.replace('form-control','');
                this.data.class += ' form-check-input';
                var el = $('<input/>');
                if(this.data.checked_value == this.data.value) {
                    this.data.checked = 'checked';
                }
                delete this.data.checked_value;

            break;
            default:
                this.data['data-lpignore'] = 'true';
                var el = $('<input/>');
        }

        var fg = _.template($('script#' + tid).html(), {variable: 'data'});

        var not_an_attribute = this.not_an_attribute
        $.each(this.data,function(n,v) {
            if(not_an_attribute.indexOf(n.toLowerCase()) >= 0) return;
            // Unescape <select> data-* values.
            if(el[0].type.match(/^select\-/) && n && n.match(/^data\-/) && 'string' === typeof v) {
                v = _.unescape(v);
            }
            el.attr(n,v);
        });

        this.html = ($(fg(this.data))[0].outerHTML).replace('___input___',el[0].outerHTML);
        if(this.data.tooltip) {
            this.html = this.html.replace('___tooltip___', this.data.tooltip.replace(/"/g, '&quot;'));
        }
        this.html = this.html.replace('___tooltip_size___', this.data.tooltip_size ? this.data.tooltip_size : 'sm');
    }
});
</script>
