<?php
error_reporting(E_ALL);
ini_set("display_errors","on");

require('../config.php');
require_once('lib.php');

global $DB;

$details = $_POST;
$userId = 0;

$infofieldDNI = $DB->get_record('user_info_data', array('fieldid' => 5, 'data' => $_POST['dni']));
$infofieldCodigo = $DB->get_record('user_info_data', array('fieldid' => 11, 'data' => $_POST['codigo']));

if($infofieldDNI->userid == $infofieldCodigo->userid) {
	$userId = $infofieldDNI->userid;
}

$userObj = $DB->get_record('user', array('id'=>$userId));
complete_user_login($userObj);

$returnArr['status'] = true;
$returnArr['data'] = $userObj;

header('Content-type: application/json');
echo json_encode($returnArr);
exit();