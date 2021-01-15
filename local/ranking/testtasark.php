<?php
require_once(dirname(__FILE__) . '/../../config.php');

global $PAGE, $OUTPUT, $DB;

$title = 'Ranking';
// Set up the page.
$url = new moodle_url("/local/ranking/testtasark.php");
$PAGE->set_url($url);

$users = $DB->get_records('user', array('deleted' => 0, 'suspended' => 0));

echo '<pre>';
var_dump(count($users));

foreach ($users as $user) {
	echo '<pre>';
	var_dump($user);
}

exit;