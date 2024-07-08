<div id="plugnpaid-products-table" class="table-wrapper"></div>
<script type="text/template" id="plugnpaid-products-template">
	<h3 class="mt-4 mb-2">{%= data.label %}</h3>
	<table class="table table-plugnpaidd">
		<colgroup>
			<col>
			<col width="50%">
			<col width="1%">
		</colgroup>
		<thead>
			<tr>
				<th>Name</th>
				<th>Product</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			{% _.each(data.levels, function(level) { %}
			<tr class="button-hover">
				<td><a href="#" data-toggle="modal" data-target="#products-plugnpaid-{%- level.id %}">{%= level.name %}</a></td>
				<td>
					<span id="plugnpaid-product-{%- level.id %}" class="plugnpaid-product-{%- level.id %}">
						{% if(WLM3ThirdPartyIntegration.plugnpaid.plugnpaid_products[level.id]) { try { %}
						{%- WLM3ThirdPartyIntegration.plugnpaid.products_options[WLM3ThirdPartyIntegration.plugnpaid.plugnpaid_products[level.id]].text %}
						{% } catch(e) {} } %}
					</span>
				</td>
				<td class="text-right">
					<div class="btn-group-action">
						<a href="#" data-toggle="modal" data-target="#products-plugnpaid-{%- level.id %}" class="btn -tags-btn" title="Edit"><i class="wlm-icons md-24">edit</i></a>
					</div>
				</td>
			</tr>
			{% }); %}
		</tbody>
	</table>
</script>
