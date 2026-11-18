<?php
$productosCarrito = $productosCarrito ?? [];
$datosPedido = $datosPedido ?? ($_SESSION['datos_pedido'] ?? []);
$total = (float) ($total ?? 0);

$metodoSeleccionado = $_SESSION['metodo_pago'] ?? 'efectivo';

if (!in_array($metodoSeleccionado, ['efectivo', 'transferencia', 'mercadopago_prueba'], true)) {
    $metodoSeleccionado = 'efectivo';
}


if (empty($_SESSION['token_confirmacion'])) {
    $_SESSION['token_confirmacion'] = bin2hex(random_bytes(32));
}

$esc = static function ($valor): string {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
};

$dinero = static function ($valor): string {
    return '$' . number_format((float) $valor, 2) . ' MXN';
};

$cantidadTotal = 0;

foreach ($productosCarrito as $producto) {
    $cantidadTotal += (int) ($producto['cantidad'] ?? 0);
}

$servicio = $datosPedido['tipo_servicio'] ?? 'recoger';
require_once __DIR__ . '/../../services/MercadoPagoPruebas.php';
$mpPruebasConfigurado = MercadoPagoPruebas::habilitado();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>FRUTA MADRE - Pago y Confirmación del Pedido</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Syne:wght@400;500;600;700;800&family=Material+Symbols+Outlined" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>

<script>
tailwind.config = {
    theme: {
        extend: {
            colors: {
                background: '#fff8f0',
                primary: '#2f612f',
                'primary-container': '#477a45',
                secondary: '#ac2d50',
                'secondary-container': '#fc6a8a',
                'surface-container-low': '#fdf2de',
                'surface-container': '#f8edd8',
                'surface-container-high': '#f2e7d2',
                'surface-container-highest': '#ece1cd',
                'outline-variant': '#c1c9bc',
                'on-surface': '#201b0f',
                'on-surface-variant': '#41493f',
                tertiary: '#903e00',
                'tertiary-container': '#b55107'
            },
            fontFamily: {
                body: ['DM Sans', 'sans-serif'],
                display: ['Syne', 'sans-serif']
            }
        }
    }
};
</script>

<style>
* { box-sizing: border-box; }
body { margin: 0; font-family: 'DM Sans', sans-serif; }
h1, h2, h3, .display { font-family: 'Syne', sans-serif; }
.pay-tab { transition: .18s; border: 2px solid transparent; }
.pay-tab.active {
    background: #2f612f;
    color: white;
    border-color: #201b0f;
    box-shadow: 3px 3px 0 #201b0f;
}
.pay-tab:not(.active) {
    background: #fdf2de;
    color: #201b0f;
    border-color: #e7d9c4;
    box-shadow: 2px 2px 0 #201b0f;
}
.pay-panel { display: none; }
.pay-panel.active { display: flex; }
.cash-btn.active {
    background: #2f612f;
    color: white;
}
.sim-btn.active {
    background: #2f612f;
    color: white;
}
.field {
    width: 100%;
    border: 1px solid #c1c9bc;
    border-bottom: 2px solid #201b0f;
    border-radius: 7px;
    padding: 11px 12px;
    background: white;
    font-family: 'DM Sans', sans-serif;
}
.field:focus { outline: 2px solid #477a45; }
@media (max-width: 640px) {
    .pay-tab { padding: 10px !important; }
}
</style>
</head>

<body class="bg-background text-on-surface">

<header class="fixed top-0 left-0 right-0 z-40 bg-background/95 shadow-sm backdrop-blur">
    <div class="mx-auto flex h-20 max-w-screen-2xl items-center justify-between gap-4 px-5 lg:px-12">

        <a href="index.php" class="flex items-center gap-3 no-underline">
            <span class="material-symbols-outlined text-4xl text-primary">nutrition</span>
            <span class="flex flex-col">
                <strong class="display text-lg font-extrabold tracking-wider">FRUTA MADRE</strong>
                <span class="text-xs font-bold text-secondary">Te damos hasta para llevar</span>
            </span>
        </a>

        <nav class="hidden items-center gap-1 rounded-lg bg-surface-container-low p-1 xl:flex">
            <a href="index.php" class="rounded-lg px-4 py-2 text-sm font-bold hover:bg-surface-container">Inicio</a>
            <a href="index.php?accion=catalogo" class="rounded-lg px-4 py-2 text-sm font-bold hover:bg-surface-container">Catálogo</a>
            <a href="index.php?accion=eventos" class="rounded-lg px-4 py-2 text-sm font-bold hover:bg-surface-container">Eventos</a>
            <a href="index.php?accion=mis-pedidos" class="rounded-lg px-4 py-2 text-sm font-bold hover:bg-surface-container">Mis pedidos</a>
        </nav>

        <div class="flex items-center gap-2">
            <a href="index.php?accion=carrito" aria-label="Carrito"
               class="relative rounded-lg bg-surface-container-low p-2">
                <span class="material-symbols-outlined">shopping_bag</span>
                <span class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-secondary text-xs font-bold text-white">
                    <?= $cantidadTotal ?>
                </span>
            </a>
            <a href="index.php?accion=cuenta" aria-label="Mi cuenta"
               class="rounded-full bg-primary p-2 text-white">
                <span class="material-symbols-outlined">person</span>
            </a>
        </div>
    </div>

    <div class="flex h-1.5">
        <div class="flex-1 bg-primary-container"></div>
        <div class="flex-1 bg-secondary-container"></div>
        <div class="flex-1 bg-surface-container-high"></div>
        <div class="flex-1 bg-tertiary-container"></div>
    </div>
</header>

<main class="min-h-screen pt-20">

    <!-- MIGAS DE PAN Y PASOS -->
    <section class="border-b-2 border-dashed border-on-surface/20 bg-surface-container-low px-5 py-4 lg:px-12">
        <div class="mx-auto flex max-w-7xl flex-col justify-between gap-4 md:flex-row md:items-center">

            <nav class="flex flex-wrap items-center gap-2 text-xs font-bold">
                <a href="index.php" class="hover:text-primary">⌂ Inicio</a>
                <span>/</span>
                <a href="index.php?accion=carrito" class="hover:text-primary">Carrito</a>
                <span>/</span>
                <span class="text-primary">Pago y Confirmación</span>
            </nav>

            <div class="flex flex-wrap items-center gap-3 text-xs font-extrabold uppercase">
                <span class="flex items-center gap-1 text-primary">
                    <span class="grid h-6 w-6 place-items-center rounded-full bg-primary text-white">✓</span>
                    1. Carrito
                </span>
                <span class="h-0.5 w-5 bg-primary/40"></span>
                <span class="flex items-center gap-1 text-primary">
                    <span class="grid h-6 w-6 place-items-center rounded-full bg-primary text-white">✓</span>
                    2. Envío
                </span>
                <span class="h-0.5 w-5 bg-secondary"></span>
                <span class="flex items-center gap-1 rounded-full bg-secondary/10 px-2 py-1 text-secondary">
                    <span class="grid h-6 w-6 place-items-center rounded-full bg-secondary text-white">3</span>
                    3. Pago
                    <span class="rounded bg-secondary px-1 text-white">En curso</span>
                </span>
                <span class="h-0.5 w-5 bg-outline-variant"></span>
                <span class="flex items-center gap-1 opacity-50">
                    <span class="grid h-6 w-6 place-items-center rounded-full bg-surface-container-highest">4</span>
                    4. Listo
                </span>
            </div>
        </div>
    </section>

    <div class="mx-auto max-w-7xl px-5 py-7 lg:px-12">

        <!-- SIMULADOR VISUAL -->
        <div class="mb-5 flex flex-wrap items-center gap-2 border-b border-dashed border-outline-variant pb-3 text-[10px] font-extrabold uppercase">
            <span class="mr-2">⚙ SIMULADOR DE ESTADOS:</span>
            <button type="button" class="sim-btn active rounded px-2 py-1" onclick="setSimulationState('vista')">▣ Vista de pago</button>
            <button type="button" class="sim-btn rounded bg-white px-2 py-1" onclick="setSimulationState('confirmado')">✨ ¡Ya quedó! (Confirmado)</button>
            <button type="button" class="sim-btn rounded bg-white px-2 py-1" onclick="setSimulationState('pendiente')">⌛ Pendiente (SPEI)</button>
            <button type="button" class="sim-btn rounded bg-white px-2 py-1" onclick="setSimulationState('rechazado')">⚠ Rechazado</button>
            <button type="button" class="sim-btn rounded bg-white px-2 py-1" onclick="setSimulationState('cancelado')">✕ Cancelado</button>
            <span class="font-normal normal-case text-on-surface-variant">Vista de demostración; no cambia el estado real del pedido.</span>
        </div>

        <!-- HERO -->
        <section class="mb-7 rounded-xl border border-on-surface/20 bg-surface-container-low p-5 shadow-[3px_3px_0px_#201b0f] md:p-7">
            <span class="mb-2 inline-block rounded-full bg-secondary/10 px-3 py-1 text-xs font-extrabold uppercase tracking-widest text-secondary">
                ● Puesto digital No. 01 · Último paso
            </span>

            <h1 class="display text-4xl font-extrabold uppercase leading-none tracking-tight md:text-6xl">
                YA CASI <span class="text-secondary">QUEDA</span>
            </h1>

            <div class="mt-3 flex flex-wrap items-end justify-between gap-4">
                <p class="max-w-xl text-sm leading-6 text-on-surface-variant">
                    Nomás falta liquidar y tu orden se echa a andar de volada.
                    Fruta fresca, chamoy legítimo y todo bien frío.
                </p>

                <div class="rotate-[-2deg] rounded-lg border-2 border-on-surface bg-primary px-4 py-2 text-center text-white shadow-[2px_2px_0px_#201b0f]">
                    <strong class="block text-xs uppercase">♧ Pago 100% seguro</strong>
                    <span class="block text-[9px]">Protección de tus datos</span>
                </div>
            </div>
        </section>

        <?php if (!empty($_SESSION['error_pedido'])): ?>
            <div class="mb-5 rounded-lg border border-red-300 bg-red-50 p-4 text-sm font-bold text-red-800">
                <?= $esc($_SESSION['error_pedido']) ?>
            </div>
            <?php unset($_SESSION['error_pedido']); ?>
        <?php endif; ?>

        <?php if (empty($productosCarrito)): ?>

            <section class="rounded-xl border bg-white p-10 text-center shadow-[3px_3px_0px_#201b0f]">
                <h2 class="display text-2xl font-extrabold uppercase">Tu carrito está vacío</h2>
                <p class="my-4">Agrega productos antes de continuar con el pago.</p>
                <a href="index.php?accion=catalogo" class="inline-block rounded-lg bg-primary px-5 py-3 font-bold text-white">
                    Volver al catálogo
                </a>
            </section>

        <?php else: ?>

        <form action="index.php?accion=confirmar-pedido" method="POST" id="form-pago">
            <input type="hidden" name="token" value="<?= $esc($_SESSION['token_confirmacion']) ?>">
            <input type="hidden" name="metodo_pago" id="metodo_pago" value="<?= $esc($metodoSeleccionado) ?>">
            <input type="hidden" name="pago_con" id="pago_con_hidden" value="">

            <div class="grid grid-cols-1 items-start gap-5 lg:grid-cols-12">

                <!-- COLUMNA IZQUIERDA: PAGO -->
                <section class="flex flex-col gap-5 lg:col-span-7">

                    <div class="rounded-xl border border-outline-variant bg-white p-4 shadow-[4px_4px_0px_#201b0f] md:p-5">

                        <div class="mb-5 flex flex-wrap items-center justify-between gap-2">
                            <h2 class="display text-lg font-extrabold uppercase">▣ ¿Cómo vas a pagar?</h2>
                            <span class="rounded-full bg-[#ffdbca] px-3 py-1 text-[10px] font-extrabold uppercase">Elige tu método</span>
                        </div>

                        <!-- MÉTODOS -->
                        <div class="mb-5 grid grid-cols-1 gap-2 sm:grid-cols-3" role="group" aria-label="Métodos de pago">

                           <button type="button" id="tab-mercadopago_prueba"
        class="pay-tab flex flex-col items-start gap-2 rounded-lg p-3 text-left <?= $mpPruebasConfigurado ? '' : 'opacity-50 cursor-not-allowed' ?>"
        <?= $mpPruebasConfigurado ? 'onclick="selectPaymentMethod(\'mercadopago_prueba\')"' : 'disabled aria-disabled="true"' ?>>
    <span class="material-symbols-outlined text-2xl">credit_card</span>
    <strong class="display text-sm uppercase">Mercado Pago</strong>
    <span class="text-[10px]">Pago de prueba · sin cobros reales</span>
</button>

                            <button type="button" id="tab-efectivo" class="pay-tab flex flex-col items-start gap-2 rounded-lg p-3 text-left"
                                    onclick="selectPaymentMethod('efectivo')">
                                <span class="material-symbols-outlined text-2xl text-tertiary">payments</span>
                                <strong class="display text-sm uppercase">Efectivo</strong>
                                <span class="text-[10px]">Pago al repartidor</span>
                            </button>

                            <button type="button" id="tab-transferencia" class="pay-tab flex flex-col items-start gap-2 rounded-lg p-3 text-left"
                                    onclick="selectPaymentMethod('transferencia')">
                                <span class="material-symbols-outlined text-2xl text-secondary">account_balance</span>
                                <strong class="display text-sm uppercase">Transferencia</strong>
                                <span class="text-[10px]">SPEI o depósito</span>
                            </button>

                        </div>

                        <!-- PANEL TARJETA -->
                        <div id="panel-mercadopago_prueba" class="pay-panel hidden flex-col gap-4">

                            <div class="flex items-center justify-between rounded-lg bg-surface-container-low p-3">
                                <span class="display text-[10px] font-extrabold uppercase">Tarjetas aceptadas:</span>
                                <div class="flex gap-2">
                                    <span class="rounded bg-white px-2 py-1 text-[9px] font-extrabold shadow">VISA</span>
                                    <span class="rounded bg-white px-2 py-1 text-[9px] font-extrabold shadow">MASTERCARD</span>
                                    <span class="rounded bg-white px-2 py-1 text-[9px] font-extrabold shadow">AMEX</span>
                                </div>
                            </div>

                            <div class="rounded-lg border border-outline-variant bg-surface-container-low p-4">
                                <div class="mb-2 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-primary">lock</span>
                                    <strong class="display text-sm uppercase">Pago electrónico seguro</strong>
                                </div>
                                <p class="text-xs leading-5 text-on-surface-variant">
                                    Mercado Pago funciona exclusivamente con credenciales TEST. Serás redirigido al Checkout Pro de pruebas.
                                    Usa solamente cuentas y tarjetas de prueba. Nunca introduzcas datos bancarios reales.
                                </p>
                            </div>

                            <div class="flex items-start gap-2 rounded-lg bg-surface-container p-3 text-xs leading-5 text-on-surface-variant">
                                <span class="material-symbols-outlined text-primary">security</span>
                                <p>Fruta Madre no almacena números de tarjeta ni CVV. Las pruebas se procesan en la plataforma de Mercado Pago.</p>
                            </div>
                        </div>

                        <!-- PANEL EFECTIVO -->
                        <div id="panel-efectivo" class="pay-panel hidden flex-col gap-4">

                            <div class="flex items-start gap-3 rounded-xl bg-surface-container-low p-4 shadow-[2px_2px_0px_#201b0f]">
                                <span class="material-symbols-outlined text-3xl text-primary">delivery_dining</span>
                                <div>
                                    <h3 class="display mb-2 text-base font-extrabold uppercase">Pago en mano al recibir</h3>
                                    <p class="text-xs leading-5 text-on-surface-variant">
                                        Pagarás tus <strong class="text-on-surface"><?= $dinero($total) ?></strong>
                                        directamente en efectivo al recibir o recoger tu pedido.
                                    </p>
                                </div>
                            </div>

                            <div>
                                <label class="display mb-3 block text-xs font-extrabold uppercase" for="pago-con-visible">
                                    ¿Con cuánto vas a pagar? (Para preparar el cambio)
                                </label>

                                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                                    <button type="button" class="cash-btn rounded-lg bg-surface-container p-3 text-[10px] font-extrabold shadow-[2px_2px_0px_#201b0f]"
                                            onclick="setCashChange(this, 'exacto')">Cambio exacto</button>
                                    <button type="button" class="cash-btn rounded-lg bg-surface-container p-3 text-[10px] font-extrabold shadow-[2px_2px_0px_#201b0f]"
                                            onclick="setCashChange(this, '200')">Con $200</button>
                                    <button type="button" class="cash-btn rounded-lg bg-surface-container p-3 text-[10px] font-extrabold shadow-[2px_2px_0px_#201b0f]"
                                            onclick="setCashChange(this, '500')">Con $500</button>
                                    <button type="button" class="cash-btn rounded-lg bg-surface-container p-3 text-[10px] font-extrabold shadow-[2px_2px_0px_#201b0f]"
                                            onclick="setCashChange(this, 'otro')">Otro billete</button>
                                </div>

                                <input type="number" id="pago-con-visible" min="<?= $total ?>" step="0.01"
                                       class="field mt-3" placeholder="Escribe el importe"
                                       oninput="actualizarCambio(this.value)">

                                <p id="cambio-estimado" class="mt-2 text-xs font-bold text-primary"></p>
                            </div>
                        </div>

                        <!-- PANEL TRANSFERENCIA -->
                        <div id="panel-transferencia" class="pay-panel hidden flex-col gap-4">

                            <div class="flex items-start gap-3 rounded-xl bg-surface-container-low p-4 shadow-[2px_2px_0px_#201b0f]">
                                <span class="material-symbols-outlined text-3xl text-secondary">account_balance</span>
                                <div>
                                    <h3 class="display mb-2 text-base font-extrabold uppercase">Transferencia SPEI</h3>
                                    <p class="text-xs leading-5 text-on-surface-variant">
                                        El pago por transferencia requiere datos bancarios oficiales y verificación
                                        de la operación antes de marcar el pedido como pagado.
                                    </p>
                                </div>
                            </div>

                            <div class="rounded-lg border-2 border-dashed border-outline-variant bg-white p-5">
                                <div class="mb-2 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-secondary">info</span>
                                    <strong class="display text-sm uppercase">Datos bancarios pendientes</strong>
                                </div>
                                <p class="text-xs leading-5 text-on-surface-variant">
                                    Los datos bancarios del negocio todavía no están configurados en el sistema.
                                    No realices una transferencia hasta recibir los datos oficiales de Fruta Madre.
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 border-t border-dashed border-outline-variant pt-4 text-[10px] leading-5 text-on-surface-variant">
                            <span class="font-extrabold text-primary">✓</span>
                            Al confirmar se registrará el pedido. Esta acción no realiza cobros electrónicos ni verifica transferencias.
                        </div>

                    </div>

                    <button type="submit" id="btn-confirmar"
                            class="flex w-full items-center justify-between gap-3 rounded-xl bg-primary px-5 py-4 text-left text-white shadow-[4px_4px_0px_#201b0f] transition hover:bg-primary-container">
                        <span class="display text-sm font-extrabold uppercase md:text-base">
                            CONFIRMAR PEDIDO — <span id="texto-total"><?= $dinero($total) ?></span>
                        </span>
                        <span class="material-symbols-outlined">arrow_forward</span>
                    </button>

                    <p class="text-[10px] text-on-surface-variant">
                        Al confirmar aceptas los términos del servicio y las condiciones de entrega.
                    </p>
                </section>

                <!-- COLUMNA DERECHA: TICKET -->
                <aside class="flex flex-col gap-3 lg:col-span-5">

                    <div class="overflow-hidden rounded-xl border border-outline-variant bg-white p-4 shadow-[4px_4px_0px_#201b0f]">

                        <div class="mb-4 flex h-1.5 overflow-hidden">
                            <div class="flex-1 bg-primary"></div>
                            <div class="flex-1 bg-secondary"></div>
                            <div class="flex-1 bg-tertiary-container"></div>
                            <div class="flex-1 bg-primary-container"></div>
                        </div>

                        <div class="mb-4 flex items-center justify-between gap-2">
                            <div>
                                <h2 class="display text-lg font-extrabold uppercase">Resumen de tu pedido</h2>
                                <p class="text-[10px] text-on-surface-variant">Fruta fresca del día</p>
                            </div>
                            <span class="rounded-full bg-secondary px-2 py-1 text-[9px] font-extrabold text-white">
                                <?= $cantidadTotal ?> ANTOJITOS
                            </span>
                        </div>

                        <!-- PRODUCTOS REALES -->
                        <div class="flex flex-col gap-3">
                            <?php foreach ($productosCarrito as $producto): ?>
                                <div class="flex items-start justify-between gap-3 border-b border-dashed border-outline-variant pb-3">
                                    <div>
                                        <p class="text-xs font-extrabold">
                                            <?= (int) ($producto['cantidad'] ?? 0) ?>×
                                            <?= $esc($producto['nombre'] ?? $producto['nombre_producto'] ?? 'Producto') ?>
                                        </p>
                                        <p class="mt-1 text-[10px] text-on-surface-variant">
                                            Cantidad: <?= (int) ($producto['cantidad'] ?? 0) ?>
                                        </p>
                                    </div>
                                    <strong class="whitespace-nowrap text-xs">
                                        <?= $dinero($producto['subtotal'] ?? 0) ?>
                                    </strong>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- TOTALES -->
                        <div class="mt-4 flex flex-col gap-2 border-b border-dashed border-outline-variant pb-4 text-xs">
                            <div class="flex justify-between gap-3">
                                <span class="text-on-surface-variant">Subtotal de antojitos</span>
                                <span><?= $dinero($total) ?></span>
                            </div>

                            <div class="flex justify-between gap-3">
                                <span class="text-on-surface-variant">Envío</span>
                                <span><?= $servicio === 'domicilio' ? 'Por definir' : '$0.00 MXN' ?></span>
                            </div>
                        </div>

                        <div class="mt-4 rounded-xl border-2 border-on-surface bg-surface-container-low p-4 shadow-[2px_2px_0px_#201b0f]">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="display text-[10px] font-extrabold uppercase">Total a liquidar</p>
                                    <p class="text-[9px] font-bold text-primary">IVA incluido cuando corresponda</p>
                                </div>
                                <strong class="display text-2xl font-extrabold text-on-surface">
                                    <?= $dinero($total) ?>
                                </strong>
                            </div>
                        </div>

                        <?php if ($servicio === 'domicilio'): ?>
                            <p class="mt-3 text-[10px] leading-4 text-on-surface-variant">
                                El costo de envío aún no se calcula y no está incluido en el total.
                            </p>
                        <?php endif; ?>

                        <!-- DIRECCIÓN REAL -->
                        <div class="mt-4 rounded-lg bg-surface-container-low p-3">
                            <div class="flex items-start gap-2">
                                <span class="material-symbols-outlined text-sm text-primary">location_on</span>
                                <div>
                                    <strong class="display text-[10px] uppercase">
                                        Destino de entrega
                                    </strong>
                                    <p class="mt-1 text-[11px] leading-4">
                                        <?= $servicio === 'domicilio'
                                            ? $esc($datosPedido['direccion'] ?? 'Dirección no especificada')
                                            : 'Recoger en el establecimiento' ?>
                                    </p>
                                    <?php if ($servicio === 'domicilio' && !empty($datosPedido['referencias'])): ?>
                                        <p class="mt-1 text-[10px] text-on-surface-variant">
                                            Referencias: <?= $esc($datosPedido['referencias']) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 rounded-lg bg-[#e9f0e8] p-3">
                            <div class="flex items-start gap-2">
                                <span class="material-symbols-outlined text-sm text-primary">verified_user</span>
                                <p class="text-[10px] leading-4 text-on-surface-variant">
                                    <strong>Garantía de Fruta Madre:</strong>
                                    revisa tus datos y productos antes de confirmar.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-xl border-2 border-on-surface shadow-[3px_3px_0px_#201b0f]">
                        <img
                            src="https://images.unsplash.com/photo-1546173159-315724a31696?auto=format&fit=crop&w=1000&q=85"
                            alt="Fruta preparada con chile y limón"
                            class="h-28 w-full object-cover">
                        <div class="bg-on-surface px-3 py-2 text-[9px] font-extrabold uppercase text-white">
                            🍉 100% hecho al momento en el puesto
                        </div>
                    </div>

                </aside>
            </div>
        </form>

        <?php endif; ?>
    </div>
</main>

<!-- FOOTER ORIGINAL -->
<footer class="mt-8 bg-surface-container-low">
    <div class="flex h-1.5">
        <div class="flex-1 bg-tertiary-container"></div>
        <div class="flex-1 bg-surface-container-high"></div>
        <div class="flex-1 bg-secondary-container"></div>
        <div class="flex-1 bg-primary-container"></div>
    </div>

    <div class="mx-auto grid max-w-7xl grid-cols-1 gap-8 px-5 py-8 md:grid-cols-2 lg:grid-cols-4 lg:px-12">

        <div>
            <h2 class="display text-lg font-extrabold text-primary">FRUTA MADRE</h2>
            <p class="mt-2 display text-xs font-extrabold uppercase tracking-wider text-secondary">
                Te damos hasta para llevar.
            </p>
            <p class="mt-3 text-xs leading-5 text-on-surface-variant">
                El auténtico sabor del mercado urbano mexicano en tu puerta:
                fruta fresca con chilito y limón, botanas preparadas, repostería
                artesanal, flores de temporada y carritos de antojos para tus mejores eventos.
            </p>
            <span class="mt-3 inline-block rounded-full bg-surface-container px-3 py-1 text-[9px] font-extrabold uppercase text-tertiary">
                🇲🇽 Hecho con mucho chamoy y amor en México
            </span>
        </div>

        <div>
            <h3 class="display mb-3 text-xs font-extrabold uppercase">Explorar</h3>
            <ul class="space-y-2 text-xs text-on-surface-variant">
                <li><a href="index.php">Inicio</a></li>
                <li><a href="index.php?accion=catalogo">Catálogo completo</a></li>
                <li><a href="index.php?accion=carrito">Carrito de compras</a></li>
                <li><a href="index.php?accion=cuenta">Mi cuenta</a></li>
            </ul>
        </div>

        <div>
            <h3 class="display mb-3 text-xs font-extrabold uppercase">Atención</h3>
            <ul class="space-y-2 text-xs text-on-surface-variant">
                <li>Contacto directo</li>
                <li>Preguntas frecuentes</li>
                <li>Términos y condiciones</li>
                <li>Aviso de privacidad</li>
            </ul>
        </div>

        <div>
            <h3 class="display mb-3 text-xs font-extrabold uppercase">Mercado social</h3>
            <p class="mb-3 text-xs text-on-surface-variant">¡Échanos un grito o síguenos la pista!</p>
            <div class="flex flex-wrap gap-2 text-[10px] font-bold">
                <span class="rounded bg-surface-container px-3 py-2">Instagram</span>
                <span class="rounded bg-surface-container px-3 py-2">TikTok</span>
                <span class="rounded bg-surface-container px-3 py-2">WhatsApp</span>
                <span class="rounded bg-surface-container px-3 py-2">Facebook</span>
            </div>
        </div>
    </div>

    <div class="flex flex-col justify-between gap-2 bg-surface-container px-5 py-4 text-center text-[10px] text-on-surface-variant sm:flex-row sm:text-left">
        <p>© <?= date('Y') ?> FRUTA MADRE. Todos los derechos reservados.</p>
        <p class="display uppercase">Puesto digital No. 01</p>
    </div>
</footer>

<script>
const totalPedido = <?= json_encode($total) ?>;

function selectPaymentMethod(metodo) {
    // El controlador acepta "transferencia", no "spei".
    document.getElementById('metodo_pago').value = metodo;

    document.querySelectorAll('.pay-tab').forEach(function (tab) {
        tab.classList.remove('active');
    });

    const tab = document.getElementById('tab-' + metodo);
    if (tab) tab.classList.add('active');

    document.querySelectorAll('.pay-panel').forEach(function (panel) {
        panel.classList.remove('active');
        panel.classList.add('hidden');
    });

    const panel = document.getElementById('panel-' + metodo);
    if (panel) {
        panel.classList.remove('hidden');
        panel.classList.add('active');
    }

    actualizarCampoEfectivo();
}

function setCashChange(boton, valor) {
    document.querySelectorAll('.cash-btn').forEach(function (btn) {
        btn.classList.remove('active');
    });

    boton.classList.add('active');

    const input = document.getElementById('pago-con-visible');

    if (valor === 'exacto') {
        input.value = totalPedido.toFixed(2);
    } else if (valor === 'otro') {
        input.value = '';
        input.focus();
    } else {
        input.value = valor;
    }

    actualizarCambio(input.value);
}

function actualizarCambio(valor) {
    const resultado = document.getElementById('cambio-estimado');
    const inputHidden = document.getElementById('pago_con_hidden');
    const cantidad = Number(valor);

    if (valor === '' || !Number.isFinite(cantidad)) {
        resultado.textContent = '';
        inputHidden.value = '';
        return;
    }

    inputHidden.value = cantidad.toFixed(2);

    if (cantidad < totalPedido) {
        resultado.textContent = 'El importe debe ser igual o mayor al total.';
        resultado.classList.remove('text-primary');
        resultado.classList.add('text-secondary');
        return;
    }

    resultado.textContent = 'Cambio estimado: $' + (cantidad - totalPedido).toFixed(2) + ' MXN';
    resultado.classList.add('text-primary');
    resultado.classList.remove('text-secondary');
}

function actualizarCampoEfectivo() {
    const metodo = document.getElementById('metodo_pago').value;
    const input = document.getElementById('pago-con-visible');

    input.disabled = metodo !== 'efectivo';

    if (metodo !== 'efectivo') {
        document.getElementById('pago_con_hidden').value = '';
        document.getElementById('cambio-estimado').textContent = '';
    } else {
        actualizarCambio(input.value);
    }
}

// Simulación visual únicamente: no modifica pagos ni pedidos reales.
function setSimulationState(estado) {
    document.querySelectorAll('.sim-btn').forEach(function (btn) {
        btn.classList.remove('active');
    });

    const boton = Array.from(document.querySelectorAll('.sim-btn')).find(function (btn) {
        return btn.getAttribute('onclick').includes("'" + estado + "'");
    });

    if (boton) boton.classList.add('active');
}

document.getElementById('form-pago')?.addEventListener('submit', function (evento) {
    const metodo = document.getElementById('metodo_pago').value;

    if (!['efectivo', 'transferencia', 'mercadopago_prueba'].includes(metodo)) {
        evento.preventDefault();
        alert('Selecciona un método de pago válido.');
        return;
    }

    const efectivo = document.getElementById('pago-con-visible');

    if (metodo === 'efectivo' && efectivo.value !== '' && Number(efectivo.value) < totalPedido) {
        evento.preventDefault();
        alert('El importe con el que pagarás no puede ser menor al total del pedido.');
        efectivo.focus();
    }
});

selectPaymentMethod(<?= json_encode($metodoSeleccionado) ?>);
</script>

</body>
</html>
