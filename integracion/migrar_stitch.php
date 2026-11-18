<?php
/** CLI seguro: solo ALTER TABLE ADD COLUMN IF NOT EXISTS, UPDATE sku vacíos e índice. */
if (PHP_SAPI!=='cli') {http_response_code(403);exit('Solo CLI');}
require __DIR__ . '/../app/config/database.php';
$db=(new Database())->connect();
$sql=file_get_contents(__DIR__.'/../database/migraciones/20261118_stitch_productos.sql');
if ($sql===false) throw new RuntimeException('Falta la migración.');
$db->beginTransaction();
try {
 $db->exec($sql);
 $db->commit();
 echo "MIGRACIÓN STITCH OK. Productos existentes conservados.\n";
 echo "Columnas SKU, costo, stock mínimo, subcategoría y paquetes listas.\n";
} catch(Throwable $e) {
 if($db->inTransaction())$db->rollBack();
 fwrite(STDERR,"ERROR de migración: ".$e->getMessage()."\n");exit(1);
}
