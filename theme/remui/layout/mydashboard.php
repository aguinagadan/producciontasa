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

$courses = enrol_get_all_users_courses($USER->id, true);

require_once("{$CFG->libdir}/completionlib.php");
$course = new stdClass();
$completedCourses = 0;
$coursesHtml = '';

foreach($courses as $key=>$c) {
	$iscomplete = 0;
	$course = get_course($c->id);
	$cinfo = new completion_info($course);
	$percentage = progress::get_course_progress_percentage($course, $USER->id);
	//$studentsCompleted = getCourseStats($c);

	if($percentage == 100) {
		$completedCourses++;
		$iscomplete = 1;
	}

	$course->percentage = $percentage;
	$course->iscomplete = $iscomplete;
	//$course->studentscompleted = $studentsCompleted;

	$courses[$key]->percentage = $percentage;
	$courses[$key]->iscomplete = $iscomplete;
	//$courses[$key]->studentscompleted = $studentsCompleted;

	//$coursesHtml .= getCoursesHtml($course);
}

$totalCourses = count($courses);

$templatecontextDashboard = [
	//samuel - pendiente al cambiar a produccion
	'URL' => $CFG->wwwroot . '/pluginfile.php/1/theme_remui/staticimage/1600901593/catalogo-cursos.titulo.png',
	'username' => $USER->firstname . ' ' . $USER->lastname,
	'levelname' => '',
	'points' => '',
	'levelbadge' => '',
	'progressbar' => '',
	'totalcourses' => $totalCourses,
	'completedcourses' => '',
	'pendingcourses' => '',
	'courseshtml' => '',
	'pendingCoursesHtml' => '',
	'seguimientoHtml' => ''
];

$templatecontext = array_merge($templatecontext, $templatecontextDashboard);

echo $OUTPUT->render_from_template('theme_remui/mydashboard', $templatecontext);

