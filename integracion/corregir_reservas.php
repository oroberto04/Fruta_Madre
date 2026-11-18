<?php
/** Corrige la incompatibilidad del esquema original: reserva.id_carrito NOT NULL, pero el modelo admite eventos sin carrito. */
if(PHP_SAPI!=='cli'){http_response_code(403);exit('Solo CLI');}
require_once __DIR__.'/../app/config/database.php';
$db=(new Database())->connect();
$db->beginTransaction();
try {
    $db->exec('ALTER TABLE reserva ALTER COLUMN id_carrito DROP NOT NULL');
    $db->commit();
    echo "RESERVAS OK: eventos pueden reservarse sin asignar un carrito físico. Datos existentes intactos.\n";
} catch(Throwable $e){if($db->inTransaction())$db->rollBack();fwrite(STDERR,"ERROR: ".$e->getMessage()."\n");exit(1);}
