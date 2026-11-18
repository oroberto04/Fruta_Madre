<?php
require __DIR__ . '/../app/services/MercadoPagoPruebas.php';
function verificar(bool $ok, string $texto): void { if (!$ok) { throw new Exception($texto); } echo "OK: $texto\n"; }
putenv('MP_ACCESS_TOKEN_TEST=APP_USR-PRODUCCION_SIMULADA');
verificar(!MercadoPagoPruebas::habilitado(), 'bloquea token de produccion');
putenv('MP_ACCESS_TOKEN_TEST=TEST-12345678901234567890');
verificar(MercadoPagoPruebas::habilitado(), 'acepta configuracion TEST');
$pago = ['external_reference'=>'FRUTA-MADRE-TEST-20','live_mode'=>false,'currency_id'=>'MXN','transaction_amount'=>123.45,'status'=>'approved'];
verificar(MercadoPagoPruebas::validarRespuestaPago($pago,20,12345)==='aprobado_prueba','valida aprobado de prueba');
$pago['status']='pending';
verificar(MercadoPagoPruebas::validarRespuestaPago($pago,20,12345)==='pendiente','distingue pago pendiente');
foreach (['referencia','monto','produccion','divisa'] as $tipo) {
    $m = $pago;
    if ($tipo==='referencia') $m['external_reference']='FRUTA-MADRE-TEST-21';
    if ($tipo==='monto') $m['transaction_amount']=0.01;
    if ($tipo==='produccion') $m['live_mode']=true;
    if ($tipo==='divisa') $m['currency_id']='USD';
    $rechazado=false;
    try { MercadoPagoPruebas::validarRespuestaPago($m,20,12345); } catch (RuntimeException $e) { $rechazado=true; }
    verificar($rechazado, 'bloquea discrepancia: '.$tipo);
}
