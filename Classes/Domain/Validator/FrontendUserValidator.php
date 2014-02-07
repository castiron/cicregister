<?php
namespace CIC\Cicregister\Domain\Validator;
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

class FrontendUserValidator extends \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator {

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 */
	protected $objectManager;

	/**
	 * Inject the objectManager
	 *
	 * @param \TYPO3\CMS\Extbase\Object\ObjectManager objectManager
	 * @return void
	 */
	public function injectObjectManager(\TYPO3\CMS\Extbase\Object\ObjectManager $objectManager) {
		$this->objectManager = $objectManager;
	}

	public function isValid($frontendUser) {
		$valid = true;
		$repository = $this->objectManager->get('CIC\\Cicregister\\Domain\\Repository\\GlobalFrontendUserRepository');
		$matches = $repository->findByEmail($frontendUser->getEmail());
		foreach($matches as $match) {
			if($match->getUid() != $frontendUser->getUid()) {
				$error = new \TYPO3\CMS\Extbase\Validation\Error('Email address is not available', 1325202490);
				$this->result->forProperty('email')->addError($error);
				$valid = false;
				break;
			}
		}
		$matches = $repository->findByUsername($frontendUser->getUsername());
		foreach($matches as $match) {
			if($match->getUid() != $frontendUser->getUid()) {
				$error = new \TYPO3\CMS\Extbase\Validation\Error('Username is not available', 1325202492);
				$this->result->forProperty('username')->addError($error);
				$valid = false;
				break;
			}
		}
		return $valid;
	}

}
