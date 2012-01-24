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

class Tx_Cicregister_Controller_FrontendUserJSONController extends Tx_Cicregister_Controller_FrontendUserBaseController {

	/**
	 * @param Tx_Cicregister_Domain_Model_FrontendUser $frontendUser
	 */
	public function createAction(Tx_Cicregister_Domain_Model_FrontendUser $frontendUser) {
		$behaviorResponse = $this->createAndPersistUser($frontendUser);
		$results = new stdClass;
		$results->hasErrors = false;

		switch(get_class($behaviorResponse)) {
			case 'Tx_Cicregister_Behaviors_Response_RenderAction':
				$viewObjectName = 'Tx_Cicregister_View_FrontendUserJSON_' . $behaviorResponse->getValue();
				$view = $this->objectManager->create($this->defaultViewObjectName);
				$this->setViewConfiguration($view);
				$view->setControllerContext($this->controllerContext);
				$view->assign('settings', $this->settings); // same with settings injection.
				$this->controllerContext->getRequest()->setFormat('html');
				$out = $view->render($behaviorResponse->getValue() . '');
				$this->controllerContext->getRequest()->setFormat('json');
				$results->html = $out;
			break;
			case 'Tx_Cicregister_Behaviors_Response_RedirectAction':
				$uriBuilder = $this->controllerContext->getUriBuilder();
				$uri = $uriBuilder
						->reset()
						->setTargetPageUid($this->settings['pids']['editView'])
						->setNoCache(false)
						->setUseCacheHash(false)
						->uriFor($behaviorResponse->getValue(), NULL, 'FrontendUser');
				$results->redirect = $uri;
			break;
			case 'Tx_Cicregister_Behaviors_Response_RedirectURI':
				$results->redirect = $behaviorResponse->getValue();
			break;
		}
		$this->view->assign('results',json_encode($results));
	}

	public function initializeCreateAction() {
#		t3lib_div::debug($this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManager::CONFIGURATION_TYPE_FRAMEWORK));
	}


	/**
	 */
	protected function errorAction() {
		$results = new stdClass;
		$results->hasErrors = false;
		$errorResults = $this->arguments->getValidationResults()->forProperty('frontendUser');

		$results->errors = new stdClass();
		$results->errors->byProperty = array();
		foreach($errorResults->getFlattenedErrors() as $property => $error) {
			$errorDetails = $errorResults->forProperty($property)->getErrors();
			foreach($errorDetails as $error) {
				$results->hasErrors = true;
				$errorObj = new stdClass;
				$errorObj->code = $error->getCode();
				$errorObj->property = $property;
				$key = 'form-frontendUserController-frontendUser.' . $errorObj->property . '-' . $errorObj->code;
				$translatedMessage = Tx_Extbase_Utility_Localization::translate($key,'cicregister');
				if($translatedMessage) {
					$errorObj->message = $translatedMessage;
				} else {
					$errorObj->message = $error->getMessage();
				}
				$results->errors->byProperty[$property][] = $errorObj;
			}
		}
		$this->view->assign('results',json_encode($results));
	}

}

?>