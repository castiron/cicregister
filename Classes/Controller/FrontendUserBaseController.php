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

abstract class Tx_Cicregister_Controller_FrontendUserBaseController extends Tx_Extbase_MVC_Controller_ActionController {

	/**
	 * @var bool
	 */
	protected $userIsAuthenticated = false;

	/**
	 * @var array
	 */
	protected $user = array();

	/**
	 * @var Tx_Cicregister_Domain_Repository_FrontendUserRepository
	 */
	protected $frontendUserRepository;

	/**
	 * @var Tx_Extbase_SignalSlot_Dispatcher
	 */
	protected $signalSlotDispatcher;

	/**
	 * @var Tx_Cicregister_Service_Decorator
	 */
	protected $decoratorService;

	/**
	 * @var Tx_Extbase_Property_PropertyMapper
	 */
	protected $propertyMapper;

	/**
	 * @var Tx_Cicregister_Domain_Repository_FrontendUserGroupRepository
	 */
	protected $frontendUserGroupRepository;

	/**
	 * @var Tx_Cicregister_Service_Behavior
	 */
	protected $behaviorService;

	/**
	 * @var Tx_Extbase_Persistence_Manager
	 */
	protected $persistenceManager;

	/**
	 * inject the persistenceManager
	 *
	 * @param Tx_Extbase_Persistence_Manager persistenceManager
	 * @return void
	 */
	public function injectPersistenceManager(Tx_Extbase_Persistence_Manager $persistenceManager) {
		$this->persistenceManager = $persistenceManager;
	}

	/**
	 * inject the behaviorService
	 *
	 * @param Tx_Cicregister_Service_Behavior behaviorService
	 * @return void
	 */
	public function injectBehaviorService(Tx_Cicregister_Service_Behavior $behaviorService) {
		$this->behaviorService = $behaviorService;
	}

	/**
	 * inject the frontendUserGroupRepository
	 *
	 * @param Tx_Cicregister_Domain_Repository_FrontendUserGroupRepository frontendUserGroupRepository
	 * @return void
	 */
	public function injectFrontendUserGroupRepository(Tx_Cicregister_Domain_Repository_FrontendUserGroupRepository $frontendUserGroupRepository) {
		$this->frontendUserGroupRepository = $frontendUserGroupRepository;
	}

	/**
	 * inject the propertyMapper
	 *
	 * @param Tx_Extbase_Property_PropertyMapper propertyMapper
	 * @return void
	 */
	public function injectPropertyMapper(Tx_Extbase_Property_PropertyMapper $propertyMapper) {
		$this->propertyMapper = $propertyMapper;
	}

	/**
	 * inject the decoratorService
	 *
	 * @param Tx_Cicregister_Service_Decorator decoratorService
	 * @return void
	 */
	public function injectDecoratorService(Tx_Cicregister_Service_Decorator $decoratorService) {
		$this->decoratorService = $decoratorService;
	}

	/**
	 * Inject the signalSlotDispatcher
	 *
	 * @param Tx_Extbase_SignalSlot_Dispatcher signalSlotDispatcher
	 * @return void
	 */
	public function injectSignalSlotDispatcher(Tx_Extbase_SignalSlot_Dispatcher $signalSlotDispatcher) {
		$this->signalSlotDispatcher = $signalSlotDispatcher;
	}

	/**
	 * Inject the frontendUserRepository
	 *
	 * @param Tx_Cicregister_Domain_Repository_FrontendUserRepository $frontendUserRepository
	 * @return void
	 */
	public function injectFrontendUserRepository(Tx_Cicregister_Domain_Repository_FrontendUserRepository $frontendUserRepository) {
		$this->frontendUserRepository = $frontendUserRepository;
	}

	/**
	 * @param $confKey
	 * @param $frontendUser
	 */
	protected function decorateUser($frontendUser, $confKey) {
		$conf = $this->settings['decorators']['frontendUser'][$confKey];
		if(!is_array($conf)) $conf = array();
		$this->decoratorService->decorate($conf, $frontendUser);
	}

	/**
	 * @param $frontendUser
	 * @param $confKey
	 * @param $defaultForward
	 * @return mixed
	 */
	protected function doBehaviors($frontendUser, $confKey, $defaultForward, $extraConf = array()) {
		$behaviorsConf = $this->settings['behaviors']['frontendUser'][$confKey];
		if(!is_array($behaviorsConf)) $behaviorsConf = array();
		$behaviorResponse = $this->behaviorService->executeBehaviors($behaviorsConf, $frontendUser, $this->controllerContext, $defaultForward, $extraConf);
		return $behaviorResponse;
	}

	/**
	 * @param Tx_Cicregister_Behaviors_Response_ResponseInterface $behaviorResponse
	 * @param Tx_Cicregister_Domain_Model_FrontendUser $frontendUser
	 */
	public function handleBehaviorResponse(Tx_Cicregister_Behaviors_Response_ResponseInterface $behaviorResponse, Tx_Cicregister_Domain_Model_FrontendUser $frontendUser) {
		// Behaviors can return one of three types of actions, which determine what happens after the user is created.

		switch (get_class($behaviorResponse)) {
			case 'Tx_Cicregister_Behaviors_Response_RenderAction':
				$this->redirect($behaviorResponse->getValue(), NULL, NULL, array('frontendUser' => $frontendUser));
				break;

			case 'Tx_Cicregister_Behaviors_Response_RedirectAction':
				$this->redirect($behaviorResponse->getValue());
				break;

			case 'Tx_Cicregister_Behaviors_Response_RedirectURI':
				$this->redirectToUri($behaviorResponse->getValue());
				break;
		}
	}

	/**
	 * @param Tx_Cicregister_Domain_Model_FrontendUser $frontendUser
	 * @return mixed
	 */
	protected function createAndPersistUser(Tx_Cicregister_Domain_Model_FrontendUser $frontendUser) {

		// add the user to the default group
		$defaultGroup = $this->frontendUserGroupRepository->findByUid($this->settings['defaults']['globalGroupId']);
		if ($defaultGroup instanceof Tx_Extbase_Domain_Model_FrontendUserGroup) $frontendUser->addUsergroup($defaultGroup);

		$this->decorateUser($frontendUser, 'created');

		// add the user to the repository
		$this->frontendUserRepository->add($frontendUser);
		$this->flashMessageContainer->add('Your account has been created.');

		// persist the user
		$this->persistenceManager->persistAll();

		return $this->doBehaviors($frontendUser, 'created', 'createConfirmation');
	}

	public function initializeAction() {
		$this->userData = $GLOBALS['TSFE']->fe_user->user;
		if (isset($GLOBALS['TSFE']) && $GLOBALS['TSFE']->loginUser) {
			$this->userIsAuthenticated = true;
		}

		// If a developer has told extbase to use another object instead of Tx_Cicregister_Domain_Model_FrontendUser, then we
		// want to make sure that the replacement object is validated instead of the default cicregister object. Whereas the
		// object manager does look at Extbase's objects Typoscript section, the argument validator does not.
		$frameworkConfiguration = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
		$replacementFrontendUserObject = $frameworkConfiguration['objects']['Tx_Cicregister_Domain_Model_FrontendUser']['className'];
		if ($replacementFrontendUserObject) {
			$frontendUserClass = $frameworkConfiguration['objects']['Tx_Cicregister_Domain_Model_FrontendUser']['className'];
			if ($this->arguments->offsetExists('frontendUser')) {
				$required = FALSE;
				if ($this->arguments->getArgument('frontendUser')->isRequired() === TRUE) $required = TRUE;
				$this->arguments->addNewArgument('frontendUser', $frontendUserClass, $required);
				// perhaps there's a better way here, than to re-initialize all arguments
				$this->initializeActionMethodValidators();
			}
		}
	}


}
