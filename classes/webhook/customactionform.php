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
class customactionform extends \moodleform {


    /**
     * Add the required basic elements to the form.
     *
     * This method adds the basic elements to the form including title and contents
     * and then calls custom_definition();
     */
    public final function definition() {
        $mform = $this->_form;
	
        $mform->addElement('header', 'typeheading', get_string('createacustomactionitem', 'local_trigger'));

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'itemid');
        $mform->setType('itemid', PARAM_INT);

        //Get all the possible actions
        $actionarray = customactions::get_all_possible_actions();
        $mform->addElement('select', 'action', get_string('customaction', 'local_trigger'),$actionarray,['class'=>'local_trigger_ca_selectbox'] );
		$mform->addRule('action', get_string('required'), 'required', null, 'client');

        //get a description of the action
        $mform->addElement('text', 'description', get_string('description', 'local_trigger'), array('size'=>70));
        $mform->setType('description', PARAM_TEXT);
        $mform->setDefault('description', '');

        //get the protocol of the action, PUT, POST, GET etc
        //$mform->addElement('select', 'protocol', get_string('protocol', 'local_trigger'), constants::PROTOCOLS);
        //$mform->addRule('action', get_string('required'), 'required', null, 'client');
        $mform->addElement('hidden', 'protocol', get_string('protocol', 'local_trigger'));
        $mform->setType('protocol', PARAM_TEXT);
        $mform->setDefault('protocol', 'post');



        //custom text fields
        $maxfields=10;
        //for($fields_text as $field){
        for($i=1;$i<=$maxfields;$i++){
            $field='customtext'. ($i);
            $helpfield='customhelp'. ($i);
            $mform->addElement('text', $field, get_string('customtext', 'local_trigger',$i), array('size'=>70, 'class'=>'local_trigger_' . $field));
            $mform->setType($field, PARAM_TEXT);
            $mform->addElement('text', $helpfield, get_string('customhelp', 'local_trigger',$i), array('size'=>70, 'class'=>'local_trigger_' . $field));
            $mform->setType($helpfield, PARAM_TEXT);
        }

		$mform->addElement('selectyesno', 'enabled', get_string('enabled', 'local_trigger'));
        $mform->setDefault('enabled', true);

		//add the action buttons
        $this->add_action_buttons(get_string('cancel'), get_string('saveitem', 'local_trigger'));

    }

}