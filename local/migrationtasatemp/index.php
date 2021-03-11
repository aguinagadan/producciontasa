<?php

global $DB, $CFG;

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/user/profile/lib.php');

use block_xp\local\xp\level_with_name;
use block_xp\local\xp\level_with_badge;

$users = $DB->get_records('user', array('deleted' => 0));

function getLevelBadgeTemp($level, $small) {
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

function obtenerLevelTempPropertyValue($level, $property) {
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

function obtenerLevelTemp($userId) {
	$world = \block_xp\di::get('course_world_factory')->get_world(1);
	$state = $world->get_store()->get_state($userId);
	$widget = new \block_xp\output\xp_widget($state, [], null, []);
	$level = $widget->state->get_level();
	$puntos = $widget->state->get_xp();

	return
		array(
			$level,
			obtenerLevelTempPropertyValue($level, 'name'),
			$puntos,
			getLevelBadgeTemp($level, 1)
		);
}

function obtenerLevelTempNameTemp($level) {
	return obtenerLevelTempPropertyValue($level, 'name');
}

foreach ($users as $key => $user) {
	profile_load_custom_fields($user);
	$area = trim($user->profile['area']);

	//$userData = $DB->get_record('tasa_user_point_tmp', array('userid' => $user->id));
	$userData = array();

	$newUserObj = new stdClass();
	$newUserObj->userid = $user->id;
	$newUserObj->username = $user->firstname . ' ' . $user->lastname;
	$newUserObj->area = $area;
	$newUserObj->points = obtenerLevelTemp($user->id)[2];
	$newUserObj->level = obtenerLevelTemp($user->id)[0]->get_level();
	$newUserObj->levelname = obtenerLevelTemp($user->id)[1];
	$newUserObj->levelimg = obtenerLevelTemp($user->id)[3];
	$newUserObj->updated_at = date("Y-m-d H:i:s");

	if (empty($userData)) {
		$newUserObj->created_at = date("Y-m-d H:i:s");
		echo '<pre>';
		var_dump($newUserObj);
		exit;
		//$DB->insert_record('tasa_user_point_tmp', $newUserObj);
	} else {
		$newUserObj->id = $userData->id;
		//$DB->update_record('tasa_user_point_tmp', $newUserObj);
	}
}

echo 'exito';