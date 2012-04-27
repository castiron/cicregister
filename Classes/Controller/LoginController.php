<?php

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

class Tx_Cicregister_Controller_LoginController extends Tx_Extbase_MVC_Controller_ActionController {

	/**
	 * @var array
	 */
	protected $userData = array();

	/**
	 * @var bool
	 */
	protected $userIsAuthenticated = false;

	/**
	 * @var Tx_Cicregister_Service_UrlValidator
	 */
	protected $urlValidator;

	/**
	 * @var Tx_Cicregister_Domain_Repository_FrontendUserRepository
	 */
	protected $frontendUserRepository;

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
	 * inject the frontendUserRepository
	 *
	 * @param Tx_Cicregister_Domain_Repository_FrontendUserRepository frontendUserRepository
	 * @return void
	 */
	public function injectFrontendUserRepository(Tx_Cicregister_Domain_Repository_FrontendUserRepository $frontendUserRepository) {
		$this->frontendUserRepository = $frontendUserRepository;
	}

	/**
	 * inject the urlValidator
	 *
	 * @param Tx_Cicregister_Service_UrlValidator urlValidator
	 * @return void
	 */
	public function injectUrlValidator(Tx_Cicregister_Service_UrlValidator $urlValidator) {
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
	 * The main point of entry for the controller
	 */
	public function dispatchAction() {
		if($this->userIsAuthenticated) {
			$this->forward('logout');
		} else {
			$this->forward('login');
		}
	}

	/**
	 * Show the logout view
	 */
	public function logoutAction() {
		t3lib_utility_Debug::debug($this->settings,__FILE__ . " " . __LINE__);
		$this->view->assign('editPid', $this->settings);

		// A fella's gotta be logged in before he can logout.
		if(!$this->userIsAuthenticated) $this->forward('login');

		$postParams['loginType'] = 'logout';
		$postParams['storagePid'] = $this->determineStoragePid();

		$this->view->assign('postParams', $postParams);
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
				list($onSub, $hid) = t3lib_div::callUserFunction($funcRef, $_params, $this);
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
	 * @param $redirectUrl
	 * @return string
	 */
	public function getValidRedirectUrl($redirectUrl) {
		$redirectUrl = t3lib_div::_GP('redirect_url');
		$referer = $this->urlValidator->validateRedirectUrl(t3lib_div::_GP('referer'));
		$redirectUrl = t3lib_div::_GP('redirect_url');
		$out = $this->urlValidator->validateRedirectUrl($redirectUrl);
		return $out;
	}

	/**
	 * Displays the form asking the user to enter his/her email address for password retrieval
	 *
	 * @param bool $requestProcessed
	 */
	public function forgotPasswordAction($requestProcessed = false) {
		$this->view->assign('requestProcessed',$requestProcessed);
	}

	/**
	 * Shows the reset password form. The key is passed via the link in the email sent to the user and is
	 * validated using cicregister's hash validator service.
	 *
	 * @param string $key
	 */
	public function resetPasswordAction($key) {
		$hashValidatorService = $this->objectManager->get('Tx_Cicregister_Service_HashValidator');
		$frontendUser = $hashValidatorService->validateKey($key);

		if($frontendUser) {
			// the controller adds errors when there is a validation error; we're not going to display them,
			// so we just flush them instead.
			$this->flashMessageContainer->flush();
			$this->view->assign('key',$key);
		} else {
			$this->forward('invalidResetRequestAction');
		}
	}

	/**
	 * Processes the password posted from the reset password form. Note that the same validation is used here
	 * that is used in the frontend user controller for user registration.
	 *
	 * @param string $key
	 * @param array $password
	 * @validate $password Tx_Cicregister_Validation_Validator_PasswordValidator
	 */
	public function handleResetPasswordAction($key, array $password) {
		$hashValidatorService = $this->objectManager->get('Tx_Cicregister_Service_HashValidator');
		$frontendUser = $hashValidatorService->validateKey($key);

		// we validate the hash again, just be safe.
		if(!$frontendUser) $this->forward('invalidResetRequestAction');

		if ($password != NULL && is_array($password) && $password[0] != false) {
			$frontendUser->setPassword($password[0]);
		}
		$this->flashMessageContainer->add('Your password has been changed. Please login below.');
		$this->forward('login');
	}

	/**
	 * Insert a flash message in cases where the key was invalid
	 */
	public function invalidResetRequestAction() {
		//TODO: Implement this view
	}

	/**
	 * @param string $emailAddress
	 */
	public function handleForgotPasswordAction($emailAddress) {
		$user = $this->frontendUserRepository->findOneByEmail($emailAddress);
		if(is_object($user) && $user->getUid()) {
			$behaviorsConf = $this->settings['behaviors']['login']['forgotPassword'];
			$this->behaviorService->executeBehaviors($behaviorsConf, $user, $this->controllerContext, 'forgotPassword');
		}
		$this->forward('forgotPassword',NULL,NULL,array('requestProcessed' => true));
	}

	/**
	 * Ideally, developers should have to do as little as possible to make the login mechanisms work. We'll look at
	 * what kind of object cicregister is responsible for, and determine its storage pid from typoscript conf.
	 * TODO: Allow developers to override this value with a flexform storage pid.
	 *
	 * @return integer
	 */
	protected function determineStoragePid() {
		$frameworkConfiguration = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
		$out = $frameworkConfiguration['persistence']['storagePid'];
		return $out;
	}

	/**
	 * Displays the login form to the user
	 *
	 * @param boolean $loginAttempt
	 * @param string $loginType
	 */
	public function loginAction($loginAttempt = false, $loginType = '') {
		
		// TODO: Handle post-login redirect
		#$redirectUrl = $this->getValidRedirectUrl();
		$hookResults = $this->handleRSAAuthHook();

		$postParams = array();
		$postParams['redirectUrl'] = $redirectUrl;
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