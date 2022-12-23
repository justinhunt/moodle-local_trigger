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
           return '[' . $webhook_record->eventdata . ']';

       }else{
           switch($event){
                //use dummy data
               case '\core\event\user_created':
                   $eventdata='{"eventname":"\\\\core\\\\event\\\\user_created","component":"core","action":"created","target":"user","objecttable":"user","objectid":"145","crud":"c","edulevel":0,"contextid":675,"contextlevel":30,"contextinstanceid":"145","userid":"145","courseid":0,"relateduserid":"145","anonymous":0,"other":null,"timecreated":1669465782,"user":{"id":"145","auth":"manual","confirmed":"1","policyagreed":"0","deleted":"0","suspended":"0","mnethostid":"1","username":"donaldduck","idnumber":"","firstname":"Donald","lastname":"Duck","email":"donaldduck@poodll.com","emailstop":"0","phone1":"","phone2":"","institution":"","department":"","address":"","city":"","country":"","lang":"en","calendartype":"gregorian","theme":"","timezone":"99","firstaccess":"0","lastaccess":"0","lastlogin":"0","currentlogin":"0","lastip":"","secret":"","picture":"0","description":"","descriptionformat":"1","mailformat":"1","maildigest":"0","maildisplay":"2","autosubscribe":"1","trackforums":"0","timecreated":"1669465781","timemodified":"1669465781","trustbitmask":"0","imagealt":"","lastnamephonetic":"","firstnamephonetic":"","middlename":"","alternatename":"","moodlenetprofile":""}}';
                   break;

               case '\core\event\user_updated':
                   $eventdata='{"eventname":"\\\\core\\\\event\\\\user_updated","component":"core","action":"updated","target":"user","objecttable":"user","objectid":"145","crud":"u","edulevel":0,"contextid":675,"contextlevel":30,"contextinstanceid":"145","userid":"145","courseid":0,"relateduserid":"145","anonymous":0,"other":null,"timecreated":1669466167,"user":{"id":"145","auth":"manual","confirmed":"1","policyagreed":"0","deleted":"0","suspended":"0","mnethostid":"1","username":"donaldduck","idnumber":"","firstname":"Donald","lastname":"Ducky","email":"donaldduck@poodll.com","emailstop":"0","phone1":"","phone2":"","institution":"","department":"","address":"","city":"","country":"","lang":"en","calendartype":"gregorian","theme":"","timezone":"99","firstaccess":"0","lastaccess":"0","lastlogin":"0","currentlogin":"0","lastip":"","secret":"","picture":"0","description":"","descriptionformat":"1","mailformat":"1","maildigest":"0","maildisplay":"2","autosubscribe":"1","trackforums":"0","timecreated":"1669465781","timemodified":"1669466167","trustbitmask":"0","imagealt":"","lastnamephonetic":"","firstnamephonetic":"","middlename":"","alternatename":"","moodlenetprofile":""}}';
                   break;

               case '\mod_quiz\event\attempt_submitted':
                   $eventdata='{"eventname":"\\\\mod_quiz\\\\event\\\\attempt_submitted","component":"mod_quiz","action":"submitted","target":"attempt","objecttable":"quiz_attempts","objectid":"3","crud":"u","edulevel":2,"contextid":674,"contextlevel":70,"contextinstanceid":"165","userid":"145","courseid":"2","relateduserid":"145","anonymous":0,"other":{"submitterid":"145","quizid":"41"},"timecreated":1669466925,"user":{"id":"145","auth":"manual","confirmed":"1","policyagreed":"0","deleted":"0","suspended":"0","mnethostid":"1","username":"donaldduck","idnumber":"","firstname":"Donald","lastname":"Ducky","email":"donaldduck@poodll.com","emailstop":"0","phone1":"","phone2":"","institution":"","department":"","address":"","city":"","country":"","lang":"en","calendartype":"gregorian","theme":"","timezone":"99","firstaccess":"0","lastaccess":"0","lastlogin":"0","currentlogin":"0","lastip":"","secret":"","picture":"0","description":"","descriptionformat":"1","mailformat":"1","maildigest":"0","maildisplay":"2","autosubscribe":"1","trackforums":"0","timecreated":"1669465781","timemodified":"1669466167","trustbitmask":"0","imagealt":"","lastnamephonetic":"","firstnamephonetic":"","middlename":"","alternatename":"","moodlenetprofile":""},"course":{"id":"2","category":"1","sortorder":"10004","fullname":"course one","shortname":"courseone","idnumber":"","summary":"","summaryformat":"1","format":"topics","showgrades":"1","newsitems":"5","startdate":"1626188400","enddate":"0","relativedatesmode":"0","marker":"0","maxbytes":"0","legacyfiles":"0","showreports":"0","visible":"1","visibleold":"1","downloadcontent":null,"groupmode":"1","groupmodeforce":"0","defaultgroupingid":"0","lang":"","calendartype":"","theme":"boost","timecreated":"1626137390","timemodified":"1656769442","requested":"0","enablecompletion":"1","completionnotify":"0","cacherev":"1669466682","originalcourseid":null,"showactivitydates":"1","showcompletionconditions":"1"}}';
                   break;

               case '\core\event\course_completed':
                $eventdata='{"eventname":"\\\\core\\\\event\\\\course_completed","component":"core","action":"completed","target":"course","objecttable":"course_completions","objectid":"28","crud":"u","edulevel":2,"contextid":686,"contextlevel":50,"contextinstanceid":"10","userid":"145","courseid":"10","relateduserid":"145","anonymous":0,"other":{"relateduserid":"145"},"timecreated":1669471709,"user":{"id":"145","auth":"manual","confirmed":"1","policyagreed":"0","deleted":"0","suspended":"0","mnethostid":"1","username":"donaldduck","idnumber":"","firstname":"Donald","lastname":"Ducky","email":"donaldduck@poodll.com","emailstop":"0","phone1":"","phone2":"","institution":"","department":"","address":"","city":"","country":"","lang":"en","calendartype":"gregorian","theme":"","timezone":"99","firstaccess":"0","lastaccess":"0","lastlogin":"0","currentlogin":"0","lastip":"","secret":"","picture":"0","description":"","descriptionformat":"1","mailformat":"1","maildigest":"0","maildisplay":"2","autosubscribe":"1","trackforums":"0","timecreated":"1669465781","timemodified":"1669466167","trustbitmask":"0","imagealt":"","lastnamephonetic":"","firstnamephonetic":"","middlename":"","alternatename":"","moodlenetprofile":""},"course":{"id":"10","category":"1","sortorder":"10001","fullname":"How to fly south safely","shortname":"htfss","idnumber":"","summary":"How to fly south safely","summaryformat":"1","format":"topics","showgrades":"1","newsitems":"5","startdate":"1669474800","enddate":"0","relativedatesmode":"0","marker":"0","maxbytes":"0","legacyfiles":"0","showreports":"0","visible":"1","visibleold":"1","downloadcontent":null,"groupmode":"0","groupmodeforce":"0","defaultgroupingid":"0","lang":"","calendartype":"","theme":"","timecreated":"1669467149","timemodified":"1669467149","requested":"0","enablecompletion":"1","completionnotify":"0","cacherev":"1669471577","originalcourseid":null,"showactivitydates":"1","showcompletionconditions":"1"}}';
                break;

               case '\core\event\user_enrolment_created':
                   $eventdata='{"eventname":"\\\\core\\\\event\\\\user_enrolment_created","component":"core","action":"created","target":"user_enrolment","objecttable":"user_enrolments","objectid":27,"crud":"c","edulevel":0,"contextid":25,"contextlevel":50,"contextinstanceid":"2","userid":"145","courseid":"2","relateduserid":"145","anonymous":0,"other":{"enrol":"manual"},"timecreated":1669466706,"user":{"id":"145","auth":"manual","confirmed":"1","policyagreed":"0","deleted":"0","suspended":"0","mnethostid":"1","username":"donaldduck","idnumber":"","firstname":"Donald","lastname":"Ducky","email":"donaldduck@poodll.com","emailstop":"0","phone1":"","phone2":"","institution":"","department":"","address":"","city":"","country":"","lang":"en","calendartype":"gregorian","theme":"","timezone":"99","firstaccess":"0","lastaccess":"0","lastlogin":"0","currentlogin":"0","lastip":"","secret":"","picture":"0","description":"","descriptionformat":"1","mailformat":"1","maildigest":"0","maildisplay":"2","autosubscribe":"1","trackforums":"0","timecreated":"1669465781","timemodified":"1669466167","trustbitmask":"0","imagealt":"","lastnamephonetic":"","firstnamephonetic":"","middlename":"","alternatename":"","moodlenetprofile":""},"course":{"id":"2","category":"1","sortorder":"10004","fullname":"course one","shortname":"courseone","idnumber":"","summary":"","summaryformat":"1","format":"topics","showgrades":"1","newsitems":"5","startdate":"1626188400","enddate":"0","relativedatesmode":"0","marker":"0","maxbytes":"0","legacyfiles":"0","showreports":"0","visible":"1","visibleold":"1","downloadcontent":null,"groupmode":"1","groupmodeforce":"0","defaultgroupingid":"0","lang":"","calendartype":"","theme":"boost","timecreated":"1626137390","timemodified":"1656769442","requested":"0","enablecompletion":"1","completionnotify":"0","cacherev":"1669466682","originalcourseid":null,"showactivitydates":"1","showcompletionconditions":"1"}}';
                   break;

               case '\core\event\user_enrolment_deleted':
                   $eventdata='{"eventname":"\\\\core\\\\event\\\\user_enrolment_deleted","component":"core","action":"deleted","target":"user_enrolment","objecttable":"user_enrolments","objectid":"26","crud":"d","edulevel":0,"contextid":25,"contextlevel":50,"contextinstanceid":"2","userid":"145","courseid":"2","relateduserid":"145","anonymous":0,"other":{"userenrolment":{"id":"26","status":"0","enrolid":"1","userid":"145","timestart":"1669466299","timeend":"0","modifierid":"2","timecreated":"1669466383","timemodified":"1669466383","courseid":"2","enrol":"manual","lastenrol":true},"enrol":"manual"},"timecreated":1669466395,"user":{"id":"145","auth":"manual","confirmed":"1","policyagreed":"0","deleted":"0","suspended":"0","mnethostid":"1","username":"donaldduck","idnumber":"","firstname":"Donald","lastname":"Ducky","email":"donaldduck@poodll.com","emailstop":"0","phone1":"","phone2":"","institution":"","department":"","address":"","city":"","country":"","lang":"en","calendartype":"gregorian","theme":"","timezone":"99","firstaccess":"0","lastaccess":"0","lastlogin":"0","currentlogin":"0","lastip":"","secret":"","picture":"0","description":"","descriptionformat":"1","mailformat":"1","maildigest":"0","maildisplay":"2","autosubscribe":"1","trackforums":"0","timecreated":"1669465781","timemodified":"1669466167","trustbitmask":"0","imagealt":"","lastnamephonetic":"","firstnamephonetic":"","middlename":"","alternatename":"","moodlenetprofile":""},"course":{"id":"2","category":"1","sortorder":"10004","fullname":"course one","shortname":"courseone","idnumber":"","summary":"","summaryformat":"1","format":"topics","showgrades":"1","newsitems":"5","startdate":"1626188400","enddate":"0","relativedatesmode":"0","marker":"0","maxbytes":"0","legacyfiles":"0","showreports":"0","visible":"1","visibleold":"1","downloadcontent":null,"groupmode":"1","groupmodeforce":"0","defaultgroupingid":"0","lang":"","calendartype":"","theme":"boost","timecreated":"1626137390","timemodified":"1656769442","requested":"0","enablecompletion":"1","completionnotify":"0","cacherev":"1669466075","originalcourseid":null,"showactivitydates":"1","showcompletionconditions":"1"}}';
                   break;

               case '\block_xp\event\user_leveledup':
                   $eventdata='{"eventname":"\\\\block_xp\\\\event\\\\user_leveledup","component":"block_xp","action":"leveledup","target":"user","objecttable":null,"objectid":null,"crud":"u","edulevel":0,"contextid":25,"contextlevel":50,"contextinstanceid":"2","userid":"145","courseid":"2","relateduserid":"145","anonymous":0,"other":{"level":4},"timecreated":1669520114,"user":{"id":"145","auth":"manual","confirmed":"1","policyagreed":"0","deleted":"0","suspended":"0","mnethostid":"1","username":"donaldduck","idnumber":"","firstname":"Donald","lastname":"Ducky","email":"donaldduck@poodll.com","emailstop":"0","phone1":"","phone2":"","institution":"","department":"","address":"","city":"","country":"","lang":"en","calendartype":"gregorian","theme":"","timezone":"99","firstaccess":"0","lastaccess":"0","lastlogin":"0","currentlogin":"0","lastip":"","secret":"","picture":"0","description":"","descriptionformat":"1","mailformat":"1","maildigest":"0","maildisplay":"2","autosubscribe":"1","trackforums":"0","timecreated":"1669465781","timemodified":"1669466167","trustbitmask":"0","imagealt":"","lastnamephonetic":"","firstnamephonetic":"","middlename":"","alternatename":"","moodlenetprofile":""},"course":{"id":"2","category":"1","sortorder":"10005","fullname":"course one","shortname":"courseone","idnumber":"","summary":"","summaryformat":"1","format":"topics","showgrades":"1","newsitems":"5","startdate":"1626188400","enddate":"0","relativedatesmode":"0","marker":"0","maxbytes":"0","legacyfiles":"0","showreports":"0","visible":"1","visibleold":"1","downloadcontent":null,"groupmode":"1","groupmodeforce":"0","defaultgroupingid":"0","lang":"","calendartype":"","theme":"boost","timecreated":"1626137390","timemodified":"1656769442","requested":"0","enablecompletion":"1","completionnotify":"0","cacherev":"1669520037","originalcourseid":null,"showactivitydates":"1","showcompletionconditions":"1"}}';
                   break;

               default:
                   $eventdata= '';
           }
           return '[' . $eventdata . ']';
       }
    }


    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function sample_webhook_returns() {
        return    new external_value(PARAM_RAW, 'JSON event data');
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.5
     */
    public static function remove_cohort_members_parameters() {
        return new external_function_parameters (
            array(
                'members' => new external_multiple_structure (
                    new external_single_structure (
                        array (
                            'cohorttype' => new external_single_structure (
                                array(
                                    'type' => new external_value(PARAM_ALPHANUMEXT, 'The name of the field: id
                                        (numeric value of cohortid) or idnumber (alphanumeric value of idnumber) '),
                                    'value' => new external_value(PARAM_RAW, 'The value of the cohort')
                                )
                            ),
                            'usertype' => new external_single_structure (
                                array(
                                    'type' => new external_value(PARAM_ALPHANUMEXT, 'The name of the field: id
                                        (numeric value of id) or username (alphanumeric value of username) '),
                                    'value' => new external_value(PARAM_RAW, 'The value of the cohort')
                                )
                            )
                        )
                    )
                )
            )
        );
    }

    /**
     * Add cohort members
     *
     * @param array $members of arrays with keys userid, cohortid
     * @since Moodle 2.5
     */
    public static function remove_cohort_members($members) {
        global $CFG, $DB;
        require_once($CFG->dirroot."/cohort/lib.php");

        $params = self::validate_parameters(self::remove_cohort_members_parameters(), array('members' => $members));

        $transaction = $DB->start_delegated_transaction();
        $warnings = array();
        foreach ($params['members'] as $member) {
            // Cohort parameters.
            $cohorttype = $member['cohorttype'];
            $cohortparam = array($cohorttype['type'] => $cohorttype['value']);
            // User parameters.
            $usertype = $member['usertype'];
            $userparam = array($usertype['type'] => $usertype['value']);
            try {
                // Check parameters.
                if ($cohorttype['type'] != 'id' && $cohorttype['type'] != 'idnumber') {
                    $warning = array();
                    $warning['warningcode'] = '1';
                    $warning['message'] = 'invalid parameter: cohortype='.$cohorttype['type'];
                    $warnings[] = $warning;
                    continue;
                }
                if ($usertype['type'] != 'id' && $usertype['type'] != 'username') {
                    $warning = array();
                    $warning['warningcode'] = '1';
                    $warning['message'] = 'invalid parameter: usertype='.$usertype['type'];
                    $warnings[] = $warning;
                    continue;
                }
                // Extract parameters.
                if (!$cohortid = $DB->get_field('cohort', 'id', $cohortparam)) {
                    $warning = array();
                    $warning['warningcode'] = '2';
                    $warning['message'] = 'cohort '.$cohorttype['type'].'='.$cohorttype['value'].' not exists';
                    $warnings[] = $warning;
                    continue;
                }
                if (!$userid = $DB->get_field('user', 'id', array_merge($userparam, array('deleted' => 0,
                    'mnethostid' => $CFG->mnet_localhost_id)))) {
                    $warning = array();
                    $warning['warningcode'] = '2';
                    $warning['message'] = 'user '.$usertype['type'].'='.$usertype['value'].' not exists';
                    $warnings[] = $warning;
                    continue;
                }

                $cohort = $DB->get_record('cohort', array('id'=>$cohortid), '*', MUST_EXIST);
                $context = context::instance_by_id($cohort->contextid, MUST_EXIST);
                if ($context->contextlevel != CONTEXT_COURSECAT and $context->contextlevel != CONTEXT_SYSTEM) {
                    $warning = array();
                    $warning['warningcode'] = '1';
                    $warning['message'] = 'Invalid context: '.$context->contextlevel;
                    $warnings[] = $warning;
                    continue;
                }
                self::validate_context($context);
            } catch (Exception $e) {
                throw new moodle_exception('Error', 'cohort', '', $e->getMessage());
            }
            if (!has_any_capability(array('moodle/cohort:manage', 'moodle/cohort:assign'), $context)) {
                throw new required_capability_exception($context, 'moodle/cohort:assign', 'nopermissions', '');
            }

            if ($DB->record_exists('cohort_members', array('cohortid' => $cohortid, 'userid' => $userid))) {
                cohort_remove_member($cohortid, $userid);
            }

        }
        $transaction->allow_commit();
        // Return.
        $result = array();
        $result['warnings'] = $warnings;
        return $result;
    }

    /**
     * Returns description of method result value
     *
     * @return null
     * @since Moodle 2.5
     */
    public static function remove_cohort_members_returns() {
        return new external_single_structure(
            array(
                'warnings' => new external_warnings()
            )
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.5
     */
    public static function add_cohort_members_parameters() {
        return new external_function_parameters (
            array(
                'members' => new external_multiple_structure (
                    new external_single_structure (
                        array (
                            'cohorttype' => new external_single_structure (
                                array(
                                    'type' => new external_value(PARAM_ALPHANUMEXT, 'The name of the field: id
                                        (numeric value of cohortid) or idnumber (alphanumeric value of idnumber) '),
                                    'value' => new external_value(PARAM_RAW, 'The value of the cohort')
                                )
                            ),
                            'usertype' => new external_single_structure (
                                array(
                                    'type' => new external_value(PARAM_ALPHANUMEXT, 'The name of the field: id
                                        (numeric value of id) or username (alphanumeric value of username) '),
                                    'value' => new external_value(PARAM_RAW, 'The value of the cohort')
                                )
                            )
                        )
                    )
                )
            )
        );
    }

    /**
     * Add cohort members
     *
     * @param array $members of arrays with keys userid, cohortid
     * @since Moodle 2.5
     */
    public static function add_cohort_members($members) {
        global $CFG, $DB;
        require_once($CFG->dirroot."/cohort/lib.php");

        $params = self::validate_parameters(self::add_cohort_members_parameters(), array('members' => $members));

        $transaction = $DB->start_delegated_transaction();
        $warnings = array();
        foreach ($params['members'] as $member) {
            // Cohort parameters.
            $cohorttype = $member['cohorttype'];
            $cohortparam = array($cohorttype['type'] => $cohorttype['value']);
            // User parameters.
            $usertype = $member['usertype'];
            $userparam = array($usertype['type'] => $usertype['value']);
            try {
                // Check parameters.
                if ($cohorttype['type'] != 'id' && $cohorttype['type'] != 'idnumber') {
                    $warning = array();
                    $warning['warningcode'] = '1';
                    $warning['message'] = 'invalid parameter: cohortype='.$cohorttype['type'];
                    $warnings[] = $warning;
                    continue;
                }
                if ($usertype['type'] != 'id' && $usertype['type'] != 'username') {
                    $warning = array();
                    $warning['warningcode'] = '1';
                    $warning['message'] = 'invalid parameter: usertype='.$usertype['type'];
                    $warnings[] = $warning;
                    continue;
                }
                // Extract parameters.
                if (!$cohortid = $DB->get_field('cohort', 'id', $cohortparam)) {
                    $warning = array();
                    $warning['warningcode'] = '2';
                    $warning['message'] = 'cohort '.$cohorttype['type'].'='.$cohorttype['value'].' not exists';
                    $warnings[] = $warning;
                    continue;
                }
                if (!$userid = $DB->get_field('user', 'id', array_merge($userparam, array('deleted' => 0,
                    'mnethostid' => $CFG->mnet_localhost_id)))) {
                    $warning = array();
                    $warning['warningcode'] = '2';
                    $warning['message'] = 'user '.$usertype['type'].'='.$usertype['value'].' not exists';
                    $warnings[] = $warning;
                    continue;
                }

                $cohort = $DB->get_record('cohort', array('id'=>$cohortid), '*', MUST_EXIST);
                $context = context::instance_by_id($cohort->contextid, MUST_EXIST);
                if ($context->contextlevel != CONTEXT_COURSECAT and $context->contextlevel != CONTEXT_SYSTEM) {
                    $warning = array();
                    $warning['warningcode'] = '1';
                    $warning['message'] = 'Invalid context: '.$context->contextlevel;
                    $warnings[] = $warning;
                    continue;
                }
                self::validate_context($context);
            } catch (Exception $e) {
                throw new moodle_exception('Error', 'cohort', '', $e->getMessage());
            }
            if (!has_any_capability(array('moodle/cohort:manage', 'moodle/cohort:assign'), $context)) {
                throw new required_capability_exception($context, 'moodle/cohort:assign', 'nopermissions', '');
            }
            //only add if they are not already in the cohort
            if (!$DB->record_exists('cohort_members', array('cohortid' => $cohortid, 'userid' => $userid))) {
                cohort_add_member($cohortid, $userid);
            }

        }
        $transaction->allow_commit();
        // Return.
        $result = array();
        $result['warnings'] = $warnings;
        return $result;
    }

    /**
     * Returns description of method result value
     *
     * @return null
     * @since Moodle 2.5
     */
    public static function add_cohort_members_returns() {
        return new external_single_structure(
            array(
                'warnings' => new external_warnings()
            )
        );
    }


}//end of class
