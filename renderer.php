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


defined('MOODLE_INTERNAL') || die();


/**
 * A custom renderer class that extends the plugin_renderer_base.
 *
 * @package local_trigger
 * @copyright COPYRIGHTNOTICE
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_trigger_renderer extends plugin_renderer_base {

 /**
 * Return HTML to display add first page links
 * @param lesson $lesson
 * @return string
 */
 public function add_edit_page_links($edittype) {
		global $CFG;
        $itemid = 0;


        switch($edittype){
            case 'webhooks':
                $links = array();
                $itemurl = new moodle_url('/local/trigger/managewebhooks.php',
                    array('itemid'=>$itemid));
                $links[] = html_writer::link($itemurl, get_string('additemwebhook', "local_trigger"));
                break;
            case 'customactions':

                $links = array();
                $itemurl = new moodle_url('/local/trigger/managecustomactions.php',
                    array('itemid'=>$itemid));
                $links[] = html_writer::link($itemurl, get_string('additemcustomaction', "local_trigger"));
                break;
        }
        return $this->output->box('<p>'.implode('</p><p>', $links).'</p>', 'generalbox firstpageoptions');
    }

    public function add_customaction_sync(){
        $items = array();
        $itemurl = new moodle_url('/local/trigger/managecustomactions.php',
            array('action'=>'sync'));
        $items[] = html_writer::link($itemurl, get_string('sync_customactions', "local_trigger"),array('class'=>'btn btn-primary'));
        $items[] = html_writer::span(get_string('sync_customactions_help', "local_trigger"));
        return $this->output->box('<p>'.implode('</p><p>', $items).'</p>', 'generalbox firstpageoptions');
    }
	
	/**
	 * Return the table of items
	 * @param array homework objects
	 * @param integer $courseid
	 * @return string html of table
	 */
	function show_webhook_items_list($items){
	
		if(!$items){
			return $this->output->heading(get_string('noitems',"local_trigger"), 3, 'main');
		}
	
		$table = new html_table();
		$table->id = 'local_trigger_qpanel';
		$table->head = array(
			get_string('event', "local_trigger"),
			get_string('webhook', "local_trigger"),
            get_string('description', "local_trigger"),
			get_string('enabled', "local_trigger"),
			get_string('actions', "local_trigger")
		);
		$table->headspan = array(1,1,1,1,2);
		$table->colclasses = array(
			'eventcol', 'webhookcol', 'descriptioncol','enabledcol', 'edit','preview','delete'
		);

		//sort by start date
		core_collator::asort_objects_by_property($items,'timecreated',core_collator::SORT_NUMERIC);

		//loop through the homoworks and add to table
		foreach ($items as $item) {
			$row = new html_table_row();
		
		
			$eventcell = new html_table_cell($item->event);	
			$webhookcell = new html_table_cell($item->webhook);	
			$descriptioncell = new html_table_cell($item->description);
            $enabledcell = $item->enabled ? new html_table_cell(get_string('yes')) : new html_table_cell(get_string('no')) ;


            $actionurl = '/local/trigger/managewebhooks.php';
			$editurl = new moodle_url($actionurl, array('itemid'=>$item->id));
			$editlink = html_writer::link($editurl, get_string('edititem', "local_trigger"));
			$editcell = new html_table_cell($editlink);
/*
            $actionurl = '/local/trigger/managewebhooks.php';
            $sampleurl = new moodle_url($actionurl, array('itemid'=>$item->id,'action'=>'sampledata'));
            $samplelink = html_writer::link($sampleurl, 'SampleData');
            $samplecell = new html_table_cell($samplelink);
*/
			$deleteurl = new moodle_url($actionurl, array('itemid'=>$item->id,'action'=>'confirmdelete'));
			$deletelink = html_writer::link($deleteurl, get_string('deleteitem', "local_trigger"));
			$deletecell = new html_table_cell($deletelink);

			$row->cells = array(
				$eventcell, $webhookcell, $descriptioncell,$enabledcell, $editcell, $deletecell, // $samplecell
			);
			$table->data[] = $row;
		}

		return html_writer::table($table);

	}

    /**
     * Return the table of items
     * @param array homework objects
     * @param integer $courseid
     * @return string html of table
     */
    function show_customaction_items_list($items){

        if(!$items){
            return $this->output->heading(get_string('noitems',"local_trigger"), 3, 'main');
        }

        $table = new html_table();
        $table->id = 'local_trigger_qpanel';
        $table->head = array(
            get_string('customaction', "local_trigger"),
            get_string('params', "local_trigger"),
            get_string('description', "local_trigger"),
            get_string('synced', "local_trigger"),
            get_string('enabled', "local_trigger"),
            get_string('actions', "local_trigger")
        );
        $table->headspan = array(1,1,1,1,1,2);
        $table->colclasses = array(
            'actioncol','paramscol', 'descriptioncol','synced','enabledcol', 'edit','preview','delete'
        );

        //sort by start date
        core_collator::asort_objects_by_property($items,'timecreated',core_collator::SORT_NUMERIC);

        //loop through the homeworks and add to table
        foreach ($items as $item) {
            $row = new html_table_row();


            $actioncell = new html_table_cell($item->action);
            $paramscell = new html_table_cell(shorten_text ($item->params, 50));
            $descriptioncell = new html_table_cell($item->description);
            $enabledcell = $item->enabled ? new html_table_cell(get_string('yes')) : new html_table_cell(get_string('no')) ;
            $syncedcell = new html_table_cell($item->needs_sync ?  '<i class="fa fa-times"></i>' : '<i class="fa fa-check"></i>');

            $actionurl = '/local/trigger/managecustomactions.php';
            $editurl = new moodle_url($actionurl, array('itemid'=>$item->id));
            $editlink = html_writer::link($editurl, get_string('edititem', "local_trigger"));
            $editcell = new html_table_cell($editlink);
            $deleteurl = new moodle_url($actionurl, array('itemid'=>$item->id,'action'=>'confirmdelete'));
            $deletelink = html_writer::link($deleteurl, get_string('deleteitem', "local_trigger"));
            $deletecell = new html_table_cell($deletelink);

            $row->cells = array(
                $actioncell, $paramscell, $descriptioncell,$syncedcell,$enabledcell, $editcell, $deletecell
            );
            $table->data[] = $row;
        }
        return html_writer::table($table);
    }

}