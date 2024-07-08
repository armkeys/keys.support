<div id="wpcourseware-levels-table" class="table-wrapper"></div>
<script type="text/template" id="wpcourseware-levels-template">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>
                    <?php esc_html_e('Membership Level', 'wishlist-member'); ?>
                    <?php $this->tooltip(__('The name of the Level. Click to edit the Actions for the Level. These include the options to Add/Remove WP Courseware Courses.', 'wishlist-member')); ?>
                </th>
                <th width="1%"></th>
            </tr>
        </thead>
        <tbody>
            {% _.each(data.levels, function(level) { %}
            <tr>
                <td><a href="#" data-toggle="modal" data-target="#wpcourseware-levels-{%- level.id %}">{%= level.name %}</a></td>
                <td>
                    <div class="btn-group-action">
                        <a href="#" data-toggle="modal" data-target="#wpcourseware-levels-{%- level.id %}" class="btn -levels-btn" title="Edit Actions"><i class="wlm-icons md-24">edit</i></a>
                    </div>
                </td>
            </tr>
            {% }); %}
        </tbody>
    </table>
</script>

<script type="text/javascript">
    $('#wpcourseware-levels-table').empty();
    $.each(all_levels, function(k, v) {
        var data = {
            levels : v
        }
        var tmpl = _.template($('script#wpcourseware-levels-template').html(), {variable: 'data'});
        var html = tmpl(data);
        $('#wpcourseware-levels-table').append(html);
        return false;
    });
</script>
