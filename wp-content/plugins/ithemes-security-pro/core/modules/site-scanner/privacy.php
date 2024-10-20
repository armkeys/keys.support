<?php

final class ITSEC_Site_Scanner_Privacy {
	public function __construct() {
		add_filter( 'itsec_get_privacy_policy_for_sharing', array( $this, 'get_privacy_policy_for_sharing' ) );
	}

	public function get_privacy_policy_for_sharing( $policy ) {
		$suggested_text = '<strong class="privacy-policy-tutorial">' . __( 'Suggested text:', 'it-l10n-ithemes-security-pro' ) . ' </strong>';

		/* Translators: 1: Link to Sucuri's privacy policy */
		$policy .= "<p>$suggested_text " . __( 'This site is scanned for potential malware and vulnerabilities by the SolidWP Site Scanner. We do not send personal information to the scanner; however, the scanner could find personal information posted publicly (such as in comments) during the scan.', 'it-l10n-ithemes-security-pro' ) . "</p>\n";

		return $policy;
	}
}
new ITSEC_Site_Scanner_Privacy();
