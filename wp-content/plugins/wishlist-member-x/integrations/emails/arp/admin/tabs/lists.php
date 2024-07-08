<div id="arp-lists-table" class="table-wrapper -no-shadow"></div>
<script type="text/template" id="arp-lists-template">
	<table class="table table-striped">
		<colgroup>
			<col>
			<col width="400">
			<col width="150">
			<col width="1%">
		</colgroup>
		<thead>
			<tr>
				<th>Membership Level</th>
				<th>AutoResponder ID</th>
				<th class="text-center">Unsubscribe if Removed from Level</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			{% _.each(data.levels, function(level) { %}
			<tr class="button-hover">
				<td><a href="#" data-toggle="modal" data-target="#arp-lists-modal-{%- level.id %}">{%= level.name %}</a></td>
				<td id="arp-lists-{%- level.id %}"></td>
				<td id="arp-unsubscribe-{%- level.id %}" class="text-center"></td>
				<td class="text-right" style="vertical-align: middle">
					<div class="btn-group-action">
						<a href="#" data-toggle="modal" data-target="#arp-lists-modal-{%- level.id %}" class="btn -tags-btn" title="Edit"><i class="wlm-icons md-24">edit</i></a>
					</div>
				</td>
			</tr>
			{% }); %}
		</tbody>
	</table>
</script>

<script type="text/javascript">
	$('#arp-lists-table').empty();
	$.each(all_levels, function(k, v) {
		var data = {
			label : post_types[k].labels.name,
			levels : v
		}
		var tmpl = _.template($('script#arp-lists-template').html(), {variable: 'data'});
		var html = tmpl(data);
		$('#arp-lists-table').append(html);
		return false;
	});
</script>