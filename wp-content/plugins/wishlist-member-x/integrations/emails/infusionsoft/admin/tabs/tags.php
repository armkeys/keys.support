<p><?php esc_html_e('Infusionsoft by Keap tags can be configured to applied or removed from users based on When Added, When Removed, When Cancelled and When Uncancelled from membership levels in WishList Member.', 'wishlist-member'); ?></p>
<div id="infusionsoft-lists-table" class="table-wrapper"></div>
<script type="text/template" id="infusionsoft-lists-template">
    <h3 style="margin-bottom: 5px">{%= data.label %}</h3>
    <table class="table table-striped">
        <colgroup>
            <col>
            <col width="1%">
        </colgroup>
        <thead>
            <tr>
                <th>
                    <?php esc_html_e('Name', 'wishlist-member'); ?>
                    <?php $this->tooltip(__('The name of the membership levels. Click to edit the Actions for the membership levels. These include the options to Apply or Remove tags in Infusionsoft by Keap based on When Added, When Removed, When Cancelled, When Uncancelled from membership levels in WishList Member.', 'wishlist-member'), 'lg'); ?>
                </th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            {% _.each(data.levels, function(level) { %}
            <tr class="button-hover">
                <td><a href="#" data-toggle="modal" data-target="#infusionsoft-lists-modal-{%- level.id %}" onclick="loadLevelPopUp({%- level.id %})">{%= level.name %}</a></td>
                <td class="text-right">
                    <div class="btn-group-action">
                        <a href="#" data-toggle="modal" data-target="#infusionsoft-lists-modal-{%- level.id %}" class="btn -tags-btn" title="Edit Tag Settings" onclick="loadLevelPopUp({%- level.id %})"><i class="wlm-icons md-24">edit</i></a>
                    </div>
                </td>
            </tr>
            {% }); %}
        </tbody>
    </table>
</script>
