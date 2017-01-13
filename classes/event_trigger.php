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
        global $DB;

        //get the event data.
        $event_data = $event->get_data();

        //fetch the registered webhooks for that event. We should have one and sometimes more!!
        $webhooks =  self::fetch_webhooks($event_data['eventname']);

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
                    $return = self::curl_data($webhook, $event_data);
                } catch (\Exception $error) {
                    debugging("cURL request for \"$webhook\" failed with error: " . $error->getMessage(), DEBUG_ALL);
                }//end of try catch
            }//end of if webhook or empty
        }//end of it web hooks
    }

    public static function fetch_webhooks($eventname){

        $webhooks = array();
        $config = get_config('local_trigger');

        if($config && property_exists($config, 'triggercount')) {
            for ($tindex = 1; $tindex <= $config->triggercount; $tindex++){
                if(property_exists($config, 'triggerevent'.$tindex) && property_exists($config, 'triggerwebhook'.$tindex) ){
                    $prop_eventname = $config->{'triggerevent' . $tindex};
                    if (strpos($prop_eventname, '\\') !== 0) {
                        $prop_eventname= '\\'.$prop_eventname;
                    }
                    if($prop_eventname==$eventname){
                       $webhooks[] = $config->{'triggerwebhook' . $tindex};
                    }
                }
            }
        }
        return $webhooks;
    }
    
    public static function curl_data($webhook, $event_data){
       global $CFG;
        require_once($CFG->libdir . '/filelib.php');

            // Only http and https links supported.
            if (!preg_match('|^https?://|i', $webhook)) {
                    return false;
            }

            $options = array();
            $options['CURLOPT_SSL_VERIFYPEER'] = true;
            $options['CURLOPT_CONNECTTIMEOUT'] = 30;
            $options['CURLOPT_FOLLOWLOCATION'] = 1;
            $options['CURLOPT_MAXREDIRS'] = 5;
            $options['CURLOPT_RETURNTRANSFER'] = true;
            $options['CURLOPT_NOBODY'] = false;
            $options['CURLOPT_TIMEOUT'] = 30;
            $postdata = json_encode($event_data);

            $curl = new \curl();


            if (isset($postdata)) {
                $content = $curl->post($webhook, $postdata, $options);
            } else {
                $content = $curl->get($webhook, null, $options);
            }


            $info       = $curl->get_info();
            $error_no   = $curl->get_errno();
            $rawheaders = $curl->get_raw_response();

            if ($error_no) {
                $error = $content;
                debugging("cURL request for \"$webhook\" failed with: $error ($error_no)", DEBUG_ALL);
                return false;
            }

            if (empty($info['http_code'])) {
                debugging("cURL request for \"$webhook\" failed, HTTP response code: missing", DEBUG_ALL);
                return false;
            }

            if ($info['http_code'] != 200) {
                debugging("cURL request for \"$webhook\" failed, HTTP response code: ". $info['http_code'] , DEBUG_ALL);
                return false;
            }

            //if we got here, we are all good baby
            return true;
    }

}