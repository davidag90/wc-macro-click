<?php
/*
Plugin Name: WooCommerce Macro Click de Pago
Plugin URI: http://woothemes.com/woocommerce
Description: Extensión WooCommerce para el procesamiento de pagos con Macro Click
Version: 0.1
Author: David Alejandro Gómez
Author URI: http://dagdev.com.ar/
	Copyright: © 2024 David Alejandro Gómez.
	License: GNU General Public License v3.0
	License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

add_action('plugins_loaded', 'woocommerce_gateway_name_init', 0);
function woocommerce_gateway_name_init() {
	if ( !class_exists( 'WC_Payment_Gateway' ) ) return;
	/**
 	 * Localisation
	 */
	load_plugin_textdomain('wc-gateway-name', false, dirname( plugin_basename( __FILE__ ) ) . '/languages');
    
	/**
 	 * Gateway class
 	 */
	class WC_Gateway_Name extends WC_Payment_Gateway {
	
		// Go wild in here
	}
	
	/**
 	* Add the Gateway to WooCommerce
 	**/
	function woocommerce_add_gateway_name_gateway($methods) {
		$methods[] = 'WC_Gateway_Name';
		return $methods;
	}
	
	add_filter('woocommerce_payment_gateways', 'woocommerce_add_gateway_name_gateway' );
}