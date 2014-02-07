<?php
namespace CIC\Cicregister\Controller;
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

class FrontendUserController extends FrontendUserBaseController {

	protected $actionSuccess = false;

	/**
	 * Method renders the "new" view, which is, by default, the AJAX new form.
	 *
	 * @param \CIC\Cicregister\Domain\Model\FrontendUser $frontendUser
	 * @dontvalidate $frontendUser
	 * @return void
	 */
	public function newAction(\CIC\Cicregister\Domain\Model\FrontendUser $frontendUser = NULL) {
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
	 * @param \CIC\Cicregister\Domain\Model\FrontendUser $frontendUser
	 * @param array $password
	 * @validate $password \CIC\Cicregister\Validation\Validator\PasswordValidator
	 */
	public function createAction(\CIC\Cicregister\Domain\Model\FrontendUser $frontendUser, array $password) {

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
		$emailValidatorService = $this->objectManager->get('CIC\\Cicregister\\Service\\HashValidator');
		$frontendUser = $emailValidatorService->validateKey($key);
		$forward = 'new'; // logged in users will get forward from new to edit; otherwise, users will be asked to signup.
		if ($frontendUser instanceof \CIC\Cicregister\Domain\Model\FrontendUser) {
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
	 * @param \CIC\Cicregister\Domain\Model\FrontendUser $frontendUser
	 * @param string $redirect
	 */
	public function sendValidationEmailAction(\CIC\Cicregister\Domain\Model\FrontendUser $frontendUser, $redirect = '') {
		$extraConf = array();
		if($redirect) {
			$extraConf = array('variables' => array('redirect' => $redirect));
		}
		$ignoreResponse = $this->doBehaviors($frontendUser, 'validationEmailSend', '', $extraConf);
		$this->flashMessageContainer->add('An email has been sent to '.$frontendUser->getEmail().' for validation.');
	}


	/**
	 * @param \CIC\Cicregister\Domain\Model\FrontendUser $frontendUser
	 */
	public function createConfirmationAction(\CIC\Cicregister\Domain\Model\FrontendUser $frontendUser) {
		$this->view->assign('frontendUser', $frontendUser);
	}

	/**
	 * @param \CIC\Cicregister\Domain\Model\FrontendUser $frontendUser
	 */
	public function createConfirmationMustValidateAction(\CIC\Cicregister\Domain\Model\FrontendUser $frontendUser) {
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
	 * @param \CIC\Cicregister\Domain\Model\FrontendUser $frontendUser
	 * @param array $otherData
	 * @param array $password
	 * @validate $password \CIC\Cicregister\Validation\Validator\PasswordValidator(allowEmpty = true)
	 */
	public function updateAction(\CIC\Cicregister\Domain\Model\FrontendUser $frontendUser, $otherData = array(), array $password = NULL) {

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
		if(!$enrollmentCode) {
			$msg = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('flash-frontendUserController-' . $this->actionMethodName . '-enterCode','cicregister');
			$this->flashMessageContainer->add($msg,'',\TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
		} else {
			$group = $this->frontendUserGroupRepository->findOneByEnrollmentCode($enrollmentCode);
			if($group instanceof \CIC\Cicregister\Domain\Model\FrontendUserGroup) {
				if($this->userIsAuthenticated) {
					$frontendUser = $this->frontendUserRepository->findByUid($this->userData['uid']);
					$frontendUser->addUserGroup($group);
					$msg = sprintf(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('flash-frontendUserController-' . $this->actionMethodName . '-success', 'cicregister'), $group->getTitle());
					$this->flashMessageContainer->add($msg,'');
					$this->view->assign('success',true);
					$this->frontendUserRepository->update($frontendUser);
					$ignoreResponse = $this->doBehaviors($frontendUser, 'enrolled', '', array('enrolledGroup' => $group));
				} else {
					$msg = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('flash-frontendUserController' . $this-actionMethodName . '-noLogin','cicregister');
					$this->flashMessageContainer->add($msg,'',\TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
				}
			} else {
				$msg = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('flash-frontendUserController-' . $this->actionMethodName . '-invalidCode','cicregister');
				$this->flashMessageContainer->add($msg,'',\TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
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
				$msg = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('flash-frontendUserController-' . $this->actionMethodName . '-default', 'cicregister');
				break;
		}
		if ($msg == false) {
			$msg = 'There was an error calling ' . $this->actionMethodName;
		}
		return $msg;
	}
}

?>