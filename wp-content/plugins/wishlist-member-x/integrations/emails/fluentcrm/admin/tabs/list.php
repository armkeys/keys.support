<div id="fluentcrm-list-table" class="table-wrapper"></div>
<script type="text/template" id="fluentcrm-list-template">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Lists</th>
                <th width="1%">{%= data.title %}</th>
            </tr>
        </thead>
        <tbody>
            {% _.each(data, function(list) { %}
                <tr class="button-hover">
                    <td><a href="#" data-toggle="modal" data-target="#fluentcrm-list-{%- list.id %}">{%= list.title %}</a></td>
                    <td>
                        <div class="btn-group-action">
                            <a href="#" data-toggle="modal" data-target="#fluentcrm-list-{%- list.id %}" class="btn -list-btn" title="Edit Actions"><i class="wlm-icons md-24">edit</i></a>
                        </div>
                    </td>
                </tr>
            {% }); %}
        </tbody>
    </table>
</script>

<script type="text/javascript">
    $('#fluentcrm-list-table').empty();
    var lists = <?php echo json_encode($lists); ?>;
    var tmpl = _.template($('script#fluentcrm-list-template').html(), {variable: 'data'});
    var html = tmpl(lists);
    $('#fluentcrm-list-table').append(html);
</script>
