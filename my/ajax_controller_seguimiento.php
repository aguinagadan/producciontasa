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
		case 'load_data':
			$returnArr = loadData();
			break;
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

function loadData() {
	global $DB;

	$response = $usersArr = array();
	$progressAVG = 0;

	$categories = $DB->get_records('course_categories');

	foreach ($categories as $category) {
		$cat = \coursecat::get($category->id);
		$coursesArr = $cat->get_courses();

		foreach ($coursesArr as $course) {
			$courseProgress = 0;
			$context = CONTEXT_COURSE::instance($course->id);
			$users = get_enrolled_users($context);
			$usersArr[$category->id][$course->id]['course_name'] = $course->fullname;
			foreach ($users as $key => $user) {
				$user->progress = round(progress::get_course_progress_percentage($course, $user->id));
				$courseProgress += $user->progress;
				$users[$key] = $user;
				profile_load_custom_fields($users[$key]);
			}

			if(count($users) != 0) {
				$progressAVG = $courseProgress / count($users);
			}

			$usersArr[$category->id][$course->id]['course_progress_avg'] = round($progressAVG);
			$usersArr[$category->id][$course->id]['users'] = $users;
		}
	}

	$response['status'] = true;
	$response['data'] = $usersArr;
	return $response;
}

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
	global $DB;
	$segModel = new SeguimientoModel();
	$coursesArr = $segModel->GetCoursesByCategory($catId);
	$returnHTML = '';

	foreach($coursesArr as $course) {
		$progress = 0;
		$contCompleted = 0;
		$returnHTML .= '<div data-id="'. $course->category .'" class="ss-container ss-main-container-course row ss-m-b-05">';

		$new = array();
		$total = count($DB->get_records('course_completions',array('course'=>$course->id)));

		$modules = $DB->get_records_sql("select * from {course_modules} c where c.course = ? AND completion > 0", array($course->id));

		foreach($modules as $mod) {
			$new[] = $mod->id;
		}

		$cantidadModulos = count($new);

		$instring = "('".implode("', '",$new)."')";
		$query = "select c.userid, COUNT(c.userid) as cont from {course_modules_completion} c where c.completionstate>0 AND c.coursemoduleid in $instring GROUP BY userid";
		$results = $DB->get_records_sql($query);

		foreach ($results as $res) {
			if($res->cont == $cantidadModulos) {
				$contCompleted++;
			}
		}

		if($total == 0) {
			$returnHTML .= '<div zona-name="zona-default" course-id="'. $course->id .'" data-open="ss-main-container-zonas-areas-detail" data-id="'. $course->id .'" class="col-sm" style="font-size: 18px;">'.$course->fullname.'</div>';
		} else {
			$progress    = round(($contCompleted/$total)*100);
			$returnHTML .= '<div zona-name="zona-default" course-id="'. $course->id .'" data-open="ss-main-container-zonas-areas-detail" data-id="'. $course->id .'" class="col-sm element-clickable" style="cursor: pointer;">'.$course->fullname.'</div>';
		}
		$returnHTML .= '<div class="col-sm" style="max-width: 3.3%; color: #526069;">'. $progress .'%</div>';
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
	$contZonasCompletados = $contAreasCompletados = $contZonasNoCompletados = $contAreasNoCompletados = 0;
	$totalAreas = 0;
	$zonas = $areas = array();
	$progressZonas = $progressAreas = 0;

	$context = CONTEXT_COURSE::instance($courseId);
	$users = get_enrolled_users($context);

	foreach($users as $key=>$user) {
		profile_load_custom_fields($user);
		$zona = $user->profile['zona'];
		if(!empty($zona)) {
			//$progressZonas = round(progress::get_course_progress_percentage($course, $user->id));
			if($progressZonas == 100) {
				$zonas[$zona]['completado']++;
			} else {
				$zonas[$zona]['no_completado']++;
			}
		}
		$area = $user->profile['area_funcional'];
		if(!empty($area)) {
			$totalAreas++;
			//$progressAreas = round(progress::get_course_progress_percentage($course, $user->id));
			if($progressAreas == 100) {
				$areas[$area]['completado']++;
			} else {
				$areas[$area]['no_completado']++;
			}
		}
	}

	foreach($zonas as $zona) {
		if(isset($zona['completado'])) {
			$contZonasCompletados += $zona['completado'];
		}
		if(isset($zona['no_completado'])) {
			$contZonasNoCompletados += $zona['no_completado'];
		}
	}

	foreach($areas as $area) {
		if(isset($area['completado'])) {
			$contAreasCompletados += $area['completado'];
		}
		if(isset($zona['no_completado'])) {
			$contAreasNoCompletados += $area['no_completado'];
		}
	}

	$seguimientoDetails = array('Seguimiento por zonas', 'Seguimiento por área funcional');

	foreach($seguimientoDetails as $seguimientoDetail) {
		if($seguimientoDetail == 'Seguimiento por zonas') {
			$dataOpen = 'ss-main-container-zonas-detail';
			$zona = 'zona-default-zonas';
			//$personaIds = getSeguimientoDetailsZonaProgress($course, $enrolledUsersArray)['ids'];
			$progress = round(($contZonasCompletados/($contZonasCompletados+$contZonasNoCompletados))*100);
		} elseif($seguimientoDetail == 'Seguimiento por área funcional') {
			$dataOpen = 'ss-main-container-areas-detail';
			$zona = 'zona-default-areas';
			//$personaIds = getSeguimientoDetailsAreaProgress($course, $enrolledUsersArray)['ids'];
			$progress = round(($contAreasCompletados/($contAreasCompletados+$contAreasNoCompletados))*100);
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
	$progressZonas = 0;

	$context = CONTEXT_COURSE::instance($courseId);
	$users = get_enrolled_users($context);

	foreach($users as $key=>$user) {
		profile_load_custom_fields($user);
		$zona = $user->profile['zona'];
		if(!empty($zona)) {
			//$progressZonas = round(progress::get_course_progress_percentage($course, $user->id));
			$zonas[$zona]['nombre'] = $zona;
			$zonas[$zona]['total']++;
			if($progressZonas == 100) {
				$zonas[$zona]['nro_completado']++;
			}
		}
	}


	foreach($zonas as $zona) {
		//$personaIds = getZonasProgressById($course, $zona, $enrolledUsersArray)['ids'];
		$returnHTML .= '<div zona-name="zona-default-zonas" course-id="'. $courseId .'" data-open="ss-main-container-division" class="ss-container ss-main-container-zonas-detail row ss-m-b-05">';
		//$returnHTML .= '<input class="personaIds" type="hidden" value="'. $personaIds .'">';
		$returnHTML .= '<div zona-name="'. $zona['nombre'] .'" course-id="'. $courseId .'" data-open="ss-main-container-division" class="col-sm element-clickable" style="cursor: pointer;">' . $zona['nombre'] . '</div>';

		if(!isset($zona['nro_completado'])) {
			$progress = 0;
		} else {
			$progress = round(($zona['nro_completado']/$zona['total'])*100);
		}

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
	$progress = 0;


	$context = CONTEXT_COURSE::instance($courseId);
	$users = get_enrolled_users($context);

	foreach($users as $key=>$user) {
		profile_load_custom_fields($user);
		if(!empty($user->profile['division'])) {
			if($user->profile['zona'] == $zona) {
				$division = $user->profile['division'];
				//$progress = round(progress::get_course_progress_percentage($course, $user->id));
				$divisiones[$division]['nombre'] = $division;
				$divisiones[$division]['total']++;
				if($progress == 100) {
					$divisiones[$division]['nro_completado']++;
				}
			}
		}
	}

	foreach($divisiones as $division) {
		//$personaIds = getDivisionesPorZonaProgress($course, $zona, $enrolledUsersArray)['ids'];
		$returnHTML .= '<div zona-name="'. $zona .'" course-id="'. $courseId .'" data-open="ss-main-container-tipo-personal" class="ss-container ss-main-container-division row ss-m-b-05">';
		//$returnHTML .= '<input class="personaIds" type="hidden" value="'. $personaIds .'">';
		$returnHTML .= '<div division-name="'. $division['nombre'] . '" zona-name="'. $zona .'" course-id="'. $courseId .'" data-open="ss-main-container-tipo-personal" class="col-sm element-clickable" style="cursor: pointer;">'. $division['nombre'] .'</div>';

		if(!isset($division['nro_completado'])) {
			$progress = 0;
		} else {
			$progress = round(($division['nro_completado']/$division['total'])*100);
		}

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
	$totalTipo = 0;
	$progress = 0;

	$context = CONTEXT_COURSE::instance($courseId);
	$users = get_enrolled_users($context);

	foreach($users as $key=>$user) {
		profile_load_custom_fields($user);
		if(!empty($user->profile['personal'])) {
			if($user->profile['zona'] == $zona && $user->profile['division'] == $division) {
				$totalTipo++;
				$tipoPersonal = $user->profile['personal'];
				//$progress = round(progress::get_course_progress_percentage($course, $user->id));
				$tipoPersonalArr[$tipoPersonal]['nombre'] = $tipoPersonal;
				$tipoPersonalArr[$tipoPersonal]['total']++;
				if($progress == 100) {
					$tipoPersonalArr[$tipoPersonal]['nro_completado']++;
				}
			}
		}
	}

	foreach($tipoPersonalArr as $tipoPersonal) {
		//$personaIds = getTipoPersonalPorDivisionProgress($course, $division, $zona, $enrolledUsersArray)['ids'];
		$returnHTML .= '<div zona-name="' . $zona . '" course-id="'. $courseId .'" data-open="ss-main-container-personal" class="ss-container ss-main-container-tipo-personal row ss-m-b-05">';
		//$returnHTML .= '<input class="personaIds" type="hidden" value="'. $personaIds .'">';
		$returnHTML .= '<div tipo-personal-name="' . $tipoPersonal['nombre'] . '" division-name="' . $division . '" zona-name="' . $zona . '" course-id="'. $courseId .'" data-open="ss-main-container-personal" class="col-sm element-clickable" style="cursor: pointer;">'. $tipoPersonal['nombre'] .'</div>';

		if(!isset($tipoPersonal['nro_completado'])) {
			$progress = 0;
		} else {
			$progress = round(($tipoPersonal['nro_completado']/$tipoPersonal['total'])*100);
		}

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
	$totalAreas = 0;
	$progress = 0;

	foreach($users as $key=>$user) {
		profile_load_custom_fields($user);
		$area = $user->profile['area_funcional'];
		if(!empty($area)) {
			$totalAreas++;
			//$progress = round(progress::get_course_progress_percentage($course, $user->id));
			$areas[$area]['nombre'] = $area;
			$areas[$area]['total']++;
			if($progress == 100) {
				$areas[$area]['nro_completado']++;
			}
		}
	}

	foreach($areas as $area) {
		//$personaIds = getAreaProgress($course, $area, $enrolledUsersArray)['ids'];
		$returnHTML.= '<div zona-name="zona-default-areas" course-id="'. $courseId .'" data-open="ss-main-container-tipo-personal-area" class="ss-container ss-main-container-areas-detail row ss-m-b-05">';
		//$returnHTML .= '<input class="personaIds" type="hidden" value="'. $personaIds .'">';
		$returnHTML .= '<div area-name="'. $area['nombre'] .'" course-id="'. $courseId .'" data-open="ss-main-container-tipo-personal-area" class="col-sm element-clickable" style="cursor: pointer;">'. $area['nombre'] .'</div>';

		if(!isset($area['nro_completado'])) {
			$progress = 0;
		} else {
			$progress = round(($area['nro_completado']/$area['total'])*100);
		}

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
	$totalTipo = 0;
	$progress = 0;

	$context = CONTEXT_COURSE::instance($courseId);
	$users = get_enrolled_users($context);

	foreach($users as $key=>$user) {
		profile_load_custom_fields($user);
		if(!empty($user->profile['personal'])) {
			if($user->profile['area_funcional'] == $area) {
				$totalTipo++;
				$tipoPersonal = $user->profile['personal'];
				//$progress = round(progress::get_course_progress_percentage($course, $user->id));
				$tipoPersonalArr[$tipoPersonal]['nombre'] = $tipoPersonal;
				$tipoPersonalArr[$tipoPersonal]['total']++;
				if($progress == 100) {
					$tipoPersonalArr[$tipoPersonal]['nro_completado']++;
				}
			}
		}
	}

	foreach($tipoPersonalArr as $tipoPersonal) {
		//$personaIds = getTipoPersonalPorDivisionProgress($course, $division, $zona, $enrolledUsersArray)['ids'];
		$returnHTML .= '<div area-name="' . $area . '" course-id="'. $courseId .'" data-open="ss-main-container-personal-area" class="ss-container ss-main-container-tipo-personal row ss-m-b-05">';
		//$returnHTML .= '<input class="personaIds" type="hidden" value="'. $personaIds .'">';
		$returnHTML .= '<div tipo-personal-name="' . $tipoPersonal['nombre'] . '" area-name="' . $area . '" course-id="'. $courseId .'" data-open="ss-main-container-personal-area" class="col-sm element-clickable" style="cursor: pointer;">'. $tipoPersonal['nombre'] .'</div>';

		if(!isset($tipoPersonal['nro_completado'])) {
			$progress = 0;
		} else {
			$progress = round(($tipoPersonal['nro_completado']/$tipoPersonal['total'])*100);
		}

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