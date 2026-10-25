
<?php
$total = (float) ($total ?? 0);
$productosCarrito = $productosCarrito ?? [];

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

    <title>FRUTA MADRE | Mi carrito</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Syne:wght@500;600;700;800&display=swap" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            font-family: 'DM Sans', sans-serif;
            background: #fff8f0;
            color: #201b0f;
        }

        h1, h2, h3, .brand {
            font-family: 'Syne', sans-serif;
        }

        button, a {
            transition: all .2s ease;
        }
    </style>
</head>

<body class="min-h-screen">

    <!-- Aviso superior -->
    <div class="bg-[#2f612f] px-4 py-2 text-center text-xs font-bold uppercase tracking-widest text-white">
        Entregas exprés · Fruta fresca del día · Chamoy artesanal
    </div>

    <!-- Encabezado -->
    <header class="sticky top-0 z-20 border-b border-[#ece1cd] bg-[#fff8f0]/95 shadow-sm backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-5 py-5 lg:px-10">

            <a href="index.php" class="block shrink-0">
                <span class="brand block text-lg font-extrabold tracking-wider text-[#201b0f]">
                    FRUTA MADRE
                </span>
                <span class="block text-xs font-bold uppercase text-[#ac2d50]">
                    Te damos hasta para llevar
                </span>
            </a>

            <nav class="hidden items-center gap-2 lg:flex">
                <a href="index.php" class="rounded-lg px-4 py-2 hover:bg-[#f8edd8]">
                    Inicio
                </a>

                <a href="index.php#catalogo" class="rounded-lg px-4 py-2 hover:bg-[#f8edd8]">
                    Catálogo
                </a>

                <a href="index.php" class="rounded-lg px-4 py-2 hover:bg-[#f8edd8]">
                    Eventos
                </a>
            </nav>

            <div class="flex items-center gap-3">
                <a href="index.php?accion=carrito"
                   aria-label="Ver carrito"
                   class="relative flex items-center gap-2 rounded-xl bg-[#f8edd8] px-4 py-3 font-bold shadow-[2px_2px_0px_#201b0f]">
                    <span class="material-symbols-outlined">shopping_bag</span>
                    <span class="hidden sm:inline">Mi carrito</span>

                    <span class="flex h-6 min-w-6 items-center justify-center rounded-full bg-[#ac2d50] px-1 text-xs text-white">
                        <?= $cantidadProductos ?>
                    </span>
                </a>
            </div>

        </div>

        <!-- Cenefa de colores -->
        <div class="flex h-1.5">
            <div class="flex-1 bg-[#477a45]"></div>
            <div class="flex-1 bg-[#fc6a8a]"></div>
            <div class="flex-1 bg-[#f2e7d2]"></div>
            <div class="flex-1 bg-[#b55107]"></div>
        </div>
    </header>

    <main class="mx-auto min-h-[65vh] max-w-7xl px-5 py-8 lg:px-10 lg:py-12">

        <!-- Ruta de navegación -->
        <div class="mb-6 flex flex-wrap items-center gap-2 text-sm text-[#41493f]">
            <a href="index.php" class="hover:text-[#2f612f]">Inicio</a>
            <span class="material-symbols-outlined text-base">chevron_right</span>
            <span class="font-bold text-[#ac2d50]">Mi carrito</span>
        </div>

        <!-- Progreso de compra -->
        <section class="mb-10 rounded-2xl border border-[#c1c9bc] bg-[#fdf2de] p-4 shadow-[3px_3px_0px_#201b0f]">
            <div class="grid grid-cols-2 gap-3 md:grid-cols-4">

                <div class="flex items-center gap-2">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#2f612f] font-bold text-white">
                        <span class="material-symbols-outlined">check</span>
                    </span>
                    <div>
                        <p class="text-xs font-extrabold uppercase">1. Carrito</p>
                        <p class="text-xs text-[#2f612f]">Actual</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 opacity-60">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#ece1cd] font-bold">2</span>
                    <div>
                        <p class="text-xs font-extrabold uppercase">2. Entrega</p>
                        <p class="text-xs">Siguiente</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 opacity-60">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#ece1cd] font-bold">3</span>
                    <div>
                        <p class="text-xs font-extrabold uppercase">3. Pago</p>
                        <p class="text-xs">Pendiente</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 opacity-60">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#ece1cd] font-bold">4</span>
                    <div>
                        <p class="text-xs font-extrabold uppercase">4. Confirmación</p>
                        <p class="text-xs">Pendiente</p>
                    </div>
                </div>

            </div>
        </section>

        <!-- Título -->
        <div class="mb-8">
            <span class="mb-3 inline-flex items-center gap-2 rounded-full bg-[#ffdbca] px-3 py-2 text-xs font-extrabold uppercase tracking-wider text-[#903e00]">
                <span class="material-symbols-outlined text-base">shopping_basket</span>
                Puesto digital · Pedido en preparación
            </span>

            <h1 class="mb-2 text-4xl font-extrabold uppercase tracking-tight sm:text-5xl">
                Tu pedido
            </h1>

            <p class="max-w-2xl text-[#41493f]">
                Vamos a revisar tus antojos antes de continuar.
                Todo va fresco, recién preparado y con harto sabor.
            </p>
        </div>

        <?php if (empty($productosCarrito)): ?>

            <!-- Carrito vacío -->
            <section class="rounded-2xl border border-[#c1c9bc] bg-white px-6 py-16 text-center shadow-[4px_4px_0px_#201b0f]">

                <div class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-full bg-[#ffd9de] text-[#ac2d50]">
                    <span class="material-symbols-outlined text-5xl">shopping_basket</span>
                </div>

                <h2 class="mb-3 text-2xl font-extrabold uppercase">
                    Tu carrito está vacío
                </h2>

                <p class="mx-auto mb-7 max-w-md text-[#41493f]">
                    No te quedes con el antojo. Regresa al catálogo y elige algo rico.
                </p>

                <a href="index.php"
                   class="inline-flex items-center gap-2 rounded-xl bg-[#2f612f] px-6 py-4 font-bold text-white shadow-[3px_3px_0px_#201b0f] hover:bg-[#477a45]">
                    <span class="material-symbols-outlined">storefront</span>
                    Ver catálogo completo
                </a>

            </section>

        <?php else: ?>

            <!-- Carrito con productos reales -->
            <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">

                <!-- Productos -->
                <section class="flex flex-col gap-5 lg:col-span-8">

                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h2 class="text-2xl font-extrabold uppercase">
                            Lo que llevas
                        </h2>

                        <span class="rounded-full bg-[#ffd9de] px-3 py-2 text-xs font-bold text-[#8c1039]">
                            <?= $cantidadProductos ?>
                            <?= $cantidadProductos === 1 ? 'producto' : 'productos' ?>
                        </span>
                    </div>

                    <?php foreach ($productosCarrito as $producto): ?>

                        <?php
                        $idProducto = (int) $producto['id_producto'];
                        $cantidad = (int) $producto['cantidad'];
                        $precio = (float) $producto['precio'];
                        $subtotal = (float) $producto['subtotal'];
                        ?>

                        <article class="flex flex-col gap-4 rounded-2xl border border-[#c1c9bc]/70 bg-white p-5 shadow-[3px_3px_0px_#201b0f] sm:flex-row sm:items-center sm:justify-between">

                            <div class="flex min-w-0 items-center gap-4">

                                <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-[#f8edd8]">
                                    <?php if (!empty($producto['imagen_url'])): ?>
                                        <img
                                            src="<?= htmlspecialchars($producto['imagen_url'], ENT_QUOTES, 'UTF-8') ?>"
                                            alt="<?= htmlspecialchars($producto['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                                            class="h-full w-full object-cover"
                                        >
                                    <?php else: ?>
                                        <span class="text-4xl">🍓</span>
                                    <?php endif; ?>
                                </div>

                                <div class="min-w-0">
                                    <p class="mb-1 text-xs font-bold uppercase tracking-wider text-[#2f612f]">
                                        <?= htmlspecialchars($producto['nombre_categoria'] ?? 'Antojito', ENT_QUOTES, 'UTF-8') ?>
                                    </p>

                                    <h3 class="text-lg font-extrabold">
                                        <?= htmlspecialchars($producto['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                    </h3>

                                    <p class="mt-1 text-sm text-[#41493f]">
                                        $<?= number_format($precio, 2) ?> MXN por unidad
                                    </p>

                                    <p class="mt-1 text-sm font-semibold">
                                        Cantidad: <?= $cantidad ?>
                                    </p>

                                    <form action="index.php?accion=eliminar" method="POST" class="mt-2">
                                        <input
                                            type="hidden"
                                            name="id_producto"
                                            value="<?= $idProducto ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="inline-flex items-center gap-1 text-sm font-bold text-[#ac2d50] hover:underline"
                                        >
                                            <span class="material-symbols-outlined text-base">delete_outline</span>
                                            Eliminar
                                        </button>
                                    </form>
                                </div>

                            </div>

                            <div class="flex shrink-0 items-center justify-between gap-4 border-t border-dashed border-[#c1c9bc] pt-3 sm:flex-col sm:items-end sm:border-0 sm:pt-0">

                                <span class="text-xs font-bold uppercase text-[#41493f]">Subtotal</span>

                                <p class="text-xl font-extrabold text-[#2f612f]">
                                    $<?= number_format($subtotal, 2) ?>
                                    <span class="block text-right text-xs font-semibold text-[#41493f]">MXN</span>
                                </p>

                            </div>

                        </article>

                    <?php endforeach; ?>

                    <div class="flex flex-wrap items-center justify-between gap-4 pt-2">

                        <a href="index.php"
                           class="inline-flex items-center gap-2 rounded-xl border-2 border-[#c1c9bc] bg-white px-5 py-3 font-bold hover:bg-[#f8edd8]">
                            <span class="material-symbols-outlined">arrow_back</span>
                            Seguir comprando
                        </a>

                        <form action="index.php?accion=vaciar" method="POST"
                              onsubmit="return confirm('¿Seguro que deseas vaciar todo el carrito?');">
                            <button type="submit"
                                    class="inline-flex items-center gap-2 rounded-xl px-4 py-3 font-bold text-[#ac2d50] hover:bg-[#ffd9de]">
                                <span class="material-symbols-outlined">delete_sweep</span>
                                Vaciar carrito
                            </button>
                        </form>

                    </div>

                </section>

                <!-- Resumen -->
                <aside class="lg:sticky lg:top-28 lg:col-span-4">
                    <div class="rounded-2xl border border-[#c1c9bc]/70 bg-white p-6 shadow-[4px_4px_0px_#201b0f]">

                        <h2 class="mb-6 text-2xl font-extrabold uppercase">
                            Resumen del pedido
                        </h2>

                        <div class="flex items-center justify-between gap-4 border-b border-dashed border-[#c1c9bc] pb-4">
                            <span class="text-[#41493f]">Productos</span>
                            <span class="font-bold"><?= $cantidadProductos ?></span>
                        </div>

                        <div class="flex items-center justify-between gap-4 border-b border-dashed border-[#c1c9bc] py-4">
                            <span class="text-[#41493f]">Subtotal</span>
                            <span class="font-bold">$<?= number_format($total, 2) ?></span>
                        </div>

                        <div class="flex items-center justify-between gap-4 py-5">
                            <span class="text-lg font-extrabold">Total</span>
                            <span class="text-2xl font-extrabold text-[#2f612f]">
                                $<?= number_format($total, 2) ?>
                                <span class="block text-right text-xs">MXN</span>
                            </span>
                        </div>

                        <p class="mb-5 text-sm leading-relaxed text-[#41493f]">
                            Los costos de entrega, si aplican, se mostrarán cuando elijas el tipo de servicio.
                        </p>

                        
<a
    href="index.php?accion=continuar"
    class="block w-full rounded-xl bg-[#2f612f] px-5 py-4 text-center font-extrabold text-white shadow-[3px_3px_0px_#201b0f] hover:bg-[#477a45]">
    Continuar con la compra
</a>


                        <p class="mt-3 text-center text-xs text-[#72796e]">
                            Próximo paso: datos de entrega y pedido
                        </p>

                    </div>
                </aside>

            </div>


            <!-- Datos del pedido -->
            <?php
            $datosPedido = $_SESSION['datos_pedido'] ?? [];
            $erroresPedido = $_SESSION['errores_pedido'] ?? [];
            ?>

            <section id="datos-pedido" class="mt-10 rounded-2xl border border-[#c1c9bc] bg-white p-6 shadow-[4px_4px_0px_#201b0f] sm:p-8">

                <div class="mb-6">
                    <span class="mb-2 inline-flex rounded-full bg-[#ffd9de] px-3 py-2 text-xs font-bold uppercase tracking-wider text-[#8c1039]">
                        Siguiente paso
                    </span>

                    <h2 class="text-2xl font-extrabold uppercase sm:text-3xl">
                        ¿Cómo quieres recibir tu pedido?
                    </h2>

                    <p class="mt-2 text-sm text-[#41493f]">
                        Déjanos tus datos para preparar tu pedido correctamente.
                    </p>
                </div>

                <?php if (!empty($erroresPedido)): ?>
                    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                        <p class="mb-2 font-bold">Revisa los siguientes datos:</p>
                        <ul class="list-inside list-disc space-y-1">
                            <?php foreach ($erroresPedido as $error): ?>
                                <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php unset($_SESSION['errores_pedido']); ?>
                <?php endif; ?>

                <?php if (isset($_GET['datos_guardados'])): ?>
                    <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-semibold text-green-800">
                        Tus datos se validaron y se guardaron temporalmente. Todavía falta implementar el pago y confirmar el pedido.
                    </div>
                <?php endif; ?>

                <form action="index.php?accion=guardar-datos" method="POST" class="space-y-7">

                    <!-- Tipo de servicio -->
                    <fieldset>
                        <legend class="mb-3 font-extrabold">Tipo de servicio</legend>

                        <div class="grid gap-4 sm:grid-cols-2">

                            <label class="flex cursor-pointer items-start gap-3 rounded-xl border-2 border-[#c1c9bc] p-4 hover:border-[#477a45]">
                                <input
                                    type="radio"
                                    name="tipo_servicio"
                                    value="domicilio"
                                    required
                                    class="mt-1 accent-[#477a45]"
                                    <?= ($datosPedido['tipo_servicio'] ?? '') === 'domicilio' ? 'checked' : '' ?>
                                >

                                <span>
                                    <span class="block font-bold">Entrega a domicilio</span>
                                    <span class="mt-1 block text-sm text-[#41493f]">
                                        Recibe tu pedido en la dirección que indiques.
                                    </span>
                                </span>
                            </label>

                            <label class="flex cursor-pointer items-start gap-3 rounded-xl border-2 border-[#c1c9bc] p-4 hover:border-[#477a45]">
                                <input
                                    type="radio"
                                    name="tipo_servicio"
                                    value="recoger"
                                    required
                                    class="mt-1 accent-[#477a45]"
                                    <?= ($datosPedido['tipo_servicio'] ?? '') === 'recoger' ? 'checked' : '' ?>
                                >

                                <span>
                                    <span class="block font-bold">Recoger en tienda</span>
                                    <span class="mt-1 block text-sm text-[#41493f]">
                                        Pasarás por tu pedido al establecimiento.
                                    </span>
                                </span>
                            </label>

                        </div>
                    </fieldset>

                    <!-- Datos del cliente -->
                    <div>
                        <h3 class="mb-4 text-lg font-extrabold">Datos de contacto</h3>

                        <div class="grid gap-5 sm:grid-cols-2">

                            <div>
                                <label for="nombre" class="mb-2 block text-sm font-bold">
                                    Nombre completo *
                                </label>

                                <input
                                    type="text"
                                    id="nombre"
                                    name="nombre"
                                    required
                                    maxlength="120"
                                    autocomplete="name"
                                    value="<?= htmlspecialchars($datosPedido['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                    placeholder="Escribe tu nombre"
                                    class="w-full rounded-xl border border-[#c1c9bc] bg-[#fff8f0] px-4 py-3 outline-none focus:border-[#477a45] focus:ring-2 focus:ring-[#477a45]/20"
                                >
                            </div>

                            <div>
                                <label for="telefono" class="mb-2 block text-sm font-bold">
                                    Teléfono o WhatsApp *
                                </label>

                                <input
                                    type="tel"
                                    id="telefono"
                                    name="telefono"
                                    required
                                    maxlength="20"
                                    autocomplete="tel"
                                    value="<?= htmlspecialchars($datosPedido['telefono'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                    placeholder="Ej. 9621234567"
                                    class="w-full rounded-xl border border-[#c1c9bc] bg-[#fff8f0] px-4 py-3 outline-none focus:border-[#477a45] focus:ring-2 focus:ring-[#477a45]/20"
                                >
                            </div>

                        </div>
                    </div>

                    <!-- Dirección -->
                    <div id="direccion-contenedor">
                        <h3 class="mb-4 text-lg font-extrabold">Dirección de entrega</h3>

                        <div class="space-y-4">

                            <div>
                                <label for="direccion" class="mb-2 block text-sm font-bold">
                                    Dirección completa *
                                </label>

                                <textarea
                                    id="direccion"
                                    name="direccion"
                                    rows="2"
                                    maxlength="500"
                                    placeholder="Calle, número, colonia y ciudad"
                                    class="w-full rounded-xl border border-[#c1c9bc] bg-[#fff8f0] px-4 py-3 outline-none focus:border-[#477a45] focus:ring-2 focus:ring-[#477a45]/20"
                                ><?= htmlspecialchars($datosPedido['direccion'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                            </div>

                            <div>
                                <label for="referencias" class="mb-2 block text-sm font-bold">
                                    Referencias para llegar
                                </label>

                                <textarea
                                    id="referencias"
                                    name="referencias"
                                    rows="2"
                                    maxlength="500"
                                    placeholder="Color de la fachada, entre calles u otras referencias"
                                    class="w-full rounded-xl border border-[#c1c9bc] bg-[#fff8f0] px-4 py-3 outline-none focus:border-[#477a45] focus:ring-2 focus:ring-[#477a45]/20"
                                ><?= htmlspecialchars($datosPedido['referencias'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                            </div>

                        </div>
                    </div>

                    <!-- Observaciones -->
                    <div>
                        <label for="observaciones" class="mb-2 block text-sm font-bold">
                            Observaciones del pedido (opcional)
                        </label>

                        <textarea
                            id="observaciones"
                            name="observaciones"
                            rows="3"
                            maxlength="1000"
                            placeholder="Indica alguna preferencia o instrucción especial."
                            class="w-full rounded-xl border border-[#c1c9bc] bg-[#fff8f0] px-4 py-3 outline-none focus:border-[#477a45] focus:ring-2 focus:ring-[#477a45]/20"
                        ><?= htmlspecialchars($datosPedido['observaciones'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <div class="flex flex-col gap-3 border-t border-dashed border-[#c1c9bc] pt-6 sm:flex-row sm:items-center sm:justify-between">

                        <p class="text-xs leading-relaxed text-[#41493f]">
                            Los campos marcados con * son obligatorios.
                            Todavía no se realizará ningún cobro.
                        </p>

                        <button
                            type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#2f612f] px-6 py-4 font-extrabold text-white shadow-[3px_3px_0px_#201b0f] hover:bg-[#477a45]">
                            Guardar datos y continuar
                            <span class="material-symbols-outlined">arrow_forward</span>
                        </button>

                    </div>

                </form>
            </section>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const opcionesServicio = document.querySelectorAll(
                        'input[name="tipo_servicio"]'
                    );

                    const direccionContenedor = document.getElementById(
                        'direccion-contenedor'
                    );

                    const direccion = document.getElementById('direccion');

                    
function actualizarDireccion() {
    const seleccion = document.querySelector(
        'input[name="tipo_servicio"]:checked'
    );

    const esDomicilio =
        seleccion && seleccion.value === 'domicilio';

    direccionContenedor.hidden = !esDomicilio;
    direccion.required = Boolean(esDomicilio);

    if (!esDomicilio) {
        direccion.value = '';
        document.getElementById('referencias').value = '';
    }
}


                    opcionesServicio.forEach(function (opcion) {
                        opcion.addEventListener('change', actualizarDireccion);
                    });

                    actualizarDireccion();
                });
            </script>

        <?php endif; ?>

    </main>

    <!-- Pie de página -->
    <footer class="mt-10 bg-[#fdf2de]">
        <div class="mx-auto flex max-w-7xl flex-col justify-between gap-4 px-5 py-8 sm:flex-row sm:items-center lg:px-10">

            <div>
                <p class="brand font-extrabold tracking-wider">FRUTA MADRE</p>
                <p class="mt-1 text-sm text-[#ac2d50]">Te damos hasta para llevar.</p>
            </div>

            <p class="text-sm text-[#41493f]">
                © <?= date('Y') ?> Fruta Madre. Todos los derechos reservados.
            </p>

        </div>
    </footer>

</body>
</html>
