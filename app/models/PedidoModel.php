
<?php

require_once __DIR__ . '/../config/database.php';

class PedidoModel
{
    private PDO $db;

    public function __construct()
    {
        $conexion = new Database();
        $this->db = $conexion->connect();
    }

    public function crearPedido(
        int $idUsuario,
        array $productosCarrito,
        array $datosPedido,
        string $metodoPago
    ): int {
        if ($idUsuario <= 0 || empty($productosCarrito)) {
            throw new InvalidArgumentException(
                'El pedido no contiene datos válidos.'
            );
        }

        $servicio = $datosPedido['tipo_servicio'] ?? '';

       $metodosPermitidos = [
    'efectivo',
    'transferencia',
    'mercadopago_prueba'
];

        if (!in_array($metodoPago, $metodosPermitidos, true)) {
            throw new InvalidArgumentException(
                'El método de pago seleccionado no es válido.'
            );
        }

        if (!in_array($servicio, ['domicilio', 'recoger'], true)) {
            throw new InvalidArgumentException(
                'El tipo de servicio no es válido.'
            );
        }

        $this->db->beginTransaction();

        try {
            // Actualizar los datos de contacto del usuario.
            $actualizarUsuario = $this->db->prepare(
                'UPDATE usuario
                 SET nombre_completo = :nombre,
                     telefono = :telefono
                 WHERE id_usuario = :usuario'
            );

            $actualizarUsuario->execute([
                'nombre' => trim($datosPedido['nombre'] ?? ''),
                'telefono' => trim($datosPedido['telefono'] ?? ''),
                'usuario' => $idUsuario
            ]);

            if ($actualizarUsuario->rowCount() === 0) {
                $verificarUsuario = $this->db->prepare(
                    'SELECT 1
                     FROM usuario
                     WHERE id_usuario = :usuario'
                );

                $verificarUsuario->execute([
                    'usuario' => $idUsuario
                ]);

                if (!$verificarUsuario->fetchColumn()) {
                    throw new RuntimeException(
                        'El usuario no existe.'
                    );
                }
            }

            $productosValidados = [];
            $subtotal = 0.0;

            // Consultar productos, precios y existencias.
            $consultaProducto = $this->db->prepare(
                'SELECT id_producto, nombre, precio, stock_disponible
                 FROM producto
                 WHERE id_producto = :id
                   AND activo = TRUE
                 FOR UPDATE'
            );

            foreach ($productosCarrito as $item) {
                $idProducto = (int) ($item['id_producto'] ?? 0);
                $cantidad = (int) ($item['cantidad'] ?? 0);

                if ($idProducto <= 0 || $cantidad <= 0) {
                    throw new RuntimeException(
                        'Hay un producto o una cantidad inválida.'
                    );
                }

                $consultaProducto->execute([
                    'id' => $idProducto
                ]);

                $producto = $consultaProducto->fetch(
                    PDO::FETCH_ASSOC
                );

                if (!$producto) {
                    throw new RuntimeException(
                        'Uno de los productos ya no está disponible.'
                    );
                }

                if (
                    $cantidad >
                    (int) $producto['stock_disponible']
                ) {
                    throw new RuntimeException(
                        'Existencias insuficientes para: ' .
                        $producto['nombre']
                    );
                }

                $precio = (float) $producto['precio'];
                $importe = round($precio * $cantidad, 2);

                $productosValidados[] = [
                    'id_producto' => $idProducto,
                    'cantidad' => $cantidad,
                    'precio' => $precio,
                    'subtotal' => $importe
                ];

                $subtotal += $importe;
            }

            $subtotal = round($subtotal, 2);
            $total = $subtotal;

                        // Crear el pedido con todos los datos de entrega y contacto.
            $insertarPedido = $this->db->prepare(
                "INSERT INTO pedido
                    (
                        id_usuario,
                        fecha_pedido,
                        estado_pedido,
                        subtotal,
                        total,
                        tipo_servicio,
                        fecha_preferida,
                        horario_preferido,
                        correo_contacto,
                        observaciones
                    )
                 VALUES
                    (
                        :usuario,
                        CURRENT_TIMESTAMP,
                        'pendiente',
                        :subtotal,
                        :total,
                        :servicio,
                        :fecha,
                        :horario,
                        :correo,
                        :observaciones
                    )
                 RETURNING id_pedido"
            );

            $insertarPedido->execute([
                'usuario' => $idUsuario,
                'subtotal' => $subtotal,
                'total' => $total,
                'servicio' => $servicio,
                'fecha' => !empty($datosPedido['fecha_preferida'])
                    ? $datosPedido['fecha_preferida']
                    : null,
                'horario' => !empty($datosPedido['horario_preferido'])
                    ? $datosPedido['horario_preferido']
                    : null,
                'correo' => !empty($datosPedido['correo'])
                    ? trim($datosPedido['correo'])
                    : null,
                'observaciones' => !empty($datosPedido['observaciones'])
                    ? trim($datosPedido['observaciones'])
                    : null
            ]);

            $idPedido = (int) $insertarPedido->fetchColumn();

            // Registrar los productos del pedido.
            $insertarDetalle = $this->db->prepare(
                'INSERT INTO detalle_pedido
                    (id_pedido, id_producto, cantidad,
                     precio_unitario, subtotal_linea)
                 VALUES
                    (:pedido, :producto, :cantidad,
                     :precio, :subtotal)'
            );

            $descontarStock = $this->db->prepare(
                'UPDATE producto
                 SET stock_disponible =
                     stock_disponible - :cantidad
                 WHERE id_producto = :producto
                   AND stock_disponible >= :cantidad'
            );

            foreach ($productosValidados as $item) {
                $insertarDetalle->execute([
                    'pedido' => $idPedido,
                    'producto' => $item['id_producto'],
                    'cantidad' => $item['cantidad'],
                    'precio' => $item['precio'],
                    'subtotal' => $item['subtotal']
                ]);

                $descontarStock->execute([
                    'cantidad' => $item['cantidad'],
                    'producto' => $item['id_producto']
                ]);

                if ($descontarStock->rowCount() !== 1) {
                    throw new RuntimeException(
                        'No fue posible reservar las existencias ' .
                        'de un producto.'
                    );
                }
            }

            // Crear dirección y entrega SOLO a domicilio.
            if ($servicio === 'domicilio') {
                $direccion = trim(
                    $datosPedido['direccion'] ?? ''
                );

                if ($direccion === '') {
                    throw new RuntimeException(
                        'La dirección de entrega es obligatoria.'
                    );
                }

                $insertarDireccion = $this->db->prepare(
                    'INSERT INTO direccion
                        (id_usuario, calle_numero, referencias)
                     VALUES
                        (:usuario, :direccion, :referencias)
                     RETURNING id_direccion'
                );

                $insertarDireccion->execute([
                    'usuario' => $idUsuario,
                    'direccion' => $direccion,
                    'referencias' => trim(
                        $datosPedido['referencias'] ?? ''
                    )
                ]);

                $idDireccion = (int)
                    $insertarDireccion->fetchColumn();

                $notas = trim(
                    $datosPedido['observaciones'] ?? ''
                );

                $insertarEntrega = $this->db->prepare(
                    "INSERT INTO entrega
                        (id_pedido, id_direccion, estado_entrega,
                         notas_repartidor)
                     VALUES
                        (:pedido, :direccion, 'pendiente', :notas)"
                );

                $insertarEntrega->execute([
                    'pedido' => $idPedido,
                    'direccion' => $idDireccion,
                    'notas' => $notas !== '' ? $notas : null
                ]);
            }

            // Registrar el pago como pendiente.
            $insertarPago = $this->db->prepare(
                "INSERT INTO pago
                    (id_pedido, metodo_pago, estado_pago,
                     monto_pagado, fecha_pago)
                 VALUES
                    (:pedido, :metodo, 'pendiente', 0, NULL)"
            );

            $insertarPago->execute([
                'pedido' => $idPedido,
                'metodo' => $metodoPago
            ]);

            // Confirmar todos los cambios.
            $this->db->commit();

            return $idPedido;

        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

public function listarPedidosUsuario(int $idUsuario): array
{
    if ($idUsuario <= 0) {
        return [];
    }

    $sql = "
        SELECT
            p.id_pedido,
            p.fecha_pedido,
            p.estado_pedido,
            p.tipo_servicio,
            p.subtotal,
            p.total,

            pg.metodo_pago,
            pg.estado_pago,

            COALESCE(det.cantidad_productos, 0) AS cantidad_productos,
            COALESCE(det.nombres_productos, '') AS nombres_productos,
            COALESCE(det.productos_json, '[]') AS productos_json,

            e.estado_entrega,
            e.fecha_estimada,
            e.notas_repartidor,

            d.calle_numero,
            d.colonia,
            d.ciudad,
            d.codigo_postal,
            d.referencias

        FROM pedido p

        LEFT JOIN pago pg
            ON pg.id_pedido = p.id_pedido

        LEFT JOIN entrega e
            ON e.id_pedido = p.id_pedido

        LEFT JOIN direccion d
            ON d.id_direccion = e.id_direccion

        LEFT JOIN LATERAL (
            SELECT
                SUM(dp.cantidad) AS cantidad_productos,

                STRING_AGG(
                    pr.nombre,
                    ', ' ORDER BY pr.nombre
                ) AS nombres_productos,

                JSON_AGG(
                    JSON_BUILD_OBJECT(
                        'id_producto', pr.id_producto,
                        'nombre', pr.nombre,
                        'cantidad', dp.cantidad,
                        'precio_unitario', dp.precio_unitario,
                        'subtotal', dp.subtotal_linea
                    )
                    ORDER BY pr.nombre
                ) AS productos_json

            FROM detalle_pedido dp

            INNER JOIN producto pr
                ON pr.id_producto = dp.id_producto

            WHERE dp.id_pedido = p.id_pedido
        ) det ON TRUE

        WHERE p.id_usuario = :id_usuario

        ORDER BY p.fecha_pedido DESC, p.id_pedido DESC
    ";

    $consulta = $this->db->prepare($sql);

    $consulta->execute([
        'id_usuario' => $idUsuario
    ]);

    $pedidos = $consulta->fetchAll(PDO::FETCH_ASSOC);

    foreach ($pedidos as &$pedido) {
        $productos = json_decode(
            $pedido['productos_json'] ?? '[]',
            true
        );

        $pedido['productos'] = is_array($productos)
            ? $productos
            : [];
    }

    unset($pedido);

    return $pedidos;
}

}
