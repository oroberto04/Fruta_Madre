
<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/app/controllers/HomeController.php';

$controller = new HomeController();
$accion = $_GET['accion'] ?? null;
$metodo = $_SERVER['REQUEST_METHOD'];

// AUTENTICACIÓN
if ($accion === 'registro') {
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

// CUENTA DEL CLIENTE
} elseif ($accion === 'cuenta') {
    if (empty($_SESSION['usuario']['id_usuario'])) {
        header('Location: index.php?accion=login');
        exit;
    }

    $usuario = $_SESSION['usuario'];
    $nombre = htmlspecialchars(
        $usuario['nombre_completo'] ?? '',
        ENT_QUOTES,
        'UTF-8'
    );
    $correo = htmlspecialchars(
        $usuario['correo'] ?? '',
        ENT_QUOTES,
        'UTF-8'
    );

    echo '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Mi cuenta | Fruta Madre</title>
        <style>
            * { box-sizing: border-box; }
            body {
                margin: 0;
                padding: 24px;
                font-family: Arial, sans-serif;
                background: #FFF4DF;
                color: #29231F;
            }
            main {
                max-width: 600px;
                margin: 50px auto;
                padding: 30px;
                background: white;
                border-radius: 16px;
            }
            h1 { color: #477A45; }
            a, button {
                display: inline-block;
                padding: 12px 16px;
                border: 0;
                border-radius: 8px;
                text-decoration: none;
                cursor: pointer;
                font-size: 15px;
            }
            a { background: #477A45; color: white; }
            button { background: #B83B32; color: white; }
            form { display: inline-block; margin-top: 16px; }
        </style>
    </head>
    <body>
        <main>
            <h1>¡Hola, ' . $nombre . '!</h1>
            <p>Bienvenido a tu cuenta de Fruta Madre.</p>
            <p><strong>Correo:</strong> ' . $correo . '</p>
            <p><a href="index.php">Ir al catálogo</a></p>
            <form action="index.php?accion=logout" method="POST">
                <button type="submit">Cerrar sesión</button>
            </form>
        </main>
    </body>
    </html>';

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

} elseif ($metodo === 'POST' && $accion === 'vaciar') {
    $controller->vaciarCarrito();


} elseif ($accion === 'continuar') {
    $controller->continuarCompra();

} elseif ($accion === 'pagar') {
    $controller->continuarAlPago();


} elseif ($metodo === 'POST' && $accion === 'confirmar-pedido') {
    $controller->confirmarPedido();

} elseif ($accion === 'pedido-confirmado') {
    // La vista de confirmación se conectará en el siguiente paso.
    $pedido = $_SESSION['pedido_creado'] ?? null;

    if (!$pedido) {
        header('Location: index.php');
        exit;
    }

    echo '<main style="max-width:600px;margin:60px auto;font-family:Arial,sans-serif">
        <h1>¡Pedido registrado!</h1>
        <p>Tu número de pedido es: <strong>#'
        . (int) $pedido['id_pedido'] .
        '</strong></p>
        <p>El pedido quedó registrado. El estado del pago debe verificarse
        según el método elegido.</p>
        <a href="index.php">Volver al catálogo</a>
    </main>';




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
} elseif (isset($_GET['producto'])) {
    $controller->detalle((int) $_GET['producto']);

} else {
    $controller->index();
}
