<?php
/**
 * Webhook de Mercado Pago para Fruta Madre.
 * Valida la firma y no acredita pagos sin verificación.
 */
final class MercadoPagoWebhookPruebas
{
    public static function atender(): never
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            exit;
        }

        $secret = trim((string) getenv('MP_WEBHOOK_SECRET_TEST'));

        if ($secret === '' || getenv('MP_SOLO_PRUEBAS_CONFIRMADO') !== 'SI') {
            http_response_code(503);
            echo json_encode(['error' => 'Webhook no configurado']);
            exit;
        }

        $firma = (string) ($_SERVER['HTTP_X_SIGNATURE'] ?? '');
        $requestId = (string) ($_SERVER['HTTP_X_REQUEST_ID'] ?? '');

        $partes = [];
        foreach (explode(',', $firma) as $parte) {
            $par = explode('=', trim($parte), 2);
            if (count($par) === 2) {
                $partes[trim($par[0])] = trim($par[1]);
            }
        }

        $ts = $partes['ts'] ?? '';
        $hash = $partes['v1'] ?? '';
        $id = (string) ($_GET['data_id'] ?? $_GET['data.id'] ?? '');

        if ($id === '') {
            $body = json_decode((string) file_get_contents('php://input'), true);
            $id = is_array($body) ? (string) ($body['data']['id'] ?? '') : '';
        }

        if (
            $requestId === '' ||
            !ctype_digit($ts) ||
            !preg_match('/^[a-f0-9]{64}$/i', $hash) ||
            $id === '' ||
            abs(time() - ((int) $ts / 1000)) > 600
        ) {
            http_response_code(401);
            echo json_encode(['error' => 'Firma inválida']);
            exit;
        }

        $manifest = 'id:' . strtolower($id)
            . ';request-id:' . $requestId
            . ';ts:' . $ts . ';';

        $esperada = hash_hmac('sha256', $manifest, $secret);

        if (!hash_equals($esperada, strtolower($hash))) {
            http_response_code(401);
            echo json_encode(['error' => 'Firma inválida']);
            exit;
        }

        // Recibir una notificación no significa que el pago fue aprobado.
        // La verificación del pago se implementará contra la API oficial.
        http_response_code(202);
        echo json_encode([
            'recibido' => true,
            'pago_confirmado' => false
        ]);
        exit;
    }
}
