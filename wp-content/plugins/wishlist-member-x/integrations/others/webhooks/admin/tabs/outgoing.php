<div id="webhooks-outgoing-table"></div>
<script type="text/template" id="wlm-webhooks-outoing-template">
    <h3 class="mt-4 mb-2">{%- data.label %}</h3>
    <div class="table-wrapper -no-shadow">
        <table id="wlm-webhooks-outgoing" class="wlm-webhooks table table-striped">
            <colgroup>
                <col>
                <col width="15%">
                <col width="15%">
                <col width="15%">
                <col width="15%">
                <col width="80">
            </colgroup>
            <thead>
                <tr>
                    <th><?php esc_html_e('Access', 'wishlist-member'); ?></th>
                    <th class="text-center">
                        <?php esc_html_e('Add', 'wishlist-member'); ?>
                        {% if ( '__levels__' === data.type ) { %}
                        <?php wishlistmember_instance()->tooltip(__('Displays if an Outgoing WebHook URL is set when a Member is Added to the Level.', 'wishlist-member')); ?>
                        {% } else { %}
                        <?php wishlistmember_instance()->tooltip(__('Displays if an Outgoing WebHook URL is set when a Member is Added to the Pay Per Post.', 'wishlist-member')); ?>
                        {% } %}
                    </th>
                    <th class="text-center">
                        <?php esc_html_e('Remove', 'wishlist-member'); ?>
                        {% if ( '__levels__' === data.type ) { %}
                        <?php wishlistmember_instance()->tooltip(__('Displays if an Outgoing WebHook URL is set when a Member is Removed from the Level.', 'wishlist-member')); ?>
                        {% } else { %}
                        <?php wishlistmember_instance()->tooltip(__('Displays if an Outgoing WebHook URL is set when a Member is Removed from the Pay Per Post.', 'wishlist-member')); ?>
                        {% } %}
                    </th>
                    {% if ( '__levels__' === data.type ) { %}
                    <th class="text-center">
                        <?php esc_html_e('Cancel', 'wishlist-member'); ?>
                        <?php wishlistmember_instance()->tooltip(__('Displays if an Outgoing WebHook URL is set when a Member is Cancelled from the Level.', 'wishlist-member')); ?>
                    </th>
                    <th class="text-center">
                        <?php esc_html_e('Uncancel', 'wishlist-member'); ?>
                        <?php wishlistmember_instance()->tooltip(__('Displays if an Outgoing WebHook URL is set when a Member is Uncancelled from the Level.', 'wishlist-member')); ?>
                    </th>
                    <th class="text-center">
                        <?php esc_html_e('Expire', 'wishlist-member'); ?>
                        <?php wishlistmember_instance()->tooltip(__('Displays if an Outgoing WebHook URL is set when a Member is Expired from the Level.', 'wishlist-member')); ?>
                    </th>
                    <th class="text-center">
                        <?php esc_html_e('Unexpire', 'wishlist-member'); ?>
                        <?php wishlistmember_instance()->tooltip(__('Displays if an Outgoing WebHook URL is set when a Member is Unexpired from the Level.', 'wishlist-member')); ?>
                    </th>
                    {% } else { %}
                    <th colspan="2"></th>
                    {% } %}
                    <th></th>
                </tr>
            </thead>
            <tbody>
                {% _.each(data.levels, function(level) { %}
                {%
                    // Ensure we have data to process.
                    data.outgoing[level.id] = $.extend( { add : '', remove : '', cancel : '', uncancel : '', expire : '', unexpire : '' }, data.outgoing[level.id] );
                    // Regexp to search for non-empty lines.
                    var lines_regexp = /[^\n\r]+/g;
                %}
                <tr class="button-hover" data-id="{%- level.id %}" data-name="{%- level.name %}" data-type="{%- data.type %}">
                    <td><a data-toggle="modal" href="#webhooks-outgoing-modal">{%- level.name %}</a></td>
                    <td class="text-center">{%= ((data.outgoing[level.id].add.match(lines_regexp) || []).length ? '<i class="wlm-icons md-18 color-green">check</i>' : '') %}</td>
                    <td class="text-center">{%= ((data.outgoing[level.id].remove.match(lines_regexp) || []).length ? '<i class="wlm-icons md-18 color-green">check</i>' : '') %}</td>
                    {% if ( '__levels__' === data.type ) { %}
                    <td class="text-center">{%= ((data.outgoing[level.id].cancel.match(lines_regexp) || []).length ? '<i class="wlm-icons md-18 color-green">check</i>' : '') %}</td>
                    <td class="text-center">{%= ((data.outgoing[level.id].uncancel.match(lines_regexp) || []).length ? '<i class="wlm-icons md-18 color-green">check</i>' : '') %}</td>
                    <td class="text-center">{%= ((data.outgoing[level.id].expire.match(lines_regexp) || []).length ? '<i class="wlm-icons md-18 color-green">check</i>' : '') %}</td>
                    <td class="text-center">{%= ((data.outgoing[level.id].unexpire.match(lines_regexp) || []).length ? '<i class="wlm-icons md-18 color-green">check</i>' : '') %}</td>
                    {% } else { %}
                    <td colspan="2"></td>
                    {% } %}
                    <td class="text-right">
                        <div class="btn-group-action">
                            <a data-toggle="modal" href="#webhooks-outgoing-modal" class="btn" title="Edit"><i class="wlm-icons md-24">edit</i></a>
                        </div>
                        <div class="btn-group-action">
                        </div>
                    </td>
                </tr>
                {% }); %}
            </tbody>
        </table>
    </div>
</script>
