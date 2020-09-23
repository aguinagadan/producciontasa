<?php
require_once(dirname(__FILE__) . '/../../config.php');

require_login();

$title = 'Aula virtual - Inducción';
// Set up the page.
$url = new moodle_url("/local/induccion/index.php", array('component' => $component, 'search' => $search));
$PAGE->set_title($title);
$PAGE->set_url($url);

$PAGE->requires->jquery();
$PAGE->requires->js(new moodle_url('js/induccion.js'));
$PAGE->requires->js(new moodle_url('js/load.game.js'));
echo $OUTPUT->header();

// require 'game.php';

echo $OUTPUT->footer();
