<?php
require_once __DIR__ . '/../models/ProductoStitchModel.php';
/** CRUD del catálogo de Stitch; aislado de los controladores de Roberto y Mercado Pago. */
final class ProductosStitchController {
    private ProductoStitchModel $modelo;
    public function __construct() {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $this->verificarAdmin();
        if (empty($_SESSION['csrf_stitch'])) $_SESSION['csrf_stitch']=bin2hex(random_bytes(32));
        $this->modelo=new ProductoStitchModel();
    }
    private function verificarAdmin():void {
        if (empty($_SESSION['usuario']['id_usuario'])) {header('Location: index.php?accion=login',true,303);exit;}
        if (($_SESSION['usuario']['rol']??'')!=='admin') {http_response_code(403);exit('Acceso exclusivo al administrador.');}
    }
    private function csrf():void {
        if (($_SERVER['REQUEST_METHOD']??'')!=='POST' || !is_string($_POST['csrf_token']??null) ||
            !hash_equals($_SESSION['csrf_stitch']??'',$_POST['csrf_token'])) {
            http_response_code(419);exit('Solicitud no autorizada: recarga la pantalla.');
        }
    }
    private function mensaje(string $tipo,string $texto):void {$_SESSION['stitch_mensaje']=['tipo'=>$tipo,'texto'=>$texto];}
    private function volver():never {header('Location: index.php?accion=admin-productos',true,303);exit;}
    public function index():void {
        $f=['q'=>mb_substr(trim((string)($_GET['q']??'')),0,120),
            'categoria'=>(string)($_GET['categoria']??''),'estado'=>(string)($_GET['estado']??''),
            'orden'=>(string)($_GET['orden']??'recientes')];
        $productos=$this->modelo->productos($f);
        $categorias=$this->modelo->categorias();
        $indicadores=$this->modelo->indicadores();
        $editar=(int)($_GET['editar']??0);
        $productoEditar=$editar>0?$this->modelo->producto($editar):false;
        $pagina=max(1,(int)($_GET['pagina']??1));
        $porPagina=10;$total=count($productos);$paginas=max(1,(int)ceil($total/$porPagina));
        $pagina=min($pagina,$paginas);
        $productosPagina=array_slice($productos,($pagina-1)*$porPagina,$porPagina);
        $mensaje=$_SESSION['stitch_mensaje']??null;unset($_SESSION['stitch_mensaje']);
        $csrfToken=$_SESSION['csrf_stitch'];
        require __DIR__ . '/../views/admin/stitch_productos.php';
    }
    public function guardar():never {
        $this->csrf();
        try {
            $id=filter_var($_POST['id_producto']??null,FILTER_VALIDATE_INT);
            $id=$id>0?(int)$id:null;
            $anterior=$id?$this->modelo->producto($id):false;
            if ($id && !$anterior) throw new InvalidArgumentException('Producto inexistente.');
            $nombre=trim((string)($_POST['nombre']??''));
            $sku=trim((string)($_POST['sku']??''));
            if ($sku==='') $sku='FM-'.strtoupper(bin2hex(random_bytes(5)));
            $categoria=filter_var($_POST['id_categoria']??null,FILTER_VALIDATE_INT);
            $precio=filter_var($_POST['precio']??null,FILTER_VALIDATE_FLOAT);
            $costo=filter_var($_POST['costo']??'0',FILTER_VALIDATE_FLOAT);
            $stock=filter_var($_POST['stock_disponible']??'0',FILTER_VALIDATE_INT);
            $minimo=filter_var($_POST['stock_minimo']??'0',FILTER_VALIDATE_INT);
            if (!$categoria || mb_strlen($nombre)<2 || mb_strlen($nombre)>255 || mb_strlen($sku)>60 ||
                !preg_match('/^[a-zA-Z0-9._\-]+$/',$sku) ||
                $precio===false || $precio<0 || $precio>99999999 ||
                $costo===false || $costo<0 || $costo>99999999 ||
                $stock===false || $stock<0 || $minimo===false || $minimo<0) {
                throw new InvalidArgumentException('Revisa nombre, SKU, categoría, precio, costo y stock.');
            }
            $imagen=$anterior['imagen_url']??null;
            if (isset($_POST['quitar_imagen'])) $imagen=null;
            if (isset($_FILES['imagen']) && ($_FILES['imagen']['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_NO_FILE) {
                $f=$_FILES['imagen'];
                if (($f['error']??null)!==UPLOAD_ERR_OK || ($f['size']??0)>5*1024*1024) throw new InvalidArgumentException('Imagen demasiado grande o inválida (máximo 5 MB).');
                $mime=(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
                $ext=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'][$mime]??null;
                if (!$ext || !@getimagesize($f['tmp_name'])) throw new InvalidArgumentException('Solo se permiten imágenes JPG, PNG y WEBP.');
                $dir=dirname(__DIR__,2).'/public/images/productos';
                if (!is_dir($dir) && !mkdir($dir,0755,true) && !is_dir($dir)) throw new RuntimeException('No se pudo crear la carpeta de fotografías.');
                $nombreArchivo='producto_'.bin2hex(random_bytes(12)).'.'.$ext;
                if (!move_uploaded_file($f['tmp_name'],$dir.'/'.$nombreArchivo)) throw new RuntimeException('No se pudo guardar la fotografía.');
                $imagen='index.php?accion=imagen-producto-stitch&f='.$nombreArchivo;
            }
            $d=['cat'=>(int)$categoria,'sku'=>$sku,'nombre'=>$nombre,
                'descripcion'=>mb_substr(trim((string)($_POST['descripcion']??'')),0,10000),
                'ingredientes'=>mb_substr(trim((string)($_POST['ingredientes']??'')),0,10000),
                'precio'=>number_format((float)$precio,2,'.',''),'costo'=>number_format((float)$costo,2,'.',''),
                'stock'=>(int)$stock,'minimo'=>(int)$minimo,
                'subcat'=>mb_substr(trim((string)($_POST['subcategoria']??'')),0,255),
                'activo'=>($_POST['activo']??'1')==='1'?'true':'false',
                'paquetes'=>isset($_POST['permite_paquetes'])?'true':'false','imagen'=>$imagen];
            $idGuardado=$this->modelo->guardar($id,$d);
            $this->mensaje('exito','Producto #'.$idGuardado.' guardado en PostgreSQL.');
        } catch (Throwable $e) {
            error_log('Stitch productos: '.$e->getMessage());
            $this->mensaje('error',$e instanceof InvalidArgumentException?$e->getMessage():'No se pudo guardar. Revisa el SKU duplicado y que la migración esté aplicada.');
        }
        $this->volver();
    }
    public function desactivar():never {
        $this->csrf();
        $id=filter_var($_POST['id_producto']??null,FILTER_VALIDATE_INT);
        try {
            if (!$id || !$this->modelo->desactivar((int)$id)) throw new RuntimeException('El producto ya está inactivo o no existe.');
            $this->mensaje('exito','Producto dado de baja del catálogo. Se conserva su historial.');
        } catch(Throwable $e) {$this->mensaje('error',$e->getMessage());}
        $this->volver();
    }
    public function exportar():never {
        $f=['q'=>trim((string)($_GET['q']??'')),'categoria'=>(string)($_GET['categoria']??''),
            'estado'=>(string)($_GET['estado']??''),'orden'=>(string)($_GET['orden']??'recientes')];
        $productos=$this->modelo->productos($f);
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="frutamadre-productos-'.date('Y-m-d').'.csv"');
        $out=fopen('php://output','w');fwrite($out,"\xEF\xBB\xBF");
        fputcsv($out,['SKU','Producto','Categoría','Precio','Costo','Stock','Stock mínimo','Activo']);
        foreach($productos as $p) fputcsv($out,[$p['sku'],$p['nombre'],$p['nombre_categoria'],$p['precio'],$p['costo'],
            $p['stock_disponible'],$p['stock_minimo'],$p['activo']]);
        fclose($out);exit;
    }
}
