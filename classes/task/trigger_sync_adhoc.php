<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * A local_trigger adhoc task
 *
 * @package    local_trigger
 * @copyright  2015 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_trigger\task;

defined('MOODLE_INTERNAL') || die();



/**
 * An adhoc task to sync custom actions
 *
 * @package   local_trigger
 * @since      Moodle 4.3
 * @copyright  2019 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class trigger_sync_adhoc extends \core\task\adhoc_task {

   	 /**
     *  Run the tasks
     */
	 public function execute(){
	     global $DB;
		$trace = new \text_progress_trace();
         $trace->output("Syncing Custom Actions for Local Trigger ");
         \local_trigger\webhook\customactions::sync_custom_actions();
         $trace->output("Finished syncing custom actions for Local Trigger ");
	}

}

