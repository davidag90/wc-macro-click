<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>WC Macro Click de Pago - Procesando...</title>
</head>
<body>
   <?php
      $test_log = fopen('test_log.txt', 'a');

      $data = "Transaccion " . strval(time()) . PHP_EOL;
      foreach ($_GET as $key => $value) {
         $data .= $key . " = " . $value . PHP_EOL;
      }
      $data .= PHP_EOL . "----------" . PHP_EOL;

      fwrite($test_log, $data);
   ?>

   <form method="POST" action="<?= $_GET['PayURL'] ?>" id="form-firma">
      <input type="hidden" name="CallbackSuccess" id="CallbackSuccess" value="<?= $_GET['CallbackSuccess'] ?>" />
      <input type="hidden" name="CallbackCancel" id="CallbackCancel" value="<?= $_GET['CallbackCancel'] ?>" />
      <input type="hidden" name="Comercio" id="Comercio" value="<?= $_GET['Comercio'] ?>" />
      <input type="hidden" name="SucursalComercio" id="Sucursal" value="<?= $_GET['SucursalComercio'] ?>" />
      <input type="hidden" name="TransaccionComercioId" value="<?= $_GET['TransaccionComercioId'] ?>" id="TransaccionComercioId" />
      <input type="hidden" name="Monto" id="Monto" value="<?= $_GET['Monto'] ?>" />
      <input type="hidden" name="Producto[0]" id="producto1" value="<?= $_GET['Producto'] ?>" />
      <input type="hidden" name="MontoProducto[0]" id="montoproducto1" value="<?= $_GET['MontoProducto'] ?>" />
      <input type="hidden" name="Hash" id="hash" value="<?= $_GET['Hash'] ?>" />
   </form>

   <script>
   document.addEventListener('DOMContentLoaded', () => {
      let form = document.getElementById('form-firma');
      form.submit();
   });
   </script>
</body>

</html>









