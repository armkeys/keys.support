<div id="spreedly-subscriptions-table"></div>
<script type="text/template" id="spreedly-subscriptions-template">
<h3 style="margin-bottom: 5px">{%= data.label %}</h3>
<table class="table table-striped">
    <colgroup>
        <col>
        <col width="150">
        <col width="110">
        <col width="45%">
    </colgroup>
    <thead>
        <tr>
            <th>Access</th>
            <th>Plan Name</th>
            <th class="text-center">Amount</th>
            <th>Subscription Link <?php $this->tooltip(__('Use this link to let the people subscribe in your site.', 'wishlist-member')); ?></th>
        </tr>
    </thead>
    <tbody>
        {% _.each(data.subscriptions, function(subscription) { %}
        {% if(subscription.feature_level in all_levels_flat) { %}
        <tr style="vertical-align: middle">
            <td>{%- all_levels_flat[subscription.feature_level].name %}</td>
            <td>{%- subscription.name %}</td>
            <td class="text-center">{%- subscription.currency_code %} {%- subscription.amount %}</td>
            <td>
                <input class="form-control copyable" readonly="readonly" value="{%- wpm_scregister %}{%- subscription.id %}">
            </td>
        </tr>
        {% } %}
        {% }) %}
    </tbody>
</table>
</script>
