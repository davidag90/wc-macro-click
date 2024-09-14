<?php
$payURL = $_GET['PayURL'];
$callbackSuccess = $_GET['CallbackSuccess'];
$callbackCancel = $_GET['CallbackCancel'];
$comercio = $_GET['Comercio'];
$sucursalComercio = $_GET['SucursalComercio'];
$transaccionComercioId = $_GET['TransaccionComercioId'];
$monto = $_GET['Monto'];
$informacion = $_GET['Informacion'];
$productos = json_decode(urldecode($_GET['Productos']), true);
$hash = $_GET['Hash'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>WC Macro Click de Pago - Procesando...</title>
   <style>
      html,
      body {
         margin: 0;
         padding: 0;
         background-color: #165b8c;
      }

      .wrapper {
         margin: 0;
         padding: 0;
         background-color: #165b8c;
         width: 100%;
         height: 100dvh;
         display: flex;
         flex-direction: column;
         align-items: center;
         justify-content: center;
      }

      .wrapper .loading {
         align-items: center;
         display: flex;
         flex-direction: row;
         gap: 32px;
         justify-content: center;
         margin: 0;
         padding: 16px;
         text-align: center;
      }

      /* HTML: <div class="loader"></div> */
      .loader {
         width: 50px;
         padding: 8px;
         aspect-ratio: 1;
         border-radius: 50%;
         background: #ffffff;
         --_m:
            conic-gradient(#0000 10%, #000),
            linear-gradient(#000 0 0) content-box;
         -webkit-mask: var(--_m);
         mask: var(--_m);
         -webkit-mask-composite: source-out;
         mask-composite: subtract;
         animation: l3 1s infinite linear;
      }

      @keyframes l3 {
         to {
            transform: rotate(1turn)
         }
      }

      form {
         display: none;
      }
   </style>
</head>

<body>
   <div class="wrapper">
      <div class="loading">
         <div class="loader"></div>
      </div>
   </div>

   <form method="POST" action="<?= $payURL ?>" id="form-firma">
      <input type="hidden" name="CallbackSuccess" id="CallbackSuccess" value="<?= $callbackSuccess ?>" />
      <input type="hidden" name="CallbackCancel" id="CallbackCancel" value="<?= $callbackCancel ?>" />
      <input type="hidden" name="Comercio" id="Comercio" value="<?= $comercio ?>" />
      <input type="hidden" name="SucursalComercio" id="Sucursal" value="<?= $sucursalComercio ?>" />
      <input type="hidden" name="TransaccionComercioId" value="<?= $transaccionComercioId ?>" id="TransaccionComercioId" />
      <input type="hidden" name="Monto" id="Monto" value="<?= $monto ?>" />
      <?php for ($i = 0; $i < count($productos); $i++) { ?>
         <input type="hidden" name="Producto[<?= $i ?>]" id="producto<?= $i ?>" value="<?= $productos[$i]['nombreProducto'] ?>" />
         <input type="hidden" name="MontoProducto[<?= $i ?>]" id="montoproducto<?= $i ?>" value="<?= $productos[$i]['montoProducto'] ?>" />
      <?php } ?>

      <input type="hidden" name="Informacion" id="informacion" value="<?= $informacion ?>" />
      <input type="hidden" name="Hash" id="hash" value="<?= $hash ?>" />
      <input type="hidden" name="Monto" id="monto" value="<?= $monto ?>" />
   </form>

   <script>
      document.addEventListener('DOMContentLoaded', () => {
         let form = document.getElementById('form-firma');
         form.submit();
      });
   </script>
</body>

</html>