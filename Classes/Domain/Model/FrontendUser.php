<?php
namespace CIC\Cicregister\Domain\Model;
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
class FrontendUser extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser {

	/**
	 * sfdcContactID
	 *
	 * @var string
	 */
	protected $sfdcContactID;

	/**
	 * sfdcLeadID
	 *
	 * @var string
	 */
	protected $sfdcLeadID;

	/**
	 * sfdcSyncTimestamp
	 *
	 * @var string
	 */
	protected $sfdcSyncTimestamp;

	/**
	 * @var string
	 * @validate String
	 * @validate NotEmpty
	 */
	protected $username;

	/**
	 * @var string
	 */
	protected $password;

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup>
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
	 * @validate NotEmpty
	 */
	protected $lastName;

	/**
	 * @var string
	 */
	protected $state;

	/**
	 * It's important to note that in the unique validator below, we're validating against all frontend users that Extbase
	 * knows about; we do that by using the global user repository.
	 *
	 * @var string
	 * @validate NotEmpty
	 * @validate EmailAddress
	 * @validate StringLength(minimum = 3,maximum = 50)
	 */
	protected $email = '';

	/**
	 * @var bool
	 */
	protected $disable = false;

	/**
	 * @var string
	 */
	protected $redirectPid;


	/**
	 * Called when the object is reconstituted.
	 */
	public function initializeObject() {
	}

	/**
	 * username == email
	 * @param $email
	 */
	public function setEmail($email) {
		$this->email = $email;
		$this->username = $email;
	}

	/**
	 * username == email
	 * @return string
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * username == email
	 * @param $username
	 */
	public function setUsername($username) {
		$this->email = $username;
		$this->username = $username;
	}

	/**
	 * @param boolean $disable
	 */
	public function setDisable($disable) {
		$this->disable = $disable;
	}

	/**
	 * @return boolean
	 */
	public function getDisable() {
		return $this->disable;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->getFirstName().' '.$this->getLastName();
	}

	/**
	 * @param string $firstName
	 */
	public function setFirstName($firstName) {
		$this->firstName = $firstName;
	}

	/**
	 * @return string
	 */
	public function getFirstName() {
		return $this->firstName;
	}

	/**
	 * @param string $lastName
	 */
	public function setLastName($lastName) {
		$this->lastName = $lastName;
	}

	/**
	 * @return string
	 */
	public function getLastName() {
		return $this->lastName;
	}

	/**
	 * Returns the sfdcContactID
	 *
	 * @return string $sfdcContactID
	 */
	public function getSfdcContactID() {
		return $this->sfdcContactID;
	}

	/**
	 * Sets the sfdcContactID
	 *
	 * @param string $sfdcContactID
	 * @return void
	 */
	public function setSfdcContactID($sfdcContactID) {
		$this->sfdcContactID = $sfdcContactID;
	}

	/**
	 * Returns the sfdcSyncTimestamp
	 *
	 * @return string $sfdcSyncTimestamp
	 */
	public function getSfdcSyncTimestamp() {
		return $this->sfdcSyncTimestamp;
	}

	/**
	 * Sets the sfdcSyncTimestamp
	 *
	 * @param string $sfdcSyncTimestamp
	 * @return void
	 */
	public function setSfdcSyncTimestamp($sfdcSyncTimestamp) {
		$this->sfdcSyncTimestamp = $sfdcSyncTimestamp;
	}

	/**
	 * @param string $sfdcLeadID
	 */
	public function setSfdcLeadID($sfdcLeadID) {
		$this->sfdcLeadID = $sfdcLeadID;
	}

	/**
	 * @return string
	 */
	public function getSfdcLeadID() {
		return $this->sfdcLeadID;
	}

	/**
	 * Determines if the user is part of the given group
	 *
	 * @param \TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup $group
	 * @return bool
	 */
	public function hasUserGroup(\TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup $group){
		return $this->usergroup->contains($group);
	}

	/**
	 * @param string $state
	 */
	public function setState($state) {
		$this->state = $state;
	}

	/**
	 * @return string
	 */
	public function getState() {
		return $this->state;
	}

	/*
	 * @return string
	 */
	public function getRedirectPid() {
		return $this->redirectPid;
	}

	/*
	 * setRedirectPid
	 *
	 * @param string $redirectPid
	 * @return void
	 *
	 */
	public function setRedirectPid($redirectPid) {
		$this->redirectPid = $redirectPid;
	}


	/**
	 * Returns a collection of this user's usergroups that have a redirect pid value pn them
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup>
	 */
	public function getUserGroupsWithRedirect() {
		$usergroupsWithRedirect = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage');

		foreach($this->getUsergroup() as $usergroup) {
			if($usergroup->getRedirectPid()) {
				$usergroupsWithRedirect->attach($usergroup);
			}
		}
		return $usergroupsWithRedirect;
	}
}
?>
