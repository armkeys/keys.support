<div id="fluentcrm-tag-table" class="table-wrapper">
    <table class="table table-striped">
        <tbody/>
        <thead>
            <tr>
                <th><?php esc_html_e('Tags', 'wishlist-member'); ?></th>
                <th width="80px"></th>
            </tr>
        </thead>
        <tfoot>
          <tr>
                <td colspan="10">
                  <p><?php esc_html_e('No tag actions found.', 'wishlist-member'); ?></p>
                </td>
          </tr>
          <tr>
                <td colspan="10" class="text-center pt-3">
                  <a href="#fluentcrm-tag-modal" data-toggle="modal" data-tag-id="new" class="btn -success -condensed"><i class="wlm-icons">add</i><?php esc_html_e('Add Tag Action', 'wishlist-member'); ?></a>
                </td>
          </tr>
        </tfoot>
    </table>
</div>
<script type="text/template" id="fluentcrm-tag-template">
    {% _.each(data.tag_ids || [], function(tag_id) { %}
        <tr class="button-hover">
            <td><a href="#" data-toggle="modal" data-target="#fluentcrm-tag-modal" data-tag-id="{%- tag_id %}">{%= data.tags[tag_id].title %}</a></td>
            <td class="text-right">
                <div class="btn-group-action">
                    <a href="#" data-toggle="modal" data-target="#fluentcrm-tag-modal" data-tag-id="{%- tag_id %}" class="btn -tag-btn" title="<?php esc_attr_e('Edit Tag Actions', 'wishlist-member'); ?>"><i class="wlm-icons md-24">edit</i></a>
                    <a href="#" data-tag-id="{%- tag_id %}" class="btn -del-tag-btn" title="<?php esc_attr_e('Delete Tag Actions', 'wishlist-member'); ?>"><i class="wlm-icons md-24">delete</i></a>
                </div>
            </td>
        </tr>
    {% }); %}
</script>

<script type="text/javascript">
function fluentcrm_load_tags_table() {
    var tmpl = _.template($('script#fluentcrm-tag-template').html(), {variable: 'data'});
    var data = {
        tags : <?php echo json_encode($tags); ?>,
        tag_ids : Object.keys( WLM3ThirdPartyIntegration.fluentcrm.fluentcrm_settings.tag || [] )
    }
    $('#fluentcrm-tag-table table tbody').empty().append(tmpl(data).trim());
    WLM3ThirdPartyIntegration.fluentcrm.fxn && WLM3ThirdPartyIntegration.fluentcrm.fxn.tag_action_events();
}
fluentcrm_load_tags_table();
</script>
