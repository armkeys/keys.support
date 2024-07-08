<p><?php esc_html_e('Membership Levels can be assigned to Call Loop Lists by entering the Call Loop List URL in the corresponding field below.', 'wishlist-member'); ?></p>
<div id="callloop-lists-table" class="table-wrapper -no-shadow"></div>
<script type="text/template" id="callloop-lists-template">
    <table class="table table-striped">
        <colgroup>
            <col>
            <col width="50%">
            <col width="150">
            <col width="1%">
        </colgroup>
        <thead>
            <tr>
                <th>Membership Level</th>
                <th>Call Loop List URL</th>
                <th class="text-center">Unsubscribe if Removed from Level</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            {% _.each(data.levels, function(level) { %}
            <tr class="button-hover">
                <td><a href="#" data-toggle="modal" data-target="#callloop-lists-modal-{%- level.id %}">{%= level.name %}</a></td>
                <td id="callloop-lists-{%- level.id %}"></td>
                <td id="callloop-unsubscribe-{%- level.id %}" class="text-center"></td>
                <td class="text-right" style="vertical-align: middle">
                    <div class="btn-group-action">
                        <a href="#" data-toggle="modal" data-target="#callloop-lists-modal-{%- level.id %}" class="btn -tags-btn" title="Edit"><i class="wlm-icons md-24">edit</i></a>
                    </div>
                </td>
            </tr>
            {% }); %}
        </tbody>
    </table>
</script>

<script type="text/javascript">
    $('#callloop-lists-table').empty();
    $.each(all_levels, function(k, v) {
        var data = {
            label : post_types[k].labels.name,
            levels : v
        }
        var tmpl = _.template($('script#callloop-lists-template').html(), {variable: 'data'});
        var html = tmpl(data);
        $('#callloop-lists-table').append(html);
        return false;
    });
</script>
