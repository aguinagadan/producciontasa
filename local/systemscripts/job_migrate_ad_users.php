<?php
global $CFG, $DB;

require('../../config.php');
require_once($CFG->dirroot.'/user/profile/lib.php');

echo 'test';
exit;

function getZonaPorDivision($division) {
	$returnValue = '';

	$divisionNorte = ['Chimbote', 'Malabrigo', 'Zananco', 'Astillero'];
	$divisionNorte = array_map('strtolower', $divisionNorte);

	$divisionCentro = ['Central pesca','CHD','Callao norte', 'Pucusana', 'Supe', 'Vegueta'];
	$divisionCentro = array_map('strtolower', $divisionCentro);

	$divisionSur = ['Ático','Atico','Pisco norte','Pisco sur', 'Matarani'];
	$divisionSur = array_map('strtolower', $divisionSur);

	$divisionCorporativo = ['Administración central','Administracion central','Administración central callao','Administracion central callao'];
	$divisionCorporativo = array_map('strtolower', $divisionCorporativo);

	if(in_array(strtolower($division), $divisionNorte)) {
		$returnValue = 'Norte';
	} else if(in_array(strtolower($division), $divisionCentro)) {
		$returnValue = 'Centro';
	} else if(in_array(strtolower($division), $divisionSur)) {
		$returnValue = 'Sur';
	} else if(in_array(strtolower($division), $divisionCorporativo)) {
		$returnValue = 'Corporativo';
	}

	return $returnValue;
}
function execCurl($data) {
	$curl = curl_init();

	$url = $data['url'];
	$postFields = $data['postFields'];
	$httpHeader = $data['httpHeader'];
	$httpMethod = $data['httpMethod'];

	$curlSetOptArray = array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => $httpMethod
	);

	if($httpMethod == 'POST') {
		$curlSetOptArray[CURLOPT_POSTFIELDS] = $postFields;
	}
	$curlSetOptArray[CURLOPT_HTTPHEADER] = $httpHeader;

	curl_setopt_array($curl, $curlSetOptArray);
	$response = curl_exec($curl);
	curl_close($curl);
	$responseData = json_decode($response,true);
	return $responseData;
}
function getADToken() {
	$data = array(
		'url' => 'https://login.microsoftonline.com/b7e26f48-2292-4a14-a355-1aeb8489ae3d/oauth2/token',
		'postFields' => http_build_query(array('grant_type' => 'client_credentials',
			'client_id' => '57f43017-1336-4356-8e65-c81a52eda0f3',
			'client_secret' => 'p._o2p72hK~C9N_op.z_YR93P~z~SjZijz',
			'scope' => 'https://graph.microsoft.com/.default'), '', '&'),
		'httpMethod' => 'POST',
		'httpHeader' => array('host: login.microsoftonline.com',
			'Content-Type: application/x-www-form-urlencoded',
			'Cookie: buid=0.AQYASG_it5IiFEqjVRrrhImuPRgFD1jTGCJMjrrTt_PN72QGAAA.AQABAAEAAAAGV_bv21oQQ4ROqh0_1-tAW_7_lkPgDNNQcc9ndJ6-VT_fKycsxUQA_fsiaenVHh0m1dZmFiOVou0VgVUcdSWQcKUXNWy0yeSTtMjrE4vBvIZsvOjiuXWYPgfnevpPNZAgAA; fpc=AlKOys_Nd-FDqTUucSXhED6Lv60HAQAAAEAZ29YOAAAA; x-ms-gateway-slice=estsfd; stsservicecookie=estsfd')
	);

	$responseData = execCurl($data);
	return $responseData['access_token'];
}
function getADUsers($key, $skipToken='') {
	if($key>0) {
		$skipToken = '&$skiptoken='.$skipToken;
	}

	$data = array(
		'url' => 'https://graph.windows.net/b7e26f48-2292-4a14-a355-1aeb8489ae3d/users?api-version=1.6'.$skipToken,
		'httpMethod' => 'GET',
		'httpHeader' => array("Authorization: ". getADToken())
	);
	$responseData = execCurl($data);
	return $responseData;
}
function updateUser($user, $userAD) {

	$dni           = !empty($userAD['extension_f356ba22a23b4c2fb35162e63d13246c_userDocumentNumber']) ? $userAD['extension_f356ba22a23b4c2fb35162e63d13246c_userDocumentNumber'] : '';

	$nroTrabajador = !empty($userAD['extension_f356ba22a23b4c2fb35162e63d13246c_userSAPR3Id']) ?
		$userAD['extension_f356ba22a23b4c2fb35162e63d13246c_userSAPR3Id'] : '';

	$gerencia      = !empty($userAD['extension_f356ba22a23b4c2fb35162e63d13246c_userHierarchyManagerDesc']) ? $userAD['extension_f356ba22a23b4c2fb35162e63d13246c_userHierarchyManagerDesc'] : 'No especificado';

	$division      = !empty($userAD['extension_f356ba22a23b4c2fb35162e63d13246c_plantDescription']) ? $userAD['extension_f356ba22a23b4c2fb35162e63d13246c_plantDescription'] : '';

	$zona          = getZonaPorDivision($division);

	$areaFuncional = !empty($userAD['department']) ?
		$userAD['department'] : '';

	$tipoEmpleado  = !empty($userAD['extension_f356ba22a23b4c2fb35162e63d13246c_userCompanyType']) ? $userAD['extension_f356ba22a23b4c2fb35162e63d13246c_userCompanyType'] : '';

	$posicion      = !empty($userAD['jobTitle']) ? $userAD['jobTitle'] : '';

	$user->profile_field_dni = $dni;
	$user->profile_field_codigo = $nroTrabajador;
	$user->profile_field_gerencia = $gerencia;
	$user->profile_field_zona = $zona;
	$user->profile_field_division = $division;
	$user->profile_field_area_funcional = $areaFuncional;
	$user->profile_field_personal = $tipoEmpleado;
	$user->profile_field_posicion = $posicion;

	profile_save_data($user);
}

$key = 0;
$skipToken = '';
$usersValues = array();
$allUsers = array();

while(true) {
	if($key>1 && $skipToken=='') {
		break;
	}
	$allUsers[] = getADUsers($key, $skipToken);
	$needle = '$skiptoken=';
	$skipToken = substr($allUsers[$key]['odata.nextLink'], strpos($allUsers[$key]['odata.nextLink'], $needle) + strlen($needle));
	$key++;
}

$count = 0;

foreach($allUsers as $allUser) {
	foreach($allUser['value'] as $key=>$val) {
		$usersValues[$count] = $val;
		$count++;
	}
}

var_dump($usersValues);
exit;

foreach($usersValues as $key=>$userAD) {
	$userPrincipalName = $userAD['userPrincipalName'];

	$user = $DB->get_record('user', array('username' => $userPrincipalName));

	if(empty($user) || !$user) {
		continue;
	}

	$userMainDataObj = new stdClass();
	$userMainDataObj->id = $user->id;
	$userMainDataObj->firstname = $userAD['givenName'];
	$userMainDataObj->lastname = $userAD['surname'];
	$userMainDataObj->email = $userAD['mail'];

	$DB->update_record('user', $userMainDataObj);

	//consultar: filtrando si tiene datos extra (?)
	if(
		isset($userAD['extension_f356ba22a23b4c2fb35162e63d13246c_userDocumentNumber'])
	) {
		updateUser($user, $userAD);
	}
}