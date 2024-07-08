<?php
/**
 * Object Oriented PHP SDK for Infusionsoft
 *
 * @CreatedBy Justin Morris on 09-10-08
 * @UpdatedBy Michael Fairchild
 * @Updated 01/14/2014
 * @iSDKVersion 1.8.6
 * @ApplicationVersion 1.29.9
 */

if (!function_exists('xmlrpc_encode_entitites')) {
	include 'xmlrpc-3.0/lib/xmlrpc.inc';
}

class WLMiSDKException extends Exception {

}

class WLMiSDK {
	private static $handle;
	public $logname        = '';
	public $loggingEnabled = 0;

	/**
	 * Creates and tests the API Connection to the Application
	 *
	 * @method cfgCon
	 * @param $name - Application Name
	 * @param string $key - API Key
	 * @param string $dbOn - Error Handling On
	 * @param string $type - Infusionsoft or Mortgage Pro
	 * @return bool
	 * @throws WLPiSDKException
	 */
	public function cfgCon( $name, $key = '', $dbOn = 'on') {
		$this->debug = ( ( 'on' === $key || 'off' === $key || 'kill' === $key || 'throw' === $key ) ? $key : $dbOn );

		if ('' != $key && 'on' !== $key && 'off' !== $key && 'kill' !== $key && 'throw' !== $key) {
			$this->key = $key;
		} else {
			include 'conn.cfg.php';
			$appLines = $connInfo;
			foreach ($appLines as $appLine) {
				$details[substr($appLine, 0, strpos($appLine, ':'))] = explode(':', $appLine);
			}
			$appname   = $details[$name][1];
			$this->key = $details[$name][3];
		}

		if (!isset($appname)) {
			$appname = $name;
		}

		$this->client = new xmlrpc_client("https://$appname.infusionsoft.com/api/xmlrpc");

		/* Return Raw PHP Types */
		$this->client->return_type = 'phpvals';

		/* SSL Certificate Verification */
		$this->client->setSSLVerifyPeer(true);
		$this->client->setCaCertificate(( __DIR__ != '__DIR__' ? __DIR__ : dirname(__FILE__) ) . '/infusionsoft.pem');
		//$this->client->setDebug(2);

		$this->encKey = php_xmlrpc_encode($this->key);

		/* Connection verification */

		if ( 'on' === $dbOn ) {
			try {
				$connected = $this->dsGetSetting('Application', 'enabled');

				if (false !== strpos($connected, 'ERROR')) {
					throw new WLPiSDKException($connected);
				}
				return true;
			} catch (WLPiSDKException $e) {
				throw new WLPiSDKException($e->getMessage());
				return false;
			}
		} else {
			$connected = $this->dsGetSetting('Application', 'enabled');
			if ('yes' === $connected) {
				return true;
			} else {
				return false;
			}
		}
	}

	/**
	 * Connect and Obtain an API key from a vendor key
	 *
	 * @method getTemporaryKey
	 * @param string $name - Application Name
	 * @param string $user - Username
	 * @param string $pass - Password
	 * @param string $key - Vendor Key
	 * @param string $dbOn - Error Handling On
	 * @return bool
	 * @throws WLPiSDKException
	 */
	public function vendorCon( $name, $user, $pass, $key = '', $dbOn = 'on') {
		$this->debug = ( ( 'on' === $key || 'off' === $key || 'kill' === $key || 'throw' === $key ) ? $key : $dbOn );

		if ('' != $key && 'on' !== $key && 'off' !== $key && 'kill' !== $key && 'throw' !== $key) {
			$this->client = new xmlrpc_client("https://$name.infusionsoft.com/api/xmlrpc");
			$this->key    = $key;
		} else {
			include 'conn.cfg.php';
			$appLines = $connInfo;
			foreach ($appLines as $appLine) {
				$details[substr($appLine, 0, strpos($appLine, ':'))] = explode(':', $appLine);
			}
			if (!empty($details[$name])) {
				if ('i' == $details[$name][2]) {
					$this->client = new xmlrpc_client('https://' . $details[$name][1] .
						'.infusionsoft.com/api/xmlrpc');
				} elseif ('m' == $details[$name][2]) {
					$this->client = new xmlrpc_client('https://' . $details[$name][1] .
						'.mortgageprocrm.com/api/xmlrpc');
				} else {
					throw new WLPiSDKException('Invalid application name: "' . $name . '"');
				}
			} else {
				throw new WLPiSDKException('Application Does Not Exist: "' . $name . '"');
			}
			$this->key = $details[$name][3];
		}

		/* Return Raw PHP Types */
		$this->client->return_type = 'phpvals';

		/* SSL Certificate Verification */
		$this->client->setSSLVerifyPeer(true);
		$this->client->setCaCertificate(( __DIR__ != '__DIR__' ? __DIR__ : dirname(__FILE__) ) . '/infusionsoft.pem');

		$carray = array(
			php_xmlrpc_encode($this->key),
			php_xmlrpc_encode($user),
			php_xmlrpc_encode(md5($pass)));

		$this->key = $this->methodCaller('DataService.getTemporaryKey', $carray);

		$this->encKey = php_xmlrpc_encode($this->key);

		try {
			$connected = $this->dsGetSetting('Application', 'enabled');
		} catch (WLPiSDKException $e) {
			throw new WLPiSDKException('Connection Failed');
		}
		return true;
	}

	/**
	 * Worthless public function, used to validate a connection
	 *
	 * @method echo
	 * @param string $txt
	 * @return int|mixed|string
	 */
	public function appEcho( $txt) {
		$carray = array(
			php_xmlrpc_encode($txt));

		return $this->methodCaller('DataService.echo', $carray);
	}

	/**
	 * Builds XML and Sends the Call
	 *
	 * @method Method Caller
	 * @param string $service
	 * @param array $callArray
	 * @return int|mixed|string
	 * @throws WLPiSDKException
	 */
	public function methodCaller( $service, $callArray) {
		/* Set up the call */
		$call = new xmlrpcmsg($service, $callArray);

		if ('DataService.getTemporaryKey' != $service) {
			array_unshift($call->params, $this->encKey);
		}

		/* Send the call */
		$now    = time();
		$start  = microtime();
		$result = $this->client->send($call);

		$stop = microtime();
		/* Check the returned value to see if it was successful and return it */
		if (!$result->faultCode()) {
			if (1 == $this->loggingEnabled) {
				$this->log(array('Method' => $service, 'Call' => $callArray, 'Start' => $start, 'Stop' => $stop, 'Now' => $now, 'Result' => $result, 'Error' => 'No', 'ErrorCode' => 'No Error Code Received'));
			}
			return $result->value();
		} else {
			if (1 == $this->loggingEnabled) {
				$this->log(array('Method' => $service, 'Call' => $callArray, 'Start' => $start, 'Stop' => $stop, 'Now' => $now, 'Result' => $result, 'Error' => 'Yes', 'ErrorCode' => 'ERROR: ' . $result->faultCode() . ' - ' . $result->faultString()));
			}
			if ('kill' === $this->debug) {
				die('ERROR: ' . esc_html( $result->faultCode() ) . ' - ' .
					esc_html( $result->faultString() ));
			} elseif ('on' === $this->debug) {
				return 'ERROR: ' . $result->faultCode() . ' - ' .
				$result->faultString();
			} elseif ('throw' === $this->debug) {
				throw new WLPiSDKException($result->faultString(), $result->faultCode());
			}
		}

	}

	/**
	 * Affiliate Program Service
	 */

	/**
	 * Gets a list of all of the affiliates with their contact data for the specified program.  This includes all of the custom fields defined for the contact and affiliate records that are retrieved.
	 *
	 * @method getAffiliatesByProgram
	 * @param int $programId
	 * @return array
	 */
	public function getAffiliatesByProgram( $programId) {
		$carray = array(
			php_xmlrpc_encode((int) $programId));
		return $this->methodCaller('AffiliateProgramService.getAffiliatesByProgram', $carray);
	}

	/**
	 * Gets a list of all of the Affiliate Programs for the Affiliate specified.
	 *
	 * @method getProgramsForAffiliate
	 * @param int $affiliateId
	 * @return array
	 */
	public function getProgramsForAffiliate( $affiliateId) {
		$carray = array(
			php_xmlrpc_encode((int) $affiliateId));
		return $this->methodCaller('AffiliateProgramService.getProgramsForAffiliate', $carray);
	}

	/**
	 * Gets a list of all of the Affiliate Programs that are in the application.
	 *
	 * @method getAffiliatePrograms
	 * @return int|mixed|string
	 */
	public function getAffiliatePrograms() {
		$carray = array();
		return $this->methodCaller('AffiliateProgramService.getAffiliatePrograms', $carray);
	}

	/**
	 * Gets a list of all of the resources that are associated to the Affiliate Program specified.
	 *
	 * @method getResourcesForAffiliateProgram
	 * @param int $programId
	 * @return array
	 */
	public function getResourcesForAffiliateProgram( $programId) {
		$carray = array(
			php_xmlrpc_encode((int) $programId));
		return $this->methodCaller('AffiliateProgramService.getResourcesForAffiliateProgram', $carray);
	}

	/**
	 * Affiliate Service
	 */

	/**
	 * Returns all clawbacks in a date range
	 *
	 * @method affClawbacks
	 * @param int $affId
	 * @param date $startDate
	 * @param date $endDate
	 * @return array
	 */
	public function affClawbacks( $affId, $startDate, $endDate) {
		$carray = array(
			php_xmlrpc_encode((int) $affId),
			php_xmlrpc_encode($startDate, array('auto_dates')),
			php_xmlrpc_encode($endDate, array('auto_dates')));
		return $this->methodCaller('APIAffiliateService.affClawbacks', $carray);
	}

	/**
	 * Returns all commissions in a date range
	 *
	 * @method affCommissions
	 * @param int $affId
	 * @param date $startDate
	 * @param date $endDate
	 * @return array
	 */
	public function affCommissions( $affId, $startDate, $endDate) {
		$carray = array(
			php_xmlrpc_encode((int) $affId),
			php_xmlrpc_encode($startDate, array('auto_dates')),
			php_xmlrpc_encode($endDate, array('auto_dates')));
		return $this->methodCaller('APIAffiliateService.affCommissions', $carray);
	}

	/**
	 * Returns all affiliate payouts in a date range
	 *
	 * @method affPayouts
	 * @param int $affId
	 * @param date $startDate
	 * @param date $endDate
	 * @return array
	 */
	public function affPayouts( $affId, $startDate, $endDate) {
		$carray = array(
			php_xmlrpc_encode((int) $affId),
			php_xmlrpc_encode($startDate, array('auto_dates')),
			php_xmlrpc_encode($endDate, array('auto_dates')));
		return $this->methodCaller('APIAffiliateService.affPayouts', $carray);
	}

	/**
	 * Returns a list with each row representing a single affiliates totals represented by a map with key (one of the names above, and value being the total for that variable)
	 *
	 * @method affRunningTotals
	 * @param array $affList
	 * @return array
	 */
	public function affRunningTotals( $affList) {
		$carray = array(
			php_xmlrpc_encode($affList));
		return $this->methodCaller('APIAffiliateService.affRunningTotals', $carray);
	}

	/**
	 * Returns how much the specified affiliates are owed
	 *
	 * @method affSummary
	 * @param array $affList
	 * @param date $startDate
	 * @param date $endDate
	 * @return array
	 */
	public function affSummary( $affList, $startDate, $endDate) {
		$carray = array(
			php_xmlrpc_encode($affList),
			php_xmlrpc_encode($startDate, array('auto_dates')),
			php_xmlrpc_encode($endDate, array('auto_dates')));
		return $this->methodCaller('APIAffiliateService.affSummary', $carray);
	}

	/**
	 * Returns redirect links for affiliate specified
	 *
	 * @method getRedirectLinksForAffiliate
	 * @param $affiliateId
	 * @return int|mixed|string
	 */
	public function getRedirectLinksForAffiliate( $affiliateId) {
		$carray = array(
			php_xmlrpc_encode((int) $affiliateId));
		return $this->methodCaller('AffiliateService.getRedirectLinksForAffiliate', $carray);
	}

	/**
	 * Contact Service
	 */

	/**
	 * Add Contact to Infusionsoft (no duplicate checking)
	 *
	 * @method add
	 * @param array $cMap
	 * @param string $optReason
	 * @return int
	 */
	public function addCon( $cMap, $optReason = '') {

		$carray = array(
			php_xmlrpc_encode($cMap, array('auto_dates')));

		$conID = $this->methodCaller('ContactService.add', $carray);
		if (!empty($cMap['Email'])) {
			if (empty( $optReason )) {
				$this->optIn($cMap['Email']);
			} else {
				$this->optIn($cMap['Email'], $optReason);
			}
		}
		return $conID;
	}

	/**
	 * Update an existing contact
	 *
	 * @method update
	 * @param int $cid
	 * @param array $cMap
	 * @return int
	 */
	public function updateCon( $cid, $cMap) {

		$carray = array(
			php_xmlrpc_encode((int) $cid),
			php_xmlrpc_encode($cMap, array('auto_dates')));
		return $this->methodCaller('ContactService.update', $carray);
	}

	/**
	 * Merge 2 contacts
	 *
	 * @method merge
	 * @param int $cid
	 * @param int $dcid
	 * @return int
	 */
	public function mergeCon( $cid, $dcid) {
		$carray = array(
			php_xmlrpc_encode($cid),
			php_xmlrpc_encode($dcid));

		return $this->methodCaller('ContactService.merge', $carray);
	}

	/**
	 * Finds all contact with an email address
	 *
	 * @method findbyEmail
	 * @param string $eml
	 * @param array $fMap
	 * @return array
	 */
	public function findByEmail( $eml, $fMap) {

		$carray = array(
			php_xmlrpc_encode($eml),
			php_xmlrpc_encode($fMap));
		return $this->methodCaller('ContactService.findByEmail', $carray);
	}

	/**
	 * Loads a contacts data
	 *
	 * @method load
	 * @param int $cid
	 * @param array $rFields
	 * @return array
	 */
	public function loadCon( $cid, $rFields) {

		$carray = array(
			php_xmlrpc_encode((int) $cid),
			php_xmlrpc_encode($rFields));
		return $this->methodCaller('ContactService.load', $carray);
	}

	/**
	 * Apply a Tag to a Contact
	 *
	 * @method addToGroup
	 * @param int $cid
	 * @param int $gid
	 * @return bool
	 */
	public function grpAssign( $cid, $gid) {
		$carray = array(
			php_xmlrpc_encode((int) $cid),
			php_xmlrpc_encode((int) $gid));
		return $this->methodCaller('ContactService.addToGroup', $carray);
	}

	/**
	 * Remove a Tag from a Contact
	 *
	 * @method removeFromGroup
	 * @param int $cid
	 * @param int $gid
	 * @return bool
	 */
	public function grpRemove( $cid, $gid) {
		$carray = array(
			php_xmlrpc_encode((int) $cid),
			php_xmlrpc_encode((int) $gid));
		return $this->methodCaller('ContactService.removeFromGroup', $carray);
	}

	/**
	 * Resumes a legacy followup sequence a contact is in
	 *
	 * @method resumeCampaignForContact
	 * @param int $cid
	 * @param int $sequenceId
	 * @return bool
	 */
	public function resumeCampaignForContact( $cid, $sequenceId) {
		$carray = array(
			php_xmlrpc_encode((int) $cid),
			php_xmlrpc_encode((int) $sequenceId));
		return $this->methodCaller('ContactService.resumeCampaignForContact', $carray);
	}

	/**
	 * Adds a contact to a legacy followup sequence
	 *
	 * @method addToCampaign
	 * @param int $cid
	 * @param int $campId
	 * @return bool
	 */
	public function campAssign( $cid, $campId) {
		$carray = array(
			php_xmlrpc_encode((int) $cid),
			php_xmlrpc_encode((int) $campId));
		return $this->methodCaller('ContactService.addToCampaign', $carray);
	}

	/**
	 * Gets next step in a legacy followup sequence
	 *
	 * @method getNextCampaignStep
	 * @param int $cid
	 * @param int $campId
	 * @return array
	 */
	public function getNextCampaignStep( $cid, $campId) {
		$carray = array(
			php_xmlrpc_encode((int) $cid),
			php_xmlrpc_encode((int) $campId));
		return
			$this->methodCaller('ContactService.getNextCampaignStep', $carray);
	}

	/**
	 * Get step details for a legacy followup sequence
	 *
	 * @method getCampaigneeStepDetails
	 * @param int $cid
	 * @param int $stepId
	 * @return array
	 */
	public function getCampaigneeStepDetails( $cid, $stepId) {
		$carray = array(
			php_xmlrpc_encode((int) $cid),
			php_xmlrpc_encode((int) $stepId));
		return
			$this->methodCaller('ContactService.getCampaigneeStepDetails', $carray);
	}

	/**
	 * Reschedule a legacy followup sequence
	 *
	 * @method rescheduleCampaignStep
	 * @param array $cidList
	 * @param int $campId
	 * @return int
	 */
	public function rescheduleCampaignStep( $cidList, $campId) {
		$carray = array(
			php_xmlrpc_encode($cidList),
			php_xmlrpc_encode((int) $campId));
		return
			$this->methodCaller('ContactService.rescheduleCampaignStep', $carray);
	}

	/**
	 * Remove a contact from a legacy followup sequence
	 *
	 * @method removeFromCampaign
	 * @param int $cid
	 * @param int $campId
	 * @return bool
	 */
	public function campRemove( $cid, $campId) {
		$carray = array(
			php_xmlrpc_encode((int) $cid),
			php_xmlrpc_encode((int) $campId));
		return $this->methodCaller('ContactService.removeFromCampaign', $carray);
	}

	/**
	 * Pause a legacy followup sequence for a contact
	 *
	 * @method pauseCampaign
	 * @param int $cid
	 * @param int $campId
	 * @return bool
	 */
	public function campPause( $cid, $campId) {
		$carray = array(
			php_xmlrpc_encode((int) $cid),
			php_xmlrpc_encode((int) $campId));
		return $this->methodCaller('ContactService.pauseCampaign', $carray);
	}

	/**
	 * Run an actionset on a contact
	 *
	 * @method runActionSequence
	 * @param int $cid
	 * @param int $aid
	 * @return array
	 */
	public function runAS( $cid, $aid) {
		$carray = array(
			php_xmlrpc_encode((int) $cid),
			php_xmlrpc_encode((int) $aid));
		return $this->methodCaller('ContactService.runActionSequence', $carray);
	}

	/**
	 * Add a note, task, or appointment to a contact from a template
	 *
	 * @method applyActivityHistoryTemplate
	 * @param int $contactId
	 * @param int $historyId
	 * @param int $userId
	 * @return int|mixed|string
	 */
	public function applyActivityHistoryTemplate( $contactId, $historyId, $userId) {
		$carray = array(
			php_xmlrpc_encode((int) $contactId),
			php_xmlrpc_encode((int) $historyId),
			php_xmlrpc_encode((int) $userId));
		return $this->methodCaller('ContactService.applyActivityHistoryTemplate', $carray);
	}

	/**
	 * Get templates for use with applyActivityHistoryTemplate
	 *
	 * @method getActivityHistoryTemplateMap
	 * @return array
	 */
	public function getActivityHistoryTemplateMap() {
		$carray = array();
		return $this->methodCaller('ContactService.getActivityHistoryTemplateMap', $carray);
	}

	/**
	 * Add a contact with duplicate checking
	 *
	 * @method addWithDupCheck
	 * @param array $cMap
	 * @param string $checkType - 'Email', 'EmailAndName', or 'EmailAndNameAnd Company'
	 * @return int
	 */
	public function addWithDupCheck( $cMap, $checkType) {
		$carray = array(
			php_xmlrpc_encode($cMap, array('auto_dates')),
			php_xmlrpc_encode($checkType));
		return $this->methodCaller('ContactService.addWithDupCheck', $carray);
	}

	/**
	 * Credit Card Submission Service
	 */

	/**
	 * Gets a token, which is needed to POST a credit card to the application
	 *
	 * @method requestSubmissionToken
	 * @param int $contactId
	 * @param string $successUrl
	 * @param string $failureUrl
	 * @return string
	 */
	public function requestCcSubmissionToken( $contactId, $successUrl, $failureUrl) {
		$carray = array(
			php_xmlrpc_encode((int) $contactId),
			php_xmlrpc_encode((string) $successUrl),
			php_xmlrpc_encode((string) $failureUrl));
		return $this->methodCaller('CreditCardSubmissionService.requestSubmissionToken', $carray);
	}

	/**
	 * Retrieves credit card details (CC number not included) that have been posted to the app
	 *
	 * @method requestCreditCardId
	 * @param $token
	 * @return array
	 */
	public function requestCreditCardId( $token) {
		$carray = array(
			php_xmlrpc_encode($token));
		return $this->methodCaller('CreditCardSubmissionService.requestCreditCardId', $carray);
	}

	/**
	 * Data Service
	 */

	/**
	 * Gets an app setting
	 *
	 * @method getAppSetting
	 * @param string $module
	 * @param string $setting
	 * @return int|mixed|string
	 */
	public function dsGetSetting( $module, $setting) {
		$carray = array(
			php_xmlrpc_encode($module),
			php_xmlrpc_encode($setting));
		return $this->methodCaller('DataService.getAppSetting', $carray);
	}

	/**
	 * Add a record to a table
	 *
	 * @method add
	 * @param string $tName
	 * @param array $iMap
	 * @return int
	 */
	public function dsAdd( $tName, $iMap) {
		$carray = array(
			php_xmlrpc_encode($tName),
			php_xmlrpc_encode($iMap, array('auto_dates')));

		return $this->methodCaller('DataService.add', $carray);
	}

	/**
	 * Add a record to a table that includes an image
	 *
	 * @method dsAddWithImage
	 * @param string $tName
	 * @param array $iMap
	 * @return int
	 */
	public function dsAddWithImage( $tName, $iMap) {
		$carray = array(
			php_xmlrpc_encode($tName),
			php_xmlrpc_encode($iMap, array('auto_dates', 'auto_base64')));

		return $this->methodCaller('DataService.add', $carray);
	}

	/**
	 * Delete a record from Infusionsoft
	 *
	 * @method delete
	 * @param string $tName
	 * @param int $id
	 * @return bool
	 */
	public function dsDelete( $tName, $id) {
		$carray = array(
			php_xmlrpc_encode($tName),
			php_xmlrpc_encode((int) $id));

		return $this->methodCaller('DataService.delete', $carray);
	}

	/**
	 * Update a record in any table
	 *
	 * @method update
	 * @param string $tName
	 * @param int $id
	 * @param array $iMap
	 * @return int
	 */
	public function dsUpdate( $tName, $id, $iMap) {
		$carray = array(
			php_xmlrpc_encode($tName),
			php_xmlrpc_encode((int) $id),
			php_xmlrpc_encode($iMap, array('auto_dates')));

		return $this->methodCaller('DataService.update', $carray);
	}

	/**
	 * Update a record in any table with an image
	 *
	 * @method dsUpdateWithImage
	 * @param string $tName
	 * @param int $id
	 * @param array $iMap
	 * @return int
	 */
	public function dsUpdateWithImage( $tName, $id, $iMap) {
		$carray = array(
			php_xmlrpc_encode($tName),
			php_xmlrpc_encode((int) $id),
			php_xmlrpc_encode($iMap, array('auto_dates', 'auto_base64')));

		return $this->methodCaller('DataService.update', $carray);
	}

	/**
	 * Load a record from any table
	 *
	 * @method load
	 * @param string $tName
	 * @param int $id
	 * @param array $rFields
	 * @return array
	 */
	public function dsLoad( $tName, $id, $rFields) {
		$carray = array(
			php_xmlrpc_encode($tName),
			php_xmlrpc_encode((int) $id),
			php_xmlrpc_encode($rFields));

		return $this->methodCaller('DataService.load', $carray);
	}

	/**
	 * Finds records by searching a specific field
	 *
	 * @method findByField
	 * @param string $tName
	 * @param int $limit
	 * @param int $page
	 * @param string $field
	 * @param string $value
	 * @param array $rFields
	 * @return array
	 */
	public function dsFind( $tName, $limit, $page, $field, $value, $rFields) {
		$carray = array(
			php_xmlrpc_encode($tName),
			php_xmlrpc_encode((int) $limit),
			php_xmlrpc_encode((int) $page),
			php_xmlrpc_encode($field),
			php_xmlrpc_encode($value),
			php_xmlrpc_encode($rFields));

		return $this->methodCaller('DataService.findByField', $carray);
	}

	/**
	 * Finds records based on query
	 *
	 * @method query
	 * @param string $tName
	 * @param int $limit
	 * @param int $page
	 * @param array $query
	 * @param array $rFields
	 * @return array
	 */
	public function dsQuery( $tName, $limit, $page, $query, $rFields, $orderby = null, $ascending = null ) {
		$carray = array(
			php_xmlrpc_encode($tName),
			php_xmlrpc_encode((int) $limit),
			php_xmlrpc_encode((int) $page),
			php_xmlrpc_encode($query, array('auto_dates')),
			php_xmlrpc_encode($rFields));

		if ( ! is_null( $orderby ) ) {
			$carray[] = php_xmlrpc_encode( $orderby );

			if ( ! is_null( $ascending ) ) {
				$carray[] = php_xmlrpc_encode( (bool) $ascending );
			}
		}

		return $this->methodCaller('DataService.query', $carray);
	}

	/**
	 * Finds records based on query with option to sort
	 *
	 * @method queryWithOrderBy
	 * @param string $tName
	 * @param int $limit
	 * @param int $page
	 * @param array $query
	 * @param array $rFields
	 * @param string $orderByField
	 * @param bool $ascending
	 * @return array
	 */
	public function dsQueryOrderBy( $tName, $limit, $page, $query, $rFields, $orderByField, $ascending = true) {
		$carray = array(
			php_xmlrpc_encode($tName),
			php_xmlrpc_encode((int) $limit),
			php_xmlrpc_encode((int) $page),
			php_xmlrpc_encode($query, array('auto_dates')),
			php_xmlrpc_encode($rFields),
			php_xmlrpc_encode($orderByField),
			php_xmlrpc_encode((bool) $ascending));

		return $this->methodCaller('DataService.query', $carray);
	}

	/**
	 * Gets record count based on query
	 *
	 * @method DataService.Count
	 * @param string $tName
	 * @param array $query
	 * @return int
	 */

	public function dsCount( $tName, $query) {
		$carray = array(
			php_xmlrpc_encode($tName),
			php_xmlrpc_encode($query, array('auto_dates'))
		);
		return $this->methodCaller('DataService.count', $carray);
	}

	/**
	 * Adds a custom field
	 *
	 * @method addCustomField
	 * @param string $context
	 * @param string $displayName
	 * @param int $dataType
	 * @param int $headerID
	 * @return int
	 */
	public function addCustomField( $context, $displayName, $dataType, $headerID) {
		$carray = array(

			php_xmlrpc_encode($context),
			php_xmlrpc_encode($displayName),
			php_xmlrpc_encode($dataType),
			php_xmlrpc_encode((int) $headerID));

		return $this->methodCaller('DataService.addCustomField', $carray);
	}

	/**
	 * Authenticates a user account in Infusionsoft
	 *
	 * @method authenticateUser
	 * @param string $userName
	 * @param string $password
	 * @return int
	 */
	public function authenticateUser( $userName, $password) {
		$password = strtolower(md5($password));
		$carray   = array(
			php_xmlrpc_encode($userName),
			php_xmlrpc_encode($password));

		return $this->methodCaller('DataService.authenticateUser', $carray);
	}

	/**
	 * Update a custom field
	 *
	 * @method - updateCustomField
	 * @param int $fieldId
	 * @param array $fieldValues
	 * @return int
	 */
	public function updateCustomField( $fieldId, $fieldValues) {
		$carray = array(
			php_xmlrpc_encode((int) $fieldId),
			php_xmlrpc_encode($fieldValues));
		return $this->methodCaller('DataService.updateCustomField', $carray);
	}

	/**
	 * Discount Service
	 */

	/**
	 * Creates a subscription free trial for the shopping cart
	 *
	 * @method addFreeTrial
	 * @param string $name
	 * @param string $description
	 * @param int $freeTrialDays
	 * @param int $hidePrice
	 * @param int $subscriptionPlanId
	 * @return int
	 */
	public function addFreeTrial( $name, $description, $freeTrialDays, $hidePrice, $subscriptionPlanId) {
		$carray = array(
			php_xmlrpc_encode((string) $name),
			php_xmlrpc_encode((string) $description),
			php_xmlrpc_encode((int) $freeTrialDays),
			php_xmlrpc_encode((int) $hidePrice),
			php_xmlrpc_encode((int) $subscriptionPlanId));
		return $this->methodCaller('DiscountService.addFreeTrial', $carray);
	}

	/**
	 * Retrieves the details on the given free trial
	 *
	 * @method getFreeTrial
	 * @param int $trialId
	 * @return array
	 */
	public function getFreeTrial( $trialId) {
		$carray = array(
			php_xmlrpc_encode((int) $trialId));
		return $this->methodCaller('DiscountService.getFreeTrial', $carray);
	}

	/**
	 * Creates an order total discount for the shopping cart
	 *
	 * @method addOrderTotalDiscount
	 * @param string $name
	 * @param string $description
	 * @param int $applyDiscountToCommission
	 * @param int $percentOrAmt
	 * @paramOption 0 Amount
	 * @paramOption 1 Percent
	 * @param double $amt
	 * @param string $payType
	 * @paramOption Gross
	 * @paramOption Net
	 * @return int
	 */
	public function addOrderTotalDiscount( $name, $description, $applyDiscountToCommission, $percentOrAmt, $amt, $payType) {
		$carray = array(
			php_xmlrpc_encode((string) $name),
			php_xmlrpc_encode((string) $description),
			php_xmlrpc_encode((int) $applyDiscountToCommission),
			php_xmlrpc_encode((int) $percentOrAmt),
			php_xmlrpc_encode($amt),
			php_xmlrpc_encode($payType));
		return $this->methodCaller('DiscountService.addOrderTotalDiscount', $carray);
	}

	/**
	 * Retrieves the details on the given order total discount
	 *
	 * @method getOrderTotalDiscount
	 * @param int $id
	 * @return array
	 */
	public function getOrderTotalDiscount( $id) {
		$carray = array(
			php_xmlrpc_encode((int) $id));
		return $this->methodCaller('DiscountService.getOrderTotalDiscount', $carray);
	}

	/**
	 * Creates a product category discount for the shopping cart
	 *
	 * @method addCategoryDiscount
	 * @param string $name
	 * @param string $description
	 * @param int $applyDiscountToCommission
	 * @param double $amt
	 * @return int
	 */
	public function addCategoryDiscount( $name, $description, $applyDiscountToCommission, $amt) {
		$carray = array(
			php_xmlrpc_encode((string) $name),
			php_xmlrpc_encode((string) $description),
			php_xmlrpc_encode((int) $applyDiscountToCommission),
			php_xmlrpc_encode($amt));
		return $this->methodCaller('DiscountService.addCategoryDiscount', $carray);
	}

	/**
	 * Retrieves the details on the Category discount
	 *
	 * @method getCategoryDiscount
	 * @param int $id
	 * @return array
	 */
	public function getCategoryDiscount( $id) {
		$carray = array(
			php_xmlrpc_encode((int) $id));
		return $this->methodCaller('DiscountService.getCategoryDiscount', $carray);
	}

	/**
	 * Assigns a product category to a particular category discount
	 *
	 * @method addCategoryAssignmentToCategoryDiscount
	 * @param int $categoryDiscountId
	 * @param int $productCategoryId
	 * @return int
	 */
	public function addCategoryAssignmentToCategoryDiscount( $categoryDiscountId, $productCategoryId) {
		$carray = array(
			php_xmlrpc_encode((int) $categoryDiscountId),
			php_xmlrpc_encode((int) $productCategoryId));
		return $this->methodCaller('DiscountService.addCategoryAssignmentToCategoryDiscount', $carray);
	}

	/**
	 * Retrieves the product categories that are currently set for the given category discount
	 *
	 * @method getCategoryAssignmentsForCategoryDiscount
	 * @param int $id
	 * @return array
	 */
	public function getCategoryAssignmentsForCategoryDiscount( $id) {
		$carray = array(
			php_xmlrpc_encode((int) $id));
		return $this->methodCaller('DiscountService.getCategoryAssignmentsForCategoryDiscount', $carray);
	}

	/**
	 * Creates a product total discount for the shopping cart
	 *
	 * @method addProductTotalDiscount
	 * @param string $name
	 * @param string $description
	 * @param int $applyDiscountToCommission
	 * @param int $productId
	 * @param int $percentOrAmt
	 * @paramOption 0 Amount
	 * @paramOption 1 Percent
	 * @param double $amt
	 * @return int
	 */
	public function addProductTotalDiscount( $name, $description, $applyDiscountToCommission, $productId, $percentOrAmt, $amt) {
		$carray = array(
			php_xmlrpc_encode((string) $name),
			php_xmlrpc_encode((string) $description),
			php_xmlrpc_encode((int) $applyDiscountToCommission),
			php_xmlrpc_encode((int) $productId),
			php_xmlrpc_encode((int) $percentOrAmt),
			php_xmlrpc_encode($amt));
		return $this->methodCaller('DiscountService.addProductTotalDiscount', $carray);
	}

	/**
	 * Retrieves the details on the given product total discount
	 *
	 * @method getProductTotalDiscount
	 * @param int $id
	 * @return array
	 */
	public function getProductTotalDiscount( $id) {
		$carray = array(
			php_xmlrpc_encode((int) $id));
		return $this->methodCaller('DiscountService.getProductTotalDiscount', $carray);
	}

	/**
	 * Creates a shipping total discount for the shopping cart
	 *
	 * @method addShippingTotalDiscount
	 * @param string $name
	 * @param string $description
	 * @param int $applyDiscountToCommission
	 * @param int $percentOrAmt
	 * @paramOption 0 Amount
	 * @paramOption 1 Percent
	 * @param double $amt
	 * @return int
	 */
	public function addShippingTotalDiscount( $name, $description, $applyDiscountToCommission, $percentOrAmt, $amt) {
		$carray = array(
			php_xmlrpc_encode((string) $name),
			php_xmlrpc_encode((string) $description),
			php_xmlrpc_encode((int) $applyDiscountToCommission),
			php_xmlrpc_encode((int) $percentOrAmt),
			php_xmlrpc_encode($amt));
		return $this->methodCaller('DiscountService.addShippingTotalDiscount', $carray);
	}

	/**
	 * Retrieves the details on the given shipping total discount
	 *
	 * @method getShippingTotalDiscount
	 * @param int $id
	 * @return array
	 */
	public function getShippingTotalDiscount( $id) {
		$carray = array(
			php_xmlrpc_encode((int) $id));
		return $this->methodCaller('DiscountService.getShippingTotalDiscount', $carray);
	}

	/**
	 * API Email Service
	 */

	/**
	 * Attachs an email to a contacts email history
	 *
	 * @method attachEmail
	 * @param int $cId
	 * @param string $fromName
	 * @param string $fromAddress
	 * @param string $toAddress
	 * @param string $ccAddresses
	 * @param string $bccAddresses
	 * @param string $contentType
	 * @param string $subject
	 * @param string $htmlBody
	 * @param string $txtBody
	 * @param string $header
	 * @param date $strRecvdDate
	 * @param date $strSentDate
	 * @param int $emailSentType
	 * @return bool
	 */
	public function attachEmail($cId, $fromName, $fromAddress, $toAddress, $ccAddresses,
								$bccAddresses, $contentType, $subject, $htmlBody, $txtBody,
								$header, $strRecvdDate, $strSentDate, $emailSentType = 1) {
		$carray = array(
			php_xmlrpc_encode((int) $cId),
			php_xmlrpc_encode($fromName),
			php_xmlrpc_encode($fromAddress),
			php_xmlrpc_encode($toAddress),
			php_xmlrpc_encode($ccAddresses),
			php_xmlrpc_encode($bccAddresses),
			php_xmlrpc_encode($contentType),
			php_xmlrpc_encode($subject),
			php_xmlrpc_encode($htmlBody),
			php_xmlrpc_encode($txtBody),
			php_xmlrpc_encode($header),
			php_xmlrpc_encode($strRecvdDate),
			php_xmlrpc_encode($strSentDate),
			php_xmlrpc_encode($emailSentType));
		return $this->methodCaller('APIEmailService.attachEmail', $carray);
	}

	/**
	 * Gets a list of all available merge fields
	 *
	 * @method getAvailableMergeFields
	 * @param string $mergeContext
	 * @return array
	 */
	public function getAvailableMergeFields( $mergeContext) {
		$carray = array(
			php_xmlrpc_encode($mergeContext));
		return $this->methodCaller('APIEmailService.getAvailableMergeFields', $carray);
	}

	/**
	 * Send an email to a list of contacts
	 *
	 * @method sendEmail
	 * @param array $conList
	 * @param string $fromAddress
	 * @param string $toAddress
	 * @param string $ccAddresses
	 * @param string $bccAddresses
	 * @param string $contentType
	 * @param string $subject
	 * @param string $htmlBody
	 * @param string $txtBody
	 * @return bool
	 */
	public function sendEmail( $conList, $fromAddress, $toAddress, $ccAddresses, $bccAddresses, $contentType, $subject, $htmlBody, $txtBody) {
		$carray = array(
			php_xmlrpc_encode($conList),
			php_xmlrpc_encode($fromAddress),
			php_xmlrpc_encode($toAddress),
			php_xmlrpc_encode($ccAddresses),
			php_xmlrpc_encode($bccAddresses),
			php_xmlrpc_encode($contentType),
			php_xmlrpc_encode($subject),
			php_xmlrpc_encode($htmlBody),
			php_xmlrpc_encode($txtBody));

		return $this->methodCaller('APIEmailService.sendEmail', $carray);
	}

	/**
	 * Sends a template to a list of contacts
	 *
	 * @method sendTemplate
	 * @note uses APIEmailService.sendEmail with different parameters
	 * @param array $conList
	 * @param int $template
	 * @return bool
	 */
	public function sendTemplate( $conList, $template) {
		$carray = array(
			php_xmlrpc_encode($conList),
			php_xmlrpc_encode($template));
		return $this->methodCaller('APIEmailService.sendEmail', $carray);
	}

	/**
	 * Note: THIS IS DEPRECATED - USE addEmailTemplate instead!
	 * Creates a legacy Email Template
	 *
	 * @method createEmailTemplate
	 * @param string $title
	 * @param int $userID
	 * @param string $fromAddress
	 * @param string $toAddress
	 * @param string $ccAddresses
	 * @param string $bccAddresses
	 * @param string $contentType
	 * @param string $subject
	 * @param string $htmlBody
	 * @param string $txtBody
	 * @return int
	 */
	public function createEmailTemplate($title, $userID, $fromAddress, $toAddress, $ccAddresses, $bccAddresses, $contentType, $subject, $htmlBody,
										$txtBody) {
		$carray = array(
			php_xmlrpc_encode($title),
			php_xmlrpc_encode(''), // category
			php_xmlrpc_encode($fromAddress),
			php_xmlrpc_encode($toAddress),
			php_xmlrpc_encode($ccAddresses),
			php_xmlrpc_encode($bccAddresses),
			php_xmlrpc_encode($subject),
			php_xmlrpc_encode($txtBody),
			php_xmlrpc_encode($htmlBody),
			php_xmlrpc_encode($contentType),
			php_xmlrpc_encode('Contact'), // mergeContext
		);
		return $this->methodCaller('APIEmailService.addEmailTemplate', $carray);
	}

	/**
	 * Creates an Email Template
	 *
	 * @method addEmailTemplate
	 * @param string $title
	 * @param string $category
	 * @param string $fromAddress
	 * @param string $toAddress
	 * @param string $ccAddresses
	 * @param string $bccAddresses
	 * @param string $subject
	 * @param string $txtBody
	 * @param string $htmlBody
	 * @param string $contentType
	 * @param string $mergeContext
	 * @return int
	 */
	public function addEmailTemplate( $title, $category, $fromAddress, $toAddress, $ccAddresses, $bccAddresses, $subject, $txtBody, $htmlBody, $contentType, $mergeContext) {
		$carray = array(
			php_xmlrpc_encode($title),
			php_xmlrpc_encode($category),
			php_xmlrpc_encode($fromAddress),
			php_xmlrpc_encode($toAddress),
			php_xmlrpc_encode($ccAddresses),
			php_xmlrpc_encode($bccAddresses),
			php_xmlrpc_encode($subject),
			php_xmlrpc_encode($txtBody),
			php_xmlrpc_encode($htmlBody),
			php_xmlrpc_encode($contentType),
			php_xmlrpc_encode($mergeContext));
		return $this->methodCaller('APIEmailService.addEmailTemplate', $carray);
	}

	/**
	 * Get the HTML of an email template
	 *
	 * @method getEmailTemplate
	 * @param int $templateId
	 * @return array
	 */
	public function getEmailTemplate( $templateId) {
		$carray = array(
			php_xmlrpc_encode((int) $templateId));
		return $this->methodCaller('APIEmailService.getEmailTemplate', $carray);
	}

	/**
	 * Update an Email template
	 *
	 * @method updateEmailTemplate
	 * @param int $templateID
	 * @param string $title
	 * @param string $categories
	 * @param string $fromAddress
	 * @param string $toAddress
	 * @param string $ccAddress
	 * @param string $bccAddress
	 * @param string $subject
	 * @param string $textBody
	 * @param string $htmlBody
	 * @param string $contentType
	 * @param string $mergeContext
	 * @return bool
	 */
	public function updateEmailTemplate( $templateID, $title, $categories, $fromAddress, $toAddress, $ccAddress, $bccAddress, $subject, $textBody, $htmlBody, $contentType, $mergeContext) {
		$carray = array(
			php_xmlrpc_encode((int) $templateID),
			php_xmlrpc_encode($title),
			php_xmlrpc_encode($categories),
			php_xmlrpc_encode($fromAddress),
			php_xmlrpc_encode($toAddress),
			php_xmlrpc_encode($ccAddress),
			php_xmlrpc_encode($bccAddress),
			php_xmlrpc_encode($subject),
			php_xmlrpc_encode($textBody),
			php_xmlrpc_encode($htmlBody),
			php_xmlrpc_encode($contentType),
			php_xmlrpc_encode($mergeContext));
		return $this->methodCaller('APIEmailService.updateEmailTemplate', $carray);
	}

	/**
	 * Get the Opt status of an email
	 *
	 * @method getOptStatus
	 * @param string $email
	 * @return int
	 */
	public function optStatus( $email) {
		$carray = array(
			php_xmlrpc_encode($email));
		return $this->methodCaller('APIEmailService.getOptStatus', $carray);
	}

	/**
	 * Opts an email in to allow emails to be sent to them
	 *
	 * @method optIn
	 * @note  Opt-In will only work on "non-marketable contacts not opted out people
	 * @param string $email
	 * @param string $reason
	 * @return bool
	 */
	public function optIn( $email, $reason = 'Contact Was Opted In through the API') {
		$carray = array(
			php_xmlrpc_encode($email),
			php_xmlrpc_encode($reason));
		return $this->methodCaller('APIEmailService.optIn', $carray);
	}

	/**
	 * Opts an email out. Emails will not be sent to them anymore
	 *
	 * @method optOut
	 * @param string $email
	 * @param string $reason
	 * @return bool
	 */
	public function optOut( $email, $reason = 'Contact Was Opted Out through the API') {
		$carray = array(
			php_xmlrpc_encode($email),
			php_xmlrpc_encode($reason));
		return $this->methodCaller('APIEmailService.optOut', $carray);
	}

	/**
	 * File Service
	 */

	/**
	 * Gets File
	 *
	 * @method getFile
	 * @param int $fileID
	 * @return base64 encoded file data
	 */
	public function getFile( $fileID) {

		$carray = array(
			php_xmlrpc_encode((int) $fileID));
		$result = $this->methodCaller('FileService.getFile', $carray);
		return $result;
	}

	/**
	 * Upload a file to Infusionsoft
	 *
	 * @method uploadFile
	 * @param string $fileName
	 * @param string $base64Enc
	 * @param int $cid
	 * @return int|mixed|string
	 */
	public function uploadFile( $fileName, $base64Enc, $cid = 0) {
		$result = 0;
		if (0 == $cid) {
			$carray = array(
				php_xmlrpc_encode($fileName),
				php_xmlrpc_encode($base64Enc));
			$result = $this->methodCaller('FileService.uploadFile', $carray);
		} else {
			$carray = array(
				php_xmlrpc_encode((int) $cid),
				php_xmlrpc_encode($fileName),
				php_xmlrpc_encode($base64Enc));
			$result = $this->methodCaller('FileService.uploadFile', $carray);
		}
		return $result;
	}

	/**
	 * Replaces existing file
	 *
	 * @method replaceFile
	 * @param int $fileID
	 * @param string $base64Enc
	 * @return bool
	 */
	public function replaceFile( $fileID, $base64Enc) {
		$carray = array(
			php_xmlrpc_encode((int) $fileID),
			php_xmlrpc_encode($base64Enc));
		$result = $this->methodCaller('FileService.replaceFile', $carray);
		return $result;
	}

	/**
	 * Rename existing file
	 *
	 * @method renameFile
	 * @param int $fileID
	 * @param string $fileName
	 * @return bool
	 */
	public function renameFile( $fileID, $fileName) {
		$carray = array(
			php_xmlrpc_encode((int) $fileID),
			php_xmlrpc_encode($fileName));
		$result = $this->methodCaller('FileService.renameFile', $carray);
		return $result;
	}

	/**
	 * Gets download url for public files
	 *
	 * @method getDownloadUrl
	 * @param int $fileID
	 * @return string
	 */
	public function getDownloadUrl( $fileID) {
		$carray = array(
			php_xmlrpc_encode((int) $fileID));
		$result = $this->methodCaller('FileService.getDownloadUrl', $carray);
		return $result;
	}

	/**
	 * Funnel Service
	 */

	/**
	 * Achieves an api goal inside of the Campaign Builder to start a campaign
	 *
	 * @method achieveGoal
	 * @param string $integration
	 * @param string $callName
	 * @param int $contactId
	 * @return array
	 */
	public function achieveGoal( $integration, $callName, $contactId) {
		$carray = array(
			php_xmlrpc_encode((string) $integration),
			php_xmlrpc_encode((string) $callName),
			php_xmlrpc_encode((int) $contactId));
		return $this->methodCaller('FunnelService.achieveGoal', $carray);
	}

	/**
	 * Invoice Service
	 */

	/**
	 * Deletes an invoice
	 *
	 * @method deleteInvoice
	 * @param int $Id
	 * @return bool
	 */
	public function deleteInvoice( $Id) {
		$carray = array(
			php_xmlrpc_encode((int) $Id));
		return $this->methodCaller('InvoiceService.deleteInvoice', $carray);
	}

	/**
	 * Delete a Subscription created through the API
	 *
	 * @method deleteSubscriptioin
	 * @param $Id
	 * @return bool
	 */
	public function deleteSubscription( $Id) {
		$carray = array(
			php_xmlrpc_encode((int) $Id));
		return $this->methodCaller('InvoiceService.deleteSubscription', $carray);
	}

	/**
	 * Get a list of payments on an invoice
	 *
	 * @method getPayments
	 * @param $Id
	 * @return array
	 */
	public function getPayments( $Id) {
		$carray = array(
			php_xmlrpc_encode((int) $Id));
		return $this->methodCaller('InvoiceService.getPayments', $carray);
	}

	/**
	 * Sets the sync status column on the Invoice table
	 *
	 * @method setInvoiceSyncStatus
	 * @param $Id
	 * @param $syncStatus
	 * @return bool
	 */
	public function setInvoiceSyncStatus( $Id, $syncStatus) {
		$carray = array(
			php_xmlrpc_encode((int) $Id),
			php_xmlrpc_encode($syncStatus));
		return $this->methodCaller('InvoiceService.setInvoiceSyncStatus', $carray);
	}

	/**
	 * Sets the sync status column on the Payment table
	 *
	 * @method setPaymentSyncStatus
	 * @param $Id
	 * @param $Status
	 * @return bool
	 */
	public function setPaymentSyncStatus( $Id, $Status) {
		$carray = array(
			php_xmlrpc_encode((int) $Id),
			php_xmlrpc_encode($Status));
		return $this->methodCaller('InvoiceService.setPaymentSyncStatus', $carray);
	}

	/**
	 * Tells if the Ecommerce plugin is enabled
	 *
	 * @method getPluginStatus
	 * @param string $className
	 * @return bool
	 */
	public function getPluginStatus( $className) {
		$carray = array(
			php_xmlrpc_encode($className));
		return $this->methodCaller('InvoiceService.getPluginStatus', $carray);
	}

	/**
	 * Get a list of all Payment Options
	 *
	 * @method getAllPaymentOptions
	 * @return array
	 */
	public function getAllPaymentOptions() {
		$carray = array();
		return $this->methodCaller('InvoiceService.getAllPaymentOptions', $carray);
	}

	/**
	 * Add a manual payment to an invoice.
	 *
	 * @method addManualPayment
	 * @note Will not complete Purchase Goals or Successful Purchase Actions
	 * @param int $invId
	 * @param double $amt
	 * @param datetime $payDate
	 * @param datetime $payType
	 * @param string $payDesc
	 * @param bool $bypassComm
	 * @return int
	 */
	public function manualPmt( $invId, $amt, $payDate, $payType, $payDesc, $bypassComm) {
		$carray = array(
			php_xmlrpc_encode((int) $invId),
			php_xmlrpc_encode($amt),
			php_xmlrpc_encode($payDate, array('auto_dates')),
			php_xmlrpc_encode($payType),
			php_xmlrpc_encode($payDesc),
			php_xmlrpc_encode($bypassComm));
		return $this->methodCaller('InvoiceService.addManualPayment', $carray);
	}

	/**
	 * Override Order Commissions
	 *
	 * @method addOrderCommissionOverride
	 * @param int $invId
	 * @param int $affId
	 * @param int $prodId
	 * @param int $percentage
	 * @param double $amt
	 * @param int $payType
	 * @param string $desc
	 * @param date $date
	 * @return bool
	 */
	public function commOverride( $invId, $affId, $prodId, $percentage, $amt, $payType, $desc, $date) {
		$carray = array(
			php_xmlrpc_encode((int) $invId),
			php_xmlrpc_encode((int) $affId),
			php_xmlrpc_encode((int) $prodId),
			php_xmlrpc_encode($percentage),
			php_xmlrpc_encode($amt),
			php_xmlrpc_encode($payType),
			php_xmlrpc_encode($desc),
			php_xmlrpc_encode($date, array('auto_dates')));

		return $this->methodCaller('InvoiceService.addOrderCommissionOverride', $carray);
	}

	/**
	 * Add a line item to an order
	 *
	 * @method addOrderItem
	 * @param int $ordId
	 * @param int $prodId
	 * @param int $type
	 * @paramOption 1 Shipping
	 * @paramOption 2 Tax
	 * @paramOption 3 Service & Misc
	 * @paramOption 4 Product
	 * @paramOption 5 Upsell Product
	 * @paramOption 6 Fiance Charge
	 * @paramOption 7 Special
	 * @paramOption 8 Program
	 * @paramOption 9 Subscription Plan
	 * @paramOption 10 Special:Free Trial Days
	 * @paramOption 12 Special: Order Total
	 * @paramOption 13 Special: Category
	 * @paramOption 14 Special: Shipping
	 * @param double $price
	 * @param itn $qty
	 * @param string $desc
	 * @param string $notes
	 * @return bool
	 */
	public function addOrderItem( $ordId, $prodId, $type, $price, $qty, $desc, $notes) {
		$carray = array(
			php_xmlrpc_encode((int) $ordId),
			php_xmlrpc_encode((int) $prodId),
			php_xmlrpc_encode((int) $type),
			php_xmlrpc_encode($price),
			php_xmlrpc_encode($qty),
			php_xmlrpc_encode($desc),
			php_xmlrpc_encode($notes));

		return $this->methodCaller('InvoiceService.addOrderItem', $carray);
	}

	/**
	 * Add a payment plan to an order
	 *
	 * @method addPaymentPlan
	 * @param int $ordId
	 * @param bool $aCharge
	 * @param int $ccId
	 * @param int $merchId
	 * @param int $retry
	 * @param int $retryAmt
	 * @param double $initialPmt
	 * @param datetime $initialPmtDate
	 * @param datetime $planStartDate
	 * @param int $numPmts
	 * @param int $pmtDays
	 * @return bool
	 */
	public function payPlan( $ordId, $aCharge, $ccId, $merchId, $retry, $retryAmt, $initialPmt, $initialPmtDate, $planStartDate, $numPmts, $pmtDays) {
		$carray = array(
			php_xmlrpc_encode((int) $ordId),
			php_xmlrpc_encode($aCharge),
			php_xmlrpc_encode((int) $ccId),
			php_xmlrpc_encode((int) $merchId),
			php_xmlrpc_encode((int) $retry),
			php_xmlrpc_encode((int) $retryAmt),
			php_xmlrpc_encode($initialPmt),
			php_xmlrpc_encode($initialPmtDate, array('auto_dates')),
			php_xmlrpc_encode($planStartDate, array('auto_dates')),
			php_xmlrpc_encode((int) $numPmts),
			php_xmlrpc_encode((int) $pmtDays));
		return $this->methodCaller('InvoiceService.addPaymentPlan', $carray);
	}

	/**
	 * Creates a subscription for a contact
	 *
	 * @method addRecurringOrder
	 * @param int $cid
	 * @param bool $allowDup
	 * @param int $progId
	 * @param int $merchId
	 * @param int $ccId
	 * @param int $affId
	 * @param  int $daysToCharge
	 * @return int
	 */
	public function addRecurring( $cid, $allowDup, $progId, $merchId, $ccId, $affId, $daysToCharge) {
		$carray = array(
			php_xmlrpc_encode((int) $cid),
			php_xmlrpc_encode($allowDup),
			php_xmlrpc_encode((int) $progId),
			php_xmlrpc_encode((int) $merchId),
			php_xmlrpc_encode((int) $ccId),
			php_xmlrpc_encode((int) $affId),
			php_xmlrpc_encode($daysToCharge));
		return $this->methodCaller('InvoiceService.addRecurringOrder', $carray);
	}

	/**
	 * Creates a subscription for a contact
	 *
	 * @method addRecurringOrderAdv
	 * @note Allows Quantity, Price and Tax
	 * @param int $cid
	 * @param bool $allowDup
	 * @param int $progId
	 * @param int $qty
	 * @param double $price
	 * @param bool $allowTax
	 * @param int $merchId
	 * @param int $ccId
	 * @param int $affId
	 * @param int $daysToCharge
	 * @return int
	 */
	public function addRecurringAdv( $cid, $allowDup, $progId, $qty, $price, $allowTax, $merchId, $ccId, $affId, $daysToCharge) {
		$carray = array(
			php_xmlrpc_encode((int) $cid),
			php_xmlrpc_encode($allowDup),
			php_xmlrpc_encode((int) $progId),
			php_xmlrpc_encode($qty),
			php_xmlrpc_encode($price),
			php_xmlrpc_encode($allowTax),
			php_xmlrpc_encode($merchId),
			php_xmlrpc_encode((int) $ccId),
			php_xmlrpc_encode((int) $affId),
			php_xmlrpc_encode($daysToCharge));
		return $this->methodCaller('InvoiceService.addRecurringOrder', $carray);
	}

	/**
	 * Calculate amount owed on an invoice
	 *
	 * @method calculateAmountOwed
	 * @param int $invId
	 * @return double
	 */
	public function amtOwed( $invId) {
		$carray = array(
			php_xmlrpc_encode((int) $invId));
		return $this->methodCaller('InvoiceService.calculateAmountOwed', $carray);
	}

	/**
	 * Get an Invoice Id attached to a one-time order
	 *
	 * @method getInvoiceId
	 * @param int $orderId
	 * @return int
	 */
	public function getInvoiceId( $orderId) {
		$carray = array(
			php_xmlrpc_encode((int) $orderId));
		return $this->methodCaller('InvoiceService.getInvoiceId', $carray);
	}

	/**
	 * Get the Order Id associated with an Invoice
	 *
	 * @method getOrderId
	 * @param int $invoiceId
	 * @return int
	 */
	public function getOrderId( $invoiceId) {
		$carray = array(
			php_xmlrpc_encode((int) $invoiceId));
		return $this->methodCaller('InvoiceService.getOrderId', $carray);
	}

	/**
	 * Charges an invoice immediately
	 *
	 * @method chargeInvoice
	 * @param int $invId
	 * @param string $notes
	 * @param int $ccId
	 * @param int $merchId
	 * @param bool $bypassComm
	 * @return array
	 */
	public function chargeInvoice( $invId, $notes, $ccId, $merchId, $bypassComm) {
		$carray = array(
			php_xmlrpc_encode((int) $invId),
			php_xmlrpc_encode($notes),
			php_xmlrpc_encode((int) $ccId),
			php_xmlrpc_encode((int) $merchId),
			php_xmlrpc_encode($bypassComm));
		return $this->methodCaller('InvoiceService.chargeInvoice', $carray);
	}

	/**
	 * Creates a blank order for a contact
	 *
	 * @method createBlankOrder
	 * @param int $conId
	 * @param string $desc
	 * @param date $oDate
	 * @param int $leadAff
	 * @param int $saleAff
	 * @return int
	 */
	public function blankOrder( $conId, $desc, $oDate, $leadAff, $saleAff) {
		$carray = array(
			php_xmlrpc_encode((int) $conId),
			php_xmlrpc_encode($desc),
			php_xmlrpc_encode($oDate, array('auto_dates')),
			php_xmlrpc_encode((int) $leadAff),
			php_xmlrpc_encode((int) $saleAff));
		return $this->methodCaller('InvoiceService.createBlankOrder', $carray);
	}

	/**
	 * Creates an invoice for a subscription
	 *
	 * @method createInvoiceForRecurring
	 * @param int $rid
	 * @return int
	 */
	public function recurringInvoice( $rid) {
		$carray = array(
			php_xmlrpc_encode((int) $rid));
		return $this->methodCaller('InvoiceService.createInvoiceForRecurring', $carray);
	}

	/**
	 * Locates a creditcard Id from based on the last 4 digits
	 *
	 * @method locateExistingCard
	 * @param int $cid
	 * @param string $last4
	 * @return int
	 */
	public function locateCard( $cid, $last4) {
		$carray = array(
			php_xmlrpc_encode((int) $cid),
			php_xmlrpc_encode($last4));
		return $this->methodCaller('InvoiceService.locateExistingCard', $carray);
	}

	/**
	 * Validates a Credit Card
	 *
	 * @method validateCreditCard
	 * @note this will take a CC ID or a CC array
	 * @param mixed $creditCard
	 * @return int
	 */
	public function validateCard( $creditCard) {
		$creditCard = is_array($creditCard) ? $creditCard : (int) $creditCard;

		$carray = array(
			php_xmlrpc_encode($creditCard));
		return $this->methodCaller('InvoiceService.validateCreditCard', $carray);
	}

	/**
	 * Updates the Next Bill Date on a Subscription
	 *
	 * @method updateSubscriptionNextBillDate
	 * @param int $subscriptionId
	 * @param date $nextBillDate
	 * @return bool
	 */
	public function updateSubscriptionNextBillDate( $subscriptionId, $nextBillDate) {
		$carray = array(
			php_xmlrpc_encode((int) $subscriptionId),
			php_xmlrpc_encode($nextBillDate, array('auto_dates')));
		return $this->methodCaller('InvoiceService.updateJobRecurringNextBillDate', $carray);
	}

	/**
	 * Recalculates tax for a given invoice Id
	 *
	 * @method recalculateTax
	 * @param $invoiceId
	 * @return bool
	 */
	public function recalculateTax( $invoiceId) {
		$carray = array(
			php_xmlrpc_encode((int) $invoiceId));
		return $this->methodCaller('InvoiceService.recalculateTax', $carray);
	}

	/**
	 * Misc iSDK Functions
	 */

	/**
	 * Returns properly formatted dates.
	 *
	 * @method infuDate
	 * @param $dateStr
	 * @param $dateFrmt - Optional date format for UK formatted Applications
	 * @return bool|string
	 */
	public function infuDate( $dateStr, $dateFrmt = 'US') {
		$dArray = date_parse($dateStr);
		if ($dArray['error_count'] < 1) {
			$tStamp =
				mktime($dArray['hour'], $dArray['minute'], $dArray['second'], $dArray['month'],
					$dArray['day'], $dArray['year']);
			if ('UK' === $dateFrmt) {
				setlocale(LC_ALL, 'en_GB');
				return wlm_date('Y-d-m\TH:i:s', $tStamp);
			} else {
				return wlm_date('Ymd\TH:i:s', $tStamp);
			}
		} else {
			foreach ($dArray['errors'] as $err) {
				echo 'ERROR: ' . esc_html( $err ) . '<br />';
			}
			die('The above errors prevented the application from executing properly.');
		}
	}

	/**
	 * Function to Enable/Disable Logging
	 *
	 * @method enableLogging
	 * @param int $log
	 */
	public function enableLogging( $log) {
		$this->loggingEnabled = $log;
	}

	/**
	 * Creates CSV Resource
	 *
	 * @method getHandle
	 * @param string $logname
	 * @return resource
	 */
	protected static function getHandle( $logname) {
		if (!is_resource(self::$handle)) {
			self::$handle = fopen($logname, 'a+');
		}
		return self::$handle;
	}

	/**
	 * Function for Logging Calls
	 *
	 * @method log
	 * @param array $data
	 * @return mixed
	 */
	private function log( $data) {
		$logdata = $data;

		if ('' == $this->logname) {
			$logname = dirname(__FILE__) . '/apilog.csv';
		} else {
			$logname = $this->logname;
		}

		if (!file_exists($logname)) {
			$this->getHandle($logname);
			// Fputcsv(self::$handle, array('Date', 'Method', 'Call', 'Start Time', 'Stop Time', 'Execution Time', 'Result', 'Error', 'Error Code')); original.
			fputcsv(self::$handle, array('Date', 'Method', 'Table', 'Execution Time', 'Error', 'Error Code'));
		} else {
			$this->getHandle($logname);
		}

		$table_called = 'N/A';
		if (isset($logdata['Call'][0]->me['string'])) {
			if ('CreditCard' === $logdata['Call'][0]->me['string']) {
				unset($logdata['Call'][1]->me['struct']);
				$logdata['Call'][1]->me['struct'] = 'Data Removed For Security';
			}
			$table_called = $logdata['Call'][0]->me['string'];
		}

		$logdata['Call'][0]->me['string'] = 'APIKEY';
		$exec_time                        = $logdata['Stop'] - $logdata['Start'];
		$exec_time                        = $exec_time < 0 ? 0 : $exec_time;
		fputcsv(self::$handle, array(
			wlm_date('Y-m-d H:i:s', $logdata['Now']),
			$logdata['Method'],
			$table_called,
			"{$exec_time} sec",
			$logdata['Error'],
			$logdata['ErrorCode'],
		));
		fclose(self::$handle);

	}

	public function setLog( $logPath) {
		$this->logname = $logPath;
	}

	/**
	 * Order Service
	 */

	/**
	 * Builds, creates and charges an order.
	 *
	 * @method placeOrder
	 * @param int $contactId
	 * @param int $creditCardId
	 * @param int $payPlanId
	 * @param array $productIds
	 * @param array $subscriptionIds
	 * @param bool $processSpecials
	 * @param array $promoCodes
	 * @param int $leadAff
	 * @param int $saleAff
	 * @return array
	 */
	public function placeOrder( $contactId, $creditCardId, $payPlanId, $productIds, $subscriptionIds, $processSpecials, $promoCodes, $leadAff = 0, $saleAff = 0) {
		$carray = array(
			php_xmlrpc_encode((int) $contactId),
			php_xmlrpc_encode((int) $creditCardId),
			php_xmlrpc_encode((int) $payPlanId),
			php_xmlrpc_encode($productIds),
			php_xmlrpc_encode($subscriptionIds),
			php_xmlrpc_encode($processSpecials),
			php_xmlrpc_encode($promoCodes),
			php_xmlrpc_encode((int) $leadAff),
			php_xmlrpc_encode((int) $saleAff));
		return $this->methodCaller('OrderService.placeOrder', $carray);
	}

	/**
	 * Product Service
	 */

	/**
	 * Retrieves the current inventory level for a specific product
	 *
	 * @method getInventory
	 * @param int $productId
	 * @return int
	 */
	public function getInventory( $productId) {
		$carray = array(
			php_xmlrpc_encode((int) $productId));
		return $this->methodCaller('ProductService.getInventory', $carray);
	}

	/**
	 * Increments current inventory level by 1
	 *
	 * @method incrementInventory
	 * @param int $productId
	 * @return bool
	 */
	public function incrementInventory( $productId) {
		$carray = array(
			php_xmlrpc_encode((int) $productId));
		return $this->methodCaller('ProductService.incrementInventory', $carray);
	}

	/**
	 * Decrements current inventory level by 1
	 *
	 * @method decrementInventory
	 * @param int $productId
	 * @return bool
	 */
	public function decrementInventory( $productId) {
		$carray = array(
			php_xmlrpc_encode((int) $productId));
		return $this->methodCaller('ProductService.decrementInventory', $carray);
	}

	/**
	 * Increases inventory levels
	 *
	 * @method increaseInventory
	 * @param int $productId
	 * @param int $quantity
	 * @return bool
	 */
	public function increaseInventory( $productId, $quantity) {
		$carray = array(
			php_xmlrpc_encode((int) $productId),
			php_xmlrpc_encode((int) $quantity));
		return $this->methodCaller('ProductService.increaseInventory', $carray);
	}

	/**
	 * Decreases inventory levels
	 *
	 * @method decreaseInventory
	 * @param int $productId
	 * @param int $quantity
	 * @return bool
	 */
	public function decreaseInventory( $productId, $quantity) {
		$carray = array(
			php_xmlrpc_encode((int) $productId),
			php_xmlrpc_encode((int) $quantity));
		return $this->methodCaller('ProductService.decreaseInventory', $carray);
	}

	/**
	 * Deactivate a credit card
	 *
	 * @method deactivateCreditCard
	 * @param int $creditCardId
	 * @return bool
	 */
	public function deactivateCreditCard( $creditCardId) {
		$carray = array(
			php_xmlrpc_encode((int) $creditCardId));
		return $this->methodCaller('ProductService.deactivateCreditCard', $carray);
	}

	/**
	 * Search Service
	 */

	/**
	 * Returns a saved search with all fields
	 *
	 * @method getSavedSearchResultsAllFields
	 * @param int $savedSearchId
	 * @param int $userId
	 * @param int $page
	 * @return array
	 */
	public function savedSearchAllFields( $savedSearchId, $userId, $page) {
		$carray = array(
			php_xmlrpc_encode((int) $savedSearchId),
			php_xmlrpc_encode((int) $userId),
			php_xmlrpc_encode((int) $page));
		return $this->methodCaller('SearchService.getSavedSearchResultsAllFields', $carray);
	}

	/**
	 * Returns a saved search with selected fields
	 *
	 * @method getSavedSearchResults
	 * @param int $savedSearchId
	 * @param int $userId
	 * @param int $page
	 * @param array $fields
	 * @return array
	 */
	public function savedSearch( $savedSearchId, $userId, $page, $fields) {
		$carray = array(
			php_xmlrpc_encode((int) $savedSearchId),
			php_xmlrpc_encode((int) $userId),
			php_xmlrpc_encode((int) $page),
			php_xmlrpc_encode($fields));
		return $this->methodCaller('SearchService.getSavedSearchResults', $carray);
	}

	/**
	 * Returns the fields available in a saved report
	 *
	 * @method getAllReportColumns
	 * @param int $savedSearchId
	 * @param int $userId
	 * @return array
	 */
	public function getAvailableFields( $savedSearchId, $userId) {
		$carray = array(
			php_xmlrpc_encode((int) $savedSearchId),
			php_xmlrpc_encode((int) $userId));
		return $this->methodCaller('SearchService.getAllReportColumns', $carray);
	}

	/**
	 * Returns the default quick search type for a user
	 *
	 * @method getDefaultQuickSearch
	 * @param int $userId
	 * @return array
	 */
	public function getDefaultQuickSearch( $userId) {
		$carray = array(
			php_xmlrpc_encode((int) $userId));
		return $this->methodCaller('SearchService.getDefaultQuickSearch', $carray);
	}

	/**
	 * Returns the available quick search types
	 *
	 * @method getAvailableQuickSearches
	 * @param int $userId
	 * @return array
	 */
	public function getQuickSearches( $userId) {
		$carray = array(
			php_xmlrpc_encode((int) $userId));
		return $this->methodCaller('SearchService.getAvailableQuickSearches', $carray);
	}

	/**
	 * Returns the results of a quick search
	 *
	 * @method quickSearch
	 * @param int $quickSearchType
	 * @param int $userId
	 * @param string $filterData
	 * @param int $page
	 * @param int $limit
	 * @return array
	 */
	public function quickSearch( $quickSearchType, $userId, $filterData, $page, $limit) {
		$carray = array(
			php_xmlrpc_encode($quickSearchType),
			php_xmlrpc_encode((int) $userId),
			php_xmlrpc_encode($filterData),
			php_xmlrpc_encode((int) $page),
			php_xmlrpc_encode((int) $limit));
		return $this->methodCaller('SearchService.quickSearch', $carray);
	}

	/**
	 * Service Call Service
	 *
	 * @note also known as Ticket System. This service is deprecated
	 */

	/**
	 * Adds move notes to existing tickets
	 *
	 * @method addMoveNotes
	 * @param array $ticketList
	 * @param string $moveNotes
	 * @param int $moveToStageId
	 * @param int $notifyIds
	 * @return bool
	 */
	public function addMoveNotes( $ticketList, $moveNotes, $moveToStageId, $notifyIds) {
		$carray = array(
			php_xmlrpc_encode($ticketList),
			php_xmlrpc_encode($moveNotes),
			php_xmlrpc_encode($moveToStageId),
			php_xmlrpc_encode($notifyIds));
		return $this->methodCaller('ServiceCallService.addMoveNotes', $carray);
	}

	/**
	 * Moves a Ticket Stage
	 *
	 * @method moveTicketStage
	 * @param int $ticketID
	 * @param string $ticketStage
	 * @param string $moveNotes
	 * @param string $notifyIds
	 * @return bool
	 */
	public function moveTicketStage( $ticketID, $ticketStage, $moveNotes, $notifyIds) {
		$carray = array(
			php_xmlrpc_encode((int) $ticketID),
			php_xmlrpc_encode($ticketStage),
			php_xmlrpc_encode($moveNotes),
			php_xmlrpc_encode($notifyIds));
		return $this->methodCaller('ServiceCallService.moveTicketStage', $carray);
	}

	/**
	 * Shipping Service
	 */

	/**
	 * Get a list of shipping methods
	 *
	 * @method getAllShippingOptions
	 * @return array
	 */
	public function getAllShippingOptions() {
		$carray = array();
		return $this->methodCaller('ShippingService.getAllShippingOptions', $carray);
	}

	/**
	 * Get a list of shipping methods
	 *
	 * @method getAllConfiguredShippingOptions
	 * @return array
	 */
	public function getAllConfiguredShippingOptions() {
		$carray = array();
		return $this->methodCaller('ShippingService.getAllShippingOptions', $carray);
	}

	/**
	 * Retrieves details on a flat rate type shipping option
	 *
	 * @method getFlatRateShippingOption
	 * @param int $optionId
	 * @return array
	 */
	public function getFlatRateShippingOption( $optionId) {
		$carray = array(
			php_xmlrpc_encode((int) $optionId));
		return $this->methodCaller('ShippingService.getFlatRateShippingOption', $carray);
	}

	/**
	 * Retrieves details on a order total type shipping option
	 *
	 * @method getOrderTotalShippingOption
	 * @param int $optionId
	 * @return array
	 */
	public function getOrderTotalShippingOption( $optionId) {
		$carray = array(
			php_xmlrpc_encode((int) $optionId));
		return $this->methodCaller('ShippingService.getOrderTotalShippingOption', $carray);
	}

	/**
	 * Retrieves the pricing range details for the given Order Total shipping option
	 *
	 * @method getOrderTotalShippingRanges
	 * @param int $optionId
	 * @return array
	 */
	public function getOrderTotalShippingRanges( $optionId) {
		$carray = array(
			php_xmlrpc_encode((int) $optionId));
		return $this->methodCaller('ShippingService.getOrderTotalShippingRanges', $carray);
	}

	/**
	 * Retrieves details on a product based type shipping option
	 *
	 * @method getProductBasedShippingOption
	 * @param int $optionId
	 * @return array
	 */
	public function getProductBasedShippingOption( $optionId) {
		$carray = array(
			php_xmlrpc_encode((int) $optionId));
		return $this->methodCaller('ShippingService.getProductBasedShippingOption', $carray);
	}

	/**
	 * Retrieves the pricing for your per product shipping options
	 *
	 * @method getProductShippingPricesForProductShippingOption
	 * @param int $optionId
	 * @return array
	 */
	public function getProductShippingPricesForProductShippingOption( $optionId) {
		$carray = array(
			php_xmlrpc_encode((int) $optionId));
		return $this->methodCaller('ShippingService.getProductShippingPricesForProductShippingOption', $carray);
	}

	/**
	 * Retrieves details on a order quantity type shipping option
	 *
	 * @method getOrderQuantityShippingOption
	 * @param int $optionId
	 * @return array
	 */
	public function getOrderQuantityShippingOption( $optionId) {
		$carray = array(
			php_xmlrpc_encode((int) $optionId));
		return $this->methodCaller('ShippingService.getOrderQuantityShippingOption', $carray);
	}

	/**
	 * Retrieves details on a weight based type shipping option
	 *
	 * @method getWeightBasedShippingOption
	 * @param int $optionId
	 * @return array
	 */
	public function getWeightBasedShippingOption( $optionId) {
		$carray = array(
			php_xmlrpc_encode((int) $optionId));
		return $this->methodCaller('ShippingService.getWeightBasedShippingOption', $carray);
	}

	/**
	 * Retrieves the weight ranges for a weight based type shipping option
	 *
	 * @method getWeightBasedShippingRanges
	 * @param int $optionId
	 * @return array
	 */
	public function getWeightBasedShippingRanges( $optionId) {
		$carray = array(
			php_xmlrpc_encode((int) $optionId));
		return $this->methodCaller('ShippingService.getWeightBasedShippingRanges', $carray);
	}

	/**
	 * Retrieves the details around a UPS type shipping option
	 *
	 * @method getUpsShippingOption
	 * @param int $optionId
	 * @return array
	 */
	public function getUpsShippingOption( $optionId) {
		$carray = array(
			php_xmlrpc_encode((int) $optionId));
		return $this->methodCaller('ShippingService.getUpsShippingOption', $carray);
	}

	/**
	 * Web Form Service
	 */

	/**
	 * Returns web form titles and Id numbers from the application
	 *
	 * @method getMap
	 * @return array
	 */
	public function getWebFormMap() {
		$carray = array();
		return $this->methodCaller('WebFormService.getMap', $carray);
	}

	/**
	 * Returns the HTML for the given web form
	 *
	 * @method getHTML
	 * @param int $webFormId
	 * @return string
	 */
	public function getWebFormHtml( $webFormId = 0) {
		$carray = array(
			php_xmlrpc_encode((int) $webFormId));
		return $this->methodCaller('WebFormService.getHTML', $carray);
	}

	/**
	 * Web Tracking Service
	 */

	/**
	 * Returns the web tracking javascript code
	 *
	 * @method getWebTrackingScriptTag
	 * @return string
	 */
	public function getWebTrackingServiceTag() {
		$carray = array();
		return $this->methodCaller('WebTrackingService.getWebTrackingScriptTag', $carray);
	}

	/**
	 * Returns the url for the web tracking code
	 *
	 * @method getWebTrackingScriptUrl
	 * @return string
	 */
	public function getWebTrackingScriptUrl() {
		$carray = array();
		return $this->methodCaller('WebTrackingService.getWebTrackingScriptUrl', $carray);
	}

}


