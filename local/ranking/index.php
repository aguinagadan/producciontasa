<?php
require_once('config.php');

global $PAGE, $OUTPUT;

$title = 'Ranking';
// Set up the page.
$url = new moodle_url("/local/ranking/index.php");
$PAGE->set_url($url);

$PAGE->requires->css(new moodle_url('../ranking/css/ranking.css'));
$PAGE->requires->js(new moodle_url('../ranking/js/ranking.js'));

$PAGE->set_title('Ranking');

echo $OUTPUT->header();
include('includes.html');
include('rankingTasa.html');
echo $OUTPUT->footer();