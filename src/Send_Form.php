<?php
   $payURL = $_GET['PayURL'];
   $callbackSuccess = $_GET['CallbackSuccess'];
   $callbackCancel = $_GET['CallbackCancel'];
   $comercio = $_GET['Comercio'];
   $sucursalComercio = $_GET['SucursalComercio'];
   $transaccionComercioId = $_GET['TransaccionComercioId'];
   $monto = $_GET['Monto'];
   $productos = json_decode(urldecode($_GET['Productos']), true);
   $hash = $_GET['Hash'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>WC Macro Click de Pago - Procesando...</title>
</head>
<body>
   <form method="POST" action="<?= $payURL ?>" id="form-firma">
      <input type="hidden" name="CallbackSuccess" id="CallbackSuccess" value="<?= $callbackSuccess ?>" />
      <input type="hidden" name="CallbackCancel" id="CallbackCancel" value="<?= $callbackCancel ?>" />
      <input type="hidden" name="Comercio" id="Comercio" value="<?= $comercio ?>" />
      <input type="hidden" name="SucursalComercio" id="Sucursal" value="<?= $sucursalComercio ?>" />
      <input type="hidden" name="TransaccionComercioId" value="<?= $transaccionComercioId ?>" id="TransaccionComercioId" />
      <input type="hidden" name="Monto" id="Monto" value="<?= $monto ?>" />
      <?php for($i = 0; $i < count($productos); $i++) { ?>
         <input type="hidden" name="Producto[<?= $i ?>]" id="producto<?= $i ?>" value="<?= $productos[$i]['nombreProducto'] ?>" />
         <input type="hidden" name="MontoProducto[<?= $i ?>]" id="montoproducto<?= $i ?>" value="<?= $productos[$i]['montoProducto'] ?>" />
      <?php } ?>
      <input type="hidden" name="Hash" id="hash" value="<?= $hash ?>" />
      <button type="submit">Enviar</button>
   </form>

   <script>
   /* document.addEventListener('DOMContentLoaded', () => {
      let form = document.getElementById('form-firma');
      form.submit();
   }); */
   </script>
</body>

</html>









