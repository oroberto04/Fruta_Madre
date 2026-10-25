
<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$errores = $_SESSION['errores_auth'] ?? [];
unset($_SESSION['errores_auth']);

function escaparLogin($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión | Fruta Madre</title>
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
            max-width: 430px;
            background: white;
            padding: 32px;
            border-radius: 18px;
            box-shadow: 0 8px 30px #29231f12;
        }
        .marca { color: #477A45; font-size: 25px; font-weight: bold; }
        .subtitulo { color: #756b61; line-height: 1.5; }
        label { display: block; font-weight: bold; margin: 18px 0 7px; }
        input {
            width: 100%;
            padding: 13px;
            border: 1px solid #ded5c9;
            border-radius: 9px;
            font-size: 16px;
        }
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
        .error {
            background: #fff0ee;
            border: 1px solid #e7b7b0;
            border-radius: 9px;
            padding: 12px;
        }
        .pie { text-align: center; margin-top: 22px; }
    </style>
</head>
<body>
    <main class="contenedor">
        <div class="marca">FRUTA MADRE</div>
        <p class="subtitulo">Qué gusto tenerte de vuelta.</p>

        <?php if (!empty($errores)): ?>
            <div class="error">
                <?php foreach ($errores as $error): ?>
                    <p><?= escaparLogin($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="index.php?accion=login" method="POST">
            <label for="correo">Correo electrónico</label>
            <input
                id="correo"
                name="correo"
                type="email"
                maxlength="255"
                autocomplete="email"
                required
            >

            <label for="contrasena">Contraseña</label>
            <input
                id="contrasena"
                name="contrasena"
                type="password"
                autocomplete="current-password"
                required
            >

            <button type="submit">Iniciar sesión</button>
        </form>

        <p class="pie">
            ¿No tienes cuenta?
            <a href="index.php?accion=registro">Crear una cuenta</a>
        </p>

        <p class="pie">
            <a href="index.php">Volver a Fruta Madre</a>
        </p>
    </main>
</body>
</html>
