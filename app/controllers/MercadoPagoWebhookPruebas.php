<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/MercadoPagoPruebas.php';

final class MercadoPagoWebhookPruebas
{
    private static function responder(int $codigo, array $datos): never
    {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($datos, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function atender(): never
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            self::responder(405, ['error' => 'Método no permitido']);
        }

        $secret = trim((string) getenv('MP_WEBHOOK_SECRET_TEST'));

        if (
            $secret === '' ||
            !MercadoPagoPruebas::habilitado()
        ) {
            self::responder(503, ['error' => 'Mercado Pago TEST no configurado']);
        }

        $firma = (string) ($_SERVER['HTTP_X_SIGNATURE'] ?? '');
        $requestId = (string) ($_SERVER['HTTP_X_REQUEST_ID'] ?? '');

        $partes = [];

        foreach (explode(',', $firma) as $parte) {
            $par = explode('=', trim($parte), 2);

            if (count($par) === 2) {
                $partes[trim($par[0])] = trim($par[1]);
            }
        }

        $ts = $partes['ts'] ?? '';
        $hash = $partes['v1'] ?? '';

        $cuerpo = json_decode(
            (string) file_get_contents('php://input'),
            true
        );

        $id = (string) (
            $_GET['data_id']
            ?? $_GET['data.id']
            ?? (is_array($cuerpo) ? ($cuerpo['data']['id'] ?? '') : '')
        );

        if (
            $requestId === '' ||
            !ctype_digit($ts) ||
            !preg_match('/^[a-f0-9]{64}$/i', $hash) ||
            $id === '' ||
            abs(time() - ((int) $ts / 1000)) > 600
        ) {
            self::responder(401, ['error' => 'Firma inválida']);
        }

        $manifest = 'id:' . strtolower($id)
            . ';request-id:' . $requestId
            . ';ts:' . $ts . ';';

        $esperada = hash_hmac('sha256', $manifest, $secret);

        if (!hash_equals($esperada, strtolower($hash))) {
            self::responder(401, ['error' => 'Firma inválida']);
        }

        // Este receptor procesa exclusivamente notificaciones de Orders.
        if (!preg_match('/^ORD[A-Z0-9]+$/i', $id)) {
            self::responder(200, [
                'recibido' => true,
                'procesado' => false,
                'motivo' => 'Evento ajeno a Orders TEST'
            ]);
        }

        try {
            $db = (new Database())->connect();

            $stmt = $db->prepare(
                'SELECT m.id_pedido, m.order_id,
                        p.total, p.estado_pedido,
                        pg.estado_pago
                 FROM mp_orders_test m
                 INNER JOIN pedido p
                    ON p.id_pedido = m.id_pedido
                 INNER JOIN pago pg
                    ON pg.id_pedido = p.id_pedido
                 WHERE m.order_id = :order_id
                   AND pg.metodo_pago = :metodo'
            );

            $stmt->execute([
                'order_id' => $id,
                'metodo' => 'mercadopago_prueba'
            ]);

            $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$pedido) {
                self::responder(200, [
                    'recibido' => true,
                    'procesado' => false,
                    'motivo' => 'Orden no registrada en Fruta Madre TEST'
                ]);
            }

            if ($pedido['estado_pedido'] === 'cancelado') {
                self::responder(200, [
                    'recibido' => true,
                    'pago_confirmado' => false,
                    'motivo' => 'Pedido cancelado'
                ]);
            }

            if ($pedido['estado_pago'] === 'aprobado_prueba') {
                self::responder(200, [
                    'recibido' => true,
                    'pago_confirmado' => true,
                    'motivo' => 'Pago previamente verificado'
                ]);
            }

            $idPedido = (int) $pedido['id_pedido'];
            $centavos = (int) round((float) $pedido['total'] * 100);

            // La firma prueba la procedencia de la notificación,
            // pero NO prueba que el pago esté aprobado.
            $order = MercadoPagoPruebas::consultarOrder($id);

            $estado = MercadoPagoPruebas::validarRespuestaOrder(
                $order,
                $id,
                $idPedido,
                $centavos
            );

            if ($estado !== 'aprobado_prueba') {
                self::responder(200, [
                    'recibido' => true,
                    'pago_confirmado' => false,
                    'estado' => $estado
                ]);
            }

            // Solo actualizar pagos pendientes de pedidos no cancelados.
            $update = $db->prepare(
                "UPDATE pago
                 SET estado_pago = 'aprobado_prueba',
                     monto_pagado = 0,
                     fecha_pago = CURRENT_TIMESTAMP
                 WHERE id_pedido = :id
                   AND metodo_pago = 'mercadopago_prueba'
                   AND estado_pago = 'pendiente'
                   AND EXISTS (
                       SELECT 1
                       FROM pedido p
                       WHERE p.id_pedido = pago.id_pedido
                         AND p.estado_pedido <> 'cancelado'
                   )"
            );

            $update->execute(['id' => $idPedido]);

            if ($update->rowCount() === 1) {
                self::responder(200, [
                    'recibido' => true,
                    'pago_confirmado' => true,
                    'tipo' => 'prueba'
                ]);
            }

            // Puede haberse confirmado simultáneamente desde el retorno.
            $verificar = $db->prepare(
                "SELECT pg.estado_pago, p.estado_pedido
                 FROM pago pg
                 INNER JOIN pedido p ON p.id_pedido = pg.id_pedido
                 WHERE pg.id_pedido = :id
                   AND pg.metodo_pago = 'mercadopago_prueba'"
            );

            $verificar->execute(['id' => $idPedido]);
            $actual = $verificar->fetch(PDO::FETCH_ASSOC);

            $confirmado = $actual
                && $actual['estado_pago'] === 'aprobado_prueba'
                && $actual['estado_pedido'] !== 'cancelado';

            self::responder(200, [
                'recibido' => true,
                'pago_confirmado' => (bool) $confirmado
            ]);

        } catch (Throwable $e) {
            error_log(
                'Fruta Madre webhook Mercado Pago TEST: ' . $e->getMessage()
            );

            // Permite que Mercado Pago reintente ante fallos temporales.
            self::responder(503, [
                'error' => 'No se pudo verificar la orden de prueba'
            ]);
        }
    }
}
