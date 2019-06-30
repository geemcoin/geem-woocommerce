<?php
/*
Geem for WooCommerce
https://github.com/geemcoin/geem-woocommerce/
*/

// Include everything
include (dirname(__FILE__) . '/geemwc-include-all.php');

//===========================================================================
// Global vars.

global $g_GEEMWC__plugin_directory_url;
$g_GEEMWC__plugin_directory_url = plugins_url ('', __FILE__);

global $g_GEEMWC__cron_script_url;
$g_GEEMWC__cron_script_url = $g_GEEMWC__plugin_directory_url . '/geemwc-cron.php';

//===========================================================================

//===========================================================================
// Global default settings
global $g_GEEMWC__config_defaults;
$g_GEEMWC__config_defaults = array (

   // ------- Hidden constants
   'assigned_address_expires_in_mins'     =>  12*60,   // 12 hours to pay for order and receive necessary number of confirmations.
   'funds_received_value_expires_in_mins' =>  '5',		// 'received_funds_checked_at' is fresh (considered to be a valid value) if it was last checked within 'funds_received_value_expires_in_mins' minutes.
   'blockchain_api_timeout_secs'          =>  '20',   // Connection and request timeouts for curl operations dealing with blockchain requests.
   'exchange_rate_api_timeout_secs'       =>  '10',   // Connection and request timeouts for curl operations dealing with exchange rate API requests.
   'soft_cron_job_schedule_name'          =>  'minutes_1',   // WP cron job frequency
   'cache_exchange_rates_for_minutes'			=>	10,			// Cache exchange rate for that number of minutes without re-calling exchange rate API's.

   // ------- General Settings
   'service_provider'				 	  =>  'local_wallet',		// 'blockchain_info'
   'address'                              =>  '', 
   'confs_num'                            =>  '4', // number of confirmations required before accepting payment.
   'exchange_multiplier'                  =>  '1.00',

   'delete_db_tables_on_uninstall'        =>  '0',
   'autocomplete_paid_orders'							=>  '1',
   'enable_soft_cron_job'                 =>  '1',    // Enable "soft" Wordpress-driven cron jobs.

   // ------- Special settings
   'exchange_rates'                       =>  array('EUR' => array('method|type' => array('time-last-checked' => 0, 'exchange_rate' => 1), 'GBP' => array())),
   );
//===========================================================================

//===========================================================================
function GEEMWC__GetPluginNameVersionEdition($please_donate = false) // false to turn off
{
  $return_data = '<h2 style="border-bottom:1px solid #DDD;padding-bottom:10px;margin-bottom:20px;">' .
            GEEMWC_PLUGIN_NAME . ', version: <span style="color:#EE0000;">' .
            GEEMWC_VERSION. '</span>' .
          '</h2>';


  if ($please_donate)
  {
    $return_data .= '<p style="border:1px solid #890e4e;padding:5px 10px;color:#004400;background-color:#FFF;"><u>Please donate GEEM to</u>:&nbsp;&nbsp;<span style="color:#d21577;font-size:110%;font-weight:bold;"></span></p>';
  }

  return $return_data;
}
//===========================================================================

//===========================================================================
function GEEMWC__withdraw ()
{
    $geemwc_settings = GEEMWC__get_settings();
    $address = $geemwc_settings['address'];

    try{
      $wallet_api = New ForkNoteWalletd("http://127.0.0.1:8070");
      $address_balance = $wallet_api->getBalance($address);
    }
    catch(Exception $e) {
    }          

    if ($address_balance === false)
    {
      return "Geem address is not found in wallet.";
    } else {
      $address_balance = $address_balance['availableBalance'];
      //round ( float $val [, int $precision = 0 [, int $mode = PHP_ROUND_HALF_UP ]] )
      $display_address_balance  = sprintf("%.4f", $address_balance  / 100000000.0); 
      $withdraw_fee = 1000000; 
      $display_fee  = sprintf("%.4f", $withdraw_fee  / 100000000.0);
      $send_amount = (floor( $address_balance / 100000000 ) * 100000000 ) - 200000000; // Only allows sending 4 decimal places
      $display_send_amount = sprintf("%.4f", $send_amount  / 100000000.0);
      $send_address = $_POST["withdraw_address"];
      
      try{
        $sent = $wallet_api->sendTransaction( array( $address ), array(array( "amount" => $send_amount, "address" => $send_address)), false, 6, $withdraw_fee, $address );
        return "Withdraw Sent in Transaction: " . $sent["transactionHash"];
        //@TODO Log
      }
      catch(Exception $e) {
        return $e->GetMessage();
      }  
    }
}
//===========================================================================

//===========================================================================
function GEEMWC__get_settings ($key=false)
{
  global   $g_GEEMWC__plugin_directory_url;
  global   $g_GEEMWC__config_defaults;

  $geemwc_settings = get_option (GEEMWC_SETTINGS_NAME);
  if (!is_array($geemwc_settings))
    $geemwc_settings = array();

  if ($key)
    return (@$geemwc_settings[$key]);
  else
    return ($geemwc_settings);
}
//===========================================================================

//===========================================================================
function GEEMWC__update_settings ($geemwc_use_these_settings=false, $also_update_persistent_settings=false)
{
   if ($geemwc_use_these_settings)
      {
      // if ($also_update_persistent_settings)
      //   GEEMWC__update_persistent_settings ($geemwc_use_these_settings);

      update_option (GEEMWC_SETTINGS_NAME, $geemwc_use_these_settings);
      return;
      }

   global   $g_GEEMWC__config_defaults;

   // Load current settings and overwrite them with whatever values are present on submitted form
   $geemwc_settings = GEEMWC__get_settings();

   foreach ($g_GEEMWC__config_defaults as $k=>$v)
      {
      if (isset($_POST[$k]))
         {
         if (!isset($geemwc_settings[$k]))
            $geemwc_settings[$k] = ""; // Force set to something.
         GEEMWC__update_individual_geemwc_setting ($geemwc_settings[$k], $_POST[$k]);
         }
      // If not in POST - existing will be used.
      }

  update_option (GEEMWC_SETTINGS_NAME, $geemwc_settings);
}
//===========================================================================

//===========================================================================
// Takes care of recursive updating
function GEEMWC__update_individual_geemwc_setting (&$geemwc_current_setting, $geemwc_new_setting)
{
   if (is_string($geemwc_new_setting))
      $geemwc_current_setting = GEEMWC__stripslashes ($geemwc_new_setting);
   else if (is_array($geemwc_new_setting))  // Note: new setting may not exist yet in current setting: curr[t5] - not set yet, while new[t5] set.
      {
      // Need to do recursive
      foreach ($geemwc_new_setting as $k=>$v)
         {
         if (!isset($geemwc_current_setting[$k]))
            $geemwc_current_setting[$k] = "";   // If not set yet - force set it to something.
         GEEMWC__update_individual_geemwc_setting ($geemwc_current_setting[$k], $v);
         }
      }
   else
      $geemwc_current_setting = $geemwc_new_setting;
}
//===========================================================================

//===========================================================================
//
// Reset settings only for one screen
function GEEMWC__reset_partial_settings ($also_reset_persistent_settings=false)
{
   global   $g_GEEMWC__config_defaults;

   // Load current settings and overwrite ones that are present on submitted form with defaults
   $geemwc_settings = GEEMWC__get_settings();

   foreach ($_POST as $k=>$v)
      {
      if (isset($g_GEEMWC__config_defaults[$k]))
         {
         if (!isset($geemwc_settings[$k]))
            $geemwc_settings[$k] = ""; // Force set to something.
         GEEMWC__update_individual_geemwc_setting ($geemwc_settings[$k], $g_GEEMWC__config_defaults[$k]);
         }
      }

  update_option (GEEMWC_SETTINGS_NAME, $geemwc_settings);

  // if ($also_reset_persistent_settings)
  //   GEEMWC__update_persistent_settings ($geemwc_settings);
}
//===========================================================================

//===========================================================================
function GEEMWC__reset_all_settings ($also_reset_persistent_settings=false)
{
  global   $g_GEEMWC__config_defaults;

  update_option (GEEMWC_SETTINGS_NAME, $g_GEEMWC__config_defaults);

  // if ($also_reset_persistent_settings)
  //   GEEMWC__reset_all_persistent_settings ();
}
//===========================================================================

//===========================================================================
// Recursively strip slashes from all elements of multi-nested array
function GEEMWC__stripslashes (&$val)
{
   if (is_string($val))
      return (stripslashes($val));
   if (!is_array($val))
      return $val;

   foreach ($val as $k=>$v)
      {
      $val[$k] = GEEMWC__stripslashes ($v);
      }

   return $val;
}
//===========================================================================

//===========================================================================
/*
    ----------------------------------
    : Table 'geem_payments' :
    ----------------------------------
      status                "unused"      - never been used address with last known zero balance
                            "assigned"    - order was placed and this address was assigned for payment
                            "revalidate"  - assigned/expired, unused or unknown address suddenly got non-zero balance in it. Revalidate it for possible late order payment against meta_data.
                            "used"        - order was placed and this address and payment in full was received. Address will not be used again.
                            "xused"       - address was used (touched with funds) by unknown entity outside of this application. No metadata is present for this address, will not be able to correlated it with any order.
                            "unknown"     - new address was generated but cannot retrieve balance due to blockchain API failure.
*/
function GEEMWC__create_database_tables ($geemwc_settings)
{
  global $wpdb;

  $geemwc_settings = GEEMWC__get_settings();
  $must_update_settings = false;

  $geem_payments_table_name             = $wpdb->prefix . 'geemwc_geem_payments';

  if($wpdb->get_var("SHOW TABLES LIKE '$geem_payments_table_name'") != $geem_payments_table_name)
      $b_first_time = true;
  else
      $b_first_time = false;

 //----------------------------------------------------------
 // Create tables
  $query = "CREATE TABLE IF NOT EXISTS `$geem_payments_table_name` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `geem_address` char(98) NOT NULL,
    `geem_payment_id` char(64) NOT NULL,
    `origin_id` char(128) NOT NULL DEFAULT '',
    `index_in_wallet` bigint(20) NOT NULL DEFAULT '0',
    `status` char(16)  NOT NULL DEFAULT 'unknown',
    `last_assigned_to_ip` char(16) NOT NULL DEFAULT '0.0.0.0',
    `assigned_at` bigint(20) NOT NULL DEFAULT '0',
    `total_received_funds` DECIMAL( 16, 8 ) NOT NULL DEFAULT '0.00000000',
    `received_funds_checked_at` bigint(20) NOT NULL DEFAULT '0',
    `address_meta` MEDIUMBLOB NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `geem_payment_id` (`geem_payment_id`),
    KEY `index_in_wallet` (`index_in_wallet`),
    KEY `origin_id` (`origin_id`),
    KEY `status` (`status`)
    );";
  $wpdb->query ($query);
 //----------------------------------------------------------
}
//===========================================================================

//===========================================================================
// NOTE: Irreversibly deletes all plugin tables and data
function GEEMWC__delete_database_tables ()
{
  global $wpdb;

  $geem_payments_table_name    = $wpdb->prefix . 'geemwc_geem_payments';

  $wpdb->query("DROP TABLE IF EXISTS `$geem_payments_table_name`");
}
//===========================================================================

