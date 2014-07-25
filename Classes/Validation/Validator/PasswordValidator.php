<?php
namespace CIC\Cicregister\Validation\Validator;
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

class PasswordValidator extends \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator {

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

	/**
	 * This contains the supported options, their default values, types and descriptions.
	 *
	 * @var array
	 */
	protected $supportedOptions = array('allowEmpty' => array(NULL, 'Boolean value', 'boolean|string|integer'));

	/**
	 * @param array $value
	 * @return bool
	 * @throws \TYPO3\CMS\Extbase\Validation\Exception_InvalidValidationConfiguration
	 */
	public function isValid($value) {

		$minimumLength = 6;
		$minimumLengthSpelledOut = 'six';

		if(array_key_exists('allowEmpty',$this->options)) {
			$allowEmpty = $this->options['allowEmpty'];
		} else {
			$allowEmpty = false;
		}

		// check for empty password
		if ($value[0] === '' && $allowEmpty != true) {
			$this->addError('Password is a required field.', 1221560718);
		}

		// check for password length
		if (
			($allowEmpty == true && strlen($value[0] > 0 && strlen($value[0]) < $minimumLength)) ||
			($allowEmpty == false && strlen($value[0]) < $minimumLength)
		) {
			$this->addError('Minimum length is ' . $minimumLengthSpelledOut . ' characters.', 1221560719);
		}

		// check that the passwords are the same
		if (strcmp($value[0], $value[1]) != 0) {
			$this->addError('Passwords do not match.', 1221560720);
		}

	}

}
