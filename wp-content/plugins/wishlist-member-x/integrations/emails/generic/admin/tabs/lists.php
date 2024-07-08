<div id="generic-lists-table" class="table-wrapper -no-shadow"></div>
<script type="text/template" id="generic-lists-template">
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
				<th>Subscribe Email</th>
				<th>Unsubscribe Email</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			{% _.each(data.levels, function(level) { %}
			<tr class="button-hover">
				<td><a href="#" data-toggle="modal" data-target="#genericar-lists-modal-{%- level.id %}">{%= level.name %}</a></td>
				<td id="genericar-email-{%- level.id %}"></td>
				<td id="genericar-remove-{%- level.id %}"></td>
				<td class="text-right" style="vertical-align: middle">
					<div class="btn-group-action">
						<a href="#" data-toggle="modal" data-target="#genericar-lists-modal-{%- level.id %}" class="btn -tags-btn" title="Edit"><i class="wlm-icons md-24">edit</i></a>
					</div>
				</td>
			</tr>
			{% }); %}
		</tbody>
	</table>
</script>

<script type="text/javascript">
	$('#generic-lists-table').empty();
	$.each(all_levels, function(k, v) {
		var data = {
			label : post_types[k].labels.name,
			levels : v
		}
		var tmpl = _.template($('script#generic-lists-template').html(), {variable: 'data'});
		var html = tmpl(data);
		$('#generic-lists-table').append(html);
		return false;
	});
</script>