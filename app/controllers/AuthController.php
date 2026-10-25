
<?php

require_once __DIR__ . '/../models/UsuarioModel.php';

class AuthController
{
    private UsuarioModel $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new UsuarioModel();

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public function mostrarRegistro(): void
    {
        require __DIR__ . '/../views/auth/registro.php';
    }

    
public function registrar(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $this->mostrarRegistro();
        return;
    }

    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';

    $errores = [];

    if ($nombre === '' || mb_strlen($nombre) > 255) {
        $errores[] = 'Escribe tu nombre completo.';
    }

    if (
        !filter_var($correo, FILTER_VALIDATE_EMAIL) ||
        mb_strlen($correo) > 255
    ) {
        $errores[] = 'Escribe un correo electrónico válido.';
    }

    if (!preg_match('/^[0-9+\s()-]{10,20}$/', $telefono)) {
        $errores[] = 'Escribe un teléfono válido.';
    }

    if (strlen($contrasena) < 8) {
        $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
    }

    if ($contrasena !== $confirmar) {
        $errores[] = 'Las contraseñas no coinciden.';
    }

    if (strlen($contrasena) > 72) {
        $errores[] = 'La contraseña es demasiado larga.';
    }

    if (empty($errores)) {
        try {
            if ($this->usuarioModel->existeCorreo($correo)) {
                $errores[] = 'Ese correo ya tiene una cuenta registrada.';
            } else {
                $hash = password_hash($contrasena, PASSWORD_DEFAULT);

                $idUsuario = $this->usuarioModel->registrar(
                    $nombre,
                    $correo,
                    $telefono,
                    $hash
                );

                session_regenerate_id(true);

                $_SESSION['usuario'] = [
                    'id_usuario' => $idUsuario,
                    'nombre_completo' => $nombre,
                    'correo' => $correo,
                    'rol' => 'cliente'
                ];

                if (
                    !empty($_SESSION['despues_login']) &&
                    !empty($_SESSION['carrito'])
                ) {
                    $destino = $_SESSION['despues_login'];
                    unset($_SESSION['despues_login']);

                    header('Location: ' . $destino);
                } elseif (
                    !empty($_SESSION['carrito']) &&
                    !empty($_SESSION['datos_pedido'])
                ) {
                    header('Location: index.php?accion=revisar');
                } else {
                    header('Location: index.php?accion=cuenta');
                }

                exit;
            }
        } catch (PDOException $e) {
            error_log('Error al registrar usuario: ' . $e->getMessage());
            $errores[] = 'No fue posible completar el registro. Inténtalo nuevamente.';
        }
    }

    $_SESSION['errores_auth'] = $errores;
    $_SESSION['datos_registro'] = [
        'nombre' => $nombre,
        'correo' => $correo,
        'telefono' => $telefono
    ];

    header('Location: index.php?accion=registro');
    exit;
}


    public function mostrarLogin(): void
    {
        require __DIR__ . '/../views/auth/login.php';
    }

    
public function login(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $this->mostrarLogin();
        return;
    }

    $correo = trim($_POST['correo'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';

    $usuario = false;

    if ($correo !== '' && $contrasena !== '') {
        try {
            $usuario = $this->usuarioModel->buscarPorCorreo($correo);
        } catch (PDOException $e) {
            error_log('Error al iniciar sesión: ' . $e->getMessage());
        }
    }

    if (
        !$usuario ||
        !password_verify($contrasena, $usuario['contrasena_hash'])
    ) {
        $_SESSION['errores_auth'] = [
            'Correo o contraseña incorrectos.'
        ];

        header('Location: index.php?accion=login');
        exit;
    }

    session_regenerate_id(true);

    $_SESSION['usuario'] = [
        'id_usuario' => (int) $usuario['id_usuario'],
        'nombre_completo' => $usuario['nombre_completo'],
        'correo' => $usuario['correo'],
        'rol' => $usuario['rol']
    ];

    if (
        !empty($_SESSION['despues_login']) &&
        !empty($_SESSION['carrito'])
    ) {
        $destino = $_SESSION['despues_login'];
        unset($_SESSION['despues_login']);

        header('Location: ' . $destino);
    } elseif (
        !empty($_SESSION['carrito']) &&
        !empty($_SESSION['datos_pedido'])
    ) {
        header('Location: index.php?accion=revisar');
    } else {
        header('Location: index.php?accion=cuenta');
    }

    exit;
}


    public function cerrarSesion(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $parametros = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $parametros['path'],
                $parametros['domain'],
                $parametros['secure'],
                $parametros['httponly']
            );
        }

        session_destroy();

        header('Location: index.php');
        exit;
    }
}
