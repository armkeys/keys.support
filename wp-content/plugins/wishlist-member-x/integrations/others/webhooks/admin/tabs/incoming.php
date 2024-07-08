<div class="table-wrapper">
    <table id="wlm-webhooks-incoming" class="wlm-webhooks table table-striped">
        <colgroup>
            <col width="20%">
            <col>
            <col width="80">
        </colgroup>
        <tbody>
            <tr>
                <td colspan="100">
                    <?php esc_html_e('Loading...', 'wishlist-member'); ?>
                </td>
            </tr>
        </tbody>
        <thead>
            <tr>
                <th><?php esc_html_e('Name', 'wishlist-member'); ?></th>
                <th><?php esc_html_e('URL', 'wishlist-member'); ?></th>
                <th></th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <td class="pt-3" colspan="7">
                    <p><?php esc_html_e('No linked webhooks.', 'wishlist-member'); ?></p>
                </td>
            </tr>
            <tr>
                <td class="pt-3 pl-0" colspan="7">
                    <a href="#" class="btn -success -add-webhook-btn -condensed">
                        <i class="wlm-icons">add</i>
                        <span><?php esc_html_e('Link New WebHook', 'wishlist-member'); ?></span>
                    </a>
                </td>
            </tr>
        </tfoot>
    </table>
</div>
<script type="text/template" id="wlm-webhooks-incoming-template">
    {% _.each(data.incoming, function(incoming, url) { %}
    {%
        // Ensure we have data to process.
        incoming.actions = $.extend( { add : [], remove : [], cancel : [], uncancel : []}, incoming.actions );
    %}
    <tr class="button-hover" data-id="{%- url %}">
        <td class="align-top">
            <a href="#" class="webhooks-incoming-modal">{%- (incoming.name || url).replace(/\\(.)/mg, '$1') %}</a>
        </td>
        <td class="align-top">
            {%- WLM3VARS.blogurl + '?wlm_webhook=' + url %}
        </td>
        <td class="align-top text-right">
            <div class="btn-group-action">
                <a href="#" class="btn webhooks-incoming-modal" title="Edit"><i class="wlm-icons md-24">edit</i></a>
                <a href="#" class="btn wlm-icons md-24 -icon-only -del-webhook-btn"><span>delete</span></a>
            </div>
            <div class="btn-group-action">
            </div>
        </td>
    </tr>
    {% }); %}
</script>
