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
 * Action for adding/editing a custom action
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
$PAGE->set_url('/local/trigger/managecustomactions.php');
$PAGE->set_title(get_string('managecustomactions','local_trigger'));
$PAGE->set_heading(get_string('managecustomactions','local_trigger'));
$PAGE->set_pagelayout('admin');

require_login();
require_capability('local/trigger:canviewsettings',$context);


// first collect any params passed into this page
$itemid = optional_param('itemid',0 ,PARAM_INT); 
$action = optional_param('action','edit',PARAM_TEXT);



//are we in new or edit mode?
if ($itemid) {
    $item = \local_trigger\webhook\customactions::fetch_item($itemid);
	if(!$item){
		print_error('could not find item of id:' . $itemid);
	}
    $edit = true;
} else {
    $edit = false;
}

//we always head back to the trigger items page
$redirecturl = new moodle_url('/local/trigger/webhooks.php', array());

    if($action=='sampledata'){
        $customaction_record = $DB->get_record(\local_trigger\webhooks\constants::SAMPLE_TABLE,
            array('event'=>$item->event),'*',IGNORE_MULTIPLE);
        if($customaction_record) {
            echo $customaction_record->eventdata;
        }else{
            echo "no sample data for this event";
        }
        die;
    }

	//handle delete actions
    if($action == 'confirmdelete'){

        $renderer = $PAGE->get_renderer('local_trigger');
		echo $renderer->header(get_string('confirmitemdeletetitle', 'local_trigger'),2);
		echo $renderer->confirm(get_string("confirmitemdelete","local_trigger",$item->action),
			new moodle_url('/local/trigger/managecustomactions.php', array('action'=>'delete','itemid'=>$itemid)),
			$redirecturl);
		echo $renderer->footer();
		return;

	/////// Delete item NOW////////
    }elseif ($action == 'delete'){
    	require_sesskey();
		$success = \local_trigger\webhook\customactions::delete_item($item);
        redirect($redirecturl);
	
    }

	//get the mform for our item
	$mform = new \local_trigger\webhook\customactionform(null,array());
	

//if the cancel button was pressed, we are out of here
if ($mform->is_cancelled()) {
    redirect($redirecturl);
    exit;
}

//if we have data, then our job here is to save it and return to the customaction edit page
if ($data = $mform->get_data()) {
		require_sesskey();
		
		$theitem = new stdClass;
        $theitem->id = $data->itemid;
        $theitem->action = $data->action;
        $theitem->protocol = $data->protocol;
        $theitem->params =  local_trigger\webhook\customactions::pack_params($data);
		$theitem->description = $data->description;
		$theitem->enabled = $data->enabled;
		$theitem->modifiedby=$USER->id;
		$theitem->timemodified=time();

		//reload cache flag.
        //If we are registering a new event, then we need to purge the events cache
		$reloadcache =false;
        $eventhooks = local_trigger\webhook\customactions::fetch_actions($theitem->action);
        if(count($eventhooks)==0){$reloadcache=true;}
		
		//first insert a new item if we need to
		if($edit){
			//now update the db
			if (!\local_trigger\webhook\customactions::update_item($item, $theitem)){
					redirect($redirecturl,"Could not update trigger item!");
			}
		}else{
			$theitem->id = \local_trigger\webhook\customactions::add_item($theitem);
			if (!$theitem->id){
					redirect($redirecturl,"Could not insert trigger item!");
			}
		}

		//reload cache if we need to
        if($reloadcache){
            purge_caches([]);
        }

		//go back to edit page
		redirect($redirecturl);
}


//if  we got here, there was no cancel, and no form data, so we are showing the form
//if edit mode load up the item into a data object
if ($edit) {
	$data = $item;		
	$data->itemid = $itemid;
    $data->params = local_trigger\webhook\customactions::unpack_params($data);
	$mform->set_data($data);
}else{
	$data=new \stdClass;
	$data->itemid = null;
	$data->visible = 1;
}
				

$renderer = $PAGE->get_renderer('local_trigger');
$PAGE->requires->js_call_amd('local_trigger/managecustomactions', 'init', array());
echo $renderer->header(get_string('edit', 'local_trigger'),2);
$mform->display();
echo $renderer->footer();