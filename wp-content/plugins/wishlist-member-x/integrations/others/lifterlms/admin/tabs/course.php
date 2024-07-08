<div id="lifterlms-courses-table" class="table-wrapper"></div>
<script type="text/template" id="lifterlms-courses-template">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Course</th>
                <th width="1%">{%= data.title %}</th>
            </tr>
        </thead>
        <tbody>
            {% _.each(data, function(course) { %}
                <tr>
                    <td><a href="#" data-toggle="modal" data-target="#lifterlms-course-{%- course.id %}">{%= course.title %}</a</td>
                    <td>
                        <div class="btn-group-action">
                            <a href="#" data-toggle="modal" data-target="#lifterlms-course-{%- course.id %}" class="btn -courses-btn" title="Edit Actions"><i class="wlm-icons md-24">edit</i></a>
                        </div>
                    </td>
                </tr>
            {% }); %}
        </tbody>
    </table>
</script>

<script type="text/javascript">
    $('#lifterlms-courses-table').empty();
    var courses = <?php echo json_encode($courses); ?>;
    var tmpl = _.template($('script#lifterlms-courses-template').html(), {variable: 'data'});
    var html = tmpl(courses);
    $('#lifterlms-courses-table').append(html);
</script>
