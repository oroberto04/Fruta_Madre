<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/MercadoPagoPruebas.php';

final class MercadoPagoPruebasController
{
    private function pedidoDeSesion(): array
    {
        $usuario = (int) ($_SESSION['usuario']['id_usuario'] ?? 0);
        $pedidoId = (int) ($_SESSION['pedido_creado']['id_pedido'] ?? 0);
        $solicitado = (int) ($_GET['id_pedido'] ?? $pedidoId);

        if (
            !$usuario || !$pedidoId || $pedidoId !== $solicitado ||
            ($_SESSION['pedido_creado']['metodo_pago'] ?? '') !== 'mercadopago_prueba'
        ) {
            throw new RuntimeException('No hay pedido de prueba autorizado en la sesión.');
        }

        $db = (new Database())->connect();

        $stmt = $db->prepare(
            'SELECT p.id_pedido, p.total, p.estado_pedido, pg.estado_pago
             FROM pedido p
             INNER JOIN pago pg ON pg.id_pedido = p.id_pedido
             WHERE p.id_pedido = :pedido
               AND p.id_usuario = :usuario
               AND pg.metodo_pago = :metodo'
        );

        $stmt->execute([
            'pedido' => $pedidoId,
            'usuario' => $usuario,
            'metodo' => 'mercadopago_prueba'
        ]);

        $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pedido) {
            throw new RuntimeException('El pedido no pertenece a esta cuenta.');
        }

        $pedido['db'] = $db;
        return $pedido;
    }

    private function informar(string $estado, string $detalle): never
    {
        $_SESSION['pago_mp_estado'] = $estado;
        $_SESSION['pago_mp_detalle'] = $detalle;

        header('Location: index.php?accion=pedido-confirmado', true, 303);
        exit;
    }

    private function generarUuid(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

        $h = bin2hex($bytes);

        return substr($h, 0, 8) . '-'
            . substr($h, 8, 4) . '-'
            . substr($h, 12, 4) . '-'
            . substr($h, 16, 4) . '-'
            . substr($h, 20);
    }

    public function iniciar(): never
    {
        try {
            $pedido = $this->pedidoDeSesion();

            if ($pedido['estado_pedido'] === 'cancelado') {
                throw new RuntimeException('Pedido cancelado.');
            }

            if ($pedido['estado_pago'] === 'aprobado_prueba') {
                $this->informar(
                    'aprobado_prueba',
                    'El pago de prueba ya fue verificado.'
                );
            }

            $db = $pedido['db'];
            $idPedido = (int) $pedido['id_pedido'];
            $centavos = (int) round((float) $pedido['total'] * 100);

            // Bloqueo por pedido para impedir creaciones simultáneas.
            $db->beginTransaction();

            $lock = $db->prepare(
                'SELECT id_pedido FROM pedido WHERE id_pedido = :id FOR UPDATE'
            );
            $lock->execute(['id' => $idPedido]);

            $stmt = $db->prepare(
                'SELECT order_id, idempotency_key, checkout_url
                 FROM mp_orders_test WHERE id_pedido = :id'
            );
            $stmt->execute(['id' => $idPedido]);

            $dato = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$dato) {
                $uuid = $this->generarUuid();

                $insert = $db->prepare(
                    'INSERT INTO mp_orders_test (id_pedido, idempotency_key)
                     VALUES (:id, :uuid)'
                );
                $insert->execute([
                    'id' => $idPedido,
                    'uuid' => $uuid
                ]);

                $dato = [
                    'order_id' => null,
                    'idempotency_key' => $uuid,
                    'checkout_url' => null
                ];
            }

            if (empty($dato['order_id']) || empty($dato['checkout_url'])) {
                $order = MercadoPagoPruebas::crearOrder(
                    $idPedido,
                    $centavos,
                    (string) $dato['idempotency_key']
                );

                $update = $db->prepare(
                    'UPDATE mp_orders_test
                     SET order_id = :order_id,
                         checkout_url = :url,
                         actualizado_en = CURRENT_TIMESTAMP
                     WHERE id_pedido = :id'
                );

                $update->execute([
                    'order_id' => $order['id'],
                    'url' => $order['url'],
                    'id' => $idPedido
                ]);

                $dato['order_id'] = $order['id'];
                $dato['checkout_url'] = $order['url'];
            }

            $db->commit();

            header('Location: ' . $dato['checkout_url'], true, 303);
            exit;

        } catch (Throwable $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }

            error_log('Fruta Madre Orders TEST inicio: ' . $e->getMessage());

            $this->informar(
                'error',
                'No se pudo iniciar el checkout de prueba.'
            );
        }
    }

    public function resultado(): never
    {
        try {
            $pedido = $this->pedidoDeSesion();

            if ($pedido['estado_pago'] === 'aprobado_prueba') {
                $this->informar(
                    'aprobado_prueba',
                    'El pago de prueba ya fue verificado.'
                );
            }

            if ($pedido['estado_pedido'] === 'cancelado') {
                throw new RuntimeException('Pedido cancelado.');
            }

            $db = $pedido['db'];
            $idPedido = (int) $pedido['id_pedido'];

            // Recuperar la orden desde PostgreSQL, no desde la sesión.
            $stmt = $db->prepare(
                'SELECT order_id FROM mp_orders_test
                 WHERE id_pedido = :id'
            );
            $stmt->execute(['id' => $idPedido]);

            $orderId = $stmt->fetchColumn();

            if (!$orderId) {
                $this->informar(
                    'pendiente',
                    'No existe una orden de Mercado Pago vinculada al pedido.'
                );
            }

            if (
                isset($_GET['order_id']) &&
                $_GET['order_id'] !== $orderId
            ) {
                throw new RuntimeException('ID de orden no coincide.');
            }

            // Consultar directamente a Mercado Pago.
            $order = MercadoPagoPruebas::consultarOrder($orderId);

            $centavos = (int) round((float) $pedido['total'] * 100);

            $estado = MercadoPagoPruebas::validarRespuestaOrder(
                $order,
                $orderId,
                $idPedido,
                $centavos
            );

            if ($estado === 'aprobado_prueba') {
                $update = $db->prepare(
                    "UPDATE pago
                     SET estado_pago = 'aprobado_prueba',
                         monto_pagado = 0,
                         fecha_pago = CURRENT_TIMESTAMP
                     WHERE id_pedido = :id
                       AND metodo_pago = 'mercadopago_prueba'
                       AND estado_pago = 'pendiente'
                       AND EXISTS (
                           SELECT 1 FROM pedido p
                           WHERE p.id_pedido = pago.id_pedido
                             AND p.estado_pedido <> 'cancelado'
                       )"
                );

                $update->execute(['id' => $idPedido]);

                if ($update->rowCount() !== 1) {
                    throw new RuntimeException(
                        'El pedido fue cancelado o cambió de estado.'
                    );
                }

                $this->informar(
                    'aprobado_prueba',
                    'Pago de prueba verificado correctamente.'
                );
            }

            $this->informar(
                $estado,
                $estado === 'pendiente'
                    ? 'El pago continúa pendiente.'
                    : 'El pago no fue aprobado.'
            );

        } catch (Throwable $e) {
            error_log('Fruta Madre Orders TEST resultado: ' . $e->getMessage());

            $this->informar(
                'error',
                'No fue posible verificar el pago.'
            );
        }
    }
}
