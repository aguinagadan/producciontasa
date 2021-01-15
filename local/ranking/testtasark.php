<?php
require_once(dirname(__FILE__) . '/../../config.php');

global $PAGE, $OUTPUT, $DB;

$title = 'Ranking';
// Set up the page.
$url = new moodle_url("/local/ranking/testtasark.php");
$PAGE->set_url($url);

$PAGE->set_title('Ranking');
echo $OUTPUT->header();

$users = $DB->get_record('user', array('deleted' => 0, 'suspended' => 0));

foreach ($users as $user) {
	echo $user->username;
}

echo $OUTPUT->footer();