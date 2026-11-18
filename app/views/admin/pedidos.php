
<?php

$mensajeAdmin = $_SESSION['mensaje_admin'] ?? null;
unset($_SESSION['mensaje_admin']);

$dinero = static function ($cantidad): string {
    return '$' . number_format((float) $cantidad, 2) . ' MXN';
};

$fecha = static function ($valor): string {
    if (empty($valor)) {
        return 'No disponible';
    }

    return date('d/m/Y H:i', strtotime($valor));
};

$estadoPedido = [
    'pendiente' => ['Pendiente', 'bg-amber-100 text-amber-800'],
    'confirmado' => ['Confirmado', 'bg-blue-100 text-blue-800'],
    'en_preparacion' => ['En preparación', 'bg-orange-100 text-orange-800'],
    'en_camino' => ['En camino', 'bg-purple-100 text-purple-800'],
    'entregado' => ['Entregado', 'bg-green-100 text-green-800'],
    'cancelado' => ['Cancelado', 'bg-red-100 text-red-800'],
];

$estadoEntrega = [
    'pendiente' => ['Pendiente', 'bg-amber-100 text-amber-800'],
    'en_camino' => ['En camino', 'bg-purple-100 text-purple-800'],
    'entregado' => ['Entregado', 'bg-green-100 text-green-800'],
    'cancelado' => ['Cancelado', 'bg-red-100 text-red-800'],
];

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Gestión de pedidos | Fruta Madre</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Syne:wght@500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            font-family: 'DM Sans', sans-serif;
        }

        .display {
            font-family: 'Syne', sans-serif;
        }

        .pedido-card {
            border: 2px solid #211d1a;
            box-shadow: 3px 3px 0 #211d1a;
        }
    </style>
</head>

<body class="bg-[#fff8f0] text-[#211d1a]">

    <!-- ENCABEZADO -->

    <header class="bg-[#477a45] text-white border-b-2 border-[#211d1a]">
        <div class="max-w-7xl mx-auto px-5 py-5 flex flex-wrap items-center justify-between gap-4">

            <div>
                <h1 class="display text-2xl font-extrabold uppercase">
                    FRUTA MADRE
                </h1>

                <p class="text-xs uppercase tracking-widest text-white/80">
                    Panel administrativo
                </p>
            </div>

            <a href="index.php?accion=admin"
               class="bg-white text-[#211d1a] px-4 py-2 rounded-lg font-bold text-sm border-2 border-[#211d1a] shadow-[2px_2px_0px_#211d1a]">
                Volver al panel
            </a>

        </div>
    </header>


    <main class="max-w-7xl mx-auto px-5 py-8">

        <!-- TÍTULO -->

        <section class="mb-8">

            <span class="inline-block bg-[#e8b93f] px-3 py-1 rounded-full text-xs font-extrabold uppercase border border-[#211d1a]">
                Administración
            </span>

            <h2 class="display text-3xl md:text-4xl font-extrabold uppercase mt-3">
                Gestión de pedidos
            </h2>

            <p class="text-[#41493f] mt-2">
                Consulta los pedidos recibidos y administra su preparación y entrega.
            </p>

        </section>


        <!-- MENSAJE -->

        <?php if ($mensajeAdmin): ?>

            <div class="mb-6 p-4 rounded-lg border-2 border-[#211d1a]
                <?= $mensajeAdmin['tipo'] === 'success'
                    ? 'bg-[#477a45] text-white'
                    : 'bg-red-100 text-red-800' ?>">

                <?= htmlspecialchars($mensajeAdmin['texto'], ENT_QUOTES, 'UTF-8') ?>

            </div>

        <?php endif; ?>


        <!-- RESUMEN -->

        <?php
        $totalPedidos = count($pedidos ?? []);

        $pendientes = 0;
        $preparacion = 0;
        $entregados = 0;

        foreach ($pedidos ?? [] as $pedido) {
            $estado = $pedido['estado_pedido'] ?? '';

            if ($estado === 'pendiente' || $estado === 'confirmado') {
                $pendientes++;
            }

            if ($estado === 'en_preparacion') {
                $preparacion++;
            }

            if ($estado === 'entregado') {
                $entregados++;
            }
        }
        ?>

        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

            <div class="pedido-card bg-white rounded-xl p-5">
                <p class="text-sm text-gray-500 font-semibold">Pedidos totales</p>
                <p class="display text-3xl font-extrabold mt-2">
                    <?= $totalPedidos ?>
                </p>
            </div>

            <div class="pedido-card bg-[#fff1cc] rounded-xl p-5">
                <p class="text-sm font-semibold">Pendientes</p>
                <p class="display text-3xl font-extrabold mt-2">
                    <?= $pendientes ?>
                </p>
            </div>

            <div class="pedido-card bg-[#ffeadb] rounded-xl p-5">
                <p class="text-sm font-semibold">En preparación</p>
                <p class="display text-3xl font-extrabold mt-2">
                    <?= $preparacion ?>
                </p>
            </div>

            <div class="pedido-card bg-[#e4f3df] rounded-xl p-5">
                <p class="text-sm font-semibold">Entregados</p>
                <p class="display text-3xl font-extrabold mt-2">
                    <?= $entregados ?>
                </p>
            </div>

        </section>


        <!-- FILTROS -->

        <section class="bg-white pedido-card rounded-xl p-5 mb-8">

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">

                <div>
                    <h3 class="display text-lg font-bold uppercase">
                        Pedidos recibidos
                    </h3>

                    <p class="text-sm text-gray-500">
                        Consulta y administra cada pedido.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-2">

                    <input
                        type="search"
                        id="buscarPedido"
                        placeholder="Buscar folio o cliente..."
                        class="border-2 border-[#211d1a] rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#477a45] outline-none"
                    >

                    <select
                        id="filtroEstado"
                        class="border-2 border-[#211d1a] rounded-lg px-3 py-2 text-sm bg-white">

                        <option value="">Todos los estados</option>
                        <option value="pendiente">Pendientes</option>
                        <option value="confirmado">Confirmados</option>
                        <option value="en_preparacion">En preparación</option>
                        <option value="en_camino">En camino</option>
                        <option value="entregado">Entregados</option>
                        <option value="cancelado">Cancelados</option>

                    </select>

                </div>

            </div>

        </section>


        <!-- LISTADO -->

        <section class="space-y-6" id="listaPedidos">

            <?php if (empty($pedidos)): ?>

                <div class="pedido-card bg-white rounded-xl p-10 text-center">

                    <div class="text-5xl mb-4">🛍️</div>

                    <h3 class="display text-xl font-bold uppercase">
                        Todavía no hay pedidos
                    </h3>

                    <p class="text-gray-500 mt-2">
                        Cuando los clientes realicen compras, aparecerán aquí.
                    </p>

                </div>

            <?php else: ?>

                <?php foreach ($pedidos as $pedido): ?>

                    <?php
                    $idPedido = (int) $pedido['id_pedido'];

                    $estado = $pedido['estado_pedido'] ?? 'pendiente';

                    $estadoVisual = $estadoPedido[$estado]
                        ?? ['Desconocido', 'bg-gray-100 text-gray-800'];

                    $productos = $pedido['productos'] ?? [];

                    $tipoServicio = $pedido['tipo_servicio'] ?? 'No especificado';

                    $estadoPago = $pedido['estado_pago'] ?? 'No disponible';
                    $pagoPendiente = $estadoPago === 'pendiente';

                    $estadoPagoVisual = [
                        'pendiente' => ['Pendiente', 'bg-amber-100 text-amber-800'],
                        'pagado' => ['Pagado', 'bg-green-100 text-green-800'],
                        'aprobado_prueba' => ['Pago aprobado (prueba)', 'bg-green-100 text-green-800'],
                        'aprobado' => ['Pago aprobado', 'bg-green-100 text-green-800'],
                        'rechazado' => ['Rechazado', 'bg-red-100 text-red-800'],
                    ];

                    $pagoVisual = $estadoPagoVisual[$estadoPago]
                        ?? [$estadoPago, 'bg-gray-100 text-gray-800'];

                    $textoBusqueda = strtolower(
                        $idPedido . ' ' .
                        ($pedido['nombre_completo'] ?? '') . ' ' .
                        ($pedido['correo'] ?? '')
                    );
                    ?>

                    <article
                        class="pedido-card bg-white rounded-xl overflow-hidden pedido-item"
                        data-estado="<?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?>"
                        data-busqueda="<?= htmlspecialchars($textoBusqueda, ENT_QUOTES, 'UTF-8') ?>"
                    >

                        <!-- CABECERA DEL PEDIDO -->

                        <div class="bg-[#f8edd8] p-5 border-b-2 border-[#211d1a]">

                            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">

                                <div>

                                    <div class="flex flex-wrap items-center gap-2">

                                        <h3 class="display text-xl font-extrabold uppercase">
                                            Pedido #<?= $idPedido ?>
                                        </h3>

                                        <span class="px-3 py-1 rounded-full text-xs font-bold <?= $estadoVisual[1] ?>">
                                            <?= htmlspecialchars($estadoVisual[0], ENT_QUOTES, 'UTF-8') ?>
                                        </span>

                                    </div>

                                    <p class="text-sm text-gray-600 mt-1">
                                        Recibido: <?= $fecha($pedido['fecha_pedido'] ?? null) ?>
                                    </p>

                                </div>

                                <div class="lg:text-right">

                                    <p class="text-xs uppercase font-bold text-gray-500">
                                        Total del pedido
                                    </p>

                                    <p class="display text-2xl font-extrabold text-[#477a45]">
                                        <?= $dinero($pedido['total'] ?? 0) ?>
                                    </p>

                                </div>

                            </div>

                        </div>


                        <div class="p-5 grid grid-cols-1 lg:grid-cols-2 gap-6">

                            <!-- CLIENTE -->

                            <div>

                                <h4 class="display uppercase font-bold text-sm mb-3">
                                    Datos del cliente
                                </h4>

                                <div class="space-y-2 text-sm">

                                    <p>
                                        <strong>Nombre:</strong>
                                        <?= htmlspecialchars($pedido['nombre_completo'] ?? 'No disponible', ENT_QUOTES, 'UTF-8') ?>
                                    </p>

                                    <p>
                                        <strong>Correo:</strong>
                                        <?= htmlspecialchars($pedido['correo'] ?? 'No disponible', ENT_QUOTES, 'UTF-8') ?>
                                    </p>

                                    <p>
                                        <strong>Teléfono:</strong>
                                        <?= htmlspecialchars($pedido['telefono'] ?? 'No disponible', ENT_QUOTES, 'UTF-8') ?>
                                    </p>

                                    <p>
                                        <strong>Servicio:</strong>
                                        <?= htmlspecialchars($tipoServicio, ENT_QUOTES, 'UTF-8') ?>
                                    </p>

                                </div>

                                <?php if (!empty($pedido['observaciones'])): ?>

                                    <div class="mt-4 p-3 bg-[#fff8f0] border border-gray-300 rounded-lg">

                                        <p class="text-xs font-bold uppercase mb-1">
                                            Observaciones del cliente
                                        </p>

                                        <p class="text-sm">
                                            <?= nl2br(htmlspecialchars($pedido['observaciones'], ENT_QUOTES, 'UTF-8')) ?>
                                        </p>

                                    </div>

                                <?php endif; ?>

                            </div>


                            <!-- PAGO Y ENTREGA -->

                            <div>

                                <h4 class="display uppercase font-bold text-sm mb-3">
                                    Pago y entrega
                                </h4>

                                <div class="space-y-2 text-sm">

                                    <p>
                                        <strong>Método de pago:</strong>
                                        <?= htmlspecialchars($pedido['metodo_pago'] ?? 'No disponible', ENT_QUOTES, 'UTF-8') ?>
                                    </p>

                                    <p class="flex flex-wrap items-center gap-2">
                                        <strong>Estado del pago:</strong>
                                        <span class="px-3 py-1 rounded-full text-xs font-bold <?= $pagoVisual[1] ?>">
                                            <?= htmlspecialchars($pagoVisual[0], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </p>

                                    <p>
                                        <strong>Importe pagado:</strong>
                                        <?= $dinero($pedido['monto_pagado'] ?? 0) ?>
                                    </p>

                                    <p>
                                        <strong>Fecha de pago:</strong>
                                        <?= $fecha($pedido['fecha_pago'] ?? null) ?>
                                    </p>

                                    <p>
                                        <strong>Tipo de servicio:</strong>
                                        <?= htmlspecialchars($tipoServicio, ENT_QUOTES, 'UTF-8') ?>
                                    </p>

                                    <?php if ($tipoServicio === 'recoger'): ?>
    <p class="mt-2 text-sm font-bold text-green-800">
        Recoger en tienda: no requiere dirección domiciliaria.
    </p>
<?php elseif (empty($pedido['calle_numero'])): ?>
    <p class="mt-2 text-sm font-bold text-red-700">
        Entrega a domicilio sin dirección registrada. Revisar con el cliente.
    </p>
<?php endif; ?>

<?php if (!empty($pedido['calle_numero'])): ?>

                                        <div class="mt-3 p-3 bg-[#fff8f0] border border-gray-300 rounded-lg">

                                            <p class="font-bold mb-1">
                                                Dirección de entrega
                                            </p>

                                            <p>
                                                <?= htmlspecialchars($pedido['calle_numero'], ENT_QUOTES, 'UTF-8') ?>
                                            </p>

                                            <p>
                                                <?= htmlspecialchars($pedido['colonia'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                            </p>

                                            <p>
                                                <?= htmlspecialchars($pedido['ciudad'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                            </p>

                                            <p>
                                                C.P. <?= htmlspecialchars($pedido['codigo_postal'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                            </p>

                                            <?php if (!empty($pedido['referencias'])): ?>
                                                <p class="mt-2 text-gray-600">
                                                    <strong>Referencias:</strong>
                                                    <?= htmlspecialchars($pedido['referencias'], ENT_QUOTES, 'UTF-8') ?>
                                                </p>
                                            <?php endif; ?>

                                        </div>

                                    <?php endif; ?>

                                </div>

                                <?php if ($pagoPendiente): ?>
                                    <form action="index.php?accion=admin-confirmar-pago"
                                          method="POST"
                                          class="mt-4 p-4 bg-[#fff8f0] border-2 border-[#211d1a] rounded-lg"
                                          onsubmit="return confirm('¿Confirmas que recibiste y verificaste el pago del pedido #<?= $idPedido ?>?');">

                                        <input type="hidden"
                                               name="id_pedido"
                                               value="<?= $idPedido ?>">

                                        <p class="text-sm mb-3">
                                            Verifica el comprobante o la recepción del efectivo antes de confirmar.
                                        </p>

                                        <button type="submit"
                                                class="w-full bg-[#477a45] text-white border-2 border-[#211d1a] rounded-lg py-2.5 px-4 font-bold text-xs uppercase shadow-[2px_2px_0px_#211d1a] hover:opacity-90">
                                            Confirmar pago
                                        </button>
                                    </form>
                                <?php elseif ($estadoPago === 'pagado'): ?>
                                    <div class="mt-4 p-3 bg-[#e4f3df] border border-[#477a45] rounded-lg text-sm font-bold text-[#315b30]">
                                        <span aria-hidden="true">✓</span>
                                        Pago confirmado
                                    </div>
                                <?php endif; ?>

                            </div>

                        </div>


                        <!-- PRODUCTOS -->

                        <div class="px-5 pb-5">

                            <h4 class="display uppercase font-bold text-sm mb-3">
                                Productos del pedido
                            </h4>

                            <div class="overflow-x-auto">

                                <table class="w-full text-sm">

                                    <thead>
                                        <tr class="bg-[#f8edd8] text-left">
                                            <th class="p-3">Producto</th>
                                            <th class="p-3">Cantidad</th>
                                            <th class="p-3">Precio</th>
                                            <th class="p-3">Subtotal</th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                        <?php foreach ($productos as $producto): ?>

                                            <tr class="border-b border-gray-200">

                                                <td class="p-3 font-semibold">
                                                    <?= htmlspecialchars($producto['nombre'] ?? 'Producto', ENT_QUOTES, 'UTF-8') ?>
                                                </td>

                                                <td class="p-3">
                                                    <?= (int) ($producto['cantidad'] ?? 0) ?>
                                                </td>

                                                <td class="p-3">
                                                    <?= $dinero($producto['precio_unitario'] ?? 0) ?>
                                                </td>

                                                <td class="p-3 font-bold">
                                                    <?= $dinero($producto['subtotal_linea'] ?? 0) ?>
                                                </td>

                                            </tr>

                                        <?php endforeach; ?>

                                    </tbody>

                                </table>

                            </div>

                            <div class="flex justify-end mt-4">

                                <div class="text-right space-y-1">

                                    <p class="text-sm text-gray-500">
                                        Subtotal: <?= $dinero($pedido['subtotal'] ?? 0) ?>
                                    </p>

                                    <p class="display text-xl font-extrabold">
                                        Total: <?= $dinero($pedido['total'] ?? 0) ?>
                                    </p>

                                </div>

                            </div>

                        </div>


                        <!-- ADMINISTRACIÓN DE ESTADOS -->

                        <div class="bg-[#f8edd8] border-t-2 border-[#211d1a] p-5">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                                <!-- ESTADO DEL PEDIDO -->

                                <form action="index.php?accion=admin-actualizar-pedido"
                                      method="POST"
                                      class="flex flex-col gap-2">

                                    <input type="hidden" name="id_pedido" value="<?= $idPedido ?>">

                                    <label class="text-xs font-extrabold uppercase">
                                        Estado del pedido
                                    </label>

                                    <select name="estado_pedido"
                                            class="w-full bg-white border-2 border-[#211d1a] rounded-lg p-3 text-sm"
                                            required>

                                        <?php foreach ($estadoPedido as $valor => $info): ?>

                                            <option value="<?= $valor ?>"
                                                <?= $estado === $valor ? 'selected' : '' ?>>

                                                <?= htmlspecialchars($info[0], ENT_QUOTES, 'UTF-8') ?>

                                            </option>

                                        <?php endforeach; ?>

                                    </select>

                                    <button type="submit"
                                            class="bg-[#477a45] text-white border-2 border-[#211d1a] rounded-lg py-2.5 px-4 font-bold text-xs uppercase shadow-[2px_2px_0px_#211d1a] hover:opacity-90">

                                        Actualizar pedido

                                    </button>

                                </form>


                                <!-- ESTADO DE ENTREGA -->

                                <?php if (!empty($pedido['id_entrega'])): ?>

                                    <form action="index.php?accion=admin-actualizar-entrega"
                                          method="POST"
                                          class="flex flex-col gap-2">

                                        <input type="hidden"
                                               name="id_entrega"
                                               value="<?= (int) $pedido['id_entrega'] ?>">

                                        <label class="text-xs font-extrabold uppercase">
                                            Estado de entrega
                                        </label>

                                        <select name="estado_entrega"
                                                class="w-full bg-white border-2 border-[#211d1a] rounded-lg p-3 text-sm"
                                                required>

                                            <?php foreach ($estadoEntrega as $valor => $info): ?>

                                                <option value="<?= $valor ?>"
                                                    <?= ($pedido['estado_entrega'] ?? '') === $valor ? 'selected' : '' ?>>

                                                    <?= htmlspecialchars($info[0], ENT_QUOTES, 'UTF-8') ?>

                                                </option>

                                            <?php endforeach; ?>

                                        </select>

                                        <button type="submit"
                                                class="bg-[#e97832] text-white border-2 border-[#211d1a] rounded-lg py-2.5 px-4 font-bold text-xs uppercase shadow-[2px_2px_0px_#211d1a] hover:opacity-90">

                                            Actualizar entrega

                                        </button>

                                    </form>

                                <?php else: ?>

                                    <div class="flex flex-col justify-center">

                                        <p class="text-xs font-extrabold uppercase">
                                            Entrega
                                        </p>

                                        <p class="text-sm text-gray-600 mt-2">
                                            Este pedido no tiene un registro de entrega asociado.
                                        </p>

                                    </div>

                                <?php endif; ?>

                            </div>

                        </div>

                    </article>

                <?php endforeach; ?>

            <?php endif; ?>

            <div id="sinResultados" class="hidden pedido-card bg-white rounded-xl p-8 text-center">
                No se encontraron pedidos con esos filtros.
            </div>

        </section>

    </main>


    <!-- FILTRADO -->

    <script>
        document.addEventListener('DOMContentLoaded', () => {

            const buscar = document.getElementById('buscarPedido');
            const filtro = document.getElementById('filtroEstado');
            const tarjetas = document.querySelectorAll('.pedido-item');
            const sinResultados = document.getElementById('sinResultados');

            function filtrarPedidos() {

                const texto = buscar.value.trim().toLowerCase();
                const estado = filtro.value;

                let visibles = 0;

                tarjetas.forEach(tarjeta => {

                    const contenido = tarjeta.dataset.busqueda || '';
                    const estadoPedido = tarjeta.dataset.estado || '';

                    const coincideTexto = contenido.includes(texto);
                    const coincideEstado = !estado || estadoPedido === estado;

                    const mostrar = coincideTexto && coincideEstado;

                    tarjeta.classList.toggle('hidden', !mostrar);

                    if (mostrar) {
                        visibles++;
                    }

                });

                sinResultados.classList.toggle('hidden', visibles !== 0);

            }

            buscar.addEventListener('input', filtrarPedidos);
            filtro.addEventListener('change', filtrarPedidos);

        });
    </script>

</body>
</html>
