<p><?php esc_html_e('Users can be Added to a List and Group(s) in MailChimp when they join or are added to a Level in WishList Member. Select a Level below to edit its corresponding MailChimp settings. ', 'wishlist-member'); ?></p>
<div id="mailchimp-lists-table" class="table-wrapper"></div>
<script type="text/template" id="mailchimp-lists-template">
    <table class="table table-striped">
        <colgroup>
            <col>
            <col width="33%">
            <col width="33%">
            <col width="1%">
        </colgroup>
        <thead>
            <tr>
                <th>
                    <?php esc_html_e('Name ', 'wishlist-member'); ?>
                    <?php wishlistmember_instance()->tooltip(__('The name of the Level. Click to edit its corresponding MailChimp settings. ', 'wishlist-member'), 'lg'); ?>
                </th>
                <th>
                    <?php esc_html_e('List Options ', 'wishlist-member'); ?>
                    <?php wishlistmember_instance()->tooltip(__('The MailChimp Email List and Group(s) a user will be added to if they join or are added to the Level. ', 'wishlist-member'), 'lg'); ?>
                </th>
                <th>
                    <?php esc_html_e('Action if Removed from Level ', 'wishlist-member'); ?>
                    <?php wishlistmember_instance()->tooltip(__('The Action set to occur if a user is Removed from the Level.', 'wishlist-member'), 'lg'); ?>
                </th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            {% _.each(data.levels, function(level) { %}
            <tr class="button-hover">
                <td class="align-baseline"><a href="#" data-toggle="modal" data-target="#mailchimp-lists-modal-{%- level.id %}">{%= level.name %}</a></td>
                <td class="align-baseline">
                    <div id="mailchimp-list-{%- level.id %}"></div>
                    <div class="mt-0 mailchimp-interest" id="mailchimp-interest-{%- level.id %}"></div>
                </td>
                <td class="align-baseline">
                    <div id="mailchimp-remove-{%- level.id %}"></div>
                    <div class="mt-0 mailchimp-interest" id="mailchimp-interestr-{%- level.id %}"></div>
                </td>
                <td class="text-right align-baseline">
                    <div class="btn-group-action">
                        <a href="#" data-toggle="modal" data-target="#mailchimp-lists-modal-{%- level.id %}" class="btn -tags-btn" title="Edit"><i class="wlm-icons md-24">edit</i></a>
                    </div>
                </td>
            </tr>
            {% }); %}
        </tbody>
    </table>
</script>

<style type="text/css">
    .mailchimp-interest {
        font-style: italic;
    }
    .mailchimp-interest::before {
        content: 'Interest Groups: ';
    }
    .mailchimp-interest:empty {
        display: none;
    }
</style>

<script type="text/javascript">
    $('#mailchimp-lists-table').empty();
    $.each(all_levels, function(k, v) {
        var data = {
            label : post_types[k].labels.name,
            levels : v
        }
        var tmpl = _.template($('script#mailchimp-lists-template').html(), {variable: 'data'});
        var html = tmpl(data);
        $('#mailchimp-lists-table').append(html);
        return false;
    });
</script>
