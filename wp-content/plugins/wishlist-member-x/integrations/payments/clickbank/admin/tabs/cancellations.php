<div id="clickbank-cancellations-table" class="table-wrapper"></div>
<script type="text/template" id="clickbank-products-template">
    <h3 class="mt-4 mb-2">{%- data.label %}</h3>
    <div class="table-wrapper -no-shadow">
        <table class="table table-striped" id="clickbank-cancellations-table">
            <colgroup>
                <col>
                <col width="27%">
                <col width="27%">
            </colgroup>
            <thead>
                <tr>
                    <th><?php esc_html_e('Access', 'wishlist-member'); ?></th>
                    <th class="text-center">
                        <?php esc_html_e('Cancel at End of Subscription', 'wishlist-member'); ?>
                        <?php wishlistmember_instance()->tooltip(__('The Membership Level will remain active until the end of the ClickBank subscription.', 'wishlist-member')); ?>
                    </th>
                    <th class="text-center">
                        <?php esc_html_e('Immediately Cancel After Subscription is Cancelled', 'wishlist-member'); ?>
                        <?php wishlistmember_instance()->tooltip(__('The Membership Level will immediately be cancelled in WishList Member when the subscription is cancelled within ClickBank.', 'wishlist-member')); ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                {% _.each(data.levels, function(level) { %}
                <tr class="button-hover" data-level="{%- data.label %}">
                    <td>{%- level.name %}</td>
                    <td class="text-center">
                        <div class="d-inline-block">
                            <template class="wlm3-form-group">
                                {
                                    name : 'cb_eot_cancel[{%- level.id %}]',
                                    value : 1,
                                    uncheck_value : 0,
                                    type : 'toggle-switch'
                                }
                            </template>
                        </div>
                    </td>
                    <td class="text-center">
                        <div class="d-inline-block">
                            <template class="wlm3-form-group">
                                {
                                    name : 'cb_scrcancel[{%- level.id %}]',
                                    value : 1,
                                    uncheck_value : 0,
                                    type : 'toggle-switch'
                                }
                            </template>
                        </div>
                    </td>
                </tr>
                {% }) %}
            </tbody>
        </table>
    </div>
</script>

<script type="text/javascript">
    $('#clickbank-cancellations-table').empty();
    $.each(all_levels, function(k, v) {
        if(!Object.keys(v).length) return true;
        var data = {
            label : post_types[k].labels.name,
            levels : v
        }
        var tmpl = _.template($('script#clickbank-products-template').html(), {variable: 'data'});
        var html = tmpl(data);
        $('#clickbank-cancellations-table').append(html);
    });
</script>
