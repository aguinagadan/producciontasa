<?php
error_reporting(E_ALL);
require_once(dirname(__FILE__) . '/../config.php');

global $USER, $CFG, $DB;

require_once($CFG->dirroot . '/lib/gradelib.php');
require_once($CFG->dirroot . '/enrol/externallib.php');
require_once($CFG->dirroot. '/course/lib.php');
require_once($CFG->dirroot . '/user/profile/lib.php');

global $courseId;

$courseId = $_GET['course_id'];

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

$context = CONTEXT_COURSE::instance($courseId);
$users = get_enrolled_users($context);
foreach($users as $key=>$user) {
	profile_load_custom_fields($user);
	$users[$key] = $user;
}

$html = '
<html>
<head>
  <meta charset="UTF-8">
</head>
<table>
 <thead>
  <tr>
   <th BGCOLOR="#154A7D;" rowspan="2"><font FACE="Arial" color="#FFFFFF">Código</font></th>
   <th BGCOLOR="#154A7D;" rowspan="2"><font FACE="Arial" color="#FFFFFF">Nombre de Trabajador</font></th>
   <th BGCOLOR="#154A7D;" rowspan="2"><font FACE="Arial" color="#FFFFFF">DNI</font></th>
   <th BGCOLOR="#154A7D;" rowspan="2"><font FACE="Arial" color="#FFFFFF">E-Mail Tasa</font></th>
   <th BGCOLOR="#154A7D;" rowspan="2"><font FACE="Arial" color="#FFFFFF">Gerencia</font></th>
   <th BGCOLOR="#154A7D;" rowspan="2"><font FACE="Arial" color="#FFFFFF">Zona</font></th>
   <th BGCOLOR="#154A7D;" rowspan="2"><font FACE="Arial" color="#FFFFFF">División</font></th>
   <th BGCOLOR="#154A7D;" rowspan="2"><font FACE="Arial" color="#FFFFFF">Área</font></th>
   <th BGCOLOR="#154A7D;" rowspan="2"><font FACE="Arial" color="#FFFFFF">Tipo de empleado</font></th>
   <th BGCOLOR="#154A7D;" rowspan="2"><font FACE="Arial" color="#FFFFFF">Función</font></th>';

$curso = get_course($courseId);
$html .= '<th BGCOLOR="#5CBDEB;" colspan="4"><font FACE="Arial" color="#FFFFFF">'. $curso->fullname .'</font></th>';

$html .= '</tr>
 </thead>
 <tbody><tr>';

$html .= '
<td><font FACE="Arial">Cumplimiento</font></td>
<td><font FACE="Arial">Nota Inicial</font></td>
<td><font FACE="Arial">Nota Final</font></td>
<td><font FACE="Arial">Fecha</font></td>';


$html .= '</tr>';

foreach($users as $user) {
	$html .= '<tr>';
	$html .= '<td><font FACE="Arial">' . strtoupper($user->profile['codigo']) . '</font></td>';
	$html .= '<td><font FACE="Arial">' . strtoupper($user->lastname) . ' ' . strtoupper($user->firstname) .  '</font></td>';
	$html .= '<td><font FACE="Arial">' . strtoupper($user->profile['DNI']) .  '</font></td>';
	$html .= '<td><font FACE="Arial">' . strtoupper($user->email) .  '</font></td>';
	$html .= '<td><font FACE="Arial">' . strtoupper($user->profile['gerencia']) .  '</font></td>';
	$html .= '<td><font FACE="Arial">' . strtoupper($user->profile['zona']) .  '</font></td>';
	$html .= '<td><font FACE="Arial">' . strtoupper($user->profile['division']) .  '</font></td>';
	$html .= '<td><font FACE="Arial">' . strtoupper($user->profile['area_funcional']) .  '</font></td>';
	$html .= '<td><font FACE="Arial">' . strtoupper($user->profile['personal']) .  '</font></td>';
	$html .= '<td><font FACE="Arial">' . strtoupper($user->profile['posicion']) .  '</font></td>';

	$quiz = $DB->get_records_sql("select * from {quiz} q where q.course = ?", array($courseId));
	$courseCompletion = $DB->get_records_sql("select * from {course_completions} c where c.course = ? and c.userid = ?", array($courseId, $user->id));

	$quizIdInicio = array_shift($quiz);
	$quizIdFin = end($quiz);

	$inicial = 0;
	$final = 0;

	$inicial = grade_get_grades($courseId, 'mod', 'quiz', $quizIdInicio->id, $user->id);
	$final = grade_get_grades($courseId, 'mod', 'quiz', $quizIdFin->id, $user->id);

	$inicialGrade = array_shift(array_shift($inicial->items)->grades)->grade;
	$finalGrade = array_shift(array_shift($final->items)->grades)->grade;

	$inicial = $inicialGrade != '' ? $inicialGrade : '-';
	$final = $finalGrade  != '' ? $finalGrade : '-';

	$timeCompleted = array_shift($courseCompletion)->timecompleted;
	$timeCompleted = $timeCompleted != NULL ? date('d/m/Y', $timeCompleted) : '-';

	if($inicial != '-' && $final != '-' && $timeCompleted != '-') {
		$cumplimiento = 1;
	} else {
		$cumplimiento = 0;
	}

	$html .= '<td><font FACE="Arial">' . $cumplimiento .  '</font></td>';
	$html .= '<td><font FACE="Arial">' . round($inicial) .  '</font></td>';
	$html .= '<td><font FACE="Arial">' . round($final) .  '</font></td>';
	$html .= '<td><font FACE="Arial">' . $timeCompleted .  '</font></td>';

	$html .= '</tr>';
}

$file = "dashboard_detalles.xls";

$html .= '</tbody></table></html>';

header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=$file");
echo $html;