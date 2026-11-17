
<?php
$productos = $productos ?? [];
$cantidadCarrito = array_sum($_SESSION['carrito'] ?? []);
$categorias = [];

foreach ($productos as $p) {
    $cat = trim((string)($p['nombre_categoria'] ?? ''));
    if ($cat !== '') {
        $categorias[$cat] = $cat;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FRUTA MADRE | Catálogo</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Syne:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

<script>
tailwind.config = {
  theme: {
    extend: {
      colors: {
        "surface-container-lowest":"#ffffff",
        "surface-container":"#f8edd8",
        "background":"#fff8f0",
        "primary":"#2f612f",
        "primary-container":"#477a45",
        "secondary":"#ac2d50",
        "secondary-container":"#fc6a8a",
        "tertiary":"#903e00",
        "tertiary-container":"#b55107",
        "on-surface":"#201b0f",
        "on-surface-variant":"#41493f",
        "surface-container-high":"#f2e7d2",
        "surface-container-low":"#fdf2de",
        "surface":"#fff8f0",
        "outline-variant":"#c1c9bc",
        "secondary-fixed":"#ffd9de",
        "primary-fixed":"#b8f1b0"
      },
      spacing: {
        "gutter":"1.25rem",
        "margin-desktop":"3rem",
        "space-xs":"0.25rem",
        "space-sm":"0.5rem",
        "space-md":"1rem",
        "space-lg":"1.5rem",
        "space-xl":"2.5rem"
      },
      fontFamily: {
        "body-md":["DM Sans"],
        "body-sm":["DM Sans"],
        "label-rotulo":["Syne"],
        "headline-lg":["Syne"],
        "headline-sm":["Syne"],
        "display-hero":["Syne"]
      },
      fontSize: {
        "body-md":["16px",{lineHeight:"24px"}],
        "body-sm":["14px",{lineHeight:"20px"}],
        "label-sm":["12px",{lineHeight:"16px",letterSpacing:"0.03em",fontWeight:"700"}],
        "label-md":["14px",{lineHeight:"18px",letterSpacing:"0.02em",fontWeight:"700"}],
        "label-rotulo":["14px",{lineHeight:"18px",letterSpacing:"0.06em",fontWeight:"800"}],
        "headline-sm":["22px",{lineHeight:"28px",fontWeight:"600"}],
        "headline-lg":["40px",{lineHeight:"46px",letterSpacing:"-0.02em",fontWeight:"700"}],
        "display-hero":["56px",{lineHeight:"60px",letterSpacing:"-0.03em",fontWeight:"800"}]
      }
    }
  }
};
</script>

<style>
html { scroll-behavior:smooth; }
body { margin:0; background:#fff8f0; color:#201b0f; font-family:'DM Sans',sans-serif; }
h1,h2,h3,.font-label-rotulo,.font-headline-lg,.font-headline-sm { font-family:'Syne',sans-serif; }
.material-symbols-outlined { font-variation-settings:'FILL' 0,'wght' 500; }
.product-card[hidden] { display:none!important; }
.category-pill { white-space:nowrap; }
#categoryTabs { scrollbar-width:none; }
#categoryTabs::-webkit-scrollbar { display:none; }
.product-card img { transition:transform .3s ease; }
.product-card:hover img { transform:scale(1.04); }
@media(max-width:639px) {
  .catalog-title { font-size:38px; line-height:1.08; }
}
</style>
</head>

<body class="bg-background font-body-md text-on-surface antialiased">

<!-- ENCABEZADO DE CATÁLOGO -->
<header class="sticky top-0 z-50 bg-surface/95 backdrop-blur-md shadow-[0_1px_8px_rgba(0,0,0,0.04)]">
  <div class="min-h-20 w-full px-gutter lg:px-margin-desktop flex flex-wrap items-center justify-between gap-space-md py-3">

    <a href="index.php" class="flex items-center gap-space-md shrink-0">
      <div class="flex flex-col">
        <span class="font-label-rotulo text-label-rotulo uppercase text-on-surface tracking-wider leading-none text-lg">FRUTA MADRE</span>
        <span class="text-label-sm text-secondary font-bold leading-tight mt-1">Te damos hasta para llevar</span>
      </div>
    </a>

    <nav class="flex flex-wrap items-center gap-space-xs bg-surface-container-low p-space-xs rounded-lg">
      <a href="index.php" class="text-on-surface-variant hover:text-on-surface px-space-md py-2 rounded-lg font-label-md text-label-md transition-colors">Inicio</a>
      <a href="index.php?accion=catalogo" class="bg-primary text-white font-bold rounded-lg shadow-[2px_2px_0px_#201b0f] px-space-md py-2 font-label-md text-label-md">Catálogo</a>
      <a href="index.php?accion=eventos" class="text-on-surface-variant hover:text-on-surface px-space-md py-2 rounded-lg font-label-md text-label-md transition-colors">Eventos</a>
      <a href="index.php?accion=mis-pedidos" class="text-on-surface-variant hover:text-on-surface px-space-md py-2 rounded-lg font-label-md text-label-md transition-colors">Mis pedidos</a>
    </nav>

    <div class="flex items-center gap-space-sm shrink-0">
      <form action="index.php" method="GET" class="hidden md:flex items-center bg-surface-container-lowest rounded-lg px-space-md py-space-xs border border-outline-variant/30">
        <span class="material-symbols-outlined text-on-surface-variant text-[20px] mr-space-xs">search</span>
        <input name="buscar" class="bg-transparent text-on-surface placeholder:text-on-surface-variant/60 text-body-sm focus:outline-none w-36 lg:w-48" placeholder="Buscar antojo..." type="search">
      </form>

      <a href="index.php?accion=carrito" aria-label="Carrito" class="relative p-space-sm rounded-lg bg-surface-container-low hover:bg-surface-container flex items-center justify-center">
        <span class="material-symbols-outlined text-[22px]">shopping_bag</span>
        <span class="absolute -top-1 -right-1 bg-secondary text-white text-label-sm w-5 h-5 rounded-full flex items-center justify-center"><?= (int)$cantidadCarrito ?></span>
      </a>

      <a href="index.php?accion=cuenta" aria-label="Mi cuenta" class="p-space-sm rounded-lg bg-surface-container-low flex items-center justify-center">
        <span class="material-symbols-outlined text-[22px]">person</span>
      </a>
    </div>
  </div>

  <div class="h-1.5 w-full flex">
    <div class="flex-1 bg-primary-container"></div>
    <div class="flex-1 bg-secondary-container"></div>
    <div class="flex-1 bg-surface-container-high"></div>
    <div class="flex-1 bg-tertiary-container"></div>
  </div>
</header>

<main class="w-full bg-background min-h-screen">

  <!-- CINTA INFORMATIVA ORIGINAL -->
  <div class="w-full bg-surface-container py-space-xs px-gutter lg:px-margin-desktop flex items-center justify-between gap-4 overflow-hidden">
    <span class="font-label-rotulo text-label-sm text-tertiary uppercase flex items-center gap-1 shrink-0">
      <span class="material-symbols-outlined text-[16px]">local_shipping</span>
      ENTREGAS EXPRÉS
    </span>
    <div class="hidden sm:flex items-center gap-space-md font-label-rotulo text-label-sm uppercase text-on-surface-variant">
      <span>• FRUTA FRESCA DEL DÍA</span>
      <span>• CHAMOY ARTESANAL DE LA CASA</span>
      <span>• EMPACADO AL MOMENTO</span>
    </div>
  </div>

  <!-- BANNER ROTULERO -->
  <section class="w-full px-gutter lg:px-margin-desktop pt-space-lg pb-space-md">
    <nav aria-label="Ruta de navegación" class="flex items-center gap-space-xs mb-space-sm">
      <a href="index.php" class="text-body-sm text-on-surface-variant hover:text-primary">Inicio</a>
      <span class="text-outline-variant">/</span>
      <span class="text-label-md text-primary font-bold">Catálogo</span>
    </nav>

    <div class="relative bg-surface-container-low rounded-xl p-space-lg lg:p-space-xl overflow-hidden shadow-sm border border-outline-variant/30">
      <div class="absolute -right-12 -bottom-12 w-64 h-64 rounded-full bg-secondary-fixed opacity-40 blur-2xl pointer-events-none"></div>
      <div class="absolute right-1/4 -top-8 w-48 h-48 rounded-full bg-primary-fixed opacity-30 blur-2xl pointer-events-none"></div>

      <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-space-lg">
        <div class="max-w-3xl">
          <div class="flex flex-wrap items-center gap-space-xs mb-space-sm">
            <span class="inline-flex items-center gap-1 bg-secondary text-white px-space-md py-1 rounded-full font-label-rotulo text-label-sm uppercase shadow-sm">
              <span class="material-symbols-outlined text-[16px]">storefront</span>
              EL PUESTO DIGITAL
            </span>
            <span class="inline-flex items-center gap-1 bg-primary text-white px-space-md py-1 rounded-full font-label-rotulo text-label-sm uppercase shadow-sm">
              <span class="material-symbols-outlined text-[16px]">nutrition</span>
              RECIÉN PREPARADO
            </span>
          </div>

          <h1 class="catalog-title font-display-hero text-display-hero uppercase text-on-surface leading-none mb-space-sm">
            ÉCHALE OJO AL<br>
            <span class="text-secondary">MENÚ, JEFE.</span>
          </h1>

          <p class="text-body-md text-on-surface-variant max-w-2xl">
            Hay de todo. Tú nomás decide qué se te antoja: fruta bien helada con chilito y chamoy, botanitas crujientes, pasteles caseros y detallitos frescos.
          </p>
        </div>

        <div class="flex flex-col sm:items-end gap-space-sm shrink-0">
          <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-md flex items-center gap-space-sm border border-outline-variant/30 transform -rotate-1 hover:rotate-0 transition-transform">
            <div class="w-12 h-12 rounded-full bg-primary flex items-center justify-center text-white">
              <span class="material-symbols-outlined text-[26px]">nutrition</span>
            </div>
            <div>
              <p class="font-label-rotulo text-label-rotulo uppercase text-primary leading-none">PUESTO MATRIZ</p>
              <p class="text-body-sm text-on-surface-variant mt-1">Fruta pelada hoy mismo</p>
            </div>
          </div>
          <div class="bg-secondary-fixed p-space-xs px-space-md rounded-lg transform rotate-2 flex items-center gap-1 shadow-sm border border-secondary/20">
            <span class="font-label-rotulo text-label-sm text-on-surface uppercase">🔥 Con o sin piquín tú mandas</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- BUSCADOR, CATEGORÍAS Y FILTROS -->
  <section class="w-full px-gutter lg:px-margin-desktop py-space-sm">
    <div class="bg-surface-container-lowest rounded-xl p-space-md shadow-sm border border-outline-variant/30 flex flex-col gap-space-md">

      <form id="searchForm" class="flex flex-col sm:flex-row items-center gap-space-sm">
        <div class="relative w-full flex-1">
          <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-[24px]">search</span>
          <input class="w-full bg-surface-container-low text-on-surface text-body-md pl-12 pr-space-md py-space-sm rounded-lg border border-transparent focus:border-primary focus:bg-surface-container transition-all focus:outline-none" id="searchInput" placeholder="¿Qué se te antojó? (ej. Mango, Chamoy, Tres Leches...)" type="search">
        </div>
        <button type="submit" class="w-full sm:w-auto bg-primary text-white px-space-xl py-space-sm rounded-lg font-label-rotulo text-label-md uppercase tracking-wider hover:bg-primary-container transition-all shadow-sm shrink-0 flex items-center justify-center gap-2">
          <span class="material-symbols-outlined text-[20px]">manage_search</span>
          Buscar
        </button>
      </form>

      <div class="flex items-center gap-space-xs overflow-x-auto pb-1" id="categoryTabs">
        <button type="button" class="category-pill px-space-md py-space-xs rounded-full font-label-rotulo text-label-rotulo uppercase tracking-wide shrink-0 bg-primary text-white shadow-sm" data-category="todos">Todos los antojos</button>
        <?php foreach ($categorias as $cat): ?>
          <button type="button" class="category-pill px-space-md py-space-xs rounded-full font-label-rotulo text-label-rotulo uppercase tracking-wide shrink-0 bg-surface-container text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high" data-category="<?= htmlspecialchars(mb_strtolower($cat, 'UTF-8'), ENT_QUOTES, 'UTF-8') ?>">
            <?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?>
          </button>
        <?php endforeach; ?>
      </div>

      <div class="flex flex-wrap items-center justify-between gap-space-sm pt-space-xs border-t border-surface-container/50">
        <div class="flex flex-wrap items-center gap-space-xs">
          <select class="bg-surface-container text-on-surface text-body-sm px-space-md py-2 rounded-lg cursor-pointer border border-transparent" id="filterPrice">
            <option value="all">Precio: Todos</option>
            <option value="under80">Menos de $80 MXN</option>
            <option value="80to150">$80 - $150 MXN</option>
            <option value="over150">Más de $150 MXN</option>
          </select>

          <select class="bg-surface-container text-on-surface text-body-sm px-space-md py-2 rounded-lg cursor-pointer border border-transparent" id="filterStock">
            <option value="all">Disponibilidad: Todos</option>
            <option value="in-stock">Disponibles ahora</option>
            <option value="out-of-stock">Mostrar agotados</option>
          </select>

          <select class="bg-surface-container text-on-surface text-body-sm px-space-md py-2 rounded-lg cursor-pointer border border-transparent" id="sortBy">
            <option value="popular">Ordenar: Recomendados</option>
            <option value="price-asc">Menor precio</option>
            <option value="price-desc">Mayor precio</option>
          </select>
        </div>

        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full bg-primary"></span>
          <span class="font-label-rotulo text-label-sm text-on-surface-variant uppercase" id="resultsCounter">
            Mostrando <?= count($productos) ?> antojitos frescos
          </span>
        </div>
      </div>
    </div>
  </section>

  <!-- CUADRÍCULA DE PRODUCTOS: DATOS REALES DE POSTGRESQL -->
  <section class="w-full px-gutter lg:px-margin-desktop py-space-md">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-gutter-desktop" id="productsGrid">

      <?php foreach ($productos as $producto):
        $id = (int)($producto['id_producto'] ?? 0);
        $nombre = (string)($producto['nombre'] ?? 'Producto');
        $descripcion = (string)($producto['descripcion'] ?? '');
        $categoria = (string)($producto['nombre_categoria'] ?? '');
        $precio = (float)($producto['precio'] ?? 0);
        $stock = (int)($producto['stock_disponible'] ?? 0);
        $imagen = trim((string)($producto['imagen_url'] ?? ''));
        $catSlug = mb_strtolower($categoria, 'UTF-8');
      ?>
      <article class="product-card group bg-surface-container-lowest rounded-xl p-space-sm shadow-sm flex flex-col justify-between transition-all hover:shadow-md hover:-translate-y-0.5 border border-outline-variant/30 relative overflow-hidden"
        data-category="<?= htmlspecialchars($catSlug, ENT_QUOTES, 'UTF-8') ?>"
        data-price="<?= $precio ?>"
        data-stock="<?= $stock > 0 ? 'in-stock' : 'out-of-stock' ?>"
        data-name="<?= htmlspecialchars(mb_strtolower($nombre, 'UTF-8'), ENT_QUOTES, 'UTF-8') ?>">

        <div class="absolute top-space-md left-space-md z-20">
          <span class="<?= $stock > 0 ? 'bg-secondary text-white' : 'bg-surface-container text-on-surface-variant' ?> font-label-rotulo text-label-sm uppercase px-space-md py-1 rounded-full shadow-md flex items-center gap-1">
            <span class="material-symbols-outlined text-[14px]"><?= $stock > 0 ? 'local_fire_department' : 'event_busy' ?></span>
            <?= $stock > 0 ? 'ANTOJO DISPONIBLE' : 'AGOTADO' ?>
          </span>
        </div>

        <div>
          <a href="index.php?producto=<?= $id ?>" class="block relative w-full aspect-square rounded-lg overflow-hidden bg-surface-container mb-space-sm">
            <?php if ($imagen !== ''): ?>
              <img alt="<?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" src="<?= htmlspecialchars($imagen, ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
            <?php else: ?>
              <div class="w-full h-full flex flex-col items-center justify-center text-primary gap-2">
                <span class="material-symbols-outlined text-6xl">nutrition</span>
                <span class="font-label-rotulo text-label-sm uppercase">Fruta Madre</span>
              </div>
            <?php endif; ?>
            <span class="absolute bottom-2 right-2 bg-surface-container-lowest/90 backdrop-blur-sm px-2 py-0.5 rounded text-secondary font-label-rotulo text-label-sm uppercase shadow-sm">
              <?= htmlspecialchars($categoria ?: 'De la casa', ENT_QUOTES, 'UTF-8') ?>
            </span>
          </a>

          <div class="px-space-xs">
            <h2 class="font-headline-sm text-headline-sm uppercase text-on-surface group-hover:text-primary transition-colors line-clamp-2 mb-1">
              <?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?>
            </h2>
            <p class="text-body-sm text-on-surface-variant line-clamp-2 mb-space-sm">
              <?= htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8') ?>
            </p>
          </div>
        </div>

        <div class="pt-space-xs px-space-xs flex items-center justify-between gap-space-sm bg-surface-container-low rounded-lg p-space-xs border border-outline-variant/20">
          <div class="flex flex-col min-w-0">
            <span class="text-label-sm text-on-surface-variant uppercase">Precio</span>
            <span class="font-headline-sm text-headline-sm text-secondary font-bold">$<?= number_format($precio, 2) ?> <span class="text-label-sm font-normal text-on-surface-variant">MXN</span></span>
          </div>

          <?php if ($stock > 0): ?>
            <form action="index.php?accion=agregar" method="POST" class="shrink-0">
              <input type="hidden" name="id_producto" value="<?= $id ?>">
              <button type="submit" aria-label="Agregar <?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?> al carrito" class="bg-primary text-white hover:bg-primary-container px-space-md py-space-xs rounded-lg font-label-rotulo text-label-sm uppercase flex items-center gap-1 transition-all shadow-sm active:scale-95">
                <span class="material-symbols-outlined text-[18px]">add_shopping_cart</span>
                Agregar +
              </button>
            </form>
          <?php else: ?>
            <button type="button" disabled class="bg-surface-container text-on-surface-variant px-space-md py-space-xs rounded-lg font-label-rotulo text-label-sm uppercase cursor-not-allowed">Agotado</button>
          <?php endif; ?>
        </div>
      </article>
      <?php endforeach; ?>

    </div>

    <div id="emptyState" class="hidden py-space-xl text-center">
      <span class="material-symbols-outlined text-5xl text-secondary">search_off</span>
      <h3 class="font-headline-sm text-headline-sm uppercase mt-space-sm">No encontramos ese antojo</h3>
      <p class="text-body-md text-on-surface-variant mt-space-xs">Prueba con otra búsqueda o cambia los filtros.</p>
      <button id="resetFilters" type="button" class="mt-space-md bg-primary text-white px-space-xl py-space-sm rounded-lg font-label-rotulo uppercase">Limpiar filtros</button>
    </div>
  </section>

  <!-- CIERRE VISUAL -->
  <section class="w-full px-gutter lg:px-margin-desktop py-space-xl">
    <div class="bg-surface-container rounded-xl p-space-lg lg:p-space-xl border border-outline-variant/30 flex flex-col md:flex-row items-start md:items-center justify-between gap-space-lg">
      <div>
        <span class="font-label-rotulo text-label-sm text-secondary uppercase tracking-widest">ANTOJOS GRANDES</span>
        <h2 class="font-headline-lg text-headline-lg uppercase mt-space-xs">¿Tienes evento, jefe?</h2>
        <p class="text-body-md text-on-surface-variant mt-space-sm max-w-2xl">Fruta fresca, botanas y antojos para compartir en tus mejores celebraciones.</p>
      </div>
      <a href="index.php" class="bg-primary text-white px-space-xl py-space-md rounded-lg font-label-rotulo uppercase shadow-[3px_3px_0px_#201b0f] shrink-0">Volver al inicio</a>
    </div>
  </section>
</main>

<footer class="w-full bg-surface-container-low text-on-surface border-t border-outline-variant/30">
  <div class="h-1.5 w-full flex">
    <div class="flex-1 bg-tertiary-container"></div>
    <div class="flex-1 bg-surface-container-high"></div>
    <div class="flex-1 bg-secondary-container"></div>
    <div class="flex-1 bg-primary-container"></div>
  </div>
  <div class="w-full px-gutter lg:px-margin-desktop py-space-xl grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-gutter-desktop">
    <div class="flex flex-col gap-space-md">
      <span class="font-headline-sm text-headline-sm uppercase text-primary font-bold">FRUTA MADRE</span>
      <p class="font-label-rotulo text-label-rotulo text-secondary tracking-wide uppercase">Te damos hasta para llevar.</p>
      <p class="text-body-md text-on-surface-variant">El auténtico sabor del mercado urbano mexicano: fruta fresca con chilito y limón, botanas preparadas y antojos para compartir.</p>
      <span class="text-label-sm text-tertiary uppercase tracking-wider font-bold">🇲🇽 Hecho con chamoy y amor en México</span>
    </div>
    <div>
      <h3 class="font-label-rotulo uppercase mb-space-md">Explorar</h3>
      <ul class="space-y-2 text-body-sm text-on-surface-variant">
        <li><a href="index.php" class="hover:text-primary">Inicio</a></li>
        <li><a href="index.php?accion=catalogo" class="hover:text-primary">Catálogo completo</a></li>
        <li><a href="index.php?accion=eventos" class="hover:text-primary">Eventos</a></li>
        <li><a href="index.php?accion=carrito" class="hover:text-primary">Mi carrito</a></li>
      </ul>
    </div>
    <div>
      <h3 class="font-label-rotulo uppercase mb-space-md">Atención</h3>
      <ul class="space-y-2 text-body-sm text-on-surface-variant">
        <li>Contacto directo</li>
        <li>Preguntas frecuentes</li>
        <li>Términos y condiciones</li>
        <li>Aviso de privacidad</li>
      </ul>
    </div>
    <div>
      <h3 class="font-label-rotulo uppercase mb-space-md">Mercado social</h3>
      <p class="text-body-sm text-on-surface-variant mb-space-sm">¡Échanos un grito o síguenos la pista!</p>
      <div class="flex flex-wrap gap-space-xs">
        <span class="px-space-md py-space-xs bg-surface-container rounded-lg text-label-sm">Instagram</span>
        <span class="px-space-md py-space-xs bg-surface-container rounded-lg text-label-sm">TikTok</span>
        <span class="px-space-md py-space-xs bg-surface-container rounded-lg text-label-sm">WhatsApp</span>
      </div>
    </div>
  </div>
  <div class="w-full px-gutter lg:px-margin-desktop py-space-md bg-surface-container flex flex-col sm:flex-row items-center justify-between gap-space-sm">
    <p class="text-body-sm text-on-surface-variant">© 2026 FRUTA MADRE. Todos los derechos reservados.</p>
    <p class="font-label-rotulo text-label-sm text-on-surface-variant uppercase">Puesto Digital No. 01</p>
  </div>
</footer>

<script>
const cards = [...document.querySelectorAll('.product-card')];
const searchInput = document.getElementById('searchInput');
const priceFilter = document.getElementById('filterPrice');
const stockFilter = document.getElementById('filterStock');
const sortBy = document.getElementById('sortBy');
const grid = document.getElementById('productsGrid');
const counter = document.getElementById('resultsCounter');
const emptyState = document.getElementById('emptyState');
let activeCategory = 'todos';

function applyFilters() {
  const query = searchInput.value.trim().toLocaleLowerCase('es');
  let visible = [];

  cards.forEach(card => {
    const name = card.dataset.name || '';
    const category = card.dataset.category || '';
    const price = Number(card.dataset.price || 0);
    const stock = card.dataset.stock || 'out-of-stock';

    const matchesSearch = name.includes(query);
    const matchesCategory = activeCategory === 'todos' || category.includes(activeCategory);
    let matchesPrice = true;

    if (priceFilter.value === 'under80') matchesPrice = price < 80;
    if (priceFilter.value === '80to150') matchesPrice = price >= 80 && price <= 150;
    if (priceFilter.value === 'over150') matchesPrice = price > 150;

    const matchesStock =
      stockFilter.value === 'all' ||
      (stockFilter.value === 'in-stock' && stock === 'in-stock') ||
      (stockFilter.value === 'out-of-stock' && stock === 'out-of-stock');

    const show = matchesSearch && matchesCategory && matchesPrice && matchesStock;
    card.hidden = !show;
    if (show) visible.push(card);
  });

  if (sortBy.value === 'price-asc') {
    visible.sort((a,b) => Number(a.dataset.price) - Number(b.dataset.price));
  } else if (sortBy.value === 'price-desc') {
    visible.sort((a,b) => Number(b.dataset.price) - Number(a.dataset.price));
  }

  visible.forEach(card => grid.appendChild(card));
  counter.textContent = `Mostrando ${visible.length} antojitos frescos`;
  emptyState.classList.toggle('hidden', visible.length !== 0);
}

document.getElementById('searchForm').addEventListener('submit', event => {
  event.preventDefault();
  applyFilters();
});
searchInput.addEventListener('input', applyFilters);
[priceFilter, stockFilter, sortBy].forEach(control => control.addEventListener('change', applyFilters));

document.querySelectorAll('.category-pill').forEach(button => {
  button.addEventListener('click', () => {
    activeCategory = button.dataset.category;
    document.querySelectorAll('.category-pill').forEach(pill => {
      pill.classList.remove('bg-primary','text-white','shadow-sm');
      pill.classList.add('bg-surface-container','text-on-surface-variant');
    });
    button.classList.add('bg-primary','text-white','shadow-sm');
    button.classList.remove('bg-surface-container','text-on-surface-variant');
    applyFilters();
  });
});

function resetFilters() {
  searchInput.value = '';
  priceFilter.value = 'all';
  stockFilter.value = 'all';
  sortBy.value = 'popular';
  activeCategory = 'todos';

  document.querySelectorAll('.category-pill').forEach(pill => {
    const active = pill.dataset.category === 'todos';
    pill.classList.toggle('bg-primary', active);
    pill.classList.toggle('text-white', active);
    pill.classList.toggle('shadow-sm', active);
    pill.classList.toggle('bg-surface-container', !active);
    pill.classList.toggle('text-on-surface-variant', !active);
  });

  applyFilters();
}

document.getElementById('resetFilters').addEventListener('click', resetFilters);
applyFilters();
</script>
</body>
</html>
