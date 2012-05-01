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

class Tx_Cicregister_Behaviors_AuthenticateUser extends Tx_Cicregister_Behaviors_AbstractBehavior implements Tx_Cicregister_Behaviors_BehaviorInterface {

	/**
	 * @var Tx_Extbase_Object_Manager
	 */
	protected $objectManager;

	/**
	 * inject the objectManager
	 *
	 * @param Tx_Extbase_Object_Manager objectManager
	 * @return void
	 */
	public function injectObjectManager(Tx_Extbase_Object_Manager $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * @param Tx_Cicregister_Domain_Model_FrontendUser $frontendUser
	 * @param array $conf
	 * @return string
	 */
	public function execute(Tx_Cicregister_Domain_Model_FrontendUser $frontendUser, array $conf) {

		// This method generates a login hash, which gets validated in the authentication service.
		// The login hash is part of a query string that the user is redirected to.
		$hashValidator = $this->objectManager->get('Tx_Cicregister_Service_HashValidator');
		$loginHash = $hashValidator->generateShortLivedKey($frontendUser->getUid());

		$uriBuilder = $this->controllerContext->getUriBuilder();

		$returnUrl = t3lib_div::_GP('return_url');

		$uri = $uriBuilder
				->reset()
				->setTargetPageUid($conf['forwardPid'])
				->setLinkAccessRestrictedPages(true)
				->setNoCache(false)
				->setUseCacheHash(false)
				->setArguments(array(
					'return_url' => $returnUrl,
					'logintype' => 'login',
					'pid' => $conf['feuserPid'],
					'loginHash' => $loginHash
				))
				->uriFor($conf['forwardAction'], NULL, 'FrontendUser');
		$response = $this->objectManager->create('Tx_Cicregister_Behaviors_Response_RedirectURI');
		$response->setValue($uri);
		return $response;

	}

}
