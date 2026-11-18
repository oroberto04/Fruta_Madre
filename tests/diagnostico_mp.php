<?php
/** Fruta Madre: diagnostico no destructivo de integracion TEST. No imprime secretos. */
$root = dirname(__DIR__);
function resultado(string $nombre, bool $ok, string $detalle = ''): void {
    echo ($ok ? '[OK] ' : '[PENDIENTE] ') . $nombre . ($detalle !== '' ? ' - ' . $detalle : '') . PHP_EOL;
}
$files = [
    'Controlador Mercado Pago' => $root.'/app/controllers/MercadoPagoPruebasController.php',
    'Servicio Mercado Pago' => $root.'/app/services/MercadoPagoPruebas.php',
    'Rutas principales' => $root.'/index.php',
    'Configuracion PostgreSQL' => $root.'/app/config/database.php',
];
foreach ($files as $label => $file) resultado($label, is_file($file));
resultado('PHP cURL', extension_loaded('curl'));
resultado('PHP PDO PostgreSQL', extension_loaded('pdo_pgsql'));
$token = trim((string)getenv('MP_ACCESS_TOKEN_TEST'));
resultado('Token TEST configurado', strlen($token) >= 20 && (str_starts_with($token, 'TEST-') || str_starts_with($token, 'APP_USR-')), 'valor oculto');
resultado('Confirmacion explicita de pruebas', getenv('MP_SOLO_PRUEBAS_CONFIRMADO') === 'SI');
$url = trim((string)getenv('FRUTA_MADRE_URL'));
$parts = $url !== '' ? parse_url($url) : false;
resultado('URL publica HTTPS', is_array($parts) && ($parts['scheme'] ?? '') === 'https' && !empty($parts['host']));
$routes = is_file($root.'/index.php') ? file_get_contents($root.'/index.php') : '';
foreach (['iniciar-pago-mp-prueba','resultado-pago-mp-prueba','pedido-confirmado'] as $route) {
    resultado('Ruta '.$route, str_contains($routes, "'".$route."'"));
}
$controller = is_file($files['Controlador Mercado Pago']) ? file_get_contents($files['Controlador Mercado Pago']) : '';
resultado('Orden ligada a sesion', str_contains($controller, "mp_order_test"), 'requiere recuperacion si se pierde la sesion');
$hasWebhook = preg_match('/(?:webhook|notificacion-mp|ipn)/i', $routes) === 1;
resultado('Ruta webhook identificada', $hasWebhook, $hasWebhook ? 'revisar implementacion' : 'no identificada en index.php');
if (extension_loaded('pdo_pgsql') && is_file($files['Configuracion PostgreSQL'])) {
    try {
        require_once $files['Configuracion PostgreSQL'];
        $pdo = (new Database())->connect();
        resultado('Conexion PostgreSQL', true);
        foreach (['usuario','producto','pedido','pago','reserva_evento'] as $table) {
            $stmt=$pdo->prepare('SELECT to_regclass(:tabla)');
            $stmt->execute(['tabla'=>'public.'.$table]);
            resultado('Tabla '.$table, $stmt->fetchColumn() !== null);
        }
    } catch (Throwable $e) {
        resultado('Conexion PostgreSQL', false, 'no disponible; revisar variables DB_* sin compartir contrasenas');
    }
}
echo PHP_EOL.'Solo diagnostico: no hace cobros, no modifica datos y no muestra credenciales.'.PHP_EOL;
