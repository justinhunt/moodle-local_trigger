<?php
///////////////////////////////////////////////////////////////////////////
//                                                                       //
// This file is part of Moodle - http://moodle.org/                      //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//                                                                       //
// Moodle is free software: you can redistribute it and/or modify        //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation, either version 3 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// Moodle is distributed in the hope that it will be useful,             //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details.                          //
//                                                                       //
// You should have received a copy of the GNU General Public License     //
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.       //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

namespace local_trigger\webhook;

defined('MOODLE_INTERNAL') || die();

/**
 * Forms for trigger Activity
 *
 * @package    mod_trigger
 * @author     Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Justin Hunt  http://poodll.com
 */

require_once($CFG->libdir . '/formslib.php');


/**
 * Abstract class that item type's inherit from.
 *
 * This is the abstract class that add item type forms must extend.
 *
 * @abstract
 * @copyright  2014 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class webhookform extends \moodleform {


    /**
     * Add the required basic elements to the form.
     *
     * This method adds the basic elements to the form including title and contents
     * and then calls custom_definition();
     */
    public final function definition() {
        $mform = $this->_form;
	
        $mform->addElement('header', 'typeheading', get_string('createaitem', 'local_trigger'));

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'itemid');
        $mform->setType('itemid', PARAM_INT);


        $eventarray = \report_eventlist_list_generator::get_all_events_list(false);
        foreach ($eventarray as $key=>$value){
            $eventarray[$key]=$key;
        }
        $mform->addElement('select', 'event', get_string('event', 'local_trigger'), $eventarray);
        //$mform->addElement('text', 'event', get_string('event', 'local_trigger'), array('size'=>70));
		//$mform->setType('event', PARAM_TEXT);
		$mform->addRule('event', get_string('required'), 'required', null, 'client');
		
		$mform->addElement('text', 'webhook', get_string('webhook', 'local_trigger'), array('size'=>70));
		$mform->setType('webhook', PARAM_URL);
		$mform->addRule('webhook', get_string('required'), 'required', null, 'client');
		
		$mform->addElement('text', 'description', get_string('description', 'local_trigger'), array('size'=>70));
		$mform->setType('description', PARAM_TEXT);
		$mform->setDefault('description', '');

		$mform->addElement('selectyesno', 'enabled', get_string('enabled', 'local_trigger'));


		//add the action buttons
        $this->add_action_buttons(get_string('cancel'), get_string('saveitem', 'local_trigger'));

    }

}