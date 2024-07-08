<p><?php esc_html_e('Map your membership levels to your webinars by selecting a webinar from the dropdowns provided under the "Webinar" column.', 'wishlist-member'); ?></p>
<div id="gotomeetingapi-lists-table" class="table-wrapper"></div>
<script type="text/template" id="gotomeetingapi-lists-template">
    <table class="table table-striped">
        <colgroup>
            <col>
            <col width="50%">
            <col width="1%">
        </colgroup>
        <thead>
            <tr>
                <th>
                    <?php _e('Membership Level', 'wishlist-member'); ?>
                    <?php wishlistmember_instance()->tooltip(__('The name of the level.', 'wishlist-member')); ?>
                </th>
                <th>
                    <?php _e('Webinar', 'wishlist-member'); ?>
                    <?php wishlistmember_instance()->tooltip(__('The webinar applied to the level. Anyone added to the level will be added to the webinar.', 'wishlist-member')); ?>
                </th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            {% _.each(data.levels, function(level) { %}
            <tr class="button-hover">
                <td><a href="#" data-toggle="modal" data-target="#gotomeetingapi-lists-modal-{%- level.id %}">{%= level.name %}</a></td>
                <td id="gotomeetingapi-lists-{%- level.id %}"></td>
                <td class="text-right" style="vertical-align: middle">
                    <div class="btn-group-action">
                        <a href="#" data-toggle="modal" data-target="#gotomeetingapi-lists-modal-{%- level.id %}" class="btn -tags-btn" title="Edit"><i class="wlm-icons md-24">edit</i></a>
                    </div>
                </td>
            </tr>
            {% }); %}
        </tbody>
    </table>
</script>

<script type="text/javascript">
    $('#gotomeetingapi-lists-table').empty();
    $.each(all_levels, function(k, v) {
        var data = {
            label : post_types[k].labels.name,
            levels : v
        }
        var tmpl = _.template($('script#gotomeetingapi-lists-template').html(), {variable: 'data'});
        var html = tmpl(data);
        $('#gotomeetingapi-lists-table').append(html);
        return false;
    });
</script>
