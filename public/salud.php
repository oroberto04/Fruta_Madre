<?php
header('Content-Type: application/json; charset=utf-8');
require dirname(__DIR__) . '/app/services/MercadoPagoPruebas.php';
echo json_encode([
    'ok' => true,
    'app' => 'fruta-madre',
    'pagos_reales_habilitados' => false,
    'mercado_pago_test_configurado' => MercadoPagoPruebas::habilitado(),
    'base_de_datos_configurada' => getenv('DATABASE_URL') !== false && getenv('DATABASE_URL') !== ''
], JSON_UNESCAPED_UNICODE);
