<?php
/** Layout inspirado directamente en el HTML Stitch 36 / 37. */
function stitchEscape(mixed $v):string {return htmlspecialchars((string)($v??''),ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
function stitchInicio(string $titulo,string $activo,string $descripcion=''):void {
    $modulos=[
      'admin'=>['▦','Dashboard'],
      'admin-pedidos'=>['🛵','Pedidos'],
      'admin-reservas-stitch'=>['📅','Reservas'],
      'admin-productos'=>['🍉','Productos'],
      'admin-categorias-stitch'=>['🍓','Categorías'],
      'admin-paquetes-stitch'=>['▤','Paquetes'],
      'admin-carritos-stitch'=>['🛺','Carritos botaneros'],
      'admin-clientes-stitch'=>['👥','Clientes'],
      'admin-inventario-stitch'=>['📦','Inventario'],
      'admin-reportes-stitch'=>['📈','Reportes'],
      'cuenta'=>['⚙','Mi cuenta']
    ];
    $nombre=stitchEscape($_SESSION['usuario']['nombre_completo']??'Administrador');
    ?>
<!DOCTYPE html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= stitchEscape($titulo) ?> | Fruta Madre</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@500;600;700;800&family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style><?php readfile(dirname(__DIR__,3).'/public/stitch-admin.css'); ?></style></head><body>
<div class="shell"><aside class="aside"><a href="index.php?accion=admin" class="logo"><span class="logo-mark">🍉</span><span><strong>FRUTA MADRE</strong><small>PUESTO • MATRIZ • GDL</small></span></a>
<div class="estado-mercado">ESTADO: MERCADO ABIERTO <span style="color:#477a45">●</span></div>
<nav class="nav" aria-label="Administración"><?php foreach($modulos as $ruta=>$datos): ?>
<a class="nav-link <?= $activo===$ruta?'active':'' ?>" href="index.php?accion=<?= stitchEscape($ruta) ?>"><span class="sym"><?= $datos[0] ?></span><?= stitchEscape($datos[1]) ?><?= $activo===$ruta?'<span class="pill">✓</span>':'' ?></a>
<?php endforeach; ?></nav><a class="logout" href="index.php?accion=logout">⇥ CERRAR SESIÓN</a></aside>
<section class="workspace"><header class="topbar"><small>PUESTO MATRIZ / <b style="color:#477a45">OPERACIONES</b></small><a href="index.php?accion=admin-productos" class="btn" style="min-height:32px">⌕ Buscar productos</a><div class="user-tag"><?= $nombre ?><br><span style="color:#477a45;font-size:9px">ADMINISTRADOR</span></div></header>
<main class="main"><section class="lead"><span class="eyebrow">☷ PUESTO DIGITAL · CONTROL MATRIZ</span> <span class="eyebrow green">● PostgreSQL conectado</span>
<h1><?= stitchEscape($titulo) ?></h1><p><?= stitchEscape($descripcion) ?></p></section>
<?php }
function stitchFin():void {echo '</main></section></div></body></html>';}
