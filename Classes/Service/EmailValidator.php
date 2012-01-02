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

class Tx_Cicregister_Service_EmailValidator implements t3lib_Singleton {

	/**
	 * @var string
	 */
	private $salt = '';

	/**
	 * @var Tx_Cicregister_Domain_Repository_GlobalFrontendUserRepository
	 */
	protected $frontendUserRepository;

	/**
	 * inject the frontendUserRepository
	 *
	 * @param Tx_Cicregister_Domain_Repository_GlobalFrontendUserRepository frontendUserRepository
	 * @return void
	 */
	public function injectFrontendUserRepository(Tx_Cicregister_Domain_Repository_GlobalFrontendUserRepository $frontendUserRepository) {
		$this->frontendUserRepository = $frontendUserRepository;
	}

	public function __construct() {
		$this->salt = $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'].'|cicregister|';
	}

	/**
	 * @param $key
	 * @return bool
	 */
	public function validateKey($key) {
		$parts = explode('-',$key);
		$hash = $parts[0];
		$uid = $parts[1];
		$rand = $parts[2];
		if($uid) {
			$frontendUser = $this->frontendUserRepository->findByUid($uid);
		}
		if(($frontendUser instanceof Tx_Cicregister_Domain_Model_FrontendUser) && $rand) {
			$confirmKey = $this->generateKey($frontendUser,$rand);
			if($confirmKey == $key) return $frontendUser;
		} else {
			return false;
		}
		return false;
	}

	/**
	 * @param Tx_Cicregister_Domain_Model_FrontendUser $frontendUser
	 * @param null $rand
	 * @return string
	 */
	public function generateKey(Tx_Cicregister_Domain_Model_FrontendUser $frontendUser, $rand = NULL) {
		if(!$rand) {
			$rand = mt_rand();
		}
		$base = $this->salt . $frontendUser->getUid() . $rand . $frontendUser->getEmail();
		$hash = md5($base);
		$key = $hash . '-' . $frontendUser->getUid().'-'.$rand;
		return $key;
	}


}
