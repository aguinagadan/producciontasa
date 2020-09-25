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

require_once('common.php');

use moodle_url;
use block_xp\local\xp\level_with_name;
use block_xp\local\xp\level_with_badge;
use core_completion\progress;
use core_course_renderer;
use coursecat_helper;
use theme_remui\usercontroller as usercontroller;

global $USER, $CFG;

$userCourses = array_values(usercontroller::get_users_courses_with_progress($USER));

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

function getSSCategories() {
	global $DB;
	$returnHTML = '';
	$categories = $DB->get_records('course_categories');

//	foreach($categories as $cat) {
//		if(categoryHasCourses($cat->id)) {
//			$extraStyle = ' style="cursor: pointer; font-size: 18px;"';
//			$extraClass = 'cat-clickable';
//			$value = getCategoryProgressById($cat->id);
//		} else {
//			$extraStyle = '';
//			$extraClass = '';
//			$value = 0;
//		}
//
//		$returnHTML .= '<div class="ss-container ss-main-container row ss-m-b-05">';
//		$returnHTML .= '<div data-id="'. $cat->id .'" class="col-sm '. $extraClass .'"'.$extraStyle.' style="font-size: 18px;">'.$cat->name.'</div>';
//		$returnHTML .= '<div class="col-sm" style="max-width: 3.3%; color: #526069;">'. round($value,0) .'%</div>';
//		$returnHTML .= '<div class="col-sm-7">'. getProgressBarDetailSeguimiento($value) .'</div>';
//		$returnHTML .= '</div>';
//	}

//	foreach($categories as $cat) {
//		$returnHTML .= getSSCoursesById($cat->id);
//	}

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
	'seguimientoHtml' => getSeguimientoHtml()
];

$templatecontext = array_merge($templatecontext, $templatecontextDashboard);

echo $OUTPUT->render_from_template('theme_remui/mydashboard', $templatecontext);