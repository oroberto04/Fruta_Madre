
<?php
$datosPedido = $_SESSION['datos_pedido'] ?? [];
$metodoSeleccionado = $_SESSION['metodo_pago'] ?? 'efectivo';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pago | Fruta Madre</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 24px;
            background: #FFF4DF;
            color: #29231F;
            font-family: Arial, sans-serif;
        }

        main {
            max-width: 760px;
            margin: 35px auto;
            padding: 30px;
            background: white;
            border-radius: 18px;
            box-shadow: 0 4px 16px #0000000D;
        }

        h1, h2 { color: #29231F; }
        h1 { font-size: 28px; }
        h2 { font-size: 19px; margin-top: 28px; }

        .opcion {
            display: block;
            padding: 17px;
            margin: 12px 0;
            border: 1px solid #D5DDD0;
            border-radius: 12px;
            cursor: pointer;
        }

        .opcion:has(input:checked) {
            border: 2px solid #477A45;
            background: #F5FAF1;
        }

        .opcion strong { display: block; margin-bottom: 6px; }
        .opcion small { color: #625E57; line-height: 1.5; }

        .acciones {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 28px;
        }

        .acciones a, .acciones button {
            padding: 14px 18px;
            border: 0;
            border-radius: 9px;
            font-size: 15px;
            text-decoration: none;
            text-align: center;
            cursor: pointer;
        }

        .volver { background: #F3E9D8; color: #29231F; }
        .confirmar { background: #477A45; color: white; flex: 1; }
        .aviso { color: #625E57; font-size: 13px; line-height: 1.6; }
    </style>
</head>

<body>
<main>
    <h1>Finaliza tu pedido</h1>
    <p>Selecciona cómo deseas pagar tu compra.</p>

    <h2>Forma de entrega</h2>
    <p>
        <?= ($datosPedido['tipo_servicio'] ?? '') === 'domicilio'
            ? 'Entrega a domicilio'
            : 'Recoger en tienda' ?>
    </p>

    <h2>Método de pago</h2>

    <form action="index.php?accion=confirmar-pedido" method="POST">
        <?php
        if (empty($_SESSION['token_confirmacion'])) {
            $_SESSION['token_confirmacion'] = bin2hex(random_bytes(32));
        }
        ?>

        <input type="hidden" name="token"
               value="<?= htmlspecialchars($_SESSION['token_confirmacion'], ENT_QUOTES, 'UTF-8') ?>">

        <label class="opcion">
            <input type="radio" name="metodo_pago" value="efectivo"
                <?= $metodoSeleccionado === 'efectivo' ? 'checked' : '' ?> required>
            <strong>Efectivo</strong>
            <small>
                Pago en efectivo al recibir tu pedido o al recogerlo en tienda.
            </small>
        </label>

        <label class="opcion">
            <input type="radio" name="metodo_pago" value="transferencia"
                <?= $metodoSeleccionado === 'transferencia' ? 'checked' : '' ?>>
            <strong>Transferencia bancaria</strong>
            <small>
                El pedido quedará pendiente hasta verificar el pago.
            </small>
        </label>

        <label class="opcion">
            <input type="radio" name="metodo_pago" value="tarjeta"
                <?= $metodoSeleccionado === 'tarjeta' ? 'checked' : '' ?>>
            <strong>Tarjeta</strong>
            <small>
                El cobro con tarjeta requiere una pasarela de pago segura.
            </small>
        </label>

        <p class="aviso">
            Al confirmar se registrará tu pedido. Esta pantalla no realiza
            cobros electrónicos ni confirma transferencias automáticamente.
        </p>

        <div class="acciones">
            <a class="volver" href="index.php?accion=revisar">
                Volver al resumen
            </a>

            <button class="confirmar" type="submit">
                Confirmar pedido
            </button>
        </div>
    </form>
</main>
</body>
</html>
