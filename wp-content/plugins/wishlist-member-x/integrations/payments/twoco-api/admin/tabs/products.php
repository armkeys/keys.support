<div id="twoco-api-products-table" class="table-wrapper"></div>
<script type="text/template" id="twoco-api-products-template">
<h3 class="mt-4 mb-2">{%= data.label %}</h3>
<table class="table table-striped">
    <colgroup>
        <col>
        <col width="135">
        <col width="80">
        <col width="135">
        <col width="135">
        <col width="1%">
    </colgroup>
    <thead>
        <tr>
            <th><?php esc_html_e('Access', 'wishlist-member'); ?></th>
            <th class="text-center"><?php esc_html_e('Amount', 'wishlist-member'); ?></th>
            <th class="text-center"><?php esc_html_e('Recurring', 'wishlist-member'); ?></th>
            <th class="text-center">
                <?php esc_html_e('Recurring Amount', 'wishlist-member'); ?>
                <?php wishlistmember_instance()->tooltip(__('Displays the set price for the Recurring Amount.', 'wishlist-member')); ?>
            </th>
            <th class="text-center">
                <?php esc_html_e('Interval', 'wishlist-member'); ?>
                <?php wishlistmember_instance()->tooltip(__('Displays the set Interval for the Recurring Price.', 'wishlist-member')); ?>
            </th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        {% _.each(data.levels, function(level) { %}
        <tr class="button-hover">
            <td><a href="#" data-toggle="modal" data-target="#products-twoco-api-{%- level.id %}">{%= level.name %}</a></td>
            <td class="text-center" id="twocoapi-products-amount-{%- level.id %}"></td>
            <td class="text-center" id="twocoapi-products-recur-{%- level.id %}"></td>
            <td class="text-center">
                <span class="twoco-api-recurring-{%- level.id %}" id="twocoapi-products-recuramount-{%- level.id %}"></span>
            </td>
            <td class="text-center">
                <span class="twoco-api-recurring-{%- level.id %}" id="twocoapi-products-interval-{%- level.id %}"></span>
                <span class="twoco-api-recurring-{%- level.id %}" id="twocoapi-products-intervaltype-{%- level.id %}"></span>
            </td>
            <td class="text-right" style="vertical-align: middle">
                <div class="btn-group-action">
                    <a href="#" data-toggle="modal" data-target="#products-twoco-api-{%- level.id %}" class="btn -tags-btn" title="Edit"><i class="wlm-icons md-24">edit</i></a>
                </div>
            </td>
        </tr>
        {% }) %}
    </tbody>
</table>
</script>

<script type="text/javascript">
$('#twoco-api-products-table').empty();
$.each(all_levels, function(k, v) {
    if(!Object.keys(v).length) return true;
    var data = {
        label : post_types[k].labels.name,
        levels : v
    }
    var tmpl = _.template($('script#twoco-api-products-template').html(), {variable: 'data'});
    var html = tmpl(data);
    $('#twoco-api-products-table').append(html);
});
</script>
