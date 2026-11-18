
<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/app/controllers/HomeController.php';

$controller = new HomeController();
$accion = $_GET['accion'] ?? null;
$metodo = $_SERVER['REQUEST_METHOD'];

// AUTENTICACIÓN
// STITCH ADMIN FUNCIONAL 36 + 37
if ($accion === 'imagen-producto-stitch') {
    require_once __DIR__ . '/app/controllers/ImagenesStitchController.php';
    ImagenesStitchController::mostrar();
} elseif (in_array($accion, ['admin-productos','admin-productos-guardar','admin-productos-desactivar','admin-productos-exportar'], true)) {
    require_once __DIR__ . '/app/controllers/ProductosStitchController.php';
    $stitch = new ProductosStitchController();
    if ($accion === 'admin-productos') $stitch->index();
    elseif ($accion === 'admin-productos-guardar' && $metodo === 'POST') $stitch->guardar();
    elseif ($accion === 'admin-productos-desactivar' && $metodo === 'POST') $stitch->desactivar();
    elseif ($accion === 'admin-productos-exportar') $stitch->exportar();
    else { http_response_code(405); exit('Método no permitido.'); }
} elseif (in_array($accion, [
    'admin-categorias-stitch','admin-paquetes-stitch','admin-carritos-stitch','admin-reservas-stitch',
    'admin-clientes-stitch','admin-inventario-stitch','admin-reportes-stitch',
    'admin-categoria-guardar-stitch','admin-paquete-guardar-stitch','admin-carrito-guardar-stitch',
    'admin-inventario-guardar-stitch','admin-reserva-guardar-stitch','admin-reportes-csv'
],true)) {
    require_once __DIR__ . '/app/controllers/OperacionesStitchController.php';
    $operaciones = new OperacionesStitchController();
    $tipos = ['admin-categoria-guardar-stitch'=>'categoria','admin-paquete-guardar-stitch'=>'paquete',
        'admin-carrito-guardar-stitch'=>'carrito','admin-inventario-guardar-stitch'=>'inventario',
        'admin-reserva-guardar-stitch'=>'reserva'];
    if (isset($tipos[$accion]) && $metodo === 'POST') $operaciones->guardar($tipos[$accion]);
    elseif ($accion === 'admin-reportes-csv') $operaciones->reporteCSV();
    elseif (isset($tipos[$accion])) {http_response_code(405);exit('Método no permitido.');}
    else $operaciones->index($accion);
} elseif ($accion === 'registro') {
    require_once __DIR__ . '/app/controllers/AuthController.php';
    $auth = new AuthController();

    if ($metodo === 'POST') {
        $auth->registrar();
    } else {
        $auth->mostrarRegistro();
    }

} elseif ($accion === 'login') {
    require_once __DIR__ . '/app/controllers/AuthController.php';
    $auth = new AuthController();

    if ($metodo === 'POST') {
        $auth->login();
    } else {
        $auth->mostrarLogin();
    }

} elseif ($accion === 'logout') {
    require_once __DIR__ . '/app/controllers/AuthController.php';
    $auth = new AuthController();
    $auth->cerrarSesion();

// ADMINISTRACIÓN
} elseif (
    in_array($accion, [
        'admin',
        'admin-pedidos',
        'admin-actualizar-pedido',
        'admin-actualizar-entrega',
        'admin-confirmar-pago'
    ], true)
) {
    require_once __DIR__ . '/app/controllers/AdminController.php';

    $admin = new AdminController();

    switch ($accion) {
        case 'admin':
            $admin->index();
            break;

        case 'admin-pedidos':
            $admin->pedidos();
            break;

        case 'admin-actualizar-pedido':
            if ($metodo === 'POST') {
                $admin->actualizarEstadoPedido();
            } else {
                header('Location: index.php?accion=admin-pedidos');
                exit;
            }
            break;

        case 'admin-actualizar-entrega':
            if ($metodo === 'POST') {
                $admin->actualizarEstadoEntrega();
            } else {
                header('Location: index.php?accion=admin-pedidos');
                exit;
            }
            break;

        case 'admin-confirmar-pago':
            if ($metodo === 'POST') {
                $admin->confirmarPago();
            } else {
                header('Location: index.php?accion=admin-pedidos');
                exit;
            }
            break;
    }

// ACTUALIZAR PERFIL
} elseif ($metodo === 'POST' && $accion === 'actualizar-perfil') {

    if (empty($_SESSION['usuario']['id_usuario'])) {
        header('Location: index.php?accion=login');
        exit;
    }

    require_once __DIR__ . '/app/models/UsuarioModel.php';

    $usuarioModel = new UsuarioModel();

    $idUsuario = (int) $_SESSION['usuario']['id_usuario'];

    $nombre = trim($_POST['nombre_completo'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');

    if ($nombre === '' || $correo === '') {

        $_SESSION['mensaje_perfil'] = [
            'tipo' => 'error',
            'texto' => 'El nombre y el correo electrónico son obligatorios.'
        ];

    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {

        $_SESSION['mensaje_perfil'] = [
            'tipo' => 'error',
            'texto' => 'El correo electrónico no tiene un formato válido.'
        ];

    } elseif ($usuarioModel->correoEnUso($correo, $idUsuario)) {

        $_SESSION['mensaje_perfil'] = [
            'tipo' => 'error',
            'texto' => 'Ese correo electrónico ya está registrado por otro usuario.'
        ];

    } else {

        try {
            $actualizado = $usuarioModel->actualizarPerfil(
                $idUsuario,
                $nombre,
                $correo,
                $telefono
            );

            if ($actualizado) {

                $_SESSION['usuario']['nombre_completo'] = $nombre;
                $_SESSION['usuario']['correo'] = $correo;
                $_SESSION['usuario']['telefono'] = $telefono;

                $_SESSION['mensaje_perfil'] = [
                    'tipo' => 'success',
                    'texto' => 'Tus datos se actualizaron correctamente.'
                ];

            } else {

                $_SESSION['mensaje_perfil'] = [
                    'tipo' => 'error',
                    'texto' => 'No se pudieron actualizar tus datos.'
                ];
            }

        } catch (PDOException $e) {

            $_SESSION['mensaje_perfil'] = [
                'tipo' => 'error',
                'texto' => 'Ocurrió un error al actualizar tu información.'
            ];
        }
    }

    header('Location: index.php?accion=cuenta');
    exit;

// CUENTA DEL CLIENTE
} elseif ($accion === 'cuenta') {

    if (empty($_SESSION['usuario']['id_usuario'])) {
        header('Location: index.php?accion=login');
        exit;
    }

    require_once __DIR__ . '/app/models/UsuarioModel.php';

    $usuarioModel = new UsuarioModel();

    $usuario = $usuarioModel->obtenerPorId(
        (int) $_SESSION['usuario']['id_usuario']
    );

    if (!$usuario) {
        $_SESSION = [];
        session_destroy();

        header('Location: index.php?accion=login');
        exit;
    }

    $_SESSION['usuario']['nombre_completo'] = $usuario['nombre_completo'];
    $_SESSION['usuario']['correo'] = $usuario['correo'];
    $_SESSION['usuario']['telefono'] = $usuario['telefono'];
    $_SESSION['usuario']['rol'] = $usuario['rol'];

    // Diseño de cuenta del cliente.
    require __DIR__ . '/app/views/admin/index.php';

// CARRITO Y PEDIDOS
} elseif ($metodo === 'POST' && $accion === 'guardar-datos') {
    $controller->guardarDatosPedido();

} elseif ($metodo === 'POST' && $accion === 'agregar') {
    $controller->agregarAlCarrito(
        (int) ($_POST['id_producto'] ?? 0)
    );

} elseif ($metodo === 'POST' && $accion === 'eliminar') {
    $controller->eliminarDelCarrito(
        (int) ($_POST['id_producto'] ?? 0)
    );

} elseif ($metodo === 'POST' && $accion === 'actualizar-cantidad') {
    $controller->actualizarCantidadCarrito(
        (int) ($_POST['id_producto'] ?? 0),
        (int) ($_POST['cantidad'] ?? 1)
    );

} elseif ($metodo === 'POST' && $accion === 'vaciar') {
    $controller->vaciarCarrito();

} elseif ($accion === 'continuar') {
    $controller->continuarCompra();

} elseif ($accion === 'pagar') {
    $controller->continuarAlPago();

} elseif ($accion === 'mis-pedidos') {
    $controller->misPedidos();

} elseif ($metodo === 'POST' && $accion === 'confirmar-pedido') {
    $controller->confirmarPedido();

// MERCADO PAGO: solamente Checkout Pro con credenciales TEST.
} elseif ($accion === 'iniciar-pago-mp-prueba') {
    require_once __DIR__ . '/app/controllers/MercadoPagoPruebasController.php';
    (new MercadoPagoPruebasController())->iniciar();

} elseif ($accion === 'resultado-pago-mp-prueba') {
    require_once __DIR__ . '/app/controllers/MercadoPagoPruebasController.php';
    (new MercadoPagoPruebasController())->resultado();

} elseif ($accion === 'pedido-confirmado') {

    $pedido = $_SESSION['pedido_creado'] ?? null;

    if (!$pedido || empty($pedido['id_pedido'])) {
        header('Location: index.php');
        exit;
    }

    require __DIR__ . '/app/views/home/confirmacion.php';

} elseif ($accion === 'revisar') {

    if (empty($_SESSION['usuario']['id_usuario'])) {
        $_SESSION['despues_login'] = 'index.php?accion=revisar';
        header('Location: index.php?accion=login');
        exit;
    }

    $controller->revisarPedido();

} elseif ($accion === 'carrito') {
    $controller->carrito();

// CATÁLOGO Y PRODUCTOS
} elseif ($accion === 'catalogo') {
    $controller->catalogo();

} elseif ($accion === 'eventos') {
    $controller->eventos();

} elseif ($accion === 'reservar-evento') {
    $controller->reservarEvento();

} elseif ($accion === 'confirmacion-reserva-evento') {
    $controller->confirmarReservaEvento();

} elseif (isset($_GET['producto'])) {
    $controller->detalle((int) $_GET['producto']);

} else {
    $controller->index();
}
