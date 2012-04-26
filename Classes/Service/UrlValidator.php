<?php
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

/**
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later

 */

class Tx_Cicregister_Service_UrlValidator implements t3lib_Singleton {

	/**
	 * Returns a valid and XSS cleaned url for redirect, checked against configuration "allowedRedirectHosts"
	 *
	 * @param string $url
	 * @return string cleaned referer or empty string if not valid
	 */
	public function validateRedirectUrl($url) {
		$url = strval($url);
		if ($url === '') {
			return '';
		}

		$decodedUrl = rawurldecode($url);
		$sanitizedUrl = t3lib_div::removeXSS($decodedUrl);

		if ($decodedUrl !== $sanitizedUrl || preg_match('#["<>\\\]+#', $url)) {
			t3lib_div::sysLog(sprintf($this->pi_getLL('xssAttackDetected'), $url), 'cicregister', t3lib_div::SYSLOG_SEVERITY_WARNING);
			return '';
		}

		// Validate the URL:
		if ($this->isRelativeUrl($url) || $this->isInCurrentDomain($url) || $this->isInLocalDomain($url)) {
			return $url;
		}

		// URL is not allowed
		t3lib_div::sysLog(sprintf($this->pi_getLL('noValidRedirectUrl'), $url), 'felogin', t3lib_div::SYSLOG_SEVERITY_WARNING);
		return '';
	}

	/**
	 * Determines whether the URL is on the current host
	 * and belongs to the current TYPO3 installation.
	 *
	 * @param string $url URL to be checked
	 * @return boolean Whether the URL belongs to the current TYPO3 installation
	 */
	protected function isInCurrentDomain($url) {
		return (t3lib_div::isOnCurrentHost($url) && t3lib_div::isFirstPartOfStr($url, t3lib_div::getIndpEnv('TYPO3_SITE_URL')));
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

		if (t3lib_div::isValidUrl($url)) {
			$parsedUrl = parse_url($url);
			if ($parsedUrl['scheme'] === 'http' || $parsedUrl['scheme'] === 'https') {
				$host = $parsedUrl['host'];
				// Removes the last path segment and slash sequences like /// (if given):
				$path = preg_replace('#/+[^/]*$#', '', $parsedUrl['path']);

				$cObj = new tslib_cObj();

				$localDomains = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
					'domainName',
					'sys_domain',
						'1=1' . $cObj->enableFields('sys_domain')
				);
				if (is_array($localDomains)) {
					foreach ($localDomains as $localDomain) {
						// strip trailing slashes (if given)
						$domainName = rtrim($localDomain['domainName'], '/');
						if (t3lib_div::isFirstPartOfStr($host . $path . '/', $domainName . '/')) {
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
			return (!t3lib_div::isFirstPartOfStr($parsedUrl['path'], '/') || t3lib_div::isFirstPartOfStr($parsedUrl['path'], t3lib_div::getIndpEnv('TYPO3_SITE_PATH')));
		}
		return FALSE;
	}

}

?>