<?php

namespace local_disableusers\task;

/**
 * A scheduled task class for CAS user sync.
 *
 * @copyright  2015 Vadim Dvorovenko <Vadimon@mail.ru>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class disableusers extends \core\task\scheduled_task
{

	/**
	 * Get a descriptive name for this task (shown to admins).
	 *
	 * @return string
	 */
	public function get_name(){
		return 'Disable users from TASA to MOODLE';
	}

	/**
	 * Run users sync.
	 */
	public function execute(){
		global $CFG;
		require_once($CFG->dirroot . '/local/disableusers/lib.php');
		disableusers_task();
	}

}