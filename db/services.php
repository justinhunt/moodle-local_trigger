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
 * @package    localtrigger
 * @copyright  2011 Jerome Mouneyrac
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// We defined the web service functions to install.
$functions = array(
    'local_trigger_hello_world' => array(
        'classname'   => 'local_trigger_services',
        'methodname'  => 'hello_world',
        'classpath'   => 'local/trigger/externallib.php',
        'description' => 'Will say Hello',
        'type'        => 'read'
    ),
        'local_trigger_create_user' => array(
                'classname'   => 'local_trigger_services',
                'methodname'  => 'create_user',
                'classpath'   => 'local/trigger/externallib.php',
                'description' => 'Will create a Moodle user',
                'type'        => 'write'
        ),
        'local_trigger_delete_user' => array(
            'classname'   => 'local_trigger_services',
            'methodname'  => 'delete_user',
            'classpath'   => 'local/trigger/externallib.php',
            'description' => 'Will delete a Moodle user',
            'type'        => 'write'
        ),
        'local_trigger_enrol_user' => array(
            'classname'   => 'local_trigger_services',
            'methodname'  => 'enrol_user',
            'classpath'   => 'local/trigger/externallib.php',
            'description' => 'Will enrol a Moodle user in one or more courses',
            'type'        => 'write'
        ), 'local_trigger_unenrol_user' => array(
            'classname'   => 'local_trigger_services',
            'methodname'  => 'unenrol_user',
            'classpath'   => 'local/trigger/externallib.php',
            'description' => 'Will unenrol a user from one or more courses',
            'type'        => 'write'
        )
);

// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
$services = array(
        'Trigger Service' => array(
                'functions' => array ('local_trigger_hello_world',
                    'local_trigger_create_user',
                        'local_trigger_delete_user',
                        'local_trigger_enrol_user',
                        'local_trigger_unenrol_user'),
                'restrictedusers' => 0,
                'enabled'=>1,
        )
);
