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
 * Action for adding/editing a webhook. 
 *
 * @package mod_trigger
 * @copyright  2014 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

require_once("../../config.php");
require_once($CFG->libdir . '/adminlib.php');

$context = context_system::instance();

/// Set up the page header
$PAGE->set_context($context);
$PAGE->set_url('/local/trigger/managewebhooks.php');
$PAGE->set_title(get_string('managewebhooks','local_trigger'));
$PAGE->set_heading(get_string('managewebhooks','local_trigger'));
$PAGE->set_pagelayout('admin');

require_login();
require_capability('local/trigger:canviewsettings',$context);


// first collect any params passed into this page
$itemid = optional_param('itemid',0 ,PARAM_INT); 
$action = optional_param('action','edit',PARAM_TEXT);


/*
//set up the page object
$PAGE->set_url('/mod/trigger/webhook/managewebhooks.php', array('itemid'=>$itemid, 'id'=>$id, 'type'=>$type));
$PAGE->set_title(format_string($trigger->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->set_pagelayout('course');
*/

//are we in new or edit mode?
if ($itemid) {
    $item = \local_trigger\webhook\webhooks::fetch_item($itemid);
	if(!$item){
		print_error('could not find item of id:' . $itemid);
	}
    $edit = true;
} else {
    $edit = false;
}

//we always head back to the trigger items page
$redirecturl = new moodle_url('/local/trigger/webhooks.php', array());

	//handle delete actions
    if($action == 'confirmdelete'){
		$renderer = $PAGE->get_renderer('local_trigger');
		echo $renderer->header(get_string('confirmitemdeletetitle', 'local_trigger'),2);
		echo $renderer->confirm(get_string("confirmitemdelete","local_trigger",$item->event), 
			new moodle_url('/local/trigger/managewebhooks.php', array('action'=>'delete','itemid'=>$itemid)), 
			$redirecturl);
		echo $renderer->footer();
		return;

	/////// Delete item NOW////////
    }elseif ($action == 'delete'){
    	require_sesskey();
		$success = \local_trigger\webhook\webhooks::delete_item($itemid);
        redirect($redirecturl);
	
    }

	//get the mform for our item
	$mform = new \local_trigger\webhook\webhookform(null,array());
	

//if the cancel button was pressed, we are out of here
if ($mform->is_cancelled()) {
    redirect($redirecturl);
    exit;
}

//if we have data, then our job here is to save it and return to the webhook edit page
if ($data = $mform->get_data()) {
		require_sesskey();
		
		$theitem = new stdClass;
        $theitem->id = $data->itemid;
        $theitem->authid = $USER->id;  //do this better soon
		$theitem->webhook = $data->webhook;
		$theitem->event = $data->event;
		$theitem->description = $data->description;
		$theitem->enabled = $data->enabled;
		$theitem->modifiedby=$USER->id;
		$theitem->timemodified=time();
		
		//first insert a new item if we need to
		//that will give us a itemid, we need that for saving files
		if($edit){
			//now update the db once we have saved files and stuff
			if (!\local_trigger\webhook\webhooks::update_item($theitem)){
					error("Could not update trigger item!");
					redirect($redirecturl);
			}
		}else{
			$theitem->id = \local_trigger\webhook\webhooks::add_item($theitem);

			if (!$theitem->id){
					error("Could not insert trigger item!");
					redirect($redirecturl);
			}
		}			
		//go back to edit quiz page
		redirect($redirecturl);
}


//if  we got here, there was no cancel, and no form data, so we are showing the form
//if edit mode load up the item into a data object
if ($edit) {
	$data = $item;		
	$data->itemid = $itemid;
	$mform->set_data($data);
}else{
	$data=new stdClass;
	$data->itemid = null;
	$data->visible = 1;
}
				

$renderer = $PAGE->get_renderer('local_trigger');
echo $renderer->header(get_string('edit', 'local_trigger'),2);
$mform->display();
echo $renderer->footer();