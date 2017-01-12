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
 * External Web Service Template
 *
 * @package    local_trigger
 * @copyright  2011 Moodle Pty Ltd (http://moodle.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->libdir . "/externallib.php");

class local_trigger_services extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function hello_world_parameters() {
        return new external_function_parameters(
                array('welcomemessage' => new external_value(PARAM_TEXT, 'The welcome message. By default it is "Hello world,"', VALUE_DEFAULT, 'Hello world, '))
        );
    }

    /**
     * Returns welcome message
     * @return string welcome message
     */
    public static function hello_world($welcomemessage = 'Hello world, ') {
        global $USER;

        //Parameter validation
        //REQUIRED
        $params = self::validate_parameters(self::hello_world_parameters(),
                array('welcomemessage' => $welcomemessage));

        //Context validation
        //OPTIONAL but in most web service it should present
        $context = context_user::instance($USER->id);
        self::validate_context($context);

        //Capability checking
        //OPTIONAL but in most web service it should present
        if (!has_capability('moodle/user:viewdetails', $context)) {
            throw new moodle_exception('cannotviewprofile');
        }

        return $params['welcomemessage'] . $USER->firstname ;;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function hello_world_returns() {
        return new external_value(PARAM_TEXT, 'The welcome message + user first name');
    }

    /*
     * Create user
     */

    public static function create_user($welcomemessage = 'Hello world'){
        //do something
    }
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function create_user_parameters() {
        return new external_function_parameters(
            ['userdetails' => new external_value(PARAM_TEXT, 'The user details', VALUE_DEFAULT, 'bah blah')]
        );
    }
    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function create_user_returns() {
        return new external_value(PARAM_TEXT, 'The result of action');
    }

    /*
     * Delete user
     */

    public static function delete_user($welcomemessage = 'Hello world'){
        //do something
    }
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function delete_user_parameters() {
        return new external_function_parameters(
            ['userdetails' => new external_value(PARAM_TEXT, 'The user details', VALUE_DEFAULT, 'bah blah')]
        );
    }
    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function delete_user_returns() {
        return new external_value(PARAM_TEXT, 'The result of action');
    }

    /*
     * Enrol user
     */

    public static function enrol_user($welcomemessage = 'Hello world'){
        //do something
    }
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function enrol_user_parameters() {
        return new external_function_parameters(
            ['userdetails'=> new external_value(PARAM_TEXT, 'The user details', VALUE_DEFAULT, 'bah blah'),
                'coursedetails' => new external_value(PARAM_TEXT, 'The course details', VALUE_DEFAULT, 'bah blah')]
        );
    }
    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function enrol_user_returns() {
        return new external_value(PARAM_TEXT, 'The result of action');
    }

    /*
     * Unenrol user
     */

    public static function unenrol_user($welcomemessage = 'Hello world'){
        //do something
    }
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function unenrol_user_parameters() {
        return new external_function_parameters(
            ['userdetails' => new external_value(PARAM_TEXT, 'The user details', VALUE_DEFAULT, 'bah blah'),
                'coursedetails' => new external_value(PARAM_TEXT, 'The course details', VALUE_DEFAULT, 'bah blah')]
        );
    }
    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function unenrol_user_returns() {
        return new external_value(PARAM_TEXT, 'The result of action');
    }

}
