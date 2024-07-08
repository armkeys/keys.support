<div id="aweber-lists-table" class="table-wrapper"></div>
<script type="text/template" id="aweber-lists-template">
	<table class="table table-striped">
		<colgroup>
			<col>
			<col width="33%">
			<col width="33%">
			<col width="1%">
		</colgroup>
		<thead>
			<tr>
				<th>Membership Level</th>
				<th>AWeber List Name</th>
				<th>Safe Unsubscribe Email</th>
			</tr>
		</thead>
		<tbody>
			{% _.each(data.levels, function(level) { %}
			<tr class="button-hover">
				<td><a href="#" data-toggle="modal" data-target="#aweber-lists-modal-{%- level.id %}">{%= level.name %}</a></td>
				<td id="aweber-email-{%- level.id %}"></td>
				<td id="aweber-remove-{%- level.id %}"></td>
				<td class="text-right" style="vertical-align: middle">
					<div class="btn-group-action">
						<a href="#" data-toggle="modal" data-target="#aweber-lists-modal-{%- level.id %}" class="btn -tags-btn" title="Edit"><i class="wlm-icons md-24">edit</i></a>
					</div>
				</td>
			</tr>
			{% }); %}
		</tbody>
	</table>
</script>

<script type="text/javascript">
	$('#aweber-lists-table').empty();
	$.each(all_levels, function(k, v) {
		var data = {
			label : post_types[k].labels.name,
			levels : v
		}
		var tmpl = _.template($('script#aweber-lists-template').html(), {variable: 'data'});
		var html = tmpl(data);
		$('#aweber-lists-table').append(html);
		return false;
	});
</script>