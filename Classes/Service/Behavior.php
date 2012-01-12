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

class Tx_Cicregister_Service_Behavior implements t3lib_Singleton {

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
	 * @param array $behaviors
	 * @param $object
	 * @param $controllerContext
	 * @param $default
	 * @return mixed
	 */
	public function executeBehaviors(array $behaviors, $object, $controllerContext, $default) {
		$behaviorResponse = false;
		foreach ($behaviors as $behaviorClassName => $enabled) {
			if ($enabled == true || (is_array($enabled) && $enabled['_typoScriptNodeValue'] == true)) {
				if (is_array($enabled)) {
					$conf = $enabled;
				} else {
					$conf = array();
				}
				$behavior = $this->objectManager->create($behaviorClassName);
				$behavior->setControllerContext($controllerContext);
				$result = $behavior->execute($object, $conf);
				if ($result) {
					$behaviorResponse = $result;
				}
			}
		}

		if ($behaviorResponse == false) {
			$behaviorResponse = $this->objectManager->create('Tx_Cicregister_Behaviors_Response_RenderAction');
			$behaviorResponse->setValue($default);
		}
		return $behaviorResponse;
	}

}
