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

class Tx_Cicregister_Controller_FrontendUserControllerTest extends Tx_Extbase_Tests_Unit_BaseTestCase {

	protected $frontendUserRepository;

	public function setup() {

	}

	/**
	 * @test
	 */
	public function newActionWorks() {
		$frontendUserRepositoryMock = $this->getMock('Tx_Cicregister_Domain_Repository_FrontendUserRepository');
		$frontendUserMock = $this->getMock('Tx_Cicregister_Domain_Model_FrontendUser');

		$signalSlotDispatcherMock = $this->getMock('Tx_Extbase_SignalSlot_Dispatcher');

		$requestMock = $this->getMock($this->buildAccessibleProxy('Tx_Extbase_MVC_Request'), array('dummy'), array(), '', FALSE);
		$requestMock->_set('pluginName', 'tx_cicregister_create');
		$requestMock->_set('controllerName','frontenduser');
		$requestMock->_set('actionName','new');

		$viewMock = $this->getMock('Tx_Fluid_Core_View_TemplateView', array('assign'), array(), '', FALSE);

		$frontendUserControllerMock = $this->getMock($this->buildAccessibleProxy('Tx_Cicregister_Controller_FrontendUserController'), array('dummy'), array(), '', FALSE);
		$frontendUserControllerMock->_set('request', $mockRequest);
		$frontendUserControllerMock->_set('signalSlotDispatcher',$signalSlotDispatcherMock);
		$frontendUserControllerMock->_set('frontendUserRepository',$frontendUserRepositoryMock);
		$frontendUserControllerMock->_set('view', $viewMock);
		$frontendUserControllerMock->_set('userIsAuthenticated', false);
		$frontendUserControllerMock->newAction($frontendUserMock);

		// TODO: Finish this test.

	}

}
