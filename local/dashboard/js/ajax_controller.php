<?php
error_reporting(E_ALL);

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/enrol/externallib.php');
require_once($CFG->dirroot. '/course/lib.php');
require_once($CFG->dirroot . '/user/profile/lib.php');

use block_xp\local\xp\level_with_name;
use core_completion\progress;

try {
	$details = $_POST;
	$returnArr = array();

	if (!isset($_REQUEST['request_type']) || strlen($_REQUEST['request_type']) == false) {
		throw new Exception();
	}

	switch ($_REQUEST['request_type']) {
		case 'obtenerUserCursos':
			$returnArr = ObtenerUserCursos();
			break;
		case 'getGerencias':
			$returnArr = getGerencias($details['courseId']);
			break;
		case 'getAreas':
			$returnArr = getAreas($details['courseId']);
			break;
		case 'getZonas':
			$returnArr = getZonas($details['courseId']);
			break;
		case 'getCursoTotals':
			$returnArr = getCursoTotals($details['courseId']);
			break;
	}
} catch (Exception $e) {
	$returnArr['status'] = false;
	$returnArr['data'] = $e->getMessage();
}

header('Content-type: application/json');

echo json_encode($returnArr);
exit();

function ObtenerUserCursos() {
	global $USER;
	$allCourses = enrol_get_users_courses($USER->id, true);

	foreach($allCourses as $course) {
		if($course->visible == 0) {
			continue;
		}
		$courses[] = [
			'name'=> $course->fullname,
			'id'=> $course->id
		];
	}

	$response['status'] = true;
	$response['data'] = $courses;

	return $response;
}

function getGerencias($courseId) {
	$course = get_course($courseId);
	$gerencias = array();

	$context = CONTEXT_COURSE::instance($courseId);
	$users = get_enrolled_users($context);

	foreach($users as $key=>$user) {
		profile_load_custom_fields($user);
		$gerencia = $user->profile['gerencia'];
		if(!empty($gerencia)) {
			$progress = round(progress::get_course_progress_percentage($course, $user->id));
			$gerencias[$gerencia]['nombre'] = $gerencia;
			$gerencias[$gerencia]['total']++;
			if($progress == 100) {
				$gerencias[$gerencia]['nro_completado']++;
			} else {
				$personaIds[] = $user->id;
			}
		}
	}

	foreach($gerencias as $key=>$gerencia) {
		$return[] = array(
			'name' => $gerencia['nombre'],
			'porcent' => round(($gerencia['nro_completado']/$gerencia['total'])*100)
		);
	}

	$response['status'] = true;
	$response['data'] = $return;

	return $response;
}

function getAreas($courseId) {
	$course = get_course($courseId);
	$areas = array();

	$context = CONTEXT_COURSE::instance($courseId);
	$users = get_enrolled_users($context);

	foreach($users as $key=>$user) {
		profile_load_custom_fields($user);
		$area = $user->profile['area_funcional'];
		if(!empty($area)) {
			$progress = round(progress::get_course_progress_percentage($course, $user->id));
			$areas[$area]['nombre'] = $area;
			$areas[$area]['total']++;
			if($progress == 100) {
				$areas[$area]['nro_completado']++;
			} else {
				$personaIds[] = $user->id;
			}
		}
	}

	foreach($areas as $key=>$area) {
		$return[] = array(
			'name' => $area['nombre'],
			'porcent' => round(($area['nro_completado']/$area['total'])*100)
		);
	}

	$response['status'] = true;
	$response['data'] = $return;

	return $response;
}

function getZonas($courseId) {
	$course = get_course($courseId);
	$zonas = array();
	$personaIds = array();

	$context = CONTEXT_COURSE::instance($courseId);
	$users = get_enrolled_users($context);

	foreach($users as $key=>$user) {
		profile_load_custom_fields($user);
		$zona = $user->profile['zona'];
		if(!empty($zona)) {
			$progressZonas = round(progress::get_course_progress_percentage($course, $user->id));
			$zonas[$zona]['nombre'] = $zona;
			$zonas[$zona]['total']++;
			if($progressZonas == 100) {
				$zonas[$zona]['nro_completado']++;
			} else {
				$personaIds[] = $user->id;
			}
		}
	}

	foreach($zonas as $key=>$zona) {
		$return[] = array(
			'name' => $zona['nombre'],
			'porcent' => round(($zona['nro_completado']/$zona['total'])*100)
		);
	}

	$response['status'] = true;
	$response['data'] = $return;

	return $response;
}

function getCursoTotals($courseId) {
	$course = get_course($courseId);
	$total = 0;
	$completed = 0;

	$context = CONTEXT_COURSE::instance($courseId);
	$users = get_enrolled_users($context);

	foreach($users as $key=>$user) {
		$progress = round(progress::get_course_progress_percentage($course, $user->id));
		$completed++;
		if($progress == 100) {
			$total++;
		}
	}

	$response['status'] = true;
	$response['total'] = $total;
	$response['completed'] = $completed;

	return $response;
}