<?php
require_once __DIR__ . '/vendor/autoload.php';
use PlusPagos\SHA256Encript;
use PlusPagos\AESEncrypter;

/**
 * Gateway class para procesar pagos con Macro Click
 * 
 * @property id $id
 * @property icon $icon
 * @property has_fields $has_fields
 * @property method_title $method_title
 * @property method_description $method_description
 * @property supports $supports
 * @property title $title
 * @property description $description
 * @property success_url $success_url
 * @property cancel_url $cancel_url
 * @property testmode $testmode
 * @property secret_key $secret_key
 * @property id_comercio $id_comercio
 * @property form_fields $form_fields
 */

class WC_Macro_Click extends WC_Payment_Gateway {
   public function __construct() {
      $this->id = 'macro_click';
      $this->icon = plugin_dir_url(__FILE__) . '/assets/logo.png';
      $this->has_fields = false;
      $this->method_title = 'Macro Click de Pago';
      $this->method_description = 'Permite a los clientes ingresar pagos por Macro Click';

      $this->supports = array( 'products' );

      $this->init_form_fields();

      $this->init_settings();
      $this->title = 'Macro Click de Pago';
      $this->description = $this->get_option('description');
      $this->success_url = $this->get_option('success_url');
      $this->cancel_url = $this->get_option('cancel_url');
      $this->testmode = 'yes' === $this->get_option('testmode');
      $this->secret_key = $this->get_option('secret_key');
      $this->id_comercio = $this->get_option('id_comercio');

      if (empty($this->success_url) || $this->success_url == null) {
         $this->success_url = get_site_url();
      }

      if (empty($this->cancel_url) || $this->cancel_url == null) {
         $this->cancel_url = get_site_url();
      }

      add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
   }

   public function init_form_fields() {
      $this->form_fields = array(
         'enabled' => array(
            'title'       => 'Activar/Desactivar',
            'label'       => 'Activar o Desactivar Macro Click de Pago',
            'type'        => 'checkbox',
            'description' => '',
            'default'     => 'no'
         ),
         'description' => array(
            'title'       => 'Descripción',
            'type'        => 'textarea',
            'description' => 'Detalles para el usuario sobre el método de pago',
            'default'     => 'Pagá utilizando tus tarjetas de crédito y débito',
         ),
         'testmode' => array(
            'title'       => 'Modo test',
            'label'       => 'Activar Modo Test',
            'type'        => 'checkbox',
            'description' => 'Habilita el modo de pruebas para desarrollo',
            'default'     => 'yes',
         ),
         'success_url' => array(
            'title'       => 'URL exitosa',
            'description' => 'URL a donde redireccionar los pagos realizados correctamente. Si no se completa, el usuario volverá a la página principal.',
            'type'        => 'text',
            'default'     => ''
         ),
         'cancel_url' => array(
            'title'       => 'URL de cancelación',
            'description' => 'URL a donde redireccionar los pagos realizados abortados. Si no se completa, el usuario volverá a la página principal.',
            'type'        => 'text',
            'default'     => ''
         ),
         'id_comercio' => array(
            'title'       => 'Id de Comercio',
            'type'        => 'text',
            'default'     => ''
         ),
         'secret_key' => array(
            'title'       => 'Secret-Key',
            'type'        => 'password',
            'default'     => ''
         ),
         'hash' => array(
            'title'        => 'Hash',
            'type'         => 'text',
            'default'      => ''
         )
      );
   }

   public function process_payment($order_id) {
      $aes = new AESEncrypter();

      global $woocommerce;

      $pedido = wc_get_order($order_id);
      $callback_success = $aes->EncryptString($this->success_url, $this->secret_key);
      $callback_cancel = $aes->EncryptString($this->cancel_url, $this->secret_key);
      $monto = $aes->EncryptString($pedido->get_total(), $this->secret_key);
      $sucursal = $aes->EncryptString('', $this->secret_key);

      $url = 'https://botonpp.macroclickpago.com.ar';

      if ($this->testmode) {
         $url = 'https://sandboxpp.macroclickpago.com.ar';
      }

      $post_args = array(
         'method'    => 'POST',

      );

      wp_remote_post($url, $args);
   }
}
