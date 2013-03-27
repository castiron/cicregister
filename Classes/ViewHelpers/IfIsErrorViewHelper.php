<?php
namespace CIC\Cicregister\ViewHelpers;

/*                                                                        *
 * This script belongs to the FLOW3 package "Cicregister".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * This view helper implements an if/else condition.
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class IfIsErrorViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractConditionViewHelper {

	/**
	 * renders <f:then> child if $condition is true, otherwise renders <f:else> child.
	 *
	 * @param string $key Error key to look up
	 * @return string the rendered string
	 * @author Zach Davis <zach@castironcoding.com>
	 */
	public function render($key) {
		$results = $this->controllerContext->getRequest()->getOriginalRequestMappingResults()->forProperty($key);
		if($results->hasErrors()) {
			return $this->renderThenChild();
		} else {
			return $this->renderElseChild();
		}
	}
}

?>
