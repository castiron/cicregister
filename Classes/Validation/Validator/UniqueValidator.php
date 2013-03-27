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

class UniqueValidator extends \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator {

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
	 * @param string $value
	 * @return bool
	 * @throws \TYPO3\CMS\Extbase\Validation\Exception\InvalidValidationConfigurationException
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
					$error = new \TYPO3\CMS\Extbase\Validation\Error('Email address is not available', 1325202490);
					$this->result->addError($error);
					return FALSE;
				}
			}
		}
		throw new \TYPO3\CMS\Extbase\Validation\Exception\InvalidValidationConfigurationException('Invalid configuration for the CICRegister Unique Validator. Annotation should include a valid repository name and a property name', 1325114848);
	}

}
