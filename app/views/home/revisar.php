
<?php
$productosCarrito = $productosCarrito ?? [];
$datosPedido = $datosPedido ?? [];
$total = (float) ($total ?? 0);

function escapar($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

$cantidadProductos = 0;
foreach ($productosCarrito as $producto) {
    $cantidadProductos += (int) $producto['cantidad'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisar pedido | Fruta Madre</title>
    <style>
        * { box-sizing: border-box; }
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
        header h1 { margin: 0; color: #477A45; }
        main { max-width: 1050px; margin: 35px auto; padding: 0 20px; }
        .panel {
            background: white;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 20px;
        }
        h2 { margin-top: 0; }
        .producto {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #eee3d5;
        }
        .muted { color: #756b61; }
        .total {
            display: flex;
            justify-content: space-between;
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
        a, button {
            display: inline-block;
            padding: 13px 18px;
            border: 0;
            border-radius: 9px;
            font-size: 15px;
            text-decoration: none;
            cursor: pointer;
        }
        .volver { background: #eee5d8; color: #29231F; }
        .pagar { background: #477A45; color: white; }
        .pagar:disabled { opacity: .55; cursor: not-allowed; }
        @media (max-width: 600px) {
            .panel { padding: 18px; }
            .producto { font-size: 14px; }
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
    <p class="muted">Verifica tus productos y los datos antes de continuar.</p>

    <section class="panel">
        <h2>Tu pedido (<?= $cantidadProductos ?> productos)</h2>

        <?php foreach ($productosCarrito as $producto): ?>
            <div class="producto">
                <div>
                    <strong><?= escapar($producto['nombre']) ?></strong>
                    <div class="muted">
                        Cantidad: <?= (int) $producto['cantidad'] ?>
                    </div>
                </div>
                <strong>
                    $<?= number_format((float) $producto['subtotal'], 2) ?>
                </strong>
            </div>
        <?php endforeach; ?>

        <div class="total">
            <span>Total</span>
            <span>$<?= number_format($total, 2) ?></span>
        </div>
    </section>

    <section class="panel">
        <h2>Datos de entrega</h2>

        <p><strong>Nombre:</strong>
            <?= escapar($datosPedido['nombre'] ?? '') ?></p>

        <p><strong>Teléfono:</strong>
            <?= escapar($datosPedido['telefono'] ?? '') ?></p>

        <p><strong>Forma de entrega:</strong>
            <?= ($datosPedido['tipo_servicio'] ?? '') === 'domicilio'
                ? 'A domicilio'
                : 'Recoger en el establecimiento' ?></p>

        <?php if (($datosPedido['tipo_servicio'] ?? '') === 'domicilio'): ?>
            <p><strong>Dirección:</strong>
                <?= escapar($datosPedido['direccion'] ?? '') ?></p>
            <p><strong>Referencias:</strong>
                <?= escapar($datosPedido['referencias'] ?? '') ?></p>
        <?php endif; ?>

        <?php if (!empty($datosPedido['observaciones'])): ?>
            <p><strong>Observaciones:</strong>
                <?= escapar($datosPedido['observaciones']) ?></p>
        <?php endif; ?>
    </section>

    
<div class="acciones">
    <a class="volver" href="index.php?accion=carrito">
        Volver y modificar
    </a>

    <a class="pagar" href="index.php?accion=pagar">
        Continuar al pago
    </a>
</div>

<p class="muted">
    Revisa que los datos de tu pedido sean correctos antes de continuar al pago.
</p>

</main>
</body>
</html>
