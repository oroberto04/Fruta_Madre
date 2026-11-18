<?php
$pedidos = $pedidos ?? [];

// Consecutivo personal calculado con los pedidos que entrega el controlador.
// El ID real de PostgreSQL se conserva para consultas y enlaces.
$pedidosPorAntiguedad = $pedidos;
usort($pedidosPorAntiguedad, static function ($a, $b): int {
    $fechaA = strtotime((string) ($a['fecha_pedido'] ?? '')) ?: 0;
    $fechaB = strtotime((string) ($b['fecha_pedido'] ?? '')) ?: 0;

    if ($fechaA === $fechaB) {
        return (int) ($a['id_pedido'] ?? 0) <=> (int) ($b['id_pedido'] ?? 0);
    }

    return $fechaA <=> $fechaB;
});

$numerosPedidos = [];
foreach ($pedidosPorAntiguedad as $indice => $pedidoOrdenado) {
    $numerosPedidos[(int) ($pedidoOrdenado['id_pedido'] ?? 0)] = $indice + 1;
}

$esc = static function ($valor): string {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
};

$dinero = static function ($valor): string {
    return '$' . number_format((float) $valor, 2) . ' MXN';
};

$fecha = static function ($valor): string {
    if (empty($valor)) {
        return 'Fecha no disponible';
    }

    try {
        return (new DateTime($valor))->format('d/m/Y · H:i');
    } catch (Throwable $e) {
        return 'Fecha no disponible';
    }
};

$estadoInfo = static function ($estado): array {
    $estado = strtolower(trim((string) $estado));

    if (in_array($estado, ['entregado', 'completado', 'finalizado'], true)) {
        return [
            'texto' => 'Entregado',
            'icono' => 'check_circle',
            'clase' => 'bg-primary-fixed text-on-primary-fixed',
            'grupo' => 'entregados'
        ];
    }

    if (in_array($estado, ['cancelado', 'cancelada'], true)) {
        return [
            'texto' => 'Cancelado',
            'icono' => 'cancel',
            'clase' => 'bg-error-container text-on-error-container',
            'grupo' => 'cancelados'
        ];
    }

    if (in_array($estado, ['en_camino', 'en camino', 'en_reparto'], true)) {
        return [
            'texto' => 'En camino',
            'icono' => 'delivery_dining',
            'clase' => 'bg-primary-fixed text-on-primary-fixed',
            'grupo' => 'proceso'
        ];
    }

    if (in_array($estado, ['en_preparacion', 'en preparación', 'preparando'], true)) {
        return [
            'texto' => 'En preparación',
            'icono' => 'skillet',
            'clase' => 'bg-tertiary-fixed text-on-tertiary-fixed',
            'grupo' => 'proceso'
        ];
    }

    if (in_array($estado, ['pendiente', 'confirmado', 'confirmada'], true)) {
        return [
            'texto' => ucfirst($estado),
            'icono' => 'schedule',
            'clase' => 'bg-surface-container-high text-on-surface',
            'grupo' => 'proceso'
        ];
    }

    return [
        'texto' => ucfirst(str_replace('_', ' ', $estado ?: 'Sin estado')),
        'icono' => 'receipt_long',
        'clase' => 'bg-surface-container-high text-on-surface',
        'grupo' => 'proceso'
    ];
};

$totalPedidos = count($pedidos);
$enProceso = 0;
$entregados = 0;
$cancelados = 0;

foreach ($pedidos as $pedido) {
    $grupo = $estadoInfo($pedido['estado_pedido'] ?? '')['grupo'];

    if ($grupo === 'proceso') {
        $enProceso++;
    } elseif ($grupo === 'entregados') {
        $entregados++;
    } elseif ($grupo === 'cancelados') {
        $cancelados++;
    }
}

$pedidoSeleccionado = null;

if (isset($_GET['pedido'])) {
    foreach ($pedidos as $pedido) {
        if ((int) $pedido['id_pedido'] === (int) $_GET['pedido']) {
            $pedidoSeleccionado = $pedido;
            break;
        }
    }
}

if (!$pedidoSeleccionado && !empty($pedidos)) {
    $pedidoSeleccionado = $pedidos[0];
}

$metodosPago = [
    'efectivo' => 'Efectivo',
    'tarjeta' => 'Tarjeta',
    'transferencia' => 'Transferencia bancaria',
    'mercadopago_prueba' => 'Mercado Pago (prueba)'
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mis pedidos | Fruta Madre</title>

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
                        'tertiary': '#903e00',
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

        body {
            margin: 0;
            font-family: 'DM Sans', sans-serif;
        }

        h1, h2, h3, .display {
            font-family: 'Syne', sans-serif;
        }

        .filter-btn {
            border: 1px solid #e7d9c4;
            box-shadow: 2px 2px 0 #201b0f;
            transition: .18s;
        }

        .filter-btn.active {
            background: #2f612f;
            color: white;
            border-color: #201b0f;
        }

        .order-card {
            border: 1px solid #e8dece;
            box-shadow: 3px 3px 0 #201b0f;
            transition: .18s;
        }

        .order-card.selected {
            border-color: #2f612f;
            outline: 2px solid #2f612f;
        }

        .order-card:hover {
            transform: translateY(-2px);
        }

        .timeline-step {
            position: relative;
            padding-left: 34px;
            padding-bottom: 24px;
        }

        .timeline-step::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 23px;
            bottom: 0;
            width: 2px;
            background: #d9dfd4;
        }

        .timeline-step:last-child::before {
            display: none;
        }

        .timeline-dot {
            position: absolute;
            left: 0;
            top: 1px;
            width: 22px;
            height: 22px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: #ece1cd;
            color: #41493f;
        }

        .timeline-step.done .timeline-dot,
        .timeline-step.current .timeline-dot {
            background: #2f612f;
            color: white;
        }

        .hidden-order {
            display: none !important;
        }
    </style>
</head>

<body class="bg-background text-on-surface">

<header class="fixed left-0 right-0 top-0 z-40 bg-background/95 shadow-sm backdrop-blur">
    <div class="mx-auto flex h-20 max-w-screen-2xl items-center justify-between gap-4 px-5 lg:px-12">

        <a href="index.php" class="flex shrink-0 items-center gap-3 no-underline">
            <span class="material-symbols-outlined text-4xl text-primary">nutrition</span>
            <span class="flex flex-col">
                <strong class="display text-lg font-extrabold tracking-wider">FRUTA MADRE</strong>
                <span class="text-xs font-bold text-secondary">Te damos hasta para llevar</span>
            </span>
        </a>

        <nav class="hidden items-center gap-1 rounded-lg bg-surface-container-low p-1 xl:flex">
            <a href="index.php" class="rounded-lg px-4 py-2 text-sm font-bold hover:bg-surface-container">Inicio</a>
            <a href="index.php?accion=catalogo" class="rounded-lg px-4 py-2 text-sm font-bold hover:bg-surface-container">Catálogo</a>
            <a href="index.php?accion=mis-pedidos" class="rounded-lg bg-primary px-4 py-2 text-sm font-bold text-white shadow-[2px_2px_0px_#201b0f]">Mis pedidos</a>
        </nav>

        <div class="flex items-center gap-2">
            <a href="index.php?accion=carrito" aria-label="Carrito"
               class="rounded-lg bg-surface-container-low p-2">
                <span class="material-symbols-outlined">shopping_bag</span>
            </a>

            <a href="index.php?accion=cuenta" aria-label="Mi cuenta"
               class="flex h-9 w-9 items-center justify-center rounded-full bg-primary text-white">
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

<main class="min-h-screen bg-background pt-20">
    <div class="mx-auto flex max-w-7xl flex-col gap-6 px-5 py-7 lg:px-12">

        <!-- MIGAS DE PAN -->
        <div class="flex flex-wrap items-center justify-between gap-3">
            <nav class="flex items-center gap-2 text-sm text-on-surface-variant">
                <a href="index.php" class="flex items-center gap-1 hover:text-primary">
                    <span class="material-symbols-outlined text-lg">storefront</span>
                    Inicio
                </a>
                <span>/</span>
                <strong class="text-secondary">Mis pedidos</strong>
            </nav>

            <div class="flex flex-wrap gap-2">
                <span class="rounded-full bg-surface-container-high px-3 py-1 text-xs font-extrabold shadow-[2px_2px_0px_#201b0f]">
                    ⚡ PUESTO DIGITAL NO. 01
                </span>
                <span class="rounded-full bg-secondary-container px-3 py-1 text-xs font-extrabold text-white shadow-[2px_2px_0px_#201b0f]">
                    🍉 RASTREO DE PEDIDOS
                </span>
            </div>
        </div>

        <!-- ENCABEZADO PRINCIPAL -->
        <section class="relative overflow-hidden rounded-3xl bg-surface-container-low p-6 shadow-[4px_4px_0px_#201b0f] md:p-10">
            <div class="pointer-events-none absolute -bottom-12 -right-12 h-48 w-48 rounded-full bg-secondary/10"></div>

            <div class="relative max-w-3xl">
                <div class="mb-3 flex flex-wrap items-center gap-2">
                    <span class="rounded bg-primary px-2 py-1 text-xs font-extrabold uppercase text-white">
                        Antojo en marcha
                    </span>
                    <span class="text-sm font-semibold text-on-surface-variant">
                        Tu historial de compras
                    </span>
                </div>

                <h1 class="display text-4xl font-extrabold uppercase tracking-tight md:text-6xl">
                    MIS <span class="text-secondary">PEDIDOS</span>
                </h1>

                <p class="mt-4 max-w-2xl text-base leading-7 text-on-surface-variant">
                    Aquí está todo lo que has pedido. Consulta tus compras, revisa sus estados
                    y encuentra los detalles de cada antojo.
                </p>
            </div>

            <!-- FILTROS REALES -->
            <div class="mt-7 flex flex-col gap-4 border-t border-dashed border-outline-variant pt-5 xl:flex-row xl:items-center xl:justify-between">
                <div class="flex flex-wrap gap-2">
                    <button type="button" class="filter-btn active rounded-full px-4 py-2 text-sm font-extrabold"
                            data-filter="todos" onclick="filtrarPedidos('todos', this)">
                        Todos (<?= $totalPedidos ?>)
                    </button>

                    <button type="button" class="filter-btn rounded-full bg-surface-container px-4 py-2 text-sm font-extrabold"
                            data-filter="proceso" onclick="filtrarPedidos('proceso', this)">
                        En proceso (<?= $enProceso ?>)
                    </button>

                    <button type="button" class="filter-btn rounded-full bg-surface-container px-4 py-2 text-sm font-extrabold"
                            data-filter="entregados" onclick="filtrarPedidos('entregados', this)">
                        Entregados (<?= $entregados ?>)
                    </button>

                    <button type="button" class="filter-btn rounded-full bg-surface-container px-4 py-2 text-sm font-extrabold"
                            data-filter="cancelados" onclick="filtrarPedidos('cancelados', this)">
                        Cancelados (<?= $cancelados ?>)
                    </button>
                </div>

                <label class="flex items-center gap-2 rounded-xl bg-surface-container-highest px-4 py-2">
                    <span class="material-symbols-outlined text-tertiary">search</span>
                    <input id="buscar-pedido" type="search"
                           placeholder="Buscar por folio o producto..."
                           class="w-full bg-transparent text-sm outline-none md:w-64"
                           oninput="buscarPedidos(this.value)">
                </label>
            </div>
        </section>

        <!-- HISTORIAL Y DETALLE -->
        <?php if (empty($pedidos)): ?>

            <section class="rounded-2xl border border-outline-variant bg-white px-6 py-16 text-center shadow-[4px_4px_0px_#201b0f]">
                <span class="material-symbols-outlined mb-4 text-6xl text-secondary">shopping_basket</span>
                <h2 class="display text-2xl font-extrabold uppercase">Todavía no tienes pedidos</h2>
                <p class="mx-auto mt-3 max-w-lg text-sm leading-6 text-on-surface-variant">
                    Cuando realices tu primera compra en Fruta Madre, aquí podrás consultar el historial y el estado de tus pedidos.
                </p>
                <a href="index.php?accion=catalogo"
                   class="mt-6 inline-flex items-center gap-2 rounded-xl bg-primary px-6 py-3 font-extrabold text-white shadow-[3px_3px_0px_#201b0f]">
                    <span class="material-symbols-outlined">storefront</span>
                    Explorar catálogo
                </a>
            </section>

        <?php else: ?>

        <div class="grid grid-cols-1 items-start gap-7 lg:grid-cols-12">

            <!-- COLUMNA IZQUIERDA -->
            <section class="flex flex-col gap-4 lg:col-span-5" id="orders-list-column">

                <div class="flex items-center justify-between px-1">
                    <span class="display text-sm font-extrabold uppercase tracking-wider">
                        Historial de encargos
                    </span>
                    <span class="text-xs font-bold text-on-surface-variant">
                        <?= $totalPedidos ?> PEDIDO<?= $totalPedidos === 1 ? '' : 'S' ?>
                    </span>
                </div>

                <?php foreach ($pedidos as $pedido): ?>
                    <?php
                    $id = (int) $pedido['id_pedido'];
                    $numeroPedido = $id;
                    $estado = $estadoInfo($pedido['estado_pedido'] ?? '');
$pagoAprobado = in_array(
    strtolower(trim((string) ($pedido['estado_pago'] ?? ''))),
    ['aprobado', 'aprobado_prueba', 'approved', 'accredited'],
    true
);
                    $esSeleccionado = $pedidoSeleccionado &&
                        (int) $pedidoSeleccionado['id_pedido'] === $id;
                    $nombres = trim((string) ($pedido['nombres_productos'] ?? ''));
                    ?>

                    <article
                        class="order-card <?= $esSeleccionado ? 'selected' : '' ?> cursor-pointer overflow-hidden rounded-2xl bg-white p-4"
                        data-order-card
                        data-id="<?= $id ?>"
                        data-grupo="<?= $esc($estado['grupo']) ?>"
                        data-search="<?= $esc(mb_strtolower(
                            '#' . $numeroPedido . ' ' . $nombres,
                            'UTF-8'
                        )) ?>"
                        onclick="seleccionarPedido(<?= $id ?>)"
                    >
                        <div class="flex flex-col gap-3">
    <?php if ($pagoAprobado): ?>
        <span class="inline-flex w-fit items-center gap-1 rounded-full bg-green-100 px-3 py-1 text-xs font-extrabold text-green-800">
            <span class="material-symbols-outlined text-sm">verified</span>
            Pago aprobado
        </span>
    <?php endif; ?>

                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <span class="display text-xl font-extrabold">
                                    Pedido #<?= $numeroPedido ?>
                                </span>

                                <span class="<?= $esc($estado['clase']) ?> flex items-center gap-1 rounded-full px-3 py-1 text-xs font-extrabold">
                                    <span class="material-symbols-outlined text-sm">
                                        <?= $esc($estado['icono']) ?>
                                    </span>
                                    <?= $esc($estado['texto']) ?>
                                </span>
                            </div>

                            <p class="text-xs text-on-surface-variant">
                                <?= $esc($fecha($pedido['fecha_pedido'] ?? null)) ?>
                            </p>

                            <div class="rounded-xl bg-surface-container-low p-3">
                                <p class="mb-1 text-xs font-extrabold">
                                    <?= (int) ($pedido['cantidad_productos'] ?? 0) ?>
                                    producto(s)
                                </p>
                                <p class="text-sm leading-5 text-on-surface-variant">
                                    <?= $esc($nombres !== '' ? $nombres : 'Detalle de productos disponible en el pedido') ?>
                                </p>
                            </div>

                            <div class="flex items-end justify-between gap-3 border-t-2 border-dashed border-on-surface/10 pt-3">
                                <div>
                                    <span class="block text-xs text-on-surface-variant">Total del pedido</span>
                                    <strong class="display text-lg font-extrabold text-secondary">
                                        <?= $dinero($pedido['total'] ?? 0) ?>
                                    </strong>
                                </div>

                                <a href="index.php?accion=mis-pedidos&pedido=<?= $id ?>"
                                   class="inline-flex items-center gap-1 rounded-lg bg-primary px-3 py-2 text-xs font-extrabold text-white shadow-[2px_2px_0px_#201b0f]"
                                   onclick="event.stopPropagation()">
                                    Ver detalle
                                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>

                <p id="sin-resultados" class="hidden rounded-xl bg-surface-container-low p-5 text-sm text-on-surface-variant">
                    No hay pedidos que coincidan con ese filtro o búsqueda.
                </p>
            </section>

            <!-- COLUMNA DERECHA: DETALLE -->
            <section class="flex flex-col gap-5 lg:col-span-7" id="order-detail-column">

                <?php if ($pedidoSeleccionado): ?>
                    <?php
                    $pedido = $pedidoSeleccionado;
                    $idPedido = (int) $pedido['id_pedido'];
                    $numeroPedidoDetalle = $idPedido;
                    $estadoActual = strtolower(trim((string) ($pedido['estado_pedido'] ?? 'pendiente')));
                    $infoEstado = $estadoInfo($estadoActual);
                    $metodo = $metodosPago[$pedido['metodo_pago'] ?? ''] ?? 'No especificado';
                    $estadoPago = strtolower(trim((string) ($pedido['estado_pago'] ?? 'pendiente')));

                    $paso = 1;

                    if (in_array($estadoActual, ['en_preparacion', 'en preparación', 'preparando'], true)) {
                        $paso = 2;
                    } elseif (in_array($estadoActual, ['en_camino', 'en camino', 'en_reparto'], true)) {
                        $paso = 4;
                    } elseif (in_array($estadoActual, ['entregado', 'completado', 'finalizado'], true)) {
                        $paso = 5;
                    } elseif (in_array($estadoActual, ['cancelado', 'cancelada'], true)) {
                        $paso = 0;
                    }
                    ?>

                    <div class="overflow-hidden rounded-2xl border border-outline-variant bg-white shadow-[4px_4px_0px_#201b0f]">

                        <div class="flex h-2">
                            <div class="flex-1 bg-primary"></div>
                            <div class="flex-1 bg-secondary"></div>
                            <div class="flex-1 bg-tertiary-container"></div>
                            <div class="flex-1 bg-primary-container"></div>
                        </div>

                        <div class="p-5 md:p-6">

                            <div class="flex flex-wrap items-start justify-between gap-4">
                                <div>
                                    <p class="mb-2 text-xs font-extrabold uppercase tracking-widest text-primary">
                                        Detalle del encargo
                                    </p>
                                    <h2 class="display text-3xl font-extrabold uppercase">
                                        Pedido #<?= $numeroPedidoDetalle ?>
                                    </h2>
                                    <p class="mt-2 text-sm text-on-surface-variant">
                                        <?= $esc($fecha($pedido['fecha_pedido'] ?? null)) ?>
                                    </p>
                                </div>

                                <span class="<?= $esc($infoEstado['clase']) ?> rounded-full px-3 py-2 text-xs font-extrabold">
                                    <?= $esc($infoEstado['texto']) ?>
                                </span>
                            </div>

                            <!-- PROGRESO -->
                            <?php if ($paso > 0): ?>
                                <div class="mt-7 rounded-xl bg-surface-container-low p-4">
                                    <div class="mb-5 flex items-center justify-between gap-3">
                                        <h3 class="display text-sm font-extrabold uppercase">Estado de tu pedido</h3>
                                        <span class="text-xs font-bold text-primary"><?= $paso ?>/5 etapas</span>
                                    </div>

                                    <div class="mb-5 h-2 overflow-hidden rounded-full bg-surface-container-highest">
                                        <div class="h-full rounded-full bg-primary transition-all"
                                             style="width: <?= ($paso / 5) * 100 ?>%"></div>
                                    </div>

                                    <div class="grid grid-cols-5 gap-1 text-center">
                                        <?php
                                        $etapas = [
                                            ['Recibido', 'receipt_long'],
                                            ['Preparación', 'skillet'],
                                            ['Empacado', 'inventory_2'],
                                            ['En camino', 'delivery_dining'],
                                            ['Entregado', 'check_circle']
                                        ];
                                        ?>

                                        <?php foreach ($etapas as $indice => $etapa): ?>
                                            <?php $numeroEtapa = $indice + 1; ?>
                                            <div class="flex flex-col items-center gap-2">
                                                <span class="material-symbols-outlined flex h-8 w-8 items-center justify-center rounded-full <?= $numeroEtapa <= $paso ? 'bg-primary text-white' : 'bg-surface-container-highest text-on-surface-variant' ?>">
                                                    <?= $esc($etapa[1]) ?>
                                                </span>
                                                <span class="text-[10px] leading-4 <?= $numeroEtapa <= $paso ? 'font-extrabold text-primary' : 'text-on-surface-variant' ?>">
                                                    <?= $esc($etapa[0]) ?>
                                                </span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <p class="mt-5 text-xs leading-5 text-on-surface-variant">
                                        El seguimiento muestra el estado registrado del pedido. Las etapas intermedias dependen de las actualizaciones que realice el negocio.
                                    </p>
                                </div>
                            <?php else: ?>
                                <div class="mt-6 rounded-xl bg-error-container p-4 text-sm font-bold text-on-error-container">
                                    Este pedido está cancelado.
                                </div>
                            <?php endif; ?>



<!-- MAPA Y SEGUIMIENTO DEL PEDIDO -->
<?php
$direccionCompleta = trim(implode(', ', array_filter([
    $pedido['calle_numero'] ?? '',
    $pedido['colonia'] ?? '',
    $pedido['ciudad'] ?? '',
    $pedido['codigo_postal'] ?? ''
], static fn($valor) => trim((string) $valor) !== '')));

$hayDireccion = $direccionCompleta !== '';
$productosPedido = is_array($pedido['productos'] ?? null)
    ? $pedido['productos']
    : [];

$totalUnidades = (int) ($pedido['cantidad_productos'] ?? 0);
?>


<!-- MAPA Y SEGUIMIENTO: DISEÑO FRUTA MADRE -->
<section class="mt-7 flex flex-col gap-4">

    <!-- TARJETA PRINCIPAL DEL MAPA -->
    <div class="overflow-hidden rounded-2xl border border-outline-variant bg-white shadow-[3px_3px_0px_#201b0f]">

        <!-- CABECERA -->
        <div class="flex flex-wrap items-center justify-between gap-3 p-5">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-widest text-primary">
                    Puesto digital No. 01
                </p>
                <h3 class="display mt-1 text-2xl font-extrabold uppercase">
                    Sigue tu antojo
                </h3>
                <p class="mt-1 text-sm text-on-surface-variant">
                    Pedido #<?= $numeroPedidoDetalle ?>
                </p>
            </div>

            <span class="<?= $esc($infoEstado['clase']) ?> rounded-full px-4 py-2 text-xs font-extrabold">
                <?= $esc($infoEstado['texto']) ?>
            </span>
        </div>

        <!-- MAPA CON INDICADORES SUPERPUESTOS -->
        <div class="relative overflow-hidden bg-surface-container-high">

            <?php if ($hayDireccion): ?>
                <iframe
                    title="Mapa de entrega del pedido #<?= $numeroPedidoDetalle ?>"
                    src="https://maps.google.com/maps?q=<?= rawurlencode($direccionCompleta) ?>&output=embed"
                    class="block h-64 w-full border-0 md:h-72"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    allowfullscreen>
                </iframe>

                <!-- INDICADOR DEL NEGOCIO -->
                <div class="absolute left-4 top-4 flex items-center gap-2 rounded-xl bg-primary px-3 py-2 text-white shadow-[3px_3px_0px_#201b0f]">
                    <span class="material-symbols-outlined text-lg">nutrition</span>
                    <span class="text-[10px] font-extrabold uppercase sm:text-xs">
                        Puesto Fruta Madre
                    </span>
                </div>

                <!-- INDICADOR DEL DESTINO -->
                <div class="absolute bottom-4 left-4 flex max-w-[75%] items-center gap-2 rounded-xl bg-white px-3 py-2 shadow-[3px_3px_0px_#201b0f]">
                    <span class="material-symbols-outlined text-xl text-secondary">location_on</span>
                    <div class="min-w-0">
                        <p class="text-[10px] font-extrabold uppercase">Tu destino</p>
                        <p class="truncate text-xs text-on-surface-variant">
                            <?= $esc($direccionCompleta) ?>
                        </p>
                    </div>
                </div>

                <a
                    href="https://www.google.com/maps/search/?api=1&query=<?= rawurlencode($direccionCompleta) ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="absolute bottom-4 right-4 inline-flex items-center gap-2 rounded-xl bg-primary px-3 py-3 text-xs font-extrabold text-white shadow-[3px_3px_0px_#201b0f] transition hover:bg-primary-container sm:px-4">
                    <span class="material-symbols-outlined text-base">map</span>
                    <span class="hidden sm:inline">Abrir en Google Maps</span>
                    <span class="sm:hidden">Abrir mapa</span>
                </a>

            <?php else: ?>
                <div class="flex min-h-64 flex-col items-center justify-center p-8 text-center">
                    <span class="material-symbols-outlined text-5xl text-primary">location_on</span>
                    <h4 class="display mt-3 text-lg font-extrabold uppercase">
                        Sin entrega a domicilio registrada
                    </h4>
                    <p class="mt-2 max-w-md text-sm leading-6 text-on-surface-variant">
                        Este pedido no tiene una entrega a domicilio registrada. Si elegiste recoger en tienda, no necesitas proporcionar una dirección.
                    </p>
                </div>
            <?php endif; ?>

        </div>

        <!-- BARRA INFERIOR DEL MAPA -->
        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-on-surface/10 bg-surface-container p-4">

            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-container text-white shadow-[2px_2px_0px_#201b0f]">
                    <span class="material-symbols-outlined text-xl">delivery_dining</span>
                </div>

                <div>
                    <p class="text-sm font-extrabold">
                        Seguimiento de tu pedido
                    </p>
                    <p class="text-xs leading-5 text-on-surface-variant">
                        <?= !empty($pedido['notas_repartidor'])
                            ? $esc($pedido['notas_repartidor'])
                            : 'Consulta aquí la información de entrega registrada.' ?>
                    </p>
                </div>
            </div>

            <span class="<?= $esc($infoEstado['clase']) ?> rounded-full px-3 py-2 text-xs font-extrabold">
                <?= $esc($infoEstado['texto']) ?>
            </span>

        </div>
    </div>

    <!-- DIRECCIÓN Y VENTANA DE ENTREGA -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

        <article class="flex flex-col gap-1 rounded-2xl bg-surface-container-low p-4 shadow-[2px_2px_0px_#201b0f]">
            <span class="flex items-center gap-1 text-xs font-extrabold uppercase tracking-wide text-on-surface-variant">
                <span class="material-symbols-outlined text-lg text-secondary">pin_drop</span>
                Dirección de entrega
            </span>

            <p class="display mt-1 text-lg font-extrabold leading-snug">
                <?= $hayDireccion
                    ? $esc($pedido['calle_numero'] ?? 'Dirección registrada')
                    : 'Sin entrega domiciliaria' ?>
            </p>

            <p class="text-sm leading-5 text-on-surface-variant">
                <?= $esc(trim(implode(', ', array_filter([
                    $pedido['colonia'] ?? '',
                    $pedido['ciudad'] ?? '',
                    $pedido['codigo_postal'] ?? ''
                ], static fn($v) => trim((string)$v) !== '')))) ?>
            </p>

            <?php if (!empty($pedido['referencias'])): ?>
                <p class="mt-2 text-xs font-semibold leading-5 text-tertiary">
                    Instrucciones: <?= $esc($pedido['referencias']) ?>
                </p>
            <?php endif; ?>
        </article>

        <article class="flex flex-col gap-1 rounded-2xl bg-surface-container-low p-4 shadow-[2px_2px_0px_#201b0f]">
            <span class="flex items-center gap-1 text-xs font-extrabold uppercase tracking-wide text-on-surface-variant">
                <span class="material-symbols-outlined text-lg text-primary">schedule</span>
                Ventana de entrega
            </span>

            <?php if (!empty($pedido['fecha_estimada'])): ?>
                <p class="display mt-1 text-lg font-extrabold">
                    <?= $esc($fecha($pedido['fecha_estimada'])) ?>
                </p>
                <p class="text-sm text-on-surface-variant">
                    Fecha estimada registrada por el establecimiento.
                </p>
            <?php else: ?>
                <p class="display mt-1 text-lg font-extrabold">
                    Por confirmar
                </p>
                <p class="text-sm leading-5 text-on-surface-variant">
                    El horario está pendiente de confirmación. Si elegiste recoger en tienda, consulta el horario de recogida con el negocio.
                </p>
            <?php endif; ?>

            <div class="mt-2 inline-flex w-fit items-center gap-1.5 rounded-lg bg-surface-container-high px-2 py-1">
                <span class="material-symbols-outlined text-base text-primary">verified</span>
                <span class="text-xs font-bold">Estado actualizado del pedido</span>
            </div>
        </article>

    </div>

    <!-- CONTENIDO DE LA CANASTA -->
    <section class="flex flex-col gap-3 pt-2">

        <div class="flex flex-wrap items-center justify-between gap-3">
            <h3 class="display text-sm font-extrabold uppercase tracking-wider">
                Contenido de la canasta
            </h3>

            <span class="rounded-full bg-surface-container-high px-3 py-1.5 text-xs font-extrabold">
                <?= $totalUnidades ?> unidades
            </span>
        </div>

        <?php if (!empty($productosPedido)): ?>
            <?php foreach ($productosPedido as $producto): ?>
                <article class="flex items-center justify-between gap-3 rounded-2xl bg-surface-container-low p-3 shadow-[2px_2px_0px_#201b0f] sm:gap-4 sm:p-4">

                    <div class="flex min-w-0 items-center gap-3">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-surface-container-high shadow-[2px_2px_0px_#201b0f] sm:h-16 sm:w-16">

                            <?php if (!empty($producto['imagen'])): ?>
                                <img
                                    src="<?= $esc($producto['imagen']) ?>"
                                    alt="<?= $esc($producto['nombre'] ?? 'Producto') ?>"
                                    class="h-full w-full object-cover"
                                    loading="lazy">
                            <?php else: ?>
                                <span class="material-symbols-outlined text-3xl text-primary">nutrition</span>
                            <?php endif; ?>

                        </div>

                        <div class="min-w-0">
                            <h4 class="text-sm font-extrabold leading-5 sm:text-base">
                                <?= $esc($producto['nombre'] ?? 'Producto') ?>
                            </h4>

                            <p class="mt-1 text-xs text-on-surface-variant">
                                Cantidad: <?= (int)($producto['cantidad'] ?? 0) ?>
                            </p>

                            <p class="mt-1 text-xs font-semibold text-secondary">
                                <?= $dinero($producto['precio_unitario'] ?? 0) ?> por unidad
                            </p>
                        </div>
                    </div>

                    <strong class="shrink-0 text-sm font-extrabold sm:text-base">
                        <?= $dinero($producto['subtotal'] ?? 0) ?>
                    </strong>

                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="rounded-xl bg-surface-container-low p-4 text-sm text-on-surface-variant">
                No hay detalles de productos disponibles para mostrar.
            </p>
        <?php endif; ?>

    </section>

    <!-- INFORMACIÓN COMPLEMENTARIA -->
    <section class="grid grid-cols-1 gap-3 pt-2 sm:grid-cols-2">

        <article class="rounded-2xl bg-surface-container-low p-4">
            <span class="material-symbols-outlined text-2xl text-primary">payments</span>
            <p class="mt-2 text-xs text-on-surface-variant">Método de pago</p>
            <strong class="mt-1 block text-sm"><?= $esc($metodo) ?></strong>
        </article>

        <article class="rounded-2xl bg-surface-container-low p-4">
            <span class="material-symbols-outlined text-2xl text-primary">receipt_long</span>
            <p class="mt-2 text-xs text-on-surface-variant">Estado del pago</p>
            <strong class="mt-1 block text-sm">
                <?= $esc(ucfirst(str_replace('_', ' ', $estadoPago))) ?>
            </strong>
        </article>

        <article class="rounded-2xl bg-surface-container-low p-4">
            <span class="material-symbols-outlined text-2xl text-primary">inventory_2</span>
            <p class="mt-2 text-xs text-on-surface-variant">Cantidad de productos</p>
            <strong class="mt-1 block text-sm">
                <?= $totalUnidades ?> producto(s)
            </strong>
        </article>

        <article class="rounded-2xl bg-surface-container-low p-4">
            <span class="material-symbols-outlined text-2xl text-primary">calendar_month</span>
            <p class="mt-2 text-xs text-on-surface-variant">Fecha de registro</p>
            <strong class="mt-1 block text-sm">
                <?= $esc($fecha($pedido['fecha_pedido'] ?? null)) ?>
            </strong>
        </article>

    </section>

    <!-- RESUMEN ECONÓMICO -->
    <section class="mt-1 rounded-2xl bg-surface-container p-5 shadow-[3px_3px_0px_#201b0f] sm:p-6">

        <h3 class="display mb-4 text-lg font-extrabold uppercase">
            Resumen de compra
        </h3>

        <div class="flex flex-col gap-2">
            <?php if (!empty($productosPedido)): ?>
                <?php foreach ($productosPedido as $producto): ?>
                    <div class="flex items-start justify-between gap-3 text-sm">
                        <div class="min-w-0">
                            <p class="font-bold">
                                <?= $esc($producto['nombre'] ?? 'Producto') ?>
                            </p>
                            <p class="text-xs text-on-surface-variant">
                                <?= (int)($producto['cantidad'] ?? 0) ?> unidad(es)
                            </p>
                        </div>

                        <span class="shrink-0 font-semibold">
                            <?= $dinero($producto['subtotal'] ?? 0) ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-sm text-on-surface-variant">
                    <?= $esc($pedido['nombres_productos'] ?: 'Productos del pedido') ?>
                </p>
            <?php endif; ?>
        </div>

        <div class="mt-4 border-t-2 border-dashed border-on-surface/20 pt-4">
            <div class="flex items-center justify-between gap-3 text-sm text-on-surface-variant">
                <span>Subtotal</span>
                <strong class="text-on-surface">
                    <?= $dinero($pedido['subtotal'] ?? 0) ?>
                </strong>
            </div>

            <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h4 class="display text-lg font-black uppercase leading-tight">
                        Total del pedido
                    </h4>
                    <p class="mt-1 text-xs text-on-surface-variant">
                        <?= $esc($metodo) ?> ·
                        <?= $esc(ucfirst(str_replace('_', ' ', $estadoPago))) ?>
                    </p>
                </div>

                <strong class="display text-2xl font-black text-secondary sm:text-3xl">
                    <?= $dinero($pedido['total'] ?? 0) ?>
                </strong>
            </div>
        </div>

    </section>

</section>


                            <!-- ACCIONES DE PEDIDO -->
                            <?php if ($estadoActual === 'pendiente'): ?>
                                <div class="mt-4 flex flex-wrap gap-3">
                                    <?php if (($pedido['metodo_pago'] ?? '') === 'mercadopago_prueba' && $estadoPago === 'pendiente'): ?>
                                    <form method="post" action="index.php?accion=reintentar-pago-mp-prueba">
                                        <input type="hidden" name="id_pedido" value="<?= $idPedido ?>">
                                        <input type="hidden" name="token" value="<?= $esc($_SESSION['csrf_pedidos']) ?>">
                                        <button type="submit" class="rounded-xl bg-primary px-5 py-3 text-sm font-extrabold text-white">Reintentar pago</button>
                                    </form>
                                    <?php endif; ?>
                                    <?php if ($estadoPago === 'pendiente'): ?>
                                    <form method="post" action="index.php?accion=cancelar-mi-pedido" onsubmit="return confirm('¿Cancelar el pedido #<?= $idPedido ?>?');">
                                        <input type="hidden" name="id_pedido" value="<?= $idPedido ?>">
                                        <input type="hidden" name="token" value="<?= $esc($_SESSION['csrf_pedidos']) ?>">
                                        <button type="submit" class="rounded-xl border-2 border-red-700 px-5 py-3 text-sm font-extrabold text-red-700">Cancelar pedido</button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <!-- ACCIONES -->
                            <div class="mt-6 flex flex-wrap gap-3">
                                <a href="index.php?accion=catalogo"
                                   class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-extrabold text-white shadow-[3px_3px_0px_#201b0f]">
                                    <span class="material-symbols-outlined">storefront</span>
                                    Seguir comprando
                                </a>

                                <a href="index.php?accion=mis-pedidos"
                                   class="inline-flex items-center gap-2 rounded-xl bg-surface-container px-5 py-3 text-sm font-extrabold shadow-[2px_2px_0px_#201b0f]">
                                    <span class="material-symbols-outlined">history</span>
                                    Ver historial
                                </a>
                            </div>

                            <p class="mt-5 text-xs leading-5 text-on-surface-variant">
                                Si necesitas modificar o cancelar este pedido, comunícate con el establecimiento.
                                La cancelación está disponible únicamente antes de confirmar el pago y comenzar la preparación.
                            </p>
                        </div>
                    </div>

                <?php endif; ?>

            </section>
        </div>
        <?php endif; ?>
    </div>
</main>

<footer class="mt-8 bg-surface-container-low">
    <div class="flex h-1.5">
        <div class="flex-1 bg-tertiary-container"></div>
        <div class="flex-1 bg-surface-container-high"></div>
        <div class="flex-1 bg-secondary-container"></div>
        <div class="flex-1 bg-primary-container"></div>
    </div>

    <div class="mx-auto grid max-w-7xl grid-cols-1 gap-8 px-5 py-10 md:grid-cols-2 lg:grid-cols-5 lg:px-12">

        <div class="lg:col-span-2">
            <h2 class="display text-xl font-extrabold text-primary">FRUTA MADRE</h2>
            <p class="mt-2 text-sm font-extrabold uppercase tracking-wide text-secondary">
                Te damos hasta para llevar.
            </p>
            <p class="mt-3 max-w-md text-sm leading-6 text-on-surface-variant">
                El auténtico sabor del mercado urbano mexicano en tu puerta:
                fruta fresca con chilito y limón, botanas preparadas, repostería
                artesanal, flores de temporada y carritos de antojos para tus mejores eventos.
            </p>
            <span class="mt-4 inline-block rounded-full bg-surface-container px-3 py-2 text-xs font-bold text-tertiary">
                🇲🇽 Hecho con mucho chamoy y amor en México
            </span>
        </div>

        <div>
            <h3 class="display mb-3 text-sm font-extrabold uppercase">Explorar</h3>
            <ul class="space-y-2 text-sm text-on-surface-variant">
                <li><a href="index.php" class="hover:text-primary">Inicio</a></li>
                <li><a href="index.php?accion=catalogo" class="hover:text-primary">Catálogo completo</a></li>
                <li><a href="index.php?accion=carrito" class="hover:text-primary">Mi carrito</a></li>
                <li><a href="index.php?accion=mis-pedidos" class="hover:text-primary">Mis pedidos</a></li>
            </ul>
        </div>

        <div>
            <h3 class="display mb-3 text-sm font-extrabold uppercase">Atención</h3>
            <ul class="space-y-2 text-sm text-on-surface-variant">
                <li>Contacto directo</li>
                <li>Preguntas frecuentes</li>
                <li>Términos y condiciones</li>
                <li>Aviso de privacidad</li>
            </ul>
        </div>

        <div>
            <h3 class="display mb-3 text-sm font-extrabold uppercase">Mercado social</h3>
            <p class="mb-3 text-sm text-on-surface-variant">¡Échanos un grito o síguenos la pista!</p>
            <div class="flex flex-wrap gap-2 text-xs">
                <span class="rounded-lg bg-surface-container px-3 py-2">Instagram</span>
                <span class="rounded-lg bg-surface-container px-3 py-2">TikTok</span>
                <span class="rounded-lg bg-surface-container px-3 py-2">WhatsApp</span>
                <span class="rounded-lg bg-surface-container px-3 py-2">Facebook</span>
            </div>
        </div>
    </div>

    <div class="flex flex-col justify-between gap-2 bg-surface-container px-5 py-4 text-center text-xs text-on-surface-variant sm:flex-row sm:text-left">
        <p>© <?= date('Y') ?> FRUTA MADRE. Todos los derechos reservados.</p>
        <p class="display uppercase">Puesto Digital No. 01</p>
    </div>
</footer>

<script>
let filtroActual = 'todos';

function filtrarPedidos(filtro, boton) {
    filtroActual = filtro;

    document.querySelectorAll('.filter-btn').forEach(function (btn) {
        btn.classList.remove('active');
    });

    boton.classList.add('active');
    aplicarFiltros();
}

function buscarPedidos(texto) {
    aplicarFiltros(texto.toLowerCase().trim());
}

function aplicarFiltros(texto = document.getElementById('buscar-pedido')?.value.toLowerCase().trim() || '') {
    const tarjetas = document.querySelectorAll('[data-order-card]');
    let visibles = 0;

    tarjetas.forEach(function (tarjeta) {
        const grupo = tarjeta.dataset.grupo;
        const busqueda = tarjeta.dataset.search || '';
        const coincideFiltro = filtroActual === 'todos' || grupo === filtroActual;
        const coincideTexto = busqueda.includes(texto);
        const mostrar = coincideFiltro && coincideTexto;

        tarjeta.classList.toggle('hidden-order', !mostrar);

        if (mostrar) visibles++;
    });

    document.getElementById('sin-resultados')?.classList.toggle('hidden', visibles > 0);
}

function seleccionarPedido(id) {
    window.location.href = 'index.php?accion=mis-pedidos&pedido=' + encodeURIComponent(id);
}
</script>

</body>
</html>