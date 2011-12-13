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
 *
 * @package cicregister
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 *
 */
class Tx_Cicregister_Controller_FEUserController extends Tx_Extbase_MVC_Controller_ActionController {

	/**
	 * fEUserRepository
	 *
	 * @var Tx_Cicregister_Domain_Repository_FEUserRepository
	 */
	protected $fEUserRepository;

	/**
	 * injectFEUserRepository
	 *
	 * @param Tx_Cicregister_Domain_Repository_FEUserRepository $fEUserRepository
	 * @return void
	 */
	public function injectFEUserRepository(Tx_Cicregister_Domain_Repository_FEUserRepository $fEUserRepository) {
		$this->fEUserRepository = $fEUserRepository;
	}

	/**
	 * action list
	 *
	 * @return void
	 */
	public function listAction() {
		$fEUsers = $this->fEUserRepository->findAll();
		$this->view->assign('fEUsers', $fEUsers);
	}

	/**
	 * action show
	 *
	 * @param $fEUser
	 * @return void
	 */
	public function showAction(Tx_Cicregister_Domain_Model_FEUser $fEUser) {
		$this->view->assign('fEUser', $fEUser);
	}

	/**
	 * action new
	 *
	 * @param $newFEUser
	 * @dontvalidate $newFEUser
	 * @return void
	 */
	public function newAction(Tx_Cicregister_Domain_Model_FEUser $newFEUser = NULL) {
		$this->view->assign('newFEUser', $newFEUser);
	}

	/**
	 * action create
	 *
	 * @param $newFEUser
	 * @return void
	 */
	public function createAction(Tx_Cicregister_Domain_Model_FEUser $newFEUser) {
		$this->fEUserRepository->add($newFEUser);
		$this->flashMessageContainer->add('Your new FEUser was created.');
		$this->redirect('list');
	}

	/**
	 * action edit
	 *
	 * @param $fEUser
	 * @return void
	 */
	public function editAction(Tx_Cicregister_Domain_Model_FEUser $fEUser) {
		$this->view->assign('fEUser', $fEUser);
	}

	/**
	 * action update
	 *
	 * @param $fEUser
	 * @return void
	 */
	public function updateAction(Tx_Cicregister_Domain_Model_FEUser $fEUser) {
		$this->fEUserRepository->update($fEUser);
		$this->flashMessageContainer->add('Your FEUser was updated.');
		$this->redirect('list');
	}

	/**
	 * action delete
	 *
	 * @param $fEUser
	 * @return void
	 */
	public function deleteAction(Tx_Cicregister_Domain_Model_FEUser $fEUser) {
		$this->fEUserRepository->remove($fEUser);
		$this->flashMessageContainer->add('Your FEUser was removed.');
		$this->redirect('list');
	}

}
?>