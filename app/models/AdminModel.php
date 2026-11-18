
<?php

require_once __DIR__ . '/../config/database.php';

class AdminModel
{
    private PDO $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    /**
     * Obtiene los indicadores generales del panel.
     */
    public function obtenerResumen(): array
    {
        $sql = "
            SELECT
                (SELECT COUNT(*)
                 FROM producto
                 WHERE activo = TRUE) AS productos_activos,

                (SELECT COUNT(*)
                 FROM categoria
                 WHERE activa = TRUE) AS categorias_activas,

                (SELECT COUNT(*)
                 FROM usuario
                 WHERE rol = 'cliente') AS clientes,

                (SELECT COUNT(*)
                 FROM pedido) AS pedidos
        ";

        $stmt = $this->db->query($sql);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'productos_activos' => 0,
            'categorias_activas' => 0,
            'clientes' => 0,
            'pedidos' => 0
        ];
    }

    /**
     * Obtiene todos los pedidos para la administración.
     */
    public function obtenerPedidos(): array
    {
        $sql = "
            SELECT
                p.id_pedido,
                p.id_usuario,
                p.fecha_pedido,
                p.estado_pedido,
                p.tipo_servicio,
                p.fecha_preferida,
                p.horario_preferido,
                p.correo_contacto,
                p.observaciones,
                p.subtotal,
                p.total,

                u.nombre_completo,
                u.correo,
                u.telefono,

                pg.metodo_pago,
                pg.estado_pago,
                pg.monto_pagado,
                pg.fecha_pago,

                e.id_entrega,
                e.estado_entrega,
                e.fecha_estimada,
                e.notas_repartidor,

                d.calle_numero,
                d.colonia,
                d.ciudad,
                d.codigo_postal,
                d.referencias,

                COALESCE(
                    JSON_AGG(
                        JSON_BUILD_OBJECT(
                            'id_producto', dp.id_producto,
                            'nombre', pr.nombre,
                            'cantidad', dp.cantidad,
                            'precio_unitario', dp.precio_unitario,
                            'subtotal_linea', dp.subtotal_linea
                        )
                    ) FILTER (WHERE dp.id_producto IS NOT NULL),
                    '[]'::json
                ) AS productos

            FROM pedido p

            INNER JOIN usuario u
                ON u.id_usuario = p.id_usuario

            LEFT JOIN pago pg
                ON pg.id_pedido = p.id_pedido

            LEFT JOIN entrega e
                ON e.id_pedido = p.id_pedido

            LEFT JOIN direccion d
                ON d.id_direccion = e.id_direccion

            LEFT JOIN detalle_pedido dp
                ON dp.id_pedido = p.id_pedido

            LEFT JOIN producto pr
                ON pr.id_producto = dp.id_producto

            GROUP BY
                p.id_pedido,
                u.id_usuario,
                pg.id_pago,
                e.id_entrega,
                d.id_direccion

            ORDER BY p.fecha_pedido DESC
        ";

        $stmt = $this->db->query($sql);

        $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($pedidos as &$pedido) {
            $pedido['productos'] = json_decode(
                $pedido['productos'] ?? '[]',
                true
            ) ?: [];
        }

        unset($pedido);

        return $pedidos;
    }

    /**
     * Actualiza el estado de un pedido y sincroniza su entrega.
     */
    public function actualizarEstadoPedido(
        int $idPedido,
        string $estado
    ): bool {
        $estadosPermitidos = [
            'pendiente',
            'confirmado',
            'en_preparacion',
            'en_camino',
            'entregado',
            'cancelado'
        ];

        if (
            $idPedido <= 0 ||
            !in_array($estado, $estadosPermitidos, true)
        ) {
            return false;
        }

        try {
            $this->db->beginTransaction();

            // Verificar que el pedido exista.
            $sqlExiste = "
                SELECT id_pedido
                FROM pedido
                WHERE id_pedido = :id_pedido
                FOR UPDATE
            ";

            $stmtExiste = $this->db->prepare($sqlExiste);
            $stmtExiste->execute([
                ':id_pedido' => $idPedido
            ]);

            if (!$stmtExiste->fetch(PDO::FETCH_ASSOC)) {
                $this->db->rollBack();
                return false;
            }

            // Actualizar el estado del pedido.
            $sqlPedido = "
                UPDATE pedido
                SET estado_pedido = :estado
                WHERE id_pedido = :id_pedido
            ";

            $stmtPedido = $this->db->prepare($sqlPedido);
            $stmtPedido->execute([
                ':estado' => $estado,
                ':id_pedido' => $idPedido
            ]);

            // Sincronizar el estado de la entrega cuando corresponda.
            $estadoEntrega = null;

            if ($estado === 'en_camino') {
                $estadoEntrega = 'en_camino';
            } elseif ($estado === 'entregado') {
                $estadoEntrega = 'entregado';
            } elseif ($estado === 'cancelado') {
                $estadoEntrega = 'cancelado';
            }

            if ($estadoEntrega !== null) {
                $sqlEntrega = "
                    UPDATE entrega
                    SET estado_entrega = :estado
                    WHERE id_pedido = :id_pedido
                ";

                $stmtEntrega = $this->db->prepare($sqlEntrega);
                $stmtEntrega->execute([
                    ':estado' => $estadoEntrega,
                    ':id_pedido' => $idPedido
                ]);
            }

            $this->db->commit();

            return true;

        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            error_log(
                'Error al actualizar pedido: ' . $e->getMessage()
            );

            return false;
        }
    }

    /**
     * Actualiza el estado de una entrega.
     */
    public function actualizarEstadoEntrega(
        int $idEntrega,
        string $estado
    ): bool {
        $estadosPermitidos = [
            'pendiente',
            'en_camino',
            'entregado',
            'cancelado'
        ];

        if (
            $idEntrega <= 0 ||
            !in_array($estado, $estadosPermitidos, true)
        ) {
            return false;
        }

        $sql = "
            UPDATE entrega
            SET estado_entrega = :estado
            WHERE id_entrega = :id_entrega
        ";

        try {
            $stmt = $this->db->prepare($sql);

            $stmt->execute([
                ':estado' => $estado,
                ':id_entrega' => $idEntrega
            ]);

            return $stmt->rowCount() > 0;

        } catch (PDOException $e) {
            error_log(
                'Error al actualizar entrega: ' . $e->getMessage()
            );

            return false;
        }
    }


    /**
     * Confirma el pago de un pedido.
     */
    public function confirmarPago(int $idPedido): bool
    {
        if ($idPedido <= 0) {
            return false;
        }

        $sql = "
            UPDATE pago pg
            SET
                estado_pago = 'pagado',
                monto_pagado = p.total,
                fecha_pago = CURRENT_TIMESTAMP
            FROM pedido p
            WHERE pg.id_pedido = p.id_pedido
              AND pg.id_pedido = :id_pedido
              AND pg.estado_pago = 'pendiente'
        ";

        try {
            $stmt = $this->db->prepare($sql);

            $stmt->execute([
                ':id_pedido' => $idPedido
            ]);

            return $stmt->rowCount() > 0;

        } catch (PDOException $e) {
            error_log(
                'Error al confirmar pago: ' . $e->getMessage()
            );

            return false;
        }
    }

}
