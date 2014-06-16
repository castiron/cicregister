<?php
namespace CIC\Cicregister\Domain\Repository;

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

class GlobalFrontendUserRepository extends \TYPO3\CMS\Extbase\Persistence\Repository {

	/**
	 * Returns the class name of this class.
	 *
	 * @return string Class name of the repository.
	 */
	protected function getRepositoryClassName() {
		// we want to be able to build out this repository without changing the extbase core feuser repository.
		// Because we tell the persistence layer that the classname is \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository,
		// it understands this repository as handling all objects handled by that repository. A bit of a hack, but
		// it seems to work.
		return 'TYPO3\\CMS\\Extbase\\Domain\\Repository\\FrontendUserRepository';
	}

	public function findByUid($uid) {
		if ($this->identityMap->hasIdentifier($uid, $this->objectType)) {
			$object = $this->identityMap->getObjectByIdentifier($uid, $this->objectType);
		} else {
			$query = $this->createQuery();
			$query->getQuerySettings()->setRespectSysLanguage(FALSE);
			$query->getQuerySettings()->setIgnoreEnableFields(TRUE);
			$query->getQuerySettings()->setRespectStoragePage(FALSE);
			$object = $query
					->matching(
				$query->equals('uid', $uid)
			)
					->execute()
					->getFirst();
		}
		return $object;
	}

	public function countByEmail($email) {
		$query = $this->createQuery();
		$query->getQuerySettings()->setIgnoreEnableFields(TRUE);
		$result = $query->matching($query->logicalAnd($query->equals('email', $email), $query->equals('deleted', 0)))
				->execute()
				->count();
		return $result;
	}

	public function findByEmail($email) {
		$query = $this->createQuery();
		$query->getQuerySettings()->setIgnoreEnableFields(TRUE);
		$result = $query->matching($query->logicalAnd($query->equals('email', $email), $query->equals('deleted', 0)))
				->execute();
		return $result;
	}

	public function findByUsername($username) {
		$query = $this->createQuery();
		$query->getQuerySettings()->setIgnoreEnableFields(TRUE);
		$result = $query->matching($query->logicalAnd($query->equals('username', $username), $query->equals('deleted', 0)))
				->execute();
		return $result;
	}

}
