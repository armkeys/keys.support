<div id="icontact-lists-table" class="table-wrapper"></div>
<script type="text/template" id="icontact-lists-template">
    <table class="table table-striped">
        <colgroup>
            <col>
            <col width="40%">
            <col width="150">
            <col width="1%">
        </colgroup>
        <thead>
            <tr>
                <th><?php esc_html_e('Membership Level', 'wishlist-member'); ?></th>
                <th><?php esc_html_e('Contact List', 'wishlist-member'); ?></th>
                <th class="text-center" style="white-space: nowrap;"><?php esc_html_e('Log Unsubscribes', 'wishlist-member'); ?> <?php wishlistmember_instance()->tooltip(__('If enabled, a txt file named [clientFolderID] (the folder selected in the Settings > Folder dropdown) is created in the WordPress site root folder. The txt file contains the email address(es) of those who unsubscribed and the date the unsubscribe occurred.', 'wishlist-member'), 'lg'); ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            {% _.each(data.levels, function(level) { %}
            <tr class="button-hover">
                <td><a href="#" data-toggle="modal" data-target="#icontact-lists-modal-{%- level.id %}">{%= level.name %}</a></td>
                <td id="icontact-lists-{%- level.id %}"></td>
                <td id="icontact-unsubscribe-{%- level.id %}" class="text-center"></td>
                <td class="text-right" style="vertical-align: middle">
                    <div class="btn-group-action">
                        <a href="#" data-toggle="modal" data-target="#icontact-lists-modal-{%- level.id %}" class="btn -tags-btn" title="Edit"><i class="wlm-icons md-24">edit</i></a>
                    </div>
                </td>
            </tr>
            {% }); %}
        </tbody>
    </table>
</script>

<script type="text/javascript">
    $('#icontact-lists-table').empty();
    $.each(all_levels, function(k, v) {
        var data = {
            label : post_types[k].labels.name,
            levels : v
        }
        var tmpl = _.template($('script#icontact-lists-template').html(), {variable: 'data'});
        var html = tmpl(data);
        $('#icontact-lists-table').append(html);
        return false;
    });
</script>
