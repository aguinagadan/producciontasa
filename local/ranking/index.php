<?php
require_once(dirname(__FILE__) . '/../../config.php');

require_login();

global $PAGE, $OUTPUT;

$title = 'Ranking';
// Set up the page.
$url = new moodle_url("/local/ranking/index.php");
$PAGE->set_url($url);

$PAGE->requires->jquery();
$PAGE->requires->js(new moodle_url('../ranking/js/ranking.js'));
$PAGE->requires->css(new moodle_url('../ranking/css/ranking.css'));

$PAGE->set_title('Ranking');

echo $OUTPUT->header();
include('includes.html');
include('rankingTasa.html');
echo $OUTPUT->footer();