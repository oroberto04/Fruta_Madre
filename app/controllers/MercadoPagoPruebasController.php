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
        if (!$usuario || !$pedidoId || $pedidoId !== $solicitado ||
            ($_SESSION['pedido_creado']['metodo_pago'] ?? '') !== 'mercadopago_prueba') {
            throw new RuntimeException('No hay pedido de prueba autorizado en la sesión.');
        }
        $db = (new Database())->connect();
        $stmt = $db->prepare('SELECT p.id_pedido, p.total, p.estado_pedido, pg.estado_pago
            FROM pedido p INNER JOIN pago pg ON pg.id_pedido = p.id_pedido
            WHERE p.id_pedido = :pedido AND p.id_usuario = :usuario AND pg.metodo_pago = :metodo');
        $stmt->execute(['pedido' => $pedidoId, 'usuario' => $usuario, 'metodo' => 'mercadopago_prueba']);
        $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$pedido) throw new RuntimeException('El pedido no pertenece a esta cuenta.');
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

    public function iniciar(): never
    {
        try {
            $pedido = $this->pedidoDeSesion();
            if ($pedido['estado_pedido'] === 'cancelado') throw new RuntimeException('Pedido cancelado.');
            if ($pedido['estado_pago'] === 'aprobado_prueba') {
                $this->informar('aprobado_prueba', 'El pago de prueba ya fue verificado.');
            }
            $idPedido = (int) $pedido['id_pedido'];
            $centavos = (int) round((float) $pedido['total'] * 100);
            if (!isset($_SESSION['mp_order_test'][$idPedido])) {
                $bytes = random_bytes(16);
                $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
                $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
                $h = bin2hex($bytes);
                $uuid = substr($h,0,8).'-'.substr($h,8,4).'-'.substr($h,12,4).'-'.substr($h,16,4).'-'.substr($h,20);
                $_SESSION['mp_order_test'][$idPedido] = ['key' => $uuid];
            }
            $dato = &$_SESSION['mp_order_test'][$idPedido];
            if (!isset($dato['id'], $dato['url'])) {
                $order = MercadoPagoPruebas::crearOrder($idPedido, $centavos, $dato['key']);
                $dato['id'] = $order['id'];
                $dato['url'] = $order['url'];
            }
            header('Location: ' . $dato['url'], true, 303);
            exit;
        } catch (Throwable $e) {
            error_log('Fruta Madre Orders TEST inicio: ' . $e->getMessage());
            $this->informar('error', 'No se pudo iniciar el checkout de prueba. Revisa URL pública, credenciales y configuración.');
        }
    }

    public function resultado(): never
    {
        try {
            $pedido = $this->pedidoDeSesion();
            if ($pedido['estado_pago'] === 'aprobado_prueba') {
                $this->informar('aprobado_prueba', 'El pago de prueba ya fue verificado.');
            }
            if ($pedido['estado_pedido'] === 'cancelado') throw new RuntimeException('Pedido cancelado.');
            $idPedido = (int) $pedido['id_pedido'];
            $orderId = $_SESSION['mp_order_test'][$idPedido]['id'] ?? null;
            if (!is_string($orderId)) {
                $this->informar('pendiente', 'No hay una order vinculada a esta sesión.');
            }
            if (isset($_GET['order_id']) && $_GET['order_id'] !== $orderId) {
                throw new RuntimeException('ID de order de retorno no coincide.');
            }
            // No confiar en los parámetros status/retorno enviados por el navegador.
            $order = MercadoPagoPruebas::consultarOrder($orderId);
            $centavos = (int) round((float) $pedido['total'] * 100);
            $estado = MercadoPagoPruebas::validarRespuestaOrder($order, $orderId, $idPedido, $centavos);
            if ($estado === 'aprobado_prueba') {
                // La actualización es condicional para evitar aprobar pedidos cancelados
                // entre la consulta inicial y la respuesta de Mercado Pago.
                $stmt = $pedido['db']->prepare("UPDATE pago SET estado_pago = 'aprobado_prueba',
                    monto_pagado = 0, fecha_pago = CURRENT_TIMESTAMP
                    WHERE id_pedido = :id AND metodo_pago = 'mercadopago_prueba' AND estado_pago = 'pendiente'
                      AND EXISTS (SELECT 1 FROM pedido p WHERE p.id_pedido = pago.id_pedido
                                  AND p.estado_pedido <> 'cancelado')");
                $stmt->execute(['id' => $idPedido]);
                if ($stmt->rowCount() !== 1) {
                    throw new RuntimeException('El pedido fue cancelado o el pago cambió de estado antes de confirmarse.');
                }
                $this->informar('aprobado_prueba', 'Order de prueba verificada por Mercado Pago. No se registró un cobro real.');
            }
            $this->informar($estado, $estado === 'pendiente' ? 'La order sigue pendiente.' : 'La order no fue aprobada.');
        } catch (Throwable $e) {
            error_log('Fruta Madre Orders TEST resultado: ' . $e->getMessage());
            $this->informar('error', 'No fue posible verificar la order. El pedido no se marcó como pagado.');
        }
    }
}
