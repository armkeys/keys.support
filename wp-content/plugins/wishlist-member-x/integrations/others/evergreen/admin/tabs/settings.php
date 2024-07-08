<p><?php esc_html_e('Ensure that First Name, Last Name and Email Address are the only required fields in the Evergreen Business System webinar settings.', 'wishlist-member'); ?></p>
<p><?php esc_html_e('Assign the Membership Levels to the corresponding Webinars. Membership Levels can be assigned to Webinars by entering the Registration URL of the webinar in the corresponding Auto Registration Link field below.', 'wishlist-member'); ?></p>
<div id="evergreen-lists-table" class="table-wrapper -no-shadow"></div>
<script type="text/template" id="evergreen-lists-template">
    <table class="table table-striped">
        <colgroup>
            <col>
            <col width="50%">
            <col width="1%">
        </colgroup>
        <thead>
            <tr>
                <th>Membership Level</th>
                <th>Auto Registration Link</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            {% _.each(data.levels, function(level) { %}
            <tr class="button-hover">
                <td><a href="#" data-toggle="modal" data-target="#evergreen-lists-modal-{%- level.id %}">{%= level.name %}</a></td>
                <td id="evergreen-lists-{%- level.id %}"></td>
                <td class="text-right" style="vertical-align: middle">
                    <div class="btn-group-action">
                        <a href="#" data-toggle="modal" data-target="#evergreen-lists-modal-{%- level.id %}" class="btn -tags-btn" title="Edit"><i class="wlm-icons md-24">edit</i></a>
                    </div>
                </td>
            </tr>
            {% }); %}
        </tbody>
    </table>
</script>

<script type="text/javascript">
    $('#evergreen-lists-table').empty();
    $.each(all_levels, function(k, v) {
        var data = {
            label : post_types[k].labels.name,
            levels : v
        }
        var tmpl = _.template($('script#evergreen-lists-template').html(), {variable: 'data'});
        var html = tmpl(data);
        $('#evergreen-lists-table').append(html);
        return false;
    });
</script>
