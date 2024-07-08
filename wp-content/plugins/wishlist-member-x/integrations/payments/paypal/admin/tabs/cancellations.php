<div id="paypalps-cancellation-table"></div>
<script type="text/template" id="paypalps-cancellation-template">
    <h3 style="margin-bottom: 5px">{%= data.label %}</h3>
    <div class="table-wrapper -no-shadow">
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th class="text-center" width="30%">Cancel Membership at End of PayPal Subscription <?php $this->tooltip(__('The Membership Level will remain active until the end of the PayPal subscription.', 'wishlist-member')); ?></th>
                    <th class="text-center" width="30%">Cancel Membership Immediately After PayPal Subscription is Cancelled <?php $this->tooltip(__('The Membership Level will immediately be cancelled in WishList Member when the subscription is cancelled within PayPal.', 'wishlist-member')); ?></th>
                </tr>
            </thead>
            <tbody>
                {% _.each(data.levels, function(level) { %}
                <tr>
                    <td>{%= level.name %}</td>
                    <td class="text-center">
                        <template class="wlm3-form-group">
                            {
                                name : 'eotcancel[{%= level.id %}]',
                                value : 1,
                                uncheck_value : 0,
                                type : 'toggle-switch'
                            }
                        </template>
                    </td>
                    <td class="text-center">
                        <template class="wlm3-form-group">
                            {
                                name : 'subscrcancel[{%= level.id %}]',
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
    $('#paypalps-cancellation-table').empty();
    $.each(all_levels, function(k, v) {
        if(!Object.keys(v).length) return true;
        var data = {
            label : post_types[k].labels.name,
            levels : v
        }
        var tmpl = _.template($('script#paypalps-cancellation-template').html(), {variable: 'data'});
        var html = tmpl(data);
        $('#paypalps-cancellation-table').append(html);
    });
</script>
