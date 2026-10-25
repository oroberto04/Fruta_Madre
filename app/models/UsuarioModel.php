
<?php

require_once __DIR__ . '/../config/database.php';

class UsuarioModel
{
    private PDO $db;

    public function __construct()
    {
        $conexion = new Database();
        $this->db = $conexion->connect();
    }

    public function existeCorreo(string $correo): bool
    {
        $sql = 'SELECT 1 FROM usuario WHERE LOWER(correo) = LOWER(:correo) LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['correo' => $correo]);

        return (bool) $stmt->fetchColumn();
    }

    public function registrar(
        string $nombre,
        string $correo,
        string $telefono,
        string $hash
    ): int {
        $sql = "
            INSERT INTO usuario (
                nombre_completo,
                correo,
                telefono,
                contrasena_hash,
                rol,
                fecha_registro
            )
            VALUES (
                :nombre,
                :correo,
                :telefono,
                :hash,
                'cliente',
                CURRENT_TIMESTAMP
            )
            RETURNING id_usuario
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'nombre' => $nombre,
            'correo' => $correo,
            'telefono' => $telefono,
            'hash' => $hash
        ]);

        return (int) $stmt->fetchColumn();
    }

    public function buscarPorCorreo(string $correo): array|false
    {
        $sql = "
            SELECT
                id_usuario,
                nombre_completo,
                correo,
                telefono,
                contrasena_hash,
                rol
            FROM usuario
            WHERE LOWER(correo) = LOWER(:correo)
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['correo' => $correo]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
