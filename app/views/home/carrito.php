<?php
$total = (float) ($total ?? 0);
$productosCarrito = $productosCarrito ?? [];
$datosPedido = $_SESSION['datos_pedido'] ?? [];
$erroresPedido = $_SESSION['errores_pedido'] ?? [];

$cantidadProductos = 0;

foreach ($productosCarrito as $producto) {
    $cantidadProductos += (int) ($producto['cantidad'] ?? 0);
}

$esc = static function ($valor): string {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
};

$logueado = !empty($_SESSION['usuario']['id_usuario']);
$servicioActual = $datosPedido['tipo_servicio'] ?? 'domicilio';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FRUTA MADRE - Carrito y Datos del Pedido</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Syne:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        :root {
            --cream: #fff8f0;
            --paper: #fdf2de;
            --soft: #f8edd8;
            --ink: #201b0f;
            --muted: #41493f;
            --green: #2f612f;
            --green-light: #477a45;
            --pink: #ac2d50;
            --line: #c1c9bc;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: var(--cream);
            color: var(--ink);
            font-family: 'DM Sans', sans-serif;
        }

        h1, h2, h3, .brand, .rotulo {
            font-family: 'Syne', sans-serif;
        }

        a, button { transition: all .2s ease; }

        .market-shadow {
            box-shadow: 3px 3px 0 var(--ink);
        }

        .section-card {
            background: white;
            border: 1px solid rgba(193,201,188,.8);
            border-radius: 18px;
            padding: 24px;
            box-shadow: 3px 3px 0 var(--ink);
        }

        .section-heading {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .section-number {
            display: flex;
            flex-shrink: 0;
            width: 34px;
            height: 34px;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: white;
            background: var(--green);
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            box-shadow: 2px 2px 0 var(--ink);
        }

        .field {
            display: block;
            width: 100%;
            padding: 12px 14px;
            border: 1px solid var(--line);
            border-radius: 11px;
            background: var(--soft);
            outline: none;
            font: inherit;
            color: var(--ink);
        }

        .field:focus {
            border-color: var(--green-light);
            background: white;
            box-shadow: 0 0 0 3px #477a4520;
        }

        .field-label {
            display: block;
            margin-bottom: 7px;
            font-size: 13px;
            font-weight: 800;
        }

        .service-option {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 16px;
            padding: 18px;
            border: 1px solid var(--line);
            border-radius: 16px;
            background: white;
            box-shadow: 3px 3px 0 var(--ink);
            cursor: pointer;
        }

        .service-option:has(input:checked) {
            border: 2px solid var(--green);
            background: #f3f8ee;
            box-shadow: 4px 4px 0 var(--green);
        }

        .service-option input { accent-color: var(--green); }

        .qty-button {
            display: flex;
            width: 32px;
            height: 32px;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: white;
            font-weight: 900;
        }

        .qty-button:hover { background: var(--soft); }

        .date-choice, .time-choice {
            cursor: pointer;
            border: 1px solid var(--line);
            border-radius: 11px;
            background: var(--soft);
            padding: 12px;
            text-align: center;
        }

        .date-choice:has(input:checked),
        .time-choice:has(input:checked) {
            border-color: var(--green);
            background: var(--green);
            color: white;
            box-shadow: 2px 2px 0 var(--ink);
        }

        .date-choice input, .time-choice input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .ticket-divider {
            border-top: 2px dashed var(--line);
        }

        [hidden] { display: none !important; }

        @media (max-width: 640px) {
            .section-card { padding: 18px; }
            .hero-title { font-size: 39px; line-height: 1.05; }
        }
    </style>
</head>

<body>

<!-- CENEFA SUPERIOR -->
<div class="bg-[#2f612f] px-4 py-2 text-center text-xs font-extrabold uppercase tracking-widest text-white">
    Entregas exprés · Fruta fresca del día · Chamoy artesanal
</div>

<!-- HEADER -->
<header class="sticky top-0 z-30 border-b border-[#ece1cd] bg-[#fff8f0]/95 shadow-sm backdrop-blur">
    <div class="mx-auto flex max-w-[1440px] items-center justify-between gap-4 px-5 py-4 lg:px-10">
        <a href="index.php" class="shrink-0">
            <span class="brand block text-xl font-extrabold tracking-wider">FRUTA MADRE</span>
            <span class="block text-xs font-extrabold uppercase text-[#ac2d50]">
                Te damos hasta para llevar
            </span>
        </a>

        <nav class="hidden items-center gap-2 lg:flex">
            <a href="index.php" class="rounded-lg px-4 py-2 hover:bg-[#f8edd8]">Inicio</a>
            <a href="index.php#catalogo" class="rounded-lg px-4 py-2 hover:bg-[#f8edd8]">Catálogo</a>
            <a href="index.php#eventos" class="rounded-lg px-4 py-2 hover:bg-[#f8edd8]">Eventos</a>
        </nav>

        <a href="index.php?accion=carrito"
           class="flex items-center gap-2 rounded-xl bg-[#f8edd8] px-4 py-3 font-bold market-shadow">
            <span class="material-symbols-outlined">shopping_bag</span>
            <span class="hidden sm:inline">Mi carrito</span>
            <span class="flex h-6 min-w-6 items-center justify-center rounded-full bg-[#ac2d50] px-1 text-xs text-white">
                <?= $cantidadProductos ?>
            </span>
        </a>
    </div>

    <div class="flex h-1.5">
        <div class="flex-1 bg-[#477a45]"></div>
        <div class="flex-1 bg-[#fc6a8a]"></div>
        <div class="flex-1 bg-[#f2e7d2]"></div>
        <div class="flex-1 bg-[#b55107]"></div>
    </div>
</header>

<main class="mx-auto max-w-[1440px] px-5 py-8 lg:px-10 lg:py-10">

    <!-- MIGAS DE PAN -->
    <div class="mb-7 flex flex-wrap items-center justify-between gap-3">
        <nav class="flex items-center gap-2 text-sm text-[#41493f]">
            <a href="index.php" class="hover:text-[#2f612f]">Inicio</a>
            <span class="material-symbols-outlined text-base">chevron_right</span>
            <span class="font-bold text-[#ac2d50]">Carrito y datos del pedido</span>
        </nav>

        <?php if ($logueado): ?>
            <span class="flex items-center gap-1 rounded-full bg-[#e5f2df] px-3 py-2 text-xs font-bold text-[#2f612f]">
                <span class="material-symbols-outlined text-base">verified_user</span>
                Sesión iniciada
            </span>
        <?php else: ?>
            <a href="index.php?accion=login" class="text-sm font-bold text-[#2f612f] underline">
                Inicia sesión para comprar
            </a>
        <?php endif; ?>
    </div>

    <!-- PROGRESO -->
    <section class="mb-9 rounded-2xl border border-[#c1c9bc] bg-[#fdf2de] p-4 shadow-[3px_3px_0px_#201b0f]">
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            <?php
            $pasos = [
                ['1', 'Carrito', 'Productos'],
                ['2', 'Datos', 'Entrega y contacto'],
                ['3', 'Pago', 'Método de pago'],
                ['4', 'Confirmación', 'Pedido registrado']
            ];
            foreach ($pasos as $i => $paso):
            ?>
                <div class="flex items-center gap-2 <?= $i > 1 ? 'opacity-55' : '' ?>">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full
                        <?= $i === 0 ? 'bg-[#2f612f] text-white' : ($i === 1 ? 'bg-[#ac2d50] text-white' : 'bg-[#ece1cd]') ?>">
                        <?php if ($i === 0): ?>
                            <span class="material-symbols-outlined">check</span>
                        <?php else: ?>
                            <?= $paso[0] ?>
                        <?php endif; ?>
                    </span>
                    <div>
                        <p class="text-xs font-extrabold uppercase"><?= $esc($paso[1]) ?></p>
                        <p class="text-xs"><?= $esc($paso[2]) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- HERO -->
    <div class="mb-9">
        <span class="mb-3 inline-flex items-center gap-2 rounded-full bg-[#ffdbca] px-3 py-2 text-xs font-extrabold uppercase tracking-wider text-[#903e00]">
            <span class="material-symbols-outlined text-base">storefront</span>
            Puesto digital · Pedido en preparación
        </span>

        <h1 class="hero-title mb-3 text-5xl font-extrabold uppercase tracking-tight">
            Tu pedido, marchante
        </h1>

        <p class="max-w-2xl text-[#41493f]">
            Revisa tus antojos, dinos cómo los quieres y déjanos los datos para
            preparar todo fresco y con harto sabor.
        </p>
    </div>

    <?php if (!empty($erroresPedido)): ?>
        <div class="mb-7 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            <p class="mb-2 font-extrabold">Revisa los siguientes datos:</p>
            <ul class="list-inside list-disc space-y-1">
                <?php foreach ($erroresPedido as $error): ?>
                    <li><?= $esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php unset($_SESSION['errores_pedido']); ?>
    <?php endif; ?>

    <?php if (empty($productosCarrito)): ?>

        <section class="rounded-2xl border border-[#c1c9bc] bg-white px-6 py-16 text-center shadow-[4px_4px_0px_#201b0f]">
            <div class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-full bg-[#ffd9de] text-[#ac2d50]">
                <span class="material-symbols-outlined text-5xl">shopping_basket</span>
            </div>

            <h2 class="mb-3 text-2xl font-extrabold uppercase">
                ¿Tu carrito anda más vacío que refri de estudiante?
            </h2>

            <p class="mx-auto mb-7 max-w-md text-[#41493f]">
                No te quedes con el antojo. Regresa al catálogo y elige algo rico.
            </p>

            <a href="index.php"
               class="inline-flex items-center gap-2 rounded-xl bg-[#2f612f] px-6 py-4 font-bold text-white market-shadow hover:bg-[#477a45]">
                <span class="material-symbols-outlined">storefront</span>
                Explorar el catálogo
            </a>
        </section>

    <?php else: ?>

        <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">

            <!-- COLUMNA PRINCIPAL -->
            <div class="flex flex-col gap-7 lg:col-span-7 xl:col-span-8">

                <!-- 1. PRODUCTOS -->
                <section class="flex flex-col gap-4">
                    <div class="section-heading">
                        <span class="section-number">1</span>
                        <div>
                            <h2 class="text-2xl font-black uppercase tracking-tight">Lo que llevas</h2>
                            <p class="text-sm text-[#41493f]">
                                Tus antojitos seleccionados para este pedido.
                            </p>
                        </div>
                    </div>

                    <?php foreach ($productosCarrito as $producto): ?>
                        <?php
                        $idProducto = (int) ($producto['id_producto'] ?? 0);
                        $cantidad = (int) ($producto['cantidad'] ?? 1);
                        $precio = (float) ($producto['precio'] ?? 0);
                        $subtotalProducto = (float) ($producto['subtotal'] ?? ($precio * $cantidad));
                        $stock = (int) ($producto['stock_disponible'] ?? $cantidad);
                        ?>

                        <article class="flex flex-col gap-4 rounded-2xl border border-[#c1c9bc]/70 bg-white p-4 shadow-[3px_3px_0px_#201b0f] sm:flex-row sm:items-center sm:justify-between">

                            <div class="flex min-w-0 items-center gap-4">
                                <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-[#f8edd8] sm:h-24 sm:w-24">
                                    <?php if (!empty($producto['imagen_url'])): ?>
                                        <img src="<?= $esc($producto['imagen_url']) ?>"
                                             alt="<?= $esc($producto['nombre'] ?? 'Producto') ?>"
                                             class="h-full w-full object-cover">
                                    <?php else: ?>
                                        <span class="text-4xl">🍓</span>
                                    <?php endif; ?>
                                </div>

                                <div class="min-w-0">
                                    <span class="text-xs font-bold uppercase tracking-wider text-[#2f612f]">
                                        <?= $esc($producto['nombre_categoria'] ?? 'Antojito') ?>
                                    </span>
                                    <h3 class="font-bold">
                                        <?= $esc($producto['nombre'] ?? 'Producto') ?>
                                    </h3>
                                    <p class="mt-1 text-sm text-[#41493f]">
                                        $<?= number_format($precio, 2) ?> MXN por unidad
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center justify-between gap-3 sm:justify-end">

                                <!-- Controles de cantidad conectados al controlador mediante POST -->

<div class="inline-flex items-center gap-1 rounded-xl bg-[#f8edd8] p-1 shadow-[2px_2px_0px_#201b0f]">

    <form action="index.php?accion=actualizar-cantidad" method="POST">
        <input type="hidden" name="id_producto" value="<?= (int) $idProducto ?>">
        <input type="hidden" name="cantidad" value="<?= max(1, $cantidad - 1) ?>">

        <button type="submit"
                class="qty-button"
                aria-label="Disminuir cantidad"
                <?= $cantidad <= 1 ? 'disabled' : '' ?>>
            −
        </button>
    </form>

    <span class="w-7 text-center font-extrabold">
        <?= (int) $cantidad ?>
    </span>

    <form action="index.php?accion=actualizar-cantidad" method="POST">
        <input type="hidden" name="id_producto" value="<?= (int) $idProducto ?>">
        <input type="hidden" name="cantidad" value="<?= min((int) $stock, (int) $cantidad + 1) ?>">

        <button type="submit"
                class="qty-button"
                aria-label="Aumentar cantidad"
                <?= $cantidad >= $stock ? 'disabled' : '' ?>>
            +
        </button>
    </form>

</div>


                                <div class="min-w-[85px] text-right">
                                    <span class="block font-extrabold text-[#2f612f]"
                                          data-subtotal="<?= $idProducto ?>">
                                        $<?= number_format($subtotalProducto, 2) ?>
                                    </span>
                                    <span class="text-[11px] font-bold text-[#41493f]">MXN</span>
                                </div>

                                <form action="index.php?accion=eliminar" method="POST">
                                    <input type="hidden" name="id_producto" value="<?= $idProducto ?>">
                                    <button type="submit"
                                            class="rounded-xl p-2 text-[#ac2d50] hover:bg-[#ffd9de]"
                                            aria-label="Eliminar producto"
                                            title="Eliminar producto">
                                        <span class="material-symbols-outlined">delete_outline</span>
                                    </button>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; ?>

                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <a href="index.php"
                           class="inline-flex items-center gap-2 rounded-xl border-2 border-[#c1c9bc] bg-white px-5 py-3 font-bold hover:bg-[#f8edd8]">
                            <span class="material-symbols-outlined">arrow_back</span>
                            Seguir comprando
                        </a>

                        <form action="index.php?accion=vaciar"
                              method="POST"
                              onsubmit="return confirm('¿Seguro que deseas vaciar todo el carrito?');">
                            <button type="submit"
                                    class="inline-flex items-center gap-2 rounded-xl px-4 py-3 font-bold text-[#ac2d50] hover:bg-[#ffd9de]">
                                <span class="material-symbols-outlined">delete_sweep</span>
                                Vaciar carrito
                            </button>
                        </form>
                    </div>
                </section>

                <!-- FORMULARIO: las seis secciones pertenecen a un mismo formulario -->
                <form id="form-datos-pedido"
                      action="index.php?accion=guardar-datos"
                      method="POST"
                      class="flex flex-col gap-7">

                    <!-- 2. TIPO DE SERVICIO -->
                    <section class="flex flex-col gap-4">
                        <div class="section-heading">
                            <span class="section-number">2</span>
                            <div>
                                <h2 class="text-2xl font-black uppercase tracking-tight">¿Cómo lo quieres?</h2>
                                <p class="text-sm text-[#41493f]">
                                    Elige si pasas al puesto o necesitas entrega a domicilio.
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">

                            <label class="service-option">
                                <input class="sr-only" type="radio"
                                       name="tipo_servicio" value="recoger"
                                       <?= $servicioActual === 'recoger' ? 'checked' : '' ?> required>

                                <div class="flex items-start justify-between">
                                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#f8edd8]">
                                        <span class="material-symbols-outlined">storefront</span>
                                    </span>
                                    <span class="material-symbols-outlined text-[#2f612f]">check_circle</span>
                                </div>

                                <div>
                                    <span class="block font-extrabold uppercase">Paso por él</span>
                                    <span class="mt-1 block text-sm text-[#41493f]">
                                        Recoger en el establecimiento.
                                    </span>
                                    <span class="mt-3 inline-block rounded-md bg-[#e5f2df] px-2 py-1 text-xs font-bold text-[#2f612f]">
                                        Sin envío a domicilio
                                    </span>
                                </div>
                            </label>

                            <label class="service-option">
                                <input class="sr-only" type="radio"
                                       name="tipo_servicio" value="domicilio"
                                       <?= $servicioActual === 'domicilio' ? 'checked' : '' ?> required>

                                <div class="flex items-start justify-between">
                                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#2f612f] text-white">
                                        <span class="material-symbols-outlined">two_wheeler</span>
                                    </span>
                                    <span class="rounded-md bg-[#ac2d50] px-2 py-1 text-[10px] font-black uppercase text-white">
                                        A domicilio
                                    </span>
                                </div>

                                <div>
                                    <span class="block font-extrabold uppercase">Envío local</span>
                                    <span class="mt-1 block text-sm text-[#41493f]">
                                        Recibe tu pedido en la dirección que indiques.
                                    </span>
                                    <span class="mt-3 inline-block rounded-md bg-[#f8edd8] px-2 py-1 text-xs font-bold">
                                        Costo por confirmar
                                    </span>
                                </div>
                            </label>

                            <div class="flex flex-col justify-between gap-3 rounded-2xl border border-dashed border-[#c1c9bc] bg-[#fdf2de] p-4">
                                <span class="material-symbols-outlined text-3xl text-[#2f612f]">eco</span>
                                <div>
                                    <span class="block font-extrabold uppercase">Fresco al momento</span>
                                    <p class="mt-1 text-sm text-[#41493f]">
                                        Preparamos tu pedido para que llegue en buenas condiciones.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- 3. DATOS DEL CLIENTE -->
                    <section class="section-card flex flex-col gap-5">
                        <div class="section-heading">
                            <span class="section-number">3</span>
                            <div>
                                <h2 class="text-2xl font-black uppercase tracking-tight">Tus datos</h2>
                                <p class="text-sm text-[#41493f]">
                                    Para saber a quién entregarle el pedido y cómo contactarte.
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label class="field-label" for="nombre">Nombre completo *</label>
                                <input class="field" id="nombre" name="nombre"
                                       type="text" maxlength="120" required
                                       autocomplete="name"
                                       value="<?= $esc($datosPedido['nombre'] ?? ($_SESSION['usuario']['nombre_completo'] ?? '')) ?>"
                                       placeholder="Tu nombre completo">
                            </div>

                            <div>
                                <label class="field-label" for="telefono">Teléfono o WhatsApp *</label>
                                <input class="field" id="telefono" name="telefono"
                                       type="tel" maxlength="20" required
                                       autocomplete="tel"
                                       value="<?= $esc($datosPedido['telefono'] ?? '') ?>"
                                       placeholder="Ej. 9621234567">
                            </div>
                        </div>

                        <div>
                            <label class="field-label" for="correo">Correo electrónico</label>
                            <input class="field" id="correo" name="correo"
                                   type="email" autocomplete="email"
                                   value="<?= $esc($datosPedido['correo'] ?? ($_SESSION['usuario']['correo'] ?? '')) ?>"
                                   placeholder="tucorreo@ejemplo.com">
                            <p class="mt-2 text-xs text-[#72796e]">
                                Este campo se muestra en el diseño; su persistencia deberá integrarse con el controlador.
                            </p>
                        </div>

                        <?php if (!$logueado): ?>
                            <div class="rounded-xl border border-[#e8c5a8] bg-[#fff4e8] p-4 text-sm text-[#773200]">
                                <span class="font-extrabold">Importante:</span>
                                para guardar tus datos y continuar, primero
                                <a href="index.php?accion=login" class="font-extrabold underline">inicia sesión</a>.
                            </div>
                        <?php endif; ?>
                    </section>

                    <!-- 4. DIRECCIÓN -->
                    <section id="direccion-contenedor" class="section-card flex flex-col gap-5">
                        <div class="section-heading">
                            <span class="section-number">4</span>
                            <div>
                                <h2 class="text-2xl font-black uppercase tracking-tight">¿A dónde lo llevamos?</h2>
                                <p class="text-sm text-[#41493f]">
                                    Déjanos una dirección clara para encontrar tu puerta.
                                </p>
                            </div>
                        </div>

                        <div>
                            <label class="field-label" for="direccion">Calle, número, colonia y ciudad *</label>
                            <textarea class="field" id="direccion" name="direccion"
                                      rows="2" maxlength="500"
                                      placeholder="Calle, número exterior, colonia y ciudad"><?= $esc($datosPedido['direccion'] ?? '') ?></textarea>
                        </div>

                        <div>
                            <label class="field-label" for="referencias">Referencias para llegar</label>
                            <textarea class="field" id="referencias" name="referencias"
                                      rows="2" maxlength="500"
                                      placeholder="Color de fachada, entre calles u otras referencias"><?= $esc($datosPedido['referencias'] ?? '') ?></textarea>
                        </div>

                        <div class="rounded-xl bg-[#fdf2de] p-4 text-sm text-[#41493f]">
                            <span class="material-symbols-outlined mr-1 inline-block align-middle text-[#2f612f]">location_on</span>
                            El costo y la cobertura de entrega deben confirmarse según la dirección real del negocio.
                        </div>
                    </section>

                    <!-- 5. FECHA Y HORARIO -->
                    <section class="section-card flex flex-col gap-5">
                        <div class="section-heading">
                            <span class="section-number">5</span>
                            <div>
                                <h2 class="text-2xl font-black uppercase tracking-tight">¿Cuándo te lo llevamos?</h2>
                                <p class="text-sm text-[#41493f]">
                                    Puedes indicar una preferencia de fecha y horario.
                                </p>
                            </div>
                        </div>

                        <div>
                            <label class="field-label" for="fecha_preferida">Fecha preferida</label>
                            <input class="field" id="fecha_preferida" name="fecha_preferida"
                                   type="date"
                                   min="<?= date('Y-m-d') ?>"
                                   value="<?= $esc($datosPedido['fecha_preferida'] ?? '') ?>">
                        </div>



                        <div>
                            <p class="field-label">Horario preferido</p>
                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                                <?php
                                $horarios = [
                                    '10:00-12:00' => 'Mañana',
                                    '12:00-14:00' => 'Mediodía',
                                    '14:00-16:00' => 'Hora de comida',
                                    '16:00-18:00' => 'Tarde',
                                    '18:00-20:00' => 'Noche'
                                ];
                                foreach ($horarios as $valorHorario => $etiquetaHorario):
                                ?>
                                    <label class="time-choice relative">
                                        <input type="radio" name="horario_preferido"
                                               value="<?= $esc($valorHorario) ?>"
                                               <?= ($datosPedido['horario_preferido'] ?? '') === $valorHorario ? 'checked' : '' ?>>
                                        <span class="block text-sm font-extrabold">
                                            <?= $esc($valorHorario) ?>
                                        </span>
                                        <span class="mt-1 block text-xs">
                                            <?= $esc($etiquetaHorario) ?>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <p class="text-xs text-[#72796e]">
                            La fecha y el horario son preferencias. Todavía deben validarse y guardarse en el sistema antes de considerarse confirmados.
                        </p>
                    </section>

                    <!-- 6. OBSERVACIONES -->
                    <section class="section-card flex flex-col gap-5">
                        <div class="section-heading">
                            <span class="section-number">6</span>
                            <div>
                                <h2 class="text-2xl font-black uppercase tracking-tight">¿Algo que debamos saber?</h2>
                                <p class="text-sm text-[#41493f]">
                                    ¿Harto limón aparte? ¿Sin chamoy? Dinos aquí mero.
                                </p>
                            </div>
                        </div>

                        <textarea class="field" id="observaciones" name="observaciones"
                                  rows="4" maxlength="1000"
                                  placeholder="Escribe aquí tus preferencias o instrucciones especiales."><?= $esc($datosPedido['observaciones'] ?? '') ?></textarea>

                        <div class="flex flex-col gap-3 border-t border-dashed border-[#c1c9bc] pt-5 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-xs leading-relaxed text-[#41493f]">
                                Los campos marcados con * son obligatorios.
                            </p>

                            <button type="submit"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#2f612f] px-6 py-4 font-extrabold uppercase text-white shadow-[3px_3px_0px_#201b0f] hover:bg-[#477a45]">
                                Guardar datos y revisar
                                <span class="material-symbols-outlined">arrow_forward</span>
                            </button>
                        </div>
                    </section>
                </form>
            </div>

            <!-- COLUMNA DERECHA: TICKET -->
            <aside class="flex flex-col gap-5 lg:sticky lg:top-28 lg:col-span-5 xl:col-span-4">

                <section class="relative flex flex-col gap-5 overflow-hidden rounded-2xl border border-[#c1c9bc]/70 bg-white p-6 shadow-[4px_4px_0px_#201b0f]">

                    <div class="pointer-events-none absolute -right-4 -top-4 flex h-20 w-20 rotate-12 items-center justify-center rounded-full bg-[#ffd9de]/70">
                        <span class="text-center text-[10px] font-black uppercase leading-tight text-[#ac2d50]">
                            MERCADO<br>100%<br>FRESCO
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-3 pr-12">
                        <h2 class="text-2xl font-black uppercase">Resumen del antojo</h2>
                        <span class="rounded-full bg-[#2f612f] px-3 py-1 text-xs font-bold text-white">
                            <?= $cantidadProductos ?> prod.
                        </span>
                    </div>

                    <div class="flex flex-col gap-4">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-sm text-[#41493f]">Subtotal de antojitos:</span>
                            <span id="summary-subtotal" class="font-extrabold">
                                $<?= number_format($total, 2) ?> MXN
                            </span>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <span class="flex items-center gap-1 text-sm text-[#41493f]">
                                <span class="material-symbols-outlined text-base text-[#2f612f]">local_shipping</span>
                                Envío local:
                            </span>
                            <span id="summary-shipping" class="text-right text-sm font-bold">
                                Por confirmar
                            </span>
                        </div>

                        <div class="flex items-center justify-between gap-3 text-sm text-[#41493f]">
                            <span class="flex items-center gap-1">
                                <span class="material-symbols-outlined text-base text-[#2f612f]">eco</span>
                                Empaque:
                            </span>
                            <span id="summary-packaging" class="text-xs font-black uppercase text-[#2f612f]">Por confirmar</span>
                        </div>
                    </div>

                    <div class="ticket-divider"></div>

                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <span class="block text-xs font-bold uppercase text-[#41493f]">Total estimado</span>
                            <span id="summary-service" class="text-xs text-[#72796e]">Selecciona cómo recibirlo</span>
                        </div>
                        <div class="text-right">
                            <span id="summary-total" data-subtotal="<?= $esc(number_format($total, 2, ".", "")) ?>" class="text-3xl font-black tracking-tight text-[#2f612f]">
                                $<?= number_format($total, 2) ?>
                            </span>
                            <span class="block text-xs font-bold">MXN</span>
                        </div>
                    </div>

                    <!-- Cupón visual: sin aplicar descuentos ficticios -->
                    <div class="flex items-center gap-2 rounded-xl border border-[#c1c9bc]/50 bg-[#f8edd8] p-2 shadow-[2px_2px_0px_#201b0f]">
                        <input id="coupon-code" type="text"
                               class="w-full min-w-0 bg-transparent px-2 py-2 text-sm font-bold uppercase outline-none"
                               placeholder="¿Tienes cupón de mercado?"
                               aria-label="Código de cupón">
                        <button id="coupon-button" type="button"
                                class="shrink-0 rounded-lg bg-white px-3 py-2 text-xs font-black uppercase shadow-[1px_1px_0px_#201b0f] hover:bg-[#f2e7d2]">
                            Aplicar
                        </button>
                    </div>

                    <p id="coupon-message" class="hidden text-xs font-semibold text-[#ac2d50]"></p>

                    <button id="main-checkout-cta" type="button"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#2f612f] px-5 py-4 font-black uppercase tracking-wide text-white shadow-[4px_4px_0px_#201b0f] hover:bg-[#477a45]">
                        Revisar datos del pedido
                        <span class="material-symbols-outlined">arrow_forward</span>
                    </button>

                    <p class="flex items-start gap-2 text-xs leading-relaxed text-[#41493f]">
                        <span class="material-symbols-outlined shrink-0 text-base text-[#2f612f]">lock</span>
                        Tus datos se utilizarán para procesar el pedido. El pedido aún no está confirmado.
                    </p>

                    <div class="flex flex-col gap-3 rounded-xl border border-[#c1c9bc]/30 bg-[#fdf2de] p-4">
                        <span class="mb-1 text-xs font-black uppercase">Garantía de barrio FRUTA MADRE:</span>

                        <div class="flex items-center gap-2 text-xs">
                            <span class="material-symbols-outlined text-base text-[#2f612f]">verified</span>
                            Fruta fresca preparada al momento.
                        </div>

                        <div class="flex items-center gap-2 text-xs">
                            <span class="material-symbols-outlined text-base text-[#2f612f]">verified</span>
                            Chamoy y salsas artesanales.
                        </div>

                        <div class="flex items-center gap-2 text-xs">
                            <span class="material-symbols-outlined text-base text-[#2f612f]">verified</span>
                            Revisión del pedido antes de confirmarlo.
                        </div>
                    </div>
                </section>

                <!-- AYUDA -->
                <section class="flex items-center justify-between gap-4 rounded-2xl border border-[#c1c9bc]/40 bg-[#fdf2de] p-4 shadow-[3px_3px_0px_#201b0f]">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#ffd9de] text-[#ac2d50] shadow-[1px_1px_0px_#201b0f]">
                            <span class="material-symbols-outlined">chat</span>
                        </div>
                        <div>
                            <span class="block text-xs font-black uppercase">¿Dudas con tu entrega?</span>
                            <span class="text-xs text-[#41493f]">Contacta con el puesto.</span>
                        </div>
                    </div>
                    <span class="shrink-0 text-xs font-bold text-[#72796e]">Próximamente</span>
                </section>
            </aside>
        </div>
    <?php endif; ?>
</main>

<!-- FOOTER -->
<footer class="mt-10 border-t border-[#ece1cd] bg-[#fdf2de] text-[#201b0f]">
    <div class="flex h-1.5">
        <div class="flex-1 bg-[#b55107]"></div>
        <div class="flex-1 bg-[#f2e7d2]"></div>
        <div class="flex-1 bg-[#fc6a8a]"></div>
        <div class="flex-1 bg-[#477a45]"></div>
    </div>

    <div class="mx-auto grid max-w-[1440px] grid-cols-1 gap-8 px-5 py-9 sm:grid-cols-2 lg:grid-cols-5 lg:px-10">
        <div class="lg:col-span-2">
            <p class="brand text-xl font-extrabold tracking-wider">FRUTA MADRE</p>
            <p class="mt-2 max-w-sm text-sm text-[#41493f]">
                Fruta fresca, chamoy artesanal y antojitos preparados al momento.
                Te damos hasta para llevar.
            </p>
        </div>

        <div>
            <h3 class="mb-3 text-sm font-extrabold uppercase">Explorar</h3>
            <div class="flex flex-col gap-2 text-sm text-[#41493f]">
                <a href="index.php" class="hover:text-[#2f612f]">Inicio</a>
                <a href="index.php#catalogo" class="hover:text-[#2f612f]">Catálogo</a>
                <a href="index.php#eventos" class="hover:text-[#2f612f]">Eventos</a>
            </div>
        </div>

        <div>
            <h3 class="mb-3 text-sm font-extrabold uppercase">Tu pedido</h3>
            <div class="flex flex-col gap-2 text-sm text-[#41493f]">
                <a href="index.php?accion=carrito" class="hover:text-[#2f612f]">Mi carrito</a>
                <a href="index.php?accion=login" class="hover:text-[#2f612f]">Mi cuenta</a>
            </div>
        </div>

        <div>
            <h3 class="mb-3 text-sm font-extrabold uppercase">Atención</h3>
            <p class="text-sm text-[#41493f]">
                Para dudas sobre disponibilidad, entrega o pedidos, utiliza los canales de contacto oficiales del negocio.
            </p>
        </div>
    </div>

    <div class="border-t border-[#c1c9bc]/50 px-5 py-5 text-center text-xs text-[#41493f]">
        © <?= date('Y') ?> Fruta Madre. Todos los derechos reservados.
    </div>
</footer>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('form-datos-pedido');
    const serviceInputs = document.querySelectorAll('input[name="tipo_servicio"]');
    const addressSection = document.getElementById('direccion-contenedor');
    const address = document.getElementById('direccion');
    const summaryShipping = document.getElementById('summary-shipping');
    const summaryPackaging = document.getElementById('summary-packaging');
    const summaryService = document.getElementById('summary-service');
    const summaryTotal = document.getElementById('summary-total');
    const subtotal = Number(summaryTotal?.dataset.subtotal || 0);
    const dateInput = document.getElementById('fecha_preferida');
    const continueButton = document.getElementById('main-checkout-cta');

    function money(value) {
        return new Intl.NumberFormat('es-MX', {style: 'currency', currency: 'MXN'}).format(value);
    }

    function updateCheckout() {
        const selected = document.querySelector('input[name="tipo_servicio"]:checked');
        const pickup = selected?.value === 'recoger';
        if (addressSection) addressSection.hidden = pickup;
        if (address) {
            address.required = !pickup;
            address.disabled = pickup;
        }
        if (summaryShipping) summaryShipping.textContent = pickup ? money(0) : 'Por confirmar';
        if (summaryPackaging) summaryPackaging.textContent = 'Por confirmar';
        if (summaryService) summaryService.textContent = pickup
            ? 'Recoger en establecimiento · Sin costo de envío'
            : 'Envío local · Sujeto a cobertura y tarifa';
        if (summaryTotal) summaryTotal.textContent = money(subtotal);
    }

    serviceInputs.forEach(input => input.addEventListener('change', updateCheckout));
    updateCheckout();

    if (dateInput) {
        const now = new Date();
        dateInput.min = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 10);
    }

    if (form) {
        form.addEventListener('submit', function (event) {
            const selected = document.querySelector('input[name="tipo_servicio"]:checked');
            if (!selected) {
                event.preventDefault(); alert('Selecciona cómo quieres recibir tu pedido.'); return;
            }
            if (selected.value === 'domicilio' && !address.value.trim()) {
                event.preventDefault(); address.focus(); alert('Escribe la dirección completa para la entrega.'); return;
            }
            const phone = document.getElementById('telefono');
            const digits = (phone?.value || '').replace(/\D/g, '');
            if (digits.length < 10 || digits.length > 15) {
                event.preventDefault(); phone.focus(); alert('Escribe un teléfono válido de 10 a 15 dígitos.');
            }
        });
    }

    if (continueButton && form) {
        continueButton.addEventListener('click', function () {
            <?php if (!$logueado): ?>
            window.location.href = 'index.php?accion=continuar';
            <?php else: ?>
            form.scrollIntoView({behavior: 'smooth', block: 'start'});
            document.getElementById('nombre')?.focus({preventScroll: true});
            <?php endif; ?>
        });
    }

    const couponButton = document.getElementById('coupon-button');
    const couponCode = document.getElementById('coupon-code');
    const couponMessage = document.getElementById('coupon-message');
    if (couponButton && couponCode && couponMessage) {
        couponButton.addEventListener('click', function () {
            couponMessage.textContent = couponCode.value.trim()
                ? 'Este cupón aún no está configurado. No se aplicó ningún descuento.'
                : 'Escribe un código de cupón.';
            couponMessage.classList.remove('hidden');
        });
    }
});
</script>

</body>
</html>