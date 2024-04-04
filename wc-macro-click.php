<?php
/**
 * Plugin Name: WooCommerce Macro Click de Pago
 * Plugin URI: https://github.com/davidag90/wc-macro-click
 * Description: WooCommerce Plugin for payment processing under Macro Click de Pago gateway
 * Version: 0.1
 * Author: David Alejandro Gómez
 * Author URI: http://dagdev.com.ar/
 * Copyright: © 2024 David Alejandro Gómez.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package WC Macro Click
 * 
 */

if (!defined('ABSPATH')) {
  exit;
}

add_action('plugins_loaded', 'woocommerce_macro_click_init', 0);

function woocommerce_macro_click_init()
{
   /**
    * Checks if WC_Payment_Gateway exists before extending it in WC_Macro_Click
    */
   if (!class_exists('WC_Payment_Gateway')) return;
   
   /**
    * Call the main Class file
    **/
   require_once('src/WC_Macro_Click.php');

   /**
    * Add the Gateway to WooCommerce
    **/
   function woocommerce_add_macro_click($methods) {
      $methods[] = 'WC_Macro_Click';
      return $methods;
   }

   add_filter('woocommerce_payment_gateways', 'woocommerce_add_macro_click');
}
