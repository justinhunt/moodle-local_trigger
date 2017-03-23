<?php  //$Id: settings.php,v 0.0.0.1 2010/01/15 22:40:00 thomw Exp $


/**
 *
 * This is a class containing settings for the trigger plugin
 *
 * @package   local_trigger
 * @copyright 2016 Poodll Co. Ltd (https://poodll.com)
 * @author    Justin Hunt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

defined('MOODLE_INTERNAL') || die;

//if ($ADMIN->fulltree) {

    // Ensure the configurations for this site are set
    if ($hassiteconfig ) {

        // Create the new settings page
        $settings = new admin_settingpage('local_trigger',get_string('triggersettings', 'local_trigger'));
        // Create
        $ADMIN->add('localplugins', $settings );

        //How many triggers
        $conf = get_config('local_trigger');
        if ($conf && property_exists($conf, 'triggercount')) {
            $triggercount = $conf->triggercount;
        } else {
            $triggercount = \local_trigger\settingstools::LOCAL_TRIGGER_DEFAULT_TRIGGER_COUNT;
        }


        //The trigger/hook count setting
        $triggercount_item = \local_trigger\settingstools::fetch_triggercount_item();
        $settings->add($triggercount_item);

        //The trigger/hook pair settings
        $trigger_items = \local_trigger\settingstools::fetch_trigger_items($triggercount);
        foreach ($trigger_items as $trigger_item) {
            $settings->add($trigger_item);
        }
        
        $ADMIN->add('root', new admin_category('trigger', new lang_string('pluginname', 'local_trigger')));
        $ADMIN->add('trigger', new admin_externalpage('trigger/webhooks',
        new lang_string('webhooks', 'local_trigger'),
        new moodle_url('/local/trigger/webhooks.php')));
    }
//}