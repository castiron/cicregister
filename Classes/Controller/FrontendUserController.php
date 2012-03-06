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

class Tx_Cicregister_Controller_FrontendUserController extends Tx_Cicregister_Controller_FrontendUserBaseController {

	/**
	 * Method renders the "new" view, which is, by default, the AJAX new form.
	 *
	 * @param Tx_Cicregister_Domain_Model_FrontendUser $frontendUser
	 * @return void
	 */
	public function newAction(Tx_Cicregister_Domain_Model_FrontendUser $frontendUser = NULL) {
		if($this->userIsAuthenticated) {
			$this->forward('edit');
		} else {
			$this->view->assign('frontendUser', $frontendUser);
		}
		// emit a signal before rendering the view
		$this->signalSlotDispatcher->dispatch(__CLASS__, 'newAction', array('frontendUser' => $frontendUser, 'view' => $this->view));
	}

	/**
	 * After the new form is submitted, the user is passed to this method so it can be created.
	 *
	 * @param Tx_Cicregister_Domain_Model_FrontendUser $frontendUser
	 * @param array $password
	 * @validate $password Tx_Cicregister_Validation_Validator_PasswordValidator
	 */
	public function createAction(Tx_Cicregister_Domain_Model_FrontendUser $frontendUser, array $password) {

		// The password is passed separately so that it can be easily validated using the PasswordValidator
		$frontendUser->setPassword($password[0]);

		// We have an inherited method for creating and persisting the user.
		$this->handleBehaviorResponse($this->createAndPersistUser($frontendUser));

	}

	/**
	 * @param string $key
	 */
	public function validateUserAction($key) {
		$emailValidatorService = $this->objectManager->get('Tx_Cicregister_Service_HashValidator');
		$frontendUser = $emailValidatorService->validateKey($key);
		// TODO: Handle an internal login redirect.
		$forward = 'new'; // logged in users will get forward from new to edit; otherwise, users will be asked to signup.
		if ($frontendUser instanceof Tx_Cicregister_Domain_Model_FrontendUser) {
			// Decorate and persist the user.
			$this->decorateUser($frontendUser, 'emailValidated');
			$this->frontendUserRepository->update($frontendUser);
			$this->persistenceManager->persistAll();
			$this->flashMessageContainer->add('You have successfully validated your email address. Thank you!.');
			$this->handleBehaviorResponse($this->doBehaviors($frontendUser, 'emailValidationSuccess', $forward), $frontenduser);
		} else {
			$this->handleBehaviorResponse($this->doBehaviors($frontendUser, 'emailValidationFailure', $forward), $frontenduser);
		}
	}

	/**
	 * @param Tx_Cicregister_Domain_Model_FrontendUser $frontendUser
	 */
	public function createConfirmationAction(Tx_Cicregister_Domain_Model_FrontendUser $frontendUser) {
		$this->view->assign('frontendUser', $frontendUser);
	}

	/**
	 * @param Tx_Cicregister_Domain_Model_FrontendUser $frontendUser
	 */
	public function createConfirmationMustValidateAction(Tx_Cicregister_Domain_Model_FrontendUser $frontendUser) {
		$this->view->assign('frontendUser',$frontendUser);
	}

	/**
	 * Edit user action
	 *
	 * @param $frontendUser
	 * @return void
	 */
	public function editAction(Tx_Cicregister_Domain_Model_FrontendUser $frontendUser = NULL) {
		if(!$this->userIsAuthenticated) {
			$this->forward('new');
		} else {
			$frontendUser = $this->frontendUserRepository->findByUid($this->userData['uid']);
			$this->view->assign('frontendUser', $frontendUser);
			// emit a signal before rendering the view
			$this->signalSlotDispatcher->dispatch(__CLASS__, 'editAction', array('frontendUser' => $frontendUser, 'view' => $this->view));
		}
	}

	/**
	 * @param Tx_Cicregister_Domain_Model_FrontendUser $frontendUser
	 * @param array $otherData
	 * @param array $password
	 * @validate $password Tx_Cicregister_Validation_Validator_PasswordValidator(allowEmpty = true)
	 */
	public function updateAction(Tx_Cicregister_Domain_Model_FrontendUser $frontendUser, $otherData = array(), array $password = NULL) {

		if ($password != NULL && is_array($password) && $password[0] != false) {
			$frontendUser->setPassword($password[0]);
		}

		$this->decorateUser($frontendUser, 'updated');

		// emit a signal prior to saving the user.
		$this->signalSlotDispatcher->dispatch(__CLASS__, 'updateAction', array('frontendUser' => $frontendUser, $otherData));

		$this->frontendUserRepository->update($frontendUser);
		$this->flashMessageContainer->add('Your profile has been updated.');
		$this->handleBehaviorResponse($this->doBehaviors($frontendUser, 'updated', 'edit'), $frontenduser);
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
		if ($msg == false) {
			$msg = 'no error message set for ' . $this->actionMethodName;
		}
		return $msg;
	}
}

?>