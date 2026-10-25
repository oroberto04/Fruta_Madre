<?php

require_once __DIR__ . '/../config/database.php';

class CatalogoModel
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function obtenerProductos()
    {
        $sql = "
            SELECT
                p.id_producto,
                p.nombre,
                p.descripcion,
                p.ingredientes,
                p.precio,
                p.imagen_url,
                p.stock_disponible,
                c.nombre_categoria
            FROM producto p
            INNER JOIN categoria c
                ON p.id_categoria = c.id_categoria
            WHERE p.activo = TRUE
            ORDER BY p.id_producto ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerCategorias()
    {
        $sql = "
            SELECT
                id_categoria,
                nombre_categoria,
                descripcion
            FROM categoria
            WHERE activa = TRUE
            ORDER BY id_categoria ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function obtenerProductoPorId($id)
{
    $sql = "
        SELECT
            p.id_producto,
            p.nombre,
            p.descripcion,
            p.ingredientes,
            p.precio,
            p.imagen_url,
            p.stock_disponible,
            c.nombre_categoria
        FROM producto p
        INNER JOIN categoria c
            ON p.id_categoria = c.id_categoria
        WHERE p.id_producto = :id
          AND p.activo = TRUE
        LIMIT 1
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}
}