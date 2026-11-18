#!/usr/bin/env node
/**
 * Ejecutar SOLO después de descomprimir el paquete en la RAÍZ de la rama
 * integracion-jam-mercadopago-pruebas de oroberto04/Fruta_Madre (basada en desarrollo).
 * Verifica todos los puntos de inserción antes de modificar cualquier archivo.
 */
import { readFileSync, writeFileSync, existsSync } from 'node:fs';
import { resolve, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';
import { execFileSync } from 'node:child_process';
const root=resolve(dirname(fileURLToPath(import.meta.url)),'..');
function fail(msg){throw new Error(msg)}
if(!existsSync(resolve(root,'index.php')))fail('Primero descomprime en la raíz del repositorio Fruta_Madre.');
let rama='';
try {rama=execFileSync('git',['branch','--show-current'],{cwd:root,encoding:'utf8'}).trim()} catch {}
if (rama !== 'integracion-jam-mercadopago-pruebas') fail('Rama actual: '+(rama||'(sin Git)')+'. Cambia a integracion-jam-mercadopago-pruebas para proteger las otras ramas.');
const changed=new Map();
function read(p){const v=resolve(root,p);if(!existsSync(v))fail('No existe '+p+'. Esta integración requiere la rama desarrollo de Roberto.');return (changed.get(p)??readFileSync(v,'utf8')).replace(/\r\n/g,'\n')}
function replace(p,find,to){const s=read(p); if(!s.includes(find)) fail('No encontré el punto para integrar en '+p+': '+String(find).slice(0,105));const i=s.indexOf(find);if(s.indexOf(find,i+1)!==-1)fail('Inserción ambigua en '+p);changed.set(p,s.replace(find,to))}
function replaceRegex(p,rx,to){const s=read(p),arr=[...s.matchAll(new RegExp(rx.source,rx.flags.includes('g')?rx.flags:rx.flags+'g'))]; if(arr.length!==1)fail('Patrón ausente o ambiguo en '+p+': '+rx); changed.set(p,s.replace(rx,to))}
replace('index.php',
"} elseif ($accion === 'pedido-confirmado') {",
`// MERCADO PAGO: solamente Checkout Pro con credenciales TEST.
} elseif ($accion === 'iniciar-pago-mp-prueba') {
    require_once __DIR__ . '/app/controllers/MercadoPagoPruebasController.php';
    (new MercadoPagoPruebasController())->iniciar();

} elseif ($accion === 'resultado-pago-mp-prueba') {
    require_once __DIR__ . '/app/controllers/MercadoPagoPruebasController.php';
    (new MercadoPagoPruebasController())->resultado();

} elseif ($accion === 'pedido-confirmado') {`);

replace('app/controllers/HomeController.php',
"$metodosPermitidos = [\n    'efectivo',\n    'transferencia'\n];",
"$metodosPermitidos = [\n    'efectivo',\n    'transferencia',\n    'mercadopago_prueba'\n];");
replace('app/controllers/HomeController.php',
"    // Preparar los productos usando el carrito de la sesión.",
`    // No crear un pedido TEST si falta la credencial de pruebas.
    if ($metodoPago === 'mercadopago_prueba') {
        require_once __DIR__ . '/../services/MercadoPagoPruebas.php';
        if (!MercadoPagoPruebas::habilitado()) {
            $_SESSION['error_pedido'] = 'Mercado Pago de pruebas requiere configurar MP_ACCESS_TOKEN_TEST.';
            header('Location: index.php?accion=pagar');
            exit;
        }
    }

    // Preparar los productos usando el carrito de la sesión.`);
replace('app/controllers/HomeController.php',
"        header('Location: index.php?accion=pedido-confirmado');",
`        unset($_SESSION['pago_mp_estado'], $_SESSION['pago_mp_detalle']);
        if ($metodoPago === 'mercadopago_prueba') {
            header('Location: index.php?accion=iniciar-pago-mp-prueba', true, 303);
        } else {
            header('Location: index.php?accion=pedido-confirmado', true, 303);
        }`);

replace('app/models/PedidoModel.php',
"$metodosPermitidos = [\n    'efectivo',\n    'transferencia'\n];",
"$metodosPermitidos = [\n    'efectivo',\n    'transferencia',\n    'mercadopago_prueba'\n];");

replace('app/views/home/pago.php',
"if (!in_array($metodoSeleccionado, ['efectivo', 'transferencia'], true)) {",
"if (!in_array($metodoSeleccionado, ['efectivo', 'transferencia', 'mercadopago_prueba'], true)) {");
replace('app/views/home/pago.php',
"$servicio = $datosPedido['tipo_servicio'] ?? 'recoger';",
`$servicio = $datosPedido['tipo_servicio'] ?? 'recoger';
require_once __DIR__ . '/../../services/MercadoPagoPruebas.php';
$mpPruebasConfigurado = MercadoPagoPruebas::habilitado();`);
replaceRegex('app/views/home/pago.php',
/<button type="button" id="tab-tarjeta"[\s\S]*?<\/button>/,
`<button type="button" id="tab-mercadopago_prueba"
        class="pay-tab flex flex-col items-start gap-2 rounded-lg p-3 text-left <?= $mpPruebasConfigurado ? '' : 'opacity-50 cursor-not-allowed' ?>"
        <?= $mpPruebasConfigurado ? 'onclick="selectPaymentMethod(\\'mercadopago_prueba\\')"' : 'disabled aria-disabled="true"' ?>>
    <span class="material-symbols-outlined text-2xl">credit_card</span>
    <strong class="display text-sm uppercase">Mercado Pago</strong>
    <span class="text-[10px]">Pago de prueba · sin cobros reales</span>
</button>`);
replace('app/views/home/pago.php',
'<div id="panel-tarjeta" class="pay-panel active flex-col gap-4">',
'<div id="panel-mercadopago_prueba" class="pay-panel hidden flex-col gap-4">');
replace('app/views/home/pago.php',
'La integración con una pasarela bancaria todavía está pendiente.\n                                    No introduzcas números de tarjeta ni CVV en esta versión.',
`Mercado Pago funciona exclusivamente con credenciales TEST. Serás redirigido al Checkout Pro de pruebas.
                                    Usa solamente cuentas y tarjetas de prueba. Nunca introduzcas datos bancarios reales.`);
replace('app/views/home/pago.php',
'Cuando integremos una pasarela, los datos de la tarjeta se capturarán de forma segura y no se almacenarán en la base de datos de Fruta Madre.',
'Fruta Madre no almacena números de tarjeta ni CVV. Las pruebas se procesan en la plataforma de Mercado Pago.');
replace('app/views/home/pago.php',
"if (!['efectivo', 'transferencia'].includes(metodo)) {",
"if (!['efectivo', 'transferencia', 'mercadopago_prueba'].includes(metodo)) {");

replace('app/views/home/confirmacion.php',
"    'tarjeta' => 'Tarjeta'",
"    'tarjeta' => 'Tarjeta',\n    'mercadopago_prueba' => 'Mercado Pago (solo pruebas)'");
replace('app/views/home/confirmacion.php',
"    default:\n        $estadoTitulo = 'Pedido registrado';",
`    case 'mercadopago_prueba':
        $estadoTitulo = ($_SESSION['pago_mp_estado'] ?? '') === 'aprobado_prueba'
            ? 'Pago de prueba aprobado' : 'Pedido registrado: prueba pendiente';
        $estadoDescripcion = $_SESSION['pago_mp_detalle'] ??
            'Tu pedido está registrado. Mercado Pago solo procesará operaciones simuladas con credenciales TEST.';
        $estadoIcono = ($_SESSION['pago_mp_estado'] ?? '') === 'aprobado_prueba' ? '✓' : '⌛';
        break;

    default:
        $estadoTitulo = 'Pedido registrado';`);
replace('app/views/home/confirmacion.php',
"                    if ($metodoPago === 'efectivo') {",
`                    if ($metodoPago === 'mercadopago_prueba') {
                        echo ($_SESSION['pago_mp_estado'] ?? '') === 'aprobado_prueba'
                            ? 'Aprobado en TEST (sin cobro real)' : 'Pendiente de prueba';
                    } elseif ($metodoPago === 'efectivo') {`);
replace('app/views/home/confirmacion.php',
`        <div class="acciones">
            <a class="boton principal" href="index.php">`,
`        <div class="acciones">
            <?php if ($metodoPago === 'mercadopago_prueba' && ($_SESSION['pago_mp_estado'] ?? '') !== 'aprobado_prueba'): ?>
                <a class="boton principal" href="index.php?accion=iniciar-pago-mp-prueba&amp;id_pedido=<?= $idPedido ?>">
                    Abrir Mercado Pago de pruebas
                </a>
            <?php endif; ?>
            <a class="boton principal" href="index.php">`);

replace('app/models/AdminModel.php',
"              AND pg.estado_pago = 'pendiente'",
"              AND pg.estado_pago = 'pendiente'\n              AND pg.metodo_pago <> 'mercadopago_prueba'");

replace('.gitignore',
'!database/seed.sql',
'!database/seed.sql\n!database/migraciones/*.sql');

const lintfiles=[...changed.keys()];
if (process.argv.includes('--verificar')) {console.log('Compatibilidad confirmada. Archivos a modificar:\n'+lintfiles.join('\n'));process.exit(0)}
for (const [p,s] of changed) writeFileSync(resolve(root,p),s,'utf8');
console.log('Integración de Mercado Pago TEST aplicada. Archivos editados:\n'+lintfiles.join('\n'));
console.log('Revisa git diff, prueba con php tests/mercadopago_pruebas_test.php y después realiza git push.');
