<p><a href="#sendstudio-list-id" class="hide-show"><?php esc_html_e('How to get the List ID in Interspire Email Marketer', 'wishlist-member'); ?></a></p>
<ol class="d-none" id="sendstudio-list-id" style="list-style: decimal">
    <li><p><?php esc_html_e('Log in to the Interspire Email Marketer account', 'wishlist-member'); ?></p></li>
    <li><p><?php esc_html_e('Navigate to the following section:', 'wishlist-member'); ?><br>Contact Lists > View Contact Lists</p></li>
    <li><p><?php esc_html_e('The View Contact Lists page will appear. Click the Edit option for the List.', 'wishlist-member'); ?></p></li>
    <li>
        <p>
            <?php esc_html_e('The Edit Contact List page will appear.', 'wishlist-member'); ?><br />
            <?php esc_html_e('Copy the number of the ID parameter at the end of the URL displayed in the address bar of the browser.', 'wishlist-member'); ?><br />
            <?php esc_html_e('URL Example:', 'wishlist-member'); ?><br />
            http://www.yourdomain.com/path/to/IEM/installationadmin/index.php?Page=Lists&Action=Edit&id=1<br />
            (<?php esc_html_e('The number 1 is the ID in this example', 'wishlist-member'); ?>)
        </p>
    </li>
</ol>

<div id="sendstudio-lists-table" class="table-wrapper -no-shadow"></div>
<script type="text/template" id="sendstudio-lists-template">
    <table class="table table-striped">
        <colgroup>
            <col>
            <col width="1%">
        </colgroup>
        <thead>
            <tr>
                <th>Membership Level</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            {% _.each(data.levels, function(level) { %}
            <tr class="button-hover">
                <td><a href="#" data-toggle="modal" data-target="#interspire-lists-modal-{%- level.id %}">{%= level.name %}</a></td>
                <td class="text-right" style="vertical-align: middle">
                    <div class="btn-group-action">
                        <a href="#" data-toggle="modal" data-target="#interspire-lists-modal-{%- level.id %}" class="btn -tags-btn" title="Edit"><i class="wlm-icons md-24">edit</i></a>
                    </div>
                </td>
            </tr>
            {% }); %}
        </tbody>
    </table>
</script>

<script type="text/javascript">
    $('#sendstudio-lists-table').empty();
    $.each(all_levels, function(k, v) {
        var data = {
            label : post_types[k].labels.name,
            levels : v
        }
        var tmpl = _.template($('script#sendstudio-lists-template').html(), {variable: 'data'});
        var html = tmpl(data);
        $('#sendstudio-lists-table').append(html);
        return false;
    });
</script>
