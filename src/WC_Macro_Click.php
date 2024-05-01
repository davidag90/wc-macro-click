<?php
require_once __DIR__ . '/../vendor/autoload.php';
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
 * @property sucursal_comercio $sucursal_comercio
 * @property form_fields $form_fields
 */

class WC_Macro_Click extends WC_Payment_Gateway {
   public $id;
   public $icon;
   public $has_fields;
   public $method_title;
   public $method_description;
   public $supports;
   public $title;
   public $description;
   public $success_url;
   public $cancel_url;
   public $testmode;
   public $secret_key;
   public $id_comercio;
   public $sucursal_comercio;

   public function __construct() {
      $this->id = 'macro_click';
      $this->icon = plugin_dir_url(__FILE__) . '../assets/logo.png';
      $this->has_fields = false;
      $this->method_title = 'Macro Click de Pago';
      $this->method_description = 'Tus clientes finalizan sus pagos en Macro Click de Pago';

      $this->supports = array( 'products' );

      $this->init_form_fields();

      $this->init_settings();
      $this->title = 'Macro Click de Pago';
      $this->description = $this->get_option('description');
      /* $this->success_url = get_site_url() . $this->get_option('success_url');
      $this->cancel_url = get_site_url() . $this->get_option('cancel_url'); */
      $this->testmode = 'yes' === $this->get_option('testmode');
      $this->secret_key = $this->get_option('secret_key');
      $this->id_comercio = $this->get_option('id_comercio');
      $this->sucursal_comercio = ''; // Por defecto en blanco

      if (empty($this->success_url) || $this->success_url == null) {
         $this->success_url = get_site_url();
      }

      if (empty($this->cancel_url) || $this->cancel_url == null) {
         $this->cancel_url = get_site_url();
      }

      add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
      add_action('woocommerce_api_' . $this->id, array($this, 'process_macro_click'));
   }

   public function init_form_fields() {
      $this->form_fields = array(
         'enabled' => array(
            'title'       => 'Activar/Desactivar',
            'label'       => 'Activar Macro Click de Pago',
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
/*          'success_url' => array(
            'title'       => 'URL exitosa',
            'description' => 'URL a donde redireccionar los pagos finalizados correctamente. Si no se completa, el usuario volverá a la página principal.',
            'type'        => 'text',
            'default'     => ''
         ),
         'cancel_url' => array(
            'title'       => 'URL de cancelación',
            'description' => 'URL a donde redireccionar los pagos abortados y/o fallidos. Si no se completa, el usuario volverá a la página principal.',
            'type'        => 'text',
            'default'     => ''
         ), */
         'id_comercio' => array(
            'title'       => 'Id de Comercio',
            'type'        => 'text',
            'default'     => ''
         ),
         'secret_key' => array(
            'title'       => 'Secret-Key',
            'type'        => 'password',
            'default'     => ''
         )
      );
   }

   public function process_payment( $order_id ) {
      global $woocommerce;
      $order = wc_get_order($order_id);   
      
      $order->update_status('on-hold', 'Aguardando confirmacion por parte de Macro');
      
      $aes = new AESEncrypter();
      $sha256 = new SHA256Encript();

      function arreIp() {
         if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
         if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return $_SERVER['HTTP_X_FORWARDED_FOR'];
         return $_SERVER['REMOTE_ADDR'];
      }

      $callback_success = $aes->EncryptString($this->get_return_url($order), $this->secret_key);
      $callback_cancel = $aes->EncryptString($this->get_return_url($order), $this->secret_key);
      $comercio = $this->id_comercio;
      $sucursal_comercio = $aes->EncryptString($this->sucursal_comercio, $this->secret_key);
      $transaccion_comercio_id = $order_id;
      $monto = $order->get_total();
      $montoEnc = $aes->EncryptString($monto, $this->secret_key);
      $monto_producto = $monto;
      $hash = $sha256->Generate(arreIp(), $this->secret_key, $comercio, '', $monto);
      
      if ($this->testmode) {
         $url = 'https://sandboxpp.asjservicios.com.ar';
      } else {
         $url = 'https://botonpp.macroclickpago.com.ar/';
      }

      $items = $order->get_items();

      $productos = [];

      foreach ( $items as $item_id => $item ) {
         $nombre_producto = $item->get_name();
         $monto_producto = str_replace([',', '.'], '', number_format($order->get_item_total($item), 2, '.', ''));

         $productos[] = array(
            'nombreProducto'  => $nombre_producto,
            'montoProducto'   => $monto_producto
         );
      }

      $productos_enc = urlencode(json_encode($productos));
      
      $params = array(
         'CallbackSuccess'       => $callback_success,
         'CallbackCancel'        => $callback_cancel,
         'Comercio'              => $comercio,
         'SucursalComercio'      => $sucursal_comercio,
         'TransaccionComercioId' => $transaccion_comercio_id,
         'Monto'                 => $montoEnc,
         'Productos'             => $productos_enc,
         'Hash'                  => $hash,
         'PayURL'                => $url
      );

      $query_params = http_build_query($params);

      return [
         'result'    => 'success',
         'redirect'  =>  plugin_dir_url(__FILE__) . 'Send_Form.php?' . $query_params
      ];
   }

   public function process_macro_click() {
      $order_id = $_REQUEST['TransaccionComercioId'];
      $order = wc_get_order($order_id);

      if($_REQUEST['EstadoId'] == 2) {
         $order->payment_complete();
      } else {
         return;
      }
   }
}