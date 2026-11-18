<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/MercadoPagoPruebas.php';
final class PedidosClienteAcciones {
    private static function validar(): array {
        $usuario=(int)($_SESSION['usuario']['id_usuario']??0);
        $id=filter_input(INPUT_POST,'id_pedido',FILTER_VALIDATE_INT);
        $token=(string)($_POST['token']??'');
        if (!$usuario || !$id || $id<=0 || empty($_SESSION['csrf_pedidos']) || !hash_equals($_SESSION['csrf_pedidos'],$token)) {
            http_response_code(403); exit('Solicitud no autorizada.');
        }
        return [$usuario,$id];
    }
    private static function volver(int $id): never {
        header('Location: index.php?accion=mis-pedidos&pedido='.$id,true,303); exit;
    }
    public static function cancelar(): never {
        [$usuario,$id]=self::validar();
        $db=(new Database())->connect();
        try {
            $db->beginTransaction();
            $q=$db->prepare('SELECT p.estado_pedido, pg.estado_pago, pg.metodo_pago, m.order_id FROM pedido p INNER JOIN pago pg ON pg.id_pedido=p.id_pedido LEFT JOIN mp_orders_test m ON m.id_pedido=p.id_pedido WHERE p.id_pedido=:id AND p.id_usuario=:usuario FOR UPDATE OF p, pg');
            $q->execute(['id'=>$id,'usuario'=>$usuario]); $pedido=$q->fetch(PDO::FETCH_ASSOC);
            if (!$pedido || $pedido['estado_pedido']!=='pendiente' || $pedido['estado_pago']!=='pendiente') throw new RuntimeException('Pedido no cancelable.');
            // Nunca cancelar una orden TEST acreditada remotamente.
            if ($pedido['metodo_pago']==='mercadopago_prueba' && !empty($pedido['order_id'])) {
                $order=MercadoPagoPruebas::consultarOrder($pedido['order_id']);
                $total=$db->prepare('SELECT total FROM pedido WHERE id_pedido=:id'); $total->execute(['id'=>$id]);
                $estado=MercadoPagoPruebas::validarRespuestaOrder($order,$pedido['order_id'],$id,(int)round((float)$total->fetchColumn()*100));
                if ($estado==='aprobado_prueba') throw new RuntimeException('El pago ya fue acreditado; solicita asistencia para cancelación y reembolso.');
                // Un checkout abierto puede pagarse posteriormente: no permitir cancelación local sin cancelar la orden remota.
                throw new RuntimeException('Existe un checkout de Mercado Pago activo; contacta al negocio para cancelar la orden de pago.');
            }
            $q=$db->prepare("UPDATE pedido SET estado_pedido='cancelado' WHERE id_pedido=:id AND id_usuario=:usuario AND estado_pedido='pendiente'");
            $q->execute(['id'=>$id,'usuario'=>$usuario]);
            if ($q->rowCount()!==1) throw new RuntimeException('No se pudo cancelar.');
            $db->commit();
            $_SESSION['mensaje_pedidos']='Pedido cancelado correctamente.';
        } catch(Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            $_SESSION['mensaje_pedidos']=$e->getMessage();
        }
        self::volver($id);
    }
    public static function reintentar(): never {
        [$usuario,$id]=self::validar();
        $db=(new Database())->connect();
        $q=$db->prepare("SELECT p.id_pedido FROM pedido p JOIN pago pg ON pg.id_pedido=p.id_pedido WHERE p.id_pedido=:id AND p.id_usuario=:usuario AND p.estado_pedido='pendiente' AND pg.estado_pago='pendiente' AND pg.metodo_pago='mercadopago_prueba'");
        $q->execute(['id'=>$id,'usuario'=>$usuario]);
        if (!$q->fetchColumn()) { $_SESSION['mensaje_pedidos']='No se puede reintentar este pago.'; self::volver($id); }
        $_SESSION['pedido_creado']=['id_pedido'=>$id,'metodo_pago'=>'mercadopago_prueba'];
        header('Location: index.php?accion=iniciar-pago-mp-prueba',true,303); exit;
    }
}