<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/MercadoPagoPruebas.php';

final class ConciliadorMercadoPagoPruebas
{
    public static function conciliar(int $idPedido): string
    {
        if ($idPedido <= 0 || !MercadoPagoPruebas::habilitado()) {
            return 'no_configurado';
        }
        $db = (new Database())->connect();
        $stmt = $db->prepare("SELECT p.id_pedido, p.total, p.estado_pedido, pg.estado_pago, m.order_id
            FROM pedido p JOIN pago pg ON pg.id_pedido=p.id_pedido
            JOIN mp_orders_test m ON m.id_pedido=p.id_pedido
            WHERE p.id_pedido=:id AND pg.metodo_pago='mercadopago_prueba'");
        $stmt->execute(['id'=>$idPedido]);
        $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$pedido || $pedido['estado_pedido']==='cancelado') return 'omitido';
        if ($pedido['estado_pago']==='aprobado_prueba') return 'aprobado_prueba';
        if ($pedido['estado_pago']!=='pendiente' || !$pedido['order_id']) return 'omitido';
        $order = MercadoPagoPruebas::consultarOrder((string)$pedido['order_id']);
        $estado = MercadoPagoPruebas::validarRespuestaOrder($order, (string)$pedido['order_id'], $idPedido, (int)round((float)$pedido['total']*100));
        if ($estado!=='aprobado_prueba') return $estado;
        $update=$db->prepare("UPDATE pago SET estado_pago='aprobado_prueba', monto_pagado=0, fecha_pago=CURRENT_TIMESTAMP
            WHERE id_pedido=:id AND metodo_pago='mercadopago_prueba' AND estado_pago='pendiente'
            AND EXISTS (SELECT 1 FROM pedido p WHERE p.id_pedido=pago.id_pedido AND p.estado_pedido<>'cancelado')");
        $update->execute(['id'=>$idPedido]);
        return $update->rowCount()===1 ? 'aprobado_prueba' : 'sin_cambios';
    }

    public static function pendientes(?int $usuario=null, int $limite=20): array
    {
        $db=(new Database())->connect();
        $sql="SELECT p.id_pedido FROM pedido p JOIN pago pg ON pg.id_pedido=p.id_pedido
            JOIN mp_orders_test m ON m.id_pedido=p.id_pedido
            WHERE pg.metodo_pago='mercadopago_prueba' AND pg.estado_pago='pendiente'
            AND p.estado_pedido<>'cancelado' AND m.order_id IS NOT NULL";
        if ($usuario!==null) $sql.=" AND p.id_usuario=:usuario";
        $sql.=" ORDER BY p.id_pedido DESC LIMIT ".max(1,min(100,$limite));
        $stmt=$db->prepare($sql);
        $stmt->execute($usuario!==null ? ['usuario'=>$usuario] : []);
        return array_map('intval',$stmt->fetchAll(PDO::FETCH_COLUMN));
    }
}
