<?php
namespace CIC\Cicregister\Service;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Sv\AuthenticationService;

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
class Authentication extends AuthenticationService {

	/**
	 * Only try to authenticate the user if a login has was returned in the URL.
	 *
	 * @return	boolean		TRUE if service is available
	 */
	public function init() {
		$available = FALSE;
		$key = GeneralUtility::_GP('loginHash');
		if($key) $available = TRUE;
		return $key;
	}

	/**
	 * Find a user (eg. look up the user record in database when a login is sent)
	 *
	 * @return	mixed		user array or FALSE
	 */
	function getUser() {

		$key = GeneralUtility::_GP('loginHash');

		$validator = GeneralUtility::makeInstance(HashValidator::class);
		$uid = $validator->validateShortLivedKey($key);
		if ($uid) {
			$this->cicregisterHashLoginUid = $uid;
			$select = '*';
			$table = 'fe_users';
			$where = 'uid=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($uid, $table);
			$limit = '1';
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where, FALSE, FALSE, $limit);
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			$this->login['uident'] = $row['username'];
			$this->login['uname'] = $row['username'];
		}

		$user = FALSE;

		if ($this->login['status'] == 'login' && $this->login['uident']) {

			$user = $this->fetchUserRecord($this->login['uname']);
			if (!is_array($user)) {

				// Failed login attempt (no username found)
				$this->writelog(255, 3, 3, 2,
					"Login-attempt from %s (%s), username '%s' not found!!",
					Array($this->authInfo['REMOTE_ADDR'], $this->authInfo['REMOTE_HOST'], $this->login['uname'])); // Logout written to log
				GeneralUtility::sysLog(
					sprintf("Login-attempt from %s (%s), username '%s' not found!", $this->authInfo['REMOTE_ADDR'], $this->authInfo['REMOTE_HOST'], $this->login['uname']),
					'Core',
					0
				);
			} else {
				if ($this->writeDevLog) GeneralUtility::devLog('User found: ' . GeneralUtility::arrayToLogString($user, array($this->db_user['userid_column'], $this->db_user['username_column'])), 'tx_sv_auth');
			}
		}

		return $user;
	}

	/**
	 * Authenticate a user (Check various conditions for the user that might invalidate its authentication, eg. password match, domain, IP, etc.)
	 *
	 * @param	array		Data of user.
	 * @return	boolean
	 */
	public function authUser(array $user): int {
		$OK = 100;

		if ($this->cicregisterHashLoginUid == true && $user['uid'] == $this->cicregisterHashLoginUid) {
			$skipCompareUident = TRUE;
			$this->login['uident'] = $user['username'];
			$this->login['uname'] = $user['username'];
			$OK = 200;
		}

		if ($this->login['uident'] && $this->login['uname']) {

			// Checking password match for user:

			if ($skipCompareUident == FALSE) {
				$OK = $this->compareUident($user, $this->login);
			}

			if (!$OK) {
				// Failed login attempt (wrong password) - write that to the log!
				if ($this->writeAttemptLog) {
					$this->writelog(255, 3, 3, 1,
						"Login-attempt from %s (%s), username '%s', password not accepted!",
						Array($this->authInfo['REMOTE_ADDR'], $this->authInfo['REMOTE_HOST'], $this->login['uname']));
					GeneralUtility::sysLog(
						sprintf("Login-attempt from %s (%s), username '%s', password not accepted!", $this->authInfo['REMOTE_ADDR'], $this->authInfo['REMOTE_HOST'], $this->login['uname']),
						'Core',
						0
					);
				}
				if ($this->writeDevLog) GeneralUtility::devLog('Password not accepted: ' . $this->login['uident'], 'tx_sv_auth', 2);
			}

			// Checking the domain (lockToDomain)
			if ($OK && $user['lockToDomain'] && $user['lockToDomain'] != $this->authInfo['HTTP_HOST']) {
				// Lock domain didn't match, so error:
				if ($this->writeAttemptLog) {
					$this->writelog(255, 3, 3, 1,
						"Login-attempt from %s (%s), username '%s', locked domain '%s' did not match '%s'!",
						Array($this->authInfo['REMOTE_ADDR'], $this->authInfo['REMOTE_HOST'], $user[$this->db_user['username_column']], $user['lockToDomain'], $this->authInfo['HTTP_HOST']));
					GeneralUtility::sysLog(
						sprintf("Login-attempt from %s (%s), username '%s', locked domain '%s' did not match '%s'!", $this->authInfo['REMOTE_ADDR'], $this->authInfo['REMOTE_HOST'], $user[$this->db_user['username_column']], $user['lockToDomain'], $this->authInfo['HTTP_HOST']),
						'Core',
						0
					);
				}
				$OK = -1;
			}
		}

		return $OK;
	}

}
