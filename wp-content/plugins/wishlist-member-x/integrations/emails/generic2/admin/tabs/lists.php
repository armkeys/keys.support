<div class="row">
	<div class="col-md-12">
		<p>
			<a href="https://wishlistmember.com/docs/generic-integration-email-providers/" target="_blank">Click Here for Documentation</a>
		</p>
	</div>
</div>
<div id="generic2-lists-table" class="table-wrapper -no-shadow"></div>
<script type="text/template" id="generic2-lists-template">
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
				<td><a href="#" data-toggle="modal" data-target="#generic2-lists-modal-{%- level.id %}">{%= level.name %}</a></td>
				<td class="text-right" style="vertical-align: middle">
					<div class="btn-group-action">
						<a href="#" data-toggle="modal" data-target="#generic2-lists-modal-{%- level.id %}" class="btn -tags-btn" title="Edit"><i class="wlm-icons md-24">edit</i></a>
					</div>
				</td>
			</tr>
			{% }); %}
		</tbody>
	</table>
</script>

<script type="text/javascript">
	$('#generic2-lists-table').empty();
	$.each(all_levels, function(k, v) {
		var data = {
			label : post_types[k].labels.name,
			levels : v
		}
		var tmpl = _.template($('script#generic2-lists-template').html(), {variable: 'data'});
		var html = tmpl(data);
		$('#generic2-lists-table').append(html);
		return false;
	});
</script>