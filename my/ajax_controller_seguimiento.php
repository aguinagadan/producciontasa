<?php
error_reporting(E_ALL);
ini_set("display_errors","on");

global $CFG;

require_once '../my/classes/model/Seguimiento.php';
require_once($CFG->dirroot . '/enrol/externallib.php');
require_once($CFG->dirroot. '/course/lib.php');

use core_completion\progress;
use Seguimiento\Model\Seguimiento as SeguimientoModel;
global $details;

try {
	$details = $_POST;
	$returnArr = array();
	switch ($details['request_type']) {
		case 'get_courses_by_category':
			$returnArr = get_courses_by_category($details['catId']);
			break;
		case 'ss-main-container-zonas-areas-detail':
			$returnArr = get_zonas_areas_detail();
			break;
		case 'ss-main-container-zonas-detail':
			$returnArr = get_zonas_detail();
			break;
		case 'ss-main-container-division':
			$returnArr = get_divisiones_detail();
			break;
		case 'ss-main-container-tipo-personal':
			$returnArr = get_tipo_personal_by_division_detail();
			break;
		case 'ss-main-container-personal':
			$returnArr = get_personal_detail();
			break;
	}
} catch (Exception $e) {
	$returnArr['status'] = false;
	$returnArr['data'] = $e->getMessage();
}

header('Content-type: application/json');
echo json_encode($returnArr);
exit();

function getProgressBarDetailSeguimientoHtml($value, $courseId=null) {
	$returnHTML = '<div course-id="'. $courseId .'" class="element-progress-bar col-sm" style="max-width: 3.3%; color: #526069;">'. round($value,0) .'%</div>';
	$returnHTML .= '<div course-id="'. $courseId .'" class="element-progress-bar col-sm-7">'. getProgressBarDetailSeguimiento($value) .'</div>';

	return $returnHTML;
}

function getProgressBarDetailSeguimiento($value) {
	return '<div class="block_xp-level-progress progress-non-zero d-progress-bar-level-course" style="width: 95%;">
<div class="xp-bar-wrapper d-progress-bar-level-course" role="progressbar" aria-valuenow="'. $value .'" aria-valuemin="0" aria-valuemax="100" style="width: ' . $value .'%; margin: 0 !important;">
<div class="xp-bar d-xp-bar-course"></div>
</div>
</div>';
}

function get_courses_by_category($catId) {
	$segModel = new SeguimientoModel();
	$coursesArr = $segModel->GetCoursesByCategory($catId);
	$returnHTML = '';
	$progress = 0;

	foreach($coursesArr as $course) {
		$returnHTML .= '<div data-id="'. $course->category .'" class="ss-container ss-main-container-course row ss-m-b-05">';
		$returnHTML .= '<div zona-name="zona-default" course-id="'. $course->id .'" data-open="ss-main-container-zonas-areas-detail" data-id="'. $course->id .'" class="col-sm element-clickable" style="cursor: pointer;">'.$course->fullname.'</div>';
		$returnHTML .= '<div class="col-sm" style="max-width: 3.3%; color: #526069;">'. round($progress,0) .'%</div>';
		$returnHTML .= '<div class="col-sm-7">'. getProgressBarDetailSeguimiento($progress) .'</div>';
		$returnHTML .= '</div>';
	}


	$response['status'] = true;
	$response['data'] = $coursesArr;
	$response['data']['html'] = $returnHTML;

	return $response;
}

function get_zonas_areas_detail() {
	global $details;
	$courseId = $details['courseId'];
	$returnHTML = '';

	$seguimientoDetails = array('Seguimiento por zonas', 'Seguimiento por área funcional');

	foreach($seguimientoDetails as $seguimientoDetail) {
		if($seguimientoDetail == 'Seguimiento por zonas') {
			$dataOpen = 'ss-main-container-zonas-detail';
			$zona = 'zona-default-zonas';
			//$personaIds = getSeguimientoDetailsZonaProgress($course, $enrolledUsersArray)['ids'];
			$progress = 50;
		} elseif($seguimientoDetail == 'Seguimiento por área funcional') {
			$dataOpen = 'ss-main-container-areas-detail';
			$zona = 'zona-default-areas';
			//$personaIds = getSeguimientoDetailsAreaProgress($course, $enrolledUsersArray)['ids'];
			$progress = 50;
		}

		$returnHTML .=	'<div zona-name="zona-default" course-id="'. $courseId .'" data-open="'. $dataOpen .'" class="ss-container ss-main-container-seguimiento-detail row ss-m-b-05">';
		//$returnHTML .= '<input class="personaIds" type="hidden" value="'. $personaIds .'">';
		$returnHTML .= '<div zona-name="'. $zona .'" course-id="'. $courseId .'" data-open="'. $dataOpen .'"  class="col-sm element-clickable" style="cursor: pointer;">'. $seguimientoDetail .'</div>';

		$returnHTML.= getProgressBarDetailSeguimientoHtml($progress);
		$returnHTML.= '-';
		$returnHTML.= '</div>';
	}

	$response['status'] = true;
	$response['data']['html'] = $returnHTML;

	return $response;
}

function get_zonas_detail() {
	global $details;
	$courseId = $details['courseId'];
	$course = get_course($courseId);
	$returnHTML = '';
	$zonas = array();

	$users = core_enrol_external::get_enrolled_users($courseId);

	foreach($users as $user) {
		//3: zonas
		if(!empty($user['customfields'][3]['value'])) {
			$zonas[] = $user['customfields'][3]['value'];
		}
	}

	$zonas = array_unique($zonas);

	foreach($zonas as $zona) {
		$progress = 0;
		$contUsers = 0;
		//$personaIds = getZonasProgressById($course, $zona, $enrolledUsersArray)['ids'];
		$returnHTML .= '<div zona-name="zona-default-zonas" course-id="'. $courseId .'" data-open="ss-main-container-division" class="ss-container ss-main-container-zonas-detail row ss-m-b-05">';
		//$returnHTML .= '<input class="personaIds" type="hidden" value="'. $personaIds .'">';
		$returnHTML .= '<div zona-name="'. $zona .'" course-id="'. $courseId .'" data-open="ss-main-container-division" class="col-sm element-clickable" style="cursor: pointer;">' . $zona . '</div>';

//		foreach($users as $user) {
//			if($user['customfields'][3]['value'] == $zona) {
//				$progress += round(progress::get_course_progress_percentage($course, $user['id']));
//				$contUsers++;
//			}
//		}

//		$progress = round($progress/$contUsers);
		$progress = 50;

		$returnHTML .= getProgressBarDetailSeguimientoHtml($progress);

		$returnHTML .= '</div>';
	}

	$response['status'] = true;
	$response['data']['html'] = $returnHTML;

	return $response;
}

function get_divisiones_detail() {
	global $details;
	$courseId = $details['courseId'];
	$course = get_course($courseId);
	$zona = $details['zona'];
	$returnHTML = '';
	$divisiones = array();

	$users = core_enrol_external::get_enrolled_users($courseId);

	foreach($users as $user) {
		//4: divisiones
		if(!empty($user['customfields'][4]['value'])) {
			if($user['customfields'][3]['value'] == $zona) {
				$divisiones[] = $user['customfields'][4]['value'];
			}
		}
	}

	$divisiones = array_unique($divisiones);

	foreach($divisiones as $division) {
		$progress = 0;
		$contUsers = 0;
		//$personaIds = getDivisionesPorZonaProgress($course, $zona, $enrolledUsersArray)['ids'];
		$returnHTML .= '<div zona-name="'. $zona .'" course-id="'. $courseId .'" data-open="ss-main-container-tipo-personal" class="ss-container ss-main-container-division row ss-m-b-05">';
		//$returnHTML .= '<input class="personaIds" type="hidden" value="'. $personaIds .'">';
		$returnHTML .= '<div division-name="'. $division . '" zona-name="'. $zona .'" course-id="'. $courseId .'" data-open="ss-main-container-tipo-personal" class="col-sm element-clickable" style="cursor: pointer;">'. $division .'</div>';
//
//		foreach($users as $user) {
//			if(
//				$user['customfields'][3]['value'] == $zona && $user['customfields'][4]['value'] == $division) {
//				$progress += round(progress::get_course_progress_percentage($course, $user['id']));
//				$contUsers++;
//			}
//		}

		//$progress = round($progress/$contUsers);
		$progress = 50;

		$returnHTML.= getProgressBarDetailSeguimientoHtml($progress);
		$returnHTML .= '</div>';
	}

	$response['status'] = true;
	$response['data']['html'] = $returnHTML;

	return $response;
}

function get_tipo_personal_by_division_detail() {
	global $details;
	$courseId = $details['courseId'];
	$course = get_course($courseId);
	$zona = $details['zona'];
	$division = $details['division'];
	$returnHTML = '';
	$tipoPersonalArr = array();

	$users = core_enrol_external::get_enrolled_users($courseId);

	foreach($users as $user) {
		//6: tipo personal
		if(!empty($user['customfields'][6]['value'])) {
			if($user['customfields'][3]['value'] == $zona && $user['customfields'][4]['value'] == $division) {
				$tipoPersonalArr[] = $user['customfields'][6]['value'];
			}
		}
	}

	$tipoPersonalArr = array_unique($tipoPersonalArr);

	foreach($tipoPersonalArr as $tipoPersonal) {
		$progress = 0;
		$contUsers = 0;
		//$personaIds = getTipoPersonalPorDivisionProgress($course, $division, $zona, $enrolledUsersArray)['ids'];
		$returnHTML .= '<div zona-name="' . $zona . '" course-id="'. $courseId .'" data-open="ss-main-container-personal" class="ss-container ss-main-container-tipo-personal row ss-m-b-05">';
		//$returnHTML .= '<input class="personaIds" type="hidden" value="'. $personaIds .'">';
		$returnHTML .= '<div tipo-personal-name="' . $tipoPersonal . '" division-name="' . $division . '" zona-name="' . $zona . '" course-id="'. $courseId .'" data-open="ss-main-container-personal" class="col-sm element-clickable" style="cursor: pointer;">'. $tipoPersonal .'</div>';

//		foreach($users as $user) {
//			if(
//				$user['customfields'][3]['value'] == $zona && $user['customfields'][4]['value'] == $division  && $user['customfields'][6]['value'] == $tipoPersonal
//			) {
//				$progress += round(progress::get_course_progress_percentage($course, $user['id']));
//				$contUsers++;
//			}
//		}

//		$progress = round($progress/$contUsers);
		$progress = 50;

		$returnHTML.= getProgressBarDetailSeguimientoHtml($progress);
		$returnHTML .= '</div>';
	}

	$response['status'] = true;
	$response['data']['html'] = $returnHTML;

	return $response;
}

function get_personal_detail() {
	global $details;

	$courseId = $details['courseId'];
	$course = get_course($courseId);
	$zona = $details['zona'];
	$division = $details['division'];
	$tipoPersonal = $details['tipoPersonal'];
	$returnHTML = '';
	$personalArr = array();

	$users = core_enrol_external::get_enrolled_users($courseId);

	foreach($users as $key=>$user) {
		//6: tipo personal
		if(!empty($user['fullname'])) {
			if(
				$user['customfields'][3]['value'] == $zona &&
				$user['customfields'][4]['value'] == $division &&
				$user['customfields'][6]['value'] == $tipoPersonal
			) {
				$personalArr[$key]['id'] = $user['id'];
				$personalArr[$key]['firstname'] = $user['firstname'];
				$personalArr[$key]['fullname'] = $user['fullname'];
			}
		}
	}

	$personalArr = array_unique($personalArr);

	foreach($personalArr as $personal) {
		$returnHTML.= '<div zona-name="'. $tipoPersonal . $division . $zona .'" course-id="'. $courseId .'" class="ss-container ss-main-container-personal row ss-m-b-05">
											<input class="personaIds" type="hidden" value="'.$personal['id'].'">
											<div data-id="'. $personal['id'] . '" data-val="'.strtoupper($personal['firstname']).'" class="col-sm personal-clickable" style="cursor: pointer; font-size: 18px;" data-toggle="modal" data-target="#myModal">'. $personal['fullname'] .'</div>
											<div data-id="'. $personal['id'] . '" data-val="'.strtoupper($personal['firstname']).'" class="col-xs logo-mail-clickable" data-toggle="modal" data-target="#myModal" style="cursor: pointer;">
													<img src="../theme/remui/pix/ic_email_24px.png">
											</div>';
		$progress = round(progress::get_course_progress_percentage($course, $personal['id']));
		$returnHTML.= getProgressBarDetailSeguimientoHtml($progress);
		$returnHTML.= '</div>';
	}

	$response['status'] = true;
	$response['data']['html'] = $returnHTML;

	return $response;
}

function get_zonas_1_percentage($courseId) {
	$course = get_course($courseId);
	$users = core_enrol_external::get_enrolled_users($courseId);
	$totalPercentage = 0;
	$totalPercentageCount = 0;

	foreach($users as $user) {
		//3: zonas
		if(!empty($user['customfields'][3]['value'])) {
			$zonas[] = $user['customfields'][3]['value'];
		}
	}

	$zonas = array_unique($zonas);

	foreach($zonas as $zona) {
		$progress = 0;
		$contUsers = 0;

		foreach($users as $user) {
			if($user['customfields'][3]['value'] == $zona) {
				$progress += round(progress::get_course_progress_percentage($course, $user['id']));
				$contUsers++;
			}
		}
		$progress = round($progress/$contUsers);
		$totalPercentage += $progress;
		$totalPercentageCount++;
	}

	return round($totalPercentage/$totalPercentageCount);
}