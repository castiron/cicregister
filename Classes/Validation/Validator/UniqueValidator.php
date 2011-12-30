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

class Tx_Cicregister_Validation_Validator_UniqueValidator extends Tx_Extbase_Validation_Validator_AbstractValidator {

	/**
	 * @var Tx_Extbase_Object_ObjectManager
	 */
	protected $objectManager;

	/**
	 * Inject the objectManager
	 *
	 * @param Tx_Extbase_Object_ObjectManager objectManager
	 * @return void
	 */
	public function injectObjectManager(Tx_Extbase_Object_ObjectManager $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * @param string $value
	 * @return bool
	 * @throws Tx_Extbase_Validation_Exception_InvalidValidationConfiguration
	 */
	public function isValid($value) {
		if($this->options['repository'] && $this->options['property']) {
			if($this->objectManager->isRegistered($this->options['repository'])) {
				$repository = $this->objectManager->get($this->options['repository']);
				$methodName = 'countBy'.ucfirst($this->options['property']);
				$count = $repository->$methodName($value);
				if($count == 0) {
					return TRUE;
				} else {
					$error = new Tx_Extbase_Validation_Error('Email address is not available', 1325202490);
					$this->result->addError($error);
					return FALSE;
				}
			}
		}
		throw new Tx_Extbase_Validation_Exception_InvalidValidationConfiguration('Invalid configuration for the CICRegister Unique Validator. Annotation should include a valid repository name and a property name', 1325114848);
	}

}
