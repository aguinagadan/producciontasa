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
		case 'obtenerNiveles':
			$returnArr = obtenerNiveles();
			break;
		case 'obtenerUsuarios':
			$returnArr = obtenerUsuarios();
			break;
		case 'obtenerAreas':
			$returnArr = obtenerAreas();
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

function getLevelBadge($level, $small) {
	$levelnum = $level->get_level();

	if($small == 1) {
		$customClass = 'icon qroma-block_xp-level';
	} else {
		$customClass = 'icon qroma-block_xp-level-2';
	}

	$classes = $customClass . ' block_xp-level level-' . $levelnum;
	$label = get_string('levelx', 'block_xp', $levelnum);
	$classes .= ' d-badge';

	$html = '';
	if ($level instanceof level_with_badge && ($badgeurl = $level->get_badge_url()) !== null) {
		$html .= html_writer::tag(
			'div',
			html_writer::empty_tag('img', ['src' => $badgeurl,
				'alt' => $label, 'class'=> 'd-badge-img']),
			['class' => $classes . ' level-badge', 'style' => 'height: 75px;']
		);
	} else {
		$html .= html_writer::tag('div', $levelnum, ['class' => $classes, 'aria-label' => $label]);
	}
	return $html;
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

	$levelInfo = array('levelName' => $levelName, 'xp' =>$xp, 'img' => getLevelBadge($level, $small), 'percentage' => $pc);

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

function obtenerNiveles() {
	$world = \block_xp\di::get('course_world_factory')->get_world(1);
	$levelsinfo = $world->get_levels_info();
	$levels = $levelsinfo->get_levels();

	$pointMin = 0;

	foreach ($levels as $key=>$level) {
		$levelName = obtenerLevelPropertyValue($level, 'name');
		$levelNumber = $level->get_level();
		$levelImg = getLevelBadge($level, 1);

		if(isset($levels[$key+1])) {
			$pointMax = $levels[$key+1]->get_xp_required();
		} else {
			$pointMax = $pointMin;
		}

		$levelArr[] = [
			'name'=> $levelName,
			'number' => $levelNumber,
			'img'=> $levelImg,
			'pointMin' => $pointMin,
			'pointMax' => $pointMax
		];
		$pointMin = $pointMax;
	}

	$response['status'] = true;
	$response['data'] = $levelArr;

	return $response;
}

function usort_callback($a, $b) {
	if ( $a['punto'] == $b['punto'] )
		return 0;

	return ( $a['punto'] > $b['punto'] ) ? -1 : 1;
}

function obtenerUsuarios() {
	global $DB, $USER;

	$usersArr = $DB->get_records('user', array('deleted' => 0, 'suspended' => 0));

	foreach($usersArr as $key=>$userArr) {
		$world = \block_xp\di::get('course_world_factory')->get_world(1);
		$state = $world->get_store()->get_state($userArr->id);
		$widget = new \block_xp\output\xp_widget($state, [], null, []);
		$level = $widget->state->get_level();

		//Get data
		$levelName = obtenerLevelPropertyValue($level, 'name');
		$xp = $widget->state->get_xp();

		$user = $DB->get_record('user', array('id' => $userArr->id));

		$users[] = [

			'userid' => $userArr->id,
			'img'=> getLevelBadge($level, 1),
			'name'=> $user->firstname . ' ' . $user->lastname,
			'punto' =>$xp,
			'level'=> 'Nivel ' . $level->get_level() .', ' . $levelName
		];
	}

	usort($users, 'usort_callback');

	foreach($users as $key=>$us) {
		$usersPos[$us['user_id']] = $key+1;
	}

	$top100 = array_slice($users, 0, 100);

	$key = array_search($USER->id, array_column($top100, 'userid'));

	if($key === false) {

		$world = \block_xp\di::get('course_world_factory')->get_world(1);
		$state = $world->get_store()->get_state($USER->id);
		$widget = new \block_xp\output\xp_widget($state, [], null, []);

		$top100[] = array(
			'pos' => $usersPos[$USER->id],
			'img' => getLevelBadge($level, 1),
			'name' => $USER->firstname . ' ' . $USER->lastname,
			'points' => $widget->state->get_xp() . ' millas naúticas',
			'level'=> 'Nivel ' . $level->get_level() .', ' . $levelName
		);
	}


	$response['status'] = true;
	$response['data'] = $top100;

	return $response;
}

function obtenerAreas() {
	$areas[] = ['name' => 'Area 1', 'punto' => '1000 millas naúticas'];
	$areas[] = ['name' => 'Area 2', 'punto' => '2000 millas naúticas'];

	$response['status'] = true;
	$response['data'] = $areas;

	return $response;
}