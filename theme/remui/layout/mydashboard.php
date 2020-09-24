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

global $USER, $CFG;

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

$templatecontextDashboard = [
	'URL' => $CFG->wwwroot . '/pluginfile.php/1/theme_remui/staticimage/1600901593/catalogo-cursos.titulo.png',
	'username' => $USER->firstname . ' ' . $USER->lastname,
	'levelname' => getLevelInformation()['levelName'],
	'points' => getLevelInformation()['points'],
	'levelbadge' => getLevelInformation()['levelBadge'],
	'progressbar' => getLevelInformation()['progressBar'],
	'totalcourses' => count(enrol_get_my_courses()),
	'completedcourses' => '',
	'pendingcourses' => '',
	'courseshtml' => '',
	'pendingCoursesHtml' => '',
	'seguimientoHtml' => ''
];

$templatecontext = array_merge($templatecontext, $templatecontextDashboard);

echo $OUTPUT->render_from_template('theme_remui/mydashboard', $templatecontext);

