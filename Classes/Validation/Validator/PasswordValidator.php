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

class Tx_Cicregister_Validation_Validator_PasswordValidator extends Tx_Extbase_Validation_Validator_AbstractValidator {

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
	 * @param array $value
	 * @return bool
	 * @throws Tx_Extbase_Validation_Exception_InvalidValidationConfiguration
	 */
	public function isValid($value) {

		// get the option for the validation
		$minimumLength = $this->options['minimumLength'];
		$minimumLengthSpelledOut = $this->options['minimumLengthSpelledOut'];
		$allowEmpty = $this->options['allowEmpty'];

		// check for empty password
		if ($value[0] === '' && $allowEmpty != true) {
			$this->addError('Empty passwords are not allowed. Please enter a secure password.', 1221560718);
		}
		// check for password length
		if (
			($allowEmpty == true && strlen($value[0] > 0 && strlen($value[0]) < $minimumLength)) ||
			($allowEmpty == false && strlen($value[0]) < $minimumLength)
		) {
			$this->addError('The password is too short, minimum length is ' . $minimumLengthSpelledOut . ' characters.', 1221560718);
		}

		// check that the passwords are the same
		if (strcmp($value[0], $value[1]) != 0) {
			$this->addError('The passwords do not match. Please make sure you have entered the password correctly in both fields', 1221560718);
		}

	}

}
