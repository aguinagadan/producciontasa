<?php
error_reporting(E_ALL);
ini_set("display_errors","on");

global $CFG;

require_once '../my/classes/model/Seguimiento.php';
require_once($CFG->dirroot . '/enrol/externallib.php');
require_once($CFG->dirroot. '/course/lib.php');
require_once($CFG->dirroot . '/user/profile/lib.php');

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
		case 'ss-main-container-areas-detail':
			$returnArr = get_areas_funcionales_detail();
			break;
		case 'ss-main-container-division':
			$returnArr = get_divisiones_detail();
			break;
		case 'ss-main-container-tipo-personal':
			$returnArr = get_tipo_personal_by_division_detail();
			break;
		case 'ss-main-container-tipo-personal-area':
			$returnArr = get_tipo_personal_by_area_funcional_detail();
			break;
		case 'ss-main-container-personal':
			$returnArr = get_personal_detail();
			break;
		case 'ss-main-container-personal-area':
			$returnArr = get_personal_by_area_detail();
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
		//$courseStats = getCourseStats($course);
		//$progress = $courseStats['studentcompleted']/count($courseStats['enrolledusers']);
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
	$course = getCourseById($courseId);
	$returnHTML = '';
	$completedZonas = 0;
	$totalZonas = 0;
	$completedAreas = 0;
	$totalAreas = 0;

	$context = CONTEXT_COURSE::instance($courseId);
	$users = get_enrolled_users($context);

	foreach($users as $key=>$user) {
		profile_load_custom_fields($user);
		$zona = $user->profile['zona'];
		if(!empty($zona)) {
			$totalZonas++;
			$progressZonas = round(progress::get_course_progress_percentage($course, $user->id));
			if($progressZonas == 100) {
				$completedZonas++;
			}
		}
		$area = $user->profile['area_funcional'];
		if(!empty($area)) {
			$totalAreas++;
			$progressAreas = round(progress::get_course_progress_percentage($course, $user->id));
			if($progressAreas == 100) {
				$completedAreas++;
			}
		}
	}

	$seguimientoDetails = array('Seguimiento por zonas', 'Seguimiento por área funcional');

	foreach($seguimientoDetails as $seguimientoDetail) {
		if($seguimientoDetail == 'Seguimiento por zonas') {
			$dataOpen = 'ss-main-container-zonas-detail';
			$zona = 'zona-default-zonas';
			//$personaIds = getSeguimientoDetailsZonaProgress($course, $enrolledUsersArray)['ids'];
			$progress = round($completedZonas/$totalZonas);
		} elseif($seguimientoDetail == 'Seguimiento por área funcional') {
			$dataOpen = 'ss-main-container-areas-detail';
			$zona = 'zona-default-areas';
			//$personaIds = getSeguimientoDetailsAreaProgress($course, $enrolledUsersArray)['ids'];
			$progress = round($completedAreas/$totalAreas);
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
	$course = getCourseById($courseId);
	$returnHTML = '';
	$zonas = array();

	$context = CONTEXT_COURSE::instance($courseId);
	$users = get_enrolled_users($context);

	foreach($users as $key=>$user) {
		profile_load_custom_fields($user);
		$zona = $user->profile['zona'];
		if(!empty($zona)) {
			$progress = round(progress::get_course_progress_percentage($course, $user->id));
			$zonas[$zona]['nombre'] = $zona;
			$zonas[$zona]['progreso'][] = $progress;
		}
	}

	foreach($zonas as $zona) {
		//$personaIds = getZonasProgressById($course, $zona, $enrolledUsersArray)['ids'];
		$returnHTML .= '<div zona-name="zona-default-zonas" course-id="'. $courseId .'" data-open="ss-main-container-division" class="ss-container ss-main-container-zonas-detail row ss-m-b-05">';
		//$returnHTML .= '<input class="personaIds" type="hidden" value="'. $personaIds .'">';
		$returnHTML .= '<div zona-name="'. $zona['nombre'] .'" course-id="'. $courseId .'" data-open="ss-main-container-division" class="col-sm element-clickable" style="cursor: pointer;">' . $zona['nombre'] . '</div>';

		$progress = round(array_sum($zona['progreso'])/count($zona['progreso']));
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
	$course = getCourseById($courseId);
	$zona = $details['zona'];
	$returnHTML = '';
	$divisiones = array();

	$context = CONTEXT_COURSE::instance($courseId);
	$users = get_enrolled_users($context);

	foreach($users as $key=>$user) {
		profile_load_custom_fields($user);
		if(!empty($user->profile['division'])) {
			if($user->profile['zona'] == $zona) {
				$division = $user->profile['division'];
				$progress = round(progress::get_course_progress_percentage($course, $user->id));
				$divisiones[$division]['nombre'] = $division;
				$divisiones[$division]['progreso'][] = $progress;
			}
		}
	}

	foreach($divisiones as $division) {
		//$personaIds = getDivisionesPorZonaProgress($course, $zona, $enrolledUsersArray)['ids'];
		$returnHTML .= '<div zona-name="'. $zona .'" course-id="'. $courseId .'" data-open="ss-main-container-tipo-personal" class="ss-container ss-main-container-division row ss-m-b-05">';
		//$returnHTML .= '<input class="personaIds" type="hidden" value="'. $personaIds .'">';
		$returnHTML .= '<div division-name="'. $division['nombre'] . '" zona-name="'. $zona .'" course-id="'. $courseId .'" data-open="ss-main-container-tipo-personal" class="col-sm element-clickable" style="cursor: pointer;">'. $division['nombre'] .'</div>';

		$progress = round(array_sum($division['progreso'])/count($division['progreso']));
		$returnHTML.= getProgressBarDetailSeguimientoHtml($progress);
		$returnHTML .= '</div>';
	}

	$response['status'] = true;
	$response['data']['html'] = $returnHTML;

	return $response;
}

function get_tipo_personal_by_division_detail() {
	global $details;
	$returnHTML = '';
	$tipoPersonalArr = array();
	$courseId = $details['courseId'];
	$course = getCourseById($courseId);
	$zona = $details['zona'];
	$division = $details['division'];

	$context = CONTEXT_COURSE::instance($courseId);
	$users = get_enrolled_users($context);

	foreach($users as $key=>$user) {
		profile_load_custom_fields($user);
		if(!empty($user->profile['personal'])) {
			if($user->profile['zona'] == $zona && $user->profile['division'] == $division) {
				$tipoPersonal = $user->profile['personal'];
				$progress = round(progress::get_course_progress_percentage($course, $user->id));
				$tipoPersonalArr[$tipoPersonal]['nombre'] = $tipoPersonal;
				$tipoPersonalArr[$tipoPersonal]['progreso'][] = $progress;
			}
		}
	}

	foreach($tipoPersonalArr as $tipoPersonal) {
		//$personaIds = getTipoPersonalPorDivisionProgress($course, $division, $zona, $enrolledUsersArray)['ids'];
		$returnHTML .= '<div zona-name="' . $zona . '" course-id="'. $courseId .'" data-open="ss-main-container-personal" class="ss-container ss-main-container-tipo-personal row ss-m-b-05">';
		//$returnHTML .= '<input class="personaIds" type="hidden" value="'. $personaIds .'">';
		$returnHTML .= '<div tipo-personal-name="' . $tipoPersonal['nombre'] . '" division-name="' . $division . '" zona-name="' . $zona . '" course-id="'. $courseId .'" data-open="ss-main-container-personal" class="col-sm element-clickable" style="cursor: pointer;">'. $tipoPersonal['nombre'] .'</div>';

		$progress = round(array_sum($tipoPersonal['progreso'])/count($tipoPersonal['progreso']));
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

	$context = CONTEXT_COURSE::instance($courseId);
	$users = get_enrolled_users($context);

	foreach($users as $key=>$user) {
		profile_load_custom_fields($user);
		if(
			$user->profile['zona'] == $zona &&
			$user->profile['division'] == $division &&
			$user->profile['personal'] == $tipoPersonal
		) {
			$personalArr[$key]['id'] = $user->id;
			$personalArr[$key]['firstname'] = $user->firstname;
			$personalArr[$key]['lastname'] = $user->lastname;
			$personalArr[$key]['fullname'] = $user->fullname;
		}
	}

	foreach($personalArr as $personal) {
		$returnHTML.= '<div zona-name="'. $tipoPersonal . $division . $zona .'" course-id="'. $courseId .'" class="ss-container ss-main-container-personal row ss-m-b-05">
											<input class="personaIds" type="hidden" value="'.$personal['id'].'">
											<div data-id="'. $personal['id'] . '" data-val="'.strtoupper($personal['firstname']).'" class="col-sm personal-clickable" style="cursor: pointer; font-size: 18px;" data-toggle="modal" data-target="#myModal">'. $personal['firstname'] . ' ' . $personal['lastname'] . '</div>
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

function get_areas_funcionales_detail() {
	global $details;
	$areas = array();
	$returnHTML = '';
	$courseId = $details['courseId'];
	$course = getCourseById($courseId);
	$context = CONTEXT_COURSE::instance($courseId);
	$users = get_enrolled_users($context);

	foreach($users as $key=>$user) {
		profile_load_custom_fields($user);
		$area = $user->profile['area_funcional'];
		if(!empty($area)) {
			$progress = round(progress::get_course_progress_percentage($course, $user->id));
			$areas[$area]['nombre'] = $area;
			$areas[$area]['progreso'][] = $progress;
		}
	}

	foreach($areas as $area) {
		//$personaIds = getAreaProgress($course, $area, $enrolledUsersArray)['ids'];
		$returnHTML.= '<div zona-name="zona-default-areas" course-id="'. $courseId .'" data-open="ss-main-container-tipo-personal-area" class="ss-container ss-main-container-areas-detail row ss-m-b-05">';
		//$returnHTML .= '<input class="personaIds" type="hidden" value="'. $personaIds .'">';
		$returnHTML .= '<div area-name="'. $area['nombre'] .'" course-id="'. $courseId .'" data-open="ss-main-container-tipo-personal-area" class="col-sm element-clickable" style="cursor: pointer;">'. $area['nombre'] .'</div>';

		$progress = round(array_sum($area['progreso'])/count($area['progreso']));
		$returnHTML.= getProgressBarDetailSeguimientoHtml($progress);
		$returnHTML.= '</div>';
	}

	$response['status'] = true;
	$response['data']['html'] = $returnHTML;

	return $response;
}

function get_tipo_personal_by_area_funcional_detail() {
	global $details;
	$courseId = $details['courseId'];
	$area = $details['area'];
	$returnHTML = '';
	$tipoPersonalArr = array();
	$course = getCourseById($courseId);

	$context = CONTEXT_COURSE::instance($courseId);
	$users = get_enrolled_users($context);

	foreach($users as $key=>$user) {
		profile_load_custom_fields($user);
		if(!empty($user->profile['personal'])) {
			if($user->profile['area_funcional'] == $area) {
				$tipoPersonal = $user->profile['personal'];
				$progress = round(progress::get_course_progress_percentage($course, $user->id));
				$tipoPersonalArr[$tipoPersonal]['nombre'] = $tipoPersonal;
				$tipoPersonalArr[$tipoPersonal]['progreso'][] = $progress;
			}
		}
	}

	foreach($tipoPersonalArr as $tipoPersonal) {
		//$personaIds = getTipoPersonalPorDivisionProgress($course, $division, $zona, $enrolledUsersArray)['ids'];
		$returnHTML .= '<div area-name="' . $area . '" course-id="'. $courseId .'" data-open="ss-main-container-personal-area" class="ss-container ss-main-container-tipo-personal row ss-m-b-05">';
		//$returnHTML .= '<input class="personaIds" type="hidden" value="'. $personaIds .'">';
		$returnHTML .= '<div tipo-personal-name="' . $tipoPersonal . '" area-name="' . $area . '" course-id="'. $courseId .'" data-open="ss-main-container-personal-area" class="col-sm element-clickable" style="cursor: pointer;">'. $tipoPersonal .'</div>';

		$progress = round(array_sum($tipoPersonal['progreso'])/count($tipoPersonal['progreso']));

		$returnHTML.= getProgressBarDetailSeguimientoHtml($progress);
		$returnHTML .= '</div>';
	}

	$response['status'] = true;
	$response['data']['html'] = $returnHTML;

	return $response;
}

function get_personal_by_area_detail() {
	global $details;

	$courseId = $details['courseId'];
	$course = get_course($courseId);
	$area = $details['area'];
	$tipoPersonal = $details['tipoPersonal'];
	$returnHTML = '';
	$personalArr = array();

	$context = CONTEXT_COURSE::instance($courseId);
	$users = get_enrolled_users($context);

	foreach($users as $key=>$user) {
		profile_load_custom_fields($user);
		if(
			$user->profile['area_funcional'] == $area &&
			$user->profile['personal'] == $tipoPersonal
		) {
			$personalArr[$key]['id'] = $user->id;
			$personalArr[$key]['firstname'] = $user->firstname;
			$personalArr[$key]['lastname'] = $user->lastname;
			$personalArr[$key]['fullname'] = $user->fullname;
		}
	}

	foreach($personalArr as $personal) {
		$returnHTML.= '<div zona-name="'. $tipoPersonal . $area .'" course-id="'. $courseId .'" class="ss-container ss-main-container-personal row ss-m-b-05">
											<input class="personaIds" type="hidden" value="'.$personal['id'].'">
											<div data-id="'. $personal['id'] . '" data-val="'.strtoupper($personal['firstname']).'" class="col-sm personal-clickable" style="cursor: pointer; font-size: 18px;" data-toggle="modal" data-target="#myModal">'. $personal['firstname'] . ' ' . $personal['lastname'] . '</div>
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

function getCourseById($courseId) {
	return get_course($courseId);
}

function getCourseStats($course) {
	return \block_remuiblck\coursehandler::get_course_stats($course);
}