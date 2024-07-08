<p>
    <?php esc_html_e('A product can be created in 1ShoppingCart for each membership level and/or pay per post. The product and the membership level or pay per post can be connected using the corresponding SKUs listed below.', 'wishlist-member'); ?>
</p>
<div id="1shoppingcart-products-table"></div>
<script type="text/template" id="1shoppingcart-products-template">
    <h3 style="margin-bottom: 5px">{%= data.label %}</h3>
    <div class="table-wrapper -no-shadow">
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                <th>
                    <?php esc_html_e('Name', 'wishlist-member'); ?>
                    <?php $this->tooltip(__('The name of the {%= data.label %}', 'wishlist-member'), 'lg'); ?>
                </th>
                    <th width="30%">
                        <?php esc_html_e('SKU', 'wishlist-member'); ?>
                        <?php $this->tooltip(__('The {%= data.label %} SKU can be copied and pasted into the SKU field in the Edit Product > Details section in 1ShoppingCart.', 'wishlist-member'), 'lg'); ?>
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
    $('#1shoppingcart-products-table').empty();
    $.each(all_levels, function(k, v) {
        var data = {
            label : post_types[k].labels.name,
            levels : v
        }
        var tmpl = _.template($('script#1shoppingcart-products-template').html(), {variable: 'data'});
        var html = tmpl(data);
        $('#1shoppingcart-products-table').append(html);
    });
</script>
