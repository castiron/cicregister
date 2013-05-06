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
class Tx_Cicregister_Domain_Model_Invitation extends Tx_Extbase_DomainObject_AbstractEntity {

	/**
	 * @var string
	 */
	protected $email;

	/**
	 * @var Tx_Cicregister_Domain_Model_FrontendUser
	 */
	protected $invitedBy;

	/**
	 * @var DateTime
	 */
	protected $expiresOn;

	/**
	 * @var bool
	 */
	protected $accepted = false;

	/**
	 * @var string
	 */
	protected $onAcceptance;

	/**
	 * @param boolean $accepted
	 */
	public function setAccepted($accepted) {
		$this->accepted = $accepted;
	}

	/**
	 * @return boolean
	 */
	public function getAccepted() {
		return $this->accepted;
	}

	/**
	 * @param string $email
	 */
	public function setEmail($email) {
		$this->email = $email;
	}

	/**
	 * @return string
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * @param \DateTime $expiresOn
	 */
	public function setExpiresOn($expiresOn) {
		$this->expiresOn = $expiresOn;
	}

	/**
	 * @return \DateTime
	 */
	public function getExpiresOn() {
		return $this->expiresOn;
	}

	/**
	 * @param \Tx_Cicregister_Domain_Model_FrontendUser $invitedBy
	 */
	public function setInvitedBy($invitedBy) {
		$this->invitedBy = $invitedBy;
	}

	/**
	 * @return \Tx_Cicregister_Domain_Model_FrontendUser
	 */
	public function getInvitedBy() {
		return $this->invitedBy;
	}

	/**
	 * @param string $onAcceptance
	 */
	public function setOnAcceptance($onAcceptance) {
		$this->onAcceptance = $onAcceptance;
	}

	/**
	 * @return string
	 */
	public function getOnAcceptance() {
		return $this->onAcceptance;
	}



}
?>
