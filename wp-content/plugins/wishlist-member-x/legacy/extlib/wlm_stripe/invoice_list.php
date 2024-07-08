<table id="wlm-stripe-table2" class="wlm-stripe-invlist">
	<thead>
		<tr>
			<th class="wlm-stripe-invlist-id-head"><?php esc_html_e('ID', 'wishlist-member'); ?></th>
			<th class="wlm-stripe-invlist-date-head"><?php esc_html_e('Date', 'wishlist-member'); ?></th>
			<th class="wlm-stripe-invlist-total-head"><?php esc_html_e('Total', 'wishlist-member'); ?></th>
		</tr>
	</thead>
	<?php if (!empty($invoices)) : ?>
		<?php foreach ($invoices as $i) : ?>
			<?php if ('invoice' === $i['object']) : ?>
				<tr class="wlm-stripe-invlist-row">
					<td class="wlm-stripe-invlist-id-col">
						<a data-id="<?php echo $i['id']; ?>" class="stripe-invoice-detail" href="#stripe-invoice-detail"><?php echo $i['id']; ?></a>
					</td>
					<td class="wlm-stripe-invlist-date-col"><?php echo wlm_date('M d, Y', $i['created']); ?></td>
					<td class="wlm-stripe-invlist-total-col"><?php echo strtoupper($i['currency']); ?> <?php echo number_format($i['total'] / 100, 2); ?></td>
				</tr>
			<?php elseif ('charge' === $i['object']) : ?>
			<?php endif; ?>
		<?php endforeach; ?>
	<?php else : ?>
		<tr>
			<td colspan="3"><p style="text-align: center"><?php esc_html_e('No previous invoices', 'wishlist-member'); ?></p></td>
		</tr>
	<?php endif; ?>
</table>
<p style="text-align: right; font-size: 11px;"><a href="#" class="stripe-invoices-close"><?php esc_html_e('Close', 'wishlist-member'); ?></a></p>

<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery('.stripe-invoices-close').on('click', function(ev) {
			ev.preventDefault();
			jQuery('#stripe-invoice-list').hide('slow');
		});
	});
</script>
