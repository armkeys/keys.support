<div id="lifterlms-memberships-table" class="table-wrapper"></div>
<script type="text/template" id="lifterlms-memberships-template">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Group</th>
                <th width="1%">{%= data.title %}</th>
            </tr>
        </thead>
        <tbody>
            {% _.each(data, function(membership) { %}
                <tr>
                    <td><a href="#" data-toggle="modal" data-target="#lifterlms-membership-{%- membership.id %}">{%= membership.title %}</a></td>
                    <td>
                        <div class="btn-membership-action">
                            <a href="#" data-toggle="modal" data-target="#lifterlms-membership-{%- membership.id %}" class="btn -memberships-btn" title="Edit Actions"><i class="wlm-icons md-24">edit</i></a>
                        </div>
                    </td>
                </tr>
            {% }); %}
        </tbody>
    </table>
</script>

<script type="text/javascript">
    $('#lifterlms-memberships-table').empty();
    var memberships = <?php echo json_encode($memberships); ?>;
    var tmpl = _.template($('script#lifterlms-memberships-template').html(), {variable: 'data'});
    var html = tmpl(memberships);
    $('#lifterlms-memberships-table').append(html);
</script>
