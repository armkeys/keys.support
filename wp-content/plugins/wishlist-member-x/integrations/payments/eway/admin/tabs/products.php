<div id="eway-products-table" class="table-wrapper"></div>
<script type="text/template" id="eway-products-template">
<h3 class="mt-4 mb-2">{%= data.label %}</h3>
<table class="table table-striped">
	<colgroup>
		<col>
		<col width="135">
		<col width="80">
		<col width="135">
		<col width="80">
		<col width="135">
		<col width="1%">
	</colgroup>
	<thead>
		<tr>
			<th>Access</th>
			<th class="text-center">Amount</th>
			<th class="text-center">Recurring</th>
			<th class="text-center">Recurring Amount</th>
			<th class="text-center">Interval</th>
			<th class="text-center">Last Rebill Date</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
		{% _.each(data.levels, function(level) { %}
		<tr class="button-hover">
			<td><a href="#" data-toggle="modal" data-target="#products-eway-{%- level.id %}">{%= level.name %}</a></td>
			<td class="text-center" id="eway-products-amount-{%- level.id %}"></td>
			<td class="text-center" id="eway-products-recur-{%- level.id %}"></td>
			<td class="text-center">
				<span class="eway-recurring-{%- level.id %}" id="eway-products-recuramount-{%- level.id %}"></span>
			</td>
			<td class="text-center">
				<span class="eway-recurring-{%- level.id %}" id="eway-products-interval-{%- level.id %}"></span>
				<span class="eway-recurring-{%- level.id %}" id="eway-products-intervaltype-{%- level.id %}"></span>
			</td>
			<td class="text-center" id="eway-products-rebillend-{%- level.id %}"></td>
			<td class="text-right" style="vertical-align: middle">
				<div class="btn-group-action">
					<a href="#" data-toggle="modal" data-target="#products-eway-{%- level.id %}" class="btn -tags-btn" title="Edit"><i class="wlm-icons md-24">edit</i></a>
				</div>
			</td>
		</tr>
		{% }) %}
	</tbody>
</table>
</script>

<script type="text/javascript">
$('#eway-products-table').empty();
$.each(all_levels, function(k, v) {
	if(!Object.keys(v).length) return true;
	var data = {
		label : post_types[k].labels.name,
		levels : v
	}
	var tmpl = _.template($('script#eway-products-template').html(), {variable: 'data'});
	var html = tmpl(data);
	$('#eway-products-table').append(html);
});
</script>