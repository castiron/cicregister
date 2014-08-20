<?php
namespace CIC\Cicregister\Controller;

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

abstract class FrontendUserBaseController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * @var bool
	 */
	protected $userIsAuthenticated = false;

	/**
	 * @var array
	 */
	protected $user = array();

	/**
	 * @var \CIC\Cicregister\Domain\Repository\FrontendUserRepository
	 */
	protected $frontendUserRepository;

	/**
	 * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
	 */
	protected $signalSlotDispatcher;

	/**
	 * @var \CIC\Cicregister\Service\Decorator
	 */
	protected $decoratorService;

	/**
	 * @var \TYPO3\CMS\Extbase\Property\PropertyMapper
	 */
	protected $propertyMapper;

	/**
	 * @var \CIC\Cicregister\Domain\Repository\FrontendUserGroupRepository
	 */
	protected $frontendUserGroupRepository;

	/**
	 * @var \CIC\Cicregister\Service\Behavior
	 */
	protected $behaviorService;

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
	 */
	protected $persistenceManager;

	/**
	 * inject the persistenceManager
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager persistenceManager
	 * @return void
	 */
	public function injectPersistenceManager(\TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager $persistenceManager) {
		$this->persistenceManager = $persistenceManager;
	}

	/**
	 * inject the behaviorService
	 *
	 * @param \CIC\Cicregister\Service\Behavior behaviorService
	 * @return void
	 */
	public function injectBehaviorService(\CIC\Cicregister\Service\Behavior $behaviorService) {
		$this->behaviorService = $behaviorService;
	}

	/**
	 * inject the frontendUserGroupRepository
	 *
	 * @param \CIC\Cicregister\Domain\Repository\FrontendUserGroupRepository frontendUserGroupRepository
	 * @return void
	 */
	public function injectFrontendUserGroupRepository(\CIC\Cicregister\Domain\Repository\FrontendUserGroupRepository $frontendUserGroupRepository) {
		$this->frontendUserGroupRepository = $frontendUserGroupRepository;
	}

	/**
	 * inject the propertyMapper
	 *
	 * @param \TYPO3\CMS\Extbase\Property\PropertyMapper propertyMapper
	 * @return void
	 */
	public function injectPropertyMapper(\TYPO3\CMS\Extbase\Property\PropertyMapper $propertyMapper) {
		$this->propertyMapper = $propertyMapper;
	}

	/**
	 * inject the decoratorService
	 *
	 * @param \CIC\Cicregister\Service\Decorator decoratorService
	 * @return void
	 */
	public function injectDecoratorService(\CIC\Cicregister\Service\Decorator $decoratorService) {
		$this->decoratorService = $decoratorService;
	}

	/**
	 * Inject the signalSlotDispatcher
	 *
	 * @param \TYPO3\CMS\Extbase\SignalSlot\Dispatcher signalSlotDispatcher
	 * @return void
	 */
	public function injectSignalSlotDispatcher(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher) {
		$this->signalSlotDispatcher = $signalSlotDispatcher;
	}

	/**
	 * Inject the frontendUserRepository
	 *
	 * @param \CIC\Cicregister\Domain\Repository\FrontendUserRepository $frontendUserRepository
	 * @return void
	 */
	public function injectFrontendUserRepository(\CIC\Cicregister\Domain\Repository\FrontendUserRepository $frontendUserRepository) {
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
	 * @param array $extraConf
	 * @return bool|object
	 */
	protected function doBehaviors($frontendUser, $confKey, $defaultForward, $extraConf = array()) {
		$behaviorsConf = $this->settings['behaviors']['frontendUser'][$confKey];
		if(!is_array($behaviorsConf)) $behaviorsConf = array();
		$behaviorResponse = $this->behaviorService->executeBehaviors($behaviorsConf, $frontendUser, $this->controllerContext, $defaultForward, $extraConf);
		return $behaviorResponse;
	}

	/**
	 * @param \CIC\Cicregister\Behaviors\Response\ResponseInterface $behaviorResponse
	 * @param \CIC\Cicregister\Domain\Model\FrontendUser $frontendUser
	 */
	public function handleBehaviorResponse(\CIC\Cicregister\Behaviors\Response\ResponseInterface $behaviorResponse, \CIC\Cicregister\Domain\Model\FrontendUser $frontendUser) {
		// Behaviors can return one of three types of actions, which determine what happens after the user is created.

		switch (get_class($behaviorResponse)) {
			case 'CIC\\Cicregister\\Behaviors\\Response\\RenderAction':
				$this->redirect($behaviorResponse->getValue(), NULL, NULL, array('frontendUser' => $frontendUser));
				break;

			case 'CIC\\Cicregister\\Behaviors\\Response\\RedirectAction':
				$this->redirect($behaviorResponse->getValue());
				break;

			case 'CIC\\Cicregister\\Behaviors\\Response\\RedirectURI':
				$this->redirectToUri($behaviorResponse->getValue());
				break;
		}
	}

	/**
	 * @param \CIC\Cicregister\Domain\Model\FrontendUser $frontendUser
	 * @return mixed
	 */
	protected function createAndPersistUser(\CIC\Cicregister\Domain\Model\FrontendUser $frontendUser) {

		// add the user to the default group
		$defaultGroup = $this->frontendUserGroupRepository->findByUid($this->settings['defaults']['globalGroupId']);
		if ($defaultGroup instanceof \TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup) $frontendUser->addUsergroup($defaultGroup);

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

		// If a developer has told extbase to use another object instead of \CIC\Cicregister\Domain\Model\FrontendUser, then we
		// want to make sure that the replacement object is validated instead of the default cicregister object. Whereas the
		// object manager does look at Extbase's objects Typoscript section, the argument validator does not.
		$frameworkConfiguration = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
		$frontendUserClass = $frameworkConfiguration['objects']['CIC\Cicregister\Domain\Model\FrontendUser']['className'];
		if ($frontendUserClass && $this->arguments->hasArgument('frontendUser')) {
			$this->arguments->getArgument('frontendUser')->setDataType($frontendUserClass);
			// perhaps there's a better way here, than to re-initialize all arguments
			$this->initializeActionMethodValidators();

		}
	}


}
