<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<meta content="web_standard" name="shell-type"/>
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=Syne:wght@400..800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<style>
@layer base {
html, body { margin: 0; padding: 0; }
body { overscroll-behavior: none; }
main > :first-child { margin-top: 0 !important; }
main > :last-child { margin-bottom: 0 !important; }
}
::-webkit-scrollbar { display: none; }
</style>
<script src="https://cdn.tailwindcss.com"></script>
<script id="tailwind-config">
tailwind.config = {
 darkMode: "class",
 theme: {
  extend: {
   colors: {
    "surface-container-lowest":"#ffffff",
    "surface-container":"#f8edd8",
    "inverse-surface":"#353022",
    "on-error":"#ffffff",
    "background":"#fff8f0",
    "surface-bright":"#fff8f0",
    "tertiary-container":"#b55107",
    "on-primary-container":"#d0ffc8",
    "error-container":"#ffdad6",
    "on-primary-fixed-variant":"#1f5121",
    "on-surface-variant":"#41493f",
    "on-surface":"#201b0f",
    "surface-variant":"#ece1cd",
    "secondary-container":"#fc6a8a",
    "primary-container":"#477a45",
    "secondary-fixed-dim":"#ffb2be",
    "tertiary":"#903e00",
    "on-primary":"#ffffff",
    "on-secondary-container":"#6b0027",
    "surface-container-high":"#f2e7d2",
    "surface-dim":"#e3d9c5",
    "inverse-primary":"#9dd496",
    "primary-fixed-dim":"#9dd496",
    "surface-container-low":"#fdf2de",
    "outline":"#72796e",
    "on-primary-fixed":"#002204",
    "inverse-on-surface":"#fbf0db",
    "primary-fixed":"#b8f1b0",
    "tertiary-fixed-dim":"#ffb68f",
    "error":"#ba1a1a",
    "on-tertiary-fixed-variant":"#773200",
    "on-tertiary-container":"#fff0e9",
    "surface-tint":"#376936",
    "secondary":"#ac2d50",
    "surface-container-highest":"#ece1cd",
    "on-secondary-fixed-variant":"#8c1039",
    "surface":"#fff8f0",
    "secondary-fixed":"#ffd9de",
    "on-error-container":"#93000a",
    "on-secondary":"#ffffff",
    "on-background":"#201b0f",
    "tertiary-fixed":"#ffdbca",
    "on-tertiary":"#ffffff",
    "on-tertiary-fixed":"#331100",
    "primary":"#2f612f",
    "on-secondary-fixed":"#400014"
   },
   borderRadius: {
    "DEFAULT":"0.25rem",
    "lg":"0.5rem",
    "xl":"0.75rem",
    "full":"9999px"
   },
   spacing: {
    "gutter-desktop":"2rem",
    "space-sm":"0.5rem",
    "margin-desktop":"3rem",
    "space-xs":"0.25rem",
    "gutter":"1.25rem",
    "space-xl":"2.5rem",
    "margin":"1.25rem",
    "space-md":"1rem",
    "space-lg":"1.5rem"
   },
   fontFamily: {
    "body-lg":["DM Sans"],
    "label-rotulo":["Syne"],
    "headline-lg-mobile":["Syne"],
    "headline-md":["Syne"],
    "headline-sm":["Syne"],
    "label-md":["DM Sans"],
    "body-sm":["DM Sans"],
    "display-hero-mobile":["Syne"],
    "label-sm":["DM Sans"],
    "headline-lg":["Syne"],
    "display-hero":["Syne"],
    "body-md":["DM Sans"]
   },
   fontSize: {
    "body-lg":["18px",{lineHeight:"28px",fontWeight:"400"}],
    "label-rotulo":["14px",{lineHeight:"18px",letterSpacing:"0.06em",fontWeight:"800"}],
    "headline-lg-mobile":["28px",{lineHeight:"34px",letterSpacing:"-0.01em",fontWeight:"700"}],
    "headline-md":["28px",{lineHeight:"34px",letterSpacing:"-0.01em",fontWeight:"700"}],
    "headline-sm":["22px",{lineHeight:"28px",fontWeight:"600"}],
    "label-md":["14px",{lineHeight:"18px",letterSpacing:"0.02em",fontWeight:"700"}],
    "body-sm":["14px",{lineHeight:"20px",fontWeight:"400"}],
    "display-hero-mobile":["38px",{lineHeight:"42px",letterSpacing:"-0.02em",fontWeight:"800"}],
    "label-sm":["12px",{lineHeight:"16px",letterSpacing:"0.03em",fontWeight:"700"}],
    "headline-lg":["40px",{lineHeight:"46px",letterSpacing:"-0.02em",fontWeight:"700"}],
    "display-hero":["56px",{lineHeight:"60px",letterSpacing:"-0.03em",fontWeight:"800"}],
    "body-md":["16px",{lineHeight:"24px",fontWeight:"400"}]
   }
  }
 }
};
</script>
</head>

<body class="bg-background font-body-md text-on-surface antialiased">
<header class="fixed top-0 left-0 right-0 z-50 bg-surface/95 backdrop-blur-md shadow-[0_1px_8px_rgba(0,0,0,0.04)]">
<div class="h-20 w-full px-gutter lg:px-margin-desktop flex items-center justify-between gap-space-md">
<div class="flex items-center gap-space-md shrink-0">
<img alt="FRUTA MADRE Logo" class="h-8 w-auto object-contain" src="https://lh3.googleusercontent.com/aida/AEtjO1W0q5rsMHajuhP3xliGM4ENIiIXYTajmRgvYwHU3mD6RbhEJ5J2-fIl5UaybVd_COqPwuK5CKGypw3IWxlfFTLUUC8Ga9K1e3W-wQqQIKre8hpcnDPDDlQxaE0X5au8QORDV9Ktw-44n2sHR0f0miZl00x_QcRrpGzizUh0ImPQSoteXpbyDjbmV_5NQghiScqmtUmF2BDSQpH7-q0y8HS-58EiwwtNmOlsdLqTePCU7JlvyHVbFyHlQw"/>
<div class="flex flex-col">
<span class="font-label-rotulo text-label-rotulo uppercase text-on-surface tracking-wider leading-none">FRUTA MADRE</span>
<span class="font-label-sm text-label-sm text-secondary font-bold leading-tight">Te damos hasta para llevar</span>
</div>
</div>

<nav class="hidden xl:flex items-center gap-space-sm bg-surface-container-low p-space-xs rounded-lg">
<a class="text-on-surface-variant hover:text-on-surface px-space-md py-space-xs font-label-md text-label-md transition-colors" href="index.php">Inicio</a>
<a class="text-on-surface-variant hover:text-on-surface px-space-md py-space-xs font-label-md text-label-md transition-colors" href="index.php?accion=catalogo">Catálogo</a>
<a aria-current="page" class="px-space-md py-space-xs transition-colors bg-primary text-on-primary font-bold rounded-lg shadow-[2px_2px_0px_#201b0f]" href="index.php?accion=eventos">Eventos</a>
<a class="text-on-surface-variant hover:text-on-surface px-space-md py-space-xs font-label-md text-label-md transition-colors" href="index.php?accion=mis-pedidos">Mis pedidos</a>
</nav>

<div class="flex items-center gap-space-sm shrink-0">
<div class="hidden md:flex items-center bg-surface-container-lowest rounded-lg px-space-md py-space-xs">
<span class="material-symbols-outlined text-on-surface-variant text-[20px] mr-space-xs">search</span>
<input class="bg-transparent text-on-surface placeholder:text-on-surface-variant/60 font-body-sm text-body-sm focus:outline-none w-36 lg:w-48" placeholder="Buscar antojo..." type="text"/>
</div>
<a aria-label="Carrito de compras" href="index.php?accion=carrito" class="relative p-space-sm rounded-lg bg-surface-container-low hover:bg-surface-container hover:text-on-surface text-on-surface transition-colors flex items-center justify-center">
<span class="material-symbols-outlined text-[22px]">shopping_bag</span>
</a>
<a aria-label="Mi cuenta" href="index.php?accion=cuenta" class="w-8 h-8 rounded-full bg-primary flex items-center justify-center shrink-0 cursor-pointer">
<span class="material-symbols-outlined text-on-primary text-[18px]">person</span>
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

<main class="w-full pt-20 bg-background min-h-screen">
<div class="flex flex-col w-full">

<section class="w-full px-gutter lg:px-margin-desktop pt-space-lg pb-space-md">
<div class="flex flex-wrap items-center justify-between gap-space-sm bg-surface-container-low p-space-md rounded-xl shadow-sm">
<div class="flex items-center gap-space-xs text-on-surface-variant font-body-sm text-body-sm">
<a class="hover:text-primary transition-colors flex items-center gap-1" href="index.php">
<span class="material-symbols-outlined text-[18px]">home</span>
<span>Inicio</span>
</a>
<span>/</span>
<span class="font-bold text-on-surface">Eventos</span>
</div>
<div class="flex flex-wrap items-center gap-space-sm">
<div class="inline-flex items-center gap-1 bg-surface-container-highest text-on-surface px-space-sm py-1 rounded-full text-label-sm font-label-rotulo uppercase">
<span class="text-tertiary font-bold">⚡</span>
<span>Puesto Digital No. 01</span>
</div>
<div class="inline-flex items-center gap-1 bg-secondary text-on-secondary px-space-sm py-1 rounded-full text-label-sm font-label-rotulo uppercase shadow-[2px_2px_0px_#201b0f]">
<span>🎉</span>
<span>Servicio para Pachangas y Eventos</span>
</div>
</div>
</div>
</section>

<!-- HERO -->
<section class="w-full px-gutter lg:px-margin-desktop py-space-lg relative overflow-hidden">
<div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter-desktop items-center">
<div class="lg:col-span-6 flex flex-col gap-space-md z-10">
<div class="inline-flex items-center gap-2 bg-tertiary-fixed text-on-tertiary-fixed px-space-md py-1 rounded-full w-fit">
<span class="material-symbols-outlined text-[18px] text-tertiary">celebration</span>
<span class="font-label-rotulo text-label-rotulo uppercase tracking-wider">Carritos &amp; Barras Callejeras</span>
</div>
<h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight leading-none">¿Hay pachanga o qué?</h1>
<p class="font-body-lg text-body-lg text-on-surface-variant max-w-xl">Llevamos el sabor de la calle a tu evento: fruta fresca, chilito, chamoy y antojos para compartir. Tú pones la pachanga, nosotros ponemos el puesto.</p>
<div class="flex flex-wrap items-center gap-space-md pt-space-sm">
<a class="inline-flex items-center gap-2 bg-primary-container text-on-primary font-label-rotulo text-label-rotulo uppercase px-space-lg py-space-md rounded-lg shadow-[3px_3px_0px_#201b0f] hover:translate-x-0.5 hover:translate-y-0.5 transition-all" href="#paquetes">
<span>Ver paquetes</span>
<span class="material-symbols-outlined text-[20px]">arrow_downward</span>
</a>
<a class="inline-flex items-center gap-2 bg-tertiary-container text-on-tertiary-container font-label-rotulo text-label-rotulo uppercase px-space-lg py-space-md rounded-lg shadow-[3px_3px_0px_#201b0f]" href="https://wa.me/?text=Hola%20Fruta%20Madre,%20quiero%20cotizar%20un%20evento" target="_blank" rel="noopener noreferrer">
<span>Cotizar Carrito por WhatsApp</span>
<span class="material-symbols-outlined text-[20px]">chat</span>
</a>
</div>
<div class="flex flex-wrap gap-space-lg pt-space-md">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-primary">check_circle</span>
<span class="font-body-sm text-body-sm">Montaje incluido</span>
</div>
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-primary">check_circle</span>
<span class="font-body-sm text-body-sm">Ingredientes frescos</span>
</div>
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-primary">check_circle</span>
<span class="font-body-sm text-body-sm">Atención personalizada</span>
</div>
</div>
</div>

<div class="lg:col-span-6 relative">
<div class="relative rounded-xl overflow-hidden shadow-xl">
<img class="w-full h-[320px] md:h-[440px] object-cover" alt="Carrito de fruta y botanas para eventos" src="https://lh3.googleusercontent.com/aida/AEtjO1X6XVOXLq3QtJEnDEoCDZb-q8NKPnOo_bXZqWN0Ylnj0w1URQ5pCr9Kiyin258RS4D4UJRzz2FkQITU6VdpYUGep0Bs7JkNi1-oF0ruarQPJZzgd-rmPo47X5wHvymSD-jEVq_Ivdq03cp0-s6IiRNtb1pcOKrxm8zgu_olMBtCG3MdHWxN_CnzmIpBmhrLjmo_k6hu0jHAiEEVtFMyjXDi9X5irB5hd9GvBdibZThSP-HDCiSQ835giQ"/>
<div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
<div class="absolute bottom-4 left-4 right-4 text-white">
<span class="inline-flex bg-secondary text-white px-space-md py-space-xs rounded-full font-label-rotulo text-label-sm uppercase">Sabor de barrio para tu fiesta</span>
</div>
</div>
</div>
</div>
</section>

<!-- FILTROS DE EVENTO -->
<section class="w-full px-gutter lg:px-margin-desktop py-space-md">
<div class="bg-surface-container-low p-space-lg rounded-xl shadow-sm">
<div class="flex flex-col md:flex-row md:items-center justify-between gap-space-sm mb-space-md">
<div>
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-secondary text-[22px]">tune</span>
<h2 class="font-headline-sm text-headline-sm text-on-surface">¿Qué vamos a celebrar?</h2>
</div>
<p class="font-body-sm text-body-sm text-on-surface-variant">Filtra los paquetes según tu pachanga o explora todos los montajes.</p>
</div>
<button class="self-start md:self-auto text-label-sm font-label-rotulo text-tertiary hover:text-on-surface uppercase flex items-center gap-1 bg-surface-container px-space-sm py-1 rounded-lg transition-colors" id="toggleEmptyStateBtn">
<span class="material-symbols-outlined text-[16px]">swap_horiz</span>
<span id="toggleBtnLabel">Simular sin paquetes</span>
</button>
</div>

<div class="flex flex-wrap items-center gap-space-xs sm:gap-space-sm" id="filterPillsContainer">
<button class="filter-btn active-filter flex items-center gap-space-xs px-space-md py-space-xs rounded-full font-label-rotulo text-label-sm uppercase transition-all bg-primary text-on-primary shadow-[2px_2px_0px_#201b0f]" data-filter="cumple"><span>🎂</span><span>Cumpleaños</span></button>
<button class="filter-btn flex items-center gap-space-xs px-space-md py-space-xs rounded-full font-label-rotulo text-label-sm uppercase transition-all bg-surface-container hover:bg-surface-container-high text-on-surface" data-filter="graduacion"><span>🎓</span><span>Graduación</span></button>
<button class="filter-btn flex items-center gap-space-xs px-space-md py-space-xs rounded-full font-label-rotulo text-label-sm uppercase transition-all bg-surface-container hover:bg-surface-container-high text-on-surface" data-filter="boda"><span>💍</span><span>Boda</span></button>
<button class="filter-btn flex items-center gap-space-xs px-space-md py-space-xs rounded-full font-label-rotulo text-label-sm uppercase transition-all bg-surface-container hover:bg-surface-container-high text-on-surface" data-filter="xv"><span>👑</span><span>XV Años</span></button>
<button class="filter-btn flex items-center gap-space-xs px-space-md py-space-xs rounded-full font-label-rotulo text-label-sm uppercase transition-all bg-surface-container hover:bg-surface-container-high text-on-surface" data-filter="reunion"><span>🎉</span><span>Reunión</span></button>
<button class="filter-btn flex items-center gap-space-xs px-space-md py-space-xs rounded-full font-label-rotulo text-label-sm uppercase transition-all bg-surface-container hover:bg-surface-container-high text-on-surface" data-filter="otro"><span>🥳</span><span>Otro evento</span></button>
</div>
</div>
</section>

<!-- ESTADO VACÍO -->
<section class="hidden w-full px-gutter lg:px-margin-desktop py-space-md" id="simulatedEmptyState">
<div class="bg-surface-container-high rounded-xl p-space-xl text-center flex flex-col items-center justify-center gap-space-md max-w-2xl mx-auto shadow-sm">
<div class="w-16 h-16 rounded-full bg-secondary-fixed flex items-center justify-center text-secondary">
<span class="material-symbols-outlined text-[36px]">soup_kitchen</span>
</div>
<h3 class="font-headline-sm text-headline-sm text-on-surface">¡Todavía estamos armando algo para esa ocasión!</h3>
<p class="font-body-md text-body-md text-on-surface-variant">No te quedes con las ganas: platícanos tu locura de fiesta y te armamos un paquete personalizado en friega de volada.</p>
<div class="flex flex-wrap items-center justify-center gap-space-sm pt-space-xs">
<button class="bg-primary text-on-primary px-space-lg py-space-sm rounded-lg font-label-rotulo text-label-rotulo uppercase shadow-[2px_2px_0px_#201b0f]" id="resetFiltersBtn">Ver todos los paquetes</button>
<a class="bg-surface-container-lowest text-on-surface px-space-lg py-space-sm rounded-lg font-label-rotulo text-label-rotulo uppercase shadow-[2px_2px_0px_#201b0f]" href="https://wa.me/" target="_blank" rel="noopener noreferrer">Platicar por WhatsApp</a>
</div>
</div>
</section>




<!-- PAQUETES -->
<section class="w-full px-gutter lg:px-margin-desktop py-space-lg" id="paquetes">
<div class="flex flex-col md:flex-row md:items-end justify-between gap-space-xs mb-space-lg">
<div>
<div class="inline-flex items-center gap-1.5 bg-secondary-fixed text-on-secondary-fixed px-space-sm py-0.5 rounded-full font-label-rotulo text-label-sm uppercase mb-space-xs"><span>🔥</span><span>Recomendados del Puesto</span></div>
<h2 class="font-headline-lg text-headline-lg text-on-surface">Paquetes para la ocasión</h2>
</div>
<p class="font-body-sm text-body-sm text-on-surface-variant max-w-sm">Todos los montajes incluyen personal con cofia, letrero rotulado rústico y barra de chilito libre.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-gutter-desktop" id="packagesGrid">

<!-- PAQUETE CHIDO -->
<div class="package-card flex flex-col bg-surface-container-lowest rounded-xl shadow-md overflow-hidden group hover:-translate-y-1 transition-transform">
<div class="relative h-48 w-full bg-surface-container overflow-hidden">
<img alt="Cucumbers and jicamas with Tajin" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" src="https://lh3.googleusercontent.com/aida/AEtjO1X6XVOXLq3QtJEnDEoCDZb-q8NKPnOo_bXZqWN0Ylnj0w1URQ5pCr9Kiyin258RS4D4UJRzz2FkQITU6VdpYUGep0Bs7JkNi1-oF0ruarQPJZzgd-rmPo47X5wHvymSD-jEVq_Ivdq03cp0-s6IiRNtb1pcOKrxm8zgu_olMBtCG3MdHWxN_CnzmIpBmhrLjmo_k6hu0jHAiEEVtFMyjXDi9X5irB5hd9GvBdibZThSP-HDCiSQ835giQ"/>
<div class="absolute top-3 left-3 bg-surface-container-highest text-on-surface px-space-sm py-1 rounded-full font-label-rotulo text-label-sm uppercase shadow-sm">Favorito para compas</div>
</div>
<div class="p-space-lg flex-1 flex flex-col justify-between">
<div>
<h3 class="font-headline-sm text-headline-sm text-on-surface mb-1">Paquete Chido</h3>
<p class="font-body-sm text-body-sm text-on-surface-variant mb-space-md">Barra básica de fruta de temporada rebanada al instante con chilito en polvo, limón criollo y salsas de la casa.</p>
<div class="space-y-1.5 pb-space-md text-body-sm text-on-surface">
<div class="flex items-center gap-2"><span class="material-symbols-outlined text-primary text-[18px]">group</span><span>Hasta <strong>25 personas</strong></span></div>
<div class="flex items-center gap-2"><span class="material-symbols-outlined text-tertiary text-[18px]">schedule</span><span>Duración: <strong>2 horas de servicio</strong></span></div>
</div>
</div>
<div class="pt-space-md bg-surface-container-low -mx-space-lg -mb-space-lg p-space-lg">
<div class="flex items-baseline justify-between mb-space-sm"><span class="font-label-sm text-label-sm text-on-surface-variant uppercase">Inversión base</span><span class="font-headline-sm text-headline-sm text-primary font-bold">Desde $1,850 <span class="font-body-sm text-body-sm text-on-surface-variant font-normal">MXN</span></span></div>
<button type="button" class="w-full py-space-sm bg-primary text-on-primary rounded-lg font-label-rotulo text-label-rotulo uppercase shadow-[2px_2px_0px_#201b0f] flex items-center justify-center gap-1 hover:brightness-110 transition-all"><span>Ver paquete</span><span class="material-symbols-outlined text-[18px]">arrow_forward</span></button>
</div>
</div>
</div>

<!-- PAQUETE PACHANGA -->
<div class="package-card flex flex-col bg-surface-container-lowest rounded-xl shadow-xl overflow-hidden relative group hover:-translate-y-1 transition-transform">
<div class="bg-secondary text-on-secondary py-1 text-center font-label-rotulo text-label-sm uppercase tracking-wider">⭐ Más pedido para fiesta ⭐</div>
<div class="relative h-48 w-full bg-surface-container overflow-hidden">
<img class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" alt="Carrito de frutas y dulces mexicanos para fiestas" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDXHBNnBnUaITFgFWZdrxEj8LboZEQ-CY-SRuGc8G9jNsersYfWGce_iACL3G0mOELgwPjtK4NuB9oKEp5z-bDCP7WEhZ2YzwdkcwOD8Hk9kOInfOhkny3hQf3W2fCxeZomMy24DaOBuaaBxbBJ--hId2C-zfdNIsdXHCX8CNyGf4lNZqfao7VNTGen5vi4gTXdzkivAhYwXgSoucOpGwvk-S-tCjHpp5THOUVFNaCzZJmZ_PbQLz6i"/>
<div class="absolute top-3 left-3 bg-secondary-container text-on-secondary-container px-space-sm py-1 rounded-full font-label-rotulo text-label-sm uppercase shadow-sm">El Consentido</div>
</div>
<div class="p-space-lg flex-1 flex flex-col justify-between">
<div>
<h3 class="font-headline-sm text-headline-sm text-on-surface mb-1">Paquete Pachanga</h3>
<p class="font-body-sm text-body-sm text-on-surface-variant mb-space-md">Fruta fresca surtida, carretilla botanera, fuentes de chamoy casero, churritos y gomitas enchiladas al gusto.</p>
<div class="space-y-1.5 pb-space-md text-body-sm text-on-surface">
<div class="flex items-center gap-2"><span class="material-symbols-outlined text-primary text-[18px]">group</span><span>Hasta <strong>50 personas</strong></span></div>
<div class="flex items-center gap-2"><span class="material-symbols-outlined text-tertiary text-[18px]">schedule</span><span>Duración: <strong>3 horas de servicio</strong></span></div>
</div>
</div>
<div class="pt-space-md bg-surface-container-low -mx-space-lg -mb-space-lg p-space-lg">
<div class="flex items-baseline justify-between mb-space-sm"><span class="font-label-sm text-label-sm text-on-surface-variant uppercase">Inversión base</span><span class="font-headline-sm text-headline-sm text-primary font-bold">Desde $3,500 <span class="font-body-sm text-body-sm text-on-surface-variant font-normal">MXN</span></span></div>
<button type="button" class="w-full py-space-sm bg-primary text-on-primary rounded-lg font-label-rotulo text-label-rotulo uppercase shadow-[2px_2px_0px_#201b0f] flex items-center justify-center gap-1 hover:brightness-110 transition-all"><span>Ver paquete</span><span class="material-symbols-outlined text-[18px]">arrow_forward</span></button>
</div>
</div>
</div>

<!-- PAQUETE REY DE LA FIESTA -->
<div class="package-card flex flex-col bg-surface-container-lowest rounded-xl shadow-md overflow-hidden group hover:-translate-y-1 transition-transform">
<div class="relative h-48 w-full bg-surface-container overflow-hidden">
<img alt="Mesa de frutas preparada para celebración" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" src="https://lh3.googleusercontent.com/aida/AEtjO1W_j9XLbR3lnCS5FJBYXHSDPuBg6_kZeZI6rvMtdt2Vzkh0H4onEhwmQLktCfUvrGUOq_wXNMGbA_UYWgSTOqobVT3bZvHsLCe_C2X7sIALvO7SrdYcivrDau_5m_mkQO6PJz-jtEkbz_NldY_V2rwltZuhUG9-Zr0845vHffNjf3KncA3qPmycpOx3X_6kd2bRj5PNzqCqQteNuaAmmDk7tihTLw5X3SI0zAlrdiGRj5IPQV4K2ACJB3E"/>
</div>
<div class="p-space-lg flex-1 flex flex-col justify-between">
<div>
<h3 class="font-headline-sm text-headline-sm text-on-surface mb-1">Paquete Rey de la Fiesta</h3>
<p class="font-body-sm text-body-sm text-on-surface-variant mb-space-md">Una experiencia completa con una gran variedad de frutas, botanas, dulces y complementos para consentir a tus invitados.</p>
<div class="space-y-1.5 pb-space-md text-body-sm text-on-surface">
<div class="flex items-center gap-2"><span class="material-symbols-outlined text-primary text-[18px]">group</span><span>Hasta <strong>100 personas</strong></span></div>
<div class="flex items-center gap-2"><span class="material-symbols-outlined text-tertiary text-[18px]">schedule</span><span>Duración: <strong>4 horas de servicio</strong></span></div>
</div>
</div>
<div class="pt-space-md bg-surface-container-low -mx-space-lg -mb-space-lg p-space-lg">
<div class="flex items-baseline justify-between mb-space-sm"><span class="font-label-sm text-label-sm text-on-surface-variant uppercase">Inversión base</span><span class="font-headline-sm text-headline-sm text-primary font-bold">Desde $6,500 <span class="font-body-sm text-body-sm text-on-surface-variant font-normal">MXN</span></span></div>
<button type="button" class="w-full py-space-sm bg-primary text-on-primary rounded-lg font-label-rotulo text-label-rotulo uppercase shadow-[2px_2px_0px_#201b0f] flex items-center justify-center gap-1 hover:brightness-110 transition-all"><span>Ver paquete</span><span class="material-symbols-outlined text-[18px]">arrow_forward</span></button>
</div>
</div>
</div>

<!-- PAQUETE PERSONALIZADO -->
<div class="package-card flex flex-col bg-surface-container-lowest rounded-xl shadow-md overflow-hidden group hover:-translate-y-1 transition-transform">
<div class="relative h-48 w-full bg-surface-container overflow-hidden">
<img alt="Botanas y dulces para eventos" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" src="https://lh3.googleusercontent.com/aida/AEtjO1W_j9XLbR3lnCS5FJBYXHSDPuBg6_kZeZI6rvMtdt2Vzkh0H4onEhwmQLktCfUvrGUOq_wXNMGbA_UYWgSTOqobVT3bZvHsLCe_C2X7sIALvO7SrdYcivrDau_5m_mkQO6PJz-jtEkbz_NldY_V2rwltZuhUG9-Zr0845vHffNjf3KncA3qPmycpOx3X_6kd2bRj5PNzqCqQteNuaAmmDk7tihTLw5X3SI0zAlrdiGRj5IPQV4K2ACJB3E"/>
</div>
<div class="p-space-lg flex-1 flex flex-col justify-between">
<div>
<h3 class="font-headline-sm text-headline-sm text-on-surface mb-1">Paquete A tu Medida</h3>
<p class="font-body-sm text-body-sm text-on-surface-variant mb-space-md">¿Tienes una idea especial? Armamos una propuesta personalizada según tu celebración, invitados y antojos favoritos.</p>
<div class="space-y-1.5 pb-space-md text-body-sm text-on-surface">
<div class="flex items-center gap-2"><span class="material-symbols-outlined text-primary text-[18px]">group</span><span>Para el número de invitados que necesites</span></div>
<div class="flex items-center gap-2"><span class="material-symbols-outlined text-tertiary text-[18px]">schedule</span><span>Servicio personalizado</span></div>
</div>
</div>
<div class="pt-space-md bg-surface-container-low -mx-space-lg -mb-space-lg p-space-lg">
<div class="flex items-baseline justify-between mb-space-sm"><span class="font-label-sm text-label-sm text-on-surface-variant uppercase">Cotización</span><span class="font-headline-sm text-headline-sm text-primary font-bold">A medida</span></div>
<a href="https://wa.me/?text=Hola%20Fruta%20Madre,%20quiero%20un%20paquete%20a%20la%20medida" target="_blank" rel="noopener noreferrer" class="w-full py-space-sm bg-primary text-on-primary rounded-lg font-label-rotulo text-label-rotulo uppercase shadow-[2px_2px_0px_#201b0f] flex items-center justify-center gap-1"><span>Cotizar paquete</span><span class="material-symbols-outlined text-[18px]">arrow_forward</span></a>
</div>
</div>
</div>

</div>
</section>

<!-- COMPARATIVA DE PAQUETES -->
<section class="w-full px-gutter lg:px-margin-desktop py-space-lg">
  <div class="bg-surface-container-low rounded-xl p-space-lg lg:p-space-xl shadow-md">

    <div class="mb-space-lg">
      <span class="inline-flex items-center gap-1 bg-surface-container px-space-md py-1 rounded-full font-label-rotulo text-label-sm uppercase">
        ⚖️ Fácil y sin vueltas
      </span>
      <h2 class="font-headline-lg text-headline-lg text-on-surface mt-space-sm">
        ¿Cuál te queda?
      </h2>
      <p class="font-body-sm text-body-sm text-on-surface-variant">
        La comparación para encontrar el paquete ideal para tu evento.
      </p>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse min-w-[760px]">
        <thead>
          <tr class="bg-surface-container rounded-lg font-label-rotulo text-label-sm uppercase">
            <th class="p-space-md rounded-l-lg">Paquete</th>
            <th class="p-space-md">Personas</th>
            <th class="p-space-md">Duración</th>
            <th class="p-space-md">Precio base</th>
            <th class="p-space-md">Lo que incluye</th>
            <th class="p-space-md rounded-r-lg">Acción</th>
          </tr>
        </thead>

        <tbody class="font-body-sm text-body-sm">

          <tr class="border-b border-surface-container">
            <td class="p-space-md font-bold">Paquete Chido</td>
            <td class="p-space-md">Hasta 25 personas</td>
            <td class="p-space-md">2 horas</td>
            <td class="p-space-md font-bold text-primary"> $1,850 MXN</td>
            <td class="p-space-md">Fruta de temporada, vasos, limón, Tajín y chamoy casero.</td>
            <td class="p-space-md">
              <a href="https://wa.me/?text=Hola%20Fruta%20Madre,%20quiero%20elegir%20el%20Paquete%20Chido"
                 target="_blank" rel="noopener noreferrer"
                 class="inline-block bg-surface-container px-space-md py-space-xs rounded-lg font-label-rotulo text-label-sm uppercase hover:bg-primary hover:text-white transition-colors">
                Elegir
              </a>
            </td>
          </tr>

          <tr class="bg-secondary-fixed/40 border-b border-surface-container">
            <td class="p-space-md font-bold text-secondary">
              Paquete Pachanga
              <span class="block text-label-sm uppercase mt-1">⭐ Top</span>
            </td>
            <td class="p-space-md font-bold">Hasta 50 personas</td>
            <td class="p-space-md">3 horas</td>
            <td class="p-space-md font-bold text-secondary">$3,450 MXN</td>
            <td class="p-space-md">Fruta surtida, carretilla de botanas fritas, gomitas con chamoy y 2 fuentes de salsa líquida.</td>
            <td class="p-space-md">
              <a href="https://wa.me/?text=Hola%20Fruta%20Madre,%20quiero%20elegir%20el%20Paquete%20Pachanga"
                 target="_blank" rel="noopener noreferrer"
                 class="inline-block bg-secondary text-white px-space-md py-space-xs rounded-lg font-label-rotulo text-label-sm uppercase hover:brightness-110 transition-colors">
                Elegir
              </a>
            </td>
          </tr>

          <tr class="border-b border-surface-container">
            <td class="p-space-md font-bold">Paquete Madre</td>
            <td class="p-space-md">Hasta 80 personas</td>
            <td class="p-space-md">4 horas</td>
            <td class="p-space-md font-bold text-primary">$5,200 MXN</td>
            <td class="p-space-md">Mangos locos completos, chicharrones preparados en vivo, dulces tradicionales y salsas premium.</td>
            <td class="p-space-md">
              <a href="https://wa.me/?text=Hola%20Fruta%20Madre,%20quiero%20elegir%20el%20Paquete%20Madre"
                 target="_blank" rel="noopener noreferrer"
                 class="inline-block bg-surface-container px-space-md py-space-xs rounded-lg font-label-rotulo text-label-sm uppercase hover:bg-primary hover:text-white transition-colors">
                Elegir
              </a>
            </td>
          </tr>

          <tr>
            <td class="p-space-md font-bold">Paquete A lo Grande</td>
            <td class="p-space-md">Hasta 150 personas</td>
            <td class="p-space-md">5 horas</td>
            <td class="p-space-md font-bold text-primary">$8,900 MXN</td>
            <td class="p-space-md">2 carritos completos, barra libre de toppings, frutas exóticas, repostería dulce y 3 personas de staff.</td>
            <td class="p-space-md">
              <a href="https://wa.me/?text=Hola%20Fruta%20Madre,%20quiero%20elegir%20el%20Paquete%20A%20lo%20Grande"
                 target="_blank" rel="noopener noreferrer"
                 class="inline-block bg-surface-container px-space-md py-space-xs rounded-lg font-label-rotulo text-label-sm uppercase hover:bg-primary hover:text-white transition-colors">
                Elegir
              </a>
            </td>
          </tr>

        </tbody>
      </table>
    </div>
  </div>
</section>

<!-- RENTA DE CARRITO -->
<section class="w-full px-gutter lg:px-margin-desktop py-space-lg">
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter-desktop items-center bg-surface-container-high rounded-xl p-space-lg lg:p-space-xl shadow-md">

    <div class="lg:col-span-7 flex flex-col gap-space-md">
      <span class="inline-flex items-center gap-1 bg-primary-container text-white px-space-md py-1 rounded-full font-label-rotulo text-label-sm uppercase w-fit">
        🛒 Renta de carrito solo
      </span>

      <h2 class="font-headline-lg text-headline-lg text-on-surface">
        Y también llevamos
        <span class="text-tertiary block underline decoration-secondary">el carrito.</span>
      </h2>

      <p class="font-body-md text-body-md text-on-surface-variant max-w-xl">
        Si prefieres armarlo a tu manera, puedes contratar únicamente nuestro carrito de snacks tradicional con personal de atención y llevar tus propios insumos o elegir menú a la carta.
      </p>

      <div class="grid grid-cols-2 sm:grid-cols-4 gap-space-xs">
        <div class="bg-surface-container-lowest rounded-lg p-space-sm">
          <p class="font-label-rotulo text-label-sm uppercase">Servicio</p>
          <p class="font-body-sm text-body-sm">Carrito de madera rústico</p>
        </div>
        <div class="bg-surface-container-lowest rounded-lg p-space-sm">
          <p class="font-label-rotulo text-label-sm uppercase">Duración</p>
          <p class="font-body-sm text-body-sm">Desde 3 horas</p>
        </div>
        <div class="bg-surface-container-lowest rounded-lg p-space-sm">
          <p class="font-label-rotulo text-label-sm uppercase">Capacidad</p>
          <p class="font-body-sm text-body-sm">30 a 200 personas</p>
        </div>
        <div class="bg-surface-container-lowest rounded-lg p-space-sm">
          <p class="font-label-rotulo text-label-sm uppercase">Inversión</p>
          <p class="font-body-sm text-body-sm font-bold text-primary">Desde $1,200 MXN</p>
        </div>
      </div>

      <a href="https://wa.me/?text=Hola%20Fruta%20Madre,%20quiero%20cotizar%20la%20renta%20de%20un%20carrito"
         target="_blank" rel="noopener noreferrer"
         class="inline-flex items-center justify-center gap-2 bg-tertiary-container text-white px-space-lg py-space-md rounded-lg font-label-rotulo text-label-rotulo uppercase shadow-[3px_3px_0px_#201b0f] hover:brightness-110 transition-all w-fit">
        Armar mi servicio personalizado
        <span class="material-symbols-outlined">arrow_forward</span>
      </a>
    </div>

    <div class="lg:col-span-5">
      <div class="relative rounded-xl overflow-hidden shadow-lg">
        <img
          src="https://lh3.googleusercontent.com/aida-public/AB6AXuDXHBNnBnUaITFgFWZdrxEj8LboZEQ-CY-SRuGc8G9jNsersYfWGce_iACL3G0mOELgwPjtK4NuB9oKEp5z-bDCP7WEhZ2YzwdkcwOD8Hk9kOInfOhkny3hQf3W2fCxeZomMy24DaOBuaaBxbBJ--hId2C-zfdNIsdXHCX8CNyGf4lNZqfao7VNTGen5vi4gTXdzkivAhYwXgSoucOpGwvk-S-tCjHpp5THOUVFNaCzZJmZ_PbQLz6i"
          alt="Carrito de frutas y botanas para eventos"
          class="w-full h-[280px] md:h-[320px] object-cover"
        >
        <div class="absolute bottom-0 left-0 right-0 bg-surface-container-lowest/95 p-space-sm flex justify-between gap-2">
          <span class="font-label-rotulo text-label-sm uppercase">Carrito auténtico Fruta Madre</span>
          <span class="font-label-rotulo text-label-sm text-tertiary">Rotulado a mano</span>
        </div>
      </div>
    </div>

  </div>
</section>

<!-- SERVICIOS ADICIONALES -->
<section class="w-full px-gutter lg:px-margin-desktop py-space-lg">
<div class="mb-space-lg">
<div class="inline-flex items-center gap-1 bg-tertiary-fixed text-on-tertiary-fixed px-space-sm py-1 rounded-full font-label-rotulo text-label-sm uppercase mb-space-xs"><span>✨</span><span>Complementos</span></div>
<h2 class="font-headline-lg text-headline-lg text-on-surface">Ponle más sabor a tu evento</h2>
<p class="font-body-md text-body-md text-on-surface-variant max-w-2xl">Agrega complementos a tu paquete para que tus invitados disfruten una experiencia todavía más completa.</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-gutter-desktop">
<div class="bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden group">
<div class="h-48 overflow-hidden"><img class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" alt="Fruta fresca preparada" src="https://lh3.googleusercontent.com/aida/AEtjO1X6XVOXLq3QtJEnDEoCDZb-q8NKPnOo_bXZqWN0Ylnj0w1URQ5pCr9Kiyin258RS4D4UJRzz2FkQITU6VdpYUGep0Bs7JkNi1-oF0ruarQPJZzgd-rmPo47X5wHvymSD-jEVq_Ivdq03cp0-s6IiRNtb1pcOKrxm8zgu_olMBtCG3MdHWxN_CnzmIpBmhrLjmo_k6hu0jHAiEEVtFMyjXDi9X5irB5hd9GvBdibZThSP-HDCiSQ835giQ"/></div>
<div class="p-space-lg"><h3 class="font-headline-sm text-headline-sm mb-space-xs">Barra de fruta fresca</h3><p class="font-body-sm text-body-sm text-on-surface-variant">Fruta de temporada con limón, chilito y salsas para preparar al gusto.</p></div>
</div>

<div class="bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden group">
<div class="h-48 overflow-hidden"><img class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" alt="Dulces enchilados" src="https://lh3.googleusercontent.com/aida/AEtjO1W_j9XLbR3lnCS5FJBYXHSDPuBg6_kZeZI6rvMtdt2Vzkh0H4onEhwmQLktCfUvrGUOq_wXNMGbA_UYWgSTOqobVT3bZvHsLCe_C2X7sIALvO7SrdYcivrDau_5m_mkQO6PJz-jtEkbz_NldY_V2rwltZuhUG9-Zr0845vHffNjf3KncA3qPmycpOx3X_6kd2bRj5PNzqCqQteNuaAmmDk7tihTLw5X3SI0zAlrdiGRj5IPQV4K2ACJB3E"/></div>
<div class="p-space-lg"><h3 class="font-headline-sm text-headline-sm mb-space-xs">Mesa botanera</h3><p class="font-body-sm text-body-sm text-on-surface-variant">Gomitas, pulpas, churritos y dulces para complementar tu celebración.</p></div>
</div>

<div class="bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden group">
<div class="h-48 overflow-hidden"><img class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" alt="Barra de chamoy y salsas" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDXHBNnBnUaITFgFWZdrxEj8LboZEQ-CY-SRuGc8G9jNsersYfWGce_iACL3G0mOELgwPjtK4NuB9oKEp5z-bDCP7WEhZ2YzwdkcwOD8Hk9kOInfOhkny3hQf3W2fCxeZomMy24DaOBuaaBxbBJ--hId2C-zfdNIsdXHCX8CNyGf4lNZqfao7VNTGen5vi4gTXdzkivAhYwXgSoucOpGwvk-S-tCjHpp5THOUVFNaCzZJmZ_PbQLz6i"/></div>
<div class="p-space-lg"><h3 class="font-headline-sm text-headline-sm mb-space-xs">Estación de chamoy</h3><p class="font-body-sm text-body-sm text-on-surface-variant">Salsas, chamoy y complementos para que cada invitado arme su antojo.</p></div>
</div>
</div>
</section>

<!-- GALERÍA -->
<section class="w-full px-gutter lg:px-margin-desktop py-space-lg">
<div class="mb-space-lg">
<div class="inline-flex items-center gap-1 bg-secondary-fixed text-on-secondary-fixed px-space-sm py-1 rounded-full font-label-rotulo text-label-sm uppercase mb-space-xs"><span>📸</span><span>Así se arma la pachanga</span></div>
<h2 class="font-headline-lg text-headline-lg text-on-surface">Antojos que se antojan</h2>
<p class="font-body-md text-body-md text-on-surface-variant">Una probadita de lo que podemos llevar a tu celebración.</p>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-space-md">
<div class="relative rounded-xl overflow-hidden h-48 md:h-64 group">
<img alt="Fruta preparada con chile" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" src="https://lh3.googleusercontent.com/aida/AEtjO1X6XVOXLq3QtJEnDEoCDZb-q8NKPnOo_bXZqWN0Ylnj0w1URQ5pCr9Kiyin258RS4D4UJRzz2FkQITU6VdpYUGep0Bs7JkNi1-oF0ruarQPJZzgd-rmPo47X5wHvymSD-jEVq_Ivdq03cp0-s6IiRNtb1pcOKrxm8zgu_olMBtCG3MdHWxN_CnzmIpBmhrLjmo_k6hu0jHAiEEVtFMyjXDi9X5irB5hd9GvBdibZThSP-HDCiSQ835giQ"/>
<div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-90"></div>
<div class="absolute bottom-3 left-3 right-3 text-white"><span class="font-label-rotulo text-label-sm uppercase block text-primary-fixed">Fruta fresca</span><span class="font-body-sm text-body-sm font-medium">Con chilito y limón</span></div>
</div>

<div class="relative rounded-xl overflow-hidden h-48 md:h-64 group">
<img alt="Dulces y gomitas para fiestas" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" src="https://lh3.googleusercontent.com/aida/AEtjO1W_j9XLbR3lnCS5FJBYXHSDPuBg6_kZeZI6rvMtdt2Vzkh0H4onEhwmQLktCfUvrGUOq_wXNMGbA_UYWgSTOqobVT3bZvHsLCe_C2X7sIALvO7SrdYcivrDau_5m_mkQO6PJz-jtEkbz_NldY_V2rwltZuhUG9-Zr0845vHffNjf3KncA3qPmycpOx3X_6kd2bRj5PNzqCqQteNuaAmmDk7tihTLw5X3SI0zAlrdiGRj5IPQV4K2ACJB3E"/>
<div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-90"></div>
<div class="absolute bottom-3 left-3 right-3 text-white"><span class="font-label-rotulo text-label-sm uppercase block text-primary-fixed">Dulces enchilados</span><span class="font-body-sm text-body-sm font-medium">Para compartir</span></div>
</div>

<div class="relative rounded-xl overflow-hidden h-48 md:h-64 group">
<img alt="Puesto de botanas" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDXHBNnBnUaITFgFWZdrxEj8LboZEQ-CY-SRuGc8G9jNsersYfWGce_iACL3G0mOELgwPjtK4NuB9oKEp5z-bDCP7WEhZ2YzwdkcwOD8Hk9kOInfOhkny3hQf3W2fCxeZomMy24DaOBuaaBxbBJ--hId2C-zfdNIsdXHCX8CNyGf4lNZqfao7VNTGen5vi4gTXdzkivAhYwXgSoucOpGwvk-S-tCjHpp5THOUVFNaCzZJmZ_PbQLz6i"/>
<div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-90"></div>
<div class="absolute bottom-3 left-3 right-3 text-white"><span class="font-label-rotulo text-label-sm uppercase block text-primary-fixed">Puesto botanero</span><span class="font-body-sm text-body-sm font-medium">El centro de la pachanga</span></div>
</div>

<div class="relative rounded-xl overflow-hidden h-48 md:h-64 group">
<img alt="Cajas de gomitas y dulces enchilados" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" src="https://lh3.googleusercontent.com/aida/AEtjO1W_j9XLbR3lnCS5FJBYXHSDPuBg6_kZeZI6rvMtdt2Vzkh0H4onEhwmQLktCfUvrGUOq_wXNMGbA_UYWgSTOqobVT3bZvHsLCe_C2X7sIALvO7SrdYcivrDau_5m_mkQO6PJz-jtEkbz_NldY_V2rwltZuhUG9-Zr0845vHffNjf3KncA3qPmycpOx3X_6kd2bRj5PNzqCqQteNuaAmmDk7tihTLw5X3SI0zAlrdiGRj5IPQV4K2ACJB3E"/>
<div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-90"></div>
<div class="absolute bottom-3 left-3 right-3 text-white"><span class="font-label-rotulo text-label-sm uppercase block text-primary-fixed">Mesa botanera</span><span class="font-body-sm text-body-sm font-medium">Gomitas y pulpas para llevar</span></div>
</div>
</div>
</section>

<!-- CTA FINAL -->
<section class="w-full px-gutter lg:px-margin-desktop pb-space-xl">
<div class="bg-surface-container-lowest rounded-xl p-space-lg lg:p-space-xl shadow-lg border-2 border-primary/20 text-center flex flex-col items-center justify-center gap-space-md relative overflow-hidden">
<div class="absolute -top-12 -left-12 w-40 h-40 bg-primary-fixed/30 rounded-full blur-2xl pointer-events-none"></div>
<div class="absolute -bottom-12 -right-12 w-40 h-40 bg-secondary-fixed/40 rounded-full blur-2xl pointer-events-none"></div>
<div class="inline-flex items-center gap-1 bg-surface-container px-space-md py-1 rounded-full text-label-rotulo text-label-sm uppercase text-secondary"><span>🍉</span><span>¡Arma tu cotización en 2 minutos!</span></div>
<h2 class="font-headline-lg text-headline-lg text-on-surface max-w-xl">¿Ya sabes qué quieres?</h2>
<p class="font-body-lg text-body-lg text-on-surface-variant max-w-lg">Elige un paquete prediseñado o platícanos qué te imaginas y te lo montamos donde sea.</p>
<div class="flex flex-wrap items-center justify-center gap-space-md pt-space-xs">
<a class="inline-flex items-center gap-2 bg-primary-container text-on-primary font-label-rotulo text-label-rotulo uppercase px-space-xl py-space-md rounded-lg shadow-[3px_3px_0px_#201b0f] hover:translate-x-0.5 hover:translate-y-0.5 transition-all" href="#paquetes"><span>Elegir paquete</span><span class="material-symbols-outlined text-[20px]">check_circle</span></a>
<a class="inline-flex items-center gap-2 bg-tertiary-container text-on-tertiary-container font-label-rotulo text-label-rotulo uppercase px-space-xl py-space-md rounded-lg shadow-[3px_3px_0px_#201b0f] hover:translate-x-0.5 hover:translate-y-0.5 transition-all" href="https://wa.me/?text=Hola%20FRUTA%20MADRE,%20quiero%20armar%20un%20evento%20a%20la%20medida" rel="noopener noreferrer" target="_blank"><span>Armar mi evento a la medida</span><span class="material-symbols-outlined text-[20px]">edit</span></a>
</div>
<p class="font-body-sm text-body-sm text-on-surface-variant pt-space-xs">Atención directa por WhatsApp y cotización formal sin compromiso. 🇲🇽</p>
</div>
</section>

<script>
(function initEventFilters() {
 const filterBtns = document.querySelectorAll('.filter-btn');
 const packagesGrid = document.getElementById('packagesGrid');
 const emptyState = document.getElementById('simulatedEmptyState');
 const toggleEmptyBtn = document.getElementById('toggleEmptyStateBtn');
 const toggleBtnLabel = document.getElementById('toggleBtnLabel');
 const resetFiltersBtn = document.getElementById('resetFiltersBtn');

 let isSimulatedEmpty = false;

 filterBtns.forEach(btn => {
  btn.addEventListener('click', () => {
   filterBtns.forEach(b => {
    b.classList.remove('bg-primary', 'text-on-primary', 'shadow-[2px_2px_0px_#201b0f]');
    b.classList.add('bg-surface-container', 'text-on-surface');
   });
   btn.classList.remove('bg-surface-container', 'text-on-surface');
   btn.classList.add('bg-primary', 'text-on-primary', 'shadow-[2px_2px_0px_#201b0f]');
   if (isSimulatedEmpty) toggleEmptyState(false);
  });
 });

 function toggleEmptyState(forceValue) {
  isSimulatedEmpty = typeof forceValue === 'boolean' ? forceValue : !isSimulatedEmpty;
  if (isSimulatedEmpty) {
   packagesGrid.classList.add('hidden');
   emptyState.classList.remove('hidden');
   toggleBtnLabel.textContent = 'Ver paquetes reales';
  } else {
   packagesGrid.classList.remove('hidden');
   emptyState.classList.add('hidden');
   toggleBtnLabel.textContent = 'Simular sin paquetes';
  }
 }

 if (toggleEmptyBtn) {
  toggleEmptyBtn.addEventListener('click', () => toggleEmptyState());
 }

 if (resetFiltersBtn) {
  resetFiltersBtn.addEventListener('click', () => {
   toggleEmptyState(false);
   if (filterBtns[0]) filterBtns[0].click();
  });
 }
})();
</script>

</div>
</main>

<footer class="w-full bg-surface-container-low text-on-surface">
<div class="h-1.5 w-full flex">
<div class="flex-1 bg-tertiary-container"></div>
<div class="flex-1 bg-surface-container-high"></div>
<div class="flex-1 bg-secondary-container"></div>
<div class="flex-1 bg-primary-container"></div>
</div>

<div class="w-full px-gutter lg:px-margin-desktop py-space-xl grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-gutter-desktop">
<div class="lg:col-span-2 flex flex-col gap-space-md">
<div class="flex items-center gap-space-sm"><span class="font-headline-sm text-headline-sm uppercase text-primary font-bold">FRUTA MADRE</span></div>
<p class="font-label-rotulo text-label-rotulo text-secondary tracking-wide uppercase">Te damos hasta para llevar.</p>
<p class="font-body-md text-body-md text-on-surface-variant max-w-md">El auténtico sabor del mercado urbano mexicano en tu puerta: fruta fresca con chilito y limón, botanas preparadas, repostería artesanal, flores de temporada y carritos de antojos para tus mejores eventos.</p>
<div class="inline-flex items-center gap-space-xs bg-surface-container px-space-md py-space-xs rounded-full w-fit"><span class="font-label-sm text-label-sm text-tertiary uppercase tracking-wider font-bold">🇲🇽 Hecho con mucho chamoy y amor en México</span></div>
</div>

<div class="flex flex-col gap-space-sm">
<h3 class="font-label-rotulo text-label-rotulo uppercase text-on-surface">Explorar</h3>
<ul class="flex flex-col gap-space-xs font-body-sm text-body-sm text-on-surface-variant">
<li><a href="index.php" class="hover:text-on-surface transition-colors">Inicio</a></li>
<li><a href="index.php?accion=catalogo" class="hover:text-on-surface transition-colors">Catálogo Completo</a></li>
<li><a href="index.php?accion=eventos" class="hover:text-on-surface transition-colors">Carritos de Eventos</a></li>
<li><a href="index.php?accion=mis-pedidos" class="hover:text-on-surface transition-colors">Mis Pedidos y Rastreo</a></li>
</ul>
</div>

<div class="flex flex-col gap-space-sm">
<h3 class="font-label-rotulo text-label-rotulo uppercase text-on-surface">Atención</h3>
<ul class="flex flex-col gap-space-xs font-body-sm text-body-sm text-on-surface-variant">
<li class="hover:text-on-surface transition-colors cursor-pointer">Contacto Directo</li>
<li class="hover:text-on-surface transition-colors cursor-pointer">Preguntas Frecuentes</li>
<li class="hover:text-on-surface transition-colors cursor-pointer">Términos y Condiciones</li>
<li class="hover:text-on-surface transition-colors cursor-pointer">Aviso de Privacidad</li>
</ul>
</div>

<div class="flex flex-col gap-space-sm">
<h3 class="font-label-rotulo text-label-rotulo uppercase text-on-surface">Mercado Social</h3>
<p class="font-body-sm text-body-sm text-on-surface-variant">¡Échanos un grito o síguenos la pista!</p>
<div class="flex flex-wrap gap-space-xs pt-space-xs">
<span class="px-space-md py-space-xs bg-surface-container rounded-lg font-label-sm text-label-sm text-on-surface hover:bg-surface-container-high transition-colors cursor-pointer">Instagram</span>
<span class="px-space-md py-space-xs bg-surface-container rounded-lg font-label-sm text-label-sm text-on-surface hover:bg-surface-container-high transition-colors cursor-pointer">TikTok</span>
<span class="px-space-md py-space-xs bg-surface-container rounded-lg font-label-sm text-label-sm text-on-surface hover:bg-surface-container-high transition-colors cursor-pointer">WhatsApp</span>
<span class="px-space-md py-space-xs bg-surface-container rounded-lg font-label-sm text-label-sm text-on-surface hover:bg-surface-container-high transition-colors cursor-pointer">Facebook</span>
</div>
</div>
</div>

<div class="w-full px-gutter lg:px-margin-desktop py-space-md bg-surface-container flex flex-col sm:flex-row items-center justify-between gap-space-sm">
<p class="font-body-sm text-body-sm text-on-surface-variant">© 2025 FRUTA MADRE. Todos los derechos reservados.</p>
<p class="font-label-rotulo text-label-sm text-on-surface-variant uppercase">Puesto Digital No. 01</p>
</div>
</footer>
</body>
</html>