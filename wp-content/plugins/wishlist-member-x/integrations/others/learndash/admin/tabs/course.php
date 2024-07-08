<div id="learndash-courses-table" class="table-wrapper"></div>
<script type="text/template" id="learndash-courses-template">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>
                    <?php esc_html_e('Course', 'wishlist-member'); ?>
                    <?php wishlistmember_instance()->tooltip(__('The name of the Course. Click to edit the Actions for the Course. These include the options to Add to / Cancel from / Remove from Levels in WishList Member.', 'wishlist-member'), 'lg'); ?>
                </th>
                <th width="1%">{%= data.title %}</th>
            </tr>
        </thead>
        <tbody>
            {% _.each(data, function(course) { %}
                <tr>
                    <td><a href="#" data-toggle="modal" data-target="#learndash-course-{%- course.id %}">{%= course.title %}</a></td>
                    <td>
                        <div class="btn-group-action">
                            <a href="#" data-toggle="modal" data-target="#learndash-course-{%- course.id %}" class="btn -courses-btn" title="Edit Actions"><i class="wlm-icons md-24">edit</i></a>
                        </div>
                    </td>
                </tr>
            {% }); %}
        </tbody>
    </table>
</script>

<script type="text/javascript">
    $('#learndash-courses-table').empty();
    var courses = <?php echo json_encode($courses); ?>;
    var tmpl = _.template($('script#learndash-courses-template').html(), {variable: 'data'});
    var html = tmpl(courses);
    $('#learndash-courses-table').append(html);
</script>
