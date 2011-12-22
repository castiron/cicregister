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
 * @package cicregister
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */

class Tx_Cicregister_Controller_FrontendUserController extends Tx_Extbase_MVC_Controller_ActionController {

	/**
	 * @var Tx_Cicregister_Domain_Repository_FrontendUserRepository
	 */
	protected $frontendUserRepository;

	/**
	 * @var Tx_Extbase_SignalSlot_Dispatcher
	 */
	protected $signalSlotDispatcher;

	/**
	 * Inject the signalSlotDispatcher
	 *
	 * @param Tx_Extbase_SignalSlot_Dispatcher signalSlotDispatcher
	 * @return void
	 */
	public function injectSignalSlotDispatcher(Tx_Extbase_SignalSlot_Dispatcher $signalSlotDispatcher) {
		$this->signalSlotDispatcher = $signalSlotDispatcher;
	}

	/**
	 * Inject the frontendUserRepository
	 *
	 * @param Tx_Cicregister_Domain_Repository_FrontendUserRepository $frontendUserRepository
	 * @return void
	 */
	public function injectFrontendUserRepository(Tx_Cicregister_Domain_Repository_FrontendUserRepository $frontendUserRepository) {
		$this->frontendUserRepository = $frontendUserRepository;
	}

	public function initializeAction() {
		// An example of how to make dynamic changes to method arguments.
		// $this->arguments->addNewArgument('frontendUser', $this->settings['userModel']);
	}

	/**
	 * Renders the "new user" form.
	 *
	 * @param $frontendUser
	 * @dontvalidate $frontendUser
	 * @return void
	 */
	public function newAction(Tx_Cicregister_Domain_Model_FrontendUser $frontendUser = NULL) {
		$this->signalSlotDispatcher->dispatch(__CLASS__, 'newAction', array('frontendUser' => $frontendUser, 'view' => $this->view));
		$this->view->assign('frontendUser', $frontendUser);
	}

	/**
	 * @param Tx_Cicregister_Domain_Model_FrontendUser $frontendUser
	 * @param null $confirmPassword
	 */
	public function createAction(Tx_Cicregister_Domain_Model_FrontendUser $frontendUser, $confirmPassword = NULL) {
		$this->signalSlotDispatcher->dispatch(__CLASS__, 'createAction', array('frontendUser' => $frontendUser, 'view' => $this->view));
	}

	/**
	 * Edit user action
	 *
	 * @param $frontendUser
	 * @return void
	 */
	public function editAction(Tx_Cicregister_Domain_Model_FrontendUser $frontendUser) {
		$this->signalSlotDispatcher->dispatch(__CLASS__, 'editAction', array('frontendUser' => $frontendUser, 'view' => $this->view));
		$this->view->assign('frontendUser', $frontendUser);
	}

	/**
	 * Update action
	 *
	 * @param $frontendUser
	 * @return void
	 */
	public function updateAction(Tx_Cicregister_Domain_Model_FrontendUser $frontendUser) {
		$this->signalSlotDispatcher->dispatch(__CLASS__, 'updateAction', array('frontendUser' => $frontendUser, 'view' => $this->view));
		$this->FrontendUserRepository->update($frontendUser);
		$this->flashMessageContainer->add('Your Frontend user was updated.');
		$this->redirect('edit');
	}

}
?>