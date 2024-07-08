<p>
    <?php esc_html_e('A Simple Checkout item can be created for a level using an Item ID below. This can be done in the Home > Simple Checkout > "Add Item" section in Authorize.net', 'wishlist-member'); ?>
</p>
<div id="authorizenet-products-table"></div>
<script type="text/template" id="authorizenet-products-template">
    <h3 style="margin-bottom: 5px">{%= data.label %}</h3>
    <div class="table-wrapper -no-shadow">
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>
                        <?php esc_html_e('Name', 'wishlist-member'); ?>
                        {% if (data.type == '__levels__ ' ) { %}
                        <?php wishlistmember_instance()->tooltip(__('The name of the level.', 'wishlist-member')); ?>
                        {% } else { %}
                        <?php wishlistmember_instance()->tooltip(__('The name of the pay per post.', 'wishlist-member')); ?>
                        {% } %}
                    </th>
                    <th width="30%">
                        <?php wishlistmember_instance()->tooltip(__('The Item ID to be used in Authorize.net', 'wishlist-member')); ?>
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
    $('#authorizenet-products-table').empty();
    $.each(all_levels, function(k, v) {
        var data = {
            type : k,
            label : post_types[k].labels.name,
            levels : v
        }
        var tmpl = _.template($('script#authorizenet-products-template').html(), {variable: 'data'});
        var html = tmpl(data);
        $('#authorizenet-products-table').append(html);
    });
</script>
