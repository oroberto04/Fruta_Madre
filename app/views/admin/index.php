<!DOCTYPE html>

<html lang="es"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<meta content="web_standard" name="shell-type"/>
<title>Mi Cuenta - FRUTA MADRE</title>
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&amp;family=Syne:wght@400..800&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script id="tailwind-config">
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            "surface-container-lowest": "#ffffff",
            "surface-container-low": "#fdf2de",
            "surface-container": "#f8edd8",
            "surface-container-high": "#f2e7d2",
            "surface-container-highest": "#ece1cd",
            "surface": "#fff8f0",
            "surface-dim": "#e3d9c5",
            "surface-bright": "#fff8f0",
            "background": "#fff8f0",
            "on-surface": "#211d1a",
            "on-surface-variant": "#41493f",
            "on-background": "#211d1a",
            "primary": "#477a45",
            "primary-container": "#477a45",
            "on-primary": "#ffffff",
            "on-primary-container": "#d0ffc8",
            "secondary": "#d94f70",
            "secondary-container": "#ffd9de",
            "on-secondary": "#ffffff",
            "on-secondary-container": "#6b0027",
            "tertiary": "#e97832",
            "tertiary-container": "#e97832",
            "on-tertiary": "#ffffff",
            "on-tertiary-container": "#fff0e9",
            "outline": "#72796e",
            "outline-variant": "#c1c9bc",
            "error": "#ba1a1a",
            "error-container": "#ffdad6",
            "on-error": "#ffffff",
            "on-error-container": "#93000a",
            "inverse-surface": "#302923",
            "inverse-on-surface": "#fff4df",
            "amarillo-maiz": "#e8b93f"
          },
          borderRadius: {
            "DEFAULT": "0.25rem",
            "lg": "0.5rem",
            "xl": "0.75rem",
            "full": "9999px"
          },
          spacing: {
            "gutter-desktop": "2rem",
            "space-sm": "0.5rem",
            "margin-desktop": "3rem",
            "space-xs": "0.25rem",
            "gutter": "1.25rem",
            "space-xl": "2.5rem",
            "space-md": "1rem",
            "margin": "1.25rem",
            "space-lg": "1.5rem"
          },
          fontFamily: {
            "display-hero": ["Syne", "sans-serif"],
            "headline-lg": ["Syne", "sans-serif"],
            "headline-md": ["Syne", "sans-serif"],
            "headline-sm": ["Syne", "sans-serif"],
            "label-rotulo": ["Syne", "sans-serif"],
            "body-lg": ["DM Sans", "sans-serif"],
            "body-md": ["DM Sans", "sans-serif"],
            "body-sm": ["DM Sans", "sans-serif"],
            "label-md": ["DM Sans", "sans-serif"],
            "label-sm": ["DM Sans", "sans-serif"]
          }
        }
      }
    };
  </script>
<style>
    @layer base {
      html, body { margin: 0; padding: 0; }
      body { overscroll-behavior: none; }
    }
    ::-webkit-scrollbar { display: none; }
  </style>
</head>
<body class="bg-background font-body-md text-on-surface antialiased">
<!-- TOPBAR ROTULERA DE ANUNCIOS -->
<aside aria-label="Avisos del puesto" class="w-full bg-primary text-on-primary py-1.5 px-4 text-center font-label-rotulo text-xs md:text-sm tracking-wider uppercase flex items-center justify-center gap-2 shadow-sm border-b-2 border-on-surface">
<span class="material-symbols-outlined text-[16px] text-amarillo-maiz">bolt</span>
<span>ENTREGAS EXPRÉS EN MENOS DE 45 MIN • FRUTA FRESCA DEL DÍA • CHAMOY ARTESANAL</span>
<span class="material-symbols-outlined text-[16px] text-amarillo-maiz hidden sm:inline">star</span>
</aside>
<!-- HEADER INSTITUCIONAL -->
<header class="sticky top-0 z-50 bg-surface/95 backdrop-blur-md border-b-2 border-on-surface">
<div class="h-20 w-full px-gutter lg:px-margin-desktop flex items-center justify-between gap-space-md">
<!-- Logotipo Oficial y Lema -->
<a class="flex items-center gap-space-md shrink-0 focus:outline-none" href="#">
<img alt="FRUTA MADRE Logo" class="h-9 w-auto object-contain" src="https://lh3.googleusercontent.com/aida/AEtjO1W0q5rsMHajuhP3xliGM4ENIiIXYTajmRgvYwHU3mD6RbhEJ5J2-fIl5UaybVd_COqPwuK5CKGypw3IWxlfFTLUUC8Ga9K1e3W-wQqQIKre8hpcnDPDDlQxaE0X5au8QORDV9Ktw-44n2sHR0f0miZl00x_QcRrpGzizUh0ImPQSoteXpbyDjbmV_5NQghiScqmtUmF2BDSQpH7-q0y8HS-58EiwwtNmOlsdLqTePCU7JlvyHVbFyHlQw"/>
<div class="flex flex-col">
<span class="font-display-hero text-headline-sm uppercase text-on-surface tracking-wider leading-none font-bold">FRUTA MADRE</span>
<span class="font-label-rotulo text-[11px] text-secondary font-extrabold leading-tight tracking-widest uppercase">Te damos hasta para llevar</span>
</div>
</a>
<!-- Menú de Navegación -->
<nav class="hidden xl:flex items-center gap-space-sm bg-surface-container-low p-space-xs rounded-xl border-2 border-on-surface shadow-[2px_2px_0px_#211d1a]">
<a class="text-on-surface hover:bg-surface-container px-space-md py-1.5 font-label-rotulo text-sm uppercase transition-colors rounded-lg" href="#">Inicio</a>
<a class="text-on-surface hover:bg-surface-container px-space-md py-1.5 font-label-rotulo text-sm uppercase transition-colors rounded-lg" href="#">Catálogo</a>
<a class="text-on-surface hover:bg-surface-container px-space-md py-1.5 font-label-rotulo text-sm uppercase transition-colors rounded-lg" href="#">Eventos</a>
<a class="text-on-surface hover:bg-surface-container px-space-md py-1.5 font-label-rotulo text-sm uppercase transition-colors rounded-lg" href="#">Mis pedidos</a>
</nav>
<!-- Barra de Búsqueda y Acciones Rápidas -->
<div class="flex items-center gap-space-sm shrink-0">
<div class="hidden md:flex items-center bg-surface-container-lowest rounded-lg border-2 border-on-surface px-space-md py-1.5 shadow-[2px_2px_0px_#211d1a]">
<span class="material-symbols-outlined text-on-surface-variant text-[20px] mr-space-xs">search</span>
<input class="bg-transparent text-on-surface placeholder:text-on-surface-variant/70 font-body-sm text-sm focus:outline-none w-36 lg:w-48" placeholder="Buscar antojito..." type="text"/>
</div>
<!-- Carrito con indicador -->
<button aria-label="Carrito de compras" class="relative p-2 rounded-lg bg-surface-container-low hover:bg-surface-container text-on-surface border-2 border-on-surface shadow-[2px_2px_0px_#211d1a] transition-all" type="button">
<span class="material-symbols-outlined text-[22px]">shopping_bag</span>
<span class="absolute -top-1.5 -right-1.5 bg-secondary text-on-secondary font-label-rotulo text-[11px] w-5 h-5 rounded-full flex items-center justify-center border border-on-surface">3</span>
</button>
<!-- Switch Claro / Oscuro -->
<button aria-label="Cambiar tema de color" class="p-2 rounded-lg bg-surface-container-low hover:bg-surface-container text-on-surface border-2 border-on-surface shadow-[2px_2px_0px_#211d1a] transition-all" id="toggle-modo" type="button">
<span class="material-symbols-outlined text-[20px]">light_mode</span>
</button>
<!-- Avatar / Perfil Seleccionado Activo -->
<a aria-label="Mi Cuenta (Perfil Activo)" class="flex items-center gap-2 p-1 pl-1 pr-3 rounded-full bg-primary text-on-primary border-2 border-on-surface shadow-[2px_2px_0px_#211d1a] ring-2 ring-primary ring-offset-2 ring-offset-surface" href="#seccion-perfil">
<div class="w-8 h-8 rounded-full bg-amarillo-maiz text-on-surface font-label-rotulo font-black flex items-center justify-center border border-on-surface">
            CO
          </div>
<span class="font-label-rotulo text-xs uppercase hidden sm:inline">Carlos</span>
</a>
</div>
</div>
<!-- Franja Mexicana Multicolor -->
<div class="h-2 w-full flex border-t-2 border-on-surface">
<div class="flex-1 bg-primary"></div>
<div class="flex-1 bg-secondary"></div>
<div class="flex-1 bg-amarillo-maiz"></div>
<div class="flex-1 bg-tertiary"></div>
</div>
</header>
<main class="w-full bg-background min-h-screen pb-space-xl">
<div class="flex flex-col w-full">
<!-- Breadcrumbs & Banner Callejero -->
<section class="w-full bg-surface-container border-b-2 border-on-surface px-gutter lg:px-margin-desktop py-space-xs flex flex-wrap items-center justify-between gap-space-sm">
<nav aria-label="Miga de pan" class="flex items-center gap-space-sm flex-wrap font-label-sm text-label-sm">
<span class="flex items-center gap-1 text-primary font-extrabold uppercase">
<span class="material-symbols-outlined text-[16px]">storefront</span>
            Puesto Digital No. 01
          </span>
<span class="text-on-surface-variant/50">/</span>
<a class="text-on-surface hover:text-primary font-bold transition-colors" href="#">Inicio</a>
<span class="text-on-surface-variant/50">/</span>
<span class="text-secondary font-bold uppercase">Mi cuenta</span>
</nav>
<div class="inline-flex items-center gap-1.5 px-space-sm py-1 rounded-full bg-secondary text-on-secondary font-label-rotulo text-xs uppercase tracking-wider border border-on-surface shadow-[2px_2px_0px_#211d1a]">
<span class="material-symbols-outlined text-[14px]">account_circle</span>
<span>Ajustes &amp; Fidelidad</span>
</div>
</section>
<!-- Hero Header de Cuenta -->
<section class="w-full px-gutter lg:px-margin-desktop pt-space-lg pb-space-md bg-surface">
<div class="w-full flex flex-col md:flex-row md:items-center justify-between gap-space-md border-b-2 border-dashed border-on-surface/20 pb-space-lg">
<div class="flex flex-col gap-space-xs max-w-2xl">
<div class="inline-flex items-center gap-space-xs bg-amarillo-maiz text-on-surface border border-on-surface px- space-sm py-0.5 rounded-full w-fit font-label-rotulo text-xs uppercase tracking-wide shadow-[1px_1px_0px_#211d1a]">
<span class="material-symbols-outlined text-[15px]">verified</span>
              Perfil Oficial Fruta Madre
            </div>
<h1 class="font-display-hero text-headline-lg lg:text-display-hero uppercase text-on-surface tracking-tight leading-none mt-space-xs">
              MI CUENTA
            </h1>
<p class="font-body-lg text-body-lg text-on-surface-variant">
              Todo lo tuyo, en un solo lugar. ¡Qué gusto verte por aquí! Tus pedidos, tus eventos y tus antojos bien afinados.
            </p>
</div>
<!-- Sello Decorativo Rotulero -->
<div class="flex flex-col items-center justify-center p-space-md rounded-xl bg-tertiary text-on-tertiary border-2 border-on-surface shadow-[4px_4px_0px_#211d1a] md:rotate-2 shrink-0">
<div class="flex items-center gap-1 font-label-rotulo text-xs uppercase tracking-widest text-amarillo-maiz font-black">
<span class="material-symbols-outlined text-[16px]">stars</span> 100% PURO SABOR
            </div>
<span class="font-display-hero text-base uppercase text-center font-extrabold mt-0.5">Cliente Consentido de Barrio</span>
<span class="mt-1 px-2 py-0.5 rounded-full bg-surface text-on-surface font-label-rotulo text-xs uppercase font-black border border-on-surface">
              Nivel Chamoy Oro 🌶️
            </span>
</div>
</div>
</section>
<!-- Contenedor Principal: 2 Columnas -->
<section class="w-full px-gutter lg:px-margin-desktop py-space-md">
<div class="w-full grid grid-cols-1 lg:grid-cols-12 gap-gutter-desktop items-start">
<!-- COLUMNA IZQUIERDA: Menú de Navegación Lateral (lg:col-span-4 / 320px) -->
<aside class="lg:col-span-4 w-full flex flex-col gap-space-md lg:sticky lg:top-28">
<nav aria-label="Navegación de cuenta" class="w-full bg-surface-container-lowest rounded-xl p-space-sm border-2 border-on-surface shadow-[3px_3px_0px_#211d1a] flex flex-col gap-1.5">
<!-- Item 1: Activo Mi Perfil -->
<a class="flex items-center justify-between p- space-sm px-3 py-2.5 rounded-lg bg-primary text-on-primary font-label-rotulo text-sm uppercase tracking-wider border-2 border-on-surface shadow-[2px_2px_0px_#211d1a] transition-all" href="#seccion-perfil">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-[20px]">person</span>
<span>Mi perfil</span>
</div>
<span class="material-symbols-outlined text-[18px]">check_circle</span>
</a>
<!-- Item 2: Mis Direcciones -->
<a class="flex items-center justify-between p-space-sm px-3 py-2.5 rounded-lg text-on-surface hover:bg-surface-container font-label-rotulo text-sm uppercase tracking-wide border border-transparent hover:border-on-surface transition-all" href="#seccion-direcciones">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-[20px] text-tertiary">place</span>
<span>Mis direcciones</span>
</div>
<span class="bg-surface-container-high text-on-surface px-2 py-0.5 rounded-full text-xs font-bold border border-on-surface">2 guardadas</span>
</a>
<!-- Item 3: Mis Pedidos -->
<a class="flex items-center justify-between p-space-sm px-3 py-2.5 rounded-lg text-on-surface hover:bg-surface-container font-label-rotulo text-sm uppercase tracking-wide border border-transparent hover:border-on-surface transition-all" href="#seccion-pedidos">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-[20px] text-primary">local_shipping</span>
<span>Mis pedidos</span>
</div>
<span class="bg-amarillo-maiz text-on-surface px-2 py-0.5 rounded-full text-xs font-extrabold border border-on-surface">1 en camino</span>
</a>
<!-- Item 4: Mis Reservas -->
<a class="flex items-center justify-between p-space-sm px-3 py-2.5 rounded-lg text-on-surface hover:bg-surface-container font-label-rotulo text-sm uppercase tracking-wide border border-transparent hover:border-on-surface transition-all" href="#seccion-reservas">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-[20px] text-secondary">celebration</span>
<span>Mis reservas</span>
</div>
<span class="bg-secondary text-on-secondary px-2 py-0.5 rounded-full text-xs font-bold border border-on-surface">1 evento</span>
</a>
<!-- Item 5: Notificaciones -->
<a class="flex items-center justify-between p-space-sm px-3 py-2.5 rounded-lg text-on-surface hover:bg-surface-container font-label-rotulo text-sm uppercase tracking-wide border border-transparent hover:border-on-surface transition-all" href="#seccion-notificaciones">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-[20px] text-tertiary">notifications</span>
<span>Notificaciones</span>
</div>
<span class="bg-tertiary text-on-tertiary px-2 py-0.5 rounded-full text-xs font-bold border border-on-surface">2 nuevas</span>
</a>
<!-- Item 6: Preferencias -->
<a class="flex items-center justify-between p-space-sm px-3 py-2.5 rounded-lg text-on-surface hover:bg-surface-container font-label-rotulo text-sm uppercase tracking-wide border border-transparent hover:border-on-surface transition-all" href="#seccion-preferencias">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-[20px]">palette</span>
<span>Preferencias &amp; Modo</span>
</div>
<span class="material-symbols-outlined text-[18px] text-on-surface-variant">arrow_forward</span>
</a>
<!-- Item 7: Seguridad -->
<a class="flex items-center justify-between p-space-sm px-3 py-2.5 rounded-lg text-on-surface hover:bg-surface-container font-label-rotulo text-sm uppercase tracking-wide border border-transparent hover:border-on-surface transition-all" href="#seccion-seguridad">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-[20px]">shield</span>
<span>Seguridad &amp; Accesos</span>
</div>
<span class="material-symbols-outlined text-[18px] text-on-surface-variant">arrow_forward</span>
</a>
<!-- Separador con estilo rotulero -->
<div class="border-t-2 border-dashed border-on-surface/20 my-1"></div>
<!-- Cerrar Sesión -->
<button class="flex items-center justify-between p-space-sm px-3 py-2 rounded-lg bg-error-container/40 text-error hover:bg-error-container border border-error/30 font-label-rotulo text-sm uppercase tracking-wide transition-all text-left" id="btn-cerrar-sesion" type="button">
<div class="flex items-center gap-2 font-bold">
<span class="material-symbols-outlined text-[20px]">logout</span>
<span>Cerrar sesión</span>
</div>
<span class="text-xs font-body-md opacity-80">Salir</span>
</button>
</nav>
<!-- Tarjeta de Soporte / Ayuda Rápida -->
<div class="w-full p-space-md rounded-xl bg-surface-container-low border-2 border-on-surface shadow-[3px_3px_0px_#211d1a] flex flex-col gap-space-xs">
<div class="flex items-center gap-2 text-primary font-label-rotulo text-xs uppercase font-extrabold">
<span class="material-symbols-outlined text-[20px]">support_agent</span>
<span>ATENCIÓN DEL BARRIO</span>
</div>
<h2 class="font-display-hero text-lg uppercase font-bold text-on-surface">¿DUDAS CON TU CUENTA?</h2>
<p class="font-body-sm text-sm text-on-surface-variant">
                Échanos un grito al WhatsApp del puesto digital. Te contestamos en friega.
              </p>
<a class="mt-2 inline-flex items-center justify-center gap-2 py-2.5 px-4 bg-primary text-on-primary rounded-lg font-label-rotulo text-xs uppercase tracking-wider border-2 border-on-surface shadow-[2px_2px_0px_#211d1a] hover:translate-x-0.5 hover:translate-y-0.5 transition-all" href="https://wa.me/" rel="noopener noreferrer" target="_blank">
<span class="material-symbols-outlined text-[18px]">chat</span>
<span>Escribir al marchante</span>
</a>
</div>
</aside>
<!-- COLUMNA DERECHA: Contenido Completo Unificado (lg:col-span-8) -->
<div class="lg:col-span-8 w-full flex flex-col gap-space-xl">
<!-- A) SECCIÓN PERFIL & EDICIÓN -->
<section class="w-full bg-surface-container-lowest p-space-md lg:p-space-lg rounded-xl border-2 border-on-surface shadow-[3px_3px_0px_#211d1a] flex flex-col gap-space-md" id="seccion-perfil">
<div class="flex flex-wrap items-center justify-between gap-space-sm border-b-2 border-dashed border-on-surface/20 pb-space-sm">
<div class="flex items-center gap-space-sm">
<span class="material-symbols-outlined text-primary text-[28px]">badge</span>
<div>
<h2 class="font-display-hero text-headline-sm uppercase text-on-surface">Mis Datos Personales</h2>
<p class="font-body-sm text-sm text-on-surface-variant">Tu credencial botanera oficial de Fruta Madre</p>
</div>
</div>
<button class="px-space-md py-1.5 rounded-lg bg-surface-container-low text-on-surface font-label-rotulo text-xs uppercase tracking-wider border-2 border-on-surface shadow-[2px_2px_0px_#211d1a] hover:bg-surface-container flex items-center gap-1.5 transition-all" id="btn-editar-perfil" type="button">
<span class="material-symbols-outlined text-[16px]">edit</span>
<span id="texto-boton-editar">Editar datos</span>
</button>
</div>
<!-- Tarjeta de Identificación Rápida del Usuario -->
<div class="flex flex-col sm:flex-row items-start sm:items-center gap-space-md bg-surface-container-low p-space-md rounded-xl border-2 border-on-surface shadow-[2px_2px_0px_#211d1a]">
<div class="relative shrink-0">
<img alt="Foto de perfil de Carlos Orozco" class="w-20 h-20 rounded-full object-cover border-2 border-on-surface shadow-sm" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCEMoKwV2g1zjeEozLAzzeHlHKw5Ptwns-wMzdo67bJ0HSWWQOsBpLcwlxSvyo6FZVWk0bkw3I7cIAWQJu9B24T1fQ0nOHNaZdJoGHSUYSBMd0BSXPIoZMGv4iF8WP3egsO1XB4zQpbwdXR98p42iIb09gsX48YCXBi-wfz9bmJ1w0lxHiYxZKs1d5ZDy2cBr6wFPszIsKlqDgi2UVmBP0xXuRQz0agttEpkbJopCvg0nJhI-63kp-M"/>
<span class="absolute -bottom-1 -right-1 bg-amarillo-maiz text-on-surface p-1 rounded-full border border-on-surface">
<span class="material-symbols-outlined text-[14px] block">verified</span>
</span>
</div>
<div class="flex flex-col flex-1">
<div class="flex items-center gap-2 flex-wrap">
<h3 class="font-display-hero text-xl uppercase font-bold text-on-surface" id="resumen-nombre"><?= htmlspecialchars($usuario['nombre_completo'] ?? '', ENT_QUOTES, 'UTF-8') ?></h3>
<span class="bg-secondary text-on-secondary px-2 py-0.5 rounded-full font-label-rotulo text-[11px] uppercase font-bold border border-on-surface">Marchante Frecuente</span>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-1 mt-2 text-sm text-on-surface-variant font-body-sm">
<span class="flex items-center gap-1.5">
<span class="material-symbols-outlined text-[16px] text-primary">mail</span>
<strong class="font-medium text-on-surface" id="resumen-correo"><?= htmlspecialchars($usuario['correo'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
</span>
<span class="flex items-center gap-1.5">
<span class="material-symbols-outlined text-[16px] text-tertiary">call</span>
<strong class="font-medium text-on-surface" id="resumen-tel"><?= htmlspecialchars($usuario['telefono'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
</span>
</div>
</div>
</div>
<!-- FORMULARIO DE EDICIÓN EN VIVO INTEGRADO -->

<form class="flex flex-col gap-space-md pt-2" id="form-perfil" action="index.php?accion=actualizar-perfil" method="POST">
<div class="grid grid-cols-1 md:grid-cols-3 gap-space-md">
<div class="flex flex-col gap-1">
<label class="font-label-rotulo text-xs uppercase font-extrabold text-on-surface" for="input-nombre">Nombre Completo</label>
<input class="bg-surface-container-lowest px-3 py-2 rounded-lg border-2 border-on-surface text-on-surface font-body-md text-sm focus:outline-none focus:ring-2 focus:ring-primary shadow-[2px_2px_0px_#211d1a]" id="input-nombre" name="nombre_completo" type="text" value="<?= htmlspecialchars($usuario['nombre_completo'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required/>
</div>
<div class="flex flex-col gap-1">
<label class="font-label-rotulo text-xs uppercase font-extrabold text-on-surface" for="input-correo">Correo Electrónico</label>
<input class="bg-surface-container-lowest px-3 py-2 rounded-lg border-2 border-on-surface text-on-surface font-body-md text-sm focus:outline-none focus:ring-2 focus:ring-primary shadow-[2px_2px_0px_#211d1a]" id="input-correo" name="correo" type="email" value="<?= htmlspecialchars($usuario['correo'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required/>
</div>
<div class="flex flex-col gap-1">
<label class="font-label-rotulo text-xs uppercase font-extrabold text-on-surface" for="input-telefono">Teléfono WhatsApp</label>
<input class="bg-surface-container-lowest px-3 py-2 rounded-lg border-2 border-on-surface text-on-surface font-body-md text-sm focus:outline-none focus:ring-2 focus:ring-primary shadow-[2px_2px_0px_#211d1a]" id="input-telefono" name="telefono" type="tel" value="<?= htmlspecialchars($usuario['telefono'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
</div>
</div>
<div class="flex items-center justify-end gap-space-sm pt-2">
<button class="px-space-md py-2 rounded-lg bg-surface-container text-on-surface font-label-rotulo text-xs uppercase tracking-wider border-2 border-on-surface hover:bg-surface-container-high transition-colors" id="btn-cancelar-perfil" type="button">
                    Cancelar
                  </button>
<button class="px-space-md py-2 rounded-lg bg-primary text-on-primary font-label-rotulo text-xs uppercase tracking-wider border-2 border-on-surface shadow-[2px_2px_0px_#211d1a] hover:translate-x-0.5 hover:translate-y-0.5 transition-all flex items-center gap-1.5 font-bold" id="btn-guardar-perfil" type="submit">
<span class="material-symbols-outlined text-[18px]">save</span>
                    Guardar cambios
                  </button>
</div>
</form>
<?php
$mensajePerfil = $_SESSION['mensaje_perfil'] ?? null;
unset($_SESSION['mensaje_perfil']);
?>

<?php if ($mensajePerfil): ?>
<div class="p-space-sm rounded-lg <?= $mensajePerfil['tipo'] === 'success' ? 'bg-primary text-on-primary' : 'bg-secondary text-on-secondary' ?> border-2 border-on-surface shadow-[2px_2px_0px_#211d1a] flex items-center justify-between font-label-rotulo text-xs uppercase tracking-wide" id="alerta-perfil">

    <span class="flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px]">
            <?= $mensajePerfil['tipo'] === 'success' ? 'check_circle' : 'error' ?>
        </span>

        <?= htmlspecialchars($mensajePerfil['texto'], ENT_QUOTES, 'UTF-8') ?>
    </span>

    <button class="text-on-primary" onclick="this.parentElement.remove()" type="button">
        <span class="material-symbols-outlined text-[16px]">close</span>
    </button>

</div>
<?php endif; ?>
<span class="flex items-center gap-2">
<span class="material-symbols-outlined text-[18px]">check_circle</span>
                  ¡Tus datos quedaron actualizados y bien afinados!
                </span>
<button class="text-on-primary" onclick="this.parentElement.classList.add('hidden')" type="button">
<span class="material-symbols-outlined text-[16px]">close</span>
</button>
</div>
</section>

<!-- B) SECCIÓN MIS DIRECCIONES -->

<section class="w-full bg-surface-container-lowest p-space-md lg:p-space-lg rounded-xl border-2 border-on-surface shadow-[3px_3px_0px_#211d1a] flex flex-col gap-space-md" id="seccion-direcciones">
<div class="flex flex-wrap items-center justify-between gap-space-sm border-b-2 border-dashed border-on-surface/20 pb-space-sm">
<div class="flex items-center gap-space-sm">
<span class="material-symbols-outlined text-primary text-[28px]">home_pin</span>
<div>
<h2 class="font-display-hero text-headline-sm uppercase text-on-surface">Mis Direcciones</h2>
<p class="font-body-sm text-sm text-on-surface-variant">Donde quieres que te caiga el pedido calientito o fresco</p>
</div>
</div>
<button class="px-space-md py-1.5 rounded-lg bg-primary text-on-primary font-label-rotulo text-xs uppercase tracking-wider border-2 border-on-surface shadow-[2px_2px_0px_#211d1a] hover:translate-x-0.5 hover:translate-y-0.5 flex items-center gap-1 transition-all" type="button">
<span class="material-symbols-outlined text-[18px]">add</span>
<span>+ Nueva dirección</span>
</button>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-space-md">
<!-- Tarjeta 1: Principal Roma Norte -->
<div class="p-space-md rounded-xl bg-surface-container-low border-2 border-on-surface shadow-[2px_2px_0px_#211d1a] flex flex-col justify-between gap-space-sm">
<div class="flex flex-col gap-1.5">
<div class="flex items-center justify-between">
<div class="flex items-center gap-1.5 font-display-hero text-base uppercase font-bold text-on-surface">
<span class="material-symbols-outlined text-primary text-[20px]">roofing</span>
                        Casa - Roma Norte
                      </div>
<span class="px-2 py-0.5 rounded-full bg-primary text-on-primary font-label-rotulo text-[10px] uppercase font-bold border border-on-surface">
                        PREDETERMINADA
                      </span>
</div>
<p class="font-body-md text-sm text-on-surface font-semibold leading-snug">
                      Colima 142, Int. 302, Colonia Roma Norte, Cuauhtémoc, CDMX, C.P. 06700
                    </p>
<div class="flex items-center gap-1 text-on-surface-variant font-body-sm text-xs bg-surface-container p-1.5 rounded border border-on-surface/20">
<span class="material-symbols-outlined text-[15px] text-tertiary">info</span>
<span>Portón verde frente al café de especialidad. Timbre 302.</span>
</div>
</div>
<div class="flex items-center justify-end gap-3 pt-2 border-t border-on-surface/15">
<button class="font-label-rotulo text-xs uppercase font-bold text-primary hover:underline flex items-center gap-1" type="button">
<span class="material-symbols-outlined text-[16px]">edit</span> Editar
                    </button>
<span class="text-on-surface-variant/30">|</span>
<button class="font-label-rotulo text-xs uppercase font-bold text-error hover:underline flex items-center gap-1" type="button">
<span class="material-symbols-outlined text-[16px]">delete</span> Eliminar
                    </button>
</div>
</div>
<!-- Tarjeta 2: Secundaria Oficina Condesa -->
<div class="p-space-md rounded-xl bg-surface-container-low border-2 border-on-surface shadow-[2px_2px_0px_#211d1a] flex flex-col justify-between gap-space-sm">
<div class="flex flex-col gap-1.5">
<div class="flex items-center justify-between">
<div class="flex items-center gap-1.5 font-display-hero text-base uppercase font-bold text-on-surface">
<span class="material-symbols-outlined text-tertiary text-[20px]">apartment</span>
                        Oficina - Condesa
                      </div>
<button class="text-[11px] font-label-rotulo text-secondary uppercase font-extrabold hover:underline" type="button">
                        Hacer predeterminada
                      </button>
</div>
<p class="font-body-md text-sm text-on-surface font-semibold leading-snug">
                      Amsterdam 210, Piso 4, Condesa, CDMX, C.P. 06100
                    </p>
<div class="flex items-center gap-1 text-on-surface-variant font-body-sm text-xs bg-surface-container p-1.5 rounded border border-on-surface/20">
<span class="material-symbols-outlined text-[15px] text-tertiary">info</span>
<span>Dejar en recepción con vigilancia.</span>
</div>
</div>
<div class="flex items-center justify-end gap-3 pt-2 border-t border-on-surface/15">
<button class="font-label-rotulo text-xs uppercase font-bold text-primary hover:underline flex items-center gap-1" type="button">
<span class="material-symbols-outlined text-[16px]">edit</span> Editar
                    </button>
<span class="text-on-surface-variant/30">|</span>
<button class="font-label-rotulo text-xs uppercase font-bold text-error hover:underline flex items-center gap-1" type="button">
<span class="material-symbols-outlined text-[16px]">delete</span> Eliminar
                    </button>
</div>
</div>
</div>
<!-- Estado Vacío Ilustrado Demo / Alternativa -->
<div class="p-space-md rounded-xl border-2 border-dashed border-on-surface/30 bg-surface-container/40 flex flex-col sm:flex-row items-center justify-between gap-space-sm text-center sm:text-left">
<div class="flex items-center gap-3">
<div class="w-10 h-10 rounded-full bg-surface-container-high border border-on-surface flex items-center justify-center text-on-surface shrink-0">
<span class="material-symbols-outlined text-[20px]">add_location_alt</span>
</div>
<div>
<span class="font-display-hero text-sm uppercase font-bold text-on-surface block">¿Quieres registrar otra dirección?</span>
<span class="font-body-sm text-xs text-on-surface-variant">Todavía no tienes direcciones guardadas para otra zona o evento.</span>
</div>
</div>
<button class="px-space-md py-1.5 rounded-lg bg-surface-container-lowest text-on-surface font-label-rotulo text-xs uppercase tracking-wider border-2 border-on-surface shadow-[2px_2px_0px_#211d1a] hover:bg-surface-container transition-all" type="button">
                  + Agregar dirección
                </button>
</div>
</section>
<!-- C) SECCIÓN MIS PEDIDOS RESUMIDOS -->
<section class="w-full bg-surface-container-lowest p-space-md lg:p-space-lg rounded-xl border-2 border-on-surface shadow-[3px_3px_0px_#211d1a] flex flex-col gap-space-md" id="seccion-pedidos">
<div class="flex flex-wrap items-center justify-between gap-space-sm border-b-2 border-dashed border-on-surface/20 pb-space-sm">
<div class="flex items-center gap-space-sm">
<span class="material-symbols-outlined text-primary text-[28px]">local_mall</span>
<div>
<h2 class="font-display-hero text-headline-sm uppercase text-on-surface">Mis Pedidos Recientes</h2>
<p class="font-body-sm text-sm text-on-surface-variant">Rastreo express de tus antojitos y botanas</p>
</div>
</div>
<a class="font-label-rotulo text-xs uppercase font-extrabold text-secondary hover:underline flex items-center gap-1" href="#">
                  Ver todos los pedidos <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
</a>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-space-md">
<!-- Pedido 1: En Ruta -->
<div class="p-space-md rounded-xl bg-surface-container-low border-2 border-on-surface shadow-[2px_2px_0px_#211d1a] flex flex-col justify-between gap-space-sm">
<div class="flex items-start justify-between gap-2">
<div class="flex items-center gap-3">
<div class="w-12 h-12 rounded-lg bg-amarillo-maiz border border-on-surface flex items-center justify-center text-on-surface shrink-0">
<span class="material-symbols-outlined text-[26px]">two_wheeler</span>
</div>
<div class="flex flex-col">
<span class="font-display-hero text-base uppercase font-bold text-on-surface">Folio #FM-000123</span>
<span class="font-body-sm text-xs text-on-surface-variant">Vaso Fruta Madre Gigante + Charola Botanera</span>
<span class="font-label-rotulo text-xs font-black text-primary mt-0.5">$450.00 MXN</span>
</div>
</div>
<span class="px-2 py-0.5 rounded-full bg-tertiary text-on-tertiary font-label-rotulo text-[10px] uppercase font-bold border border-on-surface">
                      🛵 En ruta (~15 min)
                    </span>
</div>
<div class="w-full bg-surface-container-high h-2 rounded-full overflow-hidden border border-on-surface/20">
<div class="bg-primary h-full rounded-full w-[80%]"></div>
</div>
<button class="w-full py-2 px-space-md rounded-lg bg-primary text-on-primary font-label-rotulo text-xs uppercase tracking-wider border-2 border-on-surface shadow-[2px_2px_0px_#211d1a] hover:translate-x-0.5 hover:translate-y-0.5 flex items-center justify-center gap-1.5 transition-all" type="button">
<span class="material-symbols-outlined text-[16px]">location_searching</span>
<span>Rastrear pedido</span>
</button>
</div>
<!-- Pedido 2: Entregado -->
<div class="p-space-md rounded-xl bg-surface-container-low border-2 border-on-surface shadow-[2px_2px_0px_#211d1a] flex flex-col justify-between gap-space-sm">
<div class="flex items-start justify-between gap-2">
<div class="flex items-center gap-3">
<div class="w-12 h-12 rounded-lg bg-primary/20 border border-on-surface flex items-center justify-center text-primary shrink-0">
<span class="material-symbols-outlined text-[26px]">check_circle</span>
</div>
<div class="flex flex-col">
<span class="font-display-hero text-base uppercase font-bold text-on-surface">Folio #FM-000118</span>
<span class="font-body-sm text-xs text-on-surface-variant">Mango Loco en Flor + Gomitas Enchiladas</span>
<span class="font-label-rotulo text-xs font-black text-secondary mt-0.5">$320.00 MXN</span>
</div>
</div>
<span class="px-2 py-0.5 rounded-full bg-primary text-on-primary font-label-rotulo text-[10px] uppercase font-bold border border-on-surface">
                      ✅ Entregado
                    </span>
</div>
<div class="text-xs text-on-surface-variant font-body-sm flex items-center gap-1">
<span class="material-symbols-outlined text-[14px]">calendar_today</span>
<span>Entregado el 12 de Octubre a las 16:45 hrs</span>
</div>
<button class="w-full py-2 px-space-md rounded-lg bg-surface-container-lowest hover:bg-surface-container text-on-surface font-label-rotulo text-xs uppercase tracking-wider border-2 border-on-surface shadow-[2px_2px_0px_#211d1a] flex items-center justify-center gap-1.5 transition-all" type="button">
<span class="material-symbols-outlined text-[16px]">refresh</span>
<span>Pedir otra vez</span>
</button>
</div>
</div>
<!-- Estado Vacío Referencia Demo -->
<div class="p-space-sm rounded-lg bg-surface-container/30 border border-dashed border-on-surface/20 flex flex-wrap items-center justify-between gap-2 text-xs">
<span class="text-on-surface-variant font-body-sm">¿Buscas pedidos anteriores de otra cuenta? "Todavía no tienes pedidos adicionales."</span>
<button class="font-label-rotulo text-xs uppercase text-primary font-extrabold hover:underline" type="button">Ir al catálogo →</button>
</div>
</section>
<!-- D) SECCIÓN MIS RESERVAS DE EVENTOS -->
<section class="w-full bg-surface-container-lowest p-space-md lg:p-space-lg rounded-xl border-2 border-on-surface shadow-[3px_3px_0px_#211d1a] flex flex-col gap-space-md" id="seccion-reservas">
<div class="flex flex-wrap items-center justify-between gap-space-sm border-b-2 border-dashed border-on-surface/20 pb-space-sm">
<div class="flex items-center gap-space-sm">
<span class="material-symbols-outlined text-secondary text-[28px]">celebration</span>
<div>
<h2 class="font-display-hero text-headline-sm uppercase text-on-surface">Mis Reservas de Carritos y Eventos</h2>
<p class="font-body-sm text-sm text-on-surface-variant">Tus pachangas con el Puesto Digital contratado</p>
</div>
</div>
<a class="font-label-rotulo text-xs uppercase font-extrabold text-secondary hover:underline flex items-center gap-1" href="#">
                  Ver cotizaciones <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
</a>
</div>
<!-- Tarjeta Reserva Confirmada Mariana -->
<div class="p-space-md rounded-xl bg-surface-container-low border-2 border-on-surface shadow-[2px_2px_0px_#211d1a] flex flex-col gap-space-sm">
<div class="flex flex-wrap items-start justify-between gap-space-sm">
<div>
<span class="font-label-rotulo text-xs uppercase text-secondary font-black">Evento #FM-EV-000123</span>
<h3 class="font-display-hero text-lg uppercase font-extrabold text-on-surface">Cumpleaños Mariana (80 invitados)</h3>
<p class="font-body-sm text-sm text-on-surface-variant">Paquete: <strong class="text-on-surface">Carrito Botanero Imperial</strong></p>
</div>
<span class="px-2.5 py-1 rounded-full bg-primary text-on-primary font-label-rotulo text-xs uppercase font-extrabold border border-on-surface">
                    Confirmada (Anticipo cubierto)
                  </span>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-2 bg-surface-container p-space-sm rounded-lg border border-on-surface/20 font-body-sm text-sm">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-secondary text-[18px]">event</span>
<span><strong>Fecha &amp; Horario:</strong> Sábado 21 de Octubre • 15:00 hrs</span>
</div>
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-primary text-[18px]">payments</span>
<span><strong>Costo total:</strong> $2,800.00 MXN (Resta liquidar en montaje)</span>
</div>
</div>
<div class="flex flex-wrap items-center justify-end gap-space-sm pt-2 border-t border-on-surface/15">
<button class="px-space-md py-1.5 rounded-lg bg-surface-container-lowest text-on-surface font-label-rotulo text-xs uppercase tracking-wider border-2 border-on-surface shadow-[2px_2px_0px_#211d1a] hover:bg-surface-container flex items-center gap-1.5 transition-all" type="button">
<span class="material-symbols-outlined text-[16px]">visibility</span>
<span>Ver detalles de evento</span>
</button>
<button class="px-space-md py-1.5 rounded-lg bg-secondary text-on-secondary font-label-rotulo text-xs uppercase tracking-wider border-2 border-on-surface shadow-[2px_2px_0px_#211d1a] hover:translate-x-0.5 hover:translate-y-0.5 flex items-center gap-1.5 transition-all" type="button">
<span class="material-symbols-outlined text-[16px]">download</span>
<span>Descargar comprobante</span>
</button>
</div>
</div>
<!-- Estado Vacío Referencia Paquetes -->
<div class="p-space-sm rounded-lg bg-surface-container/30 border border-dashed border-on-surface/20 flex flex-wrap items-center justify-between gap-2 text-xs">
<span class="text-on-surface-variant font-body-sm">¿Organizas boda, posada o fiesta patronal? "Todavía no tienes reservas de carritos adicionales."</span>
<button class="font-label-rotulo text-xs uppercase text-secondary font-extrabold hover:underline" type="button">Explorar paquetes →</button>
</div>
</section>
<!-- E) SECCIÓN NOTIFICACIONES & ALERTAS -->
<section class="w-full bg-surface-container-lowest p-space-md lg:p-space-lg rounded-xl border-2 border-on-surface shadow-[3px_3px_0px_#211d1a] flex flex-col gap-space-md" id="seccion-notificaciones">
<div class="flex flex-wrap items-center justify-between gap-space-sm border-b-2 border-dashed border-on-surface/20 pb-space-sm">
<div class="flex items-center gap-space-sm">
<span class="material-symbols-outlined text-tertiary text-[28px]">notifications_active</span>
<div>
<h2 class="font-display-hero text-headline-sm uppercase text-on-surface">Notificaciones &amp; Alertas</h2>
<p class="font-body-sm text-sm text-on-surface-variant">Tú mandas sobre los avisos que te enviamos al cel</p>
</div>
</div>
<span class="font-label-rotulo text-xs uppercase text-tertiary font-bold bg-tertiary/10 border border-tertiary px-2 py-0.5 rounded-full">
                  2 nuevas hoy
                </span>
</div>
<!-- Toggles Estilizados -->
<div class="flex flex-col gap-space-sm">
<!-- Toggle 1 Activo -->
<div class="flex items-center justify-between p-space-sm rounded-lg bg-surface-container-low border border-on-surface/15">
<div class="flex flex-col max-w-lg">
<span class="font-label-rotulo text-sm uppercase text-on-surface font-bold">Alertas de pedidos en tiempo real (WhatsApp y SMS)</span>
<span class="font-body-sm text-xs text-on-surface-variant">Aviso al instante cuando el repartidor sale con tu fruta picada</span>
</div>
<label class="relative inline-flex items-center cursor-pointer shrink-0">
<input checked="" class="sr-only peer" type="checkbox"/>
<div class="w-12 h-6 bg-surface-container-highest peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-6 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary border-2 border-on-surface"></div>
</label>
</div>
<!-- Toggle 2 Activo -->
<div class="flex items-center justify-between p-space-sm rounded-lg bg-surface-container-low border border-on-surface/15">
<div class="flex flex-col max-w-lg">
<span class="font-label-rotulo text-sm uppercase text-on-surface font-bold">Recordatorios de fechas de eventos y montajes</span>
<span class="font-body-sm text-xs text-on-surface-variant">Avisos de horarios, chofer asignado e insumos del carrito botanero</span>
</div>
<label class="relative inline-flex items-center cursor-pointer shrink-0">
<input checked="" class="sr-only peer" type="checkbox"/>
<div class="w-12 h-6 bg-surface-container-highest peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-6 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary border-2 border-on-surface"></div>
</label>
</div>
<!-- Toggle 3 Inactivo -->
<div class="flex items-center justify-between p-space-sm rounded-lg bg-surface-container-low border border-on-surface/15">
<div class="flex flex-col max-w-lg">
<span class="font-label-rotulo text-sm uppercase text-on-surface font-bold">Descuentos sorpresa y promociones de temporada</span>
<span class="font-body-sm text-xs text-on-surface-variant">Cupones cuando haya cosecha de mango ataúlfo y jícama dulce</span>
</div>
<label class="relative inline-flex items-center cursor-pointer shrink-0">
<input class="sr-only peer" type="checkbox"/>
<div class="w-12 h-6 bg-surface-container-highest peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-6 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary border-2 border-on-surface"></div>
</label>
</div>
</div>
<!-- Lista de 2 notificaciones recientes -->
<div class="flex flex-col gap-2 pt-2">
<span class="font-label-rotulo text-xs uppercase text-on-surface-variant font-extrabold">Avisos Recientes del Puesto</span>
<div class="flex items-center justify-between p-2.5 rounded-lg bg-surface-container border border-on-surface/20 text-sm font-body-sm">
<span class="flex items-center gap-2 text-on-surface">
<span class="text-base">🔔</span>
<span>Tu pedido <strong>FM-000123</strong> ya va en camino con Toño el repartidor.</span>
</span>
<span class="font-label-rotulo text-[11px] uppercase text-on-surface-variant">Hace 12 min</span>
</div>
<div class="flex items-center justify-between p-2.5 rounded-lg bg-surface-container border border-on-surface/20 text-sm font-body-sm">
<span class="flex items-center gap-2 text-on-surface">
<span class="text-base">🎉</span>
<span>Tu reserva de carrito para el <strong>21 de Octubre</strong> fue confirmada con éxito.</span>
</span>
<span class="font-label-rotulo text-[11px] uppercase text-on-surface-variant">Ayer</span>
</div>
</div>
</section>
<!-- F) SECCIÓN PREFERENCIAS & MODO DE PANTALLA -->
<section class="w-full bg-surface-container-lowest p-space-md lg:p-space-lg rounded-xl border-2 border-on-surface shadow-[3px_3px_0px_#211d1a] flex flex-col gap-space-md" id="seccion-preferencias">
<div class="flex flex-wrap items-center justify-between gap-space-sm border-b-2 border-dashed border-on-surface/20 pb-space-sm">
<div class="flex items-center gap-space-sm">
<span class="material-symbols-outlined text-primary text-[28px]">palette</span>
<div>
<h2 class="font-display-hero text-headline-sm uppercase text-on-surface">Preferencias &amp; Modo de Pantalla</h2>
<p class="font-body-sm text-sm text-on-surface-variant">Personaliza la vista y tu experiencia de compra</p>
</div>
</div>
</div>
<!-- Selector de Tema Visual -->
<div class="flex flex-col gap-2">
<span class="font-label-rotulo text-xs uppercase font-extrabold text-on-surface">Tema Visual Seleccionable</span>
<div class="grid grid-cols-1 sm:grid-cols-3 gap-space-sm" id="selector-tema-visual">
<!-- Modo Claro Activo -->
<button class="flex items-center gap-2.5 p-space-sm rounded-lg bg-primary text-on-primary border-2 border-on-surface shadow-[2px_2px_0px_#211d1a] text-left transition-all" type="button">
<span class="material-symbols-outlined text-[22px] text-amarillo-maiz">light_mode</span>
<div class="flex flex-col">
<span class="font-label-rotulo text-xs uppercase font-extrabold">Modo Claro</span>
<span class="text-[11px] opacity-90">Crema Mercado (Activo)</span>
</div>
</button>
<!-- Modo Oscuro -->
<button class="flex items-center gap-2.5 p-space-sm rounded-lg bg-surface-container-low text-on-surface hover:bg-surface-container border-2 border-on-surface shadow-[2px_2px_0px_#211d1a] text-left transition-all" type="button">
<span class="material-symbols-outlined text-[22px] text-secondary">dark_mode</span>
<div class="flex flex-col">
<span class="font-label-rotulo text-xs uppercase font-extrabold">Modo Oscuro</span>
<span class="text-[11px] text-on-surface-variant">Noche Botanera (#211D1A)</span>
</div>
</button>
<!-- Automático -->
<button class="flex items-center gap-2.5 p-space-sm rounded-lg bg-surface-container-low text-on-surface hover:bg-surface-container border-2 border-on-surface shadow-[2px_2px_0px_#211d1a] text-left transition-all" type="button">
<span class="material-symbols-outlined text-[22px] text-tertiary">settings_brightness</span>
<div class="flex flex-col">
<span class="font-label-rotulo text-xs uppercase font-extrabold">Automático</span>
<span class="text-[11px] text-on-surface-variant">Del sistema</span>
</div>
</button>
</div>
</div>
<!-- Checkboxes Funcionales -->
<div class="flex flex-col gap-space-sm pt-2">
<label class="flex items-center gap-3 cursor-pointer select-none bg-surface-container-low p-2.5 rounded-lg border border-on-surface/20">
<input checked="" class="w-5 h-5 rounded border-2 border-on-surface text-primary focus:ring-primary accent-primary" type="checkbox"/>
<div class="flex flex-col">
<span class="font-label-rotulo text-xs uppercase font-bold text-on-surface">Recordar mis datos para futuros pedidos</span>
<span class="font-body-sm text-xs text-on-surface-variant">No tendrás que escribir nombre ni dirección cada que se te antoje fruta</span>
</div>
</label>
<label class="flex items-center gap-3 cursor-pointer select-none bg-surface-container-low p-2.5 rounded-lg border border-on-surface/20">
<input checked="" class="w-5 h-5 rounded border-2 border-on-surface text-primary focus:ring-primary accent-primary" type="checkbox"/>
<div class="flex flex-col">
<span class="font-label-rotulo text-xs uppercase font-bold text-on-surface">Recibir promociones exclusivas y cupones de barrio</span>
<span class="font-body-sm text-xs text-on-surface-variant">Descuentos especiales aplicables directamente a tu canasta</span>
</div>
</label>
</div>
</section>
<!-- G) SECCIÓN SEGURIDAD & SESIONES -->
<section class="w-full bg-surface-container-lowest p-space-md lg:p-space-lg rounded-xl border-2 border-on-surface shadow-[3px_3px_0px_#211d1a] flex flex-col gap-space-md" id="seccion-seguridad">
<div class="flex flex-wrap items-center justify-between gap-space-sm border-b-2 border-dashed border-on-surface/20 pb-space-sm">
<div class="flex items-center gap-space-sm">
<span class="material-symbols-outlined text-primary text-[28px]">lock</span>
<div>
<h2 class="font-display-hero text-headline-sm uppercase text-on-surface">Seguridad &amp; Sesiones</h2>
<p class="font-body-sm text-sm text-on-surface-variant">Protege tu acceso y tus métodos de entrega</p>
</div>
</div>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-space-md">
<!-- Bloque Contraseña -->
<div class="p-space-md rounded-xl bg-surface-container-low border-2 border-on-surface shadow-[2px_2px_0px_#211d1a] flex flex-col justify-between gap-space-sm">
<div class="flex flex-col gap-1">
<span class="font-label-rotulo text-xs uppercase font-extrabold text-on-surface">Contraseña Encriptada</span>
<div class="p-2.5 bg-surface-container-lowest rounded-lg border border-on-surface/20 font-mono tracking-widest text-on-surface font-bold text-base">
                      ••••••••••••
                    </div>
<span class="font-body-sm text-[11px] text-on-surface-variant">Actualizada por última vez hace 2 meses</span>
</div>
<button class="w-fit px-space-md py-1.5 rounded-lg bg-surface-container text-on-surface font-label-rotulo text-xs uppercase tracking-wider border-2 border-on-surface shadow-[2px_2px_0px_#211d1a] hover:bg-surface-container-high transition-all" type="button">
                    Cambiar contraseña
                  </button>
</div>
<!-- Bloque Sesiones Activas -->
<div class="p-space-md rounded-xl bg-surface-container-low border-2 border-on-surface shadow-[2px_2px_0px_#211d1a] flex flex-col justify-between gap-space-sm">
<div class="flex flex-col gap-1.5">
<span class="font-label-rotulo text-xs uppercase font-extrabold text-on-surface">Sesiones Activas</span>
<div class="flex items-center gap-2 text-xs font-body-sm text-on-surface">
<span class="w-2.5 h-2.5 rounded-full bg-primary border border-on-surface inline-block"></span>
<span><strong>Chrome en MacOS (CDMX)</strong> • Esta sesión activa</span>
</div>
<div class="flex items-center gap-2 text-xs font-body-sm text-on-surface-variant">
<span class="w-2.5 h-2.5 rounded-full bg-on-surface-variant/40 border border-on-surface/40 inline-block"></span>
<span><strong>App Móvil iPhone 15</strong></span>
</div>
</div>
<button class="w-fit px-space-md py-1.5 rounded-lg bg-error-container text-on-error-container font-label-rotulo text-xs uppercase tracking-wider border border-error hover:opacity-90 transition-opacity" type="button">
                    Cerrar otras sesiones
                  </button>
</div>
</div>
</section>
</div>
</div>
</section>
</div>
</main>
<!-- FOOTER INSTITUCIONAL OFICIAL -->

<footer class="w-full bg-surface-container-low text-on-surface border-t-2 border-on-surface">
<div class="h-2 w-full flex">
<div class="flex-1 bg-tertiary"></div>
<div class="flex-1 bg-amarillo-maiz"></div>
<div class="flex-1 bg-secondary"></div>
<div class="flex-1 bg-primary"></div>
</div>
<div class="w-full px-gutter lg:px-margin-desktop py-space-xl grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-gutter-desktop">
<div class="lg:col-span-2 flex flex-col gap-space-md">
<div class="flex items-center gap-space-sm">
<span class="font-display-hero text-headline-sm uppercase text-primary font-bold">FRUTA MADRE</span>
</div>
<p class="font-label-rotulo text-label-rotulo text-secondary tracking-wide uppercase font-extrabold">TE DAMOS HASTA PARA LLEVAR.</p>
<p class="font-body-md text-body-md text-on-surface-variant max-w-md">
          El auténtico sabor del mercado urbano mexicano en tu puerta: fruta fresca con chilito y limón, botanas preparadas, repostería artesanal, flores de temporada y carritos de antojos para tus mejores eventos.
        </p>
<div class="inline-flex items-center gap-space-xs bg-surface-container border-2 border-on-surface shadow-[2px_2px_0px_#211d1a] px-space-md py-space-xs rounded-full w-fit">
<span class="font-label-rotulo text-xs text-on-surface uppercase tracking-wider font-extrabold">🇲🇽 Hecho con mucho chamoy y amor en México</span>
</div>
</div>
<div class="flex flex-col gap-space-sm">
<h3 class="font-display-hero text-sm uppercase text-on-surface font-extrabold">Explorar</h3>
<ul class="flex flex-col gap-space-xs font-body-sm text-body-sm text-on-surface-variant">
<li class="hover:text-primary transition-colors cursor-pointer">Inicio</li>
<li class="hover:text-primary transition-colors cursor-pointer">Catálogo Completo</li>
<li class="hover:text-primary transition-colors cursor-pointer">Carritos de Eventos</li>
<li class="hover:text-primary transition-colors cursor-pointer">Mis Pedidos y Rastreo</li>
</ul>
</div>
<div class="flex flex-col gap-space-sm">
<h3 class="font-display-hero text-sm uppercase text-on-surface font-extrabold">Atención</h3>
<ul class="flex flex-col gap-space-xs font-body-sm text-body-sm text-on-surface-variant">
<li class="hover:text-primary transition-colors cursor-pointer">Contacto Directo</li>
<li class="hover:text-primary transition-colors cursor-pointer">Preguntas Frecuentes</li>
<li class="hover:text-primary transition-colors cursor-pointer">Términos y Condiciones</li>
<li class="hover:text-primary transition-colors cursor-pointer">Aviso de Privacidad</li>
</ul>
</div>
<div class="flex flex-col gap-space-sm">
<h3 class="font-display-hero text-sm uppercase text-on-surface font-extrabold">Mercado Social</h3>
<p class="font-body-sm text-body-sm text-on-surface-variant">¡Échanos un grito o síguenos la pista!</p>
<div class="flex flex-wrap gap-space-xs pt-space-xs">
<a class="px-space-md py-1 bg-surface-container border border-on-surface rounded-lg font-label-rotulo text-xs uppercase font-bold text-on-surface hover:bg-surface-container-high transition-colors" href="#">Instagram</a>
<a class="px-space-md py-1 bg-surface-container border border-on-surface rounded-lg font-label-rotulo text-xs uppercase font-bold text-on-surface hover:bg-surface-container-high transition-colors" href="#">TikTok</a>
<a class="px-space-md py-1 bg-surface-container border border-on-surface rounded-lg font-label-rotulo text-xs uppercase font-bold text-on-surface hover:bg-surface-container-high transition-colors" href="#">WhatsApp</a>
<a class="px-space-md py-1 bg-surface-container border border-on-surface rounded-lg font-label-rotulo text-xs uppercase font-bold text-on-surface hover:bg-surface-container-high transition-colors" href="#">Facebook</a>
</div>
</div>
</div>
<div class="w-full px-gutter lg:px-margin-desktop py-space-md bg-surface-container border-t border-on-surface/20 flex flex-col sm:flex-row items-center justify-between gap-space-sm">
<p class="font-body-sm text-body-sm text-on-surface-variant">© 2025 FRUTA MADRE. Todos los derechos reservados.</p>
<p class="font-label-rotulo text-xs text-on-surface uppercase font-extrabold tracking-widest">Hecho con mucho chamoy y amor en México • Puesto Digital No. 01</p>
</div>
</footer>
<!-- Script interactivo integrado para edición de perfil y confirmaciones -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
      const formPerfil = document.getElementById('form-perfil');
      const alerta = document.getElementById('alerta-perfil');
      const resumenNombre = document.getElementById('resumen-nombre');
      const resumenCorreo = document.getElementById('resumen-correo');
      const resumenTel = document.getElementById('resumen-tel');
      const inputNombre = document.getElementById('input-nombre');
      const inputCorreo = document.getElementById('input-correo');
      const inputTel = document.getElementById('input-telefono');
      const btnCancelar = document.getElementById('btn-cancelar-perfil');

      btnCancelar?.addEventListener('click', () => {
        inputNombre.value = resumenNombre.textContent.trim();
        inputCorreo.value = resumenCorreo.textContent.trim();
        inputTel.value = resumenTel.textContent.trim();
      });

      const btnCerrar = document.getElementById('btn-cerrar-sesion');
      btnCerrar?.addEventListener('click', () => {
        if (confirm('¿Deseas cerrar sesión en este equipo? Tus antojos y órdenes seguirán bien guardados.')) {
          alert('Has cerrado sesión correctamente. ¡Vuelve pronto por tu fruta preparada!');
        }
      });

      const toggleModo = document.getElementById('toggle-modo');
      toggleModo?.addEventListener('click', () => {
        document.documentElement.classList.toggle('dark');
      });
    });
  </script>
</body></html>