<?php
require_once __DIR__.'/../app/services/ConciliadorMercadoPagoPruebas.php';
if (PHP_SAPI!=='cli') { http_response_code(403); exit; }
try {
    foreach (ConciliadorMercadoPagoPruebas::pendientes(null, 30) as $id) {
        try {
            echo "Pedido #$id: ".ConciliadorMercadoPagoPruebas::conciliar($id).PHP_EOL;
        } catch (Throwable $e) {
            error_log("Conciliacion MP TEST #$id: ".$e->getMessage());
            echo "Pedido #$id: error de verificacion".PHP_EOL;
        }
    }
} catch (Throwable $e) { fwrite(STDERR, "Error de conciliacion: ".$e->getMessage().PHP_EOL); exit(1); }
