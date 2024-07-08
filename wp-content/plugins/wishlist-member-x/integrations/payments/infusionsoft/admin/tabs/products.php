<div id="infusionsoft-products-table" class="table-wrapper"></div>
<script type="text/template" id="infusionsoft-products-template">
	<h3 class="mt-4 mb-2">{%= data.label %}</h3>
	<table class="table table-striped">
		<colgroup>
			<col>
			<col width="30%">
			<col width="1%">
		</colgroup>
		<thead>
			<tr>
				<th>Name</th>
				<th>SKU</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			{% _.each(data.levels, function(level) { %}
			<tr class="button-hover">
				<td><a href="#" data-toggle="modal" data-target="#infusionsoft-products-modal-{%- level.id %}" onclick="loadLevelPopUp({%- level.id %})">{%= level.name %}</a></td>
				<td>{%= level.id %}</td>
				<td class="text-right">
					<div class="btn-group-action">
						<a href="#" data-toggle="modal" data-target="#infusionsoft-products-modal-{%- level.id %}" class="btn -tags-btn" title="Edit Tag Settings" onclick="loadLevelPopUp({%- level.id %})"><i class="wlm-icons md-24">edit</i></a>
					</div>
				</td>
			</tr>
			{% }); %}
		</tbody>
	</table>
</script>
