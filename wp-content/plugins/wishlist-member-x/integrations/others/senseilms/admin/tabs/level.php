<div id="senseilms-levels-table" class="table-wrapper"></div>
<script type="text/template" id="senseilms-levels-template">
	<table class="table table-striped">
		<thead>
			<tr>
				<th>Membership Level</th>
				<th width="1%"></th>
			</tr>
		</thead>
		<tbody>
			{% _.each(data.levels, function(level) { %}
			<tr>
				<td><a href="#" data-toggle="modal" data-target="#senseilms-levels-{%- level.id %}">{%= level.name %}</a</td>
				<td>
					<div class="btn-group-action">
						<a href="#" data-toggle="modal" data-target="#senseilms-levels-{%- level.id %}" class="btn -levels-btn" title="Edit Actions"><i class="wlm-icons md-24">edit</i></a>
					</div>
				</td>
			</tr>
			{% }); %}
		</tbody>
	</table>
</script>

<script type="text/javascript">
	$('#senseilms-levels-table').empty();
	$.each(all_levels, function(k, v) {
		var data = {
			levels : v
		}
		var tmpl = _.template($('script#senseilms-levels-template').html(), {variable: 'data'});
		var html = tmpl(data);
		$('#senseilms-levels-table').append(html);
		return false;
	});
</script>