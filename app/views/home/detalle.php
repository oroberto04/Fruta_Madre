```php
<?php
$nombre = htmlspecialchars($producto['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
$categoria = htmlspecialchars($producto['nombre_categoria'] ?? '', ENT_QUOTES, 'UTF-8');
$descripcion = htmlspecialchars($producto['descripcion'] ?? '', ENT_QUOTES, 'UTF-8');
$ingredientes = htmlspecialchars($producto['ingredientes'] ?? '', ENT_QUOTES, 'UTF-8');
$imagen = $producto['imagen_url'] ?? '';
$precio = (float) ($producto['precio'] ?? 0);
$stock = (int) ($producto['stock_disponible'] ?? 0);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $nombre ?> | Fruta Madre</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background: #FFF4DF;
            color: #29231F;
        }

        .sombra {
            box-shadow: 5px 5px 0 #29231F;
        }
    </style>
</head>

<body class="min-h-screen">

    <header class="sticky top-0 z-20 bg-[#FFF9F0] border-b-4 border-[#477A45]">
        <div class="max-w-7xl mx-auto px-5 py-4 flex items-center justify-between gap-4">
            <a href="index.php" class="flex flex-col">
                <span class="text-xl font-black tracking-widest">FRUTA MADRE</span>
                <span class="text-sm font-extrabold text-[#D94F70]">
                    Te damos hasta para llevar
                </span>
            </a>

            <a href="index.php"
               class="bg-[#477A45] text-white px-4 py-2 rounded-lg font-extrabold">
                ← Volver al menú
            </a>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-5 py-10">

        <p class="text-sm font-extrabold uppercase tracking-widest text-[#D94F70] mb-4">
            <?= $categoria ?>
        </p>

        <section class="grid grid-cols-1 md:grid-cols-2 gap-10 items-start">

            <div class="bg-[#F8EBD5] rounded-2xl border-4 border-white sombra
                        aspect-square flex items-center justify-center overflow-hidden">

                <?php if (!empty($imagen)): ?>
                    <img
                        src="<?= htmlspecialchars($imagen, ENT_QUOTES, 'UTF-8') ?>"
                        alt="<?= $nombre ?>"
                        class="w-full h-full object-cover"
                    >
                <?php else: ?>
                    <span class="text-8xl" aria-label="Imagen pendiente">🍓</span>
                <?php endif; ?>

            </div>

            <div class="py-2">

                <p class="text-sm font-extrabold text-[#477A45] mb-3">
                    FRUTA FRESCA Y ANTOJOS
                </p>

                <h1 class="text-4xl md:text-5xl font-black leading-tight mb-5">
                    <?= $nombre ?>
                </h1>

                <p class="text-3xl font-black text-[#477A45] mb-6">
                    $<?= number_format($precio, 2) ?>
                    <span class="text-base">MXN</span>
                </p>

                <div class="border-y-2 border-dashed border-[#D8C8AF] py-5 mb-6">
                    <h2 class="font-black text-lg mb-2">¿Qué lleva?</h2>

                    <p class="text-[#62594F] leading-relaxed mb-4">
                        <?= $descripcion ?>
                    </p>

                    <p class="text-sm italic text-[#B85B32]">
                        <?= $ingredientes ?>
                    </p>
                </div>

                <p class="font-bold mb-6 <?= $stock > 0 ? 'text-[#477A45]' : 'text-[#B83B32]' ?>">
                    <?= $stock > 0 ? '✓ Disponible' : 'Agotado por ahora' ?>
                </p>

                <?php if ($stock > 0): ?>
                    <button
                        type="button"
                        disabled
                        title="El carrito se conectará en el siguiente módulo"
                        class="w-full bg-[#477A45] text-white px-6 py-4 rounded-xl
                               font-black text-lg sombra opacity-80 cursor-not-allowed">
                        🛒 Agregar al carrito
                    </button>

                    <p class="text-sm text-[#62594F] mt-3">
                        Estamos preparando el carrito de compras.
                    </p>
                <?php else: ?>
                    <button disabled
                        class="w-full bg-gray-400 text-white px-6 py-4 rounded-xl font-black">
                        Producto agotado
                    </button>
                <?php endif; ?>

            </div>

        </section>

    </main>

    <footer class="text-center px-5 py-8 border-t-2 border-dashed border-[#D8C8AF]">
        <p class="font-black">FRUTA MADRE</p>
        <p class="text-sm text-[#62594F]">Te damos hasta para llevar.</p>
    </footer>

</body>
</html>
```