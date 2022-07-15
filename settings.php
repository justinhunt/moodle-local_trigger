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


    // Ensure the configurations for this site are set
    if ($hassiteconfig ) {

        // Create the new settings page
        $settings = new admin_settingpage('local_trigger',get_string('triggersettings', 'local_trigger'),'local/trigger:canviewsettings');
        // Create
        $ADMIN->add('localplugins', $settings );

        $ADMIN->add('root', new admin_category('trigger', new lang_string('pluginname', 'local_trigger')));
        $ADMIN->add('trigger', new admin_externalpage('trigger/webhooks',
        new lang_string('webhooks', 'local_trigger'),
        new moodle_url('/local/trigger/webhooks.php'),'local/trigger:canviewsettings'));
    }