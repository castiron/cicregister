<?php
namespace CIC\Cicregister\Controller;
/***************************************************************
 *  Copyright notice
 *  (c) 2011 Zachary Davis <zach
 *
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

class LoginController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * @var array
	 */
	protected $userData = array();

	/**
	 * @var bool
	 */
	protected $userIsAuthenticated = false;

	/**
	 * @var \CIC\Cicregister\Service\UrlValidator
	 */
	protected $urlValidator;

	/**
	 * @var \CIC\Cicregister\Domain\Repository\FrontendUserRepository
	 */
	protected $frontendUserRepository;

	/**
	 * @var \CIC\Cicregister\Service\Behavior
	 */
	protected $behaviorService;

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
	 * inject the frontendUserRepository
	 *
	 * @param \CIC\Cicregister\Domain\Repository\FrontendUserRepository frontendUserRepository
	 * @return void
	 */
	public function injectFrontendUserRepository(\CIC\Cicregister\Domain\Repository\FrontendUserRepository $frontendUserRepository) {
		$this->frontendUserRepository = $frontendUserRepository;
	}

	/**
	 * inject the urlValidator
	 *
	 * @param \CIC\Cicregister\Service\UrlValidator urlValidator
	 * @return void
	 */
	public function injectUrlValidator(\CIC\Cicregister\Service\UrlValidator $urlValidator) {
		$this->urlValidator = $urlValidator;
	}

	/**
	 * Initialize the controller
	 */
	public function initializeAction() {
		$this->userData = $GLOBALS['TSFE']->fe_user->user;
		if (isset($GLOBALS['TSFE']) && $GLOBALS['TSFE']->loginUser) {
			$this->userIsAuthenticated = true;
		}
	}

	/**
	 * The default entry point. The login form also posts to the dispatch method
	 * so that it can decide what to display after login.
	 *
	 * @param boolean $loginAttempt
	 * @param string $loginType
	 */
	public function dispatchAction($loginAttempt = false, $loginType = '') {
		$loginHash = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('loginHash');
		if($this->userIsAuthenticated) {
			if($loginAttempt || $loginHash) {
				$redirectUrl = $this->getLoginRedirectUrl();
				if($redirectUrl) {
					$this->doRedirect($redirectUrl);
				} else {
					$this->forward('logout');
				}
			} else {
				$this->forward('logout');
			}
		} else {
			$this->forward('login');
		}
	}

	/**
	 * Returns the appropriate login URL for the logged in user, first by checking the
	 * returnUrl arg, then by checking the user / usergroup, then checking the configuration
	 * @return null|string
	 */
	protected function getLoginRedirectUrl() {
		$redirectUrl = null;
		$redirectPageUid = null;
		$foundRedirectTarget = false;

		// First look for redirect specified in GP vars
		if($this->settings['login']['honorRedirectUrlArgument']) {
			$redirectUrl = $this->getValidReturnUrl();
			if($redirectUrl) $foundRedirectTarget = true;
		}

		// If there isn't one, look at the user for a redirect
		if(!$foundRedirectTarget) {
			$user = $this->frontendUserRepository->findOneByUid($this->userData['uid']);
			if($user && $user->getRedirectPid()) {
				$redirectPageUid = $user->getRedirectPid();
				$foundRedirectTarget = true;
			}
		}

		// If there still isn't one, look at the groups for a redirect, but do so in the correct
		// order of priority
		if(!$foundRedirectTarget) {
			if($usergroups = $user->getUsergroupsWithRedirect()) {
				$redirectPageUid = $this->getUsergroupRedirectByPriority(
					$usergroups,
					$this->settings['login']['usergroupRedirectPriority']
				);
				if($redirectPageUid) $foundRedirectTarget = true;
			}
		}

		// Otherwise, go to the shared post login redirect page
		if(!$foundRedirectTarget && $this->settings['login']['postLoginRedirectPid']) {
			$redirectPageUid = $this->settings['login']['postLoginRedirectPid'];
			if($redirectPageUid) $foundRedirectTarget = true;
		}

		// If we don't have a URL, but do have a pid, make a URL of it!
		if($foundRedirectTarget && !$redirectUrl && $redirectPageUid) {
			$uriBuilder = $this->controllerContext->getUriBuilder();
			$redirectUrl = $uriBuilder
					->reset()
					->setTargetPageUid($redirectPageUid)
					->build();
		}
		return $redirectUrl;
	}

	/**
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $usergroups
	 * @param array $usergroupRedirectPriority
	 * @return null
	 */
	protected function getUsergroupRedirectByPriority(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $usergroups,$usergroupRedirectPriority = array()) {
		// Make an array of group uids
		$usergroupUidsArray = array();
		foreach($usergroups as $usergroup) {
 			$usergroupUidsArray[] = $usergroup->getUid();
		}

		// If there's no priority ordering specified, just go with the order we have them in
		if(!count($usergroupRedirectPriority)) {
			$usergroupRedirectPriority = array();
			foreach($usergroups as $usergroup) {
				$usergroupRedirectPriority[] = $usergroup->getUid();
			}
		}

		$foundUid = null;
		foreach($usergroupRedirectPriority as $priorityRow) {
			$priorityRowUsergroupIdsArray = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',',$priorityRow);
			foreach($priorityRowUsergroupIdsArray as $usergroupUid) {
				if(in_array($usergroupUid,$usergroupUidsArray)) {
					$foundUid = $usergroupUid;
					break 2;
				}
			}
		}

		if($foundUid) {
			foreach($usergroups as $usergroup) {
				if($usergroup->getUid() == $foundUid) {
					return $usergroup->getRedirectPid();
				}
			}
		}
		return null;
	}

	/**
	 * Show the logout view
	 */
	protected function logoutAction() {
		$this->view->assign('editPid', $this->settings);

		// A fella's gotta be logged in before he can logout.
		if(!$this->userIsAuthenticated) $this->forward('login');

		$postParams['loginType'] = 'logout';
		$postParams['storagePid'] = $this->determineStoragePid();
		$this->view->assign('editPageUid',$this->settings['pids']['editView']);
		$this->view->assign('postParams', $postParams);
		$this->view->assign('loginViewPid',$this->settings['pids']['loginView']);
		$this->view->assign('userData',$this->userData);
	}

	/**
	 * Looks for felogin's frontend login hook so that this login mechanism can
	 * be compatible with the RSAAuth extension and other extensions that attempt
	 * to extends felogin.
	 *
	 * @return array
	 */
	protected function handleRSAAuthHook() {
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['felogin']['loginFormOnSubmitFuncs'])) {
			$_params = array();
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['felogin']['loginFormOnSubmitFuncs'] as $funcRef) {
				list($onSub, $hid) = \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($funcRef, $_params, $this);
				$res = array(
					'onSubmit' => $onSub,
					'scriptInclude' => $hid
				);
			}
		}
		return $res;
	}

	/**
	 * Validates the return URL using the urlValidator service. The URL Validator is taken almost verbatim from
	 * felogin under the assumption that felogin redirect url validation is tested, stable code, that should be
	 * reused
	 *
	 * @return string
	 */
	public function getValidReturnUrl() {
		$returnUrl = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('return_url');
		$out = $this->urlValidator->validateReturnUrl($returnUrl);
		return $out;
	}

	/**
	 * Displays the form asking the user to enter his/her email address for password retrieval
	 *
	 * @param bool $requestProcessed
	 * @param bool $requestSuccessful
	 */
	public function forgotPasswordAction($requestProcessed = FALSE, $requestSuccessful = FALSE) {
		// handle flash messages
		$messages = $this->flashMessageContainer->getAllMessages();
		if(count($messages) > 0) {
			$this->view->assign('hasMessages',true);
			$this->view->assign('messages',$messages);
			$this->flashMessageContainer->flush();
		}

		if($requestProcessed === TRUE) {
			$this->view->assign('requestProcessed', true);
			if ($requestSuccessful === TRUE) {
				$this->view->assign('emailSent', true);
			}
		} else {
			$this->view->assign('requestProcessed', false);
		}

	}

	/**
	 * Shows the reset password form. The key is passed via the link in the email sent to the user and is
	 * validated using cicregister's hash validator service.
	 *
	 * @param string $key
	 */
	public function resetPasswordAction($key) {
		$hashValidatorService = $this->objectManager->get('CIC\\Cicregister\\Service\\HashValidator');
		$frontendUser = $hashValidatorService->validateKey($key);

		if($frontendUser) {
			// the controller adds errors when there is a validation error; we're not going to display them,
			// so we just flush them instead.
			$this->flashMessageContainer->flush();
			$this->view->assign('key',$key);
		} else {
			$this->forward('invalidResetRequest');
		}
	}

	/**
	 * Processes the password posted from the reset password form. Note that the same validation is used here
	 * that is used in the frontend user controller for user registration.
	 *
	 * @param string $key
	 * @param array $password
	 * @validate $password \CIC\Cicregister\Validation\Validator\PasswordValidator
	 */
	public function handleResetPasswordAction($key, array $password) {
		$hashValidatorService = $this->objectManager->get('CIC\\Cicregister\\Service\\HashValidator');
		$frontendUser = $hashValidatorService->validateKey($key);

		// we validate the hash again, just be safe.
		if(!$frontendUser) $this->forward('invalidResetRequestAction');

		if ($password != NULL && is_array($password) && $password[0] != false) {
			$frontendUser->setPassword($password[0]);
			$this->frontendUserRepository->update($frontendUser);
		}
		$this->flashMessageContainer->add(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('controller-login-pwChanged', 'cicregister'));
		$this->forward('login');
	}

	/**
	 * Insert a flash message in cases where the key was invalid
	 */
	public function invalidResetRequestAction() {
		$this->flashMessageContainer->add(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('controller-login-invalidHash', 'cicregister'));
		$this->forward('forgotPassword');
	}

	/**
	 * Accepts the forget password form submission and executes behaviors tied to the forgot password
	 * event.
	 *
	 * @param string $emailAddress
	 * @validate $emailAddress notEmpty
	 * @validate $emailAddress emailAddress
	 */
	public function handleForgotPasswordAction($emailAddress) {

		// If the extension is configured to not allow forgot password, this action is not allowed.
		if(!$this->settings['login']['allowForgotPassword']) {
			$GLOBALS['TSFE']->pageNotFoundAndExit();
		} else {
			// forgot password is allowed...
			$user = $this->frontendUserRepository->findOneByEmail($emailAddress);
			if (is_object($user) && $user->getUid()) {
				$behaviorsConf = $this->settings['behaviors']['login']['forgotPassword'];
				$this->behaviorService->executeBehaviors($behaviorsConf, $user, $this->controllerContext, 'forgotPassword');
				$this->forward('forgotPassword', NULL, NULL, array('requestProcessed' => true, 'requestSuccessful' => true));
			} else {
				// The developer can decide whether she wants to show feedback in the view when the request was not successful.
				$this->forward('forgotPassword', NULL, NULL, array('requestProcessed' => true, 'requestSuccessful' => false));
			}
		}
	}

	/**
	 * Handle default flash messages
	 *
	 * @return string
	 */
	protected function getErrorFlashMessage() {
		$msg = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('controller-login-genericActionMessage-'. $this->actionMethodName, 'cicregister');
		if(!$msg) {
			return parent::getErrorFlashMessage();
		}
		return $msg;
	}


	/**
	 * Ideally, developers should have to do as little as possible to make the login mechanisms work. We'll look at
	 * what kind of object cicregister is responsible for, and determine its storage pid from typoscript conf.
	 * TODO: Allow developers to override this value with a flexform storage pid.
	 *
	 * @return integer
	 */
	protected function determineStoragePid() {
		$frameworkConfiguration = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
		$out = $frameworkConfiguration['persistence']['storagePid'];
		return $out;
	}

	/**
	 * @param $redirectUrl
	 */
	protected function doRedirect($redirectUrl) {
		if($redirectUrl) {
			$this->redirectToUri($redirectUrl);
		}
	}

	/**
	 * Displays the login form to the user
	 *
	 * @param boolean $loginAttempt
	 * @param string $loginType
	 */
	public function loginAction($loginAttempt = false, $loginType = '') {
		$returnUrl = $this->getValidReturnUrl();
		$hookResults = $this->handleRSAAuthHook();
		$postParams = array();
		$postParams['returnUrl'] = $returnUrl;
		$postParams['loginType'] = 'login';
		$postParams['storagePid'] = $this->determineStoragePid();

		// Considered using flash messages here. However, it's often useful to have full
		// fluid/html power in the message, and that's tricky with flash messages. Eg, after
		// a user signs up, they should get a link to edit profile.
		$loginFailed = false;
		$loginSuccess = false;
		$logoutOccurred = false;
		$loginNotAttempted = false;
		if($loginType == 'logout' && !$this->userIsAuthenticated) {
			$logoutOccurred = true;
		} elseif($loginAttempt && !$this->userIsAuthenticated) {
			$loginFailed = true;
		} elseif($loginAttempt && $this->userIsAuthenticated) {
			$loginSuccess = true;
		} elseif(!$loginAttempt) {
			$loginNotAttempted = true;
		}

		// insert a return message from typoscript, if requested.
		$returnMsgKey = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('return_msg');
		if(array_key_exists($returnMsgKey,$this->settings['login']['returnMessages'])) {
			$this->view->assign('returnMessage',$this->settings['login']['returnMessages'][$returnMsgKey]);
		}

		// assign various view variables
		$this->view->assign('loginSettings',$this->settings['login']);
		$this->view->assign('loginFailed', $loginFailed);
		$this->view->assign('logoutOccurred', $logoutOccurred);
		$this->view->assign('loginSuccess', $loginSuccess);
		$this->view->assign('loginNotAttempted', $loginNotAttempted);
		$this->view->assign('hookOnSubmit', $hookResults['onSubmit']);
		$this->view->assign('hookScriptInclude', $hookResults['scriptInclude']);
		$this->view->assign('postParams',$postParams);
	}
}

?>
