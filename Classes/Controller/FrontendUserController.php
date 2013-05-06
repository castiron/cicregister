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

	protected $actionSuccess = false;

	/**
	 * Method renders the "new" view, which is, by default, the AJAX new form.
	 *
	 * @param Tx_Cicregister_Domain_Model_FrontendUser $frontendUser
	 * @dontvalidate $frontendUser
	 * @return void
	 */
	public function newAction(Tx_Cicregister_Domain_Model_FrontendUser $frontendUser = NULL) {
		if($this->userIsAuthenticated) {
			$this->forward('edit');
		} else {
			$this->view->assign('viewSettings',$this->settings['views']['new']);
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
		$this->handleBehaviorResponse($this->createAndPersistUser($frontendUser), $frontendUser);

	}

	/**
	 * @param string $key
	 * @param string $redirect
	 */
	public function validateUserAction($key, $redirect = '') {
		$emailValidatorService = $this->objectManager->get('Tx_Cicregister_Service_HashValidator');
		$frontendUser = $emailValidatorService->validateKey($key);
		$forward = 'new'; // logged in users will get forward from new to edit; otherwise, users will be asked to signup.
		if ($frontendUser instanceof Tx_Cicregister_Domain_Model_FrontendUser) {
			// Decorate and persist the user.
			$this->decorateUser($frontendUser, 'emailValidated');
			$this->frontendUserRepository->update($frontendUser);
			$this->persistenceManager->persistAll();
			$this->flashMessageContainer->add('You have successfully validated your email address. Thank you!.');
			if($redirect) {
				$this->doBehaviors($frontendUser, 'emailValidationSuccess', '');
				$this->redirectToUri($redirect);
			} else {
				$this->handleBehaviorResponse($this->doBehaviors($frontendUser, 'emailValidationSuccess', $forward), $frontendUser);
			}
		} else {
			$this->handleBehaviorResponse($this->doBehaviors($frontendUser, 'emailValidationFailure', $forward), $frontendUser);
		}
	}

	/**
	 * This action sends a validation email to $user which contains a link
	 * to the validateUser action.
	 *
	 * @param Tx_Cicregister_Domain_Model_FrontendUser $frontendUser
	 * @param string $redirect
	 */
	public function sendValidationEmailAction(Tx_Cicregister_Domain_Model_FrontendUser $frontendUser, $redirect = '') {
		$extraConf = array();
		if($redirect) {
			$extraConf = array('variables' => array('redirect' => $redirect));
		}
		$ignoreResponse = $this->doBehaviors($frontendUser, 'validationEmailSend', '', $extraConf);
		$this->flashMessageContainer->add('An email has been sent to '.$frontendUser->getEmail().' for validation.');
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
	 * @return void
	 */
	public function editAction() {
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
		$this->handleBehaviorResponse($this->doBehaviors($frontendUser, 'updated', 'edit'), $frontendUser);
	}

	/**
	 * Show a form for a user to submit an enrollment code
	 */
	public function enrollAction() {
		if(!$this->userIsAuthenticated) {
			$this->forward('new');
		}
		$this->view->assign('actionSuccess',$this->actionSuccess);
	}

	/**
	 * @param string $enrollmentCode
	 */
	public function saveEnrollmentAction($enrollmentCode = NULL) {
		// TODO: Abstract these labels into locallang.
		if(!$enrollmentCode) {
			$this->flashMessageContainer->add('Please enter an enrollment code.','',t3lib_FlashMessage::ERROR);
		} else {
			$group = $this->frontendUserGroupRepository->findOneByEnrollmentCode($enrollmentCode);
			if($group instanceof Tx_Cicregister_Domain_Model_FrontendUserGroup) {
				if($this->userIsAuthenticated) {
					$frontendUser = $this->frontendUserRepository->findByUid($this->userData['uid']);
					$frontendUser->addUserGroup($group);
					$this->flashMessageContainer->add('Your account has been successfully added to the "'.htmlspecialchars($group->getTitle()).'" group.');
					$this->view->assign('success',true);
				} else {
					$this->flashMessageContainer->add('Please log into the site before entering an enrollment code.','',t3lib_FlashMessage::ERROR);
				}
			} else {
				$this->flashMessageContainer->add('The group enrollment code that you entered was invalid. Please check your code and try again.','',t3lib_FlashMessage::ERROR);
			}
		}
	}

	/**
	 * Renders a button and a lightboxed signup form.
	 */
	public function buttonAction() {
		$this->view->assign('viewSettings',$this->settings['views']['new']);
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
			$msg = 'There was an error calling ' . $this->actionMethodName;
		}
		return $msg;
	}
}

?>