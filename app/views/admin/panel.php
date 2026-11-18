
<?php

$productosActivos = (int) ($resumen['productos_activos'] ?? 0);
$categoriasActivas = (int) ($resumen['categorias_activas'] ?? 0);
$clientes = (int) ($resumen['clientes'] ?? 0);
$pedidos = (int) ($resumen['pedidos'] ?? 0);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Administración | Fruta Madre</title>

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

        .admin-card {
            border: 2px solid #211d1a;
            box-shadow: 3px 3px 0 #211d1a;
        }
    </style>
</head>

<body class="bg-[#fff8f0] text-[#211d1a]">

    <header class="bg-[#477a45] text-white border-b-2 border-[#211d1a]">
        <div class="max-w-7xl mx-auto px-5 py-5 flex flex-wrap items-center justify-between gap-4">

            <div>
                <h1 class="display text-2xl font-extrabold uppercase">
                    FRUTA MADRE
                </h1>

                <p class="text-xs uppercase tracking-widest text-white/80">
                    Centro de administración
                </p>
            </div>

            <div class="flex items-center gap-3">
                <span class="text-sm font-semibold">
                    <?= htmlspecialchars($_SESSION['usuario']['nombre_completo'] ?? 'Administrador', ENT_QUOTES, 'UTF-8') ?>
                </span>

                <a href="index.php?accion=logout"
                   class="bg-white text-[#211d1a] px-4 py-2 rounded-lg font-bold text-sm border-2 border-[#211d1a] shadow-[2px_2px_0px_#211d1a]">
                    Cerrar sesión
                </a>
            </div>

        </div>

        <div class="h-2 flex">
            <div class="flex-1 bg-[#477a45]"></div>
            <div class="flex-1 bg-[#d94f70]"></div>
            <div class="flex-1 bg-[#e8b93f]"></div>
            <div class="flex-1 bg-[#e97832]"></div>
        </div>
    </header>


    <main class="max-w-7xl mx-auto px-5 py-10">
<!-- NAVEGACION STITCH 36/37 -->
<nav class="mb-8 flex flex-wrap gap-2" aria-label="Módulos administrativos de Fruta Madre">
 <a href="index.php?accion=admin-productos" class="bg-[#477a45] text-white px-4 py-2 rounded-lg border-2 border-[#211d1a] font-bold">🍉 Productos Stitch</a>
 <a href="index.php?accion=admin-categorias-stitch" class="bg-white px-4 py-2 rounded-lg border-2 border-[#211d1a] font-bold">🍓 Categorías</a>
 <a href="index.php?accion=admin-paquetes-stitch" class="bg-white px-4 py-2 rounded-lg border-2 border-[#211d1a] font-bold">🎉 Paquetes</a>
 <a href="index.php?accion=admin-carritos-stitch" class="bg-white px-4 py-2 rounded-lg border-2 border-[#211d1a] font-bold">🛺 Carritos</a>
 <a href="index.php?accion=admin-reservas-stitch" class="bg-white px-4 py-2 rounded-lg border-2 border-[#211d1a] font-bold">📅 Reservas</a>
 <a href="index.php?accion=admin-clientes-stitch" class="bg-white px-4 py-2 rounded-lg border-2 border-[#211d1a] font-bold">👥 Clientes</a>
 <a href="index.php?accion=admin-inventario-stitch" class="bg-white px-4 py-2 rounded-lg border-2 border-[#211d1a] font-bold">📦 Inventario</a>
 <a href="index.php?accion=admin-reportes-stitch" class="bg-white px-4 py-2 rounded-lg border-2 border-[#211d1a] font-bold">📈 Reportes</a>
</nav>

        <section class="mb-9">

            <span class="inline-block bg-[#e8b93f] px-3 py-1 rounded-full text-xs font-extrabold uppercase border border-[#211d1a]">
                Administración
            </span>

            <h2 class="display text-3xl md:text-4xl font-extrabold uppercase mt-3">
                Panel principal
            </h2>

            <p class="text-[#41493f] mt-2">
                Administra las operaciones de Fruta Madre desde un solo lugar.
            </p>

        </section>


        <!-- INDICADORES -->

        <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-10">

            <article class="admin-card bg-white rounded-xl p-5">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-bold text-gray-500 uppercase">
                        Productos activos
                    </span>
                    <span class="text-2xl">🍉</span>
                </div>

                <p class="display text-4xl font-extrabold mt-4">
                    <?= $productosActivos ?>
                </p>
            </article>


            <article class="admin-card bg-[#fce8ed] rounded-xl p-5">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-bold text-gray-600 uppercase">
                        Categorías
                    </span>
                    <span class="text-2xl">🍓</span>
                </div>

                <p class="display text-4xl font-extrabold mt-4">
                    <?= $categoriasActivas ?>
                </p>
            </article>


            <article class="admin-card bg-[#fff1cc] rounded-xl p-5">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-bold text-gray-600 uppercase">
                        Clientes registrados
                    </span>
                    <span class="text-2xl">👥</span>
                </div>

                <p class="display text-4xl font-extrabold mt-4">
                    <?= $clientes ?>
                </p>
            </article>


            <article class="admin-card bg-[#e4f3df] rounded-xl p-5">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-bold text-gray-600 uppercase">
                        Pedidos recibidos
                    </span>
                    <span class="text-2xl">🛍️</span>
                </div>

                <p class="display text-4xl font-extrabold mt-4">
                    <?= $pedidos ?>
                </p>
            </article>

        </section>


        <!-- MÓDULOS ADMINISTRATIVOS -->

        <section class="mb-5">

            <h3 class="display text-xl font-extrabold uppercase">
                Administración del negocio
            </h3>

            <p class="text-sm text-gray-500 mt-1">
                Selecciona el módulo que deseas administrar.
            </p>

        </section>


        <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">

            <!-- PEDIDOS -->

            <a href="index.php?accion=admin-pedidos"
               class="admin-card bg-white rounded-xl p-6 hover:bg-[#f8edd8] transition-colors">

                <div class="w-14 h-14 rounded-xl bg-[#e4f3df] border-2 border-[#211d1a] flex items-center justify-center text-3xl mb-5">
                    🛍️
                </div>

                <h4 class="display text-xl font-extrabold uppercase">
                    Gestión de pedidos
                </h4>

                <p class="text-sm text-gray-600 mt-2">
                    Consulta las compras, revisa los productos y administra los estados de preparación y entrega.
                </p>

                <span class="inline-flex items-center gap-2 mt-5 text-[#477a45] font-extrabold text-sm uppercase">
                    Administrar pedidos
                    <span>→</span>
                </span>

            </a>


            <!-- PRODUCTOS -->

            <div class="admin-card bg-white rounded-xl p-6">

                <div class="w-14 h-14 rounded-xl bg-[#fff1cc] border-2 border-[#211d1a] flex items-center justify-center text-3xl mb-5">
                    🍉
                </div>

                <h4 class="display text-xl font-extrabold uppercase">
                    Productos
                </h4>

                <p class="text-sm text-gray-600 mt-2">
                    Consulta y administra el catálogo de productos de Fruta Madre.
                </p>

                <span class="inline-block mt-5 text-gray-400 font-bold text-sm uppercase">
                    <a href="index.php?accion=admin-productos" class="text-[#477a45] underline">Administrar productos</a>
                </span>

            </div>


            <!-- CATEGORÍAS -->

            <div class="admin-card bg-white rounded-xl p-6">

                <div class="w-14 h-14 rounded-xl bg-[#fce8ed] border-2 border-[#211d1a] flex items-center justify-center text-3xl mb-5">
                    🍓
                </div>

                <h4 class="display text-xl font-extrabold uppercase">
                    Categorías
                </h4>

                <p class="text-sm text-gray-600 mt-2">
                    Organiza las categorías que aparecen en el catálogo.
                </p>

                <span class="inline-block mt-5 text-gray-400 font-bold text-sm uppercase">
                    <a href="index.php?accion=admin-categorias-stitch" class="text-[#477a45] underline">Administrar categorías</a>
                </span>

            </div>


            <!-- CLIENTES -->

            <div class="admin-card bg-white rounded-xl p-6">

                <div class="w-14 h-14 rounded-xl bg-[#e4f3df] border-2 border-[#211d1a] flex items-center justify-center text-3xl mb-5">
                    👥
                </div>

                <h4 class="display text-xl font-extrabold uppercase">
                    Clientes
                </h4>

                <p class="text-sm text-gray-600 mt-2">
                    Consulta la información de los clientes registrados.
                </p>

                <span class="inline-block mt-5 text-gray-400 font-bold text-sm uppercase">
                    <a href="index.php?accion=admin-clientes-stitch" class="text-[#477a45] underline">Consultar clientes</a>
                </span>

            </div>


            <!-- INVENTARIO -->

            <div class="admin-card bg-white rounded-xl p-6">

                <div class="w-14 h-14 rounded-xl bg-[#fff1cc] border-2 border-[#211d1a] flex items-center justify-center text-3xl mb-5">
                    📦
                </div>

                <h4 class="display text-xl font-extrabold uppercase">
                    Inventario
                </h4>

                <p class="text-sm text-gray-600 mt-2">
                    Consulta existencias y actualiza el stock disponible de los productos.
                </p>

                <span class="inline-block mt-5 text-gray-400 font-bold text-sm uppercase">
                    <a href="index.php?accion=admin-inventario-stitch" class="text-[#477a45] underline">Gestionar inventario</a>
                </span>

            </div>


            <!-- REPORTES -->

            <div class="admin-card bg-white rounded-xl p-6">

                <div class="w-14 h-14 rounded-xl bg-[#fce8ed] border-2 border-[#211d1a] flex items-center justify-center text-3xl mb-5">
                    📊
                </div>

                <h4 class="display text-xl font-extrabold uppercase">
                    Reportes
                </h4>

                <p class="text-sm text-gray-600 mt-2">
                    Consulta información y estadísticas de las operaciones.
                </p>

                <span class="inline-block mt-5 text-gray-400 font-bold text-sm uppercase">
                    <a href="index.php?accion=admin-reportes-stitch" class="text-[#477a45] underline">Ver reportes</a>
                </span>

            </div>

        </section>

    </main>


    <footer class="border-t-2 border-[#211d1a] bg-[#f8edd8] mt-10">
        <div class="max-w-7xl mx-auto px-5 py-5 text-center text-xs text-gray-600">
            © 2026 FRUTA MADRE · Centro de administración
        </div>
    </footer>

</body>
</html>
