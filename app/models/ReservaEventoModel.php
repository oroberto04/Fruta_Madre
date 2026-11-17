
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
     * Obtiene los paquetes disponibles.
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
     * Obtiene los extras disponibles para un paquete.
     */
    public function obtenerExtrasPorPaquete(int $idPaquete): array
    {
        $sql = "
            SELECT
                e.id_extra,
                e.nombre_extra,
                e.descripcion,
                e.precio_adicional
            FROM paquete_extra pe
            INNER JOIN extra e
                ON e.id_extra = pe.id_extra
            WHERE pe.id_paquete = :id_paquete
            ORDER BY e.id_extra
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_paquete' => $idPaquete]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Registra un evento, sus extras y su reservación.
     */
    public function crearReserva(array $datos): int
    {
        $this->db->beginTransaction();

        try {
            // 1. Consultar paquete.
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

            // 2. Validar invitados.
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

            // 3. Construir dirección.
            $direccion = implode(', ', array_filter([
                trim($datos['calle'] ?? ''),
                trim($datos['colonia'] ?? ''),
                trim($datos['municipio'] ?? ''),
                trim($datos['codigo_postal'] ?? ''),
                trim($datos['referencias'] ?? '')
            ], static fn($valor) => $valor !== ''));

            if ($direccion === '') {
                throw new RuntimeException(
                    'Debes proporcionar la dirección del evento.'
                );
            }

            // 4. Validar fecha y hora.
            $fecha = trim($datos['fecha_evento'] ?? '');
            $hora = trim($datos['hora_evento'] ?? '');

            if (
                !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) ||
                !preg_match('/^\d{2}:\d{2}$/', $hora)
            ) {
                throw new RuntimeException(
                    'La fecha o la hora del evento no son válidas.'
                );
            }

            $fechaHora = $fecha . ' ' . $hora;

            // 5. Registrar evento.
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
                'tipo_evento' => trim($datos['motivo_evento'] ?? ''),
                'fecha_hora' => $fechaHora,
                'lugar_direccion' => $direccion,
                'num_invitados' => $invitados,
                'observaciones' => trim(
                    $datos['observaciones']
                    ?? $datos['notas']
                    ?? ''
                )
            ]);

            $idEvento = (int) $stmtEvento->fetchColumn();

            // 6. Obtener extras seleccionados.
            $extras = $datos['extras'] ?? [];

            if (!is_array($extras)) {
                throw new RuntimeException(
                    'La selección de extras no es válida.'
                );
            }

            $idsExtras = array_values(array_unique(array_filter(
                array_map('intval', $extras),
                static fn($id) => $id > 0
            )));

            // 7. Consultar extras permitidos y sus precios reales.
            $costoExtras = 0;

            if (!empty($idsExtras)) {
                $placeholders = implode(
                    ', ',
                    array_fill(0, count($idsExtras), '?')
                );

                $sqlExtras = "
                    SELECT
                        e.id_extra,
                        e.precio_adicional
                    FROM paquete_extra pe
                    INNER JOIN extra e
                        ON e.id_extra = pe.id_extra
                    WHERE pe.id_paquete = ?
                      AND e.id_extra IN ($placeholders)
                    FOR SHARE OF e
                ";

                $stmtExtras = $this->db->prepare($sqlExtras);

                $stmtExtras->execute(array_merge(
                    [(int) $paquete['id_paquete']],
                    $idsExtras
                ));

                $extrasValidos = $stmtExtras->fetchAll(
                    PDO::FETCH_ASSOC
                );

                if (count($extrasValidos) !== count($idsExtras)) {
                    throw new RuntimeException(
                        'Uno o más extras no pertenecen al paquete seleccionado.'
                    );
                }

                // 8. Guardar extras del evento.
                $sqlEventoExtra = "
                    INSERT INTO evento_extra (
                        id_evento,
                        id_extra,
                        precio_aplicado
                    )
                    VALUES (
                        :id_evento,
                        :id_extra,
                        :precio_aplicado
                    )
                ";

                $stmtEventoExtra = $this->db->prepare(
                    $sqlEventoExtra
                );

                foreach ($extrasValidos as $extra) {
                    $precio = (float) $extra['precio_adicional'];

                    $stmtEventoExtra->execute([
                        'id_evento' => $idEvento,
                        'id_extra' => (int) $extra['id_extra'],
                        'precio_aplicado' => $precio
                    ]);

                    $costoExtras += $precio;
                }
            }

            // 9. Calcular costo total.
            $costoTotal = (float) $paquete['precio_base']
                + $costoExtras;

            // 10. Crear reservación.
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
                'id_carrito' => null,
                'estado_reserva' => 'pendiente',
                'costo_total' => $costoTotal
            ]);

            $idReserva = (int) $stmtReserva->fetchColumn();

            // 11. Confirmar transacción.
            $this->db->commit();

            return $idReserva;

        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    /**
     * Obtiene una reservación perteneciente al usuario.
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
