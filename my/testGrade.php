<?php
error_reporting(E_ALL);
require_once(dirname(__FILE__) . '/../config.php');

use moodle_url;

global $USER, $CFG;

require_once($CFG->dirroot . '/lib/gradelib.php');
require_once($CFG->dirroot . '/enrol/externallib.php');
require_once($CFG->dirroot. '/course/lib.php');
require_once($CFG->dirroot . '/user/profile/lib.php');

$inicial = grade_get_grades(10, 'mod', 'quiz', 1, 27);
$final = grade_get_grades(10, 'mod', 'quiz', 2, 27);

var_dump($inicial);
var_dump($final);
exit;