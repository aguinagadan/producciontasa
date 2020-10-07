<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * A two column layout for the remui theme.
 *
 * @package   theme_remui
 * @copyright 2016 Damyon Wiese
 * @copyright (c) 2020 WisdmLabs (https://wisdmlabs.com/) <support@wisdmlabs.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $USER, $CFG;

require_once('common.php');
require_once($CFG->dirroot. '/course/lib.php');
require_once($CFG->libdir. '/coursecatlib.php');
require_once($CFG->dirroot . '/enrol/externallib.php');

use moodle_url;
use block_xp\local\xp\level_with_name;
use block_xp\local\xp\level_with_badge;
use core_completion\progress;
use core_course_renderer;
use coursecat_helper;
use theme_remui\usercontroller as usercontroller;

$userCourses = array_values(usercontroller::get_users_courses_with_progress($USER));

$isManager = 1;
$personalcontext = context_user::instance($USER->id);
if (!has_capability('tool/policy:managedocs', $personalcontext)) {
	$isManager = 0;
}

$seguimientoHtml = '';

function obtenerCursosRaw() {
	return get_courses();
}

function getLevelPropertyValue($level, $property) {
	$returnedValue = '';

	switch($property) {
		case 'name':
			$name = $level instanceof level_with_name ? $level->get_name() : null;
			if (empty($name)) {
				$name = get_string('levelx', 'block_xp', $level->get_level());
			}
			$returnedValue = $name;
			break;
	}
	return $returnedValue;
}

function getProgressBar($state) {
	$classes = ['block_xp-level-progress', 'd-level-progress'];
	$pc = $state->get_ratio_in_level() * 100;
	if ($pc != 0) {
		$classes[] = 'progress-non-zero';
	}

	$html = '';

	$html .= html_writer::start_tag('div', ['class' => implode(' ', $classes)]);

	$html .= html_writer::start_tag('div', ['class' => 'xp-bar-wrapper d-progress-bar-level', 'role' => 'progressbar',
		'aria-valuenow' => round($pc, 1), 'aria-valuemin' => 0, 'aria-valuemax' => 100]);
	if($pc == 0) {
		$xpBar = 'd-xp-bar-0';
	} else {
		$xpBar = 'd-xp-bar';
	}

	$html .= html_writer::tag('div', '', ['style' => "width: {$pc}%;", 'class' => 'xp-bar '.$xpBar]);
	$html .= html_writer::end_tag('div');
	$html .= html_writer::end_tag('div');
	return $html;
}

function getLevelBadge($level) {
	$levelnum = $level->get_level();
	$classes = 'block_xp-level level-' . $levelnum;
	$label = get_string('levelx', 'block_xp', $levelnum);
	$classes .= ' d-badge';

	$html = '';
	if ($level instanceof level_with_badge && ($badgeurl = $level->get_badge_url()) !== null) {
		$html .= html_writer::tag(
			'div',
			html_writer::empty_tag('img', ['src' => $badgeurl,
				'alt' => $label, 'class'=> 'd-badge-img']),
			['class' => $classes . ' level-badge', 'style' => 'height: 75px;']
		);
	} else {
		$html .= html_writer::tag('div', $levelnum, ['class' => $classes, 'aria-label' => $label]);
	}
	return $html;
}

function getLevelInformation() {
	global $USER;

	$levelInfo = array();

	$courses = obtenerCursosRaw();
	$courseId = array_shift($courses)->id;
	$world = \block_xp\di::get('course_world_factory')->get_world($courseId);
	$state = $world->get_store()->get_state($USER->id);
	$widget = new \block_xp\output\xp_widget($state, [], null, []);
	$level = $widget->state->get_level();

	$levelInfo['levelName'] = getLevelPropertyValue($level, 'name');
	$levelInfo['points'] = $widget->state->get_xp();
	$levelInfo['levelBadge'] = getLevelBadge($level);
	$levelInfo['progressBar'] = getProgressBar($widget->state);

	return $levelInfo;
}

function convertDateToSpanish($timestamp) {
	setlocale(LC_TIME, 'es_ES', 'Spanish_Spain', 'Spanish');
	return strftime("%d de %B de %Y", $timestamp);
}

function getCategoryById($catId) {
	global $DB;
	return $DB->get_record('course_categories',array('id'=>$catId));
}

function progressBarHTML($percentage) {
	$div = '<div style="height: 15px; background-color: white;"></div>';

	if($percentage === 0) {
		$div = '<div class="progress progress-square mb-0">
									<div class="progress-bar bg-red-600-cc" style="height: 100%; width: 100%; background-color: #FF644C !important;" role="progressbar">
											<span>' . $percentage . '%' . '</span>
									</div>
							</div>';
	} elseif($percentage > 0) {
		$percentage = round($percentage);
		$div = '<div class="progress progress-square mb-0">
									<div class="progress-bar bg-green-600-cc" style="width: ' . $percentage . '%; height: 100%;" role="progressbar">
											<span>' . $percentage . '%' . '</span>
									</div>
							</div>';
	}
	return $div;
}

function getCourseImageById($courseId) {
	$course = get_course($courseId);
	return \theme_remui\utility::get_course_image($course);
}

function getPendingCoursesHtml($courses) {
	$coursesHtml = '';
	$totalPending = 0;

	foreach($courses as $key=>$course) {

		if($course->progress == 100) {
			continue;
		}

		$content = '<div class="cc-courses-info">
										<div class="dd-category-box-secundary">
									<div class="dd-h3-courses-info" style="background: url('. getCourseImageById($course->id) .');"></div>
										<div class="cc-courses-detail-container dd-ultimos-desc"> '. progressBarHTML($course->progress) .'
											<div class="text-left" style="font-size: 12px; color: #A3AFB7; padding: 2% 4% 0 7%; height: 35px;">'. getCategoryById($course->id)->name .'</div>
											<div class="dd-courses-course-name">'. $course->fullname .'</div>
											<a class="dd-courses-button" type="button" href="'. new moodle_url("/course/view.php",array("id" => $course->id)). '">Acceder al curso</a>
				</div>
				</div>
			</div>';

		$totalPending++;
		$coursesHtml.= '<div class="slide">'. $content .'</div>';
	}
	$coursesHtml.= '<input id="totalPending" type="hidden" value="'.$totalPending.'">';
	return $coursesHtml;
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

function getProgressBarDetail($value) {
	$progressHTML = '
			<div class="d-progress d-total" data-value='.$value.'>
				<span class="d-progress-left">
					<span class="d-progress-bar border-primary-green"></span>
				</span>
				<span class="d-progress-right">
					<span class="d-progress-bar border-primary-green"></span>
				</span>
				<div class="w-100 h-100 rounded-circle d-flex align-items-center justify-content-center"></div>
			</div>';

	return $progressHTML;
}

function getDaysLeftPercentage($startDate, $endDate) {
	$totalDays = intval(floor(($endDate-$startDate)/86400));
	$passedDays = intval(floor((strtotime(date('c')) - $startDate)/86400));
	return ($passedDays/$totalDays) * 100;
}

function getDaysLeft($startDate, $endDate) {
	$totalDays = intval(floor(($endDate-$startDate)/86400));
	$passedDays = intval(floor((strtotime(date('c')) - $startDate)/86400));
	return $totalDays - $passedDays;
}

function getCoursesHtml($courses) {
	$coursesHtml = '';

	if(!empty($courses)) {
		foreach($courses as $course) {
			$html = '';
			$categoryId = $course->category;
			$coursePercentage = !empty($course->progress) ? $course->progress : 0;
			$daysLeft = getDaysLeft($course->startdate, $course->enddate);
			$daysLeftPercentage = getDaysLeftPercentage($course->startdate, $course->enddate);

			$html.= '<div class="column d-course-row" style="height: 149px; width: 610px; background-color: white; box-shadow: 2px 2px 4px #00000029; border-radius: 4px; margin: 0 0 1% 1%; padding: 1%;">
							<div class="row" style="position: relative; height: 100%;">
								<div class="col-sm" style="position: relative; max-width: 40% !important; text-align: left; height: 100%;">
									<img class="dd-image-card" src="'. getCourseImageById($course->id) .'">
								</div>
								<div class="col-sm pl-0 pr-0" style="width: 50%;left: 1%;position: relative;text-align: left;">
									<div class="text-left" style="font-size: 12px; color: #A3AFB7">'. getCategoryById($categoryId)->name .'</div>
									<div class="text-left dd-line-height-name" style="font-size: 22px; font-weight: 525; color: #526069; overflow: hidden; height: 40px;"><a style="text-decoration: none !important; color: #526069 !important;" type="button" href="'. new moodle_url("/course/view.php",array("id" => $course->id)). '">'. $course->fullname .'</a></div>
									<div class="row dd-rounded-progress-box" style="width: 100%; height: auto; padding-top: 6%;">
										<div class="col-sm" style="width: 50%; height: 100%;">
											<div class="row">
												 <div class="col-sm" style="max-width: 30% !important;">';
			$html.= getProgressBarDetail($coursePercentage);
			$html.= '</div>
													<div class="col-sm dd-line-height" style="font-size: 13px; color: #A3AFB7">
														Progreso: '. round($coursePercentage) .' %';
			$html.= '</div>
											</div>
										</div>';

			if(isset($course->enddate) && !empty($course->enddate)) {
				$html.= '<div class="col-sm" style="width: 50%; height: 100%;">
											<div class="row">
												 <div class="col-sm" style="max-width: 30% !important;">';
				$html.= getProgressBarDetail($daysLeftPercentage);
			$html.= '					 </div>
													<div class="col-sm dd-line-height pr-0" style="font-size: 13px; color: #A3AFB7">
															Cierra en '. $daysLeft .' días';
						$html.= '			</div>
											</div>
									</div>';
			} else {
				$html.= '<div class="col-sm" style="width: 50%">
											<div class="row">
												 <div class="col-sm" style="width: 50%">-</div></div></div>';
			}
			$html.= '</div>
								</div>
							</div>
						</div>';
			$coursesHtml.= $html;
		}
	} else {
		$coursesHtml = '<div>No existen cursos</div>';
	}

	return $coursesHtml;
}







//function getUserAllDataByCourseId($courseId) {
//	$course = get_course($courseId);
//	$users = core_enrol_external::get_enrolled_users($courseId);
//	$progress = 0;
//
//	foreach($users as $key=>$user) {
//		//$users[$key] = get_complete_user_data('id', $user->id);
//		$progress += round(progress::get_course_progress_percentage($course, $user['id']));
//		//$users[$key] = $user;
//	}
//
//	return array();
//}
//
//function getPersonalPorArea($course, $tipoPersonal, $area, $enrolledUsersArray) {
//	$returnHTML = '';
//	$personal = array();
//
//	foreach($enrolledUsersArray as $key=>$eu) {
//		$personal[$key]['id'] = $eu->id;
//		$personal[$key]['nombre'] = $eu->firstname . ' ' . $eu->lastname;
//		$personal[$key]['tipo_personal'] = $eu->profile_field_personal;
//		$personal[$key]['area'] = $eu->profile_field_area_funcional;
//	}
//
//	$personal = array_unique($personal, SORT_REGULAR);
//
//	foreach($personal as $key=>$persona) {
//		if($persona['tipo_personal'] != $tipoPersonal || $persona['area'] != $area) {
//			continue;
//		}
//		$returnHTML.= '<div zona-name="'. $tipoPersonal . $area .'" course-id="'. $course->id .'" class="ss-container ss-main-container-personal row hidden ss-m-b-05">
//											<div data-id="'. $persona['id'] . '" data-val="'.strtoupper($persona['nombre']).'" class="col-sm personal-clickable" style="cursor: pointer; font-size: 18px;" data-toggle="modal" data-target="#myModal">'. $persona['nombre'] .'</div>
//											<div data-id="'. $persona['id'] . '" data-val="'.strtoupper($persona['nombre']).'" class="col-xs" data-toggle="modal" data-target="#myModal" style="cursor: pointer;">
//													<img src="../theme/remui/pix/ic_email_24px.png">
//											</div>';
//		$progress = round(progress::get_course_progress_percentage($course, $persona['id']));
//		$returnHTML.= getProgressBarDetailSeguimientoHtml($progress);
//		$returnHTML.= '</div>';
//	}
//
//	return $returnHTML;
//}
//
//function getPersonal($course, $tipoPersonal, $division, $zona, $enrolledUsersArray) {
//	$returnHTML = '';
//	$personal = array();
//
//	foreach($enrolledUsersArray as $key=>$eu) {
//		$personal[$key]['id'] = $eu->id;
//		$personal[$key]['nombre'] = $eu->firstname . ' ' . $eu->lastname;
//		$personal[$key]['tipo_personal'] = $eu->profile_field_personal;
//		$personal[$key]['division'] = $eu->profile_field_division;
//		$personal[$key]['zona'] = $eu->profile_field_zona;
//	}
//
//	$personal = array_unique($personal, SORT_REGULAR);
//
//	foreach($personal as $key=>$persona) {
//		if($persona['tipo_personal'] != $tipoPersonal || $persona['division'] != $division || $persona['zona'] != $zona) {
//			continue;
//		}
//		$returnHTML.= '<div zona-name="'. $tipoPersonal . $division . $zona .'" course-id="'. $course->id .'" class="ss-container ss-main-container-personal row hidden ss-m-b-05">
//											<input class="personaIds" type="hidden" value="'.$persona['id'].'">
//											<div data-id="'. $persona['id'] . '" data-val="'.strtoupper($persona['nombre']).'" class="col-sm personal-clickable" style="cursor: pointer; font-size: 18px;" data-toggle="modal" data-target="#myModal">'. $persona['nombre'] .'</div>
//											<div data-id="'. $persona['id'] . '" data-val="'.strtoupper($persona['nombre']).'" class="col-xs logo-mail-clickable" data-toggle="modal" data-target="#myModal" style="cursor: pointer;">
//													<img src="../theme/remui/pix/ic_email_24px.png">
//											</div>';
//		$progress = round(progress::get_course_progress_percentage($course, $persona['id']));
//		$returnHTML.= getProgressBarDetailSeguimientoHtml($progress);
//		$returnHTML.= '</div>';
//	}
//
//	return $returnHTML;
//}
//
//function getTipoPersonalPorArea($course, $area, $enrolledUsersArray) {
//	$returnHTML = '';
//	$tipoPersonal = array();
//
//	foreach($enrolledUsersArray as $key=>$eu) {
//		$tipoPersonal[$key]['tipo_personal'] = $eu->profile_field_personal;
//		$tipoPersonal[$key]['area'] = $eu->profile_field_area_funcional;
//	}
//
//	$tipoPersonal = array_unique($tipoPersonal, SORT_REGULAR);
//
//	foreach($tipoPersonal as $tipoPers) {
//		if($tipoPers['area'] != $area) {
//			continue;
//		}
//		$personaIds = getTipoPersonalPorAreaProgress($course, $area, $enrolledUsersArray)['ids'];
//		$returnHTML .= '<div zona-name="'. $area .'" course-id="'. $course->id .'" data-open="ss-main-container-personal" class="ss-container ss-main-container-tipo-personal row hidden ss-m-b-05">';
//		$returnHTML .= '<input class="personaIds" type="hidden" value="'. $personaIds .'">';
//		$returnHTML .= '<div zona-name="'. $tipoPers['tipo_personal'] . $area . '" course-id="'. $course->id .'" data-open="ss-main-container-personal" class="col-sm element-clickable" style="cursor: pointer;">'. $tipoPers['tipo_personal'] .'</div>';
//		$progress = getTipoPersonalPorAreaProgress($course, $area, $enrolledUsersArray)['progress'];
//		$returnHTML.= getProgressBarDetailSeguimientoHtml($progress);
//		$returnHTML .= '</div>';
//		$arrayTipoPersonal[] = $tipoPers['tipo_personal'];
//	}
//
//	foreach($arrayTipoPersonal as $tipoPers) {
//		$returnHTML .= getPersonalPorArea($course, $tipoPers, $area, $enrolledUsersArray);
//	}
//
//	return $returnHTML;
//}
//
//function getTipoPersonalPorDivision($course, $division, $zona, $enrolledUsersArray) {
//	$returnHTML = '';
//
//	foreach($enrolledUsersArray as $key=>$eu) {
//		$tipoPersonal[$key]['tipo_personal'] = $eu->profile_field_personal;
//		$tipoPersonal[$key]['division'] = $eu->profile_field_division;
//		$tipoPersonal[$key]['zona'] = $eu->profile_field_zona;
//	}
//
//	$tipoPersonal = array_unique($tipoPersonal, SORT_REGULAR);
//
//	foreach($tipoPersonal as $tipoPers) {
//		if($tipoPers['division'] != $division || $tipoPers['zona'] != $zona) {
//			continue;
//		}
//		$personaIds = getTipoPersonalPorDivisionProgress($course, $division, $zona, $enrolledUsersArray)['ids'];
//		$returnHTML .= '<div zona-name="'. $division . $zona . '" course-id="'. $course->id .'" data-open="ss-main-container-personal" class="ss-container ss-main-container-tipo-personal row hidden ss-m-b-05">';
//		$returnHTML .= '<input class="personaIds" type="hidden" value="'. $personaIds .'">';
//		$returnHTML .= '<div zona-name="'. $tipoPers['tipo_personal'] . $division . $zona . '" course-id="'. $course->id .'" data-open="ss-main-container-personal" class="col-sm element-clickable" style="cursor: pointer;">'. $tipoPers['tipo_personal'] .'</div>';
//
//		$progress = getTipoPersonalPorDivisionProgress($course, $division, $zona, $enrolledUsersArray)['progress'];
//
//		$returnHTML.= getProgressBarDetailSeguimientoHtml($progress);
//		$returnHTML .= '</div>';
//		$arrayTipoPersonal[] = $tipoPers['tipo_personal'];
//	}
//
//	foreach($arrayTipoPersonal as $tipoPers) {
//		$returnHTML .= getPersonal($course, $tipoPers, $division, $zona, $enrolledUsersArray);
//	}
//
//	return $returnHTML;
//}
//
//function getDivisionesPorZona($course, $zona, $enrolledUsersArray) {
//	$returnHTML = '';
//
//	foreach($enrolledUsersArray as $key=>$eu) {
//		$divisiones[$key]['division'] = $eu->profile_field_division;
//		$divisiones[$key]['zona'] = $eu->profile_field_zona;
//	}
//
//	$divisiones = array_unique($divisiones, SORT_REGULAR);
//
//	foreach($divisiones as $division) {
//		if($division['zona'] != $zona) {
//			continue;
//		}
//		$personaIds = getDivisionesPorZonaProgress($course, $zona, $enrolledUsersArray)['ids'];
//		$returnHTML .= '<div zona-name="'. $zona .'" course-id="'. $course->id .'" data-open="ss-main-container-tipo-personal" class="ss-container ss-main-container-division row hidden ss-m-b-05">';
//		$returnHTML .= '<input class="personaIds" type="hidden" value="'. $personaIds .'">';
//		$returnHTML .= '<div zona-name="'. $division['division'] . $zona .'" course-id="'. $course->id .'" data-open="ss-main-container-tipo-personal" class="col-sm element-clickable" style="cursor: pointer;">'. $division['division'] .'</div>';
//		$progress = getDivisionesPorZonaProgress($course, $zona, $enrolledUsersArray)['progress'];
//
//		$returnHTML.= getProgressBarDetailSeguimientoHtml($progress);
//		$returnHTML .= '</div>';
//		$arrayDivisiones[] = $division['division'];
//	}
//
//	foreach($arrayDivisiones as $division) {
//		$returnHTML .= getTipoPersonalPorDivision($course, $division, $zona, $enrolledUsersArray);
//	}
//
//	return $returnHTML;
//}
//
//function getZonas($course, $enrolledUsersArray) {
//	$returnHTML = '';
//
//	foreach($enrolledUsersArray as $eu) {
//		$zonasAll[] = $eu->profile_field_zona;
//	}
//
//	//This data is static
//	$zonas = array( 'Norte',
//		'Centro',
//		'Sur',
//		'Corporativo');
//
//	foreach($zonas as $zona) {
//		if(!in_array($zona, $zonasAll)) {
//			continue;
//		}
//		$personaIds = getZonasProgressById($course, $zona, $enrolledUsersArray)['ids'];
//		$returnHTML .= '<div zona-name="zona-default-zonas" course-id="'. $course->id .'" data-open="ss-main-container-division" class="ss-container ss-main-container-zonas-detail row hidden ss-m-b-05">';
//		$returnHTML .= '<input class="personaIds" type="hidden" value="'. $personaIds .'">';
//		$returnHTML .= '<div zona-name="'. $zona .'" course-id="'. $course->id .'" data-open="ss-main-container-division" class="col-sm element-clickable" style="cursor: pointer;">' . $zona . '</div>';
//		$progress = getZonasProgressById($course, $zona, $enrolledUsersArray)['progress'];
//		$returnHTML .= getProgressBarDetailSeguimientoHtml($progress);
//
//		$returnHTML .= '</div>';
//	}
//
//	foreach($zonas as $zona) {
//		$returnHTML .= getDivisionesPorZona($course, $zona, $enrolledUsersArray);
//	}
//
//	return $returnHTML;
//}
//
//function getAreasFuncionales($course, $enrolledUsersArray) {
//	$returnHTML = '';
//	$areasAll = array();
//
//	foreach($enrolledUsersArray as $eu) {
//		$areasAll[] = $eu->profile_field_area_funcional;
//	}
//
//	foreach($areasAll as $area) {
//		$personaIds = getAreaProgress($course, $area, $enrolledUsersArray)['ids'];
//		$returnHTML.= '<div zona-name="zona-default-areas" course-id="'. $course->id .'" data-open="ss-main-container-tipo-personal" class="ss-container ss-main-container-areas-detail row hidden ss-m-b-05">';
//		$returnHTML .= '<input class="personaIds" type="hidden" value="'. $personaIds .'">';
//		$returnHTML .= '<div zona-name="'. $area .'" course-id="'. $course->id .'" data-open="ss-main-container-tipo-personal" class="col-sm element-clickable" style="cursor: pointer;">'. $area .'</div>';
//		$progress = getAreaProgress($course, $area, $enrolledUsersArray)['progress'];
//		$returnHTML.= getProgressBarDetailSeguimientoHtml($progress, $course->id);
//		$returnHTML.= '</div>';
//	}
//
//	foreach($areasAll as $area) {
//		$returnHTML .= getTipoPersonalPorArea($course, $area, $enrolledUsersArray);
//	}
//
//	return $returnHTML;
//}
//
//function getSSSeguimientoDetails($course, $enrolledUsersArray) {
//	$returnHTML = '';
//	$dataOpen = '';
//	$zona = '';
//	$personaIds = '';
//	$seguimientoDetails = array('Seguimiento por zonas', 'Seguimiento por área funcional');
//
//	foreach($seguimientoDetails as $seguimientoDetail) {
//		if($seguimientoDetail == 'Seguimiento por zonas') {
//			$dataOpen = 'ss-main-container-zonas-detail';
//			$zona = 'zona-default-zonas';
//			$personaIds = getSeguimientoDetailsZonaProgress($course, $enrolledUsersArray)['ids'];
//			$progress = getSeguimientoDetailsZonaProgress($course, $enrolledUsersArray)['progress'];
//		} elseif($seguimientoDetail == 'Seguimiento por área funcional') {
//			$dataOpen = 'ss-main-container-areas-detail';
//			$zona = 'zona-default-areas';
//			$personaIds = getSeguimientoDetailsAreaProgress($course, $enrolledUsersArray)['ids'];
//			$progress = getSeguimientoDetailsAreaProgress($course, $enrolledUsersArray)['progress'];
//		}
//
//		$returnHTML .=	'<div zona-name="zona-default" course-id="'. $course->id .'" data-open="'. $dataOpen .'" class="ss-container ss-main-container-seguimiento-detail row hidden ss-m-b-05">';
//		$returnHTML .= '<input class="personaIds" type="hidden" value="'. $personaIds .'">';
//		$returnHTML .= '<div zona-name="'. $zona .'" course-id="'. $course->id .'" data-open="'. $dataOpen .'"  class="col-sm element-clickable" style="cursor: pointer;">'. $seguimientoDetail .'</div>';
//
//		$returnHTML.= getProgressBarDetailSeguimientoHtml($progress, $course->id);
//		$returnHTML.= '</div>';
//	}
//
//	return 	$returnHTML;
//}
//
//function getSSCoursesById($courses) {
//	$returnHTML = '';
//
//	if(!empty ($courses)) {
//		foreach($courses as $course) {
//			$enrolledUsersArray = getUserAllDataByCourseId($course->id);
//			$returnHTML .= '<div data-id="'. $course->category .'" class="ss-container ss-main-container-course row hidden ss-m-b-05">';
//			$returnHTML .= '<div zona-name="zona-default" course-id="'. $course->id .'" data-open="ss-main-container-seguimiento-detail" data-id="'. $course->id .'" class="col-sm element-clickable" style="cursor: pointer;">'.$course->fullname.'</div>';
//
//			$progress = getCursosProgress($course, $enrolledUsersArray)['progress'];
//
//			$returnHTML .= '<div class="col-sm" style="max-width: 3.3%; color: #526069;">'. round($progress,0) .'%</div>';
//			$returnHTML .= '<div class="col-sm-7">'. getProgressBarDetailSeguimiento($progress) .'</div>';
//			$returnHTML .= '</div>';
//		}
//
//		foreach($courses as $course) {
//			$returnHTML .= getSSSeguimientoDetails($course, $enrolledUsersArray);
//			$returnHTML .= getAreasFuncionales($course, $enrolledUsersArray);
//			$returnHTML .= getZonas($course, $enrolledUsersArray);
//		}
//	}
//
//	return $returnHTML;
//}
//
//function getPersonalPorAreaProgress($course, $tipoPersonal, $area, $enrolledUsersArray) {
//	$ids = '';
//	$returnArr = array();
//	$personal = array();
//
//	foreach($enrolledUsersArray as $key=>$eu) {
//		$personal[$key]['id'] = $eu->id;
//		$personal[$key]['tipo_personal'] = $eu->profile_field_personal;
//		$personal[$key]['area'] = $eu->profile_field_area_funcional;
//	}
//
//	$personal = array_unique($personal, SORT_REGULAR);
//
//	$count = 0;
//	$progress = 0;
//
//	foreach($personal as $key=>$persona) {
//		if($persona['tipo_personal'] != $tipoPersonal || $persona['area'] != $area) {
//			continue;
//		}
//		$count++;
//		$progress += round(progress::get_course_progress_percentage($course, $persona['id']));
//		$ids .= $persona['id'] . ',';
//	}
//
//	if($count == 0) {
//		return 0;
//	}
//
//	$returnArr['progress'] = $progress/$count;
//	$ids = rtrim($ids, ',');
//	$ids = implode(',', array_unique(explode(',', $ids)));
//	$returnArr['ids'] = $ids;
//
//	return $returnArr;
//}
//
//function getPersonalProgress($course, $tipoPersonal, $division, $zona, $enrolledUsersArray) {
//	$ids = '';
//	$returnArr = array();
//	$personal = array();
//
//	foreach($enrolledUsersArray as $key=>$eu) {
//		$personal[$key]['id'] = $eu->id;
//		$personal[$key]['tipo_personal'] = $eu->profile_field_personal;
//		$personal[$key]['division'] = $eu->profile_field_division;
//		$personal[$key]['zona'] = $eu->profile_field_zona;
//	}
//
//	$personal = array_unique($personal, SORT_REGULAR);
//
//	$count = 0;
//	$progress = 0;
//
//	foreach($personal as $key=>$persona) {
//		if($persona['tipo_personal'] != $tipoPersonal || $persona['division'] != $division || $persona['zona'] != $zona) {
//			continue;
//		}
//		$count++;
//		$progress += round(progress::get_course_progress_percentage($course, $persona['id']));
//		$ids .= $persona['id'] . ',';
//	}
//
//	if($count == 0) {
//		return 0;
//	}
//
//	$returnArr['progress'] = $progress/$count;
//	$ids = rtrim($ids, ',');
//	$ids = implode(',', array_unique(explode(',', $ids)));
//	$returnArr['ids'] = $ids;
//
//	return $returnArr;
//}
//
//function getTipoPersonalPorAreaProgress($course, $area, $enrolledUsersArray) {
//	$returnArr = array();
//	foreach($enrolledUsersArray as $key=>$eu) {
//		$tipoPersonal[$key]['tipo_personal'] = $eu->profile_field_personal;
//		$tipoPersonal[$key]['area'] = $eu->profile_field_area_funcional;
//	}
//
//	$tipoPersonal = array_unique($tipoPersonal, SORT_REGULAR);
//
//	$count = 0;
//	$progress = 0;
//	$ids = '';
//
//	foreach($tipoPersonal as $tipoPers) {
//		if ($tipoPers['area'] != $area) {
//			continue;
//		}
//		$count++;
//		$progress += getPersonalPorAreaProgress($course, $tipoPers['tipo_personal'], $area, $enrolledUsersArray)['progress'];
//		$ids .= getPersonalPorAreaProgress($course, $tipoPers['tipo_personal'], $area, $enrolledUsersArray)['ids'] . ',';
//	}
//
//	if($count == 0) {
//		return 0;
//	}
//
//	$returnArr['progress'] = $progress/$count;
//	$ids = rtrim($ids, ',');
//	$ids = implode(',', array_unique(explode(',', $ids)));
//	$returnArr['ids'] = $ids;
//
//	return $returnArr;
//}
//
//function getAreaProgress($course, $area, $enrolledUsersArray) {
//	$returnArr = array();
//	foreach($enrolledUsersArray as $key=>$eu) {
//		$tipoPersonal[$key]['tipo_personal'] = $eu->profile_field_personal;
//		$tipoPersonal[$key]['area'] = $eu->profile_field_area_funcional;
//	}
//
//	$count = 0;
//	$progress = 0;
//	$ids = '';
//
//	foreach($tipoPersonal as $tipoPers) {
//		if ($tipoPers['area'] != $area) {
//			continue;
//		}
//		$count++;
//		$progress += getTipoPersonalPorAreaProgress($course, $area, $enrolledUsersArray)['progress'];
//		$ids .= getTipoPersonalPorAreaProgress($course, $area, $enrolledUsersArray)['ids'];
//	}
//
//	if($count == 0) {
//		return 0;
//	}
//
//	$returnArr['progress'] = $progress/$count;
//	$ids = rtrim($ids, ',');
//	$ids = implode(',', array_unique(explode(',', $ids)));
//	$returnArr['ids'] = $ids;
//
//	return $returnArr;
//}
//
//function getTipoPersonalPorDivisionProgress($course, $division, $zona, $enrolledUsersArray) {
//	$returnArr = array();
//
//	foreach($enrolledUsersArray as $key=>$eu) {
//		$tipoPersonal[$key]['tipo_personal'] = $eu->profile_field_personal;
//		$tipoPersonal[$key]['division'] = $eu->profile_field_division;
//		$tipoPersonal[$key]['zona'] = $eu->profile_field_zona;
//	}
//
//	$tipoPersonal = array_unique($tipoPersonal, SORT_REGULAR);
//
//	$count = 0;
//	$progress = 0;
//	$ids = '';
//
//	foreach($tipoPersonal as $tipoPers) {
//		if ($tipoPers['division'] != $division || $tipoPers['zona'] != $zona) {
//			continue;
//		}
//		$count++;
//		$progress += getPersonalProgress($course, $tipoPers['tipo_personal'], $division, $zona, $enrolledUsersArray)['progress'];
//		$ids .= getPersonalProgress($course, $tipoPers['tipo_personal'], $division, $zona, $enrolledUsersArray)['ids'] . ',';
//	}
//
//	if($count == 0) {
//		return 0;
//	}
//
//	$ids = rtrim($ids, ',');
//	$ids = implode(',',array_unique(explode(',', $ids)));
//
//	$returnArr['progress'] = $progress/$count;
//	$returnArr['ids'] = $ids;
//
//	return $returnArr;
//}
//
//function getDivisionesPorZonaProgress($course, $zona, $enrolledUsersArray) {
//	$returnArr = array();
//	foreach($enrolledUsersArray as $key=>$eu) {
//		$divisiones[$key]['division'] = $eu->profile_field_division;
//		$divisiones[$key]['zona'] = $eu->profile_field_zona;
//	}
//
//	$divisiones = array_unique($divisiones, SORT_REGULAR);
//
//	$count = 0;
//	$progress = 0;
//	$ids = '';
//
//	foreach($divisiones as $division) {
//		if ($division['zona'] != $zona) {
//			continue;
//		}
//		$count++;
//		$progress += getTipoPersonalPorDivisionProgress($course, $division['division'], $zona, $enrolledUsersArray)['progress'];
//		$ids .= getTipoPersonalPorDivisionProgress($course, $division['division'], $zona, $enrolledUsersArray)['ids'] . ',';
//	}
//
//	if($count == 0) {
//		return 0;
//	}
//
//	$ids = rtrim($ids, ',');
//	$ids = implode(',',array_unique(explode(',', $ids)));
//
//	$returnArr['progress'] = $progress/$count;
//	$returnArr['ids'] = $ids;
//
//	return $returnArr;
//}
//
//function getZonasProgressById($course, $zonaExt, $enrolledUsersArray) {
//	$returnArr = array();
//
//	foreach($enrolledUsersArray as $eu) {
//		$zonasAll[] = $eu->profile_field_zona;
//	}
//
//	$zonas = array( 'Norte',
//		'Centro',
//		'Sur',
//		'Corporativo');
//
//	$count = 0;
//	$progress = 0;
//	$ids = '';
//
//	foreach($zonas as $zona) {
//		if($zonaExt != $zona) {
//			continue;
//		}
//		$count++;
//		$progress += getDivisionesPorZonaProgress($course, $zona, $enrolledUsersArray)['progress'];
//		$ids .= getDivisionesPorZonaProgress($course, $zona, $enrolledUsersArray)['ids'] . ',';
//	}
//
//	if($count == 0) {
//		return 0;
//	}
//
//	$ids = rtrim($ids, ',');
//	$ids = implode(',',array_unique(explode(',', $ids)));
//
//	$returnArr['progress'] = $progress/$count;
//	$returnArr['ids'] = $ids;
//
//	return $returnArr;
//}
//
//function getSeguimientoDetailsZonaProgress($course, $enrolledUsersArray) {
//	$returnArr = array();
//
//	foreach($enrolledUsersArray as $eu) {
//		$zonas[] = $eu->profile_field_zona;
//	}
//
//	$progress = 0;
//	$ids = '';
//
//	foreach($zonas as $zona) {
//		$progress += getZonasProgressById($course, $zona, $enrolledUsersArray)['progress'];
//		$ids .= getZonasProgressById($course, $zona, $enrolledUsersArray)['ids'] . ',';
//	}
//
//	if(count($zonas) == 0) {
//		return 0;
//	}
//	$ids = rtrim($ids, ',');
//	$ids = implode(',',array_unique(explode(',', $ids)));
//
//	$returnArr['progress'] = $progress/count($zonas);
//	$returnArr['ids'] = $ids;
//
//	return $returnArr;
//}
//
//function getSeguimientoDetailsAreaProgress($course, $enrolledUsersArray) {
//	$returnArr = array();
//
//	foreach($enrolledUsersArray as $key=>$eu) {
//		$areas[] = $eu->profile_field_area_funcional;
//	}
//
//	$progress = 0;
//	$ids = '';
//
//	foreach($areas as $area) {
//		$progress += getAreaProgress($course, $area, $enrolledUsersArray)['progress'];
//		$ids .= getAreaProgress($course, $area, $enrolledUsersArray)['ids'] . ',';
//	}
//
//	if(count($areas) == 0) {
//		return 0;
//	}
//
//	$ids = rtrim($ids, ',');
//	$ids = implode(',',array_unique(explode(',', $ids)));
//
//	$returnArr['progress'] = $progress/count($areas);
//	$returnArr['ids'] = $ids;
//
//	return $returnArr;
//}
//
//function getCursosProgress($course, $enrolledUsersArray) {
//	$returnArr = array();
//	$seguimientoDetails = array('zonas','areas');
//
//	foreach($seguimientoDetails as $seguimientoDetail) {
//		if($seguimientoDetail == 'zonas') {
//			$progress += getSeguimientoDetailsZonaProgress($course, $enrolledUsersArray)['progress'];
//		} else {
//			$progress += getSeguimientoDetailsAreaProgress($course, $enrolledUsersArray)['progress'];
//		}
//	}
//
//	$progress = $progress/count($seguimientoDetails);
//
//	$returnArr['progress'] = $progress;
//
//	return $returnArr;
//}
//

function getCoursePercentage($children_courses) {
	global $DB;
	$progressTotal = 0;

	foreach($children_courses as $course) {
		$new = array();
		$contCompleted = 0;
		$total = count($DB->get_records('course_completions',array('course'=>$course->id)));
		$modules = $DB->get_records_sql("select * from {course_modules} c where c.course = ? AND completion > 0", array($course->id));

		foreach($modules as $mod) {
			$new[] = $mod->id;
		}

		$cantidadModulos = count($new);

		$instring = "('".implode("', '",$new)."')";
		$query = "select c.userid, COUNT(c.userid) as cont from {course_modules_completion} c where c.completionstate>0 AND c.coursemoduleid in $instring GROUP BY userid";
		$results = $DB->get_records_sql($query);

		$context = context_course::instance($course->id);

		foreach ($results as $res) {
			if(is_enrolled($context, $res->userid)) {
				if ($res->cont == $cantidadModulos) {
					$contCompleted++;
				}
			}
		}
		if($total == 0) {
			$progress = 0;
		} else {
			$progress = round(($contCompleted/$total)*100);
		}
		$progressTotal += $progress;
	}
	return round($progressTotal/count($children_courses));
}

function getSSCategories() {
	global $DB;
	$returnHTML = '';
	$categories = $DB->get_records('course_categories');

	foreach($categories as $category) {
		$cat = \coursecat::get($category->id);
		$children_courses = $cat->get_courses();
		if(!empty($children_courses)) {
			$extraStyle = ' style="cursor: pointer; font-size: 18px;"';
			$extraClass = 'cat-clickable';
			$value = getCoursePercentage($children_courses);
		} else {
			$extraStyle = '';
			$extraClass = '';
		}

		$returnHTML .= '<div class="ss-container ss-main-container row ss-m-b-05">';
		$returnHTML .= '<div data-id="'. $cat->id .'" class="col-sm '. $extraClass .'"'.$extraStyle.' style="font-size: 18px;">'.$cat->name.'</div>';
		$returnHTML .= '<div class="col-sm" style="max-width: 3.3%; color: #526069;">'. $value .'%</div>';
		$returnHTML .= '<div class="col-sm-7">'. getProgressBarDetailSeguimiento($value) .'</div>';
		$returnHTML .= '</div>';
	}

	$returnHTML.= '<div class="modal fade" id="myModal" role="dialog">
		<div class="modal-dialog">

			<!-- Modal mensaje personal -->
			<div class="modal-content">
				<div class="modal-header" style="padding: 2% 3% 0 3%; border-bottom: 0;">
					<h4 class="ss-message-title-1 modal-title"></h4>
					<button type="button" class="close" data-dismiss="modal">&times;</button>
				</div>
				<div class="modal-header" style="padding: 0 0 0 3%; border-bottom: 0;">
					<h5 class="modal-title">Llegará a su correo electrónico</h5>
				</div>
				<div class="modal-body">
					<textarea id="message-text" class="form-control" style="height: 140px;"></textarea>
				</div>
				<div class="modal-footer" style="padding: 0 3% 3% 0; border-top: 0;">
					<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
					<button id="myModalBtn" type="button" class="btn btn-info" data-dismiss="modal" style="color: white;">Enviar</button>
				</div>
			</div>
		</div>
	</div>';

	$returnHTML.= '<div class="modal fade" id="myModalGeneral" role="dialog">
		<div class="modal-dialog">

			<!-- Modal mensaje general -->
			<div class="modal-content">
				<div class="modal-header" style="padding: 2% 3% 0 3%; border-bottom: 0;">
					<h4 class="ss-message-title-1 modal-title">MENSAJE PARA EMPLEADOS</h4>
					<button type="button" class="close" data-dismiss="modal">&times;</button>
				</div>
				<div class="modal-header" style="padding: 0 0 0 3%; border-bottom: 0;">
					<h5 class="modal-title">Llegará al correo de los usuarios que aún no terminan el curso.</h5>
				</div>
				<div class="modal-body">
					<textarea id="message-text-all" class="form-control" style="height: 140px;"></textarea>
				</div>
				<div class="modal-footer" style="padding: 0 3% 3% 0; border-top: 0;">
					<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
					<button id="myModalGeneralBtn" type="button" class="btn btn-info" data-dismiss="modal" style="color: white;">Enviar</button>
				</div>
			</div>
		</div>
	</div>';

	return $returnHTML;
}

function getSeguimientoHtml() {
	$seguimientoHtml = '';
	$seguimientoHtml .= '
<div style="background-color: white; width: 90%; margin-left: 5%; padding: 1% 1% 1% 2% !important; box-shadow: 2px 2px 4px #00000029;
border-radius: 4px;">
			<div class="row">
				<div class="col-sm" style="text-align: left; font-weight: bold; color: #154A7D; font-size: 24px;">Seguimiento de finalización de cursos</div>
				<div class="col-sm-1 pl-0">
					<div class="row">
						<div class="col-sm carta-general" data-toggle="modal">
						</div>
						<div id="modalXLS" class="col-sm" style="cursor: pointer;">
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col" style="text-align: left; color: #154A7D; font-size: 15px;">Puedes dar clic en cualquier item para desplegar detalles</div>
			</div>
			<div id="loadingDiv" style="width: 83px;height: 29px;" class="col-sm-1 hidden"><img src="../theme/remui/pix/spinner.gif"></div>
			<div class="row">
				<div class="ss-breadcrumb col-sm row" style="text-align: left; margin: 2% 0 1% 0; color: #154A7D; font-size: 18px;">
					<div class="main-clickable" style="cursor: pointer;">CATEGORÍAS</div>
				</div>
			</div>
			<div class="row">
				<div id="seguimiento-content" class="col" style="text-align: left; color: #526069;">'. getSSCategories().'</div>
			</div>
</div>';

	return $seguimientoHtml;
}

if($isManager) {
	$seguimientoHtml = getSeguimientoHtml();
}

$templatecontextDashboard = [
	'URL' => $CFG->wwwroot . '/pluginfile.php/1/theme_remui/staticimage/1600901593/catalogo-cursos.titulo.png',
	'username' => $USER->firstname . ' ' . $USER->lastname,
	'levelname' => getLevelInformation()['levelName'],
	'points' => getLevelInformation()['points'],
	'levelbadge' => getLevelInformation()['levelBadge'],
	'progressbar' => getLevelInformation()['progressBar'],
	'totalcourses' => count(enrol_get_my_courses()),
	'pendingCoursesHtml' => getPendingCoursesHtml($userCourses),
	'courseshtml' => getCoursesHtml($userCourses),
	'seguimientoHtml' => $seguimientoHtml
];

$templatecontext = array_merge($templatecontext, $templatecontextDashboard);

echo $OUTPUT->render_from_template('theme_remui/mydashboard', $templatecontext);