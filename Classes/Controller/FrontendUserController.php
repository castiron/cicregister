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

class Tx_Cicregister_Controller_FrontendUserController extends Tx_Cicregister_Controller_FrontendUserBaseController {

	/**
	 * Renders the "new user" form.
	 *
	 * @param Tx_Cicregister_Domain_Model_FrontendUser $frontendUser
	 * @return void
	 */
	public function newAction(Tx_Cicregister_Domain_Model_FrontendUser $frontendUser = NULL) {
		$user = $GLOBALS['TSFE']->fe_user;
		if ($user->user['uid']) {
			$this->forward('edit');
		} else {
			$this->view->assign('frontendUser', $frontendUser);
		}
	}

	/**
	 * @param Tx_Cicregister_Domain_Model_FrontendUser $frontendUser
	 */
	public function createAction(Tx_Cicregister_Domain_Model_FrontendUser $frontendUser) {

		// The user has already been validated by ExtBase. At this point, we're doing post-processing before creating
		// the user.
		$behaviorResponse = $this->createAndPersistUser($frontendUser);
		switch (get_class($behaviorResponse)) {
			case 'Tx_Cicregister_Behaviors_Response_RenderAction':
				$this->forward($behaviorResponse->getValue(), NULL, NULL, array('frontendUser' => $frontendUser));
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
	 * @param string $key
	 */
	public function validateUserAction($key) {
		$emailValidatorService = $this->objectManager->get('Tx_Cicregister_Service_HashValidator');
		$frontendUser = $emailValidatorService->validateKey($key);
		if($frontendUser instanceof Tx_Cicregister_Domain_Model_FrontendUser) {
			// Decorate the user
			$this->decoratorService->decorate($this->settings['decorators']['frontendUser']['emailValidated'], $frontendUser);
			$this->frontendUserRepository->update($frontendUser);
			$persistenceManager = $this->objectManager->get('Tx_Extbase_Persistence_Manager');
			$persistenceManager->persistAll();
			$this->flashMessageContainer->add('You have successfully validated your email address. Your account is now active.');
			$this->forward('edit');
		} else {
			$this->flashMessageContainer->add('The link you clicked was invalid. If you would like to register for a new account, please fill out the form below.');
			$this->forward('new');
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

	public function loginAction() {
	}

	/**
	 * Edit user action
	 *
	 * @param $frontendUser
	 * @return void
	 */
	public function editAction(Tx_Cicregister_Domain_Model_FrontendUser $frontendUser = NULL) {
		$user = $GLOBALS['TSFE']->fe_user;
		if(!$user->user['uid']) {
			$this->flashMessageContainer->add('You must be logged in to edit your account. Please login below.');
			$this->forward('login');
		} else {
			$frontendUser = $this->frontendUserRepository->findByUid($user->user['uid']);
			$this->view->assign('frontendUser', $frontendUser);
		}
		$this->signalSlotDispatcher->dispatch(__CLASS__, 'editAction', array('frontendUser' => $frontendUser, 'view' => $this->view));
	}

	/**
	 * Update action
	 *
	 * @param $frontendUser
	 * @return void
	 */
	public function updateAction(Tx_Cicregister_Domain_Model_FrontendUser $frontendUser) {
		$this->frontendUserRepository->update($frontendUser);
		$this->flashMessageContainer->add('Your profile has been updated.');
		$this->forward('edit');
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