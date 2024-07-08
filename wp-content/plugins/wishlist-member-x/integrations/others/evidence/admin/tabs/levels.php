<div id="evidence-lists-table" class="table-wrapper -no-shadow"></div>
<script type="text/template" id="evidence-lists-template">
	<table class="table table-striped">
		<colgroup>
			<col>
			<col width="50">
			<col width="1%">
		</colgroup>
		<thead>
			<tr>
				<th>Membership Level</th>
				<th>Active</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			{% _.each(data.levels, function(level) { %}
			<tr id="evidence-{%- level.id %}">
				<td><a href="#" data-toggle="modal" data-target="#evidence-lists-modal-{%- level.id %}">{%= level.name %}</a></td>
				<td class="text-center">
					<a href="#" data-level="{%- level.id %}" class="toggle-evidence-active {%- WLM3ThirdPartyIntegration.evidence.evidence_settings.active[level.id] == '1' ? '' : 'evidence-inactive' %}"><i class="wlm-icons md-24">check_circle</i></a>
				</td>
				<td class="text-right" style="vertical-align: middle">
					<div class="btn-group-action">
						<a href="#" data-toggle="modal" data-target="#evidence-lists-modal-{%- level.id %}" class="btn -tags-btn" title="Edit"><i class="wlm-icons md-24">edit</i></a>
					</div>
				</td>
			</tr>
			{% }); %}
		</tbody>
	</table>
</script>

<script type="text/javascript">
	$('#evidence-lists-table').empty();
	$.each(all_levels, function(k, v) {
		var data = {
			label : post_types[k].labels.name,
			levels : v
		}
		var tmpl = _.template($('script#evidence-lists-template').html(), {variable: 'data'});
		var html = tmpl(data);
		$('#evidence-lists-table').append(html);
		return false;
	});
</script>