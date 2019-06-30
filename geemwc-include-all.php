<?php
/*
Geem for WooCommerce
https://github.com/geemcoin/geem-woocommerce/
*/

//---------------------------------------------------------------------------
// Global definitions
if (!defined('GEEMWC_PLUGIN_NAME'))
  {
  define('GEEMWC_VERSION',           '2.4.2');

  //-----------------------------------------------
  define('GEEMWC_EDITION',           'Standard');    

  //-----------------------------------------------
  define('GEEMWC_SETTINGS_NAME',     'GEEMWC-Settings');
  define('GEEMWC_PLUGIN_NAME',       'Geem for WooCommerce');   


  // i18n plugin domain for language files
  define('GEEMWC_I18N_DOMAIN',       'geemwc');

  }
//---------------------------------------------------------------------------

//------------------------------------------
// Load wordpress for POSTback, WebHook and API pages that are called by external services directly.
if (defined('GEEMWC_MUST_LOAD_WP') && !defined('WP_USE_THEMES') && !defined('ABSPATH'))
   {
   $g_blog_dir = preg_replace ('|(/+[^/]+){4}$|', '', str_replace ('\\', '/', __FILE__)); // For love of the art of regex-ing
   define('WP_USE_THEMES', false);
   require_once ($g_blog_dir . '/wp-blog-header.php');

   // Force-elimination of header 404 for non-wordpress pages.
   header ("HTTP/1.1 200 OK");
   header ("Status: 200 OK");

   require_once ($g_blog_dir . '/wp-admin/includes/admin.php');
   }
//------------------------------------------


// This loads necessary modules
require_once (dirname(__FILE__) . '/libs/forknoteWalletdAPI.php');

require_once (dirname(__FILE__) . '/geemwc-cron.php');
require_once (dirname(__FILE__) . '/geemwc-utils.php');
require_once (dirname(__FILE__) . '/geemwc-admin.php');
require_once (dirname(__FILE__) . '/geemwc-render-settings.php');
require_once (dirname(__FILE__) . '/geemwc-geem-gateway.php');

?>
