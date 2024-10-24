<?php

/**
 * Class ITSEC_Settings_Command
 */
class ITSEC_Settings_Command extends WP_CLI_Command {

	/**
	 * List all the settings for a module.
	 *
	 * ## OPTIONS
	 *
	 * <module>
	 * : The module's settings to list.
	 *
	 * [--format=<format>]
	 * : Choose the format to output settings as.
	 * ---
	 * default: table
	 * options:
	 *  - table
	 *  - json
	 *  - csv
	 *  - yaml
	 *
	 * @subcommand list
	 *
	 * @param array $args
	 * @param array $assoc_args
	 */
	public function list_( $args, $assoc_args ) {

		list( $module ) = $args;
		$assoc_args = wp_parse_args( $assoc_args, array( 'format' => 'table' ) );

		$this->assert_valid_module( $module );

		$format = $assoc_args['format'];

		$settings  = ITSEC_Modules::get_settings( $module );
		$formatted = array();

		foreach ( $settings as $setting => $value ) {
			$formatted[] = array(
				'setting' => $setting,
				'value'   => $format === 'table' ? $this->pretty_value( $value ) : $value,
			);
		}

		\WP_CLI\Utils\format_items( $format, $formatted, array( 'setting', 'value' ) );
	}

	/**
	 * Get a specific setting for a module.
	 *
	 * ## OPTIONS
	 *
	 * <module>
	 * : The module to retrieve the setting from.
	 *
	 * <setting>
	 * : The setting name.
	 *
	 * [--format=<format>]
	 * : Choose the format to output settings as.
	 * ---
	 * default: auto
	 * options:
	 *  - auto
	 *  - json
	 *  - table
	 *
	 * @param array $args
	 * @param array $assoc_args
	 */
	public function get( $args, $assoc_args ) {

		list( $module, $setting ) = $args;
		$assoc_args = wp_parse_args( $assoc_args, array( 'format' => 'auto' ) );

		$this->assert_valid_module( $module );

		$value = ITSEC_Modules::get_setting( $module, $setting );

		switch ( $assoc_args['format'] ) {
			case 'json':
				if ( defined( 'JSON_PARTIAL_OUTPUT_ON_ERROR' ) ) {
					echo json_encode( $value, JSON_PARTIAL_OUTPUT_ON_ERROR );
				} else {
					echo json_encode( $value );
				}
				break;
			case 'table':
				$this->pretty_table( $value );
				break;
			case 'auto':
			default:
				if ( $pretty = $this->pretty_value( $value ) ) {
					echo "{$pretty}\n";
				} else {
					if ( is_object( $value ) ) {
						$value = get_object_vars( $value );
					}

					$this->pretty_table( $value );
				}
				break;
		}
	}

	/**
	 * Set a specific setting for a module.
	 *
	 * ## OPTIONS
	 *
	 * <module>
	 * : The module to update the setting for.
	 *
	 * <setting>
	 * : The setting name.
	 *
	 * [<value>]
	 * : The value of the option to add. If omitted, the value is read from STDIN.
	 *
	 * [--format=<format>]
	 * : The serialization format for the value.
	 * ---
	 * default: plaintext
	 * options:
	 *   - plaintext
	 *   - json
	 *
	 * @param array $args
	 * @param array $assoc_args
	 */
	public function set( $args, $assoc_args ) {

		list( $module, $setting ) = $args;

		$value = WP_CLI::get_value_from_arg_or_stdin( $args, 2 );
		$value = WP_CLI::read_value( $value, $assoc_args );

		ITSEC_Core::set_interactive();
		$updated = ITSEC_Modules::set_setting( $module, $setting, $value );

		if ( ! $updated ) {
			WP_CLI::error( __( 'Unexpected error.', 'it-l10n-ithemes-security-pro' ) );
		}

		if ( ! empty( $updated['errors'] ) ) {
			foreach ( $updated['errors'] as $error ) {
				WP_CLI::error( $error, false );
			}

			WP_CLI::halt( 1 );
		}

		foreach ( $updated['messages'] as $message ) {
			WP_CLI::log( $message );
		}

		if ( $updated['saved'] ) {
			WP_CLI::success( __( 'Setting updated.', 'it-l10n-ithemes-security-pro' ) );
		} else {
			WP_CLI::error( __( 'Failed to update setting.', 'it-l10n-ithemes-security-pro' ) );
		}
	}

	/**
	 * Set a specific setting for a module.
	 *
	 * ## OPTIONS
	 *
	 * <module>
	 * : The module to update the setting for.
	 *
	 * [--<field>=<value>]
	 * : The settings to set.
	 *
	 * [--format=<format>]
	 * : The serialization format for the values.
	 * ---
	 * default: plaintext
	 * options:
	 *   - plaintext
	 *   - json
	 *
	 * ## EXAMPLES
	 *
	 * wp itsec settings set-many recaptcha --site_key=AAA --secret_key=BBB
	 *
	 * @subcommand set-many
	 *
	 * @param array $args
	 * @param array $assoc_args
	 */
	public function set_many( $args, $assoc_args ) {

		list( $module ) = $args;

		$values = $assoc_args;
		unset( $values['format'] );

		$settings = ITSEC_Modules::get_settings( $module );

		foreach ( $values as $setting => $value ) {
			$value = WP_CLI::read_value( $value, $assoc_args );

			if ( ! array_key_exists( $setting, $settings ) ) {
				WP_CLI::error( 'Invalid setting ' . $setting );
			}

			$settings[ $setting ] = $value;
		}

		ITSEC_Core::set_interactive();
		$updated = ITSEC_Modules::set_settings( $module, $settings );

		if ( ! $updated ) {
			WP_CLI::error( 'Unexpected error.' );
		}

		if ( ! empty( $updated['errors'] ) ) {
			foreach ( $updated['errors'] as $error ) {
				WP_CLI::error( $error, false );
			}

			WP_CLI::halt( 1 );
		}

		foreach ( $updated['messages'] as $message ) {
			WP_CLI::log( $message );
		}

		if ( $updated['saved'] ) {
			WP_CLI::success( 'Settings updated.' );
		} else {
			WP_CLI::error( 'Failed to update settings.' );
		}
	}

	/**
	 * Reset the settings for a module.
	 *
	 * ## OPTIONS
	 *
	 * <module>
	 * : The module's settings to reset.
	 */
	public function reset( $args, $assoc_args ) {

		list( $module ) = $args;

		$this->assert_valid_module( $module );

		ITSEC_Core::set_interactive();
		$defaults = ITSEC_Modules::get_defaults( $module );
		ITSEC_Modules::set_settings( $module, $defaults );

		WP_CLI::success( 'Settings reset.' );
	}

	/**
	 * Launches the built in editor to edit a module's settings as JSON.
	 *
	 * ## OPTIONS
	 *
	 * <module>
	 * : The module's settings to reset.
	 */
	public function edit( $args ) {
		list( $module ) = $args;
		$this->assert_valid_module( $module );

		$settings = ITSEC_Modules::get_settings( $module );
		$as_json  = wp_json_encode( $settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );

		$edited  = \WP_CLI\Utils\launch_editor_for_input( $as_json, "{$module} Settings - WP-CLI", 'json' );
		$edited  = trim( $edited );
		$decoded = json_decode( $edited, true );

		if ( ! is_array( $decoded ) ) {
			WP_CLI::error( 'JSON is not a valid array.' );
		}

		ITSEC_Core::set_interactive();
		$updated = ITSEC_Modules::set_settings( $module, $decoded );

		if ( ! $updated ) {
			WP_CLI::error( __( 'Unexpected error.', 'it-l10n-ithemes-security-pro' ) );
		}

		if ( is_wp_error( $updated ) ) {
			WP_CLI::error( $updated );
		}

		if ( ! empty( $updated['errors'] ) ) {
			foreach ( $updated['errors'] as $error ) {
				WP_CLI::error( $error, false );
			}

			WP_CLI::halt( 1 );
		}

		foreach ( $updated['messages'] as $message ) {
			WP_CLI::log( $message );
		}

		if ( $updated['saved'] ) {
			WP_CLI::success( __( 'Settings updated.', 'it-l10n-ithemes-security-pro' ) );
		} else {
			WP_CLI::error( __( 'Failed to update settings.', 'it-l10n-ithemes-security-pro' ) );
		}

	}

	/**
	 * Pretty print a table.
	 *
	 * @param array $values
	 *
	 * @return void
	 */
	private function pretty_table( $values ) {

		$formatted = array();

		foreach ( $values as $key => $value ) {
			$pretty = $this->pretty_value( $value );

			$formatted[] = array(
				'key'   => $key,
				'value' => $pretty ?: json_encode( $value ),
			);
		}

		\WP_CLI\Utils\format_items( 'table', $formatted, array( 'key', 'value' ) );
	}

	/**
	 * Pretty print a value.
	 *
	 * @param mixed $value
	 *
	 * @return bool|string
	 */
	private function pretty_value( $value ) {
		if ( is_bool( $value ) ) {
			return $value ? 'true' : 'false';
		} elseif ( is_null( $value ) ) {
			return 'null';
		} elseif ( is_scalar( $value ) ) {
			return $value;
		} elseif ( wp_is_numeric_array( $value ) ) {
			return implode( ',', $value );
		} else {
			return false;
		}
	}

	private function assert_valid_module( $module ) {
		if ( ! in_array( $module, ITSEC_Modules::get_available_modules(), true ) ) {
			WP_CLI::error( sprintf( esc_html__( 'No module exists with the slug %s.', 'it-l10n-ithemes-security-pro' ), $module ) );
		}

	}
}

WP_CLI::add_command( 'itsec settings', 'ITSEC_Settings_Command' );
