<?php
error_reporting(E_ALL);
ini_set("display_errors","on");

global $CFG;

try {
	$details = $_POST;
	$returnArr = array();
	$returnArr['status'] = true;
	$returnArr['data'] = getPhoneNumber($details['dni']);
} catch (Exception $e) {
	$returnArr['status'] = false;
	$returnArr['data'] = $e->getMessage();
}

header('Content-type: application/json');
echo json_encode($returnArr);
exit();

function getPhoneNumber($dni) {
	$returnArr = file_get_contents('https://4ut05erv1se7454-gh-prd-backend.azurewebsites.net/api/AplicGetUserbyDniSAP?code=aNDnStJuqSZjmgWfZTaB7rqtzjlBcXBZDqZRx1X6ZOYF5xkTyPBeaQ==&userdni='.$dni);
	$returnArr = json_decode($returnArr, true);
	return $returnArr['results'][0]['Celular'];
}