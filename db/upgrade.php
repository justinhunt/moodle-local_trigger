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
 * This file keeps track of upgrades to the readaloud module
 *
 * Sometimes, changes between versions involve alterations to database
 * structures and other major things that may break installations. The upgrade
 * function in this file will attempt to perform all the necessary actions to
 * upgrade your older installation to the current version. If there's something
 * it cannot do itself, it will tell you what you need to do.  The commands in
 * here will all be database-neutral, using the functions defined in DLL libraries.
 *
 * @package    local_trigger
 * @copyright  2022 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_trigger\webhook\constants;

defined('MOODLE_INTERNAL') || die();

/**
 * Execute trigger upgrade from the given old version
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_trigger_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes


    if ($oldversion < 2022071501) {
        $table = new xmldb_table(constants::WEBHOOK_TABLE);

        // Adding fields to table local_trigger_webhooks.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('authid', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null);
        $table->add_field('event', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null);
        $table->add_field('webhook', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null);
        $table->add_field('enabled', XMLDB_TYPE_INTEGER, '2', null, null, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('createdby', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('modifiedby', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');

        // Adding keys to table local_trigger_webhooks
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for local_trigger_webhooks
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2022071501, 'local','trigger');
    }

    //Lets use varchar and not text for trigger db fields
    if ($oldversion < 2022112400) {
        $table = new xmldb_table(constants::WEBHOOK_TABLE);
        $fields = array(
            new xmldb_field('authid', XMLDB_TYPE_CHAR, '255'),
            new xmldb_field('event', XMLDB_TYPE_CHAR, '255'),
            new xmldb_field('webhook', XMLDB_TYPE_CHAR, '255')
        );
        foreach ($fields as $fielddef) {
            if($dbman->field_exists($table, $fielddef)){
                $dbman->change_field_type($table, $fielddef);
            }
        }

        upgrade_plugin_savepoint(true, 2022112400, 'local','trigger');
    }

    if ($oldversion < 2022112500) {
        $table = new xmldb_table(constants::SAMPLE_TABLE);

        // Adding fields to table
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('event', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);
        $table->add_field('eventdata', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');

        // Adding keys to table
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2022112500, 'local','trigger');
    }

    if ($oldversion < 2022122800) {
        //The update.php created local_trigger_sample - "event" field as char(255) field type but install.xml created as text field type.
        //This update will change the text type event fields, to char 255. If its already char(255) it will do nothing.
        $table = new xmldb_table(constants::SAMPLE_TABLE);
        $field = new xmldb_field('event', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null);

        //The field, in reality, will already exist in either form, but let's just be safe and check first
        if ( $dbman->field_exists($table, $field)) {
            $dbman->change_field_type($table, $field);
        }else{
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2022122800, 'local','trigger');
    }

    if ($oldversion < 2024052600) {
        $table = new xmldb_table(constants::ACTION_TABLE);

        // Adding fields to table local_trigger_actions.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('action', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL, null);
        $table->add_field('protocol', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL, null);
        $table->add_field('params', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null);
        $table->add_field('enabled', XMLDB_TYPE_INTEGER, '2', null, null, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('createdby', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('modifiedby', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');

        // Adding keys to table local_trigger_actions
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for local_trigger_actions.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        upgrade_plugin_savepoint(true, 2024052600, 'local','trigger');
    }

    //we always needs to resync the custom actions after an upgrade
    if($oldversion > 2024052600){
        //We need to sync the custom actions
        if($DB->count_records(constants::ACTION_TABLE)>0) {
            //it is too soon to call this, so we use an adhoc task
            // \local_trigger\webhook\customactions::sync_custom_actions();

            $sync_task = new \local_trigger\task\trigger_sync_adhoc();
            $sync_task->set_component('local_trigger');
            \core\task\manager::queue_adhoc_task($sync_task);
        }
    }

    // Final return of upgrade result (true, all went good) to Moodle.
    return true;
}
