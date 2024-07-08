<p><?php esc_html_e('Create a product for each Membership Level or Pay Per Post using the assigned SKUs listed below.', 'wishlist-member'); ?></p>
<p><?php esc_html_e('The Membership Level SKUs specify the Membership Levels that should be connected to each transaction.', 'wishlist-member'); ?></p>
<div id="generic-products-table"></div>
<script type="text/template" id="generic-products-template">
    <h3 style="margin-bottom: 5px">{%= data.label %}</h3>
    <div class="table-wrapper -no-shadow">
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>
                        <?php esc_html_e('Name', 'wishlist-member'); ?>
                        <?php $this->tooltip(__('The name of the ' . '{%= data.label %}' . '.', 'wishlist-member')); ?>
                    </th>
                    <th width="30%">
                        <?php esc_html_e('SKU', 'wishlist-member'); ?>
                        <?php $this->tooltip(__('Each ' . '{%= data.label %}' . ' has a unique SKU that must be used for each product that needs to be created in the online payment option.', 'wishlist-member')); ?>
                    </th>
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
    $('#generic-products-table').empty();
    $.each(all_levels, function(k, v) {
        var data = {
            label : post_types[k].labels.name,
            levels : v
        }
        var tmpl = _.template($('script#generic-products-template').html(), {variable: 'data'});
        var html = tmpl(data);
        $('#generic-products-table').append(html);
    });
</script>
