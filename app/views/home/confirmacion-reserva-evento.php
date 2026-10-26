<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$reserva = $reserva ?? ($_SESSION['reserva_evento_creada'] ?? null);

if (!$reserva || empty($reserva['id_reserva'])) {
    header('Location: index.php?accion=eventos');
    exit;
}

$escapar = static function ($valor): string {
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
};

$idReserva = (int) $reserva['id_reserva'];
$folio = $reserva['folio'] ?? ('FM-EV-' . str_pad((string) $idReserva, 6, '0', STR_PAD_LEFT));

$fecha = $reserva['fecha_evento'] ?? '';
$horaInicio = $reserva['hora_evento'] ?? '';
$invitados = (int) ($reserva['invitados'] ?? 0);
$direccion = $reserva['direccion'] ?? '';
$municipio = $reserva['municipio'] ?? '';
$colonia = $reserva['colonia'] ?? '';
$paquete = $reserva['nombre_paquete'] ?? 'Paquete Pachanga';
$estado = $reserva['estado'] ?? 'Pendiente de confirmación';
$metodoPago = $reserva['metodo_pago'] ?? 'Por confirmar';

$precioBase = (float) ($reserva['precio_base'] ?? 3450);
$descuento = (float) ($reserva['descuento'] ?? 250);
$anticipo = (float) ($reserva['anticipo'] ?? 0);
$total = (float) ($reserva['total'] ?? max(0, $precioBase - $descuento));
$saldo = max(0, $total - $anticipo);

$extras = $reserva['extras'] ?? [];

$fechaLegible = 'Por confirmar';

if ($fecha !== '') {
    try {
        $fechaObjeto = new DateTimeImmutable($fecha);
        $fechaLegible = $fechaObjeto->format('d/m/Y');
    } catch (Throwable $e) {
        $fechaLegible = (string) $fecha;
    }
}

$horaLegible = 'Por confirmar';

if ($horaInicio !== '') {
    $horaLegible = substr((string) $horaInicio, 0, 5);
}

$ubicacion = implode(', ', array_filter([
    $direccion,
    $colonia,
    $municipio
], static fn($valor) => trim((string) $valor) !== ''));

if ($ubicacion === '') {
    $ubicacion = 'Por confirmar';
}

$estadoNormalizado = strtolower((string) $estado);
$reservaConfirmada = str_contains($estadoNormalizado, 'confirmad');
$estadoPago = $reserva['estado_pago'] ?? 'Pendiente de confirmación';

$dinero = static fn($cantidad): string =>
    '$' . number_format((float) $cantidad, 2, '.', ',') . ' MXN';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#fff8ed">
<title>Confirmación de reserva | Fruta Madre</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Syne:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">

<style>
:root {
    --cream: #fff8ed;
    --card: #fffaf0;
    --green: #2f612f;
    --green-light: #477a45;
    --pink: #fc6688;
    --orange: #b55107;
    --ink: #201b0f;
    --muted: #626456;
    --line: #e9ddc5;
}
* { box-sizing: border-box; }
body {
    margin: 0;
    background: var(--cream);
    color: var(--ink);
    font-family: 'DM Sans', sans-serif;
    font-size: 14px;
}
h1,h2,h3,h4,.heading { font-family: 'Syne', sans-serif; }
a { color: inherit; text-decoration: none; }
button { font: inherit; cursor: pointer; }
.page-shell { max-width: 1440px; margin: auto; }
.topbar {
    position: sticky;
    top: 0;
    z-index: 10;
    background: rgba(255,248,237,.97);
    border-bottom: 1px solid var(--line);
}
.topbar-inner {
    max-width: 1440px;
    margin: auto;
    padding: 13px 32px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
}
.brand { display: flex; align-items: center; gap: 10px; }
.brand-mark {
    width: 36px;
    height: 36px;
    display: grid;
    place-items: center;
    color: var(--green);
    border: 2px solid var(--green);
    border-radius: 9px;
}
.brand-name { font-family: 'Syne', sans-serif; font-size: 16px; font-weight: 800; }
.brand-tag { display: block; font-size: 9px; color: var(--pink); font-weight: 800; }
.nav-links { display: flex; gap: 22px; font-size: 12px; font-weight: 800; }
.nav-links a:hover { color: var(--green); }
.nav-actions { display: flex; gap: 8px; }
.nav-icon {
    width: 34px;
    height: 34px;
    display: grid;
    place-items: center;
    border: 1px solid var(--line);
    border-radius: 50%;
    background: white;
}
.color-strip { display: flex; height: 3px; }
.color-strip span { flex: 1; }
.page-content { padding: 28px 32px 38px; }
.breadcrumbs { display: flex; flex-wrap: wrap; gap: 8px; font-size: 11px; color: var(--muted); margin-bottom: 22px; }
.hero { margin-bottom: 24px; }
.tags { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; }
.pill { display: inline-block; border-radius: 20px; padding: 7px 12px; font-size: 10px; font-weight: 800; text-transform: uppercase; }
.pill-green { background: var(--green); color: white; }
.pill-pink { background: var(--pink); color: #6b0027; }
.hero h1 { margin: 0; font-size: clamp(30px, 4vw, 48px); line-height: 1.1; letter-spacing: -.04em; text-transform: uppercase; }
.hero p { max-width: 700px; font-size: 15px; line-height: 1.7; color: var(--muted); }
.layout { display: grid; grid-template-columns: minmax(0,1.65fr) minmax(290px,.85fr); gap: 20px; align-items: start; }
.column { display: flex; flex-direction: column; gap: 17px; min-width: 0; }
.card { background: var(--card); border: 1px solid var(--line); border-radius: 10px; box-shadow: 2px 2px 0 var(--ink); padding: 20px; }
.confirmation { text-align: center; padding: 32px 22px; }
.success-icon {
    display: grid;
    place-items: center;
    margin: 0 auto 16px;
    width: 72px;
    height: 72px;
    border-radius: 50%;
    background: #e6f1df;
    color: var(--green);
}
.success-icon .material-symbols-outlined { font-size: 40px; }
.eyebrow { color: var(--pink); font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .06em; }
.confirmation h2 { font-size: clamp(25px,3vw,36px); margin: 10px 0; }
.confirmation p { color: var(--muted); font-size: 14px; line-height: 1.7; max-width: 620px; margin: auto; }
.folio {
    margin: 24px auto 0;
    padding: 16px;
    max-width: 390px;
    border: 1px dashed #c9b99f;
    border-radius: 9px;
    background: #fff4df;
}
.folio span { display: block; color: var(--muted); font-size: 11px; margin-bottom: 6px; }
.folio strong { color: var(--green); font-family: 'Syne',sans-serif; font-size: 26px; overflow-wrap: anywhere; }
.section-heading { margin-bottom: 17px; }
.section-heading h2 { margin: 0; font-size: 19px; }
.section-heading p { margin: 5px 0 0; color: var(--muted); font-size: 12px; line-height: 1.6; }
.timeline { display: grid; grid-template-columns: repeat(4,minmax(0,1fr)); }
.timeline-step { position: relative; text-align: center; padding: 0 6px; }
.timeline-step:not(:last-child)::after {
    content: '';
    position: absolute;
    top: 15px;
    left: calc(50% + 18px);
    width: calc(100% - 36px);
    height: 2px;
    background: var(--line);
}
.timeline-step.done:not(:last-child)::after { background: var(--green); }
.timeline-icon {
    width: 32px;
    height: 32px;
    display: grid;
    place-items: center;
    margin: 0 auto 9px;
    position: relative;
    z-index: 1;
    border-radius: 50%;
    background: #f1e7d4;
    color: var(--muted);
}
.timeline-step.done .timeline-icon { background: var(--green); color: white; }
.timeline-step.current .timeline-icon { outline: 3px solid #dce8d2; }
.timeline-step strong { display: block; font-size: 11px; line-height: 1.4; }
.timeline-step small { display: block; color: var(--muted); font-size: 10px; margin-top: 4px; line-height: 1.4; }
.detail-list { display: flex; flex-direction: column; }
.detail-row { display: flex; align-items: flex-start; justify-content: space-between; gap: 15px; padding: 13px 0; border-bottom: 1px solid var(--line); }
.detail-row:last-child { border-bottom: 0; }
.detail-label { display: flex; gap: 9px; align-items: flex-start; color: var(--muted); font-size: 12px; }
.detail-label .material-symbols-outlined { font-size: 18px; color: var(--green); }
.detail-value { max-width: 58%; text-align: right; font-size: 12px; font-weight: 800; overflow-wrap: anywhere; }
.status { display: inline-block; padding: 6px 9px; border-radius: 20px; background: #f8edd8; color: #80501d; font-size: 10px; font-weight: 800; }
.status-green { background: #e5f2df; color: var(--green); }
.package-card { display: flex; gap: 14px; align-items: center; background: #f8edd8; padding: 14px; border-radius: 8px; }
.package-image {
    width: 76px;
    height: 76px;
    display: grid;
    place-items: center;
    flex-shrink: 0;
    background: #f1e2c5;
    border-radius: 8px;
    color: var(--green);
}
.package-image .material-symbols-outlined { font-size: 38px; }
.package-info h3 { margin: 0; font-size: 16px; }
.package-info p { margin: 5px 0; color: var(--muted); font-size: 11px; line-height: 1.5; }
.package-info strong { color: var(--green); font-size: 13px; }
.extra-list { margin-top: 13px; }
.extra-row { display: flex; justify-content: space-between; gap: 12px; padding: 8px 0; font-size: 12px; border-bottom: 1px solid var(--line); }
.extra-row:last-child { border-bottom: 0; }
.extra-row span { color: var(--muted); }
.money-row { display: flex; justify-content: space-between; gap: 12px; padding: 9px 0; font-size: 12px; }
.money-row span { color: var(--muted); }
.money-row strong { text-align: right; }
.money-total { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 16px; margin-top: 8px; border-radius: 8px; background: #f8edd8; }
.money-total span { font-size: 11px; font-weight: 800; text-transform: uppercase; }
.money-total strong { color: var(--green); font-family: 'Syne',sans-serif; font-size: clamp(23px,2vw,32px); }
.payment-box { padding: 14px; border-radius: 8px; background: #f8edd8; margin-top: 14px; }
.payment-box strong { font-size: 12px; }
.payment-box p { font-size: 11px; line-height: 1.6; color: var(--muted); margin: 6px 0 0; }
.button {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 14px 16px;
    border-radius: 6px;
    font-family: 'Syne',sans-serif;
    font-size: 12px;
    font-weight: 800;
    border: 1px solid var(--ink);
    box-shadow: 2px 2px 0 var(--ink);
    margin-top: 12px;
}
.button-primary { color: white; background: var(--green); }
.button-primary:hover { background: var(--green-light); }
.button-secondary { background: #f8edd8; }
.button-secondary:hover { background: #f1e2c5; }
.button .material-symbols-outlined { font-size: 18px; }
.note { padding: 13px; background: #fff4df; border-radius: 7px; font-size: 11px; line-height: 1.6; color: var(--muted); margin-top: 14px; }
.faq-item { padding: 12px 0; border-bottom: 1px solid var(--line); }
.faq-item:last-child { border-bottom: 0; }
.faq-item strong { font-size: 12px; }
.faq-item p { color: var(--muted); font-size: 11px; line-height: 1.6; margin: 6px 0 0; }
.footer { margin-top: 28px; background: #fff4df; border-top: 1px solid var(--line); }
.footer-grid { max-width: 1440px; margin: auto; padding: 26px 32px; display: grid; grid-template-columns: 2fr 1fr 1fr 1.2fr; gap: 25px; }
.footer h3 { font-size: 12px; margin: 0 0 10px; }
.footer p,.footer li { color: var(--muted); font-size: 11px; line-height: 1.7; }
.footer ul { list-style: none; padding: 0; margin: 0; }
.footer-brand { color: var(--green); font-family: 'Syne',sans-serif; font-size: 19px; font-weight: 800; }
.footer-tag { color: var(--pink) !important; font-weight: 800; }
.footer-bottom { border-top: 1px solid var(--line); padding: 12px 32px; display: flex; justify-content: space-between; gap: 12px; color: var(--muted); font-size: 10px; }
@media(max-width:900px) {
    .topbar-inner,.page-content { padding-left: 18px; padding-right: 18px; }
    .layout { grid-template-columns: minmax(0,1fr); }
    .footer-grid { padding-left: 18px; padding-right: 18px; }
}
@media(max-width:650px) {
    .nav-links { display: none; }
    .topbar-inner { padding: 12px; }
    .page-content { padding: 18px 12px 28px; }
    .card { padding: 15px; }
    .confirmation { padding: 25px 16px; }
    .timeline-step strong { font-size: 10px; }
    .timeline-step small { font-size: 9px; }
    .detail-row { gap: 10px; }
    .detail-value { max-width: 55%; }
    .footer-grid { grid-template-columns: repeat(2,minmax(0,1fr)); padding: 22px 14px; gap: 18px; }
    .footer-grid > div:first-child { grid-column: 1 / -1; }
    .footer-bottom { padding: 12px 14px; flex-direction: column; }
}
@media(max-width:390px) {
    .timeline-step { padding: 0 2px; }
    .timeline-step strong { font-size: 9px; }
    .timeline-step small { font-size: 8px; }
    .detail-label,.detail-value { font-size: 11px; }
    .package-image { width: 58px; height: 58px; }
}
</style>
</head>

<body>
<header class="topbar">
    <div class="topbar-inner">
        <a href="index.php" class="brand">
            <span class="brand-mark"><span class="material-symbols-outlined">nutrition</span></span>
            <span>
                <span class="brand-name">FRUTA MADRE</span>
                <span class="brand-tag">TE DAMOS HASTA PARA LLEVAR</span>
            </span>
        </a>

        <nav class="nav-links">
            <a href="index.php">Inicio</a>
            <a href="index.php?accion=catalogo">Catálogo</a>
            <a href="index.php?accion=eventos">Eventos</a>
            <a href="index.php?accion=mis-pedidos">Mis pedidos</a>
        </nav>

        <div class="nav-actions">
            <a class="nav-icon" href="index.php?accion=catalogo" aria-label="Buscar"><span class="material-symbols-outlined">search</span></a>
            <a class="nav-icon" href="index.php?accion=carrito" aria-label="Carrito"><span class="material-symbols-outlined">shopping_bag</span></a>
            <a class="nav-icon" href="index.php?accion=login" aria-label="Mi cuenta"><span class="material-symbols-outlined">person</span></a>
        </div>
    </div>
    <div class="color-strip">
        <span style="background:var(--green)"></span>
        <span style="background:var(--pink)"></span>
        <span style="background:#f8edd8"></span>
        <span style="background:var(--orange)"></span>
    </div>
</header>

<main class="page-shell">
<div class="page-content">
    <nav class="breadcrumbs">
        <a href="index.php">⌂ Inicio</a><span>/</span>
        <a href="index.php?accion=eventos">Eventos</a><span>/</span>
        <strong>Confirmación de reserva</strong>
    </nav>

    <section class="hero">
        <div class="tags">
            <span class="pill pill-green">● FRUTA MADRE · EVENTOS</span>
            <span class="pill pill-pink">✦ RESERVA RECIBIDA</span>
        </div>
        <h1>¡Ya quedó la pachanga!</h1>
        <p>Gracias por elegir Fruta Madre. Aquí tienes los detalles de tu reserva para que tengas todo a la mano. Nuestro equipo revisará la solicitud y te confirmará los últimos detalles.</p>
    </section>

    <div class="layout">
        <div class="column">
            <section class="card confirmation">
                <div class="success-icon">
                    <span class="material-symbols-outlined">celebration</span>
                </div>
                <div class="eyebrow">Tu evento empieza aquí</div>
                <h2>¡Gracias por reservar con Fruta Madre!</h2>
                <p>Hemos recibido tu solicitud. Conserva tu folio para cualquier consulta relacionada con tu evento.</p>

                <div class="folio">
                    <span>FOLIO DE TU RESERVA</span>
                    <strong><?= $escapar($folio) ?></strong>
                </div>

                <div style="margin-top:17px">
                    <span class="status <?= $reservaConfirmada ? 'status-green' : '' ?>">
                        <?= $escapar($estado) ?>
                    </span>
                </div>
            </section>

            <section class="card">
                <div class="section-heading">
                    <h2>El camino de tu pachanga</h2>
                    <p>Así avanza tu reservación desde que recibimos la solicitud.</p>
                </div>

                <div class="timeline">
                    <div class="timeline-step done">
                        <div class="timeline-icon"><span class="material-symbols-outlined">task_alt</span></div>
                        <strong>Solicitud recibida</strong>
                        <small>Registro realizado</small>
                    </div>
                    <div class="timeline-step <?= $reservaConfirmada ? 'done current' : 'current' ?>">
                        <div class="timeline-icon"><span class="material-symbols-outlined">event_available</span></div>
                        <strong>Confirmación</strong>
                        <small><?= $reservaConfirmada ? 'Reserva confirmada' : 'Pendiente de revisión' ?></small>
                    </div>
                    <div class="timeline-step">
                        <div class="timeline-icon"><span class="material-symbols-outlined">shopping_basket</span></div>
                        <strong>Preparación</strong>
                        <small>Antes del evento</small>
                    </div>
                    <div class="timeline-step">
                        <div class="timeline-icon"><span class="material-symbols-outlined">celebration</span></div>
                        <strong>¡La pachanga!</strong>
                        <small>Día del evento</small>
                    </div>
                </div>

                <div class="note">La solicitud recibida no significa que la fecha esté confirmada todavía. La reservación queda sujeta a la revisión de disponibilidad por parte del equipo de Fruta Madre.</div>
            </section>

            <section class="card">
                <div class="section-heading">
                    <h2>Los detalles de tu evento</h2>
                    <p>Guarda esta información para tenerla disponible cuando la necesites.</p>
                </div>

                <div class="detail-list">
                    <div class="detail-row">
                        <div class="detail-label"><span class="material-symbols-outlined">confirmation_number</span>Folio de reserva</div>
                        <div class="detail-value"><?= $escapar($folio) ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label"><span class="material-symbols-outlined">calendar_month</span>Fecha del evento</div>
                        <div class="detail-value"><?= $escapar($fechaLegible) ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label"><span class="material-symbols-outlined">schedule</span>Hora de inicio</div>
                        <div class="detail-value"><?= $escapar($horaLegible) ?><?= $horaInicio !== '' ? ' hrs' : '' ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label"><span class="material-symbols-outlined">group</span>Número de invitados</div>
                        <div class="detail-value"><?= $invitados > 0 ? $invitados . ($invitados === 1 ? ' persona' : ' personas') : 'Por confirmar' ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label"><span class="material-symbols-outlined">location_on</span>Ubicación</div>
                        <div class="detail-value"><?= $escapar($ubicacion) ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label"><span class="material-symbols-outlined">celebration</span>Motivo del evento</div>
                        <div class="detail-value"><?= $escapar($reserva['motivo_evento'] ?? 'Evento especial') ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label"><span class="material-symbols-outlined">payments</span>Estado del pago</div>
                        <div class="detail-value"><?= $escapar($estadoPago) ?></div>
                    </div>
                </div>
            </section>

            <section class="card">
                <div class="section-heading">
                    <h2>¿Qué incluye tu pachanga?</h2>
                    <p>Este es el paquete asociado a tu solicitud.</p>
                </div>

                <div class="package-card">
                    <div class="package-image">
                        <span class="material-symbols-outlined">nutrition</span>
                    </div>
                    <div class="package-info">
                        <h3><?= $escapar($paquete) ?></h3>
                        <p>Servicio para eventos · <?= $invitados > 0 ? $invitados . ' invitados' : 'Invitados por confirmar' ?></p>
                        <strong><?= $dinero($precioBase) ?></strong>
                    </div>
                </div>

                <?php if (!empty($extras) && is_array($extras)): ?>
                    <div class="extra-list">
                        <?php foreach ($extras as $extra): ?>
                            <?php
                            if (is_array($extra)) {
                                $nombreExtra = $extra['nombre'] ?? $extra['label'] ?? 'Servicio adicional';
                                $precioExtra = (float) ($extra['precio'] ?? $extra['price'] ?? 0);
                            } else {
                                $nombreExtra = (string) $extra;
                                $precioExtra = 0;
                            }
                            ?>
                            <div class="extra-row">
                                <span><?= $escapar($nombreExtra) ?></span>
                                <strong><?= $dinero($precioExtra) ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="note">No se registraron servicios adicionales para esta solicitud.</div>
                <?php endif; ?>
            </section>
        </div>

        <aside class="column">
            <section class="card">
                <div class="section-heading">
                    <div class="eyebrow">Tu resumen de fiesta</div>
                    <h2 style="margin-top:5px">Resumen de reserva</h2>
                </div>

                <div class="money-row">
                    <span>Paquete base</span>
                    <strong><?= $dinero($precioBase) ?></strong>
                </div>

                <?php
                $totalExtras = 0;
                foreach ($extras as $extra) {
                    if (is_array($extra)) {
                        $totalExtras += (float) ($extra['precio'] ?? $extra['price'] ?? 0);
                    }
                }
                ?>

                <?php if ($totalExtras > 0): ?>
                    <div class="money-row">
                        <span>Servicios adicionales</span>
                        <strong><?= $dinero($totalExtras) ?></strong>
                    </div>
                <?php endif; ?>

                <div class="money-row">
                    <span>Descuento</span>
                    <strong style="color:var(--pink)">−<?= $dinero($descuento) ?></strong>
                </div>

                <div class="money-total">
                    <span>Total estimado</span>
                    <strong><?= $dinero($total) ?></strong>
                </div>

                <p style="font-size:10px;color:var(--muted);line-height:1.6;margin-top:10px">
                    El importe mostrado es el registrado para esta solicitud. Consulta con el equipo cualquier ajuste pendiente.
                </p>

                <div class="payment-box">
                    <strong>Anticipo registrado: <?= $dinero($anticipo) ?></strong>
                    <p>Saldo restante estimado: <strong><?= $dinero($saldo) ?></strong></p>
                    <p>Método de pago: <?= $escapar($metodoPago) ?></p>
                </div>

                <button type="button" class="button button-primary" onclick="window.print()">
                    <span>Imprimir comprobante</span>
                    <span class="material-symbols-outlined">print</span>
                </button>

                <button type="button" id="addCalendar" class="button button-secondary">
                    <span>Agregar al calendario</span>
                    <span class="material-symbols-outlined">calendar_add_on</span>
                </button>

                <a class="button button-secondary" href="index.php?accion=eventos">
                    <span>Ver paquetes de eventos</span>
                    <span class="material-symbols-outlined">arrow_forward</span>
                </a>

                <div class="note">
                    <strong>¿Necesitas ayuda?</strong><br>
                    Contacta al equipo de Fruta Madre para consultar el estado de tu reservación.
                </div>
            </section>

            <section class="card">
                <div class="section-heading">
                    <h2>Preguntas frecuentes</h2>
                </div>
                <div class="faq-item">
                    <strong>¿Mi reserva ya está confirmada?</strong>
                    <p>Solo si el estado de tu reservación indica que fue confirmada. Si aparece pendiente, el equipo debe revisar la disponibilidad.</p>
                </div>
                <div class="faq-item">
                    <strong>¿Qué pasa con mi anticipo?</strong>
                    <p>El importe mostrado corresponde al dato registrado en la solicitud. Confirma las condiciones de pago con el equipo.</p>
                </div>
                <div class="faq-item">
                    <strong>¿Puedo cambiar los detalles?</strong>
                    <p>Comunícate con Fruta Madre para consultar si es posible modificar la fecha, el horario o los servicios.</p>
                </div>
            </section>
        </aside>
    </div>
</div>
</main>

<footer class="footer">
    <div class="color-strip">
        <span style="background:var(--green)"></span>
        <span style="background:var(--pink)"></span>
        <span style="background:#f8edd8"></span>
        <span style="background:var(--orange)"></span>
    </div>
    <div class="footer-grid">
        <div>
            <div class="footer-brand">FRUTA MADRE</div>
            <p class="footer-tag">TE DAMOS HASTA PARA LLEVAR.</p>
            <p>Fruta fresca, chilito, gomitas y carritos de antojos para celebrar tus mejores momentos.</p>
        </div>
        <div>
            <h3>EXPLORAR</h3>
            <ul>
                <li><a href="index.php">Inicio</a></li>
                <li><a href="index.php?accion=catalogo">Catálogo</a></li>
                <li><a href="index.php?accion=eventos">Eventos</a></li>
            </ul>
        </div>
        <div>
            <h3>ATENCIÓN</h3>
            <ul>
                <li>Contacto</li>
                <li>Preguntas frecuentes</li>
                <li>Aviso de privacidad</li>
            </ul>
        </div>
        <div>
            <h3>MERCADO SOCIAL</h3>
            <p>¡Échanos un grito y síguenos la pista!</p>
            <p>Instagram · TikTok · WhatsApp</p>
        </div>
    </div>
    <div class="footer-bottom">
        <span>© <?= date('Y') ?> Fruta Madre. Todos los derechos reservados.</span>
        <span class="heading">PUESTO DIGITAL NO. 01</span>
    </div>
</footer>

<script>
(() => {
    const fecha = <?= json_encode($fecha, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    const hora = <?= json_encode($horaInicio, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    const folio = <?= json_encode($folio, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

    document.getElementById('addCalendar').addEventListener('click', () => {
        if (!fecha) {
            alert('La fecha del evento todavía no está disponible para agregar al calendario.');
            return;
        }

        const startDate = new Date(`${fecha}T${hora || '12:00'}:00`);
        if (Number.isNaN(startDate.getTime())) {
            alert('No se pudo interpretar la fecha u hora de la reservación.');
            return;
        }

        const endDate = new Date(startDate.getTime() + 3 * 60 * 60 * 1000);
        const toCalendarDate = date => date.toISOString().replace(/[-:]/g, '').replace(/\.\d{3}/, '');

        const title = `Reserva Fruta Madre ${folio}`;
        const description = `Reserva de evento Fruta Madre. Folio: ${folio}`;
        const url = new URL('https://calendar.google.com/calendar/render');
        url.searchParams.set('action', 'TEMPLATE');
        url.searchParams.set('text', title);
        url.searchParams.set('dates', `${toCalendarDate(startDate)}/${toCalendarDate(endDate)}`);
        url.searchParams.set('details', description);

        window.open(url.toString(), '_blank', 'noopener,noreferrer');
    });
})();
</script>
</body>
</html>