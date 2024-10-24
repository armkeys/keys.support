<?php

namespace iThemesSecurity\WebAuthn;

use iThemesSecurity\Contracts\Runnable;

final class Module implements Runnable {

	/** @var Runnable[] */
	private $runnable;

	public function __construct( Runnable ...$runnable ) {
		$this->runnable = $runnable;
	}

	public function run() {
		foreach ( $this->runnable as $runnable ) {
			$runnable->run();
		}

		add_action( 'itsec_scheduled_clear-trashed-passkeys', [ $this, 'clear_trashed_passkeys' ] );
		add_action( 'itsec_passwordless_login_enqueue_profile_scripts', [ $this, 'enqueue_profile' ] );
		add_filter( 'debug_information', [ $this, 'add_site_health_info' ], 11 );
	}

	public function clear_trashed_passkeys() {
		\ITSEC_Modules::get_container()
		              ->get( PublicKeyCredential_Record_Repository::class )
		              ->delete_trashed_credentials( 7 );
	}

	public function enqueue_profile( \WP_User $user ) {
		if ( $user->ID !== wp_get_current_user()->ID ) {
			return;
		}

		wp_enqueue_style( 'itsec-webauthn-profile' );
		wp_enqueue_script( 'itsec-webauthn-profile' );

		$credentials = rest_do_request( '/ithemes-security/v1/webauthn/credentials' );

		if ( ! $credentials->is_error() ) {
			wp_add_inline_script(
				'itsec-webauthn-profile',
				sprintf(
					"wp.data.dispatch('%s').receiveCredentials( %s );",
					'ithemes-security/webauthn',
					wp_json_encode( $credentials->get_data() )
				)
			);
		}
	}

	public function add_site_health_info( $info ) {
		global $wpdb;

		$users = count( $wpdb->get_col(
			"SELECT COUNT(*) FROM {$wpdb->base_prefix}itsec_webauthn_credentials GROUP BY webauthn_user",
		) );
		$registered = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->base_prefix}itsec_webauthn_credentials",
		);

		$info['solid-security']['fields']['passkeys-users'] = [
			'label' => __( 'Passkeys Users', 'it-l10n-ithemes-security-pro' ),
			'value' => $users,
			'debug' => $users,
		];

		$info['solid-security']['fields']['passkeys-total'] = [
			'label' => __( 'Registered Passkeys', 'it-l10n-ithemes-security-pro' ),
			'value' => $registered,
			'debug' => $registered,
		];

		return $info;
	}
}
