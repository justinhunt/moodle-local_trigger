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
 * Provides the interface for overall managing of items
 *
 * @package mod_trigger
 * @copyright  2014 Justin Hunt  {@link http://poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$context = context_system::instance();

/// Set up the page header
$PAGE->set_context($context);
$PAGE->set_url('/local/trigger/webhooks.php');
$PAGE->set_title(get_string('webhooks','local_trigger'));
$PAGE->set_heading(get_string('webhooks','local_trigger'));
$PAGE->set_pagelayout('admin');

require_login();


$items =  \local_trigger\webhook\webhooks::fetch_items();

//set up renderer and nav
$renderer = $PAGE->get_renderer('local_trigger');
echo $renderer->header(get_string('webhooks', 'local_trigger'),2);
echo $renderer->add_edit_page_links();
if($items){
	echo $renderer->show_items_list($items);
}
echo $renderer->footer();
