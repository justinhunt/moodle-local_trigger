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
class webhooks
{
   
   public static function delete_item($itemid) {
		global $DB;
		$ret = false;
		
        if ($DB->delete_records(constants::WEBHOOK_TABLE, array('id'=>$itemid))){
        	$ret=true;
        }else{
        	print_error("Could not delete item");
        }
		return $ret;
   }
   
   public static function add_item($itemdata) {
		global $DB;
		$ret = false;
		
        $ret=$DB->insert_record(constants::WEBHOOK_TABLE,$itemdata);
        if(!$ret){
        	print_error("Could not insert item");
        }
		return $ret;
   }  
   
   public static function update_item($itemdata) {
		global $DB;
		$ret = false;
		
        if ($DB->update_record(constants::WEBHOOK_TABLE,$itemdata)){
        	$ret=true;
        }else{
        	print_error("Could not update item");
        }
		return $ret;
   } 
   
   public static function fetch_items(){
		global $DB;
        $records = $DB->get_records(constants::WEBHOOK_TABLE,array());
        
        return $records;
    }
    
    public static function fetch_item($itemid){
		global $DB;
        $record = $DB->get_record(constants::WEBHOOK_TABLE,array('id'=>$itemid));
        
        return $record;
    }
   
    public static function fetch_webhooks($eventname=false,$enabled=true){
		global $DB;
        $webhooks = array();

        if($eventname) {
            $records = $DB->get_records_select(constants::WEBHOOK_TABLE,
                     $DB->sql_compare_text('event') . ' = ? AND enabled = ?',
                    array( $eventname,$enabled));
        }else{
            $records = $DB->get_records(constants::WEBHOOK_TABLE);
        }

        if($records){
        	foreach($records as $record){
        		$webhooks[]=$record->webhook;
        	}
        }
        return $webhooks;
    }
    
    public static function call_webhook($webhook, $event_data){
       global $CFG;
        require_once($CFG->libdir . '/filelib.php');

            // Only http and https links supported.
            if (!preg_match('|^https?://|i', $webhook)) {
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
            $postdata = json_encode($event_data);

            $curl = new \curl();


            if (isset($postdata)) {
                $content = $curl->post($webhook, $postdata, $options);
            } else {
                $content = $curl->get($webhook, null, $options);
            }


            $info       = $curl->get_info();
            $error_no   = $curl->get_errno();
            $rawheaders = $curl->get_raw_response();

            if ($error_no) {
                $error = $content;
                debugging("cURL request for \"$webhook\" failed with: $error ($error_no)", DEBUG_ALL);
                return false;
            }

            if (empty($info['http_code'])) {
                debugging("cURL request for \"$webhook\" failed, HTTP response code: missing", DEBUG_ALL);
                return false;
            }

            if ($info['http_code'] != 200) {
                debugging("cURL request for \"$webhook\" failed, HTTP response code: ". $info['http_code'] , DEBUG_ALL);
                return false;
            }

            //if we got here, we are all good baby
            return true;
    }
    
    public static function register($authid,$event,$webhook,$enabled){
    	$item=new stdClass();
    	$item->authid=$authid;
    	$item->event=$event;
    	$item->webhook = $webhook;
    	$item->enabled= $enabled;
    	$ret = self::insert_item($item);
    	return $ret;
	}
	
	public static function deregister(){
		$config = get_config('local_trigger');
	
	}
}