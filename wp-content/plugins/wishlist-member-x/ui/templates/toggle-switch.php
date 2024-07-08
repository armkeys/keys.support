<?php

/**
 * Toggle switch template processor
 *
 * @package WishListMember/UI
 */

?>
<script type="text/template" id="wlm3-template-toggle-switch">
    <div class="switch">
        <div class="switch-left">
            <div class="form-group">
                <label class="switch-light switch-wlm" onclick="">
                    ___input___
                    <span>
                        <span><i class="wlm-icons md-18 ico-check">check</i></span>
                        <span><i class="wlm-icons md-18 ico-close">close</i></span>
                        <a></a>
                    </span>
                </label>
            </div>
        </div>
        <div class="switch-body">
            {% if(data.label) { %}
            {%= data.label %}
            {% } %}
            {% if(data.tooltip) { %}
            <?php wishlistmember_instance()->tooltip(__('___tooltip___', 'wishlist-member')); ?>
            {% } %}
        </div>
    </div>
</script>
<script type="text/javascript">
var wlm3_toggle_switch = Backbone.View.extend({

    data: {},
    html: '',

    initialize: function(data) {
        this.data = $.extend({
            id : '',
            name: '',
            value : '',
            class : '',
            checked : false,
            label : '',
            tooltip : '',
        }, data);
        this.data.type="checkbox";
        if(this.data.id === '') this.data.id = this.data.name + '-id' + (new Date().getTime());
        this.render();
    },

    render: function() {
        var fg = _.template($('script#wlm3-template-toggle-switch').html(), {variable: 'data'});
        var el = $('<input/>');
        $.each(this.data,function(n,v) {
            el.attr(n,v);
        });
        el.addClass('is-toggle-switch');
        this.html = ($(fg(this.data))[0].outerHTML)
            .replace('___input___', el[0].outerHTML)
            .replace('___tooltip___', this.data.tooltip.replace(/"/g, '&quot;'));
    }
});
</script>
