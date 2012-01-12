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

class Tx_Cicregister_Behaviors_SendValidationEmail extends Tx_Cicregister_Behaviors_AbstractBehavior implements Tx_Cicregister_Behaviors_BehaviorInterface {

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @var Tx_Cicregister_Service_HashValidator
	 */
	protected $emailValidator;

	/**
	 * @param Tx_Cicregister_Service_HashValidator $emailValidator
	 */
	public function injectEmailValidator(Tx_Cicregister_Service_HashValidator $emailValidator) {
		$this->emailValidator = $emailValidator;
	}

	public function initializeObject() {
		$this->settings = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);
	}

	/**
	 * @param Tx_Cicregister_Domain_Model_FrontendUser $frontendUser
	 * @param array $conf
	 * @return string
	 */
	public function execute(Tx_Cicregister_Domain_Model_FrontendUser $frontendUser, array $conf) {
		$recipients = array($frontendUser->getEmail() => $frontendUser->getName());
		$sender = array($conf['senderEmail'] => $conf['senderName']);
		$subject = $conf['validateSubject'];
		$templateName = 'ValidateEmail.html';
		$variables = $conf['variables'];
		$variables['frontendUser'] = $frontendUser;
		$variables['validationKey'] = $this->emailValidator->generateKey($frontendUser);
		$this->sendTemplateEmail($recipients, $sender, $subject, $templateName, $variables);

		$response = $this->objectManager->create('Tx_Cicregister_Behaviors_Response_RenderAction');
		$response->setValue('createConfirmationMustValidate');
		return $response;
	}

}
