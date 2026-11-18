<?php
/** Checkout Pro Orders API: operaciones exclusivamente con credencial de prueba. */
final class MercadoPagoPruebas
{
    public static function habilitado(): bool
    {
        $token = trim((string) getenv('MP_ACCESS_TOKEN_TEST'));
        // APP_USR también se usa en producción: la variable explícita es una barrera adicional,
        // NO una prueba criptográfica del ambiente. Validamos live_mode en la respuesta.
        return strlen($token) >= 20
            && (str_starts_with($token, 'TEST-') || str_starts_with($token, 'APP_USR-'))
            && getenv('MP_SOLO_PRUEBAS_CONFIRMADO') === 'SI';
    }

    private static function credencial(): string
    {
        if (!self::habilitado()) {
            throw new RuntimeException('Confirma que el token viene de Credenciales de prueba y configura MP_SOLO_PRUEBAS_CONFIRMADO=SI.');
        }
        return trim((string) getenv('MP_ACCESS_TOKEN_TEST'));
    }

    public static function urlPublica(): string
    {
        $url = rtrim(trim((string) getenv('FRUTA_MADRE_URL')), '/');
        $p = parse_url($url);
        if (!$p || ($p['scheme'] ?? '') !== 'https' || empty($p['host']) || isset($p['user']) || isset($p['pass'])) {
            throw new RuntimeException('Configura FRUTA_MADRE_URL con una URL pública HTTPS.');
        }
        return $url;
    }

    public static function validarRespuestaOrder(array $order, string $idOrder, int $idPedido, int $centavos): string
    {
        if (($order['id'] ?? null) !== $idOrder || ($order['external_reference'] ?? null) !== 'FRUTA-MADRE-TEST-' . $idPedido) {
            throw new RuntimeException('La order no corresponde al pedido.');
        }
        $liveMode = $order['live_mode'] ?? null;

        if ($liveMode === true
            || ($liveMode !== false && $liveMode !== null)
            || ($liveMode === null && (
                !str_starts_with($idOrder, 'ORDTST')
                || !self::habilitado()
            ))) {
            throw new RuntimeException('La API no confirmó que la order sea de prueba.');
        }
        if (($order['currency'] ?? null) !== 'MXN' && ($order['currency_id'] ?? null) !== 'MXN') {
            throw new RuntimeException('Divisa no confirmada como MXN.');
        }
        if (!isset($order['total_amount']) || (int) round((float) $order['total_amount'] * 100) !== $centavos) {
            throw new RuntimeException('Importe diferente al de PostgreSQL.');
        }
        $status = (string) ($order['status'] ?? '');
        $detail = (string) ($order['status_detail'] ?? '');
        if ($status === 'processed' && $detail === 'accredited'
            && isset($order['total_paid_amount'])
            && (int) round((float) $order['total_paid_amount'] * 100) === $centavos) {
            return 'aprobado_prueba';
        }
        if (in_array($status, ['created', 'processing', 'action_required', 'pending'], true)) {
            return 'pendiente';
        }
        return 'rechazado';
    }

    public static function crearOrder(int $idPedido, int $centavos, string $idempotencia): array
    {
        if ($idPedido <= 0 || $centavos <= 0 || !preg_match('/^[a-f0-9-]{36}$/i', $idempotencia)) {
            throw new InvalidArgumentException('Pedido, importe o clave de idempotencia inválidos.');
        }
        $monto = number_format($centavos / 100, 2, '.', '');
        $url = self::urlPublica() . '/index.php?accion=resultado-pago-mp-prueba&id_pedido=' . $idPedido;
        $r = self::solicitud('POST', '/v1/orders', [
            'type' => 'online',
            'processing_mode' => 'manual',
            'total_amount' => $monto,
            'external_reference' => 'FRUTA-MADRE-TEST-' . $idPedido,
            'items' => [['title' => 'Fruta Madre - Pedido #' . $idPedido, 'quantity' => 1, 'unit_price' => $monto]],
            'config' => ['online' => [
                'success_url' => $url . '&retorno=success',
                'failure_url' => $url . '&retorno=failure',
                'pending_url' => $url . '&retorno=pending',
                'auto_return' => 'approved'
            ]]
        ], $idempotencia);
        $id = $r['id'] ?? null;
        $destino = $r['checkout_url'] ?? null;
        if (!is_string($id) || !preg_match('/^ORD[A-Z0-9]+$/i', $id)
            || !is_string($destino) || parse_url($destino, PHP_URL_SCHEME) !== 'https'
            || !preg_match('/(^|\.)mercadopago\.(com|com\.mx|com\.ar|com\.br|com\.co|cl|com\.pe|com\.uy)$/i', (string) parse_url($destino, PHP_URL_HOST))) {
            throw new RuntimeException('Respuesta de checkout inesperada.');
        }
        return ['id' => $id, 'url' => $destino];
    }

    public static function consultarOrder(string $id): array
    {
        if (!preg_match('/^ORD[A-Z0-9]+$/i', $id)) {
            throw new InvalidArgumentException('ID de order inválido.');
        }
        return self::solicitud('GET', '/v1/orders/' . rawurlencode($id));
    }

    private static function solicitud(string $metodo, string $ruta, ?array $datos = null, ?string $idempotencia = null): array
    {
        $token = self::credencial();
        if (!function_exists('curl_init')) {
            throw new RuntimeException('PHP necesita la extensión cURL.');
        }
        $ch = curl_init('https://api.mercadopago.com' . $ruta);
        $headers = ['Authorization: Bearer ' . $token, 'Accept: application/json', 'Content-Type: application/json'];
        if ($idempotencia !== null) $headers[] = 'X-Idempotency-Key: ' . $idempotencia;
        curl_setopt_array($ch, [CURLOPT_CUSTOMREQUEST => $metodo, CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => 6, CURLOPT_TIMEOUT => 20,
            CURLOPT_FOLLOWLOCATION => false, CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => 2]);
        if ($datos !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($datos, JSON_THROW_ON_ERROR));
        $respuesta = curl_exec($ch);
        $codigo = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        if ($respuesta === false || $codigo < 200 || $codigo >= 300) {
            error_log('Fruta Madre Orders TEST HTTP ' . $codigo . ': ' . $error);
            throw new RuntimeException('Mercado Pago devolvió HTTP ' . $codigo . '.');
        }
        $json = json_decode($respuesta, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($json)) throw new RuntimeException('Respuesta JSON inválida.');
        return $json;
    }
}
