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
 *
 * @package cicregister
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 *
 */
class Tx_Cicregister_Domain_Model_FrontendUser extends Tx_Extbase_Domain_Model_FrontendUser {

	/**
	 * @var string
	 * @validate String
	 * @validate NotEmpty
	 */
	protected $username;

	/**
	 * @var string
	 * @validate NotEmpty
	 */
	protected $password;

	/**
	 * @var string
	 * @validate NotEmpty
	 */
	protected $confirmPassword;

	/**
	 * @var Tx_Extbase_Persistence_ObjectStorage<Tx_Extbase_Domain_Model_FrontendUserGroup>
	 */
	protected $usergroup;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 * @validate NotEmpty
	 */
	protected $firstName;

	/**
	 * @var string
	 */
	protected $middleName;

	/**
	 * @var string
	 * @validate NotEmpty
	 */
	protected $lastName;

	/**
	 * @var string
	 */
	protected $address = '';

	/**
	 * @var string
	 */
	protected $telephone = '';

	/**
	 * @var string
	 */
	protected $fax = '';

	/**
	 * @var string
	 * @validate NotEmpty
	 * @validate EmailAddress
	 * @validate StringLength(minimum = 3,maximum = 50)
	 * @validate Tx_Cicregister_Validation_Validator_UniqueValidator(repository = Tx_Extbase_Domain_Repository_FrontendUserRepository, property = email)
	 */
	protected $email = '';

	/**
	 * @var string
	 */
	protected $lockToDomain = '';

	/**
	 * @var string
	 */
	protected $title = '';

	/**
	 * @var string
	 */
	protected $zip = '';

	/**
	 * @var string
	 */
	protected $city = '';

	/**
	 * @var string
	 */
	protected $country = '';

	/**
	 * @var string
	 */
	protected $www = '';

	/**
	 * @var string
	 */
	protected $company = '';

	/**
	 * @var string
	 */
	protected $image = '';

	/**
	 * @var DateTime
	 */
	protected $lastlogin = '';

	/**
	 * @var DateTime
	 */
	protected $isOnline = '';

	/**
	 * Called when the object is reconstituted.
	 */
	public function initializeObject() {
	}

	/**
	 * An example of how to force email and username to be equal
	 * @param $email
	 */
	public function setEmail($email) {
		$this->email = $email;
		$this->username = $email;
	}

	/**
	 * An example of how to force email and username to be equal
	 * @param $username
	 */
	public function setUsername($username) {
		$this->email = $email;
		$this->username = $username;
	}

	/**
	 * @param string $confirmPassword
	 */
	public function setConfirmPassword($confirmPassword) {
		$this->confirmPassword = $confirmPassword;
	}

	/**
	 * @return string
	 */
	public function getConfirmPassword() {
		return $this->confirmPassword;
	}
}
?>