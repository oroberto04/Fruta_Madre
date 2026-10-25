<?php
$pedido = $pedido ?? ($_SESSION['pedido_creado'] ?? null);

if (!$pedido || empty($pedido['id_pedido'])) {
    header('Location: index.php');
    exit;
}

$escapar = static function ($valor): string {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
};

$idPedido = (int) $pedido['id_pedido'];
$metodoPago = $pedido['metodo_pago'] ?? '';

$metodos = [
    'efectivo' => 'Efectivo',
    'transferencia' => 'Transferencia bancaria',
    'tarjeta' => 'Tarjeta'
];

$nombreMetodo = $metodos[$metodoPago] ?? 'No especificado';

switch ($metodoPago) {
    case 'efectivo':
        $estadoTitulo = 'Pedido registrado';
        $estadoDescripcion =
            'Tu pedido quedó registrado. El pago en efectivo se realizará al recibirlo o recogerlo.';
        $estadoIcono = '✓';
        break;

    case 'transferencia':
        $estadoTitulo = 'Pedido registrado, pago pendiente';
        $estadoDescripcion =
            'Tu pedido quedó registrado, pero la transferencia todavía debe verificarse.';
        $estadoIcono = '⌛';
        break;

    case 'tarjeta':
        $estadoTitulo = 'Pedido registrado, pago pendiente';
        $estadoDescripcion =
            'Tu pedido quedó registrado, pero esta aplicación todavía no procesa cobros con tarjeta.';
        $estadoIcono = '💳';
        break;

    default:
        $estadoTitulo = 'Pedido registrado';
        $estadoDescripcion =
            'Tu pedido quedó registrado. Consulta con el establecimiento el estado de tu pago.';
        $estadoIcono = '✓';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="theme-color" content="#FFF4DF">
    <title>Pedido registrado | Fruta Madre</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            padding: 24px;
            display: flex;
            flex-direction: column;
            background: #FFF4DF;
            color: #29231F;
            font-family: Arial, Helvetica, sans-serif;
        }

        header {
            width: 100%;
            max-width: 1050px;
            margin: 0 auto;
        }

        .marca {
            color: #477A45;
            font-size: 25px;
            font-weight: 800;
            letter-spacing: -.7px;
        }

        .marca span {
            color: #29231F;
        }

        main {
            width: 100%;
            max-width: 650px;
            margin: auto;
            padding: 20px 0;
        }

        .tarjeta {
            padding: 38px;
            background: #FFFFFF;
            border: 1px solid #EEE5D7;
            border-radius: 22px;
            box-shadow: 0 8px 30px #45301B0A;
            text-align: center;
        }

        .icono {
            width: 76px;
            height: 76px;
            margin: 0 auto 20px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: #EDF6E9;
            color: #477A45;
            font-size: 37px;
            font-weight: bold;
        }

        .etiqueta {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            background: #F7E8CF;
            color: #775127;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1px;
        }

        h1 {
            margin: 17px 0 12px;
            font-size: clamp(26px, 5vw, 35px);
            line-height: 1.2;
            letter-spacing: -.7px;
        }

        .descripcion {
            max-width: 460px;
            margin: 0 auto;
            color: #746B61;
            font-size: 14px;
            line-height: 1.7;
        }

        .folio {
            margin: 28px 0;
            padding: 19px;
            border: 1px dashed #D7CCBA;
            border-radius: 13px;
            background: #FFFCF7;
        }

        .folio small {
            display: block;
            margin-bottom: 5px;
            color: #746B61;
            font-size: 12px;
        }

        .folio strong {
            color: #477A45;
            font-size: 29px;
            letter-spacing: 1px;
        }

        .detalles {
            margin-top: 22px;
            padding: 20px;
            border-radius: 13px;
            background: #FAF7F0;
            text-align: left;
        }

        .detalles h2 {
            margin: 0 0 15px;
            font-size: 16px;
        }

        .fila {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 15px;
            padding: 11px 0;
            border-bottom: 1px solid #EAE2D6;
            font-size: 13px;
        }

        .fila:last-child {
            border-bottom: 0;
        }

        .fila span {
            color: #746B61;
        }

        .fila strong {
            text-align: right;
            overflow-wrap: anywhere;
        }

        .aviso {
            margin-top: 18px;
            color: #746B61;
            font-size: 12px;
            line-height: 1.6;
        }

        .acciones {
            display: flex;
            flex-direction: column;
            gap: 11px;
            margin-top: 25px;
        }

        .boton {
            display: block;
            padding: 14px 18px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 800;
            text-decoration: none;
            text-align: center;
            transition: background .2s;
        }

        .principal {
            background: #477A45;
            color: #FFFFFF;
        }

        .principal:hover {
            background: #345D32;
        }

        .secundario {
            background: #F2EBDD;
            color: #29231F;
        }

        .secundario:hover {
            background: #EAE0D0;
        }

        footer {
            padding: 18px 0 5px;
            color: #746B61;
            font-size: 12px;
            text-align: center;
        }

        @media (max-width: 480px) {
            body {
                padding: 18px;
            }

            .tarjeta {
                padding: 24px 18px;
            }

            .fila {
                flex-direction: column;
                gap: 4px;
            }

            .fila strong {
                text-align: left;
            }
        }
    </style>
</head>

<body>

<header>
    <div class="marca">Fruta <span>Madre</span></div>
</header>

<main>
    <section class="tarjeta">

        <div class="icono" aria-hidden="true">
            <?= $escapar($estadoIcono) ?>
        </div>

        <span class="etiqueta">FRUTA MADRE</span>

        <h1><?= $escapar($estadoTitulo) ?></h1>

        <p class="descripcion">
            <?= $escapar($estadoDescripcion) ?>
        </p>

        <div class="folio">
            <small>Tu número de pedido</small>
            <strong>#<?= $idPedido ?></strong>
        </div>

        <div class="detalles">
            <h2>Detalles de tu compra</h2>

            <div class="fila">
                <span>Folio</span>
                <strong>#<?= $idPedido ?></strong>
            </div>

            <div class="fila">
                <span>Método de pago</span>
                <strong><?= $escapar($nombreMetodo) ?></strong>
            </div>

            <div class="fila">
                <span>Estado del pedido</span>
                <strong>Registrado</strong>
            </div>

            <div class="fila">
                <span>Estado del pago</span>
                <strong>
                    <?php
                    if ($metodoPago === 'efectivo') {
                        echo 'Pago al recibir o recoger';
                    } elseif (
                        $metodoPago === 'transferencia' ||
                        $metodoPago === 'tarjeta'
                    ) {
                        echo 'Pendiente de verificar o procesar';
                    } else {
                        echo 'Por consultar';
                    }
                    ?>
                </strong>
            </div>
        </div>

        <p class="aviso">
            Guarda tu número de pedido para futuras consultas.
            Si necesitas ayuda con tu compra, contacta al establecimiento.
        </p>

        <div class="acciones">
            <a class="boton principal" href="index.php">
                Volver al catálogo
            </a>

            <a class="boton secundario" href="index.php?accion=catalogo">
                Seguir comprando
            </a>
        </div>

    </section>
</main>

<footer>
    Fruta Madre · Te damos hasta para llevar.
</footer>

</body>
</html>