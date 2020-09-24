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

$templatecontextDashboard = [
	'URL' => $CFG->wwwroot . '/pluginfile.php/1/theme_remui/staticimage/1600901593/catalogo-cursos.titulo.png',
	'username' => $USER->firstname . ' ' . $USER->lastname,
	'levelname' => getLevelInformation()['levelName'],
	'points' => getLevelInformation()['points'],
	'levelbadge' => getLevelInformation()['levelBadge'],
	'progressbar' => getLevelInformation()['progressBar'],
	'totalcourses' => count(enrol_get_my_courses()),
	'pendingCoursesHtml' => getPendingCoursesHtml($userCourses),
	'courseshtml' => '',
	'seguimientoHtml' => ''
];

$templatecontext = array_merge($templatecontext, $templatecontextDashboard);

echo $OUTPUT->render_from_template('theme_remui/mydashboard', $templatecontext);

