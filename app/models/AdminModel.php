
<?php

require_once __DIR__ . '/../config/database.php';

class AdminModel
{
    private PDO $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    /**
     * Obtiene los indicadores generales del panel.
     */
    public function obtenerResumen(): array
    {
        $sql = "
            SELECT
                (SELECT COUNT(*)
                 FROM producto
                 WHERE activo = TRUE) AS productos_activos,

                (SELECT COUNT(*)
                 FROM categoria
                 WHERE activa = TRUE) AS categorias_activas,

                (SELECT COUNT(*)
                 FROM usuario
                 WHERE rol = 'cliente') AS clientes,

                (SELECT COUNT(*)
                 FROM pedido) AS pedidos
        ";

        $stmt = $this->db->query($sql);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'productos_activos' => 0,
            'categorias_activas' => 0,
            'clientes' => 0,
            'pedidos' => 0
        ];
    }
}
