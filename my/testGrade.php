<?php
error_reporting(E_ALL);
require_once(dirname(__FILE__) . '/../config.php');

use moodle_url;

global $USER, $CFG, $DB;

require_once($CFG->dirroot . '/lib/gradelib.php');
require_once($CFG->dirroot . '/enrol/externallib.php');
require_once($CFG->dirroot. '/course/lib.php');
require_once($CFG->dirroot . '/user/profile/lib.php');

$quiz = $DB->get_records_sql("select * from {quiz} q where q.course = ?", array(10));
$courseCompletion = $DB->get_records_sql("select * from {course_completions} c where c.course = ? and c.userid = ?", array(10,27));
$quizIdInicio = array_shift($quiz);
$quizIdFin = end($quiz);

$inicial = grade_get_grades(10, 'mod', 'quiz', $quizIdInicio->id, 27);
$final = grade_get_grades(10, 'mod', 'quiz', $quizIdFin->id, 27);

$inicialItems = $inicial->items[0];
$finalItems = $final->items[0];

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
var_dump($inicial);
var_dump($final);
var_dump($timeCompleted);
var_dump($cumplimiento);
exit;