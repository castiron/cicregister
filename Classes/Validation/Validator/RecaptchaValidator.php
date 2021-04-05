<?php namespace CIC\Cicregister\Validation\Validator;

use GuzzleHttp\Client;
use TYPO3\CMS\Extbase\Error\Error;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2021 Lucas Thurston <lucas@castironcoding.com>, Cast Iron Coding, Inc
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

class RecaptchaValidator extends \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator {
    /**
     * @param mixed $recaptchaResponse
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function isValid($recaptchaResponse) {
        // It's valid if it's not enabled
        if(!$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['cicregister']['recaptcha']['enabled']) return;

        $client = new Client([
            'base_uri' => 'https://google.com/recaptcha/api/',
            'timeout' => 2.0
        ]);
        $response = $client->request('POST', 'siteverify', [
            'query' => [
                'secret' => $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['cicregister']['recaptcha']['secret_key'],
                'response' => $recaptchaResponse
            ]
        ]);
        $parsed = json_decode((string) $response->getBody());
        if(!(bool) @$parsed->success) {
            $this->result->addError(new Error('Captcha is invalid'));
        }
    }
}
