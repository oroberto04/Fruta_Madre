
<?php
$total = $total ?? 0;
$productosCarrito = $productosCarrito ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi carrito | Fruta Madre</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-[#FFF4DF] text-[#29231F]">
    <header class="bg-[#477A45] px-6 py-5 text-white">
        <div class="mx-auto flex max-w-5xl items-center justify-between gap-4">
            <a href="index.php" class="text-xl font-bold">FRUTA MADRE</a>
            <a href="index.php" class="rounded-lg bg-white/15 px-4 py-2 hover:bg-white/25">
                Seguir comprando
            </a>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-10">
        <h1 class="mb-2 text-3xl font-bold">Mi carrito</h1>
        <p class="mb-8 text-[#6D6257]">Tus antojos, listos para llevar.</p>

        <?php if (empty($productosCarrito)): ?>
            <section class="rounded-2xl bg-white p-8 text-center shadow-sm">
                <div class="mb-4 text-5xl">🛒</div>
                <h2 class="mb-2 text-xl font-bold">Tu carrito está vacío</h2>
                <p class="mb-6 text-[#6D6257]">
                    ¡Échale un ojo al catálogo y agrega algo rico!
                </p>
                <a href="index.php"
                   class="inline-block rounded-xl bg-[#477A45] px-6 py-3 font-bold text-white hover:opacity-90">
                    Ver catálogo
                </a>
            </section>
        <?php else: ?>

            <div class="grid gap-6 md:grid-cols-[1fr_320px]">
                <section class="space-y-4">
                    <?php foreach ($productosCarrito as $producto): ?>
                        <article class="flex flex-col gap-4 rounded-2xl bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-start gap-4">
                                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-xl bg-[#FFF4DF] text-3xl">
                                    🍓
                                </div>

                                <div>
                                    <h2 class="font-bold">
                                        <?= htmlspecialchars($producto['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                    </h2>

                                    <p class="mt-1 text-sm text-[#6D6257]">
                                        $<?= number_format((float) $producto['precio'], 2) ?> MXN por unidad
                                    </p>

                                    <p class="mt-1 text-sm">
                                        Cantidad: <?= (int) $producto['cantidad'] ?>
                                    </p>

                                    <form action="index.php?accion=eliminar" method="POST" class="mt-2">
                                        <input
                                            type="hidden"
                                            name="id_producto"
                                            value="<?= (int) $producto['id_producto'] ?>"
                                        >
                                        <button type="submit" class="text-sm font-semibold text-[#B83B32] hover:underline">
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <p class="text-lg font-bold">
                                $<?= number_format((float) $producto['subtotal'], 2) ?> MXN
                            </p>
                        </article>
                    <?php endforeach; ?>
                </section>

                <aside class="h-fit rounded-2xl bg-white p-6 shadow-sm">
                    <h2 class="mb-5 text-xl font-bold">Resumen del pedido</h2>

                    <div class="flex items-center justify-between gap-4 border-b border-dashed border-[#D9CEBC] pb-4">
                        <span class="text-[#6D6257]">Productos</span>
                        <span><?= array_sum(array_column($productosCarrito, 'cantidad')) ?></span>
                    </div>

                    <div class="flex items-center justify-between gap-4 py-5">
                        <span class="font-bold">Total</span>
                        <span class="text-2xl font-bold text-[#477A45]">
                            $<?= number_format((float) $total, 2) ?>
                        </span>
                    </div>

                    <p class="mb-5 text-xs text-[#6D6257]">
                        El total corresponde a los productos agregados. Los costos de entrega, si aplican, se calcularán más adelante.
                    </p>

                    <button type="button" disabled
                            class="w-full cursor-not-allowed rounded-xl bg-gray-300 px-5 py-3 font-bold text-gray-600">
                        Continuar con la compra
                    </button>

                    <form action="index.php?accion=vaciar" method="POST" class="mt-3">
                        <button type="submit"
                                class="w-full rounded-xl border border-[#D9CEBC] px-5 py-3 font-semibold hover:bg-[#FFF4DF]">
                            Vaciar carrito
                        </button>
                    </form>
                </aside>
            </div>

        <?php endif; ?>
    </main>
</body>
</html>
