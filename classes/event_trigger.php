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

use \local_trigger\webhook\constants;

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
    const LOCAL_TRIGGER_SOMETHING = 0;

    private $trigger_type=0;
    
     
    /**
     * trigger the event
     *
     *
     */
    public static function trigger($event)
    {
        global $DB,$CFG;
        require_once($CFG->dirroot . '/user/profile/lib.php');

        //get the event data.
        $event_data = $event->get_data();

        //fetch the registered webhooks for that event. We should have one and sometimes more!!
        $webhooks =  webhook\webhooks::fetch_webhooks($event_data['eventname']);

        foreach($webhooks as $webhook) {
            if ($webhook && !empty($webhook)) {
                //do DB stuff, probably most triggers will need user or course data
                try {
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
                } catch (\Exception $error) {
                    debugging("fetching user/course data error for trigger request for \"$webhook\" failed with error: " . $error->getMessage(), DEBUG_ALL);
                }

                //do CURL request
                try {
                    $return = webhook\webhooks::call_webhook($webhook, $event_data);

                    //save the last data
                    if($DB->record_exists(constants::SAMPLE_TABLE,array('event'=>$event_data['eventname']))){
                        $DB->delete_records(constants::SAMPLE_TABLE,array('event'=>$event_data['eventname']));
                    }
                    $DB->insert_record(constants::SAMPLE_TABLE,array('event'=>$event_data['eventname'],'eventdata'=>json_encode($event_data)));

                } catch (\Exception $error) {
                    debugging("cURL request for \"$webhook\" failed with error: " . $error->getMessage(), DEBUG_ALL);
                }//end of try catch
            }//end of if webhook or empty
        }//end of it web hooks
    }

}