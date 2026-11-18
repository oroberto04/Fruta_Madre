<?php
require_once __DIR__ . '/../config/database.php';
/** Módulos secundarios de la navegación Stitch; consultas y acciones con BD real. */
final class OperacionesStitchController {
    private PDO $db;
    private const MODULOS=['admin-categorias-stitch'=>'categorias','admin-paquetes-stitch'=>'paquetes',
       'admin-carritos-stitch'=>'carritos','admin-reservas-stitch'=>'reservas','admin-clientes-stitch'=>'clientes',
       'admin-inventario-stitch'=>'inventario','admin-reportes-stitch'=>'reportes'];
    public function __construct(){
        if(session_status()!==PHP_SESSION_ACTIVE)session_start();
        if(empty($_SESSION['usuario']['id_usuario'])){header('Location: index.php?accion=login',true,303);exit;}
        if(($_SESSION['usuario']['rol']??'')!=='admin'){http_response_code(403);exit('Administración restringida.');}
        if(empty($_SESSION['csrf_stitch']))$_SESSION['csrf_stitch']=bin2hex(random_bytes(32));
        $this->db=(new Database())->connect();
    }
    private function comprobarPost():void{
        if(($_SERVER['REQUEST_METHOD']??'')!=='POST' || !is_string($_POST['csrf_token']??null) ||
        !hash_equals($_SESSION['csrf_stitch']??'',$_POST['csrf_token'])){http_response_code(419);exit('Formulario no autorizado.');}
    }
    private function ir(string $modulo,string $tipo,string $mensaje):never{
        $_SESSION['stitch_mensaje']=['tipo'=>$tipo,'texto'=>$mensaje];
        header('Location: index.php?accion=admin-'.$modulo.'-stitch',true,303);exit;
    }
    public function index(string $ruta):void{
        if(!isset(self::MODULOS[$ruta])){http_response_code(404);exit('Módulo no disponible.');}
        $modulo=self::MODULOS[$ruta];$filas=[];$resumen=[];
        $buscar=trim((string)($_GET['q']??''));
        switch($modulo){
            case 'categorias':
                $filas=$this->db->query('SELECT c.id_categoria,c.nombre_categoria,c.descripcion,c.activa,COUNT(p.id_producto) productos FROM categoria c LEFT JOIN producto p USING(id_categoria) GROUP BY c.id_categoria ORDER BY c.id_categoria')->fetchAll(PDO::FETCH_ASSOC);
                break;
            case 'paquetes':
                $filas=$this->db->query('SELECT p.id_paquete,p.nombre_paquete,p.descripcion,p.capacidad_personas,p.duracion_horas,p.precio_base,COUNT(e.id_evento) eventos FROM paquete p LEFT JOIN evento e USING(id_paquete) GROUP BY p.id_paquete ORDER BY p.id_paquete')->fetchAll(PDO::FETCH_ASSOC);
                break;
            case 'carritos':
                $filas=$this->db->query('SELECT c.id_carrito,c.codigo_inventario,c.estado_fisico,COUNT(r.id_reserva) reservas FROM carrito_snack c LEFT JOIN reserva r USING(id_carrito) GROUP BY c.id_carrito ORDER BY c.id_carrito')->fetchAll(PDO::FETCH_ASSOC);
                break;
            case 'reservas':
                $filas=$this->db->query('SELECT r.id_reserva,r.estado_reserva,r.costo_total,e.tipo_evento,e.fecha_hora,e.lugar_direccion,e.num_invitados,u.nombre_completo,u.correo,p.nombre_paquete,cs.codigo_inventario FROM reserva r JOIN evento e USING(id_evento) JOIN usuario u ON u.id_usuario=e.id_usuario JOIN paquete p ON p.id_paquete=e.id_paquete LEFT JOIN carrito_snack cs USING(id_carrito) ORDER BY e.fecha_hora DESC')->fetchAll(PDO::FETCH_ASSOC);
                break;
            case 'clientes':
                $filas=$this->db->query("SELECT u.id_usuario,u.nombre_completo,u.correo,u.telefono,u.fecha_registro,COUNT(p.id_pedido) pedidos FROM usuario u LEFT JOIN pedido p USING(id_usuario) WHERE u.rol='cliente' GROUP BY u.id_usuario ORDER BY u.fecha_registro DESC")->fetchAll(PDO::FETCH_ASSOC);
                break;
            case 'inventario':
                $filas=$this->db->query('SELECT p.id_producto,p.sku,p.nombre,p.stock_disponible,p.stock_minimo,p.activo,c.nombre_categoria FROM producto p JOIN categoria c USING(id_categoria) ORDER BY p.stock_disponible ASC,p.nombre')->fetchAll(PDO::FETCH_ASSOC);
                break;
            case 'reportes':
                $filas=$this->db->query("SELECT p.id_pedido,p.fecha_pedido,p.estado_pedido,p.total,u.nombre_completo,pg.metodo_pago,pg.estado_pago FROM pedido p JOIN usuario u ON u.id_usuario=p.id_usuario LEFT JOIN pago pg ON pg.id_pedido=p.id_pedido ORDER BY p.fecha_pedido DESC LIMIT 100")->fetchAll(PDO::FETCH_ASSOC);
                $resumen=$this->db->query("SELECT (SELECT COUNT(*) FROM pedido) pedidos, (SELECT COUNT(*) FROM usuario WHERE rol='cliente') clientes, (SELECT COALESCE(SUM(p.total),0) FROM pedido p JOIN pago pg USING(id_pedido) WHERE pg.estado_pago='pagado' AND pg.metodo_pago <> 'mercadopago_prueba') ingresos_confirmados, (SELECT COUNT(*) FROM reserva) reservas")->fetch(PDO::FETCH_ASSOC);
                break;
        }
        if($buscar!==''){$filas=array_values(array_filter($filas,static fn($x)=>mb_stripos(implode(' ',array_map('strval',$x)),$buscar)!==false));}
        $mensaje=$_SESSION['stitch_mensaje']??null;unset($_SESSION['stitch_mensaje']);
        $csrfToken=$_SESSION['csrf_stitch'];
        require __DIR__.'/../views/admin/stitch_operaciones.php';
    }
    public function guardar(string $tipo):never{
        $this->comprobarPost();
        $modulo=['categoria'=>'categorias','paquete'=>'paquetes','carrito'=>'carritos','inventario'=>'inventario','reserva'=>'reservas'][$tipo]??'categorias';
        try {
            $id=filter_var($_POST['id']??null,FILTER_VALIDATE_INT);
            if($tipo==='categoria'){
                $nombre=trim((string)($_POST['nombre']??''));$desc=trim((string)($_POST['descripcion']??''));
                if(mb_strlen($nombre)<2||mb_strlen($nombre)>255)throw new InvalidArgumentException('Nombre de categoría inválido.');
                if($id){$q=$this->db->prepare('UPDATE categoria SET nombre_categoria=:n,descripcion=:d,activa=:a WHERE id_categoria=:id');$q->execute(['n'=>$nombre,'d'=>$desc,'a'=>(($_POST['activa']??'1')==='1'?'true':'false'),'id'=>$id]);}
                else{$q=$this->db->prepare('INSERT INTO categoria(nombre_categoria,descripcion,activa) VALUES(:n,:d,TRUE)');$q->execute(['n'=>$nombre,'d'=>$desc]);}
            } elseif($tipo==='paquete'){
                $nombre=trim((string)($_POST['nombre']??''));$desc=trim((string)($_POST['descripcion']??''));
                $cap=filter_var($_POST['capacidad']??null,FILTER_VALIDATE_INT);$dur=filter_var($_POST['duracion']??null,FILTER_VALIDATE_INT);$precio=filter_var($_POST['precio']??null,FILTER_VALIDATE_FLOAT);
                if(mb_strlen($nombre)<2||mb_strlen($nombre)>255||$cap===false||$cap<1||$dur===false||$dur<1||$precio===false||$precio<0)throw new InvalidArgumentException('Revisa nombre, capacidad, duración y precio.');
                $d=['n'=>$nombre,'d'=>$desc,'cap'=>$cap,'dur'=>$dur,'precio'=>number_format($precio,2,'.','')];
                if($id){$q=$this->db->prepare('UPDATE paquete SET nombre_paquete=:n,descripcion=:d,capacidad_personas=:cap,duracion_horas=:dur,precio_base=:precio WHERE id_paquete=:id');$q->execute($d+['id'=>$id]);}
                else{$q=$this->db->prepare('INSERT INTO paquete(nombre_paquete,descripcion,capacidad_personas,duracion_horas,precio_base) VALUES(:n,:d,:cap,:dur,:precio)');$q->execute($d);}
            } elseif($tipo==='carrito'){
                $codigo=trim((string)($_POST['codigo']??''));$estado=(string)($_POST['estado']??'disponible');
                if(mb_strlen($codigo)<2||mb_strlen($codigo)>50||!in_array($estado,['disponible','mantenimiento','ocupado','inactivo'],true))throw new InvalidArgumentException('Código o estado de carrito inválido.');
                if($id){$q=$this->db->prepare('UPDATE carrito_snack SET codigo_inventario=:c,estado_fisico=:e WHERE id_carrito=:id');$q->execute(['c'=>$codigo,'e'=>$estado,'id'=>$id]);}
                else{$q=$this->db->prepare('INSERT INTO carrito_snack(codigo_inventario,estado_fisico) VALUES(:c,:e)');$q->execute(['c'=>$codigo,'e'=>$estado]);}
            } elseif($tipo==='inventario'){
                $stock=filter_var($_POST['stock']??null,FILTER_VALIDATE_INT);$min=filter_var($_POST['minimo']??null,FILTER_VALIDATE_INT);
                if(!$id||$stock===false||$stock<0||$min===false||$min<0)throw new InvalidArgumentException('Indica producto y cantidades válidas.');
                $q=$this->db->prepare('UPDATE producto SET stock_disponible=:s,stock_minimo=:m,actualizado_en=CURRENT_TIMESTAMP WHERE id_producto=:id');$q->execute(['s'=>$stock,'m'=>$min,'id'=>$id]);
            } elseif($tipo==='reserva'){
                $estado=(string)($_POST['estado']??'');
                if(!$id||!in_array($estado,['pendiente','confirmada','en_proceso','completada','cancelada'],true))throw new InvalidArgumentException('Reserva o estado inválidos.');
                $q=$this->db->prepare('UPDATE reserva SET estado_reserva=:e WHERE id_reserva=:id');$q->execute(['e'=>$estado,'id'=>$id]);
            } else {throw new InvalidArgumentException('Acción desconocida.');}
            if($id && $q->rowCount()===0)throw new RuntimeException('No se encontró el registro o no hubo cambios.');
            $this->ir($modulo,'exito','Cambios guardados en PostgreSQL.');
        } catch(Throwable $e) {error_log('Operación Stitch: '.$e->getMessage());$this->ir($modulo,'error',($e instanceof InvalidArgumentException||$e instanceof RuntimeException)?$e->getMessage():'No se pudo guardar el registro.');}
    }
    public function reporteCSV():never{
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="frutamadre-pedidos-'.date('Y-m-d').'.csv"');
        $out=fopen('php://output','w');fwrite($out,"\xEF\xBB\xBF");
        fputcsv($out,['Pedido','Fecha','Estado pedido','Total MXN','Método pago','Estado pago']);
        $r=$this->db->query('SELECT p.id_pedido,p.fecha_pedido,p.estado_pedido,p.total,pg.metodo_pago,pg.estado_pago FROM pedido p LEFT JOIN pago pg USING(id_pedido) ORDER BY p.fecha_pedido DESC');
        while($x=$r->fetch(PDO::FETCH_NUM)) fputcsv($out,$x);
        fclose($out);exit;
    }
}
