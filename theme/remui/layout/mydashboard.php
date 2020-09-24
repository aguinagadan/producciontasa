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

global $USER;

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

function getLevelName() {
	global $USER;

	$world = \block_xp\di::get('course_world_factory')->get_world($this->page->course->id);
	$state = $world->get_store()->get_state($USER->id);
	$widget = new \block_xp\output\xp_widget($state, [], null, []);
	$level = $widget->state->get_level();

	return getLevelPropertyValue($level, 'name');
}

$templatecontextDashboard = [
	//samuel - pendiente al cambiar a produccion
	'URL' => $CFG->wwwroot . '/pluginfile.php/1/theme_remui/staticimage/1600901593/catalogo-cursos.titulo.png',
	'username' => $USER->firstname . ' ' . $USER->lastname,
	'levelname' => getLevelName(),
	'points' => '',
	'levelbadge' => '',
	'progressbar' => '',
	'totalcourses' => '',
	'completedcourses' => '',
	'pendingcourses' => '',
	'courseshtml' => '',
	'pendingCoursesHtml' => '',
	'seguimientoHtml' => ''
];

$templatecontext = array_merge($templatecontext, $templatecontextDashboard);

echo $OUTPUT->render_from_template('theme_remui/mydashboard', $templatecontext);

