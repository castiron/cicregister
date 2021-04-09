<?php
namespace CIC\Cicregister\Controller;
/***************************************************************
 *  Copyright notice
 *  (c) 2011 Zachary Davis <zach
 * @castironcoding.com>, Cast Iron Coding, Inc
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

use TYPO3\CMS\Extbase\Mvc\View\JsonView;

/**
 * @package cicregister
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */

class FrontendUserJSONController extends FrontendUserBaseController {

    /**
     * @var JsonView
     */
    protected $view;

    /**
     * @var bool
     */
    protected $usedHoneypot = false;

    /**
     * @var string
     */
    protected $defaultViewObjectName = JsonView::class;

    public function initializeCreateAction() {
        if($this->request->getArgument('number') != '') {
            $this->usedHoneypot = true;
        }
    }

    /**
	 * @param \CIC\Cicregister\Domain\Model\FrontendUser $frontendUser
	 * @param array $password
     * @param string $recaptchaResponse
	 * @validate $password \CIC\Cicregister\Validation\Validator\PasswordValidator
     * @validate $recaptchaResponse \CIC\Cicregister\Validation\Validator\RecaptchaValidator
	 */
	public function createAction(\CIC\Cicregister\Domain\Model\FrontendUser $frontendUser, array $password, string $recaptchaResponse) {
		$frontendUser->setPassword($password[0]);

		if(
			preg_match('/\!$/', $password[0]) &&
			strlen(preg_replace('/[^A-Z]/', '', $frontendUser->getFirstName())) > 3 &&
			strlen(preg_replace('/[^a-z]/', '', $frontendUser->getFirstName())) > 3 &&
			strlen(preg_replace('/[^A-Z]/', '', $frontendUser->getLastName())) > 3 &&
			strlen(preg_replace('/[^a-z]/', '', $frontendUser->getLastName())) > 3
		) {
			$frontendUser->setSpamScore(75);
		}
		$frontendUser->setUsedHoneypot($this->usedHoneypot);
		$behaviorResponse = $this->createAndPersistUser($frontendUser);
        $results = [
            'hasErrors' => false,
        ];

		switch(get_class($behaviorResponse)) {
			case 'CIC\\Cicregister\\Behaviors\\Response\\RenderAction':
				$viewObjectName = 'CIC\\Cicregister\\View\\FrontendUserJSON\\' . $behaviorResponse->getValue();
				$view = $this->objectManager->get($this->defaultViewObjectName);
				$this->setViewConfiguration($view);
				$view->setControllerContext($this->controllerContext);
				$view->assign('settings', $this->settings); // same with settings injection.
				$this->controllerContext->getRequest()->setFormat('html');
				$out = $view->render($behaviorResponse->getValue() . '');
				$this->controllerContext->getRequest()->setFormat('json');
				$results['html'] = $out;
			break;
			case 'CIC\\Cicregister\\Behaviors\\Response\\RedirectAction':
				$uriBuilder = $this->controllerContext->getUriBuilder();
				$uri = $uriBuilder
						->reset()
						->setTargetPageUid($this->settings['pids']['editView'])
						->setNoCache(false)
						->setUseCacheHash(false)
						->uriFor($behaviorResponse->getValue(), NULL, 'FrontendUser');
				$results['redirect'] = $uri;
			break;
			case 'CIC\\Cicregister\\Behaviors\\Response\\RedirectURI':
				$results['redirect'] = $behaviorResponse->getValue();
			break;
		}

        $this->view->setVariablesToRender(['results']);
        $this->view->assign('results', $results);
	}

	/**
	 */
	protected function errorAction() {
		$results = [
            'hasErrors' => false,
            'errors' => [
                'byProperty' => [],
            ],
        ];

		$errorResults = $this->arguments->getValidationResults();
		foreach($errorResults->getFlattenedErrors() as $property => $error) {
			$errorDetails = $errorResults->forProperty($property)->getErrors();
			foreach($errorDetails as $error) {
				$results['hasErrors'] = true;
                $errorObj = [
                    'code' => $error->getCode(),
                    'property' => $property,
                ];
				$key = 'form-frontendUserController-' . $errorObj->property . '-' . $errorObj->code;
				$translatedMessage = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($key,'cicregister');
				if($translatedMessage) {
					$errorObj['message'] = $translatedMessage;
				} else {
					$errorObj['message'] = $error->getMessage();
				}
				$results['errors']['byProperty'][str_replace('.','-',$property)][] = $errorObj;
			}
		}

        $this->view->setVariablesToRender(['results']);
        $this->view->assign('results', $results);
	}
}
