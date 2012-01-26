<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Zachary Davis <zach@castironcoding.com>, Cast Iron Coding, Inc
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 *
 */
require_once(t3lib_extMgm::extPath('cicregister') . 'Lib/SFDC/SoapClient/SforcePartnerClient.php');

class Tx_Cicregister_Behaviors_SyncToSalesforceContact extends Tx_Cicregister_Behaviors_AbstractBehavior implements Tx_Cicregister_Behaviors_BehaviorInterface {

	/**
	 * @var array
	 */
	protected $settings;
	protected $SFConnection;

	public function initializeObject() {
		$this->settings = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);
	}

	protected function initSFConnection() {
		if($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cicregister']) {
			$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cicregister']);
			if($extConf['SFDCUsername'] && $extConf['SFDCPassword'] && $extConf['SFDCToken'] && $extConf['SFDCWSDL']) {
				$wsdlPath = t3lib_div::getFileAbsFileName($extConf['SFDCWSDL']);
				$this->SFConnection = new SforcePartnerClient;
				$this->SFConnection->createConnection($wsdlPath);
				$SFLoginResults = $this->SFConnection->login($extConf['SFDCUsername'], $extConf['SFDCPassword'] . $extConf['SFDCToken']);
			} else {
				// TODO: Throw an exception
			}
		}
	}


	/**
	 * @param Tx_Cicregister_Domain_Model_FrontendUser $frontendUser
	 * @param array $conf
	 * @return string
	 */
	public function execute(Tx_Cicregister_Domain_Model_FrontendUser $frontendUser, array $conf) {

		$this->initSFConnection();

		$SFContact = $this->createSFContact($frontendUser);
		if($frontendUser->getSfdcContactId()) $SFContact->fields['Id'] = $frontendUser->getSfdcContactId();
		$SFContactId = $this->upsertOneObject($SFContact);

		if($SFContactId) {
			// success
			$frontendUser->setSfdcContactID($SFContactId);
		} elseif($SFContact->fields['Id']) {
			// failure; try again without the ID (force an insert)
			$SFContact->fields['Id'] = '';
			$SFContactId = $this->upsertOneObject($SFContact);
			$frontendUser->setSfdcContactID($SFContactId);
		} else {
			// failure...
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
			t3lib_utility_Debug::debug($e->getMessage(),__FILE__ . " " . __LINE__);
		}

		$SFObjId = $res[0]->id;
		return $SFObjId;
	}

	/**
	 * @param Tx_Cicregister_Domain_Model_FrontendUser $frontendUser
	 * @return sObject
	 */
	protected function createSfContact(Tx_Cicregister_Domain_Model_FrontendUser $frontendUser) {
		$SFContact = new sObject();
		$SFContact->type = 'Contact';
		$SFContact->fields['FirstName'] = $frontendUser->getFirstName();
		$SFContact->fields['LastName'] = $frontendUser->getLastName();
		$SFContact->fields['Email'] = $frontendUser->getEmail();
		$SFContact->fields['Phone'] = $frontendUser->getTelephone();
		$SFContact->fields['MailingCity'] = $frontendUser->getCity();
		$SFContact->fields['MailingCountry'] = $frontendUser->getCountry();
		$SFContact->fields['MailingPostalCode'] = $frontendUser->getZip();
		$SFContact->fields['MailingStreet'] = $frontendUser->getAddress();
		$SFContact->fields['Title'] = $frontendUser->getTitle();
		$this->createSfContactChild($SFContact, $frontendUser);
		return $SFContact;
	}

	/**
	 * Implement this in your own child class to add additional fields to the SF Contact.
	 *
	 * @param sObject $SFContact
	 * @param Tx_Cicregister_Domain_Model_FrontendUser $frontendUser
	 */
	protected function createSfContactChild(sObject $SFContact, Tx_Cicregister_Domain_Model_FrontendUser $frontendUser) {
		// do nothing...
	}

}