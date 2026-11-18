
<?php
$productos = $productos ?? [];
$categorias = $categorias ?? [];
$indicadores = $indicadores ?? [
    'activos' => 0,
    'bajo_stock' => 0,
    'agotados' => 0,
    'inactivos' => 0
];

$filtros = $filtros ?? [];
$busqueda = $filtros['busqueda'] ?? '';
$categoriaSeleccionada = $filtros['categoria'] ?? '';
$estadoSeleccionado = $filtros['estado'] ?? '';
$ordenSeleccionado = $filtros['orden'] ?? 'recientes';

$productoEditar = $productoEditar ?? null;

function e($valor): string {
    return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

function estadoProducto(array $producto): array {
    if (empty($producto['activo'])) {
        return ['Inactivo', 'estado-inactivo'];
    }

    $stock = (int)($producto['stock_disponible'] ?? 0);
    $minimo = (int)($producto['stock_minimo'] ?? 0);

    if ($stock <= 0) {
        return ['Agotado', 'estado-agotado'];
    }

    if ($stock <= $minimo) {
        return ['Poco stock', 'estado-bajo'];
    }

    return ['Disponible', 'estado-disponible'];
}

$mensaje = $_SESSION['mensaje_producto'] ?? null;
unset($_SESSION['mensaje_producto']);
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Syne:wght@500;600;700;800&display=swap');

:root {
    --fm-fondo:#fff8f0;
    --fm-superficie:#ffffff;
    --fm-crema:#fdf2de;
    --fm-crema2:#f8edd8;
    --fm-verde:#477a45;
    --fm-rosa:#d94f70;
    --fm-naranja:#e97832;
    --fm-amarillo:#e8b93f;
    --fm-texto:#211d1a;
    --fm-secundario:#41493f;
    --fm-borde:#211d1a;
}

.fm-admin {
    color:var(--fm-texto);
    font-family:'DM Sans',sans-serif;
    width:100%;
}

.fm-admin * { box-sizing:border-box; }

.fm-admin h1,
.fm-admin h2,
.fm-admin h3,
.fm-admin .fm-label,
.fm-admin button,
.fm-admin th {
    font-family:'Syne',sans-serif;
}

.fm-admin .fm-encabezado {
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:20px;
    flex-wrap:wrap;
    padding-bottom:24px;
    border-bottom:2px dashed #211d1a33;
}

.fm-admin .fm-etiquetas {
    display:flex;
    gap:8px;
    flex-wrap:wrap;
    align-items:center;
}

.fm-admin .fm-etiqueta {
    display:inline-flex;
    align-items:center;
    gap:7px;
    padding:7px 12px;
    border:2px solid var(--fm-borde);
    border-radius:30px;
    font-size:11px;
    font-weight:800;
    letter-spacing:.06em;
    text-transform:uppercase;
}

.fm-admin .fm-etiqueta.rosa {
    color:white;
    background:var(--fm-rosa);
    box-shadow:2px 2px 0 var(--fm-borde);
}

.fm-admin .fm-etiqueta.crema {
    background:var(--fm-crema);
}

.fm-admin h1 {
    font-size:clamp(28px,3vw,42px);
    font-weight:800;
    line-height:1;
    text-transform:uppercase;
    letter-spacing:-1.5px;
    margin:12px 0 8px;
}

.fm-admin .fm-descripcion {
    color:var(--fm-secundario);
    max-width:750px;
    line-height:1.6;
    font-size:14px;
}

.fm-admin .fm-kpis {
    display:flex;
    flex-wrap:wrap;
    gap:9px;
    background:var(--fm-crema);
    border:2px solid var(--fm-borde);
    border-radius:16px;
    padding:10px;
    box-shadow:3px 3px 0 var(--fm-borde);
}

.fm-admin .fm-kpi {
    background:white;
    border:2px solid var(--fm-borde);
    border-radius:11px;
    min-width:105px;
    padding:10px 14px;
    text-align:center;
    box-shadow:2px 2px 0 var(--fm-borde);
}

.fm-admin .fm-kpi small {
    display:block;
    text-transform:uppercase;
    font-size:10px;
    font-weight:800;
    color:#72796e;
}

.fm-admin .fm-kpi strong {
    display:block;
    font-family:'Syne',sans-serif;
    font-size:25px;
    font-weight:800;
    color:var(--fm-verde);
}

.fm-admin .fm-kpi.bajo strong { color:var(--fm-naranja); }
.fm-admin .fm-kpi.agotado strong { color:var(--fm-rosa); }

.fm-admin .fm-tabs {
    display:flex;
    gap:9px;
    overflow-x:auto;
    padding:18px 2px;
}

.fm-admin .fm-tab {
    white-space:nowrap;
    border:2px solid var(--fm-borde);
    border-radius:11px;
    background:white;
    color:var(--fm-texto);
    padding:10px 15px;
    font-size:11px;
    font-weight:800;
    text-transform:uppercase;
    cursor:pointer;
    box-shadow:2px 2px 0 var(--fm-borde);
}

.fm-admin .fm-tab.activo {
    background:var(--fm-verde);
    color:white;
    box-shadow:3px 3px 0 var(--fm-borde);
}

.fm-admin .fm-filtros {
    display:flex;
    align-items:center;
    flex-wrap:wrap;
    gap:12px;
    padding:16px;
    background:var(--fm-crema);
    border:2px solid var(--fm-borde);
    border-radius:16px;
    box-shadow:3px 3px 0 var(--fm-borde);
    margin-bottom:20px;
}

.fm-admin .fm-control {
    min-height:42px;
    border:2px solid var(--fm-borde);
    border-radius:8px;
    background:white;
    color:var(--fm-texto);
    padding:9px 12px;
    font-family:'DM Sans',sans-serif;
    font-size:13px;
    outline:none;
}

.fm-admin .fm-control:focus {
    box-shadow:0 0 0 3px #477a4533;
}

.fm-admin .fm-busqueda {
    flex:1;
    min-width:210px;
}

.fm-admin .fm-boton {
    display:inline-flex;
    justify-content:center;
    align-items:center;
    gap:8px;
    min-height:40px;
    border:2px solid var(--fm-borde);
    border-radius:9px;
    padding:9px 14px;
    background:white;
    color:var(--fm-texto);
    font-size:11px;
    font-weight:800;
    text-transform:uppercase;
    cursor:pointer;
    box-shadow:2px 2px 0 var(--fm-borde);
    text-decoration:none;
}

.fm-admin .fm-boton:hover {
    transform:translateY(-1px);
}

.fm-admin .fm-boton.verde {
    color:white;
    background:var(--fm-verde);
}

.fm-admin .fm-boton.rosa {
    color:white;
    background:var(--fm-rosa);
}

.fm-admin .fm-tabla-contenedor {
    width:100%;
    overflow-x:auto;
    border:2px solid var(--fm-borde);
    border-radius:15px;
    background:white;
    box-shadow:3px 3px 0 var(--fm-borde);
}

.fm-admin table {
    width:100%;
    border-collapse:collapse;
    min-width:850px;
}

.fm-admin thead {
    background:var(--fm-crema);
}

.fm-admin th {
    padding:14px 12px;
    text-align:left;
    font-size:10px;
    font-weight:800;
    text-transform:uppercase;
    letter-spacing:.05em;
    border-bottom:2px solid var(--fm-borde);
}

.fm-admin td {
    padding:13px 12px;
    border-bottom:1px solid #211d1a20;
    font-size:12px;
    vertical-align:middle;
}

.fm-admin tbody tr:hover { background:#fdf8ef; }

.fm-admin .fm-producto {
    display:flex;
    align-items:center;
    gap:11px;
    min-width:220px;
}

.fm-admin .fm-imagen {
    width:48px;
    height:48px;
    border:2px solid var(--fm-borde);
    border-radius:10px;
    object-fit:cover;
    background:var(--fm-crema);
}

.fm-admin .fm-producto strong {
    display:block;
    font-size:12px;
    font-weight:800;
}

.fm-admin .fm-producto small {
    color:#72796e;
    font-size:10px;
}

.fm-admin .fm-estado {
    display:inline-block;
    padding:5px 9px;
    border:1px solid var(--fm-borde);
    border-radius:30px;
    font-size:10px;
    font-weight:800;
    white-space:nowrap;
}

.fm-admin .estado-disponible { background:#d9eed5; color:#245b27; }
.fm-admin .estado-bajo { background:#ffe5c7; color:#9a4b13; }
.fm-admin .estado-agotado { background:#ffd9de; color:#a21e43; }
.fm-admin .estado-inactivo { background:#ece8e1; color:#59544d; }

.fm-admin .fm-acciones {
    display:flex;
    align-items:center;
    gap:7px;
}

.fm-admin .fm-icono {
    width:34px;
    height:34px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    border:1.5px solid var(--fm-borde);
    border-radius:8px;
    background:white;
    color:var(--fm-texto);
    cursor:pointer;
    text-decoration:none;
    font-size:15px;
}

.fm-admin .fm-icono:hover { background:var(--fm-crema); }
.fm-admin .fm-icono.borrar { color:#b42340; }

.fm-admin .fm-paginacion {
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    flex-wrap:wrap;
    padding:15px 4px;
    color:var(--fm-secundario);
    font-size:12px;
}

.fm-admin .fm-mensaje {
    padding:12px 15px;
    margin-bottom:16px;
    border:2px solid var(--fm-borde);
    border-radius:10px;
    font-size:13px;
    font-weight:700;
}

.fm-admin .fm-mensaje.error { background:#ffd9de; }
.fm-admin .fm-mensaje.exito { background:#d9eed5; }

.fm-admin .fm-panel {
    margin-top:28px;
    padding-top:22px;
    border-top:2px dashed #211d1a33;
}

.fm-admin .fm-panel h2 {
    font-size:21px;
    text-transform:uppercase;
    margin:0 0 6px;
}

.fm-admin .fm-modulos {
    display:grid;
    grid-template-columns:repeat(4,minmax(0,1fr));
    gap:14px;
    margin-top:15px;
}

.fm-admin .fm-modulo {
    padding:16px;
    border:2px solid var(--fm-borde);
    border-radius:13px;
    background:var(--fm-crema);
    box-shadow:2px 2px 0 var(--fm-borde);
}

.fm-admin .fm-modulo strong {
    display:block;
    font-family:'Syne',sans-serif;
    font-size:13px;
    margin-bottom:7px;
}

.fm-admin .fm-modulo p {
    font-size:11px;
    line-height:1.5;
    color:var(--fm-secundario);
    min-height:65px;
}

.fm-admin .fm-modal-fondo {
    display:none;
    position:fixed;
    inset:0;
    z-index:1000;
    background:#211d1acc;
    padding:20px;
    overflow-y:auto;
    align-items:center;
    justify-content:center;
}

.fm-admin .fm-modal-fondo.abierto { display:flex; }

.fm-admin .fm-modal {
    width:min(720px,100%);
    max-height:calc(100vh - 40px);
    overflow-y:auto;
    background:var(--fm-fondo);
    border:2px solid var(--fm-borde);
    border-radius:17px;
    box-shadow:5px 5px 0 var(--fm-borde);
}

.fm-admin .fm-modal-cabecera {
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:18px 20px;
    background:var(--fm-crema);
    border-bottom:2px solid var(--fm-borde);
}

.fm-admin .fm-modal-cabecera h2 {
    margin:0;
    font-size:20px;
    text-transform:uppercase;
}

.fm-admin .fm-formulario {
    padding:20px;
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:15px;
}

.fm-admin .fm-campo {
    display:flex;
    flex-direction:column;
    gap:6px;
}

.fm-admin .fm-campo.completo { grid-column:1/-1; }

.fm-admin .fm-campo label {
    font-family:'Syne',sans-serif;
    font-size:11px;
    font-weight:800;
    text-transform:uppercase;
}

.fm-admin .fm-campo textarea {
    min-height:95px;
    resize:vertical;
}

.fm-admin .fm-modal-pie {
    display:flex;
    justify-content:flex-end;
    gap:10px;
    flex-wrap:wrap;
    padding:15px 20px;
    border-top:1px solid #211d1a33;
}

.fm-admin .fm-aviso {
    padding:12px;
    background:var(--fm-crema);
    border:1px solid var(--fm-borde);
    border-radius:9px;
    font-size:12px;
    line-height:1.5;
    grid-column:1/-1;
}

@media(max-width:1100px) {
    .fm-admin .fm-modulos { grid-template-columns:repeat(2,minmax(0,1fr)); }
}

@media(max-width:700px) {
    .fm-admin .fm-kpis { width:100%; }
    .fm-admin .fm-kpi { flex:1; min-width:85px; }
    .fm-admin .fm-formulario { grid-template-columns:1fr; }
    .fm-admin .fm-campo.completo { grid-column:auto; }
    .fm-admin .fm-modulos { grid-template-columns:1fr; }
}
</style>

<div class="fm-admin">

    <div class="fm-encabezado">
        <div>
            <div class="fm-etiquetas">
                <span class="fm-etiqueta rosa">⚙ PUESTO DIGITAL • CONTROL MATRIZ</span>
                <span class="fm-etiqueta crema">● Sincronización en vivo</span>
            </div>

            <h1>Gestión Administrativa</h1>

            <p class="fm-descripcion">
                Centro operativo maestro para catalogar, ajustar precios al vuelo,
                supervisar existencias en piso de venta y orquestar la oferta
                gastronómica de Fruta Madre.
            </p>
        </div>

        <div class="fm-kpis">
            <div class="fm-kpi">
                <small>Catálogo Activo</small>
                <strong><?= (int)$indicadores['activos'] ?></strong>
            </div>

            <div class="fm-kpi bajo">
                <small>Bajo Stock</small>
                <strong><?= (int)$indicadores['bajo_stock'] ?></strong>
            </div>

            <div class="fm-kpi agotado">
                <small>Agotados</small>
                <strong><?= (int)$indicadores['agotados'] ?></strong>
            </div>
        </div>
    </div>

    <nav class="fm-tabs">
        <a class="fm-tab activo" href="index.php?accion=admin-productos">🍉 Productos</a>
        <a class="fm-tab" href="index.php?accion=admin-categorias">🗂️ Categorías</a>
        <a class="fm-tab" href="index.php?accion=admin-paquetes">🎉 Paquetes</a>
        <a class="fm-tab" href="index.php?accion=admin-carritos">🛺 Carritos</a>
        <a class="fm-tab" href="index.php?accion=admin-pedidos">🛵 Pedidos</a>
        <a class="fm-tab" href="index.php?accion=admin-reservas">📅 Reservas</a>
        <a class="fm-tab" href="index.php?accion=admin-clientes">👥 Clientes</a>
        <a class="fm-tab" href="index.php?accion=admin-inventario">📦 Inventario</a>
        <a class="fm-tab" href="index.php?accion=admin-reportes">📈 Reportes</a>
    </nav>

    <?php if ($mensaje): ?>
        <div class="fm-mensaje <?= e($mensaje['tipo'] ?? 'exito') ?>">
            <?= e($mensaje['texto'] ?? '') ?>
        </div>
    <?php endif; ?>

    <form class="fm-filtros" method="GET" action="index.php">
        <input type="hidden" name="accion" value="admin-productos">

        <input
            class="fm-control fm-busqueda"
            type="search"
            name="busqueda"
            placeholder="Buscar producto por nombre, SKU o ingrediente..."
            value="<?= e($busqueda) ?>"
        >

        <select class="fm-control" name="categoria">
            <option value="">Todas las categorías</option>
            <?php foreach ($categorias as $categoria): ?>
                <option
                    value="<?= (int)$categoria['id_categoria'] ?>"
                    <?= (string)$categoriaSeleccionada === (string)$categoria['id_categoria'] ? 'selected' : '' ?>
                >
                    <?= e($categoria['nombre_categoria']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select class="fm-control" name="estado">
            <option value="">Todos los estados</option>
            <option value="disponible" <?= $estadoSeleccionado === 'disponible' ? 'selected' : '' ?>>🟢 Disponible</option>
            <option value="poco_stock" <?= $estadoSeleccionado === 'poco_stock' ? 'selected' : '' ?>>🟡 Poco stock</option>
            <option value="agotado" <?= $estadoSeleccionado === 'agotado' ? 'selected' : '' ?>>🔴 Agotado</option>
            <option value="inactivo" <?= $estadoSeleccionado === 'inactivo' ? 'selected' : '' ?>>⚪ Inactivo</option>
        </select>

        <select class="fm-control" name="orden">
            <option value="recientes" <?= $ordenSeleccionado === 'recientes' ? 'selected' : '' ?>>Más recientes</option>
            <option value="precio_menor" <?= $ordenSeleccionado === 'precio_menor' ? 'selected' : '' ?>>Precio: menor a mayor</option>
            <option value="precio_mayor" <?= $ordenSeleccionado === 'precio_mayor' ? 'selected' : '' ?>>Precio: mayor a menor</option>
            <option value="stock" <?= $ordenSeleccionado === 'stock' ? 'selected' : '' ?>>Stock en mostrador</option>
            <option value="margen" <?= $ordenSeleccionado === 'margen' ? 'selected' : '' ?>>Margen de ganancia</option>
        </select>

        <button class="fm-boton verde" type="submit">⌕ Filtrar</button>

        <a class="fm-boton" href="index.php?accion=admin-productos">Limpiar</a>

        <button class="fm-boton rosa" type="button" onclick="fmAbrirFormulario()">
            ＋ NUEVO PRODUCTO
        </button>
    </form>

    <div class="fm-tabla-contenedor">
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>SKU</th>
                    <th>Categoría</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($productos)): ?>
                    <tr>
                        <td colspan="7" style="text-align:center;padding:35px">
                            No hay productos que coincidan con los filtros seleccionados.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($productos as $producto): ?>
                        <?php [$estadoTexto, $estadoClase] = estadoProducto($producto); ?>

                        <tr>
                            <td>
                                <div class="fm-producto">
                                    <?php if (!empty($producto['imagen_url'])): ?>
                                        <img
                                            class="fm-imagen"
                                            src="<?= e($producto['imagen_url']) ?>"
                                            alt="<?= e($producto['nombre']) ?>"
                                        >
                                    <?php else: ?>
                                        <div class="fm-imagen" style="display:grid;place-items:center">🍉</div>
                                    <?php endif; ?>

                                    <div>
                                        <strong><?= e($producto['nombre']) ?></strong>
                                        <small><?= e($producto['subcategoria'] ?? '') ?></small>
                                    </div>
                                </div>
                            </td>

                            <td><?= e($producto['sku']) ?></td>
                            <td><?= e($producto['nombre_categoria'] ?? '') ?></td>
                            <td><strong>$<?= number_format((float)$producto['precio'], 2) ?></strong></td>
                            <td>
                                <?= (int)$producto['stock_disponible'] ?>
                                <small style="color:#72796e"> / mínimo <?= (int)$producto['stock_minimo'] ?></small>
                            </td>
                            <td>
                                <span class="fm-estado <?= e($estadoClase) ?>">
                                    <?= e($estadoTexto) ?>
                                </span>
                            </td>

                            <td>
                                <div class="fm-acciones">
                                    <a
                                        class="fm-icono"
                                        title="Editar producto"
                                        href="index.php?accion=admin-productos&editar=<?= (int)$producto['id_producto'] ?>"
                                    >✎</a>

                                    <button
                                        class="fm-icono borrar"
                                        type="button"
                                        title="Dar de baja"
                                        onclick="fmConfirmarBaja(<?= (int)$producto['id_producto'] ?>, '<?= e($producto['nombre']) ?>')"
                                    >⌫</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="fm-paginacion">
        <span>Mostrando <?= count($productos) ?> productos</span>
        <span>Los resultados corresponden a los filtros seleccionados.</span>
    </div>

    <section class="fm-panel">
        <h2>Arquitectura unificada</h2>
        <p class="fm-descripcion">
            Una sola plantilla maestra, cero fricción operativa.
            Adaptabilidad multimódulo Fruta Madre.
        </p>

        <div class="fm-modulos">
            <article class="fm-modulo">
                <strong>📦 Módulo Inventario</strong>
                <p>Materia prima y stock. Control de existencias, merma, costos y alertas de reabastecimiento.</p>
                <a class="fm-boton" href="index.php?accion=admin-inventario">Ver inventario →</a>
            </article>

            <article class="fm-modulo">
                <strong>🛵 Módulo Pedidos</strong>
                <p>Comandas y delivery. Consulta pedidos, pagos, repartidores y estados de entrega.</p>
                <a class="fm-boton" href="index.php?accion=admin-pedidos">Ver pedidos →</a>
            </article>

            <article class="fm-modulo">
                <strong>🛺 Módulo Reservas</strong>
                <p>Pachangas y fiestas. Consulta paquetes contratados, fechas, anticipos y saldos.</p>
                <a class="fm-boton" href="index.php?accion=admin-reservas">Ver reservas →</a>
            </article>

            <article class="fm-modulo">
                <strong>📈 Módulo Reportes</strong>
                <p>Métricas y ganancias. Consulta ventas, productos destacados y movimientos de caja.</p>
                <a class="fm-boton" href="index.php?accion=admin-reportes">Ver reportes →</a>
            </article>
        </div>
    </section>
</div>

<!-- FORMULARIO DE PRODUCTO -->
<div class="fm-admin fm-modal-fondo <?= $productoEditar ? 'abierto' : '' ?>" id="fmModalProducto">
    <div class="fm-modal">
        <div class="fm-modal-cabecera">
            <h2><?= $productoEditar ? 'Editar producto' : 'Nuevo producto' ?></h2>
            <button class="fm-icono" type="button" onclick="fmCerrarFormulario()">✕</button>
        </div>

        <form
            class="fm-formulario"
            method="POST"
            action="index.php?accion=<?= $productoEditar ? 'admin-guardar-producto' : 'admin-guardar-producto' ?>"
            enctype="multipart/form-data"
        >
            <input type="hidden" name="id_producto" value="<?= (int)($productoEditar['id_producto'] ?? 0) ?>">

            <div class="fm-campo">
                <label>Nombre del producto *</label>
                <input class="fm-control" type="text" name="nombre" required maxlength="150"
                       value="<?= e($productoEditar['nombre'] ?? '') ?>">
            </div>

            <div class="fm-campo">
                <label>SKU *</label>
                <input class="fm-control" type="text" name="sku" required maxlength="30"
                       value="<?= e($productoEditar['sku'] ?? '') ?>">
            </div>

            <div class="fm-campo">
                <label>Categoría *</label>
                <select class="fm-control" name="id_categoria" required>
                    <option value="">Seleccionar categoría</option>
                    <?php foreach ($categorias as $categoria): ?>
                        <option
                            value="<?= (int)$categoria['id_categoria'] ?>"
                            <?= (string)($productoEditar['id_categoria'] ?? '') === (string)$categoria['id_categoria'] ? 'selected' : '' ?>
                        >
                            <?= e($categoria['nombre_categoria']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="fm-campo">
                <label>Subcategoría</label>
                <input class="fm-control" type="text" name="subcategoria"
                       value="<?= e($productoEditar['subcategoria'] ?? '') ?>">
            </div>

            <div class="fm-campo">
                <label>Precio de venta ($ MXN) *</label>
                <input class="fm-control" type="number" name="precio" min="0" step="0.01" required
                       value="<?= e($productoEditar['precio'] ?? '') ?>">
            </div>

            <div class="fm-campo">
                <label>Costo unitario ($ MXN) *</label>
                <input class="fm-control" type="number" name="costo" min="0" step="0.01" required
                       value="<?= e($productoEditar['costo'] ?? '0') ?>">
            </div>

            <div class="fm-campo">
                <label>Stock en mostrador *</label>
                <input class="fm-control" type="number" name="stock_disponible" min="0" required
                       value="<?= e($productoEditar['stock_disponible'] ?? '0') ?>">
            </div>

            <div class="fm-campo">
                <label>Alerta mínimo *</label>
                <input class="fm-control" type="number" name="stock_minimo" min="0" required
                       value="<?= e($productoEditar['stock_minimo'] ?? '5') ?>">
            </div>

            <div class="fm-campo completo">
                <label>Fotografía oficial del producto</label>
                <input class="fm-control" type="file" name="imagen" accept="image/jpeg,image/png,image/webp">
                <?php if (!empty($productoEditar['imagen_url'])): ?>
                    <small>Imagen actual: <?= e($productoEditar['imagen_url']) ?></small>
                <?php endif; ?>
                <input type="hidden" name="imagen_url_actual" value="<?= e($productoEditar['imagen_url'] ?? '') ?>">
            </div>

            <div class="fm-campo completo">
                <label>Descripción e ingredientes</label>
                <textarea class="fm-control" name="descripcion"><?= e($productoEditar['descripcion'] ?? '') ?></textarea>
            </div>

            <div class="fm-campo completo">
                <label>Ingredientes</label>
                <textarea class="fm-control" name="ingredientes"><?= e($productoEditar['ingredientes'] ?? '') ?></textarea>
            </div>

            <div class="fm-campo completo">
                <label>Disponibilidad en catálogo</label>
                <select class="fm-control" name="activo">
                    <option value="1" <?= !isset($productoEditar['activo']) || $productoEditar['activo'] ? 'selected' : '' ?>>Disponible</option>
                    <option value="0" <?= isset($productoEditar['activo']) && !$productoEditar['activo'] ? 'selected' : '' ?>>Inactivo</option>
                </select>
            </div>

            <div class="fm-campo completo">
                <label style="display:flex;align-items:center;gap:10px">
                    <input type="checkbox" name="permite_paquetes" value="1"
                        <?= !empty($productoEditar['permite_paquetes']) ? 'checked' : '' ?>>
                    Apto para incluir en Carrito Botanero
                </label>
            </div>

            <div class="fm-aviso">
                Échale ojo antes de guardar. Los cambios de precio se reflejan
                en el catálogo una vez guardados.
            </div>

            <div class="fm-modal-pie fm-campo completo">
                <button class="fm-boton" type="button" onclick="fmCerrarFormulario()">Cancelar</button>
                <button class="fm-boton verde" type="submit">Guardar producto</button>
            </div>
        </form>
    </div>
</div>

<!-- CONFIRMACIÓN DE BAJA -->
<div class="fm-admin fm-modal-fondo" id="fmModalBaja">
    <div class="fm-modal" style="max-width:470px">
        <div class="fm-modal-cabecera">
            <h2>⚠ Confirmación operativa</h2>
            <button class="fm-icono" type="button" onclick="fmCerrarBaja()">✕</button>
        </div>

        <form method="POST" action="index.php?accion=admin-cambiar-estado-producto">
            <div style="padding:20px">
                <p style="font-weight:800">¿Seguro que quieres dar de baja este producto?</p>
                <p style="font-size:13px;line-height:1.6">
                    Estás a punto de dar de baja
                    <strong id="fmNombreBaja"></strong>.
                    El producto dejará de estar activo, pero se conservará en la base de datos.
                </p>
                <input type="hidden" name="id_producto" id="fmIdBaja">
                <input type="hidden" name="activo" value="0">
            </div>

            <div class="fm-modal-pie">
                <button class="fm-boton" type="button" onclick="fmCerrarBaja()">Cancelar</button>
                <button class="fm-boton rosa" type="submit">Sí, dar de baja</button>
            </div>
        </form>
    </div>
</div>

<script>
function fmAbrirFormulario() {
    document.getElementById('fmModalProducto').classList.add('abierto');
}

function fmCerrarFormulario() {
    document.getElementById('fmModalProducto').classList.remove('abierto');

    const url = new URL(window.location.href);
    if (url.searchParams.has('editar')) {
        url.searchParams.delete('editar');
        window.location.href = url.toString();
    }
}

function fmConfirmarBaja(id, nombre) {
    document.getElementById('fmIdBaja').value = id;
    document.getElementById('fmNombreBaja').textContent = '"' + nombre + '"';
    document.getElementById('fmModalBaja').classList.add('abierto');
}

function fmCerrarBaja() {
    document.getElementById('fmModalBaja').classList.remove('abierto');
}

document.querySelectorAll('.fm-modal-fondo').forEach(function(modal) {
    modal.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.classList.remove('abierto');
        }
    });
});

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        document.querySelectorAll('.fm-modal-fondo').forEach(function(modal) {
            modal.classList.remove('abierto');
        });
    }
});
</script>
