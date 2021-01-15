<?php
require_once(dirname(__FILE__) . '/../../config.php');

global $PAGE, $OUTPUT, $DB;

$title = 'Ranking';
// Set up the page.
$url = new moodle_url("/local/ranking/testtasark.php");
$PAGE->set_url($url);

$users = $DB->get_record('user', array('deleted' => 0, 'suspended' => 0));

var_dump(count($users));

foreach ($users as $user) {
	var_dump($user);
}