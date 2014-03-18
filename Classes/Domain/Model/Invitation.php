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
class Invitation extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {
	/**
	 * Defines how long a user must wait before resending an invitation
	 */
	const WAIT_PERIOD = 'PT3M';
	const WAIT_PERIOD_ENGLISH = 'three minutes';
	/**
	 * Defines how long a user must wait before resending an invitation
	 */
	const WAIT_PERIOD = 'PT3M';
	const WAIT_PERIOD_ENGLISH = 'three minutes';

	/**
	 * @var string
	 */
	protected $email;

	/**
	 * @var \CIC\Cicregister\Domain\Model\FrontendUser
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
	 * @var DateTime
	 */
	protected $lastModified;

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
	 * @param \\CIC\Cicregister\Domain\Model\FrontendUser $invitedBy
	 */
	public function setInvitedBy($invitedBy) {
		$this->invitedBy = $invitedBy;
	}

	/**
	 * @return \\CIC\Cicregister\Domain\Model\FrontendUser
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
	/**
	 * @param \DateTime $lastModified
	 */
	public function setLastModified($lastModified) {
		$this->lastModified = $lastModified;
	}
	/**
	 * @return \DateTime
	 */
	public function getLastModified() {
		return $this->lastModified;
	}
	/**
	 * @return bool
	 */
	public function canResend() {
		$waitPeriodEnd = new \DateTime();
		$waitPeriodEnd->sub(new \DateInterval(self::WAIT_PERIOD));
		return $this->lastModified < $waitPeriodEnd;
	}


	/**
	 * @param \DateTime $lastModified
	 */
	public function setLastModified($lastModified) {
		$this->lastModified = $lastModified;
	}

	/**
	 * @return \DateTime
	 */
	public function getLastModified() {
		return $this->lastModified;
	}

	/**
	 * @return bool
	 */
	public function canResend() {
		$waitPeriodEnd = new DateTime();
		$waitPeriodEnd->sub(new DateInterval(self::WAIT_PERIOD));
		return $this->lastModified < $waitPeriodEnd;
	}


}
?>
