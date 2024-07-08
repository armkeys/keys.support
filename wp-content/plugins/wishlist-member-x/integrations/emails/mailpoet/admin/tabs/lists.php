<div id="mailpoet-lists-table" class="table-wrapper">
    <table class="table table-striped">
        <colgroup>
            <col>
            <col width="1%">
        </colgroup>
        <thead>
            <tr>
                <th><?php esc_html_e('Membership Level', 'wishlist-member'); ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>
<script type="text/template" id="mailpoet-lists-template">
{% _.each(data.levels, function(level) { %}
<tr class="button-hover">
    <td class="align-top"><a href="#" data-toggle="modal" data-target="#mailpoet-lists-modal-{%- level.id %}">{%= level.name %}</a></td>
    <td class="align-top text-right" style="vertical-align: middle">
        <div class="btn-group-action">
            <a href="#" data-toggle="modal" data-target="#mailpoet-lists-modal-{%- level.id %}" class="btn -tags-btn" title="Edit"><i class="wlm-icons md-24">edit</i></a>
        </div>
    </td>
</tr>
{% }); %}
</script>
