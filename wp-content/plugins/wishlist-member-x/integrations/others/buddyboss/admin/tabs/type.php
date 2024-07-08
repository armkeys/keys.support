<div id="buddyboss-types-table" class="table-wrapper"></div>
<script type="text/template" id="buddyboss-types-template">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>
                    <?php esc_html_e('Profile Type', 'wishlist-member'); ?>
                    <?php wishlistmember_instance()->tooltip(__('The name of the BuddyBoss Profile Type. Click to edit the Actions for the Profile Type. These include the options to Add to, Cancel from, or Remove from Level(s) when Added to or Removed from the BuddyBoss Profile Type.', 'wishlist-member'), 'lg'); ?>
                </th>
                <th width="1%">{%= data.title %}</th>
            </tr>
        </thead>
        <tbody>
            {% _.each(data, function(group) { %}
                <tr>
                    <td><a href="#" data-toggle="modal" data-target="#buddyboss-types-{%- group.id %}">{%= group.title %}</a></td>
                    <td>
                        <div class="btn-group-action">
                            <a href="#" data-toggle="modal" data-target="#buddyboss-types-{%- group.id %}" class="btn -types-btn" title="Edit Actions"><i class="wlm-icons md-24">edit</i></a>
                        </div>
                    </td>
                </tr>
            {% }); %}
        </tbody>
    </table>
</script>

<script type="text/javascript">
    $('#buddyboss-types-table').empty();
    var member_types = <?php echo json_encode($member_types); ?>;
    var tmpl = _.template($('script#buddyboss-types-template').html(), {variable: 'data'});
    var html = tmpl(member_types);
    $('#buddyboss-types-table').append(html);
</script>
