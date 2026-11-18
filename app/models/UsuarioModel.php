
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
        $sql = 'SELECT 1 FROM usuario
                WHERE LOWER(correo) = LOWER(:correo)
                LIMIT 1';

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

    // Obtener información completa del usuario autenticado.
    public function obtenerPorId(int $idUsuario): array|false
    {
        $sql = "
            SELECT
                id_usuario,
                nombre_completo,
                correo,
                telefono,
                rol,
                fecha_registro
            FROM usuario
            WHERE id_usuario = :id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $idUsuario]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Comprobar si otro usuario ya utiliza el correo.
    public function correoEnUso(string $correo, int $idUsuario): bool
    {
        $sql = "
            SELECT 1
            FROM usuario
            WHERE LOWER(correo) = LOWER(:correo)
              AND id_usuario <> :id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'correo' => $correo,
            'id' => $idUsuario
        ]);

        return (bool) $stmt->fetchColumn();
    }

    // Actualizar los datos del perfil del usuario.
    public function actualizarPerfil(
        int $idUsuario,
        string $nombre,
        string $correo,
        string $telefono
    ): bool {
        $sql = "
            UPDATE usuario
            SET nombre_completo = :nombre,
                correo = :correo,
                telefono = :telefono
            WHERE id_usuario = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'nombre' => $nombre,
            'correo' => $correo,
            'telefono' => $telefono,
            'id' => $idUsuario
        ]);
    }

}
