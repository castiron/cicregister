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
 * @package cicregister
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */

class Tx_Cicregister_Controller_FrontendUserController extends Tx_Extbase_MVC_Controller_ActionController {

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

	public function initializeAction() {
		// If a developer has told extbase to use another object instead of Tx_Cicregister_Domain_Model_FrontendUser, then we
		// want to make sure that the replacement object is validated instead of the default cicregister object. Whereas the
		// object manager does look at Extbase's objects Typoscript section, the argument validator does not.
		$frameworkConfiguration = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
		$replacementFrontendUserObject = $frameworkConfiguration['objects']['Tx_Cicregister_Domain_Model_FrontendUser']['className'];
		if($replacementFrontendUserObject) {
			$frontendUserClass = $frameworkConfiguration['objects']['Tx_Cicregister_Domain_Model_FrontendUser']['className'];
			if($this->arguments->offsetExists('frontendUser')) {
				$required = FALSE;
				if($this->arguments->getArgument('frontendUser')->isRequired() === TRUE) $required = TRUE;
				$this->arguments->addNewArgument('frontendUser', 'Tx_Dodgeuser_Domain_Model_FrontendUser', $required);
				// perhaps there's a better way here, than to re-initialize all arguments
				$this->initializeActionMethodValidators();
			}
		}
	}

	/**
	 * Renders the "new user" form.
	 *
	 * @param Tx_Cicregister_Domain_Model_FrontendUser $frontendUser
	 * @return void
	 */
	public function newAction(Tx_Cicregister_Domain_Model_FrontendUser $frontendUser = NULL) {
		$this->view->assign('frontendUser', $frontendUser);
		$this->signalSlotDispatcher->dispatch(__CLASS__, 'newAction', array('frontendUser' => $frontendUser, 'view' => $this->view));
	}

	/**
	 * @param Tx_Cicregister_Domain_Model_FrontendUser $frontendUser
	 */
	public function createAction(Tx_Cicregister_Domain_Model_FrontendUser $frontendUser) {

		// add the user to the default group
		$defaultGroup = $this->frontendUserGroupRepository->findByUid($this->settings['defaults']['groupUid']);
		if($defaultGroup instanceof Tx_Extbase_Domain_Model_FrontendUserGroup) $frontendUser->addUsergroup($defaultGroup);

		// decorate the new user
		$this->decoratorService->decorate($this->settings['decorators']['frontendUser']['created'],$frontendUser);

		// add the user to the repository
		$this->frontendUserRepository->add($frontendUser);
		$this->flashMessageContainer->add('Your account has been created.');

		// persist the user
		$persistenceManager = $this->objectManager->get('Tx_Extbase_Persistence_Manager');
		$persistenceManager->persistAll();

		// execute behaviors and forward (a redirect would be nice here, but it's tricky because the user object is still disabled!)
		$forwardAction = $this->behaviorService->executeBehaviors($this->settings['behaviors']['frontendUser']['created'],$frontendUser);
		if($forwardAction == false || $forwardAction == '') $forwardAction = 'createConfirmation';
		$this->forward($forwardAction,NULL,NULL,array('frontendUser' => $frontendUser));

	}

	/**
	 * @param string $key
	 */
	public function validateUserAction($key) {
		$emailValidatorService = $this->objectManager->get('Tx_Cicregister_Service_EmailValidator');
		$frontendUser = $emailValidatorService->validateKey($key);
		if($frontendUser instanceof Tx_Cicregister_Domain_Model_FrontendUser) {
			$frontendUser->setDisable(false);
			$this->frontendUserRepository->update($frontendUser);
			$persistenceManager = $this->objectManager->get('Tx_Extbase_Persistence_Manager');
			$persistenceManager->persistAll();
		}
		// TODO: Handle forwards and confirmations.
	}

	/**
	 * @param Tx_Cicregister_Domain_Model_FrontendUser $frontendUser
	 */
	public function createConfirmationAction(Tx_Cicregister_Domain_Model_FrontendUser $frontendUser) {

	}

	/**
	 * @param Tx_Cicregister_Domain_Model_FrontendUser $frontendUser
	 */
	public function createConfirmationMustValidateAction(Tx_Cicregister_Domain_Model_FrontendUser $frontendUser) {

	}

	/**
	 * Edit user action
	 *
	 * @param $frontendUser
	 * @return void
	 */
	public function editAction(Tx_Cicregister_Domain_Model_FrontendUser $frontendUser) {
		$this->signalSlotDispatcher->dispatch(__CLASS__, 'editAction', array('frontendUser' => $frontendUser, 'view' => $this->view));
		$this->view->assign('frontendUser', $frontendUser);
	}

	/**
	 * Update action
	 *
	 * @param $frontendUser
	 * @return void
	 */
	public function updateAction(Tx_Cicregister_Domain_Model_FrontendUser $frontendUser) {
		$this->signalSlotDispatcher->dispatch(__CLASS__, 'updateAction', array('frontendUser' => $frontendUser, 'view' => $this->view));
		#$this->FrontendUserRepository->update($frontendUser);
		$this->flashMessageContainer->add('Your Frontend user was updated.');
		$this->redirect('edit');
	}

	/**
	 * @return string
	 */
	protected function getErrorFlashMessage() {
		switch ($this->actionMethodName) {
			default:
				$msg = Tx_Extbase_Utility_Localization::translate('flash-frontendUserController-' . $this->actionMethodName . '-default', 'cicregister');
			break;
		}
		if($msg == false) {
			$msg = 'no error message set for '.$this->actionMethodName;
		}
		return $msg;
	}

}
?>