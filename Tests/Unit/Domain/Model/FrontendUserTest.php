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
 *  the Free Software Foundation; either version 2 of the License, or
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
 * Test case for class Tx_Cicregister_Domain_Model_FEUser.
 *
 * @version $Id$
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 * @package TYPO3
 * @subpackage CIC User Registration
 *
 * @author Zachary Davis <zach@castironcoding.com>
 */
class Tx_Cicregister_Domain_Model_FrontendUserTest extends Tx_Extbase_Tests_Unit_BaseTestCase {

	/**
	 * @var Tx_Cicregister_Domain_Model_FrontendUser
	 */
	protected $fixture;

	/**
	 *
	 */
	public function setUp() {
		$this->fixture = new Tx_Cicregister_Domain_Model_FrontendUser();
	}

	/**
	 *
	 */
	public function tearDown() {
		unset($this->fixture);
	}

	/**
	 * Test if username can be set
	 *
	 * @test
	 * @return void
	 */
	public function usernameCanBeSet() {
		$username = 'peewee';
		$this->fixture->setUsername($username);
		$this->assertEquals($username, $this->fixture->getUsername());
	}

	/**
	 * Test if email can be set
	 *
	 * @test
	 * @return void
	 */
	public function emailCanBeSet() {
		$email = 'name@email.com';
		$this->fixture->setEmail($email);
		$this->assertEquals($email, $this->fixture->getEmail());
	}

	/**
	 * Test if a user can be disabled
	 *
	 * @test
	 * @return void
	 */
	public function disableCanBeSet() {
		$disable = true;
		$this->fixture->setDisable($disable);
		$this->assertEquals($disable, $this->fixture->getDisable());
	}

	/**
	 * Tests if first name can be set
	 *
	 * @test
	 * @return void
	 */
	public function firstNameCanBeSet() {
		$firstName = 'John';
		$this->fixture->setFirstName($firstName);
		$this->assertEquals($firstName, $this->fixture->getFirstName());
	}

	/**
	 * Tests if last name can be set
	 *
	 * @test
	 * @return void
	 */
	public function lastNameCanBeSet() {
		$lastName = 'Milton';
		$this->fixture->setLastName($lastName);
		$this->assertEquals($lastName, $this->fixture->getLastName());
	}

	/**
	 * Tests if name is composed of $firstName.' '.$lastName
	 *
	 * @test
	 * @return void
	 */
	public function nameEqualsFirstAndLastName() {
		$lastName = 'Milton';
		$firstName = 'John';
		$fullName = $firstName.' '.$lastName;
		$this->fixture->setFirstName($firstName);
		$this->fixture->setLastName($lastName);
		$this->assertEquals($fullName, $this->fixture->getName());
	}

	/**
	 * Tests if sfdcContactID can be set
	 *
	 * @test
	 * @return void
	 */
	public function sfdcContactIdCanBeSet() {
		$sfdcContactID = '0035000000N1Gwj';
		$this->fixture->setSfdcContactID($sfdcContactID);
		$this->assertEquals($sfdcContactID, $this->fixture->getSfdcContactID());
	}

	/**
	 * Tests if sfdcLeadId can be set
	 *
	 * @test
	 * @return void
	 */
	public function sfdcLeadIdCanBeSet() {
		$sfdcLeadId = '0035000000N1Gwj';
		$this->fixture->setSfdcLeadID($sfdcLeadId);
		$this->assertEquals($sfdcLeadId, $this->fixture->getSfdcLeadID());
	}

	/**
	 * Tests if sfdcSyncTimestamp can be set
	 *
	 * @test
	 * @return void
	 */
	public function sfdcTimestampCanBeSet() {
		$sfdcSyncTimestamp = time();
		$this->fixture->setSfdcSyncTimestamp($sfdcSyncTimestamp);
		$this->assertEquals($sfdcSyncTimestamp, $this->fixture->getSfdcSyncTimestamp());
	}



}
?>