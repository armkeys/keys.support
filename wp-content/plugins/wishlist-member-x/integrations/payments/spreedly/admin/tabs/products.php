<p><?php esc_html_e('Use the information below to create plans in your Pin Payments account.', 'wishlist-member'); ?></p>
<div id="spreedly-products-table"></div>
<script type="text/template" id="spreedly-products-template">
<h3 style="margin-bottom: 5px">{%= data.label %}</h3>
<div class="table-wrapper -no-shadow">
    <table class="table table-striped">
        <colgroup>
            <col>
            <col width="110">
            <col width="55%">
        </colgroup>
        <thead>
            <tr>
                <th>Access</th>
                <th class="text-center">Feature Level</th>
                <th>URL a customer is returned to on sale</th>
            </tr>
        </thead>
        <tbody>
            {% _.each(data.levels, function(level) { %}
            <tr style="vertical-align: middle">
                <td>{%- level.name %}</td>
                <td class="text-center">{%- level.id %}</td>
                <td>
                    <input class="form-control copyable" readonly="readonly" value="{%- data.tyurl %}?sku={%- level.id %}">
                </td>
            </tr>
            {% }) %}
        </tbody>
    </table>
</div>
</script>

<script type="text/javascript">
$('#spreedly-products-table').empty();
$.each(all_levels, function(k, v) {
    if(!Object.keys(v).length) return true;
    var data = {
        tyurl : WLM3ThirdPartyIntegration['spreedly'].spreedlythankyou_url,
        label : post_types[k].labels.name,
        levels : v
    }
    var tmpl = _.template($('script#spreedly-products-template').html(), {variable: 'data'});
    var html = tmpl(data);
    $('#spreedly-products-table').append(html);
});
</script>
