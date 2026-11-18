<?php
/** Diagnóstico sin llamadas externas, contraseñas ni datos privados. */
if (PHP_SAPI!=='cli') exit(1);
require __DIR__.'/../app/config/database.php';
$db=(new Database())->connect();
$campos=$db->query("SELECT column_name FROM information_schema.columns WHERE table_name='producto' AND table_schema='public'")->fetchAll(PDO::FETCH_COLUMN);
foreach (['sku','costo','stock_minimo','subcategoria','permite_paquetes','actualizado_en'] as $c) {
 echo "Columna producto.$c: ".(in_array($c,$campos,true)?'OK':'FALTA MIGRACIÓN')."\n";
}
foreach (['producto','categoria','pedido','usuario','paquete','reserva','carrito_snack'] as $tabla) {
 echo "$tabla: ".$db->query('SELECT COUNT(*) FROM '.$tabla)->fetchColumn()." registros\n";
}
echo "Checkout Pro TEST (servicio): ".(is_file(__DIR__.'/../app/services/MercadoPagoPruebas.php')?'PRESENTE':'NO INSTALADO')."\n";
echo "Token TEST: no se muestra.\n";
