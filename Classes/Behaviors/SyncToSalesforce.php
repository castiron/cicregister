<?php
/***************************************************************
 *  Copyright notice
 *  (c) 2011 Zachary Davis <zach
 * @castironcoding.com>, Cast Iron Coding, Inc
 *  All rights reserved
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later

 */
require_once(t3lib_extMgm::extPath('cicregister') . 'Lib/SFDC/SoapClient/SforcePartnerClient.php');

class Tx_Cicregister_Behaviors_SyncToSalesforce extends Tx_Cicregister_Behaviors_AbstractBehavior implements Tx_Cicregister_Behaviors_BehaviorInterface {

	/**
	 * @var array
	 */
	protected $settings;

	protected $SFConnection;

	public function initializeObject() {
		$this->settings = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);
	}

	protected function initSFConnection() {
		if ($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cicregister']) {
			$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cicregister']);
			if ($extConf['SFDCUsername'] && $extConf['SFDCPassword'] && $extConf['SFDCToken'] && $extConf['SFDCWSDL']) {
				$wsdlPath = t3lib_div::getFileAbsFileName($extConf['SFDCWSDL']);
				$this->SFConnection = new SforcePartnerClient;
				$this->SFConnection->createConnection($wsdlPath);
				$SFLoginResults = $this->SFConnection->login($extConf['SFDCUsername'], $extConf['SFDCPassword'] . $extConf['SFDCToken']);
			} else {
				// TODO: Throw an exception
			}
		}
	}

	public function getContactIDByLeadID($id) {
		$query = "
				SELECT	l.ConvertedContactId,
						l.CreatedDate
				FROM Lead l
				WHERE
					l.Id = '" . addslashes($id) . "' AND
					l.isDeleted = false
			";
		$results = $this->SFConnection->query($query);
		if(is_object($results)) {
			if(is_array($results->records) && is_object($results->records[0])) {
				$contactId = $results->records[0]->fields->ConvertedContactId;
				if($contactId) return $contactId;
			}
		}
		return false;
	}

	/**
	 * @param Tx_Cicregister_Domain_Model_FrontendUser $frontendUser
	 * @param array $conf
	 * @return string
	 */
	public function execute(Tx_Cicregister_Domain_Model_FrontendUser $frontendUser, array $conf) {

		// 1. Decide whether we're creating a lead or a contact based on the SFDC Ids stored on the user record.
		if($frontendUser->getSfdcLeadID()) {
			$objType = 'lead';
			$objId = $frontendUser->getSfdcLeadID();
		} elseif($frontendUser->getSfdcContactID()) {
			$objType = 'contact';
			$objId = $frontendUser->getSfdcContactID();
		} else {
			$objType = 'lead';
		}

		$this->initSFConnection();

		// 2. Check if the existing lead has at some point been converted to a contact. If so, then we'll want to
		//    go ahead and update the contact, not the lead.
		if($objId && $objType == 'lead') {
			$contactId = $this->getContactIDByLeadID($objId);
			if($contactId) {
				$frontendUser->setSfdcContactID($contactId);
				$objType = 'contact';
				$objId = $contactId;
			}
		}

		// 3. Upsert the ocontact or the lead.
		if($objType == 'lead') {
			$SFUpsertObject = $this->createSfLead($frontendUser);
		} elseif($objType == 'contact') {
			$SFUpsertObject = $this->createSfContact($frontendUser);
		}
		if ($objId) $SFUpsertObject->fields['Id'] = $objId;
		$SFUpsertObjectId = $this->upsertOneObject($SFUpsertObject);

		// 4. If we have a SFDC Id after the upsert, then we assume it succeeded and we add the ID to the correct
		//    field on the user record. If we do not have a SFDC Id, then we assume that the upsert failed, and we
		//    try to insert a fresh lead.
		if ($SFUpsertObjectId) {
			// success
			if($objType == 'lead') {
				$frontendUser->setSfdcLeadID($SFUpsertObjectId);
				$frontendUser->setSfdcSyncTimestamp(time());
			} elseif($objType == 'contact') {
				$frontendUser->setSfdcContactID($SFUpsertObjectId);
				$frontendUser->setSfdcSyncTimestamp(time());
			}
		} elseif ($SFUpsertObject->fields['Id']) {
			// failure; try again without the ID (force an insert)
			$SFUpsertObject->fields['Id'] = '';
			$SFUpsertObjectId = $this->upsertOneObject($SFUpsertObject);
			$frontendUser->setSfdcLeadID($SFUpsertObjectId);
			$frontendUser->setSfdcContactID('');
			$frontendUser->setSfdcSyncTimestamp(time());
		} else {
			// TODO: Handle this exception.
		}

		// 5. Add the campaign ID to the contact or lead, if required.
		if($SFUpsertObjectId && $conf['sfdcCampaignId']) {
			$SFCampaignMember = new sObject();
			$SFCampaignMember->type = 'CampaignMember';
			$SFCampaignMember->fields['CampaignId'] = $conf['sfdcCampaignId'];
			if($type == 'Lead') {
				$SFCampaignMember->fields['LeadId'] = $SFUpsertObjectId;
				$SFCampaignMember->fields['ContactId'] = NULL;
			} elseif($type == 'Contact') {
				$SFCampaignMember->fields['ContactId'] = $SFUpsertObjectId;
				$SFCampaignMember->fields['LeadId'] = NULL;
			}
			$SFCampaignMemberId = $this->upsertOneObject($SFCampaignMember);
		}

	}

	/**
	 * @param sObject $SFObject
	 * @param string $externalIdField
	 * @return mixed
	 */
	protected function upsertOneObject(sObject $SFObject, $externalIdField = 'Id') {
		try {
			$res = $this->SFConnection->upsert($externalIdField, array($SFObject));
		} catch (Exception $e) {
			// TODO: Better error handling
			// t3lib_utility_Debug::debug($e->getMessage(), __FILE__ . " " . __LINE__);
		}
		$SFObjId = $res[0]->id;
		return $SFObjId;
	}

	/**
	 * @param Tx_Cicregister_Domain_Model_FrontendUser $frontendUser
	 * @return sObject
	 */
	protected function createSfLead(Tx_Cicregister_Domain_Model_FrontendUser $frontendUser) {
		$SFObj = new sObject();
		$SFObj->type = 'Lead';
		$SFObj->fields['FirstName'] = $frontendUser->getFirstName();
		$SFObj->fields['LastName'] = $frontendUser->getLastName();
		$SFObj->fields['Email'] = $frontendUser->getEmail();
		$SFObj->fields['Phone'] = $frontendUser->getTelephone();
		$SFObj->fields['Website'] = $frontendUser->getWWW();
		$SFObj->fields['City'] = $frontendUser->getCity();
		$SFObj->fields['Country'] = $frontendUser->getCountry();
		$SFObj->fields['PostalCode'] = $frontendUser->getZip();
		$SFObj->fields['Street'] = $frontendUser->getAddress();
		$SFObj->fields['Company'] = $frontendUser->getCompany();
		$SFObj->fields['Title'] = $frontendUser->getTitle();

		if($SFObj->fields['Company'] == '' && $frontendUser->getName()) {
			$SFObj->fields['Company'] = $frontendUser->getName();
		} elseif($SFObj->fields['Company'] == '') {
			$SFObj->fields['Company'] = 'Unknown';
		}
		$this->createSfLeadChild($SFObj, $frontendUser);
		return $SFObj;
	}

	/**
	 * @param Tx_Cicregister_Domain_Model_FrontendUser $frontendUser
	 * @return sObject
	 */
	protected function createSfContact(Tx_Cicregister_Domain_Model_FrontendUser $frontendUser) {
		$SFObj = new sObject();
		$SFObj->type = 'Contact';
		$SFObj->fields['FirstName'] = $frontendUser->getFirstName();
		$SFObj->fields['LastName'] = $frontendUser->getLastName();
		$SFObj->fields['Email'] = $frontendUser->getEmail();
		$SFObj->fields['Phone'] = $frontendUser->getTelephone();
		$SFObj->fields['MailingCity'] = $frontendUser->getCity();
		$SFObj->fields['MailingCountry'] = $frontendUser->getCountry();
		$SFObj->fields['MailingPostalCode'] = $frontendUser->getZip();
		$SFObj->fields['MailingStreet'] = $frontendUser->getAddress();
		$SFObj->fields['Title'] = $frontendUser->getTitle();
		$this->createSfContactChild($SFObj, $frontendUser);
		return $SFObj;
	}

	/**
	 * Implement this in your child class
	 * @param sObject $SFLead
	 * @param Tx_Cicregister_Domain_Model_FrontendUser $frontendUser
	 */
	protected function createSfLeadChild(sObject $SFLead, Tx_Cicregister_Domain_Model_FrontendUser $frontendUser) {
	}

	/**
	 * Implement this in your child class
	 * @param sObject $SFContact
	 * @param Tx_Cicregister_Domain_Model_FrontendUser $frontendUser
	 */
	protected function createSfContactChild(sObject $SFContact, Tx_Cicregister_Domain_Model_FrontendUser $frontendUser) {
	}
}