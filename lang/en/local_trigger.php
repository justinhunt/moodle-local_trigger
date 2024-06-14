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
 * Web service template plugin related strings
 * @package   local_trigger
 * @copyright 2016 Poodll Co. Ltd (https://poodll.com)
 * @author    Justin Hunt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Trigger';
$string['triggersettings'] = 'Triggers';
$string['settingheading'] = 'Trigger: ';
$string['trigger:canviewsettings'] = 'Trigger: can view settings';
$string['trigger:canmanagewebhooks'] = 'Trigger: can manage webhooks';
$string['triggerevent'] = 'Trigger event (Moodle)';
$string['triggerwebhook'] = 'Trigger webhook (Zapier)';
$string['triggercount'] = 'Trigger count';
$string['triggercount_desc'] = 'Set the number of triggers you want to register here. After you visit your site notifications area, those settings fields will be added or removed.';
$string['whatdonow'] = 'What would you like to do?';
$string['addnewitem'] = 'Add a new webhook';
$string['createawebhookitem'] = 'Create/edit a webhook';
$string['createacustomactionitem'] = 'Create/edit a custom action';
$string['itemtitle'] = 'Webhook Title';
$string['itemcontents'] = 'Webhook Text';
$string['saveitem'] = 'Save Item';
$string['itemname'] = 'Item Name';
$string['actions'] = 'Actions';
$string['edititem'] = 'Edit';
$string['previewitem'] = 'Preview';
$string['deleteitem'] = 'Delete';
$string['confirmitemdelete'] = 'Are you sure you want to <i>DELETE</i> this item? : {$a}';
$string['confirmitemdeletetitle'] = 'Really delete?';
$string['noitems'] = 'There are no items currently';
$string['additemwebhook'] = 'Add Webhook';
$string['additemcustomaction'] = 'Add Custom Action';
$string['enabled'] = 'Enabled';
$string['edit'] = 'Edit';
$string['webhook'] = 'Webhook';
$string['event'] = 'Event';
$string['webhooks'] = 'Webhooks';
$string['managewebhooks'] = 'Manage Webhooks';
$string['events'] = 'Events';
$string['description'] = 'Description';
$string['privacy:metadata'] = 'The local_trigger plugin does not store any user data locally. It does act as a conduit to send event data to 3rd party services as determined by the site administrator.';
$string['eventtriggerwebhookcalled']="Webhook Called event";
$string['customaction'] = 'Custom Action';
$string['managecustomactions'] = 'Manage Custom Actions';
$string['customactions'] = 'Custom Actions';
$string['params'] = 'Params';
$string['protocol'] = 'Protocol';
$string['webhooks_explanation'] = 'Webhooks allow you to register webhooks(URLs) against events that occur in this Moodle instance. When the event occurs, the webhook is called with the event data. ';
$string['customactions_explanation'] = 'Custom actions allow you to register additional external services APIs that can be called via trigger. This can be useful when you want to call an action in a 3rd party plugin, or a core API that is not included in the local_trigger web services.';
$string['customtext'] = 'Field {$a}';
$string['customhelp'] = 'Field Help {$a}';
$string['synced'] = "In Sync";
$string['syncedcustomactions'] = "All Custom Actions were synced";
$string['sync_customactions'] = "Sync Custom Actions NOW";
$string['sync_customactions_help'] = "Sync Custom Actions - After local_trigger updates your custom actions need to be synced with moodle";
$string['webhooksandactions'] = "Webhooks and Actions";