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
 * Cohort enrolment plugin event handler definition.
 *
 * @package local_trigger
 * @category local plugin
 * @copyright 2017 Justin Hunt {@link https://poodll.com}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$config = get_config('local_trigger');
$observers = array();
if($config && property_exists($config, 'triggercount')) {
    for ($tindex = 1; $tindex <= $config->triggercount; $tindex++){
        if(property_exists($config, 'triggerevent'.$tindex) && property_exists($config, 'triggerwebhook'.$tindex) ){
            if(!empty($config->{'triggerevent' . $tindex}) && !empty($config->{'triggerwebhook' . $tindex})){
                $observers[] = array(
                    'eventname' => $config->{'triggerevent' . $tindex},
                    'callback' => '\local_trigger\event_trigger::trigger',
                    'internal' => false
                );
            }
        }
    }
}
