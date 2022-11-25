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

namespace local_trigger;

defined('MOODLE_INTERNAL') || die();


/**
 *
 * This is a class containing settings for the trigger plugin
 *
 * @package   local_trigger
 * @copyright 2016 Poodll Co. Ltd (https://poodll.com)
 * @author    Justin Hunt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
class settingstools
{
    const LOCAL_TRIGGER_DEFAULT_TRIGGER_COUNT = 5;

    public static function fetch_trigger_items($triggercount)
    {
        global $CFG;
        $items = array();

        //Add trigger fields
        for ($tindex = 1; $tindex <= $triggercount; $tindex++){
            $items[] = new \admin_setting_heading('local_trigger/settingheading' . $tindex, get_string('settingheading', 'local_trigger') . ' ' . $tindex, '');
            $items[] = new \admin_setting_configtext('local_trigger/triggerevent' . $tindex, get_string('triggerevent', 'local_trigger'), '', '', PARAM_TEXT,50);
            $items[] = new \admin_setting_configtext('local_trigger/triggerwebhook' . $tindex, get_string('triggerwebhook', 'local_trigger'), '', '', PARAM_TEXT,50);
        }
	return $items;

    }// end of fetch general items


    public static function fetch_triggercount_item(){


        $item= new \admin_setting_configtext('local_trigger/triggercount',
                    get_string('triggercount', 'local_trigger'),
                    get_string('triggercount_desc', 'local_trigger'),
                     5, PARAM_INT,20);
        return $item;

    }//end of function fetch trigger count

    /**
     * Returns log table name of preferred reader, if leagcy then return empty string.
     *
     * @return string table name
     */
    public static function get_log_table_name() {
        // Get prefered sql_internal_table_reader reader (if enabled).
        $logmanager = get_log_manager();
        $readers = $logmanager->get_readers();
        $logtable = '';

        // Get preferred reader.
        if (!empty($readers)) {
            foreach ($readers as $readerpluginname => $reader) {
                // If legacy reader is preferred reader.
                if ($readerpluginname == 'logstore_legacy') {
                    break;
                }

                // If sql_internal_table_reader is preferred reader.
                if ($reader instanceof \core\log\sql_internal_table_reader) {
                    $logtable = $reader->get_internal_log_table_name();
                    break;
                }
            }
        }
        return $logtable;
    }

}//end of class
