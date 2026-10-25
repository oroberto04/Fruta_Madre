
<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$errores = $_SESSION['errores_auth'] ?? [];
unset($_SESSION['errores_auth']);

$datos = $_SESSION['datos_registro'] ?? [];
unset($_SESSION['datos_registro']);

function escaparRegistro($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta | Fruta Madre</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #FFF4DF;
            color: #29231F;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
        }
        .contenedor {
            width: 100%;
            max-width: 460px;
            background: white;
            padding: 32px;
            border-radius: 18px;
            box-shadow: 0 8px 30px #29231f12;
        }
        .marca { color: #477A45; font-size: 25px; font-weight: bold; }
        .subtitulo { color: #756b61; line-height: 1.5; }
        label { display: block; font-weight: bold; margin: 17px 0 7px; }
        input {
            width: 100%;
            padding: 13px;
            border: 1px solid #ded5c9;
            border-radius: 9px;
            font-size: 16px;
        }
        input:focus { outline: 2px solid #477A45; border-color: transparent; }
        button {
            width: 100%;
            padding: 14px;
            margin-top: 24px;
            background: #477A45;
            color: white;
            border: 0;
            border-radius: 9px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }
        a { color: #477A45; font-weight: bold; }
        .mensaje-error {
            background: #fff0ee;
            border: 1px solid #e7b7b0;
            padding: 12px;
            border-radius: 9px;
            margin-top: 16px;
        }
        .pie { text-align: center; margin-top: 22px; }
    </style>
</head>
<body>
    <main class="contenedor">
        <div class="marca">FRUTA MADRE</div>
        <p class="subtitulo">Crea tu cuenta y disfruta de tus antojos favoritos.</p>

        <?php if (!empty($errores)): ?>
            <div class="mensaje-error">
                <?php foreach ($errores as $error): ?>
                    <p><?= escaparRegistro($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="index.php?accion=registro" method="POST">
            <label for="nombre">Nombre completo</label>
            <input
                id="nombre"
                name="nombre"
                type="text"
                maxlength="255"
                autocomplete="name"
                value="<?= escaparRegistro($datos['nombre'] ?? '') ?>"
                required
            >

            <label for="correo">Correo electrónico</label>
            <input
                id="correo"
                name="correo"
                type="email"
                maxlength="255"
                autocomplete="email"
                value="<?= escaparRegistro($datos['correo'] ?? '') ?>"
                required
            >

            <label for="telefono">Teléfono</label>
            <input
                id="telefono"
                name="telefono"
                type="tel"
                maxlength="20"
                autocomplete="tel"
                value="<?= escaparRegistro($datos['telefono'] ?? '') ?>"
                required
            >

            <label for="contrasena">Contraseña</label>
            <input
                id="contrasena"
                name="contrasena"
                type="password"
                minlength="8"
                autocomplete="new-password"
                required
            >

            <label for="confirmar">Confirmar contraseña</label>
            <input
                id="confirmar"
                name="confirmar"
                type="password"
                minlength="8"
                autocomplete="new-password"
                required
            >

            <button type="submit">Crear mi cuenta</button>
        </form>

        <p class="pie">
            ¿Ya tienes cuenta?
            <a href="index.php?accion=login">Inicia sesión</a>
        </p>

        <p class="pie">
            <a href="index.php">Volver a Fruta Madre</a>
        </p>
    </main>
</body>
</html>
