<?php
declare(strict_types=1);
// Offline regression tests: no database connection, HTTP requests or real payments.
$root = dirname(__DIR__);
$service = $root . '/app/services/MercadoPagoPruebas.php';
$controller = $root . '/app/controllers/MercadoPagoPruebasController.php';
if (!is_file($service) || !is_file($controller)) { fwrite(STDERR, "Ejecuta desde la raíz del proyecto, con el archivo en tests/.\n"); exit(2); }
require_once $service;
$passed=0;$failed=0;
function t(string $label, callable $test): void {global $passed,$failed;try {if (!$test()) throw new RuntimeException('resultado inesperado');echo "OK  $label\n";$passed++;}catch(Throwable $e){echo "FALLO  $label: {$e->getMessage()}\n";$failed++;}}
function rejects(array $o, string $id='ORDTST123', int $pedido=9, int $cents=1234): bool {try{return MercadoPagoPruebas::validarRespuestaOrder($o,$id,$pedido,$cents)!=='aprobado_prueba';}catch(Throwable $e){return true;}}
$base=['id'=>'ORDTST123','external_reference'=>'FRUTA-MADRE-TEST-9','live_mode'=>false,'currency'=>'MXN','total_amount'=>'12.34','total_paid_amount'=>'12.34','status'=>'processed','status_detail'=>'accredited'];
t('Orden TEST válida se reconoce',fn()=>MercadoPagoPruebas::validarRespuestaOrder($base,'ORDTST123',9,1234)==='aprobado_prueba');
foreach (['id'=>'ORDOTHER','external_reference'=>'OTRO-9','live_mode'=>true,'currency'=>'USD','total_amount'=>'12.35','total_paid_amount'=>'12.35','status'=>'pending','status_detail'=>'other'] as $key=>$value){$modified=$base;$modified[$key]=$value;t("No aprobar con $key inválido",fn()=>rejects($modified));}
$pending=$base;$pending['status']='pending';t('Orden pendiente no aprobada',fn()=>MercadoPagoPruebas::validarRespuestaOrder($pending,'ORDTST123',9,1234)==='pendiente');
$controllerCode=file_get_contents($controller);
t('El retorno no confía en status del navegador',fn()=>str_contains($controllerCode,'MercadoPagoPruebas::consultarOrder($orderId)'));
t('La confirmación requiere pedido no cancelado',fn()=>str_contains($controllerCode,"p.estado_pedido <> 'cancelado'"));
t('La confirmación verifica filas actualizadas',fn()=>str_contains($controllerCode,'$stmt->rowCount() !== 1'));
t('La orden se vincula al usuario autenticado',fn()=>str_contains($controllerCode,'p.id_usuario = :usuario'));
t('El servicio exige URL pública HTTPS',fn()=>str_contains(file_get_contents($service),"!== 'https'"));
echo "\nRESULTADO: $passed correctas, $failed fallidas. Pruebas locales sin llamadas a Mercado Pago.\n";
exit($failed?1:0);
