# Geem for WooCommerce

Geem for WooCommerce is a Wordpress plugin that allows merchants to accept GEEM at WooCommerce-powered online stores.

Contributors: KittyCatTech, gesman, The Geem Maintainer.

Tags: geem, geem wordpress plugin, geem plugin, geem payments, accept geem

Requires at least: 3.0.1

Tested up to: 5.2.1

Stable tag: Kainaat

License: BipCot NoGov Software License bipcot.org

License URI: https://github.com/geemcoin/geem-woocommerce/blob/master/LICENSE

## Store Frontend | Customer Journey

![alt text](https://raw.githubusercontent.com/geemcoin/geem-woocommerce/master/README_Images/Geem_Store1.png)

![alt text](https://raw.githubusercontent.com/geemcoin/geem-woocommerce/master/README_Images/Geem_Store2.png)

![alt text](https://raw.githubusercontent.com/geemcoin/geem-woocommerce/master/README_Images/Geem_Store3.png)

![alt text](https://raw.githubusercontent.com/geemcoin/geem-woocommerce/master/README_Images/Geem_Store4.png)

![alt text](https://raw.githubusercontent.com/geemcoin/geem-woocommerce/master/README_Images/Geem_Store5.png)

## Description

Your online store must use WooCommerce platform (free wordpress plugin).
Once you have installed and activated WooCommerce, you may install and activate Geem for WooCommerce.

### Benefits 

* Fully automatic operation.
* Can be used with view only wallet so only the view private key is on the server and none of the spend private keys are required to be kept anywhere on your online store server.
* Accept payments in Geem directly into your Geem wallet.
* Geem wallet payment option completely removes dependency on any third party service and middlemen.
* Accept payment in Geem for physical and digital downloadable products.
* Add Geem option to your existing online store with alternative main currency.
* Flexible exchange rate calculations fully managed via administrative settings.
* Zero fees and no commissions for Geem processing from any third party.
* Set main currency of your store in GEEM or USD.
* Automatic conversion to Geem via realtime rate conversion calculations.
* Ability to set exchange rate calculation multiplier to compensate for any possible losses due to rate conversions and funds transfer fees.


## Installation 


1.  Install WooCommerce plugin and configure your store (if you haven't done so already - http://wordpress.org/plugins/woocommerce/).
2.  Install "Geem for WooCommerce" wordpress plugin just like any other Wordpress plugin.
3.  Activate.
4.  Download and install the Geem CLI suite from: https://geem.io/downloads/
5.  It is highly recommended to run walletd on the same server as your website for security reasons. By default, the plugin code is configured to use localhost or 127.0.0.1 as walletd host. You MUST use your own server to run walletd. Example: your.server.com:8070 or 127.0.0.1:8070 if walletd is running on the same server as your webserver. If walletd is not running on the same server as your webserver, you will need to modify the following files and either provide the hostname or the IP address running walletd. By default, the port is set at 8070 which can be modified depending on the port used when wallettd is run. Below is the list of files that will need to be updated accordingly.
./geemwc-utils.php:  $wallet_api = New ForkNoteWalletd("http://127.0.0.1:8070");
./geemwc-utils.php:  $fnw = New ForkNoteWalletd("http://127.0.0.1:8070");
./geemwc-utils.php:            $wallet_api = New ForkNoteWalletd("http://127.0.0.1:8070");
./geemwc-render-settings.php:      $wallet_api = New ForkNoteWalletd("http://127.0.0.1:8070");
./geemwc-geem-gateway.php:	    		$wallet_api = New ForkNoteWalletd("http://127.0.0.1:8070");
./geemwc-geem-gateway.php:               $wallet_api = New ForkNoteWalletd("http://127.0.0.1:8070");
./geemwc-admin.php:      $wallet_api = New ForkNoteWalletd("http://127.0.0.1:8070");
6.  Generate Container (optionally reset containter to view only container and add view only address). Run walletd as a service.
7.  Get your wallet address from walletd.
8.  Within your site's Wordpress admin, navigate to:
	    WooCommerce -> Settings -> Checkout -> Geem
	    and paste your wallet address into "Wallet Address" field.
9.  Select "Geem service provider" = "Local Wallet" and fill-in other settings at Geem management panel.
10. Press [Save changes]
11. If you do not see any errors, your store is ready for operation and to access payments in Geem!


## Remove plugin

1. Deactivate plugin through the 'Plugins' menu in WordPress
2. Delete plugin through the 'Plugins' menu in WordPress


## Changelog

none
