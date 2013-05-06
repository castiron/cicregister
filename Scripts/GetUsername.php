<?php

$user = tslib_eidtools::initFeUser();

$response = new stdClass;

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