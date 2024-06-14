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

namespace local_trigger\webhook;

defined('MOODLE_INTERNAL') || die();

/**
 *
 * This is a class containing functions for sending triggers
 * @package   local_trigger
 * @copyright 2016 Poodll Co. Ltd (https://poodll.com)
 * @author    Justin Hunt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class customactions
{
   public static function delete_item($itemdata) {
		global $DB;
		$ret = false;
		
        if ($DB->delete_records(constants::ACTION_TABLE, array('id'=>$itemdata->id))){
            self::remove_action_from_trigger_service($itemdata->action);
        	$ret=true;
        }else{
        	print_error("Could not delete item");
        }
		return $ret;
   }

    public static function action_exists($action) {
        global $DB;
        $triggerservice = $DB->get_record('external_services', array('name'=>'Poodll Trigger'));
        if(!$triggerservice){
            return true;//this would be weird and we should really error out, but in any case lets not continue
        }
        $ret = $DB->record_exists('external_services_functions', array('externalserviceid'=>$triggerservice->id,'functionname'=>$action));
        return $ret;
    }

    public static function add_item($itemdata) {
		global $DB;
		$ret = false;
		
        $ret=$DB->insert_record(constants::ACTION_TABLE,$itemdata);
        if(!$ret){
        	print_error("Could not insert item");
        }else{
            self::add_action_to_trigger_service($itemdata->action);
        }
		return $ret;
   }  
   
   public static function update_item($olditem,$newitem) {
		global $DB;
		$ret = false;
		
        if ($DB->update_record(constants::ACTION_TABLE,$newitem)){
            if(!empty($olditem->action) && $olditem->action != $newitem->action) {
                self::remove_action_from_trigger_service($olditem->action);
                self::add_action_to_trigger_service($newitem->action);
            }
        	$ret=true;
        }else{
        	print_error("Could not update item");
        }
		return $ret;
   }

   public static function add_action_to_trigger_service($functionname){
       global $DB;
       $triggerservice = $DB->get_record('external_services', array('name'=>'Poodll Trigger'));
       if($triggerservice){
           $exists = $DB->record_exists('external_services_functions', array('externalserviceid'=>$triggerservice->id,'functionname'=>$functionname));
           if(!$exists){
               $DB->insert_record('external_services_functions', array('externalserviceid'=>$triggerservice->id,'functionname'=>$functionname));
           }
       }
   }
    public static function remove_action_from_trigger_service($functionname){
        global $DB;
        $triggerservice = $DB->get_record('external_services', array('name'=>'Poodll Trigger'));
        if($triggerservice){
            $existingrecord = $DB->get_record('external_services_functions', array('externalserviceid'=>$triggerservice->id,'functionname'=>$functionname));
            if($existingrecord){
                $DB->delete_records('external_services_functions', array('id'=>$existingrecord->id));
            }
        }
    }

    public static function needs_sync($triggerservice,$functionname){
        global $DB;
        $exists =false;
        if($triggerservice){
            $exists = $DB->record_exists('external_services_functions', array('externalserviceid'=>$triggerservice->id,'functionname'=>$functionname));
        }
        return !$exists;
    }

    public static function sync_custom_actions(){
       global $DB;

        $existingactions= $DB->get_records(constants::ACTION_TABLE,[]);
        if($existingactions) {
            foreach($existingactions as $action) {
                self::add_action_to_trigger_service($action->action);
            }
        }
    }
   
   public static function fetch_items(){
		global $DB;
		//when installing several plugins at once, we can arrive here BEFORE table created. ouch
		$tables = $DB->get_tables();
        $triggerservice = $DB->get_record('external_services', array('name'=>'Poodll Trigger'));
		$records =false;
		if(in_array(constants::ACTION_TABLE,$tables)) {
            $records = $DB->get_records(constants::ACTION_TABLE, array());
            foreach($records as $record){
                $record->needs_sync = self::needs_sync($triggerservice,$record->action);
            }
        }
        return $records;
    }
    
    public static function fetch_item($itemid){
		global $DB;
        $record = $DB->get_record(constants::ACTION_TABLE,array('id'=>$itemid));
        return $record;
    }
   
    public static function fetch_actions($actionname=false,$enabled=true){
		global $DB;
        $params = array();

        if($actionname) {
            $records = $DB->get_records_select(constants::ACTION_TABLE,
                     $DB->sql_compare_text('action') . ' = ? AND enabled = ?',
                    array( $actionname,$enabled));
        }else{
            $records = $DB->get_records(constants::ACTION_TABLE);
        }

        if($records){
        	foreach($records as $record){
        		$params[]=$record->params;
        	}
        }
        return $params;
    }

    public static function fetch_full_actions($actionname=false,$enabled=true){
        global $DB;
        $params = array();

        if($actionname) {
            $records = $DB->get_records_select(constants::ACTION_TABLE,
                $DB->sql_compare_text('action') . ' = ? AND enabled = ?',
                array( $actionname,$enabled));
        }else{
            $records = $DB->get_records(constants::ACTION_TABLE);
        }

        return $records;
    }

    public static function pack_params($data){
       $maxfields=10;
       $params = [];
        for($i=1;$i<=$maxfields;$i++){
            $field='customtext'. ($i);
            $helpfield='customhelp'. ($i);
           if(isset($data->$field) &&!empty($data->$field)){
               $params[$field] = $data->$field;
               if (isset($data->{$helpfield})) {
                   $params[$helpfield] = $data->$helpfield;
               }else{
                   $params[$helpfield]  = '';
               }
           }
       }
       return json_encode($params);
    }

    public static function unpack_params($data){
       $maxfields=10;
        if(isset($data->params) && self::is_json($data->params)) {
            $params = json_decode($data->params);
            for($i=1;$i<=$maxfields;$i++){
                $field='customtext'. ($i);
                $helpfield='customhelp'. ($i);
                if (isset($params->{$field}) && !empty($params->{$field})) {
                    $data->$field = $params->{$field};
                    if (isset($params->{$field})) {
                        $data->$helpfield = $params->{$helpfield};
                    }else{
                        $data->$helpfield = '';
                    }
                }
            }
        }
        return $data;
    }
    
    public static function call_action($action, $event_data){
       global $CFG;
        require_once($CFG->libdir . '/filelib.php');

            $actionurl = $CFG->wwwroot . '/local/trigger/actions/' . $action->action . '.php';
            $paramsdef=json_decode($action->params);
            $action_data=\stdClass();
            foreach ($paramsdef as $key => $fieldname) {
                //if we have a params def customint1 => 'courseid'
                //we will look for event_data->customint1 and if it exists we will set action_data->courseid to
                // whatever customint1 is
                if(isset($event_data->$key)) {
                    $action_data->$fieldname = $event_data->$key;
                }
            }

            // Only http and https links supported.
            if (!preg_match('|^https?://|i', $actionurl)) {
                    return false;
            }

            $options = array();
            $options['CURLOPT_SSL_VERIFYPEER'] = true;
            $options['CURLOPT_CONNECTTIMEOUT'] = 30;
            $options['CURLOPT_FOLLOWLOCATION'] = 1;
            $options['CURLOPT_MAXREDIRS'] = 5;
            $options['CURLOPT_RETURNTRANSFER'] = true;
            $options['CURLOPT_NOBODY'] = false;
            $options['CURLOPT_TIMEOUT'] = 30;
            $postdata = json_encode($action_data);

            $curl = new \curl();

            if (isset($postdata)) {
                $content = $curl->post($actionurl, $postdata, $options);
            } else {
                $content = $curl->get($actionurl, null, $options);
            }

            $info       = $curl->get_info();
            $error_no   = $curl->get_errno();
            $rawheaders = $curl->get_raw_response();

            if ($error_no) {
                $error = $content;
                debugging("cURL request for \"$actionurl\" failed with: $error ($error_no)", DEBUG_ALL);
                return false;
            }

            if (empty($info['http_code'])) {
                debugging("cURL request for \"$actionurl\" failed, HTTP response code: missing", DEBUG_ALL);
                return false;
            }

            if ($info['http_code'] != 200) {
                debugging("cURL request for \"$actionurl\" failed, HTTP response code: ". $info['http_code'] , DEBUG_ALL);
                return false;
            }

            //if we got here, we are all good baby
            return true;
    }
    
    public static function register($action,$params,$description,$enabled){
    	$item=new \stdClass();
    	$item->action=$action;
    	$item->params = $params;
        $item->description= $description;
    	$item->enabled= $enabled;
    	$ret = self::add_item($item);
    	return $ret;
	}
	
	public static function deregister(){
		$config = get_config('local_trigger');
	}

    public static function get_all_possible_actions(){
        global $DB;
        $functions = $DB->get_records('external_functions', [], 'name');
        $ret=[];
        foreach ($functions as $function) {
            $ret[$function->name] = $function->name;
        }
        return $ret;
    }

    public static function is_json($string) {
        if(!$string){return false;}
        if(empty($string)){return false;}
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}