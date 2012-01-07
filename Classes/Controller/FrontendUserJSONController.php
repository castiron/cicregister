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
 * @package cicregister
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */

class Tx_Cicregister_Controller_FrontendUserJSONController extends Tx_Extbase_MVC_Controller_ActionController {

	/**
	 * @var Tx_Cicregister_Domain_Repository_FrontendUserRepository
	 */
	protected $frontendUserRepository;

	/**
	 * @var Tx_Extbase_Reflection_Service
	 */
	protected $reflectionService;

	/**
	 * inject the reflectionService
	 *
	 * @param Tx_Extbase_Reflection_Service reflectionService
	 * @return void
	 */
	public function injectReflectionService(Tx_Extbase_Reflection_Service $reflectionService) {
		$this->reflectionService = $reflectionService;
	}

	/**
	 * @param Tx_Cicregister_Domain_Model_FrontendUser $frontendUser
	 */
	public function createAction(Tx_Cicregister_Domain_Model_FrontendUser $frontendUser) {



		// add the user to the default group
#		$defaultGroup = $this->frontendUserGroupRepository->findByUid($this->settings['defaults']['groupUid']);
#		if ($defaultGroup instanceof Tx_Extbase_Domain_Model_FrontendUserGroup) $frontendUser->addUsergroup($defaultGroup);

		// decorate the new user
#		$this->decoratorService->decorate($this->settings['decorators']['frontendUser']['created'], $frontendUser);

		// add the user to the repository
#		$this->frontendUserRepository->add($frontendUser);
#		$this->flashMessageContainer->add('Your account has been created.');

		// persist the user
#		$persistenceManager = $this->objectManager->get('Tx_Extbase_Persistence_Manager');
#		$persistenceManager->persistAll();

		// execute behaviors and forward (a redirect would be nice here, but it's tricky because the user object is still disabled!)
#		$forwardAction = $this->behaviorService->executeBehaviors($this->settings['behaviors']['frontendUser']['created'], $frontendUser);
#		if ($forwardAction == false || $forwardAction == '') $forwardAction = 'createConfirmation';
#		$this->forward($forwardAction, NULL, NULL, array('frontendUser' => $frontendUser));
	}


	/**
	 */
	protected function errorAction() {
		$results = new stdClass;
		$results->success = false;
		$errorResults = $this->arguments->getValidationResults()->forProperty('frontendUser');
		$results->errors = new stdClass();
		$results->errors->byProperty = array();
		foreach($errorResults->getFlattenedErrors() as $property => $error) {
			$errorDetails = $errorResults->forProperty($property)->getErrors();
			foreach($errorDetails as $error) {
				$errorObj = new stdClass;
				$errorObj->code = $error->getCode();
				$errorObj->property = $property;
				$errorObj->message = $error->getMessage();
				$results->errors->byProperty[$property][] = $errorObj;
			}
		}
		$this->view->assign('results',json_encode($results));
	}

}

?>