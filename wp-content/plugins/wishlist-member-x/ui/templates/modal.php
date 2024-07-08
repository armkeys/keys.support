<?php

/**
 * Modal template processor
 *
 * @package WishListMember/UI
 */

?>
<!-- Modal Underscores Template -->
<script type="text/template" id="wlm3-template-modal">
<div class="modal fade" id="{%= data.id %}" role="dialog" aria-labelledby="{%= data.label %}">
    <div class="modal-dialog {%= data.classes %}" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="{%= data.label %}-title">{%= data.title %}</h4>
                <div class="modal-toaster" style="display:none;"></div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                {%= data.body %}
            </div>
            {% _.each(data.footer, function(footer) { %}
            <div class="modal-footer">
                {%= footer %}
                {% if ( data.show_default_footer ) { data.show_default_footer = false; %}
                    <button type="button" data-dismiss="modal" class="btn -bare">
                        <span><?php esc_html_e('Close', 'wishlist-member'); ?></span>
                    </button>
                    <button type="button" class="save-button btn -primary">
                        <i class="wlm-icons">save</i>
                        <span><?php esc_html_e('Save', 'wishlist-member'); ?></span>
                    </button>
                    <button type="button" class="save-button -close btn -success">
                        <i class="wlm-icons">save</i>
                        <span>Save &amp; Close</span>
                    </button>
                {% } %}
            </div>
            {% }); %}
        </div>
        <div class="modal-loader-overlay-holder d-none">
            <div class="modal-loader-overlay text-center">
                <div class="row h-100">
                    <div class="col-sm-12 my-auto">
                        <div class="card card-block">
                            <img class="l-logo" src="<?php echo esc_attr(wishlistmember_instance()->plugin_url3); ?>/ui/images/wlm-opaque.png" alt="">
                            <img class="d-block mt-4" style="opacity: .5; margin: auto" src="<?php echo esc_attr(wishlistmember_instance()->plugin_url3); ?>/ui/images/wlm-loader03.gif" alt="">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</script>

<!-- Modal View -->
<script type="text/javascript">
var wlm3_modal = Backbone.View.extend({

    data: {},

    initialize: function(id, options) {
        this.id = id;
        var noop = function(){};
        this.options = {
            save_handler : noop,
            before_open : noop,
            after_open : noop,
            before_close : noop,
            after_close : noop
        };
        if(typeof options === 'object') {
            this.options = $.extend(this.options, options);
        }else if (typeof options === 'function'){
            this.options.save_handler = options;
        }
        this.render();
    },

    render: function() {
        var modal = _.template($('script#wlm3-template-modal').html(), {variable: 'data'});
        var footers = [];
        $(this.id + ' .footer').each(function() {
            footers.push($(this).html());
        });
        this.data = {
            id : $(this.id).attr('data-id'),
            label : $(this.id).attr('data-label'),
            title : $(this.id).attr('data-title'),
            body : $(this.id + ' .body').html(),
            footer : footers,
            classes : $(this.id).attr('data-classes'),
            show_default_footer : $(this.id).attr('data-show-default-footer')
        }

        if(this.data.show_default_footer && this.data.footer.length < 1) {
            this.data.footer.push('');
        }
        var xid = '#'+this.data.id;
        $(this.id).replaceWith(modal(this.data));

        $(xid).find(':input').attr('data-lpignore', 'true');
        $(xid).find('.nav-tabs a:not([data-target])').each(function() {
            $(this).attr('data-target', this.hash);
            this.href = '#';
        });

        $(xid).on('show.bs.modal', this.options.before_open);
        $(xid).on('show.bs.modal', {that : this}, function(e) {
            $('.modal-loader-overlay-holder').addClass('d-none');
            $('.modal-toaster').hide();
            $('.wlm-toaster').hide();
            $('.app-container').css('max-width', $('.app-container').width());
            parent.jQuery('html, body').css({
                overflow: 'hidden',
                height: '100%'
            });

            // Reinitialize tinymce inside modals.
            // This ensures that tinymce editors are not orphaned and values are set.
            tinymce.remove('#' + this.id + ' .richtext');
            $('#' + this.id + ' .mce-tinymce').remove();
            try {
                wlm.richtext();
            } catch(e) {}
        });
        $(xid).on('shown.bs.modal', this.options.after_open);
        $(xid).on('hide.bs.modal', this.options.before_close);
        $(xid).on('hidden.bs.modal', this.options.after_close);
        $(xid).on('hidden.bs.modal', {that : this}, function(e) {
            $('.app-container').css('max-width', '');
            parent.jQuery('html, body').css({
                overflow: '',
                height: ''
            });
        });
        $('#'+this.data.id+' .save-button').click({modal:this}, this.options.save_handler);
    },

    open: function() {
        $('#'+this.data.id).modal('show');
    },

    close: function() {
        $('#'+this.data.id).modal('hide');
    }
});
</script>
