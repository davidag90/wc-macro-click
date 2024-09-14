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
         flex-direction: column;
         gap: 16px;
         justify-content: center;
         margin: 0;
         padding: 16px;
         text-align: center;
      }

      .wrapper .loading span {
         color: #FFF;
         display: block;
         font-family: sans-serif;
         font-size: 24px;
         line-height: 1;
         margin: 0;
         padding: 0;
      }

      /* HTML: <div class="loader"></div> */
      .loader {
         animation: l5 1s infinite linear alternate;
         aspect-ratio: 1;
         border-radius: 50%;
         width: 15px;
      }

      @keyframes l5 {
         0% {
            box-shadow: 20px 0 #FFF, -20px 0 #FFF2;
            background: #FFF
         }

         33% {
            box-shadow: 20px 0 #FFF, -20px 0 #FFF2;
            background: #FFF2
         }

         66% {
            box-shadow: 20px 0 #FFF2, -20px 0 #FFF;
            background: #FFF2
         }

         100% {
            box-shadow: 20px 0 #FFF2, -20px 0 #FFF;
            background: #FFF
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
         <span>Enviando datos...</span>
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