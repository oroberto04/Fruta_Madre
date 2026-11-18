
<?php
$productosCarrito = $productosCarrito ?? [];
$datosPedido = $datosPedido ?? [];
$total = (float) ($total ?? 0);

function escapar($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

$cantidadProductos = 0;

foreach ($productosCarrito as $producto) {
    $cantidadProductos += (int) ($producto['cantidad'] ?? 0);
}

$tipoServicio = $datosPedido['tipo_servicio'] ?? '';

$metodosServicio = [
    'domicilio' => 'A domicilio',
    'recoger' => 'Recoger en el establecimiento'
];

$formaEntrega = $metodosServicio[$tipoServicio] ?? 'No especificada';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisar pedido | Fruta Madre</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #FFF4DF;
            color: #29231F;
        }

        header {
            background: white;
            padding: 22px 6%;
            border-bottom: 1px solid #eadfce;
        }

        header h1 {
            margin: 0;
            color: #477A45;
        }

        header p {
            margin-bottom: 0;
        }

        main {
            max-width: 1050px;
            margin: 35px auto;
            padding: 0 20px;
        }

        .panel {
            background: white;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 20px;
        }

        h2 {
            margin-top: 0;
        }

        .producto {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #eee3d5;
        }

        .muted {
            color: #756b61;
        }

        .dato {
            margin: 12px 0;
            line-height: 1.5;
            overflow-wrap: anywhere;
        }

        .total {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            font-size: 22px;
            font-weight: bold;
            margin-top: 20px;
        }

        .acciones {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 24px;
        }

        a {
            display: inline-block;
            padding: 13px 18px;
            border: 0;
            border-radius: 9px;
            font-size: 15px;
            text-decoration: none;
            cursor: pointer;
        }

        .volver {
            background: #eee5d8;
            color: #29231F;
        }

        .pagar {
            background: #477A45;
            color: white;
        }

        .pagar:hover {
            background: #386537;
        }

        @media (max-width: 600px) {
            main {
                margin: 22px auto;
            }

            .panel {
                padding: 18px;
            }

            .producto {
                font-size: 14px;
            }

            .total {
                font-size: 19px;
            }

            .acciones {
                flex-direction: column;
            }

            .acciones a {
                text-align: center;
            }
        }
    </style>
</head>

<body>

<header>
    <h1>FRUTA MADRE</h1>
    <p>Te damos hasta para llevar.</p>
</header>

<main>

    <h2>Revisa tu pedido</h2>

    <p class="muted">
        Verifica tus productos y los datos antes de continuar.
    </p>

    <!-- PRODUCTOS DEL PEDIDO -->
    <section class="panel">

        <h2>Tu pedido (<?= $cantidadProductos ?> productos)</h2>

        <?php if (empty($productosCarrito)): ?>

            <p class="muted">
                No hay productos en tu pedido.
            </p>

        <?php else: ?>

            <?php foreach ($productosCarrito as $producto): ?>

                <div class="producto">

                    <div>
                        <strong>
                            <?= escapar($producto['nombre'] ?? '') ?>
                        </strong>

                        <div class="muted">
                            Cantidad:
                            <?= (int) ($producto['cantidad'] ?? 0) ?>
                        </div>
                    </div>

                    <strong>
                        $<?= number_format((float) ($producto['subtotal'] ?? 0), 2) ?>
                    </strong>

                </div>

            <?php endforeach; ?>

            <div class="total">
                <span>Total</span>
                <span>$<?= number_format($total, 2) ?></span>
            </div>

        <?php endif; ?>

    </section>

    <!-- DATOS DEL CLIENTE Y ENTREGA -->
    <section class="panel">

        <h2>Datos del pedido</h2>

        <p class="dato">
            <strong>Nombre:</strong>
            <?= escapar($datosPedido['nombre'] ?? '') ?>
        </p>

        <p class="dato">
            <strong>Teléfono:</strong>
            <?= escapar($datosPedido['telefono'] ?? '') ?>
        </p>

        <p class="dato">
            <strong>Correo electrónico:</strong>
            <?= escapar($datosPedido['correo'] ?? '') ?>
        </p>

        <p class="dato">
            <strong>Forma de entrega:</strong>
            <?= escapar($formaEntrega) ?>
        </p>

        <?php if ($tipoServicio === 'domicilio'): ?>

            <p class="dato">
                <strong>Dirección:</strong>
                <?= escapar($datosPedido['direccion'] ?? '') ?>
            </p>

            <p class="dato">
                <strong>Referencias:</strong>
                <?= !empty($datosPedido['referencias'])
                    ? escapar($datosPedido['referencias'])
                    : 'Sin referencias adicionales' ?>
            </p>

        <?php endif; ?>

        <p class="dato">
            <strong>Fecha preferida:</strong>
            <?= escapar($datosPedido['fecha_preferida'] ?? '') ?>
        </p>

        <p class="dato">
            <strong>Horario preferido:</strong>
            <?= escapar($datosPedido['horario_preferido'] ?? '') ?>
        </p>

        <p class="dato">
            <strong>Observaciones:</strong>
            <?= !empty($datosPedido['observaciones'])
                ? escapar($datosPedido['observaciones'])
                : 'Sin observaciones' ?>
        </p>

    </section>

    <!-- ACCIONES -->
    <div class="acciones">

        <a class="volver" href="index.php?accion=carrito">
            Volver y modificar
        </a>

        <?php if (!empty($productosCarrito)): ?>

            <a class="pagar" href="index.php?accion=pagar">
                Continuar al pago
            </a>

        <?php endif; ?>

    </div>

    <p class="muted">
        Revisa que los datos de tu pedido sean correctos antes de continuar al pago.
    </p>

</main>

</body>
</html>
