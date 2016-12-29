<?php

$TSFE = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController', $GLOBALS['TYPO3_CONF_VARS'], 0, 0);

$user = $TSFE->initFEuser();

$response = new \stdClass;

if($user->user['uid'] > 0) {
    $response->foundUser = true;
    $response->userName = $user->user['username'];
} else {
    $response->foundUser = false;
}

header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
header('Pragma: no-cache'); // HTTP 1.0.
header('Expires: 0'); // Proxies.
header('Content-type: application/json');
echo json_encode($response);
die();

?>