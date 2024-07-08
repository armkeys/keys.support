<div id="paypalec-cancellation-table"></div>
<script type="text/template" id="paypalec-cancellation-template">
    <h3 style="margin-bottom: 5px">{%= data.label %}</h3>
    <div class="table-wrapper -no-shadow">
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th class="text-center" width="30%">
                        Cancel Membership at End of PayPal Subscription 
                        <?php $this->tooltip(__('If enabled, the {%= data.label %} will be cancelled in WishList Member when the subscription ends in PayPal. As an example, if a subscription has a billing cycle set to charge once a month for a total of 6 months in PayPal, the {%= data.label %} in WishList Member will be cancelled at the end of the 6 month subscription. <br><br>
						If disabled, the {%= data.label %} will continue to be active in WishList Member when the subscription ends in PayPal. As an example, if a subscription has a billing cycle set to charge once a month for a total of 6 months in PayPal, the {%= data.label %} in WishList Member will remain active at the end of the 6 month subscription.', 'wishlist-member'), 'lg'); ?>
                    </th>
                    <th class="text-center" width="30%">
                        Cancel Membership Immediately After PayPal Subscription is Cancelled 
                        <?php $this->tooltip(__('If enabled, the {%= data.label %} will be immediately cancelled in WishList Member if the user cancels the subscription in PayPal. In this example, a subscription has a billing cycle set to charge once a month for a total of 12 months in PayPal and the monthly re-bill is on the 21st. If the user cancels the subscription in PayPal on the 4th, their access will be immediately cancelled in WishList Member. <br><br>If disabled, the {%= data.label %} in WishList Member will remain active and will wait for the end of the current billing cycle before access is cancelled. This means the user will retain access after they cancel the PayPal subscription until the end of that billing cycle. In this example, a subscription has a billing cycle set to charge once a month for a total of 12 months in PayPal and the monthly re-bill is on the 21st. If the user cancels the subscription in PayPal on the 4th, they will retain access to the membership until it is cancelled on the 21st of the month.', 'wishlist-member'), 'lg'); ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                {% _.each(data.levels, function(level) { %}
                <tr>
                    <td>{%= level.name %}</td>
                    <td class="text-center">
                        <template class="wlm3-form-group">
                            {
                                name : 'paypaleceotcancel[{%= level.id %}]',
                                value : 1,
                                uncheck_value : 0,
                                type : 'toggle-switch'
                            }
                        </template>
                    </td>
                    <td class="text-center">
                        <template class="wlm3-form-group">
                            {
                                name : 'paypalecsubscrcancel[{%= level.id %}]',
                                value : 1,
                                uncheck_value : 0,
                                type : 'toggle-switch'
                            }
                        </template>
                    </td>
                </tr>
                {% }); %}
            </tbody>
        </table>
    </div>
</script>

<script type="text/javascript">
    $('#paypalec-cancellation-table').empty();
    $.each(all_levels, function(k, v) {
        if(!Object.keys(v).length) return true;
        var data = {
            label : post_types[k].labels.name,
            levels : v
        }
        var tmpl = _.template($('script#paypalec-cancellation-template').html(), {variable: 'data'});
        var html = tmpl(data);
        $('#paypalec-cancellation-table').append(html);
    });
</script>
