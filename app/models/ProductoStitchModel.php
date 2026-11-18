<?php
/** Operaciones reales del panel Stitch. No afecta el modelo de tienda de Roberto. */
require_once __DIR__ . '/../config/database.php';
final class ProductoStitchModel {
    private PDO $db;
    public function __construct() { $this->db = (new Database())->connect(); }
    public function conexion(): PDO { return $this->db; }
    public function categorias(): array {return $this->db->query('SELECT id_categoria,nombre_categoria,activa FROM categoria ORDER BY nombre_categoria')->fetchAll(PDO::FETCH_ASSOC);}
    public function producto(int $id): array|false {
        $q=$this->db->prepare('SELECT p.*,c.nombre_categoria FROM producto p JOIN categoria c USING(id_categoria) WHERE p.id_producto=:id');
        $q->execute(['id'=>$id]);return $q->fetch(PDO::FETCH_ASSOC);
    }
    public function productos(array $f): array {
        $where=[];$p=[];
        if (($f['q']??'')!=='') {$where[]='(p.nombre ILIKE :q OR p.sku ILIKE :q OR p.ingredientes ILIKE :q)';$p['q']='%'.$f['q'].'%';}
        if (($f['categoria']??'')!=='' && ctype_digit((string)$f['categoria'])) {$where[]='p.id_categoria=:cat';$p['cat']=(int)$f['categoria'];}
        $estado=$f['estado']??'';
        if ($estado==='disponible') $where[]='p.activo=TRUE AND p.stock_disponible>p.stock_minimo';
        if ($estado==='bajo') $where[]='p.activo=TRUE AND p.stock_disponible>0 AND p.stock_disponible<=p.stock_minimo';
        if ($estado==='agotado') $where[]='p.activo=TRUE AND p.stock_disponible=0';
        if ($estado==='inactivo') $where[]='p.activo=FALSE';
        $sort=['recientes'=>'p.id_producto DESC','precio_asc'=>'p.precio ASC,p.id_producto DESC',
            'precio_desc'=>'p.precio DESC,p.id_producto DESC','stock'=>'p.stock_disponible ASC,p.id_producto DESC',
            'margen'=>'(p.precio-p.costo) DESC,p.id_producto DESC','nombre'=>'p.nombre ASC'];
        $orden=$sort[$f['orden']??'recientes']??$sort['recientes'];
        $sql='SELECT p.*,c.nombre_categoria FROM producto p JOIN categoria c ON c.id_categoria=p.id_categoria'
            .($where?' WHERE '.implode(' AND ',$where):'').' ORDER BY '.$orden;
        $q=$this->db->prepare($sql);$q->execute($p);return $q->fetchAll(PDO::FETCH_ASSOC);
    }
    public function indicadores(): array {
        return $this->db->query("SELECT COUNT(*) FILTER(WHERE activo=TRUE) activos,
          COUNT(*) FILTER(WHERE activo=TRUE AND stock_disponible>0 AND stock_disponible<=stock_minimo) bajo,
          COUNT(*) FILTER(WHERE activo=TRUE AND stock_disponible=0) agotados,
          COUNT(*) total FROM producto")->fetch(PDO::FETCH_ASSOC);
    }
    public function guardar(?int $id, array $d): int {
        if ($id!==null) {
            $q=$this->db->prepare('UPDATE producto SET id_categoria=:cat, sku=:sku,nombre=:nombre,descripcion=:descripcion,
                ingredientes=:ingredientes,precio=:precio,costo=:costo,stock_disponible=:stock,stock_minimo=:minimo,
                subcategoria=:subcat,activo=:activo,permite_paquetes=:paquetes,imagen_url=:imagen,actualizado_en=CURRENT_TIMESTAMP
                WHERE id_producto=:id');
            $q->execute($d+['id'=>$id]);if ($q->rowCount()!==1) throw new RuntimeException('No se actualizó el producto');return $id;
        }
        $q=$this->db->prepare('INSERT INTO producto (id_categoria,sku,nombre,descripcion,ingredientes,precio,costo,
          stock_disponible,stock_minimo,subcategoria,activo,permite_paquetes,imagen_url,actualizado_en)
          VALUES (:cat,:sku,:nombre,:descripcion,:ingredientes,:precio,:costo,:stock,:minimo,:subcat,:activo,:paquetes,:imagen,CURRENT_TIMESTAMP)
          RETURNING id_producto');
        $q->execute($d);return (int)$q->fetchColumn();
    }
    public function desactivar(int $id): bool {
        $q=$this->db->prepare('UPDATE producto SET activo=FALSE,actualizado_en=CURRENT_TIMESTAMP WHERE id_producto=:id AND activo=TRUE');
        $q->execute(['id'=>$id]);return $q->rowCount()===1;
    }
}
