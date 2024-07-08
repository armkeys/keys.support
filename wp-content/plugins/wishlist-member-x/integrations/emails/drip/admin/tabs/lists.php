<div id="drip-lists-table" class="table-wrapper"></div>
<script type="text/template" id="drip-lists-template">
	<table class="table table-striped">
		<colgroup>
			<col>
			<col width="35%">
			<col width="150">
			<col width="150">
			<col width="1%">
		</colgroup>
		<thead>
			<tr>
				<th>Name</th>
				<th>Campaign</th>
				<th class="text-center">Double Opt-in</th>
				<th class="text-center">Unsubscribe if Removed from Level</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			{% _.each(data.levels, function(level) { %}
			<tr>
				<td><a href="#" data-toggle="modal" data-target="#driplegacy-lists-modal-{%- level.id %}">{%= level.name %}</a></td>
				<td id="driplegacy-lists-{%- level.id %}"></td>
				<td id="driplegacy-double-{%- level.id %}" class="text-center"></td>
				<td id="driplegacy-unsubscribe-{%- level.id %}" class="text-center"></td>
				<td class="text-right" style="vertical-align: middle">
					<div class="btn-group-action">
						<a href="#" data-toggle="modal" data-target="#driplegacy-lists-modal-{%- level.id %}" class="btn -tags-btn" title="Edit"><i class="wlm-icons md-24">edit</i></a>
					</div>
				</td>
			</tr>
			{% }); %}
		</tbody>
	</table>
</script>

<script type="text/javascript">
	$('#drip-lists-table').empty();
	$.each(all_levels, function(k, v) {
		var data = {
			label : post_types[k].labels.name,
			levels : v
		}
		var tmpl = _.template($('script#drip-lists-template').html(), {variable: 'data'});
		var html = tmpl(data);
		$('#drip-lists-table').append(html);
		return false;
	});
</script>