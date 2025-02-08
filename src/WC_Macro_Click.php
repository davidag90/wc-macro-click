<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PlusPagos\SHA256Encript;
use PlusPagos\AESEncrypter;

/**
 * Gateway class for payment processing using Macro Click de Pago system
 * 
 * @property id $id
 * @property icon $icon
 * @property has_fields $has_fields
 * @property method_title $method_title
 * @property method_description $method_description
 * @property supports $supports
 * @property title $title
 * @property description $description
 * @property cancel_url $cancel_url
 * @property testmode $testmode
 * @property secret_key $secret_key
 * @property id_comercio $id_comercio
 * @property sucursal_comercio $sucursal_comercio
 * @property form_fields $form_fields
 */

class WC_Macro_Click extends WC_Payment_Gateway
{
   public $id;
   public $icon;
   public $has_fields;
   public $method_title;
   public $method_description;
   public $supports;
   public $title;
   public $description;
   public $cancel_url;
   public $testmode;
   public $secret_key;
   public $id_comercio;
   public $sucursal_comercio;

   public function __construct()
   {
      $this->id = 'macro_click';
      $this->icon = plugin_dir_url(__FILE__) . '../assets/logo.png';
      $this->has_fields = false;
      $this->method_title = 'Macro Click de Pago';
      $this->method_description = 'Tus clientes finalizan sus pagos en Macro Click de Pago';

      $this->supports = array('products');

      $this->init_form_fields();

      $this->init_settings();
      $this->title = 'Macro Click de Pago';
      $this->description = $this->get_option('description');
      $this->testmode = 'yes' === $this->get_option('testmode');

      // Checks if testmode is "on" to define "secret_key" and "id_comercio"
      if ($this->testmode) {
         $this->secret_key = $this->get_option('secret_key_test');
         $this->id_comercio = $this->get_option('id_comercio_test');
      } else {
         $this->secret_key = $this->get_option('secret_key');
         $this->id_comercio = $this->get_option('id_comercio');
      }

      $this->sucursal_comercio = ''; // Default value defined by API docs

      // Options update hook
      add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

      // API hook to process the result of payment operation on the Gateway side
      add_action('woocommerce_api_' . $this->id, array($this, 'process_macro_click'));
   }

   public function init_form_fields()
   {
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
         'id_comercio_test' => array(
            'title'       => 'Id de Comercio Test',
            'type'        => 'text',
            'default'     => ''
         ),
         'secret_key_test' => array(
            'title'       => 'Secret-Key Test',
            'type'        => 'password',
            'default'     => ''
         ),
      );
   }

   public function process_payment($order_id)
   {
      global $woocommerce;

      $order = wc_get_order($order_id);

      $order->update_status('on-hold', 'Aguardando confirmacion por parte de Macro sobre el resultado del intento de pago');

      $aes = new AESEncrypter();
      $sha256 = new SHA256Encript();

      function arreIp()
      {
         if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
         if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return $_SERVER['HTTP_X_FORWARDED_FOR'];
         return $_SERVER['REMOTE_ADDR'];
      }

      $callback_success = $aes->EncryptString($this->get_return_url($order), $this->secret_key);
      $callback_cancel = $aes->EncryptString(wc_get_checkout_url() . '?order_canceled=true', $this->secret_key);

      $comercio = $this->id_comercio;
      $sucursal_comercio = $aes->EncryptString($this->sucursal_comercio, $this->secret_key);

      function generateRandomStr($length = 8)
      {
         $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
         $charactersLength = strlen($characters);
         $randomString = '';
         for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
         }

         return $randomString;
      }

      $transaccion_comercio_id = $order_id . '-' . generateRandomStr(8);

      $monto = $order->get_total();
      $montoEnc = $aes->EncryptString($monto, $this->secret_key);
      $monto_producto = $monto;
      $hash = $sha256->Generate(arreIp(), $this->secret_key, $comercio, '', $monto);

      if ($this->testmode) {
         $url = 'https://sandboxpp.macroclickpago.com.ar';
      } else {
         $url = 'https://botonpp.macroclickpago.com.ar/';
      }

      $items = $order->get_items();

      $productos = [];

      foreach ($items as $item_id => $item) {
         $nombre_producto = $item->get_name();

         // Ajusta formato del dato numérico brindado por WooCommerce a las necesidades de Macro
         $monto_producto = str_replace([',', '.'], '', number_format($order->get_item_total($item), 2, '.', ''));

         $productos[] = array(
            'nombreProducto'  => $nombre_producto,
            'montoProducto'   => $monto_producto
         );
      }

      $productos_enc = urlencode(json_encode($productos));

      $alumno_info = [
         'Nombre y Apellido' => $order->get_formatted_billing_full_name(),
         'Telefono' => $order->get_billing_phone(),
         'CUIT-CUIL' => $order->get_meta('_billing_cuit_cuil'),
         'URLBaseTienda' => home_url('/', 'https')
      ];

      $informacion_json = json_encode($alumno_info, JSON_UNESCAPED_UNICODE);

      $informacion = $aes->EncryptString($informacion_json, $this->secret_key);

      $params = array(
         'CallbackSuccess'       => $callback_success,
         'CallbackCancel'        => $callback_cancel,
         'Comercio'              => $comercio,
         'SucursalComercio'      => $sucursal_comercio,
         'TransaccionComercioId' => $transaccion_comercio_id,
         'Monto'                 => $montoEnc,
         'Productos'             => $productos_enc,
         'Informacion'           => $informacion,
         'Hash'                  => $hash,
         'PayURL'                => $url,
      );

      $query_params = http_build_query($params);

      return [
         'result'    => 'success',
         'redirect'  =>  plugin_dir_url(__FILE__) . 'Send_Form.php?' . $query_params
      ];
   }

   public function process_macro_click()
   {
      if ($_SERVER['REQUEST_METHOD'] === 'POST') {
         $jsonBody = file_get_contents('php://input');
         $data = json_decode($jsonBody, true);
         $writeData = serialize($data);
         file_put_contents(__DIR__ . '/debug.log', PHP_EOL . $writeData . PHP_EOL, FILE_APPEND);

         $order_id = strstr($data['TransaccionComercioId'], '-', true);

         $status = $data['EstadoId'];
         $order = wc_get_order($order_id);

         $transac_id_exists = $order->meta_exists('macro_click_transac_id');

         if ($status === '3') {
            if ($transac_id_exists) {
               $order->update_meta_data('macro_click_transac_id', $data['TransaccionPlataformaId']);
            } else {
               $order->add_meta_data('macro_click_transac_id', $data['TransaccionPlataformaId']);
            }

            $order->payment_complete();
            $order->update_status('completed');
         }

         if ($status === '4' || $status === '7' || $status === '8' || $status === '11') {
            if ($transac_id_exists) {
               $order->update_meta_data('macro_click_transac_id', $data['TransaccionPlataformaId']);
            } else {
               $order->add_meta_data('macro_click_transac_id', $data['TransaccionPlataformaId']);
            }

            $order->update_status('pending', 'Pedido en suspenso por pago fallido');
            wc_add_notice('Procedimiento de pago cancelado. Por favor, intenta nuevamente con otro medio', 'notice');
         }
      }
   }
}
