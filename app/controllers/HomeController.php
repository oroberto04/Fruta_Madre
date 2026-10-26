
<?php

require_once __DIR__ . '/../models/PedidoModel.php';
require_once __DIR__ . '/../models/CatalogoModel.php';

class HomeController
{
    private $catalogoModel;

    public function __construct()
    {
        $this->catalogoModel = new CatalogoModel();

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (!isset($_SESSION['carrito'])) {
            $_SESSION['carrito'] = [];
        }
    }

    public function index()
    {
        $productos = $this->catalogoModel->obtenerProductos();

        require_once __DIR__ . '/../views/home/index.php';
    }


public function catalogo()
{
    $productos = $this->catalogoModel->obtenerProductos();

    require __DIR__ . '/../views/home/catalogo.php';
}

public function eventos()
{
    require __DIR__ . '/../views/home/eventos.php';
}


public function reservarEvento(): void
{
    if (empty($_SESSION['usuario']['id_usuario'])) {
        $_SESSION['despues_login'] = 'index.php?accion=eventos';
        header('Location: index.php?accion=login');
        exit;
    }

    require_once __DIR__ . '/../models/ReservaEventoModel.php';

    $reservaModel = new ReservaEventoModel();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $paquetes = $reservaModel->obtenerPaquetes();

        require __DIR__ . '/../views/home/reservar-evento.php';
        return;
    }

    $datos = [
        'id_usuario' => (int) $_SESSION['usuario']['id_usuario'],
        'id_paquete' => (int) ($_POST['id_paquete'] ?? 1),
        'fecha_evento' => trim($_POST['fecha_evento'] ?? ''),
        'hora_evento' => trim($_POST['hora_evento'] ?? '13:00'),
        'motivo_evento' => trim($_POST['motivo_evento'] ?? ''),
        'invitados' => (int) ($_POST['invitados'] ?? 0),
        'calle' => trim($_POST['calle'] ?? ''),
        'colonia' => trim($_POST['colonia'] ?? ''),
        'codigo_postal' => trim($_POST['codigo_postal'] ?? ''),
        'municipio' => trim($_POST['municipio'] ?? ''),
        'referencias' => trim($_POST['referencias'] ?? ''),
        'observaciones' => trim($_POST['notas'] ?? '')
    ];

    $errores = [];

    $fecha = DateTime::createFromFormat('!Y-m-d', $datos['fecha_evento']);
    $fechaValida = $fecha && $fecha->format('Y-m-d') === $datos['fecha_evento'];

    if (!$fechaValida || $datos['fecha_evento'] < date('Y-m-d')) {
        $errores[] = 'Selecciona una fecha válida, igual o posterior a hoy.';
    }

    if (
        !preg_match('/^\d{2}:\d{2}$/', $datos['hora_evento']) ||
        $datos['hora_evento'] > '23:59' ||
        (int) substr($datos['hora_evento'], 3, 2) > 59
    ) {
        $errores[] = 'Selecciona una hora válida.';
    }

    if ($datos['invitados'] < 1) {
        $errores[] = 'El número de invitados debe ser mayor que cero.';
    }

    foreach (['motivo_evento', 'calle', 'colonia', 'municipio'] as $campo) {
        if ($datos[$campo] === '') {
            $errores[] = 'Completa el tipo de evento y la dirección.';
            break;
        }
    }

    if ($datos['id_paquete'] < 1) {
        $errores[] = 'Selecciona un paquete válido.';
    }

    if (!empty($errores)) {
        $_SESSION['errores_reserva_evento'] = $errores;
        $_SESSION['datos_reserva_evento'] = $datos;

        header('Location: index.php?accion=reservar-evento');
        exit;
    }

    try {
        $idReserva = $reservaModel->crearReserva($datos);

        unset(
            $_SESSION['errores_reserva_evento'],
            $_SESSION['datos_reserva_evento']
        );

        $_SESSION['reserva_evento_creada'] = [
            'id_reserva' => $idReserva
        ];

        header('Location: index.php?accion=confirmacion-reserva-evento');
        exit;

    } catch (Throwable $e) {
        error_log('Error al crear reserva de evento: ' . $e->getMessage());

        $_SESSION['errores_reserva_evento'] = [
            'No fue posible registrar la reservación. ' .
            'Revisa la configuración de la reserva e inténtalo nuevamente.'
        ];

        header('Location: index.php?accion=reservar-evento');
        exit;
    }
}


    public function detalle($id)
    {
        $producto = $this->catalogoModel->obtenerProductoPorId($id);

        if (!$producto) {
            http_response_code(404);
            echo 'Producto no encontrado.';
            return;
        }

        require_once __DIR__ . '/../views/home/detalle.php';
    }

    public function agregarAlCarrito($id)
    {
        $producto = $this->catalogoModel->obtenerProductoPorId($id);

        if (!$producto) {
            header('Location: index.php?error=producto');
            exit;
        }

        if ((int) $producto['stock_disponible'] <= 0) {
            header('Location: index.php?error=agotado');
            exit;
        }

        $id = (int) $producto['id_producto'];
        $cantidadActual = (int) ($_SESSION['carrito'][$id] ?? 0);

        if ($cantidadActual >= (int) $producto['stock_disponible']) {
            header('Location: index.php?error=stock');
            exit;
        }

        $_SESSION['carrito'][$id] = $cantidadActual + 1;

        header('Location: index.php?agregado=1');
        exit;
    }

    public function carrito()
    {
        $productosCarrito = [];
        $total = 0;

        foreach ($_SESSION['carrito'] as $id => $cantidad) {
            $producto = $this->catalogoModel->obtenerProductoPorId((int) $id);

            if (!$producto) {
                unset($_SESSION['carrito'][$id]);
                continue;
            }

            $cantidad = (int) $cantidad;
            $subtotal = (float) $producto['precio'] * $cantidad;

            $producto['cantidad'] = $cantidad;
            $producto['subtotal'] = $subtotal;

            $productosCarrito[] = $producto;
            $total += $subtotal;
        }

        require_once __DIR__ . '/../views/home/carrito.php';
    }

    public function eliminarDelCarrito($id)
    {
        unset($_SESSION['carrito'][(int) $id]);

        header('Location: index.php?accion=carrito');
        exit;
    }

    public function vaciarCarrito()
    {
        $_SESSION['carrito'] = [];

        header('Location: index.php?accion=carrito');
        exit;
    }


    
public function continuarCompra()
{
    if (empty($_SESSION['carrito'])) {
        header('Location: index.php?accion=carrito');
        exit;
    }

    // Si no ha iniciado sesión, guardar el destino y pedir acceso.
    if (empty($_SESSION['usuario']['id_usuario'])) {
        $_SESSION['despues_login'] = 'index.php?accion=carrito';
        header('Location: index.php?accion=login');
        exit;
    }

    // Si ya inició sesión, mostrar el carrito y sus datos de entrega.
    header('Location: index.php?accion=carrito#datos-pedido');
    exit;
}




    public function guardarDatosPedido()
    {
        if (empty($_SESSION['usuario']['id_usuario'])) {
    $_SESSION['despues_login'] = 'index.php?accion=carrito';
    header('Location: index.php?accion=login');
    exit;
}

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?accion=carrito');
            exit;
        }

        if (empty($_SESSION['carrito'])) {
            header('Location: index.php?accion=carrito');
            exit;
        }

        $servicio = $_POST['tipo_servicio'] ?? '';
        $nombre = trim($_POST['nombre'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        $referencias = trim($_POST['referencias'] ?? '');
        $observaciones = trim($_POST['observaciones'] ?? '');

        $errores = [];

        if (!in_array($servicio, ['domicilio', 'recoger'], true)) {
            $errores[] = 'Selecciona cómo quieres recibir tu pedido.';
        }

        if ($nombre === '') {
            $errores[] = 'Escribe tu nombre completo.';
        }

        if (!preg_match('/^[0-9+\s()-]{10,20}$/', $telefono)) {
            $errores[] = 'Escribe un teléfono válido.';
        }

        if ($servicio === 'domicilio' && $direccion === '') {
            $errores[] = 'Escribe la dirección de entrega.';
        }

        if (!empty($errores)) {
            $_SESSION['errores_pedido'] = $errores;
            $_SESSION['datos_pedido'] = [
                'tipo_servicio' => $servicio,
                'nombre' => $nombre,
                'telefono' => $telefono,
                'direccion' => $direccion,
                'referencias' => $referencias,
                'observaciones' => $observaciones
            ];

            header('Location: index.php?accion=carrito');
            exit;
        }

        $_SESSION['datos_pedido'] = [
            'tipo_servicio' => $servicio,
            'nombre' => $nombre,
            'telefono' => $telefono,
            'direccion' => $direccion,
            'referencias' => $referencias,
            'observaciones' => $observaciones
        ];

        unset($_SESSION['errores_pedido']);

        header('Location: index.php?accion=revisar');
exit;
    }



    public function revisarPedido()
    {

        if (empty($_SESSION['usuario']['id_usuario'])) {
    $_SESSION['despues_login'] = 'index.php?accion=carrito';
    header('Location: index.php?accion=login');
    exit;
}

        if (empty($_SESSION['carrito'])) {
            header('Location: index.php?accion=carrito');
            exit;
        }

        if (empty($_SESSION['datos_pedido'])) {
            header('Location: index.php?accion=carrito');
            exit;
        }

        $productosCarrito = [];
        $total = 0;

        foreach ($_SESSION['carrito'] as $id => $cantidad) {
            $producto = $this->catalogoModel->obtenerProductoPorId((int) $id);

            if (!$producto) {
                unset($_SESSION['carrito'][$id]);
                continue;
            }

            $cantidad = (int) $cantidad;

            if ($cantidad < 1 || $cantidad > (int) $producto['stock_disponible']) {
                header('Location: index.php?accion=carrito');
                exit;
            }

            $producto['cantidad'] = $cantidad;
            $producto['subtotal'] = (float) $producto['precio'] * $cantidad;

            $productosCarrito[] = $producto;
            $total += $producto['subtotal'];
        }

        if (empty($productosCarrito)) {
            header('Location: index.php?accion=carrito');
            exit;
        }

        $datosPedido = $_SESSION['datos_pedido'];

        require __DIR__ . '/../views/home/revisar.php';
    }





public function continuarAlPago(): void
{
    if (empty($_SESSION['usuario']['id_usuario'])) {
        $_SESSION['despues_login'] = 'index.php?accion=revisar';
        header('Location: index.php?accion=login');
        exit;
    }

    if (empty($_SESSION['carrito']) || empty($_SESSION['datos_pedido'])) {
        header('Location: index.php?accion=carrito');
        exit;
    }

    $productosCarrito = [];
    $total = 0;

    foreach ($_SESSION['carrito'] as $id => $cantidad) {
        $producto = $this->catalogoModel->obtenerProductoPorId((int) $id);

        if (!$producto || (int) $cantidad < 1 ||
            (int) $cantidad > (int) $producto['stock_disponible']) {
            header('Location: index.php?accion=carrito');
            exit;
        }

        $producto['cantidad'] = (int) $cantidad;
        $producto['subtotal'] = (float) $producto['precio'] * (int) $cantidad;

        $productosCarrito[] = $producto;
        $total += $producto['subtotal'];
    }

    if (empty($productosCarrito)) {
        header('Location: index.php?accion=carrito');
        exit;
    }

    $datosPedido = $_SESSION['datos_pedido'];

    require __DIR__ . '/../views/home/pago.php';
}



public function confirmarPedido(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: index.php?accion=pagar');
        exit;
    }

    if (empty($_SESSION['usuario']['id_usuario'])) {
        $_SESSION['despues_login'] = 'index.php?accion=revisar';
        header('Location: index.php?accion=login');
        exit;
    }

    if (empty($_SESSION['carrito']) || empty($_SESSION['datos_pedido'])) {
        header('Location: index.php?accion=carrito');
        exit;
    }

    // Validar el token del formulario.
    $tokenFormulario = $_POST['token'] ?? '';
    $tokenSesion = $_SESSION['token_confirmacion'] ?? '';

    if (
        $tokenSesion === '' ||
        !is_string($tokenFormulario) ||
        !hash_equals($tokenSesion, $tokenFormulario)
    ) {
        $_SESSION['error_pedido'] =
            'El formulario venció o ya fue procesado. Revisa tu pedido e inténtalo nuevamente.';
        header('Location: index.php?accion=revisar');
        exit;
    }

    $metodoPago = $_POST['metodo_pago'] ?? '';
    $metodosPermitidos = ['efectivo', 'transferencia', 'tarjeta'];

    if (!in_array($metodoPago, $metodosPermitidos, true)) {
        $_SESSION['error_pedido'] = 'Selecciona un método de pago válido.';
        header('Location: index.php?accion=pagar');
        exit;
    }

    // Preparar los productos usando el carrito de la sesión.
    $productosCarrito = [];

    foreach ($_SESSION['carrito'] as $id => $cantidad) {
        $productosCarrito[] = [
            'id_producto' => (int) $id,
            'cantidad' => (int) $cantidad
        ];
    }

    require_once __DIR__ . '/../models/PedidoModel.php';

    try {
        $pedidoModel = new PedidoModel();

        $idPedido = $pedidoModel->crearPedido(
            (int) $_SESSION['usuario']['id_usuario'],
            $productosCarrito,
            $_SESSION['datos_pedido'],
            $metodoPago
        );

        // Evitar que se vuelva a enviar el mismo formulario.
        unset($_SESSION['token_confirmacion']);
        $_SESSION['carrito'] = [];
        unset($_SESSION['datos_pedido'], $_SESSION['errores_pedido']);

        $_SESSION['pedido_creado'] = [
            'id_pedido' => $idPedido,
            'metodo_pago' => $metodoPago
        ];

        header('Location: index.php?accion=pedido-confirmado');
        exit;

    } catch (Throwable $e) {
        error_log('Error al crear pedido: ' . $e->getMessage());

        $_SESSION['error_pedido'] =
            'No fue posible registrar el pedido. Tu carrito se conserva; inténtalo nuevamente.';

        header('Location: index.php?accion=pagar');
        exit;
    }
}


public function misPedidos(): void
{
    if (empty($_SESSION['usuario']['id_usuario'])) {
        $_SESSION['despues_login'] = 'index.php?accion=mis-pedidos';
        header('Location: index.php?accion=login');
        exit;
    }

    require_once __DIR__ . '/../models/PedidoModel.php';

    try {
        $pedidoModel = new PedidoModel();

        $pedidos = $pedidoModel->listarPedidosUsuario(
            (int) $_SESSION['usuario']['id_usuario']
        );

        require __DIR__ . '/../views/home/mispedidos.php';

    } catch (Throwable $e) {
        error_log('Error al consultar pedidos: ' . $e->getMessage());

        http_response_code(500);
        echo 'No fue posible cargar tus pedidos. Inténtalo más tarde.';
    }
}


public function confirmarReservaEvento(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (empty($_SESSION['usuario']['id_usuario'])) {
        header('Location: index.php?accion=login');
        exit;
    }

    $reservaCreada = $_SESSION['reserva_evento_creada'] ?? null;

    if (empty($reservaCreada['id_reserva'])) {
        header('Location: index.php?accion=eventos');
        exit;
    }

    require_once __DIR__ . '/../models/ReservaEventoModel.php';

    $reservaModel = new ReservaEventoModel();

    $reserva = $reservaModel->obtenerReserva(
        (int) $reservaCreada['id_reserva'],
        (int) $_SESSION['usuario']['id_usuario']
    );

    if (!$reserva) {
        unset($_SESSION['reserva_evento_creada']);
        header('Location: index.php?accion=eventos');
        exit;
    }

    require __DIR__ . '/../views/home/confirmacion-reserva-evento.php';
}


}
