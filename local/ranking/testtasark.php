<?php
require_once(dirname(__FILE__) . '/../../config.php');

use block_xp\local\xp\level_with_name;
use block_xp\local\xp\level_with_badge;

global $PAGE, $OUTPUT, $DB;

$title = 'Ranking';
// Set up the page.
$url = new moodle_url("/local/ranking/testtasark.php");
$PAGE->set_url($url);

$users = $DB->get_records('user', array('deleted' => 0, 'suspended' => 0));

function getLevelBadge($level, $small) {
	$levelnum = $level->get_level();

	if($small == 1) {
		$customClass = 'qroma-block_xp-level';
	} else {
		$customClass = 'qroma-block_xp-level-2';
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

$return = array();

foreach ($users as $user) {
	$world = \block_xp\di::get('course_world_factory')->get_world(1);
	$state = $world->get_store()->get_state($user->id);
	$widget = new \block_xp\output\xp_widget($state, [], null, []);
	$level = $widget->state->get_level();

	//Get data
	$levelName = obtenerLevelPropertyValue($level, 'name');
	$xp = $widget->state->get_xp();

	$return[] = array(
		'userid' => $user->id,
		'username' => $user->firstname . ' ' . $user->lastname,
		'points' => $xp
	);
}

function usort_callback($a, $b) {
	if ( $a['points'] == $b['points'] )
		return 0;

	return ( $a['points'] > $b['points'] ) ? -1 : 1;
}

usort($return, 'usort_callback');

$top100 = array_slice($return, 0, 100);

echo '<pre>';
var_dump($top100);
exit;