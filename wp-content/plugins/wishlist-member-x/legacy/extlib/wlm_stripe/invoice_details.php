<style>
 .stripe_invoice_td {
	border: 1px solid;
 }
</style>

<h3><?php esc_html_e('Invoice Details', 'wishlist-member'); ?></h3>
<table>
	<tr>
		<td><?php esc_html_e('Invoice ID', 'wishlist-member'); ?></td>
		<td><?php echo $inv->id; ?></td>
	</tr>
	<tr>
		<td><?php esc_html_e('Date', 'wishlist-member'); ?></td>
		<td><?php echo wlm_date('M d Y', $inv->created); ?></td>
	</tr>
	<tr>
		<td><?php esc_html_e('Customer', 'wishlist-member'); ?></td>
		<td><?php echo $cust->description; ?></td>
	</tr>
</table>
<h3><?php esc_html_e('Summary', 'wishlist-member'); ?></h3>
<table width="100%">
	<tr>
		<td width="50%"></td>
		<td><?php esc_html_e('Subtotal:', 'wishlist-member'); ?> </td>
		<td><?php echo strtoupper($inv->currency); ?> <strong><?php echo number_format($inv->subtotal / 100, 2); ?></strong></td>
	</tr>
	<tr>
		<td width="50%"></td>
		<td><?php esc_html_e('Total:', 'wishlist-member'); ?> </td>
		<td><?php echo strtoupper($inv->currency); ?> <strong><?php echo number_format($inv->total / 100, 2); ?></strong></td>
	</tr>
	<tr>
		<td width="50%"></td>
		<td><strong><?php esc_html_e('Amount Due:', 'wishlist-member'); ?> </strong></td>
		<td><?php echo strtoupper($inv->currency); ?> <strong><?php echo number_format($inv->total / 100, 2); ?></strong></td>
	</tr>
</table>
<h3><?php esc_html_e('Line Items', 'wishlist-member'); ?></h3>
<table width="100%"> 
	<?php if ( isset($inv->lines ) && count( $inv->lines ) > 0 ) : ?>
		<?php foreach ($inv->lines as $ss) : ?>
			<tr>
				<td class="stripe_invoice_td">
					<?php echo $ss->description; ?>
				</td>
				<td class="stripe_invoice_td">
					<?php $plan = $ss->plan; ?>
					<?php echo strtoupper(( $ss->currency )); ?> <?php echo sprintf('%s (%s/%s)', $plan->name, number_format($plan->amount / 100, 2), $plan->interval); ?>
				</td>
				<td class="stripe_invoice_td"><?php echo sprintf('%s - %s', wlm_date('M d, Y', $ss->period->start), wlm_date('M d, Y', $ss->period->end)); ?></td>
				<td class="stripe_invoice_td"><?php echo strtoupper(( $ss->currency )); ?> <?php echo number_format($ss->amount / 100, 2); ?></td>
			</tr>
		<?php endforeach; ?>
	<?php endif; ?>
	<?php if ( isset($inv->lines->invoiceitems ) && count( $inv->lines->invoiceitems ) > 0 ) : ?>
		<?php foreach ($inv->lines->invoiceitems as $ss) : ?>
			<tr>
				<td width="50%">
					<?php echo $ss->description; ?>
				</td>

				<td><?php echo wlm_date('M d, Y', $ss->date); ?></td>
				<td><?php echo strtoupper(( $ss->currency )); ?> <?php echo number_format($ss->amount / 100, 2); ?></td>
			</tr>
		<?php endforeach; ?>
	<?php endif; ?>
	<?php if ( isset($inv->lines->prorations ) && count( $inv->lines->prorations ) > 0 ) : ?>
		<?php foreach ($inv->lines->prorations as $ss) : ?>
			<tr>
				<td width="50%">
					<?php echo $ss->description; ?>
				</td>

				<td><?php echo wlm_date('M d, Y', $ss->date); ?></td>
				<td><?php echo strtoupper(( $ss->currency )); ?> <?php echo number_format($ss->amount / 100, 2); ?></td>
			</tr>
		<?php endforeach; ?>
	<?php endif; ?>
</table>
