<?php

/**
 * Constant Contact admin UI > Tags tab
 *
 * @package WishListMember/Autoresponders
 */

?>
<div id="constantcontacttags-table"  class="table-wrapper"></div>
<script type="text/template" id="constantcontacttags-template">
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
        <tbody>
            {% _.each(data.levels, function(level) { %}
            <tr>
                <td><a href="#" data-toggle="modal" data-target="#constantcontact-tags-modal-{%- level.id %}">{%= level.name %}</a></td>
                <td class="text-right" style="vertical-align: middle">
                    <div class="btn-group-action">
                        <a href="#" data-toggle="modal" data-target="#constantcontact-tags-modal-{%- level.id %}" class="btn -tags-btn" title="Edit"><i class="wlm-icons md-24">edit</i></a>
                    </div>
                </td>
            </tr>
            {% }); %}
        </tbody>
    </table>
</script>

<script type="text/javascript">
    $('#constantcontacttags-table').empty();
    $.each(all_levels, function(k, v) {
        var data = {
            label: post_types[k].labels.name,
            levels: v
        }
        var tmpl = _.template($('script#constantcontacttags-template').html(), {
            variable: 'data'
        });
        var html = tmpl(data);
        $('#constantcontacttags-table').append(html);
        return false;
    });
</script>
