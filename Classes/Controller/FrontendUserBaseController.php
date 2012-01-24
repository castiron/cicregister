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

	/**
	 * @param Tx_Cicregister_Domain_Model_FrontendUser $frontendUser
	 * @return mixed
	 */
	protected function createAndPersistUser(Tx_Cicregister_Domain_Model_FrontendUser $frontendUser) {

		// add the user to the default group
		$defaultGroup = $this->frontendUserGroupRepository->findByUid($this->settings['defaults']['groupUid']);
		if ($defaultGroup instanceof Tx_Extbase_Domain_Model_FrontendUserGroup) $frontendUser->addUsergroup($defaultGroup);

		// decorate the new user
		$this->decoratorService->decorate($this->settings['decorators']['frontendUser']['created'], $frontendUser);

		// add the user to the repository
		$this->frontendUserRepository->add($frontendUser);
		$this->flashMessageContainer->add('Your account has been created.');

		// persist the user
		$persistenceManager = $this->objectManager->get('Tx_Extbase_Persistence_Manager');
		$persistenceManager->persistAll();

		$behaviorResponse = $this->behaviorService->executeBehaviors($this->settings['behaviors']['frontendUser']['created'], $frontendUser, $this->controllerContext, 'createConfirmation');
		return $behaviorResponse;

	}




}
