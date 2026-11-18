<?php
require __DIR__ . '/../app/services/MercadoPagoPruebas.php';
function check(bool $v, string $m): void { if (!$v) throw new Exception($m); echo "OK $m\n"; }
putenv('MP_ACCESS_TOKEN_TEST=APP_USR-12345678901234567890');
check(!MercadoPagoPruebas::habilitado(), 'sin confirmación explícita no habilita');
putenv('MP_SOLO_PRUEBAS_CONFIRMADO=SI');
check(MercadoPagoPruebas::habilitado(), 'token recibido y confirmación');
$o = ['id'=>'ORDTST123','external_reference'=>'FRUTA-MADRE-TEST-9','live_mode'=>false,'currency'=>'MXN','total_amount'=>'12.34','total_paid_amount'=>'12.34','status'=>'processed','status_detail'=>'accredited'];
check(MercadoPagoPruebas::validarRespuestaOrder($o,'ORDTST123',9,1234)==='aprobado_prueba','aprobación válida');
foreach (['id','external_reference','live_mode','currency','total_amount','total_paid_amount'] as $campo) {
 $x=$o; $x[$campo] = ['id'=>'ORDOTHER','external_reference'=>'OTRA','live_mode'=>true,'currency'=>'USD','total_amount'=>'1.00','total_paid_amount'=>'1.00'][$campo];
 $blocked=false; try { $v=MercadoPagoPruebas::validarRespuestaOrder($x,'ORDTST123',9,1234); $blocked=$v!=='aprobado_prueba'; } catch (RuntimeException $e) { $blocked=true; }
 check($blocked,'bloquea '.$campo);
}
$o['status']='created'; check(MercadoPagoPruebas::validarRespuestaOrder($o,'ORDTST123',9,1234)==='pendiente','pendiente');
