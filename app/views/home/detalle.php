<?php
$nombre = (string)($producto['nombre'] ?? 'Producto');
$categoria = (string)($producto['nombre_categoria'] ?? 'Antojitos');
$descripcion = (string)($producto['descripcion'] ?? 'Fruta fresca preparada al momento, con limón y chamoy de la casa.');
$imagen = trim((string)($producto['imagen_url'] ?? ''));
$idProducto = (int)($producto['id_producto'] ?? 0);
$precio = (float)($producto['precio'] ?? 0);
$stock = (int)($producto['stock_disponible'] ?? 0);
$cantidadCarrito = array_sum($_SESSION['carrito'] ?? []);
$esc = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

if ($imagen === '') {
    $imagen = 'https://images.unsplash.com/photo-1490474418585-ba9bad8fd0ea?auto=format&fit=crop&w=1200&q=85';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $esc($nombre) ?> | FRUTA MADRE</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Syne:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:FILL@0..1&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                background: '#fff8f0',
                surface: '#fff8f0',
                primary: '#2f612f',
                secondary: '#ac2d50',
                'on-surface': '#201b0f',
                'on-surface-variant': '#41493f',
                'surface-container': '#f8edd8',
                'surface-container-low': '#fdf2de',
                'surface-container-high': '#f2e7d2',
                'surface-container-highest': '#ece1cd',
                'primary-container': '#477a45',
                'secondary-container': '#fc6a8a',
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
:root{color-scheme:light;--bg:#fff8f0;--surface:#fff8f0;--ink:#201b0f;--muted:#41493f;--green:#2f612f;--green2:#477a45;--pink:#ac2d50;--cream:#f8edd8;--cream2:#fdf2de;--border:#ece1cd}
*{box-sizing:border-box}
html{scroll-behavior:smooth}
body{margin:0;background:var(--bg);color:var(--ink);font-family:'DM Sans',sans-serif}
h1,h2,h3,.brand{font-family:'Syne',sans-serif}
button,a,input{transition:all .2s ease}
button{cursor:pointer}
a{text-decoration:none;color:inherit}
.material-symbols-outlined{font-size:22px;line-height:1;vertical-align:middle}
::selection{background:#fc6a8a;color:#201b0f}
.site-header{position:sticky;top:0;z-index:50;background:rgba(255,248,240,.96);backdrop-filter:blur(14px);box-shadow:0 1px 8px #201b0f0a;border-bottom:1px solid var(--border)}
.header-inner{max-width:1440px;margin:auto;min-height:80px;padding:15px 32px;display:flex;align-items:center;justify-content:space-between;gap:20px}
.brand{font-size:15px;font-weight:900;letter-spacing:.08em;line-height:1.2}
.brand-sub{font-size:11px;font-weight:800;color:var(--pink);margin-top:4px}
.nav{display:flex;align-items:center;gap:4px;background:var(--cream2);padding:4px;border-radius:10px}
.nav a{padding:10px 13px;border-radius:8px;color:var(--muted);font-size:13px;font-weight:700}
.nav a:hover,.nav a.active{background:var(--green);color:white;box-shadow:2px 2px 0 #201b0f}
.header-actions{display:flex;align-items:center;gap:8px}
.searchbox{display:flex;align-items:center;gap:7px;padding:9px 12px;background:white;border:1px solid var(--border);border-radius:9px}
.searchbox input{width:135px;border:0;outline:0;background:transparent;font:inherit;font-size:13px}
.icon-button{width:42px;height:42px;border:0;border-radius:9px;background:var(--cream2);display:inline-flex;align-items:center;justify-content:center;position:relative}
.icon-button:hover{background:var(--cream)}
.cart-count{position:absolute;right:-5px;top:-5px;background:var(--pink);color:white;width:20px;height:20px;border-radius:50%;display:grid;place-items:center;font-size:11px;font-weight:800}
.profile{width:36px;height:36px;border-radius:50%;display:grid;place-items:center;background:var(--green);color:white}
.color-stripe{height:5px;display:flex}
.color-stripe span{flex:1}
.page{padding-top:0;min-height:70vh}
.container{max-width:1440px;margin:auto;padding-left:32px;padding-right:32px}
.section-space{padding-top:22px;padding-bottom:22px}
.shadow-street{box-shadow:4px 4px 0 #201b0f}
.shadow-small{box-shadow:2px 2px 0 #201b0f}
.view-switcher{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:13px 16px;border-radius:13px;background:var(--cream);box-shadow:0 2px 10px #201b0f0a}
.switch-buttons{display:flex;gap:4px;padding:4px;background:var(--border);border-radius:10px}
.switch-buttons button{display:flex;align-items:center;justify-content:center;gap:7px;padding:10px 14px;border:0;border-radius:8px;background:transparent;color:var(--muted);font-weight:800;font-size:12px}
.switch-buttons button.active{background:var(--green);color:white;box-shadow:2px 2px 0 #201b0f}
.eyebrow{font-family:'Syne',sans-serif;font-weight:800;font-size:11px;letter-spacing:.1em;text-transform:uppercase}
.text-pink{color:var(--pink)}
.breadcrumb-row{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;color:var(--muted);font-size:13px}
.breadcrumb{display:flex;align-items:center;gap:7px;flex-wrap:wrap}
.breadcrumb .current{color:var(--ink);font-weight:800}
.detail-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:38px;align-items:start}
.gallery{display:flex;flex-direction:column;gap:16px;min-width:0}
.main-image-wrap{position:relative;aspect-ratio:1/1;border-radius:13px;overflow:hidden;background:var(--cream);box-shadow:4px 4px 0 #201b0f}
.main-image{height:100%;width:100%;object-fit:cover;transition:transform .5s}
.main-image-wrap:hover .main-image{transform:scale(1.035)}
.sticker{position:absolute;top:17px;left:16px;background:var(--pink);color:white;border-radius:30px;padding:9px 13px;font-family:'Syne',sans-serif;font-weight:800;font-size:10px;letter-spacing:.06em;transform:rotate(-2deg);box-shadow:2px 2px 0 #201b0f}
.seal{position:absolute;top:14px;right:14px;width:68px;height:68px;border-radius:50%;background:white;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;box-shadow:2px 2px 0 #201b0f;transform:rotate(6deg)}
.seal span:first-child,.seal span:last-child{font-size:8px;font-weight:900;letter-spacing:.05em}
.seal strong{font-family:'Syne',sans-serif;font-size:14px;color:var(--pink)}
.microcopy{position:absolute;bottom:15px;left:15px;right:15px;padding:11px 13px;background:#fff8f0e8;backdrop-filter:blur(12px);border-radius:9px;box-shadow:2px 2px 0 #201b0f;display:flex;justify-content:space-between;align-items:center;gap:10px;font-size:12px;font-weight:700;font-style:italic}
.microcopy .material-symbols-outlined{color:var(--pink)}
.thumbnails{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px}
.thumbnail-btn{aspect-ratio:1;border:2px solid transparent;padding:0;position:relative;overflow:hidden;border-radius:9px;background:var(--cream);box-shadow:2px 2px 0 #201b0f}
.thumbnail-btn img{width:100%;height:100%;object-fit:cover}
.thumbnail-btn.active{border-color:var(--green);opacity:1}
.thumbnail-btn:hover{transform:translateY(-2px)}
.guarantee{display:flex;align-items:center;gap:14px;padding:17px;border-radius:12px;background:var(--cream2)}
.guarantee-icon{flex:0 0 48px;width:48px;height:48px;display:grid;place-items:center;border-radius:50%;background:#477a45;color:white}
.guarantee h3{margin:0 0 5px;font-size:13px;text-transform:uppercase}
.guarantee p{margin:0;color:var(--muted);font-size:12px;line-height:1.6}
.product-info{display:flex;flex-direction:column;gap:19px;min-width:0}
.category-status{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap}
.category-tag{padding:8px 12px;border-radius:30px;background:var(--cream);font-family:'Syne',sans-serif;font-weight:800;font-size:11px;text-transform:uppercase}
.status-pill{display:flex;align-items:center;gap:7px;padding:8px 12px;border-radius:30px;background:#f8edd8;color:var(--green);font-size:11px;font-weight:700}
.status-dot{width:8px;height:8px;border-radius:50%;background:var(--green);animation:pulse 1.8s infinite}
@keyframes pulse{50%{opacity:.4}}
.product-title{font-size:clamp(30px,3vw,45px);font-weight:900;line-height:1.08;letter-spacing:-.04em;margin:0}
.product-description{font-size:15px;line-height:1.8;color:var(--muted);margin:10px 0 0}
.price-row{display:flex;align-items:baseline;gap:10px;flex-wrap:wrap}
.price{font-family:'Syne',sans-serif;font-weight:900;font-size:35px;color:var(--green)}
.price-note{font-size:12px;color:var(--muted)}
.divider{height:1px;background:var(--border)}
.field-title{font-family:'Syne',sans-serif;font-size:13px;font-weight:900;text-transform:uppercase;letter-spacing:.04em;margin-bottom:11px}
.size-options{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:11px}
.size-option{display:flex;justify-content:space-between;align-items:center;gap:8px;border:1px solid var(--border);border-radius:10px;background:white;padding:13px 14px;text-align:left}
.size-option strong{font-family:'Syne',sans-serif;font-size:13px}
.size-option span{font-size:12px;color:var(--muted)}
.size-option.active{border:2px solid var(--green);background:#f2f7ed;padding:12px 13px;box-shadow:2px 2px 0 #201b0f}
.extras-list{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.extra-option{display:flex;align-items:flex-start;gap:9px;padding:12px;border:1px solid var(--border);background:white;border-radius:9px;font-size:12px;line-height:1.5}
.extra-option input{accent-color:var(--green);width:16px;height:16px;flex-shrink:0;margin-top:1px}
.extra-option strong{display:block;font-size:12px}
.extra-option small{display:block;color:var(--muted);margin-top:2px}
.quantity-line{display:flex;align-items:center;justify-content:space-between;gap:15px;flex-wrap:wrap}
.quantity-control{display:flex;align-items:center;gap:0;border:1px solid var(--border);border-radius:9px;overflow:hidden;background:white}
.quantity-control button{width:42px;height:42px;border:0;background:white;font-size:20px}
.quantity-control button:hover{background:var(--cream)}
.quantity-control output{min-width:42px;text-align:center;font-weight:800}
.action-buttons{display:grid;grid-template-columns:1fr 1fr;gap:11px}
.btn-primary,.btn-secondary{min-height:49px;display:flex;align-items:center;justify-content:center;gap:8px;padding:12px 14px;border-radius:9px;font-family:'Syne',sans-serif;font-size:12px;font-weight:900;text-transform:uppercase;letter-spacing:.025em}
.btn-primary{background:var(--green);color:white;border:1px solid var(--green);box-shadow:3px 3px 0 #201b0f}
.btn-primary:hover{background:#244e24;transform:translateY(-1px)}
.btn-secondary{background:var(--cream2);border:1px solid var(--border);box-shadow:2px 2px 0 #201b0f}
.btn-secondary:hover{background:var(--cream)}
.btn-primary:disabled{opacity:.5;cursor:not-allowed;transform:none}
.stock-note{display:flex;align-items:center;gap:7px;color:var(--muted);font-size:12px}
.stock-note .material-symbols-outlined{font-size:17px;color:var(--green)}
.stock-note.sold-out{color:#ba1a1a}
.sold-out-banner{margin-top:12px;padding:16px;border-radius:12px;background:#ffdad6;color:#93000a;display:flex;align-items:center;gap:12px}
.sold-out-banner[hidden]{display:none}
.related-section{padding-top:38px;padding-bottom:52px}
.section-heading{display:flex;align-items:end;justify-content:space-between;gap:15px;margin-bottom:20px}
.section-heading h2{font-size:clamp(24px,3vw,34px);margin:0;font-weight:900}
.section-heading p{color:var(--muted);font-size:13px;margin:6px 0 0}
.related-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:20px}
.related-card{background:white;border:1px solid var(--border);border-radius:13px;overflow:hidden;box-shadow:3px 3px 0 #201b0f;display:flex;flex-direction:column}
.related-card img{width:100%;aspect-ratio:1.35;object-fit:cover;background:var(--cream)}
.related-card-content{padding:16px;display:flex;flex-direction:column;gap:9px;flex:1}
.related-card h3{margin:0;font-size:17px}
.related-card p{margin:0;color:var(--muted);font-size:12px;line-height:1.6;flex:1}
.related-card-bottom{display:flex;align-items:center;justify-content:space-between;gap:10px}
.related-card-bottom strong{font-family:'Syne',sans-serif;font-size:20px;color:var(--green)}
.related-card-bottom a{padding:8px 11px;background:var(--green);color:white;border-radius:7px;font-size:11px;font-weight:800}
.site-footer{background:var(--cream2);border-top:1px solid var(--border)}
.footer-inner{max-width:1440px;margin:auto;padding:35px 32px;display:grid;grid-template-columns:1.4fr 1fr 1fr 1fr;gap:28px}
.footer-inner h3{font-size:13px;margin:0 0 13px}
.footer-inner p,.footer-inner a{font-size:12px;line-height:1.9;color:var(--muted)}
.footer-inner a{display:block}
.footer-inner a:hover{color:var(--pink)}
.footer-bottom{max-width:1440px;margin:auto;padding:16px 32px;border-top:1px solid var(--border);font-size:11px;color:var(--muted);display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap}
.toast{position:fixed;right:20px;bottom:20px;z-index:100;max-width:min(390px,calc(100vw - 40px));padding:15px 18px;border-radius:10px;background:var(--green);color:white;box-shadow:4px 4px 0 #201b0f;display:flex;align-items:center;gap:10px;transform:translateY(140%);opacity:0;pointer-events:none;transition:.3s}
.toast.show{transform:translateY(0);opacity:1}
.toast .material-symbols-outlined{font-size:22px}
.hidden{display:none!important}
.dark-mode{--bg:#1e1a17;--surface:#24201c;--ink:#fff4df;--muted:#c1c9bc;--cream:#302923;--cream2:#29251f;--border:#494137;color-scheme:dark}
.dark-mode .site-header{background:rgba(30,26,23,.96)}
.dark-mode .nav,.dark-mode .icon-button,.dark-mode .switch-buttons,.dark-mode .view-switcher,.dark-mode .guarantee,.dark-mode .category-tag{background:#302923}
.dark-mode .nav a.active,.dark-mode .switch-buttons button.active{background:var(--green2)}
.dark-mode .searchbox,.dark-mode .size-option,.dark-mode .extra-option,.dark-mode .quantity-control,.dark-mode .quantity-control button,.dark-mode .related-card{background:#29251f;color:var(--ink);border-color:var(--border)}
.dark-mode .size-option.active{background:#263426;border-color:#79a875}
.dark-mode .status-pill{background:#302923}
.dark-mode .microcopy{background:#29251fe8}
.dark-mode .site-footer{background:#24201c}
.dark-mode .btn-secondary{background:#302923;color:var(--ink);border-color:var(--border)}
.dark-mode .price{color:#9dd496}
.dark-mode .seal{background:#fff8f0;color:#201b0f}
.dark-mode .related-card-bottom strong{color:#9dd496}
@media(max-width:1100px){.header-inner{padding:14px 22px}.nav{display:none}.container{padding-left:22px;padding-right:22px}.detail-grid{gap:26px}.footer-inner{padding-left:22px;padding-right:22px}}
@media(max-width:760px){.header-inner{min-height:70px;padding:12px 16px}.searchbox{display:none}.container{padding-left:16px;padding-right:16px}.section-space{padding-top:15px;padding-bottom:15px}.view-switcher{align-items:stretch;flex-direction:column}.switch-buttons{width:100%}.switch-buttons button{flex:1;padding:10px 7px;font-size:10px}.switch-buttons .material-symbols-outlined{display:none}.detail-grid{grid-template-columns:1fr;gap:26px}.product-title{font-size:34px}.product-info{gap:16px}.price{font-size:31px}.related-grid{grid-template-columns:1fr 1fr;gap:12px}.related-card-content{padding:12px}.related-card h3{font-size:15px}.related-card-bottom{align-items:flex-start;flex-direction:column}.related-card-bottom a{width:100%;text-align:center}.footer-inner{grid-template-columns:1fr 1fr;padding:28px 16px;gap:24px}.footer-bottom{padding:15px 16px}.extras-list{grid-template-columns:1fr 1fr}}
@media(max-width:430px){.brand{font-size:13px}.brand-sub{font-size:9px}.header-actions{gap:5px}.icon-button{width:37px;height:37px}.profile{width:32px;height:32px}.category-status{align-items:flex-start;flex-direction:column}.status-pill{font-size:10px}.extras-list{grid-template-columns:1fr}.action-buttons{grid-template-columns:1fr}.related-grid{grid-template-columns:1fr 1fr}.related-card h3{font-size:13px}.related-card p{font-size:11px}.related-card-bottom strong{font-size:17px}.footer-inner{grid-template-columns:1fr 1fr}.seal{width:58px;height:58px}.seal strong{font-size:12px}.sticker{font-size:8px;padding:8px 10px}}
</style>
</head>

<body>
<header class="site-header">
    <div class="header-inner">
        <a href="index.php" aria-label="Fruta Madre, inicio">
            <div class="brand">FRUTA MADRE</div>
            <div class="brand-sub">Te damos hasta para llevar</div>
        </a>

        <nav class="nav" aria-label="Navegación principal">
            <a href="index.php">Inicio</a>
            <a href="index.php?accion=catalogo" class="active">Catálogo</a>
            <a href="index.php#eventos">Eventos</a>
            <a href="index.php?accion=login">Mis pedidos</a>
        </nav>

        <div class="header-actions">
            <label class="searchbox">
                <span class="material-symbols-outlined">search</span>
                <input id="search-input" type="search" placeholder="Buscar antojo..." aria-label="Buscar productos">
            </label>

            <a href="index.php?accion=carrito" class="icon-button" aria-label="Carrito de compras">
                <span class="material-symbols-outlined">shopping_bag</span>
                <span class="cart-count" id="cart-count"><?= (int)$cantidadCarrito ?></span>
            </a>

            <button class="icon-button" id="theme-toggle" type="button" aria-label="Cambiar tema">
                <span class="material-symbols-outlined" id="theme-icon">light_mode</span>
            </button>

            <a href="index.php?accion=login" class="profile" aria-label="Mi cuenta">
                <span class="material-symbols-outlined">person</span>
            </a>
        </div>
    </div>

    <div class="color-stripe">
        <span style="background:#477a45"></span>
        <span style="background:#fc6a8a"></span>
        <span style="background:#f2e7d2"></span>
        <span style="background:#b55107"></span>
    </div>
</header>

<main class="page">
    <section class="container section-space">
        <div class="view-switcher">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-primary text-white">
                    <span class="material-symbols-outlined">sync_alt</span>
                </span>
                <div>
                    <div class="eyebrow text-pink">Plantilla modular universal</div>
                    <p class="text-sm text-on-surface-variant m-0 mt-1">Intercambia entre antojito del día y montaje para fiestones.</p>
                </div>
            </div>

            <div class="switch-buttons">
                <button id="btn-view-product" class="active" type="button" onclick="switchView('product')">
                    <span class="material-symbols-outlined">nutrition</span>
                    Producto individual
                </button>
                <button id="btn-view-package" type="button" onclick="switchView('package')">
                    <span class="material-symbols-outlined">celebration</span>
                    Paquete de eventos
                </button>
            </div>
        </div>
    </section>

    <section class="container">
        <div class="breadcrumb-row">
            <nav class="breadcrumb" aria-label="Ruta de navegación">
                <a href="index.php">Inicio</a>
                <span class="material-symbols-outlined">chevron_right</span>
                <a href="index.php?accion=catalogo" id="breadcrumb-category"><?= $esc($categoria) ?></a>
                <span class="material-symbols-outlined">chevron_right</span>
                <span class="current" id="breadcrumb-current"><?= $esc($nombre) ?></span>
            </nav>

            <button type="button" id="stock-demo-button" class="btn-secondary" style="min-height:35px;padding:7px 10px;font-size:10px" onclick="toggleStockDemo()">
                <span class="material-symbols-outlined" style="font-size:16px">info</span>
                Simular agotado
            </button>
        </div>

        <div id="banner-out-of-stock" class="sold-out-banner" hidden>
            <span class="material-symbols-outlined">sentiment_dissatisfied</span>
            <div style="flex:1">
                <strong id="stock-alert-title">¡Chin! Por ahora se nos acabó este antojo.</strong>
                <div id="stock-alert-text" style="font-size:12px;margin-top:4px">Pronto tendremos más para ti.</div>
            </div>
            <button class="btn-secondary" style="min-height:35px" type="button" onclick="toggleStockDemo()">Restablecer</button>
        </div>
    </section>

    <section class="container section-space">
        <div class="detail-grid">
            <div class="gallery">
                <div class="main-image-wrap">
                    <div class="sticker" id="badge-top">🔥 ¡HECHO AL MOMENTO!</div>

                    <div class="seal">
                        <span>100% PURO</span>
                        <strong>SABOR</strong>
                        <span>MEXICANO</span>
                    </div>

                    <img class="main-image" id="main-product-image" src="<?= $esc($imagen) ?>" alt="<?= $esc($nombre) ?>">

                    <div class="microcopy">
                        <span id="gallery-microcopy">“Este sí se antoja. Limoncito y harto chamoy.”</span>
                        <span class="material-symbols-outlined">local_fire_department</span>
                    </div>
                </div>

                <div class="thumbnails" id="thumbnails-track">
                    <button class="thumbnail-btn active" type="button" onclick="selectThumbnail(0)" aria-label="Ver imagen 1">
                        <img id="thumb-img-0" src="<?= $esc($imagen) ?>" alt="Vista 1">
                    </button>
                    <button class="thumbnail-btn" type="button" onclick="selectThumbnail(1)" aria-label="Ver imagen 2">
                        <img id="thumb-img-1" src="<?= $esc($imagen) ?>" alt="Vista 2">
                    </button>
                    <button class="thumbnail-btn" type="button" onclick="selectThumbnail(2)" aria-label="Ver imagen 3">
                        <img id="thumb-img-2" src="<?= $esc($imagen) ?>" alt="Vista 3">
                    </button>
                    <button class="thumbnail-btn" type="button" onclick="selectThumbnail(3)" aria-label="Ver imagen 4">
                        <img id="thumb-img-3" src="<?= $esc($imagen) ?>" alt="Vista 4">
                    </button>
                </div>

                <div class="guarantee">
                    <div class="guarantee-icon">
                        <span class="material-symbols-outlined">verified</span>
                    </div>
                    <div>
                        <h3>Garantía de sabor callejero</h3>
                        <p>Fruta picada al momento de la orden. Chamoy de flor de jamaica 100% natural, sin conservadores raros.</p>
                    </div>
                </div>
            </div>

            <div class="product-info">
                <div class="category-status">
                    <span class="category-tag" id="tag-category">🍉 <?= $esc($categoria) ?></span>
                    <span class="status-pill" id="availability-pill">
                        <span class="status-dot"></span>
                        <span id="availability-text"><?= $stock > 0 ? 'Disponible para entrega hoy' : 'Por el momento agotado' ?></span>
                    </span>
                </div>

                <div>
                    <h1 class="product-title" id="item-title"><?= $esc($nombre) ?></h1>
                    <p class="product-description" id="item-subtitle"><?= $esc($descripcion) ?></p>
                </div>

                <div class="price-row">
                    <span class="price" id="product-price">$<?= number_format($precio, 2) ?></span>
                    <span class="price-note" id="price-note">MXN · Precio por pieza</span>
                </div>

                <div class="divider"></div>

                <div id="product-options">
                    <div class="field-title">1. Escoge tu tamaño</div>
                    <div class="size-options">
                        <button type="button" class="size-option active" data-price="<?= $esc($precio) ?>" data-label="Tamaño normal" onclick="setProductSize(this, <?= json_encode($precio) ?>, 'Tamaño normal')">
                            <span><strong>Tamaño normal</strong><br><span>Presentación individual</span></span>
                            <strong>$<?= number_format($precio, 2) ?></strong>
                        </button>
                        <button type="button" class="size-option" data-price="<?= $esc($precio * .765) ?>" data-label="Tamaño chico" onclick="setProductSize(this, <?= json_encode(round($precio * .765, 2)) ?>, 'Tamaño chico')">
                            <span><strong>Tamaño chico</strong><br><span>Para el antojo</span></span>
                            <strong>$<?= number_format($precio * .765, 2) ?></strong>
                        </button>
                    </div>

                    <div style="margin-top:22px">
                        <div class="field-title">2. Ponle lo bueno</div>
                        <div class="extras-list">
                            <label class="extra-option">
                                <input type="checkbox" class="product-extra" value="0" data-name="Con todo y limón" onchange="updateProductTotal()">
                                <span><strong>Con todo y limón</strong><small>Incluido · Clásico de la casa</small></span>
                            </label>
                            <label class="extra-option">
                                <input type="checkbox" class="product-extra" value="8" data-name="Chamoy de la casa extra" onchange="updateProductTotal()">
                                <span><strong>Chamoy de la casa extra</strong><small>+$8.00</small></span>
                            </label>
                            <label class="extra-option">
                                <input type="checkbox" class="product-extra" value="5" data-name="Salsa de la que sí pica" onchange="updateProductTotal()">
                                <span><strong>Salsa de la que sí pica</strong><small>+$5.00</small></span>
                            </label>
                            <label class="extra-option">
                                <input type="checkbox" class="product-extra" value="0" data-name="Sin pepino / Sin jícama" onchange="updateProductTotal()">
                                <span><strong>Sin pepino / Sin jícama</strong><small>Sin costo · A tu gusto</small></span>
                            </label>
                        </div>
                    </div>
                </div>

                <div id="package-options" class="hidden">
                    <div class="field-title">Arma tu paquete para eventos</div>
                    <p class="text-sm text-on-surface-variant" style="line-height:1.7;margin:0 0 14px">Una opción para cumpleaños, reuniones y pachangas. Personaliza los extras de tu paquete.</p>
                    <div class="extras-list">
                        <label class="extra-option">
                            <input type="checkbox" class="package-extra" value="150" data-name="Chamoy artesanal extra" onchange="updatePackageTotal()">
                            <span><strong>Chamoy artesanal extra</strong><small>+$150.00</small></span>
                        </label>
                        <label class="extra-option">
                            <input type="checkbox" class="package-extra" value="200" data-name="Barra de salsas" onchange="updatePackageTotal()">
                            <span><strong>Barra de salsas</strong><small>+$200.00</small></span>
                        </label>
                        <label class="extra-option">
                            <input type="checkbox" class="package-extra" value="250" data-name="Montaje para evento" onchange="updatePackageTotal()">
                            <span><strong>Montaje para evento</strong><small>+$250.00</small></span>
                        </label>
                        <label class="extra-option">
                            <input type="checkbox" class="package-extra" value="100" data-name="Decoración especial" onchange="updatePackageTotal()">
                            <span><strong>Decoración especial</strong><small>+$100.00</small></span>
                        </label>
                    </div>
                    <p class="text-sm text-on-surface-variant" style="margin:12px 0 0;line-height:1.6">El precio final del evento se confirma según cantidad de invitados y montaje solicitado.</p>
                </div>

                <div class="divider"></div>

                <div class="quantity-line">
                    <div>
                        <div class="field-title" style="margin-bottom:7px">Cantidad</div>
                        <div class="quantity-control">
                            <button type="button" onclick="changeQuantity(-1)" aria-label="Disminuir cantidad">−</button>
                            <output id="quantity-value">1</output>
                            <button type="button" onclick="changeQuantity(1)" aria-label="Aumentar cantidad">+</button>
                        </div>
                    </div>

                    <div style="text-align:right">
                        <div style="font-size:11px;color:var(--muted);margin-bottom:4px">Total estimado</div>
                        <div class="price" style="font-size:27px" id="product-total">$<?= number_format($precio, 2) ?></div>
                    </div>
                </div>

                <div class="stock-note <?= $stock > 0 ? '' : 'sold-out' ?>" id="stock-note">
                    <span class="material-symbols-outlined"><?= $stock > 0 ? 'check_circle' : 'cancel' ?></span>
                    <span id="stock-note-text"><?= $stock > 0 ? 'Disponible · ' . $stock . ' en existencia' : 'Producto agotado por el momento' ?></span>
                </div>

                <div class="action-buttons">
                    <button class="btn-primary" type="button" id="add-cart-button" onclick="handleAddToCart()" <?= $stock <= 0 ? 'disabled' : '' ?>>
                        <span class="material-symbols-outlined">add_shopping_cart</span>
                        Agregar al carrito
                    </button>
                    <button class="btn-secondary" type="button" onclick="handleBuyNow()">
                        <span class="material-symbols-outlined">bolt</span>
                        Comprar ahora
                    </button>
                </div>

                <p style="font-size:11px;color:var(--muted);line-height:1.7;margin:0">
                    <span class="material-symbols-outlined" style="font-size:15px">lock</span>
                    Tu pedido se confirma al continuar. Si todavía no has iniciado sesión, te pediremos entrar a tu cuenta antes de finalizar la compra.
                </p>

                <div id="package-actions" class="hidden">
                    <button type="button" class="btn-primary" style="width:100%" onclick="handleReservePackage()">
                        <span class="material-symbols-outlined">celebration</span>
                        Solicitar paquete para evento
                    </button>
                    <button type="button" class="btn-secondary" style="width:100%;margin-top:12px" onclick="handleWhatsAppQuote()">
                        <span class="material-symbols-outlined">chat</span>
                        Cotizar por WhatsApp
                    </button>
                </div>
            </div>
        </div>
    </section>

    <section class="container related-section">
        <div class="section-heading">
            <div>
                <div class="eyebrow text-pink">Para completar el antojo</div>
                <h2>También se te va a antojar</h2>
                <p>Échale ojo a otros favoritos de la casa.</p>
            </div>
            <a href="index.php?accion=catalogo" class="btn-secondary" style="min-height:38px;padding:9px 12px;font-size:10px">Ver catálogo <span class="material-symbols-outlined" style="font-size:16px">arrow_forward</span></a>
        </div>

        <div class="related-grid">
            <article class="related-card">
                <img src="https://images.unsplash.com/photo-1553279768-865429fa0078?auto=format&fit=crop&w=900&q=85" alt="Mango fresco" loading="lazy">
                <div class="related-card-content">
                    <div class="eyebrow text-pink">Favorito de la casa</div>
                    <h3>Mango Loco en Flor</h3>
                    <p>Mango jugoso con chilito, limón y chamoy para que el antojo quede completo.</p>
                    <div class="related-card-bottom">
                        <strong>Desde $<?= number_format($precio, 2) ?></strong>
                        <a href="index.php?accion=catalogo">Ver producto</a>
                    </div>
                </div>
            </article>

            <article class="related-card">
                <img src="https://images.unsplash.com/photo-1447175008436-054170c2e979?auto=format&fit=crop&w=900&q=85" alt="Pepino y verduras frescas" loading="lazy">
                <div class="related-card-content">
                    <div class="eyebrow text-pink">Fresco y picosito</div>
                    <h3>Pepino con Chilito</h3>
                    <p>Una combinación fresca, crujiente y con ese toque picosito que se disfruta.</p>
                    <div class="related-card-bottom">
                        <strong>Desde $<?= number_format($precio, 2) ?></strong>
                        <a href="index.php?accion=catalogo">Ver producto</a>
                    </div>
                </div>
            </article>

            <article class="related-card">
                <img src="https://images.unsplash.com/photo-1499636136210-6f4ee915583e?auto=format&fit=crop&w=900&q=85" alt="Dulces y gomitas" loading="lazy">
                <div class="related-card-content">
                    <div class="eyebrow text-pink">Para compartir</div>
                    <h3>Mix Dulce & Gomitas</h3>
                    <p>Una mezcla para quienes quieren algo dulce, acidito y lleno de sabor.</p>
                    <div class="related-card-bottom">
                        <strong>Desde $<?= number_format($precio, 2) ?></strong>
                        <a href="index.php?accion=catalogo">Ver producto</a>
                    </div>
                </div>
            </article>
        </div>
    </section>
</main>

<footer class="site-footer">
    <div class="footer-inner">
        <div>
            <a href="index.php">
                <div class="brand">FRUTA MADRE</div>
                <div class="brand-sub">Te damos hasta para llevar</div>
            </a>
            <p>Fruta fresca, chamoy artesanal y antojos hechos al momento. El sabor del barrio, listo para llevar.</p>
        </div>
        <div>
            <h3>Explora</h3>
            <a href="index.php">Inicio</a>
            <a href="index.php?accion=catalogo">Catálogo</a>
            <a href="index.php#eventos">Eventos</a>
        </div>
        <div>
            <h3>Tu cuenta</h3>
            <a href="index.php?accion=login">Iniciar sesión</a>
            <a href="index.php?accion=carrito">Mi carrito</a>
            <a href="index.php?accion=login">Mis pedidos</a>
        </div>
        <div>
            <h3>El puesto</h3>
            <p>Hecho al momento y con sabor mexicano.</p>
            <p><span class="material-symbols-outlined" style="font-size:16px">favorite</span> Gracias por apoyar lo local.</p>
        </div>
    </div>
    <div class="footer-bottom">
        <span>© <?= date('Y') ?> Fruta Madre. Todos los derechos reservados.</span>
        <span>Te damos hasta para llevar.</span>
    </div>
</footer>

<div class="toast" id="toast" role="status" aria-live="polite">
    <span class="material-symbols-outlined" id="toast-icon">check_circle</span>
    <span id="toast-message">¡Listo! Se agregó al carrito.</span>
</div>

<script>
const PRODUCT = {
    id: <?= json_encode($idProducto) ?>,
    name: <?= json_encode($nombre, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    price: <?= json_encode($precio) ?>,
    stock: <?= json_encode($stock) ?>,
    image: <?= json_encode($imagen, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
};

let currentMode = 'product';
let currentPrice = PRODUCT.price;
let currentSize = 'Tamaño normal';
let quantity = 1;
let stockDemoActive = false;
let toastTimeout;

const money = value => new Intl.NumberFormat('es-MX', {
    style: 'currency',
    currency: 'MXN'
}).format(value);

function showToast(message, icon = 'check_circle') {
    const toast = document.getElementById('toast');
    document.getElementById('toast-message').textContent = message;
    document.getElementById('toast-icon').textContent = icon;
    toast.classList.add('show');
    clearTimeout(toastTimeout);
    toastTimeout = setTimeout(() => toast.classList.remove('show'), 3200);
}

function switchView(mode) {
    currentMode = mode;
    const isProduct = mode === 'product';

    document.getElementById('btn-view-product').classList.toggle('active', isProduct);
    document.getElementById('btn-view-package').classList.toggle('active', !isProduct);
    document.getElementById('product-options').classList.toggle('hidden', !isProduct);
    document.getElementById('package-options').classList.toggle('hidden', isProduct);
    document.getElementById('package-actions').classList.toggle('hidden', isProduct);
    document.getElementById('add-cart-button').closest('.action-buttons').classList.toggle('hidden', !isProduct);
    document.getElementById('stock-note').classList.toggle('hidden', !isProduct);

    document.getElementById('badge-top').textContent = isProduct
        ? '🔥 ¡HECHO AL MOMENTO!'
        : '🎉 ¡QUE COMIENCE LA PACHANGA!';

    document.getElementById('gallery-microcopy').textContent = isProduct
        ? '“Este sí se antoja. Limoncito y harto chamoy.”'
        : '“Tu evento, tus invitados y un montón de sabor.”';

    document.getElementById('price-note').textContent = isProduct
        ? 'MXN · Precio por pieza'
        : 'MXN · Cotización estimada';

    document.getElementById('product-price').textContent = isProduct
        ? money(currentPrice)
        : 'Desde ' + money(currentPrice * 10);

    document.getElementById('product-total').textContent = isProduct
        ? money(calculateProductTotal())
        : money(calculatePackageTotal());

    document.getElementById('availability-text').textContent = isProduct
        ? (PRODUCT.stock > 0 ? 'Disponible para entrega hoy' : 'Por el momento agotado')
        : 'Cotiza la fecha de tu evento';

    if (!isProduct) {
        document.getElementById('breadcrumb-current').textContent = 'Paquetes para eventos';
        document.getElementById('tag-category').textContent = '🎉 Eventos';
    } else {
        document.getElementById('breadcrumb-current').textContent = PRODUCT.name;
        document.getElementById('tag-category').textContent = '🍉 <?= $esc($categoria) ?>';
    }
}

function selectThumbnail(index) {
    const selected = document.getElementById('thumb-img-' + index);
    if (!selected) return;

    document.getElementById('main-product-image').src = selected.src;

    document.querySelectorAll('.thumbnail-btn').forEach((button, i) => {
        button.classList.toggle('active', i === index);
    });
}

function setProductSize(button, price, label) {
    currentPrice = Number(price);
    currentSize = label;

    document.querySelectorAll('.size-option').forEach(option => {
        option.classList.remove('active');
    });

    button.classList.add('active');
    document.getElementById('product-price').textContent = money(currentPrice);
    updateProductTotal();
}

function changeQuantity(delta) {
    quantity = Math.max(1, Math.min(99, quantity + delta));
    document.getElementById('quantity-value').textContent = quantity;
    updateProductTotal();
    updatePackageTotal();
}

function calculateProductTotal() {
    const extras = [...document.querySelectorAll('.product-extra:checked')]
        .reduce((sum, item) => sum + Number(item.value || 0), 0);

    return (currentPrice + extras) * quantity;
}

function calculatePackageTotal() {
    const extras = [...document.querySelectorAll('.package-extra:checked')]
        .reduce((sum, item) => sum + Number(item.value || 0), 0);

    return (PRODUCT.price * 10 + extras) * quantity;
}

function updateProductTotal() {
    document.getElementById('product-total').textContent = money(calculateProductTotal());
}

function updatePackageTotal() {
    document.getElementById('product-total').textContent = money(calculatePackageTotal());
    document.getElementById('product-price').textContent = 'Desde ' + money(PRODUCT.price * 10);
}

function toggleStockDemo() {
    stockDemoActive = !stockDemoActive;
    const agotado = stockDemoActive || PRODUCT.stock <= 0;

    document.getElementById('banner-out-of-stock').hidden = !stockDemoActive;
    document.getElementById('availability-text').textContent = agotado
        ? 'Por el momento agotado'
        : 'Disponible para entrega hoy';

    document.getElementById('stock-note-text').textContent = agotado
        ? 'Producto agotado por el momento'
        : 'Disponible · ' + PRODUCT.stock + ' en existencia';

    document.getElementById('stock-note').classList.toggle('sold-out', agotado);
    document.getElementById('add-cart-button').disabled = agotado;

    document.getElementById('stock-demo-button').innerHTML = stockDemoActive
        ? '<span class="material-symbols-outlined" style="font-size:16px">check_circle</span> Restablecer disponibilidad'
        : '<span class="material-symbols-outlined" style="font-size:16px">info</span> Simular agotado';

    if (stockDemoActive) {
        showToast('Vista de demostración: producto agotado.', 'info');
    }
}

async function handleAddToCart() {
    if (currentMode !== 'product') {
        handleReservePackage();
        return;
    }

    if (PRODUCT.id <= 0) {
        showToast('No se pudo identificar el producto.', 'error');
        return;
    }

    if (stockDemoActive || PRODUCT.stock <= 0) {
        showToast('Este producto está agotado por el momento.', 'error');
        return;
    }

    const extras = [...document.querySelectorAll('.product-extra:checked')]
        .map(item => item.dataset.name);

    const formData = new FormData();
    formData.append('id_producto', PRODUCT.id);
    formData.append('cantidad', quantity);
    formData.append('tamano', currentSize);
    formData.append('extras', JSON.stringify(extras));

    try {
        const response = await fetch('index.php?accion=agregarAlCarrito', {
            method: 'POST',
            body: formData,
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        });

        if (response.redirected) {
            window.location.href = response.url;
            return;
        }

        if (response.ok) {
            let result = null;
            try {
                result = await response.clone().json();
            } catch (_) {}

            if (result && result.success === false) {
                showToast(result.mensaje || 'No se pudo agregar el producto.', 'error');
                return;
            }

            showToast('¡Listo! Se envió la solicitud para agregar al carrito.');
            const cartCount = document.getElementById('cart-count');
            cartCount.textContent = Number(cartCount.textContent || 0) + quantity;
            return;
        }

        showToast('No se pudo agregar. Intenta nuevamente.', 'error');
    } catch (error) {
        showToast('No se pudo conectar. Revisa tu conexión e intenta nuevamente.', 'error');
    }
}

function handleBuyNow() {
    if (currentMode === 'package') {
        handleReservePackage();
        return;
    }

    if (stockDemoActive || PRODUCT.stock <= 0) {
        showToast('Este producto está agotado por el momento.', 'error');
        return;
    }

    const extras = [...document.querySelectorAll('.product-extra:checked')]
        .map(item => item.dataset.name);

    const params = new URLSearchParams({
        id_producto: String(PRODUCT.id),
        cantidad: String(quantity),
        tamano: currentSize,
        extras: JSON.stringify(extras)
    });

    window.location.href = 'index.php?accion=login&continuar=compra&' + params.toString();
}

function handleReservePackage() {
    const extras = [...document.querySelectorAll('.package-extra:checked')]
        .map(item => item.dataset.name);

    const params = new URLSearchParams({
        tipo: 'evento',
        producto: PRODUCT.name,
        cantidad: String(quantity),
        extras: JSON.stringify(extras),
        total_estimado: String(calculatePackageTotal())
    });

    window.location.href = 'index.php?accion=login&continuar=evento&' + params.toString();
}

function handleWhatsAppQuote() {
    const extras = [...document.querySelectorAll('.package-extra:checked')]
        .map(item => item.dataset.name);

    const message = [
        'Hola, quiero cotizar un paquete de Fruta Madre para un evento.',
        'Producto de referencia: ' + PRODUCT.name,
        'Cantidad: ' + quantity,
        'Extras: ' + (extras.length ? extras.join(', ') : 'Sin extras seleccionados'),
        'Total estimado: ' + money(calculatePackageTotal()),
        'Me gustaría recibir información sobre disponibilidad y montaje.'
    ].join('\n');

    // Sustituye el número por el WhatsApp oficial de Fruta Madre, incluyendo código de país.
    const whatsappNumber = '';
    if (!whatsappNumber) {
        showToast('Configura el número oficial de WhatsApp en el código para habilitar las cotizaciones.', 'info');
        return;
    }

    window.open(
        'https://wa.me/' + whatsappNumber + '?text=' + encodeURIComponent(message),
        '_blank',
        'noopener,noreferrer'
    );
}

document.getElementById('theme-toggle').addEventListener('click', function () {
    document.body.classList.toggle('dark-mode');
    const dark = document.body.classList.contains('dark-mode');
    document.getElementById('theme-icon').textContent = dark ? 'dark_mode' : 'light_mode';
    this.setAttribute('aria-label', dark ? 'Cambiar a tema claro' : 'Cambiar a tema oscuro');
});

document.getElementById('search-input').addEventListener('keydown', function (event) {
    if (event.key === 'Enter' && this.value.trim()) {
        window.location.href = 'index.php?accion=catalogo&buscar=' + encodeURIComponent(this.value.trim());
    }
});

document.getElementById('main-product-image').addEventListener('error', function () {
    this.src = 'https://images.unsplash.com/photo-1490474418585-ba9bad8fd0ea?auto=format&fit=crop&w=1200&q=85';
});

document.querySelectorAll('.thumbnail-btn img').forEach(img => {
    img.addEventListener('error', function () {
        this.src = 'https://images.unsplash.com/photo-1490474418585-ba9bad8fd0ea?auto=format&fit=crop&w=500&q=80';
    });
});

updateProductTotal();
</script>
</body>
</html>