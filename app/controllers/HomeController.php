
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





}
