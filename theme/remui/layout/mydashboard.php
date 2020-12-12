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
require_once($CFG->dirroot . '/enrol/externallib.php');

use moodle_url;
use block_xp\local\xp\level_with_name;
use block_xp\local\xp\level_with_badge;
use core_completion\progress;
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

		$categoryName = isset(getCategoryById($course->id)->name) ? getCategoryById($course->id)->name : '-';

		$content = '<div class="cc-courses-info">
										<div class="dd-category-box-secundary">
									<div class="dd-h3-courses-info" style="background: url('. getCourseImageById($course->id) .');"></div>
										<div class="cc-courses-detail-container dd-ultimos-desc"> '. progressBarHTML($course->progress) .'
											<div class="text-left" style="font-size: 12px; color: #A3AFB7; padding: 2% 4% 0 7%; height: 35px;">'. $categoryName .'</div>
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

			$categoryName = isset(getCategoryById($course->id)->name) ? getCategoryById($course->id)->name : '-';

			$html.= '<div class="column d-course-row" style="height: 149px; width: 610px; background-color: white; box-shadow: 2px 2px 4px #00000029; border-radius: 4px; margin: 0 0 1% 1%; padding: 1%;">
							<div class="row" style="position: relative; height: 100%;">
								<div class="col-sm" style="position: relative; max-width: 40% !important; text-align: left; height: 100%;">
									<img class="dd-image-card" src="'. getCourseImageById($course->id) .'">
								</div>
								<div class="col-sm pl-0 pr-0" style="width: 50%;left: 1%;position: relative;text-align: left;">
									<div class="text-left" style="font-size: 12px; color: #A3AFB7">'. $categoryName .'</div>
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
											<div class="row">';
//				$html.= '<div class="col-sm" style="max-width: 30% !important;">';
//				$html.= getProgressBarDetail($daysLeftPercentage);
//				$html.= '					 </div>
//													<div class="col-sm dd-line-height pr-0" style="font-size: 13px; color: #A3AFB7">
//															Cierra en '. $daysLeft .' días';
//						$html.= '			</div>';
						$html.= '</div>
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

function getCoursePercentage($children_courses) {
	global $DB;
	$progressTotal = 0;

	foreach($children_courses as $course) {
		$new = array();
		$contCompleted = 0;
		$totalData = $DB->get_records_sql("select c.userid from {course_completions} c where c.course = ?", array($course->id));
		$totalData = array_keys($totalData);
		$total = count($totalData);
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
			if(in_array($res->userid, $totalData)) {
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
		if($category->visible != 1) {
			continue;
		}
		$cat = \core_course_category::get($category->id);
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
				<div class="col-sm-2 pl-0">
					<div class="row">
						<div class="col-sm cert-download" data-toggle="modal">
							<img style="opacity: 0.5; cursor: pointer;" src="../theme/remui/pix/cert24px.png">
						</div>
						<div class="col-sm carta-general" data-toggle="modal">
							<img style="opacity: 0.5; cursor: auto;" src="../theme/remui/pix/ic_email_24px.png">
						</div>
						<div id="modalXLS" class="col-sm excel-general" style="cursor: pointer;">
							<img style="opacity: 0.5; cursor: auto;" src="../theme/remui/pix/ic_get_app_24px.png">
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
	$seguimientoHtml = '<div></div>';
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
echo $OUTPUT->render_from_template('theme_remui/seguimientotasa', $templatecontext);
echo $OUTPUT->render_from_template('theme_remui/mydashboardremuiblock', $templatecontext);