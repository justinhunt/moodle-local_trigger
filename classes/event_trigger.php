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

use \local_trigger\webhook\constants;

defined('MOODLE_INTERNAL') || die();


/**
 *
 * This is a class containing functions for sending triggers
 * @package   local_trigger
 * @copyright 2016 Poodll Co. Ltd (https://poodll.com)
 * @author    Justin Hunt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class event_trigger
{
    /**
     * send the triggered event data to the webhook
     *
     *
     */
    public static function trigger($event)
    {
        global $DB,$CFG;
        require_once($CFG->dirroot . '/user/profile/lib.php');

        // Get the event data.
        $event_data = $event->get_data();

        // Prevent recursion if webhook_called event is triggered.
        if (strpos($event_data['eventname'], 'webhook_called') !== false) {
            return;
        }

        // Fetch the registered webhooks for that event.
        $webhooks_records = webhook\webhooks::fetch_full_webhooks($event_data['eventname']);
        if (empty($webhooks_records)) {
            return;
        }

        // Expand event data (user and course) once before processing webhooks.
        try {
            $event_data = self::expand_event($event_data);
        } catch (\Exception $error) {
            debugging("fetching user/course data error for trigger request failed with error: " . $error->getMessage(), DEBUG_ALL);
        }

        $config = get_config('local_trigger');
        $queuedelivery = !isset($config->queuedelivery) || !empty($config->queuedelivery);

        foreach ($webhooks_records as $webhook_record) {
            $webhook = $webhook_record->webhook;
            if ($webhook && !empty($webhook)) {
                if ($queuedelivery) {
                    $task = new \local_trigger\task\send_webhook_adhoc();
                    $task->set_component('local_trigger');
                    $customdata = (object)[
                        'webhook' => $webhook,
                        'event_data' => $event_data,
                        'webhook_record' => $webhook_record,
                        'attemptcount' => 1
                    ];
                    $task->set_custom_data($customdata);
                    \core\task\manager::queue_adhoc_task($task);
                } else {
                    // Synchronous delivery fallback
                    try {
                        $return = webhook\webhooks::call_webhook($webhook, $event_data);

                        // Save the last data
                        if (!in_array($event_data['eventname'], constants::SAMPLE_EVENTS)) {
                            if ($DB->record_exists(constants::SAMPLE_TABLE, array('event' => $event_data['eventname']))) {
                                $DB->delete_records(constants::SAMPLE_TABLE, array('event' => $event_data['eventname']));
                            }
                            $DB->insert_record(constants::SAMPLE_TABLE, array(
                                'event' => $event_data['eventname'],
                                'eventdata' => json_encode($event_data, JSON_INVALID_UTF8_SUBSTITUTE)
                            ));
                        }
                        // Trigger our own event to confirm the hook was sent
                        \local_trigger\event\webhook_called::create_event($webhook_record, $event_data)->trigger();

                    } catch (\Exception $error) {
                        debugging("cURL request for \"$webhook\" failed with error: " . $error->getMessage(), DEBUG_ALL);
                    }
                }
            }
        }
    }

    public static function expand_event($event_data){
        global $DB, $CFG;

        require_once($CFG->dirroot . '/user/profile/lib.php');

        //user data
        $userid=false;
        if(array_key_exists('relateduserid', $event_data)){
            $userid = $event_data['relateduserid'];
            $event_data['userid'] = $userid;
        }elseif(array_key_exists('userid', $event_data)){
            $userid = $event_data['userid'];
        }
        if ($userid) {
            $user = $DB->get_record('user', array('id' => $userid));
            if ($user) {
                unset($user->password);
                //profile fields
                $profileprops = get_object_vars(profile_user_record($user->id));
                if($profileprops){
                    foreach($profileprops as $key=>$value){
                        $user->{'upf_' . $key}=$value;
                    }
                }

                $event_data['user'] = $user;
            }
        }
        //course data
        if (array_key_exists('courseid', $event_data)) {
            $course = $DB->get_record('course', array('id' => $event_data['courseid']));
            if ($course) {
                $event_data['course'] = $course;
            }
        }
        return $event_data;
    }

    /**
     * Save a sample of this events data so the Zapier has something to work with
     * We only keep the latest one as a sample.
     *
     */
    public static function sample($event)
    {
        global $DB;
        //get the event data.
        $event_data = $event->get_data();
        $eventname = $event_data['eventname'];
        //do DB stuff, probably most triggers will need user or course data
        try {
            $event_data = self::expand_event($event_data);
        } catch (\Exception $error) {
            debugging("fetching user/course data error for trigger request for \"$eventname\" failed with error: " . $error->getMessage(), DEBUG_ALL);
        }

        if ($DB->record_exists(constants::SAMPLE_TABLE, array('event' => $event_data['eventname']))) {
            $DB->delete_records(constants::SAMPLE_TABLE, array('event' => $event_data['eventname']));
        }
        $DB->insert_record(constants::SAMPLE_TABLE, array(
            'event' => $event_data['eventname'],
            'eventdata' => json_encode($event_data, JSON_INVALID_UTF8_SUBSTITUTE)
        ));
    }

}
