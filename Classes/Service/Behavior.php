<?php
namespace CIC\Cicregister\Service;
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

class Behavior implements \TYPO3\CMS\Core\SingletonInterface {

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
	 * @param array $behaviors
	 * @param $object
	 * @param $controllerContext
	 * @param $default
	 * @param array $extraConf
	 * @return bool|object
	 */
	public function executeBehaviors(array $behaviors, $object, $controllerContext, $default, $extraConf = array()) {
		$behaviorResponse = false;
		foreach ($behaviors as $behaviorClassName => $enabled) {
			if ((is_array($enabled) && $enabled['_typoScriptNodeValue'] == true) || !is_array($enabled) && $enabled == true) {
				if (is_array($enabled)) {
					$conf = $enabled;
				} else {
					$conf = array();
				}
				$conf = array_merge_recursive($conf, $extraConf);
				$behavior = $this->objectManager->create($behaviorClassName);
				$behavior->setControllerContext($controllerContext);
				$result = $behavior->execute($object, $conf);
				if ($result) {
					$behaviorResponse = $result;
				}
			}
		}

		if ($behaviorResponse == false) {
			$behaviorResponse = $this->objectManager->create('CIC\\Cicregister\\Behaviors\\Response\\RenderAction');
			$behaviorResponse->setValue($default);
		}
		return $behaviorResponse;
	}

}
