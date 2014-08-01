<?php
namespace CIC\Cicregister\Service;
/***************************************************************
 *  Copyright notice
 *  (c) 2011 Zachary Davis <zach@castironcoding.com>, Cast Iron Coding, Inc
 *  All rights reserved
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Exception;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later

 */

class UrlValidator implements \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * Returns a valid and XSS cleaned url for redirect, checked against configuration "allowedRedirectHosts"
	 *
	 * @param string $url
	 * @return string cleaned referer or empty string if not valid
	 */
	public function validateReturnUrl($url) {
		$url = strval($url);
		if ($url === '') {
			return '';
		}

		$decodedUrl = rawurldecode($url);
		$sanitizedUrl = \TYPO3\CMS\Core\Utility\GeneralUtility::removeXSS($decodedUrl);

		if ($decodedUrl !== $sanitizedUrl || preg_match('#["<>\\\]+#', $url)) {
			\TYPO3\CMS\Core\Utility\GeneralUtility::sysLog(sprintf(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('service-URLValidator-xssAttackDetected', 'cicregister'), $url), 'cicregister', \TYPO3\CMS\Core\Utility\GeneralUtility::SYSLOG_SEVERITY_WARNING);
			return '';
		}

		// Validate the URL:
		if ($this->canRedirectToUrl($url)) {
			return $url;
		}

		// URL is not allowed
		\TYPO3\CMS\Core\Utility\GeneralUtility::sysLog(sprintf(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('service-URLValidator-noValidRedirectUrl', 'cicregister'), $url), 'felogin', \TYPO3\CMS\Core\Utility\GeneralUtility::SYSLOG_SEVERITY_WARNING);
		return '';
	}

	/**
	 * @param $url
	 * @return bool
	 */
	protected function canRedirectToUrl($url) {
		return $this->isRelativeUrl($url) || $this->isInCurrentDomain($url) || $this->isInLocalDomain($url) || $this->isOfConfiguredAllowableDomain($url);
	}

	/**
	 * Determines whether the URL is on the current host
	 * and belongs to the current TYPO3 installation.
	 *
	 * @param string $url URL to be checked
	 * @return boolean Whether the URL belongs to the current TYPO3 installation
	 */
	protected function isInCurrentDomain($url) {
		return (\TYPO3\CMS\Core\Utility\GeneralUtility::isOnCurrentHost($url) && \TYPO3\CMS\Core\Utility\GeneralUtility::isFirstPartOfStr($url, \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SITE_URL')));
	}

	/**
	 * Determines whether the URL matches a domain
	 * in the sys_domain databse table.
	 *
	 * @param string $url Absolute URL which needs to be checked
	 * @return boolean Whether the URL is considered to be local
	 */
	protected function isInLocalDomain($url) {
		$result = FALSE;

		if (\TYPO3\CMS\Core\Utility\GeneralUtility::isValidUrl($url)) {
			$parsedUrl = parse_url($url);
			if ($parsedUrl['scheme'] === 'http' || $parsedUrl['scheme'] === 'https') {
				$host = $parsedUrl['host'];
				// Removes the last path segment and slash sequences like /// (if given):
				$path = preg_replace('#/+[^/]*$#', '', $parsedUrl['path']);

				$cObj = new ContentObjectRenderer();

				$localDomains = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
					'domainName',
					'sys_domain',
						'1=1' . $cObj->enableFields('sys_domain')
				);
				if (is_array($localDomains)) {
					foreach ($localDomains as $localDomain) {
						// strip trailing slashes (if given)
						$domainName = rtrim($localDomain['domainName'], '/');
						if (\TYPO3\CMS\Core\Utility\GeneralUtility::isFirstPartOfStr($host . $path . '/', $domainName . '/')) {
							$result = TRUE;
							break;
						}
					}
				}
			}
		}
		return $result;
	}

	/**
	 * Determines whether the URL is in a list of allowed redirect source domains
	 * from typoscript.
	 *
	 * @param $url
	 * @return bool
	 */
	protected function isOfConfiguredAllowableDomain($url) {
		$out = false;
		if($configuredDomains = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_cicregister.']['settings.']['login.']['allowedRedirectSourceDomains']) {
			$allowedDomains = GeneralUtility::trimExplode(',', $configuredDomains);
			if(count($allowedDomains) && $d = $this->getDomainFromUrl($url)) {
				$out = in_array($d, $allowedDomains);
			}
		}
		return $out;
	}

	/**
	 * @param $url
	 * @return string
	 * @throws \TYPO3\CMS\Extbase\Exception
	 */
	protected function getDomainFromUrl($url) {
		$out = '';
		$found = preg_match('~https?://?([^/]+)~', $url, $matches);
		if ($found && $matches[1]) {
			$out = strtolower($matches[1]);
		}
		return $out;
	}

	/**
	 * Determines wether the URL is relative to the
	 * current TYPO3 installation.
	 *
	 * @param string $url URL which needs to be checked
	 * @return boolean Whether the URL is considered to be relative
	 */
	protected function isRelativeUrl($url) {
		$parsedUrl = @parse_url($url);
		if ($parsedUrl !== FALSE && !isset($parsedUrl['scheme']) && !isset($parsedUrl['host'])) {
			// If the relative URL starts with a slash, we need to check if it's within the current site path
			return (!\TYPO3\CMS\Core\Utility\GeneralUtility::isFirstPartOfStr($parsedUrl['path'], '/') || \TYPO3\CMS\Core\Utility\GeneralUtility::isFirstPartOfStr($parsedUrl['path'], \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SITE_PATH')));
		}
		return FALSE;
	}

}

?>