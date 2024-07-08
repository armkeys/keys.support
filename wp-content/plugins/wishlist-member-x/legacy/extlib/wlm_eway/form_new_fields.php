<?php
$incremental_index = time();
$fields            = array(
	'nonce' => array(
		'type'  => 'hidden',
		'name'  => 'nonce',
		'label' => '',
		'value' => wp_create_nonce('eway-do-charge'),
		'class' => ''
	),
	'regform_action' => array(
		'type'  => 'hidden',
		'name'  => 'regform_action',
		'label' => '',
		'value' => 'charge',
		'class' => ''
	),
	'charge_type' => array(
		'type'  => 'hidden',
		'name'  => 'charge_type',
		'label' => '',
		'value' => is_user_logged_in() ? 'existing' : 'new',
		'class' => '',
	),
	'subscription' => array(
		'type'  => 'hidden',
		'name'  => 'subscription',
		'label' => '',
		'value' => $settings['subscription'],
		'class' => ''
	),
	'redirect_to' => array(
		'type'  => 'hidden',
		'name'  => 'redirect_to',
		'label' => '',
		'value' => get_permalink(),
		'class' => ''
	),
	'sku' => array(
		'type'  => 'hidden',
		'name'  => 'sku',
		'label' => '',
		'value' => $sku,
		'class' => ''
	),
	// Name fields.
  $incremental_index++ => array('type' => 'heading', 'text' => __( 'Personal Information', 'wishlist-member' ) ),
	'first_name' => array(
		'type'        => 'text',
		'name'        => 'first_name',
		'label'       => __('First Name', 'wishlist-member'),
		'placeholder' => __('First Name', 'wishlist-member'),
		'value'       => $current_user->first_name,
	'col'         => 'col-6',
	),
	'last_name' => array(
		'type'        => 'text',
		'name'        => 'last_name',
		'label'       => __('Last Name', 'wishlist-member'),
		'placeholder' => __('Last Name', 'wishlist-member'),
		'value'       => $current_user->last_name,
	'col'         => 'col-6',
	),
	'email' => array(
		'type'        => 'text',
		'name'        => 'email',
		'label'       => __('Email', 'wishlist-member'),
		'placeholder' => __('Email', 'wishlist-member'),
		'value'       => $current_user->user_email,
		'col'         => 'col-12',
	),
  $incremental_index++ => array('type' => 'break'),
  $incremental_index++ => array('type' => 'heading', 'text' => __( 'Card Details', 'wishlist-member' ) ),
	// Card fields.
	'cc_fields' => array(
	'type' => 'cc_fields',
	'has' => []
	),

  $incremental_index++ => array('type' => 'break'),
);

$heading            = empty($settings['formheading']) ? 'Register for %level' : $settings['formheading'];
$heading            = str_replace('%level', $level_name, $heading);
$panel_button_label =  str_replace('%waiting', '<span class="regform-waiting">...</span> ', $panel_btn_label);
$panel_button_label =  str_replace('%currency', $currency, $panel_button_label);
$panel_button_label =  str_replace('%amount', $amt, $panel_button_label);

$data['fields']             = $fields;
$data['heading']            = $heading;
$data['panel_button_label'] = $panel_button_label;
$data['form_action']        = $thankyouurl;
$data['id']                 = $sku;
$data['logo']               = $logo;
$data['showlogin']          = true;

$currency = '';
if ($settings['subscription']) {
	switch ($settings['rebill_interval_type']) {
		case '1': 
			// Translators: %d: Rebill interval in Days.
			$interval = _n('%d Day', '%d Days', $settings['rebill_interval'], 'wishlist-member');
			break;
		case '2': 
			// Translators: %d: Rebill interval in Weeks.
			$interval = _n('%d Week', '%d Weeks', $settings['rebill_interval'], 'wishlist-member');
			break;
		case '3': 
			// Translators: %d: Rebill interval in Months.
			$interval = _n('%d Month', '%d Months', $settings['rebill_interval'], 'wishlist-member');
			break;
		case '4': 
			// Translators: %d: Rebill interval in Years.
			$interval = _n('%d Year', '%d Years', $settings['rebill_interval'], 'wishlist-member');
			break;
	}
	$interval = sprintf($interval, $settings['rebill_interval']);
	
	if ( $settings['rebill_init_amount'] ) {
		$data['payment_description'] = sprintf(
			// Translators: 1: initial amount, 2: currency, 3: rebill recurring amount, 4: interval, 5: rebill end date.
			__( '%1$.2f %2$s then %3$.2f %2$s every %4$s until %5$s', 'wishlist-member' ), $settings['rebill_init_amount'],
			$currency,
			$settings['rebill_recur_amount'],
			$interval,
			$settings['rebill_end_date']
		);
	} else {
		$data['payment_description'] = sprintf(
			// Translators: 1: rebill recurring amount, 2: currency, 3: interval, 4: rebill end date.
			__( '%1$.2f %2$s every %3$s until %4$s', 'wishlist-member' ), $settings['rebill_init_amount'],
			$settings['rebill_recur_amount'],
			$currency,
			$interval,
			$settings['rebill_end_date']
		);
	}
} else {
	$data['payment_description'] = sprintf('%.2f %s', $settings['rebill_init_amount'], $currency);
}
