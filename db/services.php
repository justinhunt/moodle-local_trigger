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
 * Web service local plugin template external functions and service definitions.
 *
 * @package    local_trigger
 * @copyright  2022 Justin Hunt (justin@poodll.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// We defined the web service functions to install.
$functions = array(
    'local_trigger_register_webhook' => array(
        'classname'   => 'local_trigger_services',
        'methodname'  => 'register_webhook',
        'classpath'   => 'local/trigger/externallib.php',
        'description' => 'Will register a webhook',
        'type'        => 'write',
        'capabilities'=> 'local/trigger:canmanagewebhooks'
    ),

    'local_trigger_deregister_webhook' => array(
            'classname'   => 'local_trigger_services',
            'methodname'  => 'deregister_webhook',
            'classpath'   => 'local/trigger/externallib.php',
            'description' => 'Will deregister a webhook',
            'type'        => 'read',
            'capabilities'=> 'local/trigger:canmanagewebhooks'
    ),

    'local_trigger_sample_webhook' => array(
        'classname'   => 'local_trigger_services',
        'methodname'  => 'sample_webhook',
        'classpath'   => 'local/trigger/externallib.php',
        'description' => 'Will return a sample event data',
        'type'        => 'read',
        'capabilities'=> 'local/trigger:canmanagewebhooks'
    ),

    'local_trigger_add_cohort_members' => array(
        'classname'   => 'local_trigger_services',
        'methodname'  => 'add_cohort_members',
        'classpath'   => 'local/trigger/externallib.php',
        'description' => 'Will add a user to a cohort',
        'type'        => 'write',
        'capabilities'=> 'local/trigger:canmanagewebhooks'
    ),

    'local_trigger_remove_cohort_members' => array(
        'classname'   => 'local_trigger_services',
        'methodname'  => 'remove_cohort_members',
        'classpath'   => 'local/trigger/externallib.php',
        'description' => 'Will remove a user from a cohort',
        'type'        => 'write',
        'capabilities'=> 'local/trigger:canmanagewebhooks'
    ),

    'local_trigger_fetch_function_details' => array(
        'classname'   => 'local_trigger_services',
        'methodname'  => 'fetch_function_details',
        'classpath'   => 'local/trigger/externallib.php',
        'description' => 'Get the params of a custom web service function',
        'type'        => 'read',
        'capabilities'=> 'local/trigger:canmanagewebhooks',
        'ajax'        => true,
    ),

    'local_trigger_get_customactions' => array(
        'classname'   => 'local_trigger_services',
        'methodname'  => 'get_customactions',
        'classpath'   => 'local/trigger/externallib.php',
        'description' => 'Get a list of custom web service functions',
        'type'        => 'read',
        'capabilities'=> 'local/trigger:canmanagewebhooks',
        'ajax'        => true,
    ),

    'local_trigger_get_customaction_details' => array(
        'classname'   => 'local_trigger_services',
        'methodname'  => 'get_customaction_details',
        'classpath'   => 'local/trigger/externallib.php',
        'description' => 'Get the params of a custom web service function',
        'type'        => 'read',
        'capabilities'=> 'local/trigger:canmanagewebhooks',
        'ajax'        => true,
    )
);

$services = array(
    'Poodll Trigger' => array(
        'functions' => array(
            'local_trigger_add_cohort_members',
            'local_trigger_remove_cohort_members',
            'core_course_get_courses',
            'core_course_get_courses_by_field',
            'core_enrol_get_enrolled_users',
            'core_user_create_users',
            'core_user_delete_users',
            'core_user_get_users',
            'core_user_get_users_by_field',
            'core_user_update_users',
            'core_group_add_group_members',
            'core_group_delete_group_members',
            'core_group_get_course_groups',
            'enrol_manual_enrol_users',
            'enrol_manual_unenrol_users',
            'local_trigger_deregister_webhook',
            'local_trigger_register_webhook',
            'local_trigger_sample_webhook',
            'local_trigger_get_customactions',
            'local_trigger_get_customaction_details',
            'core_webservice_get_site_info'
        ),
        'requiredcapability' => 'local/trigger:canmanagewebhooks',
        'enabled' => 1,
        'restrictedusers' => 1,
        'downloadfiles' => 0,
        'uploadfiles' => 0
    )
);

