<?php

$user = tslib_eidtools::initFeUser();

$response = new stdClass;

if($user->user['uid'] > 0) {
    $response->foundUser = true;
    $response->userName = $user->user['username'];
} else {
    $response->foundUser = false;
}

header('Content-type: application/json');
echo json_encode($response);
die();

?>