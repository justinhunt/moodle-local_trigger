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


    if ($oldversion < 2022071500) {
        $table = new xmldb_table('local_trigger_webhooks');

        // Adding fields to table tool_dataprivacy_contextlist.
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

        // Adding keys to table tool_dataprivacy_contextlist.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for tool_dataprivacy_contextlist.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }


        upgrade_mod_savepoint(true, 2022071500, 'local_trigger');
    }

    // Final return of upgrade result (true, all went good) to Moodle.
    return true;
}
