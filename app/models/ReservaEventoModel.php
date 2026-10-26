
<?php

require_once __DIR__ . '/../config/database.php';

class ReservaEventoModel
{
    private PDO $db;

    public function __construct()
    {
        $conexion = new Database();
        $this->db = $conexion->connect();
    }

    /**
     * Obtiene los paquetes disponibles para reservar eventos.
     */
    public function obtenerPaquetes(): array
    {
        $sql = "
            SELECT
                id_paquete,
                nombre_paquete,
                descripcion,
                capacidad_personas,
                duracion_horas,
                precio_base
            FROM paquete
            ORDER BY id_paquete
        ";

        return $this->db
            ->query($sql)
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Registra el evento y crea su reservación.
     * Devuelve el ID de la reservación creada.
     */
    public function crearReserva(array $datos): int
    {
        $this->db->beginTransaction();

        try {
            // 1. Consultar el paquete y obtener su precio real.
            $sqlPaquete = "
                SELECT
                    id_paquete,
                    nombre_paquete,
                    capacidad_personas,
                    precio_base
                FROM paquete
                WHERE id_paquete = :id_paquete
                FOR SHARE
            ";

            $stmtPaquete = $this->db->prepare($sqlPaquete);

            $stmtPaquete->execute([
                'id_paquete' => (int) $datos['id_paquete']
            ]);

            $paquete = $stmtPaquete->fetch(PDO::FETCH_ASSOC);

            if (!$paquete) {
                throw new RuntimeException(
                    'El paquete seleccionado no existe.'
                );
            }

            // 2. Validar el número de invitados.
            $invitados = (int) $datos['invitados'];

            if ($invitados < 1) {
                throw new RuntimeException(
                    'El número de invitados debe ser mayor que cero.'
                );
            }

            if (
                $paquete['capacidad_personas'] !== null &&
                $invitados > (int) $paquete['capacidad_personas']
            ) {
                throw new RuntimeException(
                    'El número de invitados supera la capacidad del paquete.'
                );
            }

            // 3. Construir la dirección del evento.
            $direccion = implode(', ', array_filter([
                trim($datos['calle'] ?? ''),
                trim($datos['colonia'] ?? ''),
                trim($datos['municipio'] ?? ''),
                trim($datos['codigo_postal'] ?? ''),
                trim($datos['referencias'] ?? '')
            ], static fn($valor) => $valor !== ''));

            // 4. Combinar la fecha y la hora.
            $fechaHora = $datos['fecha_evento']
                . ' '
                . $datos['hora_evento'];

            // 5. Registrar el evento.
            $sqlEvento = "
                INSERT INTO evento (
                    id_usuario,
                    id_paquete,
                    tipo_evento,
                    fecha_hora,
                    lugar_direccion,
                    num_invitados,
                    observaciones
                )
                VALUES (
                    :id_usuario,
                    :id_paquete,
                    :tipo_evento,
                    :fecha_hora,
                    :lugar_direccion,
                    :num_invitados,
                    :observaciones
                )
                RETURNING id_evento
            ";

            $stmtEvento = $this->db->prepare($sqlEvento);

            $stmtEvento->execute([
                'id_usuario' => (int) $datos['id_usuario'],
                'id_paquete' => (int) $paquete['id_paquete'],
                'tipo_evento' => trim($datos['motivo_evento']),
                'fecha_hora' => $fechaHora,
                'lugar_direccion' => $direccion,
                'num_invitados' => $invitados,
                'observaciones' => trim(
                    $datos['observaciones'] ?? ''
                )
            ]);

            $idEvento = (int) $stmtEvento->fetchColumn();

            // 6. Los eventos no requieren un carrito de compra.
            $idCarrito = null;

            // 7. Crear la reservación con el precio del paquete.
            $sqlReserva = "
                INSERT INTO reserva (
                    id_evento,
                    id_carrito,
                    estado_reserva,
                    costo_total
                )
                VALUES (
                    :id_evento,
                    :id_carrito,
                    :estado_reserva,
                    :costo_total
                )
                RETURNING id_reserva
            ";

            $stmtReserva = $this->db->prepare($sqlReserva);

            $stmtReserva->execute([
                'id_evento' => $idEvento,
                'id_carrito' => $idCarrito,
                'estado_reserva' => 'pendiente',
                'costo_total' => $paquete['precio_base']
            ]);

            $idReserva = (int) $stmtReserva->fetchColumn();

            // 8. Confirmar ambos registros.
            $this->db->commit();

            return $idReserva;

        } catch (Throwable $e) {
            // Si algo falla, deshacer los cambios.
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    /**
     * Obtiene una reservación y verifica que pertenezca al usuario.
     */
    public function obtenerReserva(
        int $idReserva,
        int $idUsuario
    ): array|false {
        $sql = "
            SELECT
                r.id_reserva,
                r.estado_reserva,
                r.costo_total,
                e.id_evento,
                e.tipo_evento,
                e.fecha_hora,
                e.lugar_direccion,
                e.num_invitados,
                e.observaciones,
                p.nombre_paquete,
                p.duracion_horas,
                u.nombre_completo,
                u.correo,
                u.telefono
            FROM reserva r
            INNER JOIN evento e
                ON e.id_evento = r.id_evento
            INNER JOIN paquete p
                ON p.id_paquete = e.id_paquete
            INNER JOIN usuario u
                ON u.id_usuario = e.id_usuario
            WHERE r.id_reserva = :id_reserva
              AND e.id_usuario = :id_usuario
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'id_reserva' => $idReserva,
            'id_usuario' => $idUsuario
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
