<div id="recurly-products-table" class="table-wrapper"></div>
<script type="text/template" id="recurly-products-template">
<h3 class="mt-4 mb-2">{%= data.label %}</h3>
<table class="table table-striped">
	<colgroup>
		<col>
		<col width="150">
		<col width="40%">
		<col width="1%">
	</colgroup>
	<thead>
		<tr>
			<th>Access</th>
			<th>SKU</th>
			<th>Plan</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
		{% _.each(data.levels, function(level) { %}
		<tr class="button-hover">
			<td><a href="#" data-toggle="modal" data-target="#products-recurly-{%- level.id %}">{%= level.name %}</a></td>
			<td>{%- level.id %}</td>
			<td id="recurly-product-plan-{%- level.id %}"></td>
			<td class="text-right">
				<div class="btn-group-action">
					<a href="#" data-toggle="modal" data-target="#products-stripe-{%- level.id %}" class="btn -tags-btn" title="Edit"><i class="wlm-icons md-24">edit</i></a>
				</div>
			</td>
		</tr>
		{% }) %}
	</tbody>
</table>
</script>

<script type="text/javascript">
$('#recurly-products-table').empty();
$.each(all_levels, function(k, v) {
	if(!Object.keys(v).length) return true;
	var data = {
		label : post_types[k].labels.name,
		levels : v
	}
	var tmpl = _.template($('script#recurly-products-template').html(), {variable: 'data'});
	var html = tmpl(data);
	$('#recurly-products-table').append(html);
});
</script>