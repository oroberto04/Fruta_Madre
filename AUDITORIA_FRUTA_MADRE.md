# Auditoría estática Fruta Madre

{
  "fecha": "2026-11-18T10:25:12.010Z",
  "rama": "integracion-jam-mercadopago-pruebas",
  "archivosPHP": 47,
  "vistas": 23,
  "lintOK": 47,
  "lintBad": 0,
  "lintSkipped": false,
  "hallazgos": 67,
  "nota": "Análisis estático: no verifica PostgreSQL, pagos, correo, navegación en navegador ni permisos de administrador. No modifica archivos."
}

## Hallazgos
- [REVISAR] app\views\admin\index.php: Enlace a accion=admin-pedidos sin coincidencia literal en index.php
- [REVISAR] app\views\admin\modales\producto.php: Enlace a accion=admin-categorias sin coincidencia literal en index.php
- [REVISAR] app\views\admin\modales\producto.php: Enlace a accion=admin-paquetes sin coincidencia literal en index.php
- [REVISAR] app\views\admin\modales\producto.php: Enlace a accion=admin-carritos sin coincidencia literal en index.php
- [REVISAR] app\views\admin\modales\producto.php: Enlace a accion=admin-pedidos sin coincidencia literal en index.php
- [REVISAR] app\views\admin\modales\producto.php: Enlace a accion=admin-reservas sin coincidencia literal en index.php
- [REVISAR] app\views\admin\modales\producto.php: Enlace a accion=admin-clientes sin coincidencia literal en index.php
- [REVISAR] app\views\admin\modales\producto.php: Enlace a accion=admin-inventario sin coincidencia literal en index.php
- [REVISAR] app\views\admin\modales\producto.php: Enlace a accion=admin-reportes sin coincidencia literal en index.php
- [REVISAR] app\views\admin\modales\producto.php: Enlace a accion=admin-inventario sin coincidencia literal en index.php
- [REVISAR] app\views\admin\modales\producto.php: Enlace a accion=admin-pedidos sin coincidencia literal en index.php
- [REVISAR] app\views\admin\modales\producto.php: Enlace a accion=admin-reservas sin coincidencia literal en index.php
- [REVISAR] app\views\admin\modales\producto.php: Enlace a accion=admin-reportes sin coincidencia literal en index.php
- [REVISAR] app\views\admin\modales\producto.php: Enlace a accion=admin-cambiar-estado-producto sin coincidencia literal en index.php
- [REVISAR] app\views\admin\modales\producto.php: 1 indicadores de lógica provisional o avisos (inspeccionar manualmente)
- [REVISAR] app\views\admin\panel.php: Enlace a accion=admin-categorias-stitch sin coincidencia literal en index.php
- [REVISAR] app\views\admin\panel.php: Enlace a accion=admin-paquetes-stitch sin coincidencia literal en index.php
- [REVISAR] app\views\admin\panel.php: Enlace a accion=admin-carritos-stitch sin coincidencia literal en index.php
- [REVISAR] app\views\admin\panel.php: Enlace a accion=admin-reservas-stitch sin coincidencia literal en index.php
- [REVISAR] app\views\admin\panel.php: Enlace a accion=admin-clientes-stitch sin coincidencia literal en index.php
- [REVISAR] app\views\admin\panel.php: Enlace a accion=admin-inventario-stitch sin coincidencia literal en index.php
- [REVISAR] app\views\admin\panel.php: Enlace a accion=admin-reportes-stitch sin coincidencia literal en index.php
- [REVISAR] app\views\admin\panel.php: Enlace a accion=admin-pedidos sin coincidencia literal en index.php
- [REVISAR] app\views\admin\panel.php: Enlace a accion=admin-categorias-stitch sin coincidencia literal en index.php
- [REVISAR] app\views\admin\panel.php: Enlace a accion=admin-clientes-stitch sin coincidencia literal en index.php
- [REVISAR] app\views\admin\panel.php: Enlace a accion=admin-inventario-stitch sin coincidencia literal en index.php
- [REVISAR] app\views\admin\panel.php: Enlace a accion=admin-reportes-stitch sin coincidencia literal en index.php
- [REVISAR] app\views\admin\pedidos.php: Enlace a accion=admin sin coincidencia literal en index.php
- [REVISAR] app\views\admin\pedidos.php: Enlace a accion=admin-confirmar-pago sin coincidencia literal en index.php
- [REVISAR] app\views\admin\pedidos.php: Enlace a accion=admin-actualizar-pedido sin coincidencia literal en index.php
- [REVISAR] app\views\admin\pedidos.php: Enlace a accion=admin-actualizar-entrega sin coincidencia literal en index.php
- [REVISAR] app\views\admin\pedidos.php: 3 indicadores de lógica provisional o avisos (inspeccionar manualmente)
- [REVISAR] app\views\admin\stitch_layout.php: Enlace a accion=admin sin coincidencia literal en index.php
- [REVISAR] app\views\admin\stitch_operaciones.php: Enlace a accion=admin sin coincidencia literal en index.php
- [REVISAR] app\views\admin\stitch_operaciones.php: Enlace a accion=admin-pedidos sin coincidencia literal en index.php
- [REVISAR] app\views\admin\stitch_operaciones.php: Enlace a accion=admin-reservas-stitch sin coincidencia literal en index.php
- [REVISAR] app\views\admin\stitch_operaciones.php: Enlace a accion=admin-clientes-stitch sin coincidencia literal en index.php
- [REVISAR] app\views\admin\stitch_operaciones.php: Enlace a accion=admin-reportes-stitch sin coincidencia literal en index.php
- [REVISAR] app\views\admin\stitch_operaciones.php: Enlace a accion=admin- sin coincidencia literal en index.php
- [REVISAR] app\views\admin\stitch_operaciones.php: Enlace a accion=admin-pedidos sin coincidencia literal en index.php
- [REVISAR] app\views\admin\stitch_operaciones.php: Enlace a accion=admin-reservas-stitch sin coincidencia literal en index.php
- [REVISAR] app\views\admin\stitch_operaciones.php: 2 indicadores de lógica provisional o avisos (inspeccionar manualmente)
- [REVISAR] app\views\admin\stitch_productos.php: Enlace a accion=admin-categorias-stitch sin coincidencia literal en index.php
- [REVISAR] app\views\admin\stitch_productos.php: Enlace a accion=admin-paquetes-stitch sin coincidencia literal en index.php
- [REVISAR] app\views\admin\stitch_productos.php: Enlace a accion=admin-carritos-stitch sin coincidencia literal en index.php
- [REVISAR] app\views\admin\stitch_productos.php: Enlace a accion=admin-pedidos sin coincidencia literal en index.php
- [REVISAR] app\views\admin\stitch_productos.php: Enlace a accion=admin-reservas-stitch sin coincidencia literal en index.php
- [REVISAR] app\views\admin\stitch_productos.php: Enlace a accion=admin-clientes-stitch sin coincidencia literal en index.php
- [REVISAR] app\views\admin\stitch_productos.php: Enlace a accion=admin-inventario-stitch sin coincidencia literal en index.php
- [REVISAR] app\views\admin\stitch_productos.php: Enlace a accion=admin-reportes-stitch sin coincidencia literal en index.php
- [REVISAR] app\views\admin\stitch_productos.php: Enlace a accion=admin-paquetes-stitch sin coincidencia literal en index.php
- [REVISAR] app\views\admin\stitch_productos.php: Enlace a accion=admin-inventario-stitch sin coincidencia literal en index.php
- [REVISAR] app\views\admin\stitch_productos.php: Enlace a accion=admin-pedidos sin coincidencia literal en index.php
- [REVISAR] app\views\admin\stitch_productos.php: Enlace a accion=admin-reservas-stitch sin coincidencia literal en index.php
- [REVISAR] app\views\admin\stitch_productos.php: Enlace a accion=admin-reportes-stitch sin coincidencia literal en index.php
- [REVISAR] app\views\admin\stitch_productos.php: 1 indicadores de lógica provisional o avisos (inspeccionar manualmente)
- [REVISAR] app\views\home\carrito.php: 8 indicadores de lógica provisional o avisos (inspeccionar manualmente)
- [REVISAR] app\views\home\catalogo.php: 10 indicadores de lógica provisional o avisos (inspeccionar manualmente)
- [REVISAR] app\views\home\confirmacion-reserva-evento.php: 8 indicadores de lógica provisional o avisos (inspeccionar manualmente)
- [REVISAR] app\views\home\confirmacion.php: 15 indicadores de lógica provisional o avisos (inspeccionar manualmente)
- [REVISAR] app\views\home\detalle.php: 3 indicadores de lógica provisional o avisos (inspeccionar manualmente)
- [REVISAR] app\views\home\eventos.php: Enlace a accion=admin-paquetes-stitch sin coincidencia literal en index.php
- [REVISAR] app\views\home\eventos.php: 1 indicadores de lógica provisional o avisos (inspeccionar manualmente)
- [REVISAR] app\views\home\index.php: 7 indicadores de lógica provisional o avisos (inspeccionar manualmente)
- [REVISAR] app\views\home\mispedidos.php: 14 indicadores de lógica provisional o avisos (inspeccionar manualmente)
- [REVISAR] app\views\home\pago.php: 31 indicadores de lógica provisional o avisos (inspeccionar manualmente)
- [REVISAR] app\views\home\revisar.php: 2 indicadores de lógica provisional o avisos (inspeccionar manualmente)

## Pruebas funcionales aún necesarias
- Registro, inicio/cierre de sesión y roles
- Crear, editar y borrar productos y categorías
- Subir imágenes y comprobar persistencia
- Buscar, filtrar, carrito, inventario y pedidos
- Crear reservas, verificar disponibilidad y guardar en PostgreSQL
- Checkout Pro TEST, retorno, webhook y conciliación de estado
- Panel de administración y permisos
- Revisión visual de logo y diseños Stitch 36/37
