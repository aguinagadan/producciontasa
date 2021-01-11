<?php
error_reporting(E_ALL);

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/enrol/externallib.php');
require_once($CFG->dirroot. '/course/lib.php');
require_once($CFG->dirroot . '/user/profile/lib.php');

use block_xp\local\xp\level_with_name;
use block_xp\local\xp\level_with_badge;

try {
	$details = $_POST;
	$returnArr = array();

	if (!isset($_REQUEST['request_type']) || strlen($_REQUEST['request_type']) == false) {
		throw new Exception();
	}

	switch ($_REQUEST['request_type']) {
		case 'obtenerUsuario':
			$returnArr = obtenerUsuario();
			break;
	}
} catch (Exception $e) {
	$returnArr['status'] = false;
	$returnArr['data'] = $e->getMessage();
}

header('Content-type: application/json');

echo json_encode($returnArr);
exit();

function getUserImage() {
	global $USER;
	return '/user/pix.php/'.$USER->id.'/f1.jpg';
}

function obtenerLevelPropertyValue($level, $property) {
	$returnedValue = '';

	switch($property) {
		case 'name':
			$name = $level instanceof level_with_name ? $level->get_name() : null;
			if (empty($name)) {
				$name = get_string('levelx', 'block_xp', $level->get_level());
			}
			$returnedValue = $name;
			break;
	}
	return $returnedValue;
}

function getLevelBadge($level) {
	$badgeurl = $level->get_badge_url();
	var_dump($badgeurl->host);
	var_dump($badgeurl['host']);
	exit;
	if ($level instanceof level_with_badge && ($badgeurl = $level->get_badge_url()) !== null) {
		return $badgeurl->host.$badgeurl->path.$badgeurl->slashargument;
	} else {
		return '';
	}
}

function getUserLevel($small) {
	global $USER;

	$world = \block_xp\di::get('course_world_factory')->get_world(1);
	$state = $world->get_store()->get_state($USER->id);
	$widget = new \block_xp\output\xp_widget($state, [], null, []);
	$level = $widget->state->get_level();

	//Get data
	$levelName = obtenerLevelPropertyValue($level, 'name');
	$xp = $widget->state->get_xp();

	$widgetState = $widget->state;
	$pc = $widgetState->get_ratio_in_level() * 100;

	$levelInfo = array('levelName' => $levelName, 'xp' =>$xp, 'img' => getLevelBadge($level), 'percentage' => $pc);

	return $levelInfo;
}

function obtenerUsuario() {
	global $USER;
	$userArr = array(
		'id' => $USER->id,
		'photo' => getUserImage(),
		'name' => strtoupper($USER->firstname . ' ' . $USER->lastname),
		'levelName' => getUserLevel(1)['levelName'],
		'levelImage' => getUserLevel(1)['img'],
		'points' => getUserLevel(1)['xp'],
		'percentage' => getUserLevel(1)['percentage'],
	);

	$response['status'] = true;
	$response['data'] = $userArr;

	return $response;
}