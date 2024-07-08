<div id="getresponse-lists-table" class="table-wrapper -no-shadow"></div>
<script type="text/template" id="getresponse-lists-template">
	<table class="table table-striped">
		<thead>
			<tr>
				<th>Membership Level</th>
				<th>Autoresponder Email</th>
				<th>Unsubscribe Email</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			{% _.each(data.levels, function(level) { %}
			<tr class="button-hover">
				<td><a href="#" data-toggle="modal" data-target="#getresponselegacy-lists-modal-{%- level.id %}">{%= level.name %}</a></td>
				<td id="getresponselegacy-email-{%- level.id %}"></td>
				<td id="getresponselegacy-remove-{%- level.id %}"></td>
				<td class="text-right" style="vertical-align: middle">
					<div class="btn-group-action">
						<a href="#" data-toggle="modal" data-target="#getresponselegacy-lists-modal-{%- level.id %}" class="btn -tags-btn" title="Edit"><i class="wlm-icons md-24">edit</i></a>
					</div>
				</td>
			</tr>
			{% }); %}
		</tbody>
	</table>
</script>

<script type="text/javascript">
	$('#getresponse-lists-table').empty();
	$.each(all_levels, function(k, v) {
		var data = {
			label : post_types[k].labels.name,
			levels : v
		}
		var tmpl = _.template($('script#getresponse-lists-template').html(), {variable: 'data'});
		var html = tmpl(data);
		$('#getresponse-lists-table').append(html);
		return false;
	});
</script>