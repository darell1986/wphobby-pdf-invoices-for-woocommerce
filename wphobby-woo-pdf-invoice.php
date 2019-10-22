<?php
/*
Plugin Name: WPHobby PDF Invoices for WooCommerce
Plugin URI: http://wphobby.com
Description: Generate Woocommerce PDF Invoice.
Version: 1.0.2
Author: wphobby
Author URI: https://wphobby.com/downloads/woocommerce-pdf-invoice-maker/
*/

if ( ! defined( 'ABSPATH' ) ) {
   exit;
} // Exit if accessed directly

// Load plugin text domian
load_plugin_textdomain( 'wphobby-woo-pdf-invoice', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

$wp_upload_dir = wp_upload_dir();
// Set constants
define('WHPDF_DIR', plugin_dir_path(__FILE__));
define('WHPDF_URL', plugin_dir_url(__FILE__));
define('WHPDF_INVOICE_TEMPLATE_DIR', WHPDF_DIR . 'templates/invoice/' );
define('WHPDF_DOCUMENT_SAVE_DIR', $wp_upload_dir['basedir'] . '/whpdf-invoice/' );
define('WHPDF_OPTIONS', 'whpdf_general_data');
define('WHPDF_VERSION', '1.0.2');


if( ! function_exists( 'whpdf_install_woocommerce_admin_notice' ) ) {
   /**
    * Display an admin notice if woocommerce is deactivated
    *
    * @since 1.0.0
    * @return void
    * @use admin_notices hooks
    */
   function whpdf_install_woocommerce_admin_notice() { ?>
      <div class="error">
         <p><?php esc_html_e( 'WooCommerce PDF Invoice Maker is enabled but not effective. It requires WooCommerce in order to work.', 'wphobby-woo-pdf-invoice' ); ?></p>
      </div>
      <?php
   }
}

if( ! function_exists( 'whpdf_install_premium_admin_notice' ) ) {
   /**
    * Display an admin notice for premium version link
    *
    * @since 1.0.1
    * @return void
    * @use admin_notices hooks
    */
   function whpdf_install_premium_admin_notice() {

      global $current_user;

      $user_id = $current_user->ID;

      if (!get_user_meta($user_id, 'whpdf_admin_notice_ignore')) {

         echo sprintf(
             '<div class="notice alert success"><p>%s</p></div>',
             sprintf(
                 __("Like WPHobby WooCommerce PDF Invoice free version? You can also buy our premium version <a href='https://wphobby.com/wp/woo-pdf-invoice-notice'>Here</a> <div class='alert_close'><a href='?whpdf-ignore-notice'>Dismiss</a></div>", "wphobby-woo-pdf-invoice")
             )
         );

      }

   }
}


if( ! function_exists( 'whpdf_install' ) ){
   function whpdf_install() {

      if ( ! function_exists( 'WC' ) ) {
         add_action( 'admin_notices', 'whpdf_install_woocommerce_admin_notice' );
      }else{
         add_action( 'admin_notices', 'whpdf_install_premium_admin_notice' );

         // Include files
         require_once('includes/whpdf_init.php');
         require_once('includes/whpdf_wc_compatibility.php');
         require_once('includes/whpdf_document.php' );
         require_once('includes/whpdf_invoice.php' );
         require_once('includes/whpdf_shipping.php' );
         require_once('includes/whpdf_admin.php');

         // Initalize this plugin
         $WHPDF = new WHPDF();
         // When admin active this plugin
         register_activation_hook(__FILE__, array(&$WHPDF, 'activate'));
         // When admin deactive this plugin
         register_deactivation_hook(__FILE__, array(&$WHPDF, 'deactivate'));

         // Run the plugins initialization method
         add_action('init', array(&$WHPDF, 'initialize'));

      }
   }
}

add_action( 'plugins_loaded', 'whpdf_install', 11 );
?>
