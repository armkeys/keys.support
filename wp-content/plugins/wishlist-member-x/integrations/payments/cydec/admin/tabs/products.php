<p><?php esc_html_e('Create a product for each Membership Level or Pay Per Post using the assigned SKUs listed below.', 'wishlist-member'); ?></p>
<p><?php esc_html_e('The Membership Level SKUs specify the Membership Levels that should be connected to each transaction.', 'wishlist-member'); ?></p>
<div id="cydec-products-table"></div>
<script type="text/template" id="cydec-products-template">
    <h3 style="margin-bottom: 5px">{%= data.label %}</h3>
    <div class="table-wrapper -no-shadow">
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th width="30%">SKU <?php $this->tooltip(__('Each Membership Level has a unique Level ID that must be used for each product that needs to be created in the online payment option.', 'wishlist-member')); ?></th>
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
    $('#cydec-products-table').empty();
    $.each(all_levels, function(k, v) {
        var data = {
            label : post_types[k].labels.name,
            levels : v
        }
        var tmpl = _.template($('script#cydec-products-template').html(), {variable: 'data'});
        var html = tmpl(data);
        $('#cydec-products-table').append(html);
    });
</script>
