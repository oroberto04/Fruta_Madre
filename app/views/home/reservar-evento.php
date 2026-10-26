<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reserva de Evento | Fruta Madre</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Syne:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
  theme: {
    extend: {
      colors: {
        primary: '#2f612f',
        'primary-light': '#477a45',
        secondary: '#fc6688',
        cream: '#fff8ed',
        'cream-card': '#fffaf0',
        ink: '#201b0f',
        muted: '#626456',
        orange: '#b55107'
      },
      fontFamily: {
        heading: ['Syne', 'sans-serif'],
        body: ['DM Sans', 'sans-serif']
      }
    }
  }
};
</script>
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
html { scroll-behavior: smooth; }
body {
  margin: 0;
  background: var(--cream);
  color: var(--ink);
  font-family: 'DM Sans', sans-serif;
}
h1,h2,h3,h4,.font-heading { font-family: 'Syne', sans-serif; }
button,input,select,textarea { font: inherit; }
button { -webkit-tap-highlight-color: transparent; }
a { text-decoration: none; color: inherit; }
button:focus-visible,input:focus-visible,select:focus-visible,
textarea:focus-visible,a:focus-visible {
  outline: 2px solid var(--green-light);
  outline-offset: 2px;
}
.page-shell { max-width: 1440px; margin: 0 auto; }
.topbar {
  position: sticky; top: 0; z-index: 50;
  background: rgba(255,248,237,.97);
  border-bottom: 1px solid var(--line);
}
.topbar-inner {
  max-width: 1440px; min-height: 68px; margin: auto;
  padding: 10px 32px;
  display: flex; align-items: center; justify-content: space-between; gap: 18px;
}
.brand { display: flex; align-items: center; gap: 9px; flex-shrink: 0; }
.brand-mark {
  width: 31px; height: 31px; border: 2px solid var(--green);
  border-radius: 8px; display: grid; place-items: center; color: var(--green);
}
.brand-name { font-family: 'Syne',sans-serif; font-weight: 800; font-size: 14px; line-height: 1; }
.brand-tag { font-size: 8px; color: var(--pink); font-weight: 800; letter-spacing: .05em; margin-top: 3px; }
.nav-links { display: flex; align-items: center; gap: 19px; font-size: 11px; font-weight: 700; }
.nav-links a:hover { color: var(--green); }
.nav-actions { display: flex; align-items: center; gap: 7px; }
.nav-icon {
  width: 29px; height: 29px; border: 1px solid var(--line);
  border-radius: 50%; display: grid; place-items: center; background: white;
}
.nav-icon .material-symbols-outlined { font-size: 16px; }
.color-strip { height: 3px; display: flex; }
.color-strip span { flex: 1; }
.page-content { padding: 21px 32px 32px; }
.breadcrumbs { display: flex; align-items: center; gap: 7px; font-size: 9px; color: var(--muted); margin-bottom: 15px; }
.hero { margin-bottom: 21px; }
.hero-tags { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 8px; }
.pill {
  border-radius: 20px; padding: 5px 10px; font-size: 8px;
  font-weight: 800; letter-spacing: .025em; text-transform: uppercase;
}
.pill-green { background: var(--green); color: white; }
.pill-pink { background: var(--pink); color: #6b0027; }
.hero h1 { font-size: clamp(24px,3vw,37px); line-height: 1.05; font-weight: 800; margin: 0; text-transform: uppercase; letter-spacing: -.04em; }
.hero p { font-size: 11px; line-height: 1.55; color: var(--muted); max-width: 580px; margin-top: 7px; }
.hero-badge {
  background: #f8edd8; color: var(--orange); border-radius: 20px;
  padding: 7px 12px; font-size: 8px; font-weight: 800;
}
.main-grid {
  display: grid; grid-template-columns: minmax(0, 1.9fr) minmax(260px, .9fr);
  align-items: start; gap: 17px;
}
.form-column { display: flex; flex-direction: column; gap: 13px; min-width: 0; }
.panel {
  background: var(--card); border: 1px solid #f0e5d0; border-radius: 9px;
  box-shadow: 2px 2px 0 var(--ink); padding: 15px;
}
.package-panel {
  display: flex; align-items: center; justify-content: space-between; gap: 12px;
  padding: 11px;
}
.package-info { display: flex; align-items: center; gap: 10px; min-width: 0; }
.package-image {
  width: 52px; height: 52px; object-fit: cover; border-radius: 6px;
  background: #f2e7d2; flex-shrink: 0;
}
.eyebrow { color: var(--pink); font-size: 8px; font-weight: 800; text-transform: uppercase; }
.package-title { font-family: 'Syne',sans-serif; font-size: 14px; font-weight: 800; }
.package-subtitle { color: var(--muted); font-size: 9px; margin-top: 2px; }
.package-price { color: var(--green); font-weight: 800; font-size: 10px; margin-top: 3px; }
.small-button {
  display: inline-flex; align-items: center; justify-content: center; gap: 4px;
  padding: 7px 10px; background: #f8edd8; border-radius: 6px;
  font-size: 8px; font-weight: 800; white-space: nowrap; border: 0; cursor: pointer;
}
.small-button:hover { background: #eee1c8; }
.section-title { display: flex; align-items: flex-start; gap: 8px; margin-bottom: 12px; }
.step {
  flex: 0 0 18px; width: 18px; height: 18px; display: grid; place-items: center;
  background: var(--green); color: white; border-radius: 50%; font-size: 10px; font-weight: 800;
}
.section-title h2 { font-size: 13px; font-weight: 800; line-height: 1.2; margin: 1px 0 0; }
.section-title p { color: var(--muted); font-size: 9px; margin-top: 4px; line-height: 1.4; }
.calendar-box { border: 1px solid var(--line); border-radius: 7px; overflow: hidden; }
.calendar-head {
  display: flex; align-items: center; justify-content: space-between; gap: 8px;
  padding: 7px 9px; background: #f8edd8;
}
.month-title { font-family: 'Syne',sans-serif; font-size: 10px; font-weight: 800; text-align: center; }
.month-button {
  display: grid; place-items: center; width: 23px; height: 23px;
  border: 0; border-radius: 5px; background: white; cursor: pointer;
}
.month-button .material-symbols-outlined { font-size: 16px; }
.month-button:disabled { opacity: .3; cursor: not-allowed; }
.calendar-body { padding: 9px; background: #fff4df; }
.weekdays,.calendar-days { display: grid; grid-template-columns: repeat(7,minmax(0,1fr)); gap: 3px; }
.weekdays { margin-bottom: 4px; }
.weekdays span { text-align: center; color: var(--muted); font-size: 8px; font-weight: 800; padding: 3px 0; }
.calendar-day,.calendar-empty { min-width: 0; aspect-ratio: 1.25 / 1; }
.calendar-day {
  border: 1px solid transparent; border-radius: 5px; background: transparent;
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  gap: 2px; font-size: 9px; font-weight: 700; cursor: pointer;
}
.calendar-day:hover:not(:disabled):not(.selected) { background: #f1e4c9; }
.calendar-day.today:not(.selected) { border-color: var(--orange); }
.calendar-day.selected {
  color: white; background: var(--green); border-color: var(--ink);
  box-shadow: 1px 1px 0 var(--ink);
}
.calendar-day:disabled { color: #c4bcab; text-decoration: line-through; cursor: not-allowed; opacity: .7; }
.day-dot { width: 3px; height: 3px; border-radius: 50%; background: var(--pink); }
.calendar-day.selected .day-dot { background: white; }
.calendar-legend { display: flex; flex-wrap: wrap; gap: 12px; padding: 8px 1px 0; }
.legend-item { display: flex; align-items: center; gap: 4px; color: var(--muted); font-size: 8px; }
.legend-dot { width: 5px; height: 5px; border-radius: 50%; display: inline-block; }
.notice {
  display: flex; align-items: flex-start; gap: 6px; padding: 8px;
  border-radius: 5px; background: #f8edd8; color: var(--muted);
  font-size: 9px; line-height: 1.45; margin-top: 9px;
}
.notice .material-symbols-outlined { font-size: 14px; color: var(--orange); flex-shrink: 0; }
.time-grid { display: grid; grid-template-columns: repeat(6,minmax(0,1fr)); gap: 5px; }
.time-card {
  min-width: 0; padding: 8px 4px; text-align: center;
  border: 1px solid var(--line); background: #fff4df; border-radius: 5px;
  cursor: pointer; transition: background .15s,border-color .15s;
}
.time-card:hover:not(.active) { border-color: var(--green); }
.time-card.active { background: var(--green); color: white; border-color: var(--green); }
.time-card .time-start { display: block; font-size: 9px; font-weight: 800; }
.time-card .time-end { display: block; margin-top: 4px; font-size: 7px; line-height: 1.35; }
.time-card .time-status { display: block; font-size: 7px; margin-top: 4px; color: var(--muted); }
.time-card.active .time-status { color: #e8f5dc; }
.field-label { display: block; font-size: 8px; font-weight: 800; text-transform: uppercase; margin-bottom: 5px; }
.field {
  width: 100%; min-width: 0; padding: 9px 10px; border-radius: 5px;
  border: 1px solid transparent; background: #fff4df; color: var(--ink);
  font-size: 10px; outline: none;
}
.field:focus { background: white; border-color: var(--green); }
.field::placeholder { color: #9c9789; }
.field-grid { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: 10px; }
.guest-row { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.guest-counter { display: flex; align-items: center; gap: 5px; padding: 5px; background: #fff4df; border-radius: 6px; }
.guest-button {
  width: 24px; height: 24px; display: grid; place-items: center; border: 0;
  border-radius: 4px; background: white; cursor: pointer; font-weight: 800;
}
.guest-number { min-width: 48px; text-align: center; font-family: 'Syne',sans-serif; font-size: 22px; font-weight: 800; line-height: 1; }
.guest-number small { display: block; font-family: 'DM Sans',sans-serif; font-size: 7px; font-weight: 800; text-transform: uppercase; }
.guest-status { color: var(--green); font-size: 9px; font-weight: 800; }
.guest-help { color: var(--muted); font-size: 8px; margin-top: 3px; line-height: 1.45; }
.event-types { display: flex; flex-wrap: wrap; gap: 5px; }
.event-type {
  padding: 5px 8px; border: 1px solid transparent; border-radius: 14px;
  background: #f8edd8; font-size: 8px; font-weight: 800; cursor: pointer;
}
.event-type.active { background: var(--green); color: white; }
.addons { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: 7px; }
.addon {
  display: flex; align-items: flex-start; gap: 7px; padding: 9px;
  border-radius: 6px; background: #fff4df; border: 1px solid transparent; cursor: pointer;
}
.addon:has(input:checked) { border-color: var(--green); background: #f0f5e8; }
.addon input { width: 12px; height: 12px; accent-color: var(--green); flex-shrink: 0; margin: 1px 0 0; }
.addon-title { font-size: 9px; font-weight: 800; line-height: 1.35; }
.addon-description { color: var(--muted); font-size: 8px; line-height: 1.4; margin-top: 3px; }
.addon-price { color: var(--orange); font-size: 8px; font-weight: 800; margin-top: 4px; }
.summary {
  position: sticky; top: 85px; border: 1px solid #e5d8c0;
  border-radius: 9px; background: var(--card); box-shadow: 2px 2px 0 var(--ink);
  overflow: hidden;
}
.summary-top { padding: 13px; border-bottom: 1px solid var(--line); }
.summary-eyebrow { font-size: 8px; font-weight: 800; color: var(--pink); text-transform: uppercase; }
.summary-top h2 { font-size: 16px; font-weight: 800; margin-top: 2px; }
.summary-body { padding: 13px; }
.summary-package { display: flex; align-items: center; gap: 8px; padding: 8px; border-radius: 6px; background: #f8edd8; margin-bottom: 12px; }
.summary-package img { width: 45px; height: 45px; object-fit: cover; border-radius: 5px; flex-shrink: 0; }
.summary-package h3 { font-size: 10px; font-weight: 800; }
.summary-package p { font-size: 8px; color: var(--muted); margin-top: 2px; }
.summary-package strong { display: block; color: var(--green); font-size: 9px; margin-top: 2px; }
.summary-lines { display: flex; flex-direction: column; gap: 9px; }
.summary-line { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; font-size: 9px; }
.summary-line span { color: var(--muted); }
.summary-line strong { text-align: right; overflow-wrap: anywhere; }
.summary-divider { height: 1px; background: var(--line); margin: 12px 0; }
.summary-lines-title { font-size: 10px; font-weight: 800; margin-bottom: 9px; }
.summary-total { padding: 11px; background: #f8edd8; border-radius: 6px; margin-top: 12px; }
.total-label { font-size: 8px; font-weight: 800; text-transform: uppercase; }
.total-amount { font-family: 'Syne',sans-serif; font-size: 26px; font-weight: 800; color: var(--green); }
.total-note { text-align: right; font-size: 7px; color: var(--muted); }
.summary-small { color: var(--muted); font-size: 8px; line-height: 1.5; margin-top: 8px; }
.deposit-box { background: #f8edd8; padding: 10px; border-radius: 6px; margin-top: 12px; }
.deposit-box strong { font-size: 9px; }
.deposit-box p { color: var(--muted); font-size: 8px; line-height: 1.45; margin-top: 5px; }
.primary-button {
  width: 100%; padding: 12px; background: var(--green); color: white;
  border: 1px solid var(--ink); border-radius: 5px; box-shadow: 2px 2px 0 var(--ink);
  font-family: 'Syne',sans-serif; font-size: 10px; font-weight: 800;
  text-transform: uppercase; display: flex; align-items: center;
  justify-content: space-between; gap: 8px; cursor: pointer; margin-top: 12px;
}
.primary-button:hover { background: var(--green-light); }
.primary-button .material-symbols-outlined { font-size: 16px; }
.form-message { display: none; padding: 10px; border-radius: 5px; font-size: 10px; }
.form-message.error { display: block; background: #ffdfd9; color: #93000a; }
.form-message.success { display: block; background: #e5f3df; color: #1f5121; }
.footer { border-top: 1px solid var(--line); background: #fff4df; margin-top: 26px; }
.footer-grid {
  max-width: 1440px; margin: auto; padding: 24px 32px;
  display: grid; grid-template-columns: 2fr 1fr 1fr 1.2fr; gap: 28px;
}
.footer h3 { font-size: 11px; font-weight: 800; margin-bottom: 9px; }
.footer p,.footer li { color: var(--muted); font-size: 9px; line-height: 1.7; }
.footer ul { list-style: none; padding: 0; margin: 0; }
.footer li { margin: 3px 0; }
.footer-brand { font-family: 'Syne',sans-serif; color: var(--green); font-size: 17px; font-weight: 800; }
.footer-tag { color: var(--pink) !important; font-weight: 800; font-size: 9px !important; }
.footer-chip { display: inline-block; background: #f8edd8; padding: 4px 7px; border-radius: 4px; font-size: 8px; font-weight: 800; margin: 3px 2px 0 0; }
.footer-bottom { border-top: 1px solid var(--line); padding: 10px 32px; display: flex; justify-content: space-between; gap: 10px; font-size: 8px; color: var(--muted); }
@media(min-width:1100px) {
  .page-content { padding-top: 21px; }
}
@media(max-width:900px) {
  .topbar-inner { padding: 10px 18px; }
  .page-content { padding: 18px; }
  .main-grid { grid-template-columns: minmax(0,1.5fr) minmax(240px, .9fr); gap: 12px; }
  .panel { padding: 12px; }
  .footer-grid { padding: 22px 18px; gap: 17px; }
  .footer-bottom { padding: 10px 18px; }
  .nav-links { gap: 10px; }
  .time-grid { grid-template-columns: repeat(3,minmax(0,1fr)); }
}
@media(max-width:650px) {
  .topbar-inner { padding: 10px 12px; }
  .nav-links { display: none; }
  .page-content { padding: 15px 12px 24px; }
  .main-grid { grid-template-columns: minmax(0,1fr); }
  .summary { position: static; }
  .footer-grid { grid-template-columns: repeat(2,minmax(0,1fr)); padding: 20px 14px; }
  .footer-grid > div:first-child { grid-column: 1 / -1; }
  .footer-bottom { padding: 10px 14px; flex-direction: column; }
  .field-grid { gap: 8px; }
  .panel { padding: 12px; }
  .package-panel { align-items: flex-start; }
  .package-panel > a { align-self: center; }
  .time-grid { grid-template-columns: repeat(3,minmax(0,1fr)); }
  .calendar-day,.calendar-empty { aspect-ratio: 1 / 1; }
}
@media(max-width:390px) {
  .brand-name { font-size: 12px; }
  .brand-tag { font-size: 7px; }
  .nav-actions { gap: 4px; }
  .field-grid { grid-template-columns: minmax(0,1fr); }
  .addons { grid-template-columns: minmax(0,1fr); }
  .time-card .time-start { font-size: 8px; }
}

/* ===== AJUSTE EXCLUSIVO DE TAMAÑOS DE LETRA ===== */
.brand-name { font-size: 16px; }
.brand-tag { font-size: 9px; }
.nav-links { font-size: 13px; }
.breadcrumbs { font-size: 11px; }
.pill { font-size: 9px; }
.hero h1 { font-size: clamp(30px, 3.2vw, 46px); }
.hero p { font-size: 14px; }
.hero-badge { font-size: 10px; }
.eyebrow { font-size: 10px; }
.package-title { font-size: 18px; }
.package-subtitle { font-size: 11px; }
.package-price { font-size: 12px; }
.small-button { font-size: 10px; }
.step { font-size: 12px; }
.section-title h2 { font-size: 17px; }
.section-title p { font-size: 12px; }
.month-title { font-size: 14px; }
.weekdays span { font-size: 10px; }
.calendar-day { font-size: 13px; }
.legend-item { font-size: 10px; }
.notice { font-size: 11px; }
.time-card .time-start { font-size: 12px; }
.time-card .time-end { font-size: 10px; }
.time-card .time-status { font-size: 9px; }
.field-label { font-size: 10px; }
.field { font-size: 13px; }
.guest-number { font-size: 26px; }
.guest-number small { font-size: 9px; }
.guest-status { font-size: 11px; }
.guest-help { font-size: 10px; }
.event-type { font-size: 10px; }
.addon-title { font-size: 11px; }
.addon-description { font-size: 10px; }
.addon-price { font-size: 10px; }
.summary-eyebrow { font-size: 10px; }
.summary-top h2 { font-size: 20px; }
.summary-package h3 { font-size: 12px; }
.summary-package p { font-size: 10px; }
.summary-package strong { font-size: 11px; }
.summary-line { font-size: 11px; }
.summary-lines-title { font-size: 13px; }
.total-label { font-size: 10px; }
.total-amount { font-size: clamp(28px, 2.2vw, 36px); }
.total-note { font-size: 9px; }
.summary-small { font-size: 10px; }
.deposit-box strong { font-size: 11px; }
.deposit-box p { font-size: 10px; }
.primary-button { font-size: 12px; }
.form-message { font-size: 12px; }
.footer h3 { font-size: 13px; }
.footer p, .footer li { font-size: 11px; }
.footer-tag { font-size: 10px !important; }
.footer-chip { font-size: 10px; }
.footer-bottom { font-size: 10px; }

@media (max-width: 390px) {
  .brand-name { font-size: 13px; }
  .brand-tag { font-size: 8px; }
  .time-card .time-start { font-size: 10px; }
}
</style>
</head>
<body>

<header class="topbar">
  <div class="topbar-inner">
    <a href="index.php" class="brand">
      <span class="brand-mark"><span class="material-symbols-outlined" style="font-size:20px">nutrition</span></span>
      <span>
        <span class="brand-name block">FRUTA MADRE</span>
        <span class="brand-tag block">TE DAMOS HASTA PARA LLEVAR</span>
      </span>
    </a>
    <nav class="nav-links">
      <a href="index.php">Inicio</a>
      <a href="index.php?accion=catalogo">Catálogo</a>
      <a href="index.php?accion=eventos">Eventos</a>
      <a href="index.php?accion=mis-pedidos">Mis pedidos</a>
    </nav>
    <div class="nav-actions">
      <a href="index.php?accion=catalogo" class="nav-icon" aria-label="Buscar"><span class="material-symbols-outlined">search</span></a>
      <a href="index.php?accion=carrito" class="nav-icon" aria-label="Carrito"><span class="material-symbols-outlined">shopping_bag</span></a>
      <button type="button" class="nav-icon" id="themeButton" aria-label="Cambiar tema"><span class="material-symbols-outlined">light_mode</span></button>
      <a href="index.php?accion=login" class="nav-icon" aria-label="Mi cuenta" style="background:var(--green);color:white"><span class="material-symbols-outlined">person</span></a>
    </div>
  </div>
  <div class="color-strip">
    <span style="background:var(--green)"></span><span style="background:var(--pink)"></span>
    <span style="background:#f8edd8"></span><span style="background:var(--orange)"></span>
  </div>
</header>

<main class="page-shell">
<div class="page-content">
  <nav class="breadcrumbs">
    <a href="index.php">⌂ Inicio</a><span>/</span>
    <a href="index.php?accion=eventos">Eventos</a><span>/</span>
    <strong>Reserva de Evento</strong>
  </nav>

  <section class="hero">
    <div class="flex flex-wrap items-end justify-between gap-4">
      <div>
        <div class="hero-tags">
          <span class="pill pill-green">● Puesto digital No. 01</span>
          <span class="pill pill-pink">✦ Reserva de pachanga</span>
        </div>
        <h1>Vamos a armar tu evento</h1>
        <p>Tú dinos cuándo y dónde. Nosotros nos encargamos de llevar el carrito hasta el chongo con fruta fresca, chilito piquín y gomitas.</p>
      </div>
      <span class="hero-badge">✦ COTIZA TU PACHANGA FRESQUITA</span>
    </div>
  </section>

  <form id="eventForm" action="index.php?accion=reservar-evento" method="POST">
    <input type="hidden" name="id_paquete" value="1">
    <input type="hidden" name="fecha_evento" id="fechaEvento" value="">
    <input type="hidden" name="hora_evento" id="horaEvento" value="13:00">
    <input type="hidden" name="motivo_evento" id="motivoEvento" value="Cumpleaños">
    <input type="hidden" name="invitados" id="invitados" value="45">

    <div class="main-grid">
      <div class="form-column">

        <section class="panel package-panel">
          <div class="package-info">
            <img class="package-image" alt="Paquete Pachanga" src="https://lh3.googleusercontent.com/aida-public/AB6AXuApnxA5C1I9Mu8yFMqFz9-c-DrEUYBOXTN9lsBS5ObLZVdQiQeWLCc58fZbUHzKnGJHjR9QwJgUKCTuKLi39VFCrpLRYhl9lTBINa_uExU3XwiTfW0CVLBQCEHFXZlN598MaKNl-uUOnA7iJMBgxuMAk_0Agsr0Wab2U115F9qd0_mWum9KhV-18vXjBwwdtFg7wi95wGIAr34KtsPXYG6pe6oSVK18GKUhPl3SqAAoDuDJsIc97-J">
            <div>
              <div class="eyebrow">Paquete seleccionado</div>
              <div class="package-title">Paquete Pachanga</div>
              <div class="package-subtitle">El consentido · Hasta 50 personas · 3 horas de servicio</div>
              <div class="package-price">Precio base: $3,450 MXN</div>
            </div>
          </div>
          <a href="index.php?accion=eventos" class="small-button">Cambiar paquete</a>
        </section>

        <section class="panel">
          <div class="section-title">
            <span class="step">1</span>
            <div>
              <h2>¿Cuándo es la pachanga?</h2>
              <p>Selecciona el día de tu evento en el puesto de reservas.</p>
            </div>
          </div>

          <div class="calendar-box">
            <div class="calendar-head">
              <button type="button" id="prevMonth" class="month-button" aria-label="Mes anterior"><span class="material-symbols-outlined">chevron_left</span></button>
              <h3 id="monthTitle" class="month-title">Cargando calendario...</h3>
              <button type="button" id="nextMonth" class="month-button" aria-label="Mes siguiente"><span class="material-symbols-outlined">chevron_right</span></button>
            </div>
            <div class="calendar-body">
              <div class="weekdays">
                <span>DOM</span><span>LUN</span><span>MAR</span><span>MIÉ</span><span>JUE</span><span>VIE</span><span>SÁB</span>
              </div>
              <div id="calendarDays" class="calendar-days" aria-label="Días del mes"></div>
              <div class="calendar-legend">
                <span class="legend-item"><i class="legend-dot" style="background:var(--green)"></i> Seleccionable</span>
                <span class="legend-item"><i class="legend-dot" style="background:var(--orange)"></i> Hoy</span>
                <span class="legend-item">× No disponible: fechas pasadas</span>
                <span class="legend-item" style="margin-left:auto"><i class="legend-dot" style="background:var(--pink)"></i> Fecha seleccionada</span>
              </div>
            </div>
          </div>
          <div class="notice"><span class="material-symbols-outlined">info</span><span>Selecciona una fecha para continuar. La disponibilidad definitiva se confirma al revisar tu solicitud.</span></div>
        </section>

        <section class="panel">
          <div class="section-title">
            <span class="step">2</span>
            <div>
              <h2>¿A qué hora empieza?</h2>
              <p>Elige la hora de inicio de tu evento. El servicio tiene una duración de 3 horas.</p>
            </div>
          </div>

          <div class="time-grid" id="timeOptions">
            <button type="button" class="time-card" data-time="09:00" aria-pressed="false">
              <span class="time-start">09:00 hrs</span><span class="time-end">Hasta 12:00 hrs</span><span class="time-status">Horario de inicio</span>
            </button>
            <button type="button" class="time-card" data-time="11:00" aria-pressed="false">
              <span class="time-start">11:00 hrs</span><span class="time-end">Hasta 14:00 hrs</span><span class="time-status">Horario de inicio</span>
            </button>
            <button type="button" class="time-card active" data-time="13:00" aria-pressed="true">
              <span class="time-start">13:00 hrs</span><span class="time-end">Hasta 16:00 hrs</span><span class="time-status">Horario seleccionado</span>
            </button>
            <button type="button" class="time-card" data-time="15:00" aria-pressed="false">
              <span class="time-start">15:00 hrs</span><span class="time-end">Hasta 18:00 hrs</span><span class="time-status">Horario de inicio</span>
            </button>
            <button type="button" class="time-card" data-time="17:00" aria-pressed="false">
              <span class="time-start">17:00 hrs</span><span class="time-end">Hasta 20:00 hrs</span><span class="time-status">Horario de inicio</span>
            </button>
            <button type="button" class="time-card" data-time="19:00" aria-pressed="false">
              <span class="time-start">19:00 hrs</span><span class="time-end">Hasta 22:00 hrs</span><span class="time-status">Horario de inicio</span>
            </button>
          </div>
          <div class="notice"><span class="material-symbols-outlined">schedule</span><span>Tu horario: <strong id="selectedTimeText">13:00 a 16:00 hrs</strong>. El equipo confirmará la disponibilidad.</span></div>
        </section>

        <section class="panel">
          <div class="section-title">
            <span class="step">3</span>
            <div>
              <h2>¿Cuánta gente va?</h2>
              <p>Elige el número aproximado de invitados.</p>
            </div>
          </div>
          <div class="guest-row">
            <div class="guest-counter">
              <button type="button" class="guest-button" id="decreaseGuests" aria-label="Disminuir invitados">−</button>
              <div class="guest-number"><span id="guestCount">45</span><small>personas</small></div>
              <button type="button" class="guest-button" id="increaseGuests" aria-label="Aumentar invitados">+</button>
            </div>
            <div>
              <div class="guest-status" id="guestStatus">● ¡Vas sobrado!</div>
              <div class="guest-help" id="guestHelp">Tu Paquete Pachanga cubre hasta 50 personas.</div>
            </div>
          </div>
        </section>

        <section class="panel">
          <div class="section-title">
            <span class="step">4</span>
            <div>
              <h2>¿Dónde va a ser el jolgorio?</h2>
              <p>Compártenos la dirección donde se realizará tu evento.</p>
            </div>
            <button type="button" id="clearAddress" class="small-button" style="margin-left:auto">Limpiar dirección</button>
          </div>
          <div class="field-grid">
            <div>
              <label class="field-label" for="calle">Calle y número exterior/interior</label>
              <input class="field" id="calle" name="calle" required placeholder="Calle y número" autocomplete="street-address">
            </div>
            <div>
              <label class="field-label" for="colonia">Colonia o localidad</label>
              <input class="field" id="colonia" name="colonia" required placeholder="Colonia">
            </div>
            <div>
              <label class="field-label" for="codigoPostal">Código postal</label>
              <input class="field" id="codigoPostal" name="codigo_postal" inputmode="numeric" maxlength="5" pattern="[0-9]{5}" placeholder="Código postal">
            </div>
            <div>
              <label class="field-label" for="municipio">Municipio o ciudad</label>
              <input class="field" id="municipio" name="municipio" required placeholder="Municipio">
            </div>
            <div>
              <label class="field-label" for="tipoLugar">Tipo de lugar</label>
              <select class="field" id="tipoLugar" name="tipo_lugar">
                <option value="Casa">Casa particular</option>
                <option value="Salon">Salón de eventos</option>
                <option value="Jardin">Jardín</option>
                <option value="Empresa">Empresa</option>
                <option value="Escuela">Escuela</option>
                <option value="Otro">Otro</option>
              </select>
            </div>
            <div>
              <label class="field-label" for="referencias">Referencias para llegar</label>
              <input class="field" id="referencias" name="referencias" placeholder="Referencias de acceso">
            </div>
          </div>
        </section>

        <section class="panel">
          <div class="section-title">
            <span class="step">5</span>
            <div>
              <h2>Cuéntanos un poquito más</h2>
              <p>Personalicemos tu experiencia.</p>
            </div>
          </div>
          <label class="field-label">Motivo del festejo</label>
          <div class="event-types" id="eventTypes">
            <button type="button" class="event-type active" data-event="Cumpleaños">🎂 Cumpleaños</button>
            <button type="button" class="event-type" data-event="Graduación">🎓 Graduación</button>
            <button type="button" class="event-type" data-event="Boda">💍 Boda</button>
            <button type="button" class="event-type" data-event="XV años">👑 XV años</button>
            <button type="button" class="event-type" data-event="Reunión familiar">🎉 Reunión</button>
            <button type="button" class="event-type" data-event="Otro">✦ Otro</button>
          </div>
          <div style="margin-top:12px">
            <label class="field-label" for="notas">¿Algo especial que debamos considerar?</label>
            <textarea class="field" id="notas" name="notas" rows="3" placeholder="Cuéntanos cómo será tu evento, si tienes preferencias o necesitas algo especial..."></textarea>
          </div>
        </section>

        <section class="panel">
          <div class="flex items-start justify-between gap-3" style="margin-bottom:12px">
            <div class="section-title" style="margin:0">
              <span class="step">6</span>
              <div>
                <h2>¿Le echamos más crema a los tacos?</h2>
                <p>Complementa tu carrito con extras para la pachanga.</p>
              </div>
            </div>
            <span class="pill pill-pink" style="font-size:7px">Opcionales</span>
          </div>
          <div class="addons">
            <label class="addon">
              <input type="checkbox" name="extras[]" value="barra_fruta" data-price="450" data-label="Barra extra de fruta">
              <span><span class="addon-title block">Barra extra de fruta</span><span class="addon-description block">Más fruta fresca preparada al momento.</span><span class="addon-price block">+ $450 MXN</span></span>
            </label>
            <label class="addon">
              <input type="checkbox" name="extras[]" value="gomitas_pulpas" data-price="350" data-label="Gomitas enchiladas y pulpas">
              <span><span class="addon-title block">Gomitas enchiladas y pulpas</span><span class="addon-description block">Dulces, pulpas y botanas con chilito.</span><span class="addon-price block">+ $350 MXN</span></span>
            </label>
            <label class="addon">
              <input type="checkbox" name="extras[]" value="hora_extra" data-price="600" data-label="Hora extra de carrito">
              <span><span class="addon-title block">Hora extra de carrito</span><span class="addon-description block">Una hora más de servicio.</span><span class="addon-price block">+ $600 MXN</span></span>
            </label>
            <label class="addon">
              <input type="checkbox" name="extras[]" value="letrero" data-price="280" data-label="Letrero personalizado">
              <span><span class="addon-title block">Letrero personalizado</span><span class="addon-description block">Un detalle decorativo para tu evento.</span><span class="addon-price block">+ $280 MXN</span></span>
            </label>
          </div>
        </section>

        <section class="panel">
          <div class="section-title">
            <span class="step"><span class="material-symbols-outlined" style="font-size:12px">person</span></span>
            <div>
              <h2>¿Con quién confirmamos?</h2>
              <p>Datos para contactar y dar seguimiento a tu solicitud.</p>
            </div>
          </div>
          <div class="field-grid">
            <div>
              <label class="field-label" for="nombre">Nombre completo</label>
              <input class="field" id="nombre" name="nombre" autocomplete="name" required placeholder="Tu nombre">
            </div>
            <div>
              <label class="field-label" for="telefono">WhatsApp</label>
              <input class="field" id="telefono" name="telefono" type="tel" autocomplete="tel" required placeholder="Tu teléfono">
            </div>
            <div style="grid-column:1/-1">
              <label class="field-label" for="correo">Correo electrónico</label>
              <input class="field" id="correo" name="correo" type="email" autocomplete="email" placeholder="tucorreo@ejemplo.com">
            </div>
          </div>
          <label class="flex items-start gap-2" style="font-size:8px;color:var(--muted);margin-top:10px;line-height:1.5">
            <input type="checkbox" name="acepta_aviso" id="aceptaAviso" required style="accent-color:var(--green);margin-top:2px">
            <span>Acepto que Fruta Madre se comunique conmigo para confirmar la disponibilidad y los detalles de mi evento.</span>
          </label>
        </section>

        <div id="formMessage" class="form-message" role="status" aria-live="polite"></div>
        <button type="submit" class="primary-button" style="margin-top:0">
          <span>Continuar con mi reserva</span><span class="material-symbols-outlined">arrow_forward</span>
        </button>
      </div>

      <aside class="summary">
        <div class="summary-top">
          <div class="summary-eyebrow">Tu resumen de fiesta</div>
          <h2>Resumen de Evento</h2>
        </div>
        <div class="summary-body">
          <div class="summary-package">
            <img alt="Paquete de fruta para eventos" src="https://lh3.googleusercontent.com/aida-public/AB6AXuApnxA5C1I9Mu8yFMqFz9-c-DrEUYBOXTN9lsBS5ObLZVdQiQeWLCc58fZbUHzKnGJHjR9QwJgUKCTuKLi39VFCrpLRYhl9lTBINa_uExU3XwiTfW0CVLBQCEHFXZlN598MaKNl-uUOnA7iJMBgxuMAk_0Agsr0Wab2U115F9qd0_mWum9KhV-18vXjBwwdtFg7wi95wGIAr34KtsPXYG6pe6oSVK18GKUhPl3SqAAoDuDJsIc97-J">
            <div>
              <h3>Paquete Pachanga</h3>
              <p>3 horas de servicio · Hasta 50 invitados</p>
              <strong>$3,450 MXN</strong>
            </div>
          </div>

          <div class="summary-lines">
            <div class="summary-line"><span>◷ Fecha del evento</span><strong id="summaryDate">Selecciona una fecha</strong></div>
            <div class="summary-line"><span>◷ Horario</span><strong id="summaryTime">13:00 a 16:00 hrs</strong></div>
            <div class="summary-line"><span>♙ Invitados</span><strong id="summaryGuests">45 personas</strong></div>
            <div class="summary-line"><span>⌖ Ubicación</span><strong id="summaryLocation">Por definir</strong></div>
          </div>

          <div class="summary-divider"></div>
          <h3 class="summary-lines-title">Desglose de cuenta</h3>
          <div class="summary-lines">
            <div class="summary-line"><span>Paquete base</span><strong>$3,450 MXN</strong></div>
            <div id="summaryExtras" class="summary-lines"></div>
            <div class="summary-line" style="color:var(--pink)"><span>Descuento de bienvenida</span><strong id="summaryDiscount">−$250 MXN</strong></div>
          </div>

          <div class="summary-total">
            <div class="flex items-center justify-between gap-2">
              <span class="total-label">Total estimado</span>
              <span class="total-amount" id="summaryTotal">$3,200</span>
            </div>
            <div class="total-note">Pesos mexicanos (MXN)</div>
          </div>
          <p class="summary-small">*Total estimado. El precio definitivo se confirma al revisar la solicitud y los servicios adicionales.</p>

          <div class="deposit-box">
            <strong>♙ <span id="depositText">Apartado estimado: $960 MXN</span></strong>
            <p>Anticipo de referencia del 30% del total estimado. El monto final se confirma con el equipo.</p>
          </div>

          <button type="button" class="primary-button" id="reviewReservation">
            <span>Continuar con mi reserva</span><span class="material-symbols-outlined">arrow_forward</span>
          </button>

          <div class="notice"><span class="material-symbols-outlined">verified</span><span><strong>Tu pachanga, bien atendida.</strong><br>Puntualidad, montaje profesional y fruta fresca del día.</span></div>
        </div>
      </aside>
    </div>
  </form>
</div>
</main>

<footer class="footer">
  <div class="color-strip">
    <span style="background:var(--green)"></span><span style="background:var(--pink)"></span>
    <span style="background:#f8edd8"></span><span style="background:var(--orange)"></span>
  </div>
  <div class="footer-grid">
    <div>
      <div class="footer-brand">FRUTA MADRE</div>
      <p class="footer-tag">TE DAMOS HASTA PARA LLEVAR.</p>
      <p style="margin-top:9px;max-width:330px">El auténtico sabor del mercado urbano mexicano en tu puerta: fruta fresca con chilito y limón, botanas preparadas, repostería artesanal, flores de temporada y carritos de antojos para tus mejores eventos.</p>
      <span class="footer-chip">🇲🇽 Hecho con mucho chamoy y amor en México</span>
    </div>
    <div>
      <h3>EXPLORAR</h3>
      <ul>
        <li><a href="index.php">Inicio</a></li>
        <li><a href="index.php?accion=catalogo">Catálogo completo</a></li>
        <li><a href="index.php?accion=eventos">Carritos de eventos</a></li>
        <li><a href="index.php?accion=mis-pedidos">Mis pedidos</a></li>
      </ul>
    </div>
    <div>
      <h3>ATENCIÓN</h3>
      <ul>
        <li>Contacto directo</li>
        <li>Preguntas frecuentes</li>
        <li>Términos y condiciones</li>
        <li>Aviso de privacidad</li>
      </ul>
    </div>
    <div>
      <h3>MERCADO SOCIAL</h3>
      <p>¡Échanos un grito o síguenos la pista!</p>
      <span class="footer-chip">Instagram</span><span class="footer-chip">TikTok</span>
      <span class="footer-chip">WhatsApp</span><span class="footer-chip">Facebook</span>
    </div>
  </div>
  <div class="footer-bottom">
    <span>© <span id="currentYear"></span> FRUTA MADRE. Todos los derechos reservados.</span>
    <span class="font-heading font-bold">PUESTO DIGITAL NO. 01</span>
  </div>
</footer>

<script>
(() => {
  'use strict';

  const $ = (selector, root = document) => root.querySelector(selector);
  const $$ = (selector, root = document) => [...root.querySelectorAll(selector)];

  const form = $('#eventForm');
  const dateInput = $('#fechaEvento');
  const timeInput = $('#horaEvento');
  const guestInput = $('#invitados');
  const message = $('#formMessage');

  const BASE_PRICE = 3450;
  const DISCOUNT = 250;
  const DEPOSIT_RATE = 0.30;
  const MAX_GUESTS = 50;
  const SERVICE_HOURS = 3;

  const months = [
    'ENERO','FEBRERO','MARZO','ABRIL','MAYO','JUNIO',
    'JULIO','AGOSTO','SEPTIEMBRE','OCTUBRE','NOVIEMBRE','DICIEMBRE'
  ];
  const weekdays = ['domingo','lunes','martes','miércoles','jueves','viernes','sábado'];

  const money = amount => '$' + Math.round(amount).toLocaleString('es-MX');

  const today = new Date();
  today.setHours(0,0,0,0);

  let currentYear = today.getFullYear();
  let currentMonth = today.getMonth();
  let selectedDate = null;
  let selectedGuests = 45;

  function keyDate(date) {
    return `${date.getFullYear()}-${String(date.getMonth()+1).padStart(2,'0')}-${String(date.getDate()).padStart(2,'0')}`;
  }

  function formatDate(date) {
    if (!date) return 'Selecciona una fecha';
    return `${weekdays[date.getDay()].slice(0,3)}, ${date.getDate()} ${months[date.getMonth()].toLowerCase()} ${date.getFullYear()}`;
  }

  function formatTimeRange(start) {
    const [hours, minutes] = start.split(':').map(Number);
    const end = new Date(2000,0,1,hours + SERVICE_HOURS,minutes);
    const endString = `${String(end.getHours()).padStart(2,'0')}:${String(end.getMinutes()).padStart(2,'0')}`;
    return `${start} a ${endString} hrs`;
  }

  function renderCalendar() {
    const grid = $('#calendarDays');
    grid.innerHTML = '';
    $('#monthTitle').textContent = `${months[currentMonth]} ${currentYear}`;

    const first = new Date(currentYear,currentMonth,1);
    const daysInMonth = new Date(currentYear,currentMonth+1,0).getDate();
    const todayKey = keyDate(today);

    for (let i=0; i<first.getDay(); i++) {
      const blank = document.createElement('div');
      blank.className = 'calendar-empty';
      blank.setAttribute('aria-hidden','true');
      grid.appendChild(blank);
    }

    for (let day=1; day<=daysInMonth; day++) {
      const date = new Date(currentYear,currentMonth,day);
      const key = keyDate(date);
      const isPast = date < today;
      const isToday = key === todayKey;
      const isSelected = selectedDate && key === keyDate(selectedDate);

      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'calendar-day';
      button.textContent = day;
      button.setAttribute('aria-label',`${day} de ${months[currentMonth].toLowerCase()} de ${currentYear}`);
      button.setAttribute('aria-pressed',String(Boolean(isSelected)));

      if (isPast) {
        button.disabled = true;
        button.title = 'Fecha pasada';
      } else {
        const dot = document.createElement('span');
        dot.className = 'day-dot';
        dot.setAttribute('aria-hidden','true');
        button.appendChild(dot);
        button.addEventListener('click',() => {
          selectedDate = new Date(currentYear,currentMonth,day);
          dateInput.value = keyDate(selectedDate);
          renderCalendar();
          updateSummary();
        });
      }

      if (isToday) button.classList.add('today');
      if (isSelected) button.classList.add('selected');

      grid.appendChild(button);
    }

    while (grid.children.length % 7 !== 0) {
      const blank = document.createElement('div');
      blank.className = 'calendar-empty';
      blank.setAttribute('aria-hidden','true');
      grid.appendChild(blank);
    }

    const currentStart = new Date(today.getFullYear(),today.getMonth(),1);
    const shownStart = new Date(currentYear,currentMonth,1);
    $('#prevMonth').disabled = shownStart <= currentStart;
  }

  $('#prevMonth').addEventListener('click',() => {
    const candidate = new Date(currentYear,currentMonth-1,1);
    if (candidate < new Date(today.getFullYear(),today.getMonth(),1)) return;
    currentYear = candidate.getFullYear();
    currentMonth = candidate.getMonth();
    renderCalendar();
  });

  $('#nextMonth').addEventListener('click',() => {
    const candidate = new Date(currentYear,currentMonth+1,1);
    currentYear = candidate.getFullYear();
    currentMonth = candidate.getMonth();
    renderCalendar();
  });

  $$('#timeOptions [data-time]').forEach(button => {
    button.addEventListener('click',() => {
      $$('#timeOptions [data-time]').forEach(item => {
        item.classList.remove('active');
        item.setAttribute('aria-pressed','false');
        item.querySelector('.time-status').textContent = 'Horario de inicio';
      });

      button.classList.add('active');
      button.setAttribute('aria-pressed','true');
      button.querySelector('.time-status').textContent = 'Horario seleccionado';
      timeInput.value = button.dataset.time;
      updateSummary();
    });
  });

  $('#decreaseGuests').addEventListener('click',() => {
    selectedGuests = Math.max(1,selectedGuests-1);
    updateSummary();
  });

  $('#increaseGuests').addEventListener('click',() => {
    selectedGuests = Math.min(500,selectedGuests+1);
    updateSummary();
  });

  $$('#eventTypes [data-event]').forEach(button => {
    button.addEventListener('click',() => {
      $$('#eventTypes [data-event]').forEach(item => item.classList.remove('active'));
      button.classList.add('active');
      $('#motivoEvento').value = button.dataset.event;
    });
  });

  function getExtras() {
    return $$('input[name="extras[]"]:checked').map(input => ({
      label: input.dataset.label,
      price: Number(input.dataset.price)
    }));
  }

  function updateSummary() {
    dateInput.value = selectedDate ? keyDate(selectedDate) : '';
    $('#summaryDate').textContent = formatDate(selectedDate);
    $('#summaryTime').textContent = formatTimeRange(timeInput.value);
    $('#selectedTimeText').textContent = formatTimeRange(timeInput.value);

    $('#guestCount').textContent = selectedGuests;
    guestInput.value = selectedGuests;
    $('#summaryGuests').textContent = `${selectedGuests} ${selectedGuests === 1 ? 'persona' : 'personas'}`;

    const address = [
      $('#calle').value.trim(),
      $('#colonia').value.trim(),
      $('#municipio').value.trim()
    ].filter(Boolean);

    $('#summaryLocation').textContent = address.length ? address.join(', ') : 'Por definir';

    const extras = getExtras();
    const extrasNode = $('#summaryExtras');
    extrasNode.innerHTML = '';

    if (!extras.length) {
      const line = document.createElement('div');
      line.className = 'summary-line';
      line.innerHTML = '<span>Extras adicionales</span><strong>—</strong>';
      extrasNode.appendChild(line);
    } else {
      extras.forEach(extra => {
        const line = document.createElement('div');
        line.className = 'summary-line';
        const label = document.createElement('span');
        label.textContent = extra.label;
        const price = document.createElement('strong');
        price.textContent = money(extra.price);
        line.append(label,price);
        extrasNode.appendChild(line);
      });
    }

    const total = Math.max(0,BASE_PRICE + extras.reduce((sum,item) => sum+item.price,0)-DISCOUNT);
    $('#summaryTotal').textContent = money(total);
    $('#depositText').textContent = `Apartado estimado: ${money(total*DEPOSIT_RATE)} MXN`;

    if (selectedGuests > MAX_GUESTS) {
      $('#guestStatus').textContent = 'Requiere una cotización especial';
      $('#guestStatus').style.color = 'var(--orange)';
      $('#guestHelp').textContent = `El paquete cubre hasta ${MAX_GUESTS} personas.`;
    } else {
      $('#guestStatus').textContent = '● ¡Vas sobrado!';
      $('#guestStatus').style.color = 'var(--green)';
      $('#guestHelp').textContent = `Tu Paquete Pachanga cubre hasta ${MAX_GUESTS} personas.`;
    }
  }

  ['calle','colonia','municipio'].forEach(id => {
    $(`#${id}`).addEventListener('input',updateSummary);
  });

  $('#clearAddress').addEventListener('click',() => {
    ['calle','colonia','codigoPostal','municipio','referencias'].forEach(id => {
      $(`#${id}`).value = '';
    });
    updateSummary();
  });

  $('#themeButton').addEventListener('click',() => {
    const dark = document.body.dataset.theme !== 'dark';
    document.body.dataset.theme = dark ? 'dark' : 'light';
    document.body.style.background = dark ? '#25231e' : 'var(--cream)';
    document.body.style.color = dark ? '#fff8ed' : 'var(--ink)';
    $$('.panel,.summary').forEach(el => {
      el.style.background = dark ? '#302d26' : 'var(--card)';
      el.style.borderColor = dark ? '#4a453a' : '#f0e5d0';
    });
    $('#themeButton .material-symbols-outlined').textContent = dark ? 'dark_mode' : 'light_mode';
  });

  $('#reviewReservation').addEventListener('click',() => {
    $('#nombre').scrollIntoView({behavior:'smooth',block:'center'});
    $('#nombre').focus({preventScroll:true});
  });

  function showError(text) {
    message.textContent = text;
    message.className = 'form-message error';
    message.scrollIntoView({behavior:'smooth',block:'center'});
  }

  form.addEventListener('submit',event => {
    if (!selectedDate) {
      event.preventDefault();
      showError('Selecciona la fecha de tu evento antes de continuar.');
      return;
    }

    if (selectedGuests > MAX_GUESTS) {
      event.preventDefault();
      showError(`El paquete cubre hasta ${MAX_GUESTS} personas. Solicita una cotización para un grupo más grande.`);
      return;
    }

    if (!$('#calle').value.trim() || !$('#colonia').value.trim() || !$('#municipio').value.trim()) {
      event.preventDefault();
      showError('Completa la calle, colonia y municipio para indicar dónde se realizará el evento.');
      return;
    }

    if (!form.checkValidity()) {
      event.preventDefault();
      form.reportValidity();
      return;
    }

    /*
      La solicitud se envía al controlador existente mediante POST.
      El controlador debe validar disponibilidad, sesión y datos,
      y guardar la reservación en PostgreSQL.
    */
  });

  $('#currentYear').textContent = new Date().getFullYear();

  renderCalendar();
  updateSummary();
})();
</script>
</body>
</html>