<?php

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
 * External Web Service for Local Trigger
 *
 * @package    local_trigger
 * @copyright  2022 Justin Hunt (https://poodll.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->libdir . "/externallib.php");

use \local_trigger\webhook\constants;

class local_trigger_services extends external_api {


    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function register_webhook_parameters() {
        $params = array();
        $params['event'] = new external_value(PARAM_TEXT, 'The full event name, eg \mod_quiz\event\attempt_submitted ', VALUE_DEFAULT, 'audio');
        $params['hook'] = new external_value(PARAM_TEXT, 'The URL of the webhook', VALUE_DEFAULT, 'http://127.0.0.1');
        $params['description'] = new external_value(PARAM_TEXT, 'The webhook description', VALUE_DEFAULT, 'file.mp3');
         return new external_function_parameters(
            $params
        );
    }

    /**
     * Returns result of action
     * @return array result of action
     */
    public static function register_webhook($event, $hook, $description)
    {
        global $USER;

        $success = false;
        $message = "unknown error";

        $rawparams = array();
        $rawparams['event'] = $event;
        $rawparams['hook'] = $hook;
        $rawparams['description'] = $description;

        //Parameter validation
        $params = self::validate_parameters(self::register_webhook_parameters(),
            $rawparams);

        //Context validation
        $context = context_user::instance($USER->id);
        self::validate_context($context);

        //Capability checking
        if (!has_capability('local/trigger:canmanagewebhooks', $context)) {
            throw new moodle_exception('nopermission');
        }


        $theitem = new stdClass;
       // $theitem->id = //new entry so its not specified
        $theitem->authid = $USER->id;  //do this better soon
        $theitem->webhook = $params['hook'];
        $theitem->event =  $params['event'];
        $theitem->description =  $params['description'];;
        $theitem->enabled = 1;
        $theitem->modifiedby=$USER->id;
        $theitem->timemodified=time();

        //reload cache flag.
        //If we are registering a new event, then we need to purge the events cache
        $reloadcache =false;
        $eventhooks = local_trigger\webhook\webhooks::fetch_webhooks($theitem->event);
        if(count($eventhooks)==0){$reloadcache=true;}

        //first insert a new item if we need to
        $theitem->id = \local_trigger\webhook\webhooks::add_item($theitem);

        if (!$theitem->id){
            $successcode = "1";
            $message = 'failed to insert trigger item';
        }else{
            //reload cache if we need to
            if($reloadcache){
                purge_caches([]);
            }
            $successcode = "0";
            $message = "All good";
        }


        $result = array();
        $result['returnCode'] = $successcode;
        $result['returnMessage'] = $message;

        return $result;
    }


    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function register_webhook_returns() {
        return
            new external_single_structure(
                array(
                    'returnCode' => new external_value(PARAM_TEXT, 'A code indicating success or otherwise'),
                    'returnMessage' => new external_value(PARAM_TEXT, 'A description of what happened')
            ));
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function deregister_webhook_parameters() {
        $params = array();
        $params['event'] = new external_value(PARAM_TEXT, 'The media type, either audio or video', VALUE_DEFAULT, 'audio');
        $params['hook'] = new external_value(PARAM_TEXT, 'The URL of the webhook', VALUE_DEFAULT, 'http://127.0.0.1');
        return new external_function_parameters(
            $params
        );
    }

    /**
     * Returns result of action
     * @return array result of action
     */
    public static function deregister_webhook($event, $hook)
    {
        global $USER, $DB;

        $success = false;

        $rawparams = array();
        $rawparams['event'] = $event;
        $rawparams['hook'] = $hook;

        //Parameter validation
        $params = self::validate_parameters(self::deregister_webhook_parameters(),
            $rawparams);

        //Context validation
        $context = context_user::instance($USER->id);
        self::validate_context($context);

        //Capability checking
        if (!has_capability('local/trigger:canmanagewebhooks', $context)) {
            throw new moodle_exception('nopermission');
        }


        $webhook_record = $DB->get_record(constants::WEBHOOK_TABLE,array('event'=>$params['event'],'webhook'=>$params['hook'],'authid'=>$USER->id));
        if($webhook_record) {
            $success = \local_trigger\webhook\webhooks::delete_item($webhook_record->id);
        }

        if (!$success){
            $successcode = "1";
            $message = 'failed to insert trigger item';
        }else{
            purge_caches([]);
            $successcode = "0";
            $message = "All good";
        }

        $result = array();
        $result['returnCode'] = $successcode;
        $result['returnMessage'] = $message;

        return $result;
    }


    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function deregister_webhook_returns() {
        return
            new external_single_structure(
                array(
                    'returnCode' => new external_value(PARAM_TEXT, 'A code indicating success or otherwise'),
                    'returnMessage' => new external_value(PARAM_TEXT, 'A description of what happened')
                ));
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function sample_webhook_parameters() {
        $params = array();
        $params['event'] = new external_value(PARAM_TEXT, 'event name', VALUE_REQUIRED);
        return new external_function_parameters(
            $params
        );
    }

    /**
     * Returns result of action
     * @return array result of action
     */
    public static function sample_webhook($event)
    {
        global $USER, $DB;


        $rawparams = array();
        $rawparams['event'] = $event;

        //Parameter validation
        $params = self::validate_parameters(self::sample_webhook_parameters(),
            $rawparams);

        //Context validation
        $context = context_user::instance($USER->id);
        self::validate_context($context);

        //Capability checking
        if (!has_capability('local/trigger:canmanagewebhooks', $context)) {
            throw new moodle_exception('nopermission');
        }

       $webhook_record = $DB->get_record(constants::SAMPLE_TABLE,array('event'=>$params['event']),'*',IGNORE_MULTIPLE);
       if($webhook_record){
           return ['eventdata'=>json_decode($webhook_record->eventdata)];
       }else{
           return [];
       }
    }


    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function sample_webhook_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'eventdata' => new external_value(PARAM_RAW, 'JSON event data'),
                )
            )
        );
    }


}//end of class
