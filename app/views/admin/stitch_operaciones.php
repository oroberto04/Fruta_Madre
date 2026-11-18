<?php
require_once __DIR__.'/stitch_layout.php';
$e='stitchEscape';
$titulos=['categorias'=>'Categorías','paquetes'=>'Paquetes para eventos','carritos'=>'Carritos botaneros',
 'reservas'=>'Reservas y agenda','clientes'=>'Clientes','inventario'=>'Inventario','reportes'=>'Reportes y métricas'];
$descripcion=['categorias'=>'Organiza las familias de producto de la tienda.','paquetes'=>'Administra paquetes de fiestas y su cotización base.',
 'carritos'=>'Control de las unidades físicas disponibles.','reservas'=>'Reservas reales realizadas desde el sitio de Roberto.',
 'clientes'=>'Cuentas reales registradas; no se exponen contraseñas.','inventario'=>'Ajusta existencias y alertas mínimas de los productos.',
 'reportes'=>'Indicadores y exportación de pedidos existentes; los pagos TEST no cuentan como ingresos.'];
$titulo=$titulos[$modulo];
$columnas=[
 'categorias'=>['id_categoria'=>'ID','nombre_categoria'=>'Categoría','descripcion'=>'Descripción','activa'=>'Activa','productos'=>'Productos'],
 'paquetes'=>['id_paquete'=>'ID','nombre_paquete'=>'Paquete','descripcion'=>'Descripción','capacidad_personas'=>'Personas','duracion_horas'=>'Horas','precio_base'=>'Precio','eventos'=>'Eventos'],
 'carritos'=>['id_carrito'=>'ID','codigo_inventario'=>'Código','estado_fisico'=>'Estado físico','reservas'=>'Reservas'],
 'reservas'=>['id_reserva'=>'Folio','fecha_hora'=>'Fecha del evento','nombre_completo'=>'Cliente','nombre_paquete'=>'Paquete','codigo_inventario'=>'Carrito','num_invitados'=>'Invitados','estado_reserva'=>'Estado','costo_total'=>'Costo'],
 'clientes'=>['id_usuario'=>'ID','nombre_completo'=>'Nombre','correo'=>'Correo','telefono'=>'Teléfono','pedidos'=>'Pedidos','fecha_registro'=>'Registro'],
 'inventario'=>['id_producto'=>'ID','sku'=>'SKU','nombre'=>'Producto','nombre_categoria'=>'Categoría','stock_disponible'=>'Stock','stock_minimo'=>'Mínimo','activo'=>'Activo'],
 'reportes'=>['id_pedido'=>'Pedido','fecha_pedido'=>'Fecha','nombre_completo'=>'Cliente','total'=>'Total','estado_pedido'=>'Estado','metodo_pago'=>'Método','estado_pago'=>'Pago']
];
$editId=(int)($_GET['editar']??0);$edit=[];
foreach($filas as $f) {
 $clave=['categorias'=>'id_categoria','paquetes'=>'id_paquete','carritos'=>'id_carrito','inventario'=>'id_producto','reservas'=>'id_reserva'][$modulo]??'';
 if($clave&&isset($f[$clave])&&(int)$f[$clave]===$editId){$edit=$f;break;}
}
stitchInicio($titulo,'admin-'.$modulo.'-stitch',$descripcion[$modulo]);
?>
<?php if($mensaje): ?><div role="status" class="alert <?= ($mensaje['tipo']??'')==='error'?'error':'' ?>"><?= $e($mensaje['texto']??'') ?></div><?php endif; ?>
<?php if($modulo==='reportes'): ?><div class="stats"><div class="stat"><span>PEDIDOS</span><strong><?= (int)($resumen['pedidos']??0) ?></strong></div><div class="stat"><span>CLIENTES</span><strong><?= (int)($resumen['clientes']??0) ?></strong></div><div class="stat"><span>RESERVAS</span><strong><?= (int)($resumen['reservas']??0) ?></strong></div><div class="stat"><span>INGRESOS CONFIRMADOS (MXN)</span><strong style="font-size:22px">$<?= number_format((float)($resumen['ingresos_confirmados']??0),2) ?></strong></div></div><p class="section-note">Se contabilizan únicamente pedidos con pagos registrados como <b>pagado</b>, excluyendo Mercado Pago de pruebas.</p><?php endif; ?>
<div class="modules"><a class="module" href="index.php?accion=admin">🏠 Dashboard</a><a class="module" href="index.php?accion=admin-productos">🍉 Productos</a><a class="module" href="index.php?accion=admin-pedidos">🛵 Pedidos</a><a class="module" href="index.php?accion=admin-reservas-stitch">📅 Reservas</a><a class="module" href="index.php?accion=admin-clientes-stitch">👥 Clientes</a><a class="module" href="index.php?accion=admin-reportes-stitch">📊 Reportes</a></div>
<form class="toolbar" action="index.php" method="get"><input type="hidden" name="accion" value="<?= $e($ruta) ?>"><div class="group search"><label for="buscar">Buscar en <?= $e(mb_strtolower($titulo)) ?></label><input class="control" id="buscar" type="search" name="q" value="<?= $e($buscar) ?>" placeholder="Nombre, folio, estado..."></div><button class="btn" type="submit">⌕ Buscar</button><a href="index.php?accion=<?= $e($ruta) ?>" class="btn">Limpiar</a><?php if($modulo==='reportes'): ?><a class="btn green" href="index.php?accion=admin-reportes-csv">⇩ Exportar CSV</a><?php elseif(in_array($modulo,['categorias','paquetes','carritos'],true)): ?><a class="btn green" href="#formulario">⊕ Registrar</a><?php endif; ?></form>
<div class="<?= in_array($modulo,['categorias','paquetes','carritos','inventario','reservas'],true)?'content-grid':'stack' ?>"><section class="panel"><div class="panel-heading"><h2>● <?= $e(mb_strtoupper($titulo)) ?> · DATOS REALES</h2><span class="muted"><?= count($filas) ?> registros</span></div><div class="table-wrap"><table class="table"><thead><tr><?php foreach($columnas[$modulo] as $nom): ?><th><?= $e($nom) ?></th><?php endforeach; ?><?php if(in_array($modulo,['categorias','paquetes','carritos','inventario','reservas'],true)): ?><th>Editar</th><?php endif; ?></tr></thead><tbody>
<?php foreach($filas as $fila): ?><tr><?php foreach($columnas[$modulo] as $k=>$nom): ?><td><?php
$v=$fila[$k]??'';
if(in_array($k,['precio_base','costo_total','total'],true))echo '$'.number_format((float)$v,2);
elseif(in_array($k,['activa','activo'],true))echo in_array($v,[true,'t','1',1],true)?'Sí':'No';
elseif(str_starts_with($k,'estado'))echo '<span class="badge">'.$e($v).'</span>';
else echo $e($v);
?></td><?php endforeach; ?><?php if(in_array($modulo,['categorias','paquetes','carritos','inventario','reservas'],true)): ?><td><a class="mini" href="index.php?accion=<?= $e($ruta) ?>&editar=<?= (int)($fila[array_key_first($columnas[$modulo])]??0) ?>#formulario">✎</a></td><?php endif; ?></tr><?php endforeach; ?><?php if(!$filas): ?><tr><td class="table-empty" colspan="<?= count($columnas[$modulo])+1 ?>">No hay registros coincidentes en PostgreSQL.</td></tr><?php endif; ?></tbody></table></div></section>
<?php if(in_array($modulo,['categorias','paquetes','carritos','inventario','reservas'],true)): ?>
<section class="panel" id="formulario"><div class="editor-heading"><small>⚡ EDICIÓN DE DATOS REALES</small><h2><?= $edit?'EDITAR REGISTRO':'NUEVO / ACTUALIZAR' ?></h2><small>Los cambios se guardan en PostgreSQL</small></div>
<?php $action=['categorias'=>'categoria','paquetes'=>'paquete','carritos'=>'carrito','inventario'=>'inventario','reservas'=>'reserva'][$modulo]; ?>
<form method="post" action="index.php?accion=admin-<?= $action ?>-guardar-stitch"><input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>"><input type="hidden" name="id" value="<?= (int)($edit[array_key_first($columnas[$modulo])]??0) ?>"><div class="editor">
<?php if($modulo==='categorias'): ?>
<div class="field"><label>Nombre *</label><input required maxlength="255" class="control" name="nombre" value="<?= $e($edit['nombre_categoria']??'') ?>"></div>
<div class="field"><label>Descripción</label><textarea rows="3" class="control" name="descripcion"><?= $e($edit['descripcion']??'') ?></textarea></div>
<?php if($edit): ?><div class="field"><label>Visibilidad</label><select class="control" name="activa"><option value="1" <?= in_array($edit['activa'],['t',true,'1',1],true)?'selected':'' ?>>Activa</option><option value="0" <?= !in_array($edit['activa'],['t',true,'1',1],true)?'selected':'' ?>>Inactiva</option></select></div><?php endif; ?>
<?php elseif($modulo==='paquetes'): ?>
<div class="field"><label>Nombre *</label><input required maxlength="255" class="control" name="nombre" value="<?= $e($edit['nombre_paquete']??'') ?>"></div><div class="field"><label>Descripción</label><textarea class="control" name="descripcion" rows="3"><?= $e($edit['descripcion']??'') ?></textarea></div>
<div class="fieldrow"><div class="field"><label>Capacidad personas *</label><input required class="control" type="number" min="1" name="capacidad" value="<?= $e($edit['capacidad_personas']??'10') ?>"></div><div class="field"><label>Duración horas *</label><input required class="control" type="number" min="1" name="duracion" value="<?= $e($edit['duracion_horas']??'2') ?>"></div></div><div class="field"><label>Precio base MXN *</label><input required type="number" step="0.01" min="0" name="precio" class="control" value="<?= $e($edit['precio_base']??'') ?>"></div>
<?php elseif($modulo==='carritos'): ?>
<div class="field"><label>Código del inventario *</label><input required maxlength="50" class="control" name="codigo" value="<?= $e($edit['codigo_inventario']??'') ?>"></div><div class="field"><label>Estado físico *</label><select class="control" name="estado"><?php foreach(['disponible','mantenimiento','ocupado','inactivo'] as $v): ?><option <?= ($edit['estado_fisico']??'disponible')===$v?'selected':'' ?>><?= $v ?></option><?php endforeach; ?></select></div>
<?php elseif($modulo==='inventario'): ?>
<?php if(!$edit): ?><p class="section-note">Selecciona ✎ en un producto para ajustar existencias. Los datos no se borran.</p><?php else: ?><p><strong><?= $e($edit['nombre']) ?> · <?= $e($edit['sku']) ?></strong></p><div class="field"><label>Existencia actual *</label><input class="control" type="number" min="0" required name="stock" value="<?= (int)$edit['stock_disponible'] ?>"></div><div class="field"><label>Alerta mínima *</label><input class="control" type="number" min="0" required name="minimo" value="<?= (int)$edit['stock_minimo'] ?>"></div><?php endif; ?>
<?php elseif($modulo==='reservas'): ?>
<?php if(!$edit): ?><p class="section-note">Selecciona ✎ en una reserva para modificar su estado.</p><?php else: ?><p><strong>Reserva #<?= (int)$edit['id_reserva'] ?> · <?= $e($edit['nombre_completo']) ?></strong></p><div class="field"><label>Estado de reserva</label><select class="control" name="estado"><?php foreach(['pendiente','confirmada','en_proceso','completada','cancelada'] as $v): ?><option <?= ($edit['estado_reserva']??'')===$v?'selected':'' ?>><?= $v ?></option><?php endforeach; ?></select></div><?php endif; ?>
<?php endif; ?></div><div class="editor-footer"><a class="btn" href="index.php?accion=<?= $e($ruta) ?>">Cancelar</a><?php if(!in_array($modulo,['inventario','reservas'],true)||$edit): ?><button class="btn green" type="submit">▣ GUARDAR</button><?php endif; ?></div></form></section>
<?php endif; ?></div>
<div class="bottom-cards"><div class="bottom-card"><span class="eyebrow">🍉 CATÁLOGO</span><h3>Productos reales</h3><p>Agregar, editar, dar de baja y gestionar fotografías.</p><a class="btn" href="index.php?accion=admin-productos">Ir a productos</a></div><div class="bottom-card"><span class="eyebrow green">🛵 PEDIDOS</span><h3>Comandas de Roberto</h3><p>Conserva el flujo original de gestión de pedidos.</p><a class="btn" href="index.php?accion=admin-pedidos">Ver pedidos</a></div><div class="bottom-card"><span class="eyebrow">📅 EVENTOS</span><h3>Reservas</h3><p>Consulta los eventos programados.</p><a class="btn" href="index.php?accion=admin-reservas-stitch">Ver agenda</a></div><div class="bottom-card"><span class="eyebrow green">📊 REPORTES</span><h3>Exportación CSV</h3><p>Descarga los pedidos reales de PostgreSQL.</p><a class="btn" href="index.php?accion=admin-reportes-csv">Exportar CSV</a></div></div>
<?php stitchFin(); ?>
