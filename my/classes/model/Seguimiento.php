<?php

namespace Seguimiento\Model;

global $CFG;

require(__DIR__.'/../../../config.php');
require_once($CFG->dirroot. '/course/lib.php');
require_once($CFG->libdir. '/coursecatlib.php');

class Seguimiento {
	public function __construct() {
	}

	public function GetCoursesByCategory($idCat) {
		$cat = \coursecat::get($idCat);
		$children_courses = $cat->get_courses();
		return $children_courses;
	}
}