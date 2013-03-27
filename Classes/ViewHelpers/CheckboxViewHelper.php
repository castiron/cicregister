<?php
namespace CIC\Cicregister\ViewHelpers;
/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
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
 * View Helper which creates a simple checkbox (<input type="checkbox">).
 *
 * = Examples =
 *
 * <code title="Example">
 * <f:form.checkbox name="myCheckBox" value="someValue" />
 * </code>
 * <output>
 * <input type="checkbox" name="myCheckBox" value="someValue" />
 * </output>
 *
 * <code title="Preselect">
 * <f:form.checkbox name="myCheckBox" value="someValue" checked="{object.value} == 5" />
 * </code>
 * <output>
 * <input type="checkbox" name="myCheckBox" value="someValue" checked="checked" />
 * (depending on $object)
 * </output>
 *
 * <code title="Bind to object property">
 * <f:form.checkbox property="interests" value="TYPO3" />
 * </code>
 * <output>
 * <input type="checkbox" name="user[interests][]" value="TYPO3" checked="checked" />
 * (depending on property "interests")
 * </output>
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 */
class CheckboxViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Form\CheckboxViewHelper {

	// TODO: Remove this viewhelper and rely on FLUID checkbox viewhelper once this patch is added to FLUID:
	// https://review.typo3.org/#change,7856
	// http://forge.typo3.org/issues/8854

	/**
	 * Renders the checkbox.
	 *
	 * @param boolean $checked Specifies that the input element should be preselected
	 * @param boolean $multiple Specifies whether this checkbox belongs to a multivalue (is part of a checkbox group)
	 * @return string
	 * @api
	 */
	public function render($checked = NULL, $multiple = NULL) {
		$this->tag->addAttribute('type', 'checkbox');

		$nameAttribute = $this->getName();
		$valueAttribute = $this->getValue();
		if ($this->isObjectAccessorMode()) {
			try {
				$propertyValue = $this->getPropertyValue();
			} catch (\TYPO3\CMS\Fluid\Core\ViewHelper\Exception_InvalidVariableException $exception) {
				$propertyValue = FALSE;
			}
			if ($propertyValue instanceof \Traversable) {
				$propertyValue = iterator_to_array($propertyValue);
			}
			if (is_array($propertyValue)) {
				if ($checked === NULL) {
					$checked = in_array($valueAttribute, $propertyValue);
				}
				$nameAttribute .= '[]';
			} elseif ($multiple === TRUE) {
				$nameAttribute .= '[]';
			} elseif ($checked === NULL && $propertyValue !== NULL) {
				$checked = (boolean)$propertyValue === (boolean)$valueAttribute;
			}
		}

		$this->registerFieldNameForFormTokenGeneration($nameAttribute);
		$this->tag->addAttribute('name', $nameAttribute);
		$this->tag->addAttribute('value', $valueAttribute);
		if ($checked) {
			$this->tag->addAttribute('checked', 'checked');
		}

		$this->setErrorClassAttribute();

		$hiddenField = $this->renderHiddenFieldForEmptyValue();
		return $hiddenField . $this->tag->render();
	}
}

?>