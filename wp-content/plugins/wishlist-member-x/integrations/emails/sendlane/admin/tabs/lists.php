<div id="sendlane-lists-table" class="table-wrapper"></div>
<script type="text/template" id="sendlane-lists-template">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>
                    Membership Level
                    <?php wishlistmember_instance()->tooltip(__('The name of the Level. Click to edit the Actions for the Level.', 'wishlist-member')); ?>
                </th>
                <th width="1%"></th>
            </tr>
        </thead>
        <tbody>
            {% _.each(data.levels, function(level) { %}
            <tr>
                <td><a href="#" data-toggle="modal" data-target="#sendlane-tags-{%- level.id %}">{%= level.name %}</a></td>
                <td>
                    <div class="btn-group-action">
                        <a href="#" data-toggle="modal" data-target="#sendlane-tags-{%- level.id %}" class="btn -tags-btn" title="Edit Actions"><i class="wlm-icons md-24">edit</i></a>
                    </div>
                </td>
            </tr>
            {% }); %}
        </tbody>
    </table>
</script>

<script type="text/javascript">
    $('#sendlane-lists-table').empty();
    $.each(all_levels, function(k, v) {
        var data = {
            label : post_types[k].labels.name,
            levels : v
        }
        var tmpl = _.template($('script#sendlane-lists-template').html(), {variable: 'data'});
        var html = tmpl(data);
        $('#sendlane-lists-table').append(html);
        return false;
    });
</script>
