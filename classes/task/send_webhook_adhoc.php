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
 * An adhoc task to send a webhook asynchronously.
 *
 * @package    local_trigger
 * @copyright  2026 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_trigger\task;

use local_trigger\webhook\constants;
use local_trigger\webhook\webhooks;
use local_trigger\event\webhook_called;

defined('MOODLE_INTERNAL') || die();

/**
 * Adhoc task to deliver webhooks in background.
 *
 * @package    local_trigger
 * @copyright  2026 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class send_webhook_adhoc extends \core\task\adhoc_task {

    /**
     * Run the task.
     */
    public function execute() {
        global $DB;
        $trace = new \text_progress_trace();

        $data = $this->get_custom_data();
        if (empty($data) || empty($data->webhook) || empty($data->event_data)) {
            $trace->output("send_webhook_adhoc: missing custom data, aborting.");
            return;
        }

        $webhook = $data->webhook;
        // Cast stdClass to array recursively if needed for event_data.
        $event_data = json_decode(json_encode($data->event_data), true);
        $webhook_record = !empty($data->webhook_record) ? $data->webhook_record : null;
        $attemptcount = !empty($data->attemptcount) ? (int)$data->attemptcount : 1;
        $eventname = !empty($event_data['eventname']) ? $event_data['eventname'] : 'unknown';

        $trace->output("Executing webhook delivery for {$eventname} to {$webhook} (attempt {$attemptcount})");

        $success = webhooks::call_webhook($webhook, $event_data);

        if (!$success) {
            $maxattempts = 3;
            if ($attemptcount < $maxattempts) {
                $delays = [1 => 60, 2 => 300]; // 1 min, then 5 mins
                $delay = isset($delays[$attemptcount]) ? $delays[$attemptcount] : 300;
                $data->attemptcount = $attemptcount + 1;

                $trace->output("Webhook delivery failed for {$webhook}. Scheduling retry {$data->attemptcount} in {$delay} seconds.");

                $retrytask = new send_webhook_adhoc();
                $retrytask->set_component('local_trigger');
                $retrytask->set_custom_data($data);
                $retrytask->set_next_run_time(time() + $delay);
                \core\task\manager::queue_adhoc_task($retrytask);
            } else {
                $trace->output("Webhook delivery failed for {$webhook} after {$attemptcount} attempts. Giving up.");
            }
            return;
        }

        $trace->output("Webhook delivered successfully to {$webhook}");

        // Save sample data for Zapier/API inspection.
        if (!empty($event_data['eventname']) && !in_array($event_data['eventname'], constants::SAMPLE_EVENTS)) {
            try {
                if ($DB->record_exists(constants::SAMPLE_TABLE, array('event' => $event_data['eventname']))) {
                    $DB->delete_records(constants::SAMPLE_TABLE, array('event' => $event_data['eventname']));
                }
                $DB->insert_record(constants::SAMPLE_TABLE, array(
                    'event' => $event_data['eventname'],
                    'eventdata' => json_encode($event_data, JSON_INVALID_UTF8_SUBSTITUTE)
                ));
            } catch (\Exception $e) {
                debugging("Failed to store sample data for {$event_data['eventname']}: " . $e->getMessage(), DEBUG_ALL);
            }
        }

        // Trigger our own event to confirm the webhook was sent.
        if ($webhook_record) {
            try {
                webhook_called::create_event($webhook_record, $event_data)->trigger();
            } catch (\Exception $e) {
                debugging("Failed to trigger webhook_called event: " . $e->getMessage(), DEBUG_ALL);
            }
        }
    }
}
