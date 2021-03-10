<?php
global $DB;

var_dump('a');

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/user/profile/lib.php');

var_dump('b');
exit;