<div id="slack-lists-table" class="table-wrapper -no-shadow"></div>
<script type="text/template" id="slack-lists-template">
    <table class="table table-striped">
        <colgroup>
            <col>
            <col width="150">
            <col width="150">
            <col width="150">
            <col width="1%">
        </colgroup>
        <thead>
            <tr>
                <th><?php esc_html_e('Membership Level', 'wishlist-member'); ?></th>
                <th><?php esc_html_e('Added', 'wishlist-member'); ?></th>
                <th><?php esc_html_e('Removed', 'wishlist-member'); ?></th>
                <th><?php esc_html_e('Cancelled', 'wishlist-member'); ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            {% _.each(data.levels, function(level) { %}
            <tr id="slack-{%- level.id %}">
                <td><a href="#" data-toggle="modal" data-target="#slack-lists-modal-{%- level.id %}">{%- level.name %}</a></td>
                {% ['added', 'removed', 'cancelled'].forEach(function(trigger) { %}
                <td>
                    <a href="#" data-trigger="{%- trigger %}" data-level="{%- level.id %}" class="toggle-slack-active {%- WLM3ThirdPartyIntegration.slack.slack_settings[trigger].active[level.id] == '1' ? '' : 'slack-inactive' %}"><i class="wlm-icons md-24">check_circle</i></a>
                    {% if(WLM3ThirdPartyIntegration.slack.slack_settings[trigger].active[level.id] == '1') { %}
                        {% if(WLM3ThirdPartyIntegration.slack.slack_settings[trigger].custom_channel_enabled[level.id] == 1 && WLM3ThirdPartyIntegration.slack.slack_settings[trigger].custom_channel[level.id].trim() != '') { %}
                            {%- WLM3ThirdPartyIntegration.slack.slack_settings[trigger].custom_channel[level.id].trim() %}
                        {% } else { %}
                            <?php esc_html_e('(Default)', 'wishlist-member'); ?>
                        {% } %}
                    {% } %}
                </td>
                {% }) %}
                <td class="text-right" style="vertical-align: middle">
                    <div class="btn-group-action">
                        <a href="#" data-toggle="modal" data-target="#slack-lists-modal-{%- level.id %}" class="btn -tags-btn" title="Edit"><i class="wlm-icons md-24">edit</i></a>
                    </div>
                </td>
            </tr>
            {% }); %}
        </tbody>
    </table>
</script>

<script type="text/javascript">
function reload_slack_lists() {
    $('#slack-lists-table').empty();
    $.each(all_levels, function(k, v) {
        var data = {
            label : post_types[k].labels.name,
            levels : v
        }
        var tmpl = _.template($('script#slack-lists-template').html(), {variable: 'data'});
        var html = tmpl(data);
        $('#slack-lists-table').append(html);
        return false;
    });
}
reload_slack_lists();
</script>
