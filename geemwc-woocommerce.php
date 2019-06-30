<?php
/*

Plugin Name: Geem for WooCommerce
Plugin URI: https://github.com/geemcoin/geem-woocommerce/
Description: Geem for WooCommerce plugin allows you to accept payments in Geem for physical and digital products at your WooCommerce-powered online store.
Version: 2.4.2
Author: KittyCatTech, The Geem maintainer.
Author URI: https://github.com/geemcoin/geem-woocommerce/
License: BipCot NoGov Software License bipcot.org

*/


// Include everything
include (dirname(__FILE__) . '/geemwc-include-all.php');

//---------------------------------------------------------------------------
// Add hooks and filters

// create custom plugin settings menu
add_action( 'admin_menu',                   'GEEMWC_create_menu' );

register_activation_hook(__FILE__,          'GEEMWC_activate');
register_deactivation_hook(__FILE__,        'GEEMWC_deactivate');
register_uninstall_hook(__FILE__,           'GEEMWC_uninstall');

add_filter ('cron_schedules',               'GEEMWC__add_custom_scheduled_intervals');
add_action ('GEEMWC_cron_action',             'GEEMWC_cron_job_worker');     // Multiple functions can be attached to 'GEEMWC_cron_action' action

GEEMWC_set_lang_file();
//---------------------------------------------------------------------------

//===========================================================================
// activating the default values
function GEEMWC_activate()
{
    global  $g_GEEMWC__config_defaults;

    $geemwc_default_options = $g_GEEMWC__config_defaults;

    // This will overwrite default options with already existing options but leave new options (in case of upgrading to new version) untouched.
    $geemwc_settings = GEEMWC__get_settings ();

    foreach ($geemwc_settings as $key=>$value)
    	$geemwc_default_options[$key] = $value;

    update_option (GEEMWC_SETTINGS_NAME, $geemwc_default_options);

    // Re-get new settings.
    $geemwc_settings = GEEMWC__get_settings ();

    // Create necessary database tables if not already exists...
    GEEMWC__create_database_tables ($geemwc_settings);
    GEEMWC__SubIns ();

    //----------------------------------
    // Setup cron jobs

    if ($geemwc_settings['enable_soft_cron_job'] && !wp_next_scheduled('GEEMWC_cron_action'))
    {
    	$cron_job_schedule_name = $geemwc_settings['soft_cron_job_schedule_name'];
    	wp_schedule_event(time(), $cron_job_schedule_name, 'GEEMWC_cron_action');
    }
    //----------------------------------

}
//---------------------------------------------------------------------------
// Cron Subfunctions
function GEEMWC__add_custom_scheduled_intervals ($schedules)
{
	$schedules['seconds_30']     = array('interval'=>30,     'display'=>__('Once every 30 seconds'));
	$schedules['minutes_1']      = array('interval'=>1*60,   'display'=>__('Once every 1 minute'));
	$schedules['minutes_2.5']    = array('interval'=>2.5*60, 'display'=>__('Once every 2.5 minutes'));
	$schedules['minutes_5']      = array('interval'=>5*60,   'display'=>__('Once every 5 minutes'));

	return $schedules;
}
//---------------------------------------------------------------------------
//===========================================================================

//===========================================================================
// deactivating
function GEEMWC_deactivate ()
{
    // Do deactivation cleanup. Do not delete previous settings in case user will reactivate plugin again...

    //----------------------------------
    // Clear cron jobs
    wp_clear_scheduled_hook ('GEEMWC_cron_action');
    //----------------------------------
}
//===========================================================================

//===========================================================================
// uninstalling
function GEEMWC_uninstall ()
{
    $geemwc_settings = GEEMWC__get_settings();

    if ($geemwc_settings['delete_db_tables_on_uninstall'])
    {
        // delete all settings.
        delete_option(GEEMWC_SETTINGS_NAME);

        // delete all DB tables and data.
        GEEMWC__delete_database_tables ();
    }
}
//===========================================================================

//===========================================================================
function GEEMWC_create_menu()
{

    // create new top-level menu
    // http://www.fileformat.info/info/unicode/char/e3f/index.htm
    add_menu_page (
        __('Woo Geem', GEEMWC_I18N_DOMAIN),                    // Page title
        __('Geem', GEEMWC_I18N_DOMAIN),                        // Menu Title - lower corner of admin menu
        'administrator',                                        // Capability
        'geemwc-settings',                                        // Handle - First submenu's handle must be equal to parent's handle to avoid duplicate menu entry.
        'GEEMWC__render_general_settings_page',                   // Function
        plugins_url('/images/geem_16x.png', __FILE__)      // Icon URL
        );

    add_submenu_page (
        'geemwc-settings',                                        // Parent
        __("WooCommerce Geem Gateway", GEEMWC_I18N_DOMAIN),                   // Page title
        __("General Settings", GEEMWC_I18N_DOMAIN),               // Menu Title
        'administrator',                                        // Capability
        'geemwc-settings',                                        // Handle - First submenu's handle must be equal to parent's handle to avoid duplicate menu entry.
        'GEEMWC__render_general_settings_page'                    // Function
        );

}
//===========================================================================

//===========================================================================
// load language files
function GEEMWC_set_lang_file()
{
    # set the language file
    $currentLocale = get_locale();
    if(!empty($currentLocale))
    {
        $moFile = dirname(__FILE__) . "/lang/" . $currentLocale . ".mo";
        if (@file_exists($moFile) && is_readable($moFile))
        {
            load_textdomain(GEEMWC_I18N_DOMAIN, $moFile);
        }

    }
}
//===========================================================================

