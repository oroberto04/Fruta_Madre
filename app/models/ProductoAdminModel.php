
<?php

require_once __DIR__ . '/../config/database.php';

class ProductoAdminModel
{
    private PDO $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function obtenerProductosAdmin(array $filtros = []): array
    {
        $sql = "
            SELECT
                p.id_producto,
                p.id_categoria,
                p.sku,
                p.nombre,
                p.descripcion,
                p.ingredientes,
                p.precio,
                p.costo,
                p.imagen_url,
                p.stock_disponible,
                p.stock_minimo,
                p.activo,
                p.permite_paquetes,
                p.subcategoria,
                p.actualizado_en,
                c.nombre_categoria
            FROM producto p
            INNER JOIN categoria c
                ON c.id_categoria = p.id_categoria
            WHERE 1 = 1
        ";

        $params = [];

        if (!empty($filtros['busqueda'])) {
            $sql .= "
                AND (
                    p.nombre ILIKE :busqueda
                    OR p.sku ILIKE :busqueda
                    OR p.descripcion ILIKE :busqueda
                )
            ";
            $params[':busqueda'] = '%' . trim($filtros['busqueda']) . '%';
        }

        if (!empty($filtros['categoria'])) {
            $sql .= " AND p.id_categoria = :categoria";
            $params[':categoria'] = (int) $filtros['categoria'];
        }

        if (($filtros['estado'] ?? '') === 'disponible') {
            $sql .= " AND p.activo = TRUE AND p.stock_disponible > p.stock_minimo";
        } elseif (($filtros['estado'] ?? '') === 'poco_stock') {
            $sql .= " AND p.activo = TRUE AND p.stock_disponible > 0
                      AND p.stock_disponible <= p.stock_minimo";
        } elseif (($filtros['estado'] ?? '') === 'agotado') {
            $sql .= " AND p.activo = TRUE AND p.stock_disponible = 0";
        } elseif (($filtros['estado'] ?? '') === 'inactivo') {
            $sql .= " AND p.activo = FALSE";
        }

        $orden = $filtros['orden'] ?? 'recientes';

        switch ($orden) {
            case 'precio_menor':
                $sql .= " ORDER BY p.precio ASC";
                break;
            case 'precio_mayor':
                $sql .= " ORDER BY p.precio DESC";
                break;
            case 'stock':
                $sql .= " ORDER BY p.stock_disponible ASC";
                break;
            case 'margen':
                $sql .= " ORDER BY (p.precio - p.costo) DESC";
                break;
            default:
                $sql .= " ORDER BY p.id_producto DESC";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerCategoriasAdmin(): array
    {
        $sql = "
            SELECT id_categoria, nombre_categoria
            FROM categoria
            WHERE activa = TRUE
            ORDER BY nombre_categoria ASC
        ";

        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerProductoAdminPorId(int $id): ?array
    {
        $sql = "
            SELECT *
            FROM producto
            WHERE id_producto = :id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);

        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        return $producto ?: null;
    }

    public function skuExiste(string $sku, ?int $idExcluir = null): bool
    {
        $sql = "SELECT COUNT(*) FROM producto WHERE sku = :sku";
        $params = [':sku' => $sku];

        if ($idExcluir !== null) {
            $sql .= " AND id_producto <> :id";
            $params[':id'] = $idExcluir;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function crearProducto(array $datos): int
    {
        $sql = "
            INSERT INTO producto (
                id_categoria,
                sku,
                nombre,
                descripcion,
                ingredientes,
                precio,
                costo,
                imagen_url,
                stock_disponible,
                stock_minimo,
                activo,
                permite_paquetes,
                subcategoria,
                actualizado_en
            ) VALUES (
                :id_categoria,
                :sku,
                :nombre,
                :descripcion,
                :ingredientes,
                :precio,
                :costo,
                :imagen_url,
                :stock_disponible,
                :stock_minimo,
                :activo,
                :permite_paquetes,
                :subcategoria,
                CURRENT_TIMESTAMP
            )
            RETURNING id_producto
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($this->parametrosProducto($datos));

        return (int) $stmt->fetchColumn();
    }

    public function actualizarProducto(int $id, array $datos): bool
    {
        $sql = "
            UPDATE producto SET
                id_categoria = :id_categoria,
                sku = :sku,
                nombre = :nombre,
                descripcion = :descripcion,
                ingredientes = :ingredientes,
                precio = :precio,
                costo = :costo,
                imagen_url = :imagen_url,
                stock_disponible = :stock_disponible,
                stock_minimo = :stock_minimo,
                activo = :activo,
                permite_paquetes = :permite_paquetes,
                subcategoria = :subcategoria,
                actualizado_en = CURRENT_TIMESTAMP
            WHERE id_producto = :id
        ";

        $params = $this->parametrosProducto($datos);
        $params[':id'] = $id;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    private function parametrosProducto(array $datos): array
    {
        return [
            ':id_categoria' => (int) $datos['id_categoria'],
            ':sku' => trim($datos['sku']),
            ':nombre' => trim($datos['nombre']),
            ':descripcion' => $datos['descripcion'] ?? null,
            ':ingredientes' => $datos['ingredientes'] ?? null,
            ':precio' => (float) $datos['precio'],
            ':costo' => (float) $datos['costo'],
            ':imagen_url' => $datos['imagen_url'] ?? null,
            ':stock_disponible' => (int) $datos['stock_disponible'],
            ':stock_minimo' => (int) $datos['stock_minimo'],
            ':activo' => (bool) $datos['activo'],
            ':permite_paquetes' => (bool) $datos['permite_paquetes'],
            ':subcategoria' => $datos['subcategoria'] ?? null
        ];
    }

    public function cambiarEstadoProducto(int $id, bool $activo): bool
    {
        $sql = "
            UPDATE producto
            SET activo = :activo,
                actualizado_en = CURRENT_TIMESTAMP
            WHERE id_producto = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':activo' => $activo,
            ':id' => $id
        ]);
    }

    public function eliminarProducto(int $id): bool
    {
        $sql = "
            UPDATE producto
            SET activo = FALSE,
                actualizado_en = CURRENT_TIMESTAMP
            WHERE id_producto = :id
        ";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function obtenerIndicadoresProductos(): array
    {
        $sql = "
            SELECT
                COUNT(*) FILTER (WHERE activo = TRUE) AS activos,
                COUNT(*) FILTER (
                    WHERE activo = TRUE
                    AND stock_disponible > 0
                    AND stock_disponible <= stock_minimo
                ) AS bajo_stock,
                COUNT(*) FILTER (
                    WHERE activo = TRUE
                    AND stock_disponible = 0
                ) AS agotados,
                COUNT(*) FILTER (WHERE activo = FALSE) AS inactivos
            FROM producto
        ";

        return $this->db->query($sql)->fetch(PDO::FETCH_ASSOC)
            ?: [
                'activos' => 0,
                'bajo_stock' => 0,
                'agotados' => 0,
                'inactivos' => 0
            ];
    }
}
