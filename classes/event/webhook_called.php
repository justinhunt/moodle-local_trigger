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
 * The local_trigger attempt submitted event.
 *
 * @package    local_trigger
 * @copyright  2023 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_trigger\event;



defined('MOODLE_INTERNAL') || die();


/**
 * The local_trigger webhook called event class.
 *
 * @property-read array $other {
 *      Extra information about event.
 * }
 *
 * @package    local_trigger
 * @since      Moodle 4.1
 * @copyright  2023 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class webhook_called extends \core\event\base {

    /**
     * Create instance of event.
     *
     * @since Moodle 2.7
     *
     * @param $trigger
     * @param $submission
     * @param $editable
     * @return webhook_called
     */
    public static function create_event($webhookdata,$eventdata) {

        $systemcontext =\context_system::instance();


        $data = array(
            'context' => $systemcontext,
            'userid'=>-1,//system
            'relateduserid'=>0,//by default 'not logged in'
            'other' => ['eventname'=>$eventdata['eventname'],'webhook'=>$webhookdata->webhook,'eventdata'=>json_encode($eventdata) ]
        );

        $relateduserid=false;
        if(array_key_exists('userid', $eventdata)){
            $relateduserid = $eventdata['userid'];
        }
        if ($relateduserid) {
            $data['relateduserid'] = $relateduserid;
        }

        $event =  self::create($data);
        return $event;
    }

    /**
     * Init method.
     */
    protected function init() {
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['crud'] = 'r';
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "The webhook of name '{$this->other['webhook']}' was called for '{$this->other['eventname']}' for the user of id " . $this->relateduserid;
    }

    /**
     * Returns localised general event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventtriggerwebhookcalled', 'local_trigger');
    }

    /**
     * Custom validation.
     *
     * @throws \coding_exception
     * @return void
     */
    protected function validate_data() {
        parent::validate_data();

    }

}
