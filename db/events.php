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
 * Local trigger plugin event handler definition.
 *
 * @package local_trigger
 * @category local plugin
 * @copyright 2017 Justin Hunt {@link https://poodll.com}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use \local_trigger\webhook\constants;

$config = get_config('local_trigger');
$observers = array();
$added = array();

$webhooks = \local_trigger\webhook\webhooks::fetch_items();
if($webhooks) {
    foreach ($webhooks as $webhook) {
        //we only want to add each event once, even if multiple trigger webhooks for it are registered
        //when it fires we loop through the registered hooks
        if (!array_key_exists($webhook->event, $added)) {
            $added[$webhook->event] = true;
            $observers[] = array(
                'eventname' => $webhook->event,
                'callback' => '\local_trigger\event_trigger::trigger',
                'internal' => false
            );
        }
    }
}
//These webhooks are to fetch sample data for creating zaps
foreach(constants::SAMPLE_EVENTS as $sample_event){
    if(class_exists($sample_event)) {
        $observers[] = array(
            'eventname' => $sample_event,
            'callback' => '\local_trigger\event_trigger::sample',
            'internal' => false
        );
    }
}
