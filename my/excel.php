<?php
error_reporting(E_ALL);
require_once(dirname(__FILE__) . '/../config.php');

use moodle_url;

global $USER, $CFG;

require_once($CFG->dirroot . '/lib/gradelib.php');
require_once($CFG->dirroot . '/enrol/externallib.php');
require_once($CFG->dirroot. '/course/lib.php');
require_once($CFG->dirroot . '/user/profile/lib.php');

function getEnrolledUsersDetail($courseId) {
	global $DB;
	$enrolledusers = $DB->get_records_sql(
		"SELECT u.*
               FROM {course} c
               JOIN {context} ctx ON c.id = ctx.instanceid AND ctx.contextlevel = ?
               JOIN {enrol} e ON c.id = e.courseid
               JOIN {user_enrolments} ue ON e.id = ue.enrolid
               JOIN {user} u ON ue.userid = u.id
               JOIN {role_assignments} ra ON ctx.id = ra.contextid AND u.id = ra.userid AND ra.roleid = ?
              WHERE c.id = ?",
		array(CONTEXT_COURSE, 5, $courseId)
	);

	return $enrolledusers;
}

function getUserAllDataByCourseId($courseId) {
	global $CFG;
	require_once($CFG->dirroot.'/user/profile/lib.php');

	$users = getEnrolledUsersDetail($courseId);

	foreach($users as $key=>$user) {
		profile_load_data($user);
	}

	return $users;
}

$cursos = get_courses();
$usersTotal = array();

foreach($cursos as $curso) {
	$context = CONTEXT_COURSE::instance($curso->id);
	$users = get_enrolled_users($context);
	foreach($users as $key=>$user) {
		profile_load_custom_fields($user);
		$users[$key] = $user;
	}
	$usersTotal += $users;
}

$html = '
<html>
<head>
  <meta charset="UTF-8">
</head>
<table>
 <thead>
  <tr>
   <th rowspan="2">Código</th>
   <th rowspan="2">Nombre de Trabajador</th>
   <th rowspan="2">DNI</th>
   <th rowspan="2">E-Mail Tasa</th>
   <th rowspan="2">Gerencia</th>
   <th rowspan="2">ZONA</th>
   <th rowspan="2">DIVISIÓN</th>
   <th rowspan="2">ÁREA FUNCIONAL</th>
   <th rowspan="2">TIPO DE EMPLEADO</th>
   <th rowspan="2">FUNCIÓN</th>';

function cmpBySort($a, $b) {
	return $a->id - $b->id;
}
usort($cursos, 'cmpBySort');

foreach($cursos as $c) {
	if($c->category == 0) {
		continue;
	}
	$html .= '<th colspan="4">'. $c->fullname .'</th>';
	$cursoIds[] = $c->id;
}

$html .= '</tr>
 </thead>
 <tbody><tr>';

foreach($cursos as $c) {
	$html .= '
	<td>Cumplimiento</td>
	<td>Nota Inicial</td>
	<td>Nota Final</td>
	<td>Fecha</td>';
}

$html .= '</tr>';

foreach($usersTotal as $user) {
	$html .= '<tr>';
	$html .= '<td>' . $user->profile['codigo'] . '</td>';
	$html .= '<td>' . $user->lastname . ' ' . $user->firstname .  '</td>';
	$html .= '<td>' . $user->profile['DNI'] .  '</td>';
	$html .= '<td>' . $user->email .  '</td>';
	$html .= '<td>' . $user->profile['gerencia'] .  '</td>';
	$html .= '<td>' . $user->profile['zona'] .  '</td>';
	$html .= '<td>' . $user->profile['division'] .  '</td>';
	$html .= '<td>' . $user->profile['area_funcional'] .  '</td>';
	$html .= '<td>' . $user->profile['personal'] .  '</td>';
	$html .= '<td>' . $user->profile['posicion'] .  '</td>';

	$coursesUser = enrol_get_all_users_courses($user->id, true);

	usort($coursesUser, 'cmpBySort');

	$cont = 0;

	foreach($cursoIds as $key=>$cursoId) {
		if($cursoId != $coursesUser[$cont]->id) {
			$html .= '<td>-</td>';
			$html .= '<td>-</td>';
			$html .= '<td>-</td>';
			$html .= '<td>-</td>';
			continue;
		}

		$quiz = $DB->get_records_sql("select * from {quiz} q where q.course = ?", array($cursoId));
		$courseCompletion = $DB->get_records_sql("select * from {course_completions} c where c.course = ? and c.userid = ?", array($cursoId,$user->id));
		$quizIdInicio = array_shift($quiz);
		$quizIdFin = end($quiz);

		$inicial = grade_get_grades($cursoId, 'mod', 'quiz', $quizIdInicio->id, $user->id);
		$final = grade_get_grades($cursoId, 'mod', 'quiz', $quizIdFin->id, $user->id);

		$inicialItems = array_shift($inicial->items);
		$finalItems = array_shift($final->items);

		$inicialGrade = array_shift($inicialItems->grades)->grade;
		$finalGrade = array_shift($finalItems->grades)->grade;

		$inicial = $inicialGrade ? $inicialGrade : '-';
		$final = $finalGrade ? $finalGrade : '-';

		$timeCompleted = array_shift($courseCompletion)->timecompleted;
		$timeCompleted = $timeCompleted != NULL ? date('d/m/Y', $timeCompleted) : '-';

		if($inicial != '-' && $final != '-' && $timeCompleted != '-') {
			$cumplimiento = 1;
		} else {
			$cumplimiento = 0;
		}

		$html .= '<td>' . $cumplimiento .  '</td>';
		$html .= '<td>' . round($inicial) .  '</td>';
		$html .= '<td>' . round($final) .  '</td>';
		$html .= '<td>' . $timeCompleted .  '</td>';
		$cont++;
	}

	$html .= '</tr>';
}

$file = "dashboard_detalles.xls";

$html .= '</tbody></table></html>';

header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=$file");
echo $html;