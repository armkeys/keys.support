<div id="twoco-products-table"></div>
<script type="text/template" id="twoco-products-template">
    <h3 style="margin-bottom: 5px">{%= data.label %}</h3>
    <div class="table-wrapper -no-shadow">
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th width="30%">SKU <?php $this->tooltip(__('Each Membership Level has a unique SKU that must be used for each product that needs to be created in the online payment option.', 'wishlist-member')); ?></th>
                </tr>
            </thead>
            <tbody>
                {% _.each(data.levels, function(level) { %}
                <tr>
                    <td>{%= level.name %}</td>
                    <td>{%= level.id %}</td>
                </tr>
                {% }); %}
            </tbody>
        </table>
    </div>
</script>

<script type="text/javascript">
    $('#twoco-products-table').empty();
    $.each(all_levels, function(k, v) {
        var data = {
            label : post_types[k].labels.name,
            levels : v
        }
        var tmpl = _.template($('script#twoco-products-template').html(), {variable: 'data'});
        var html = tmpl(data);
        $('#twoco-products-table').append(html);
    });
</script>
