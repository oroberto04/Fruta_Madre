
<?php

require_once __DIR__ . '/../models/AdminModel.php';

class ProductosAdminController
{
    private AdminModel $model;

    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $this->model = new AdminModel();

        if (empty($_SESSION['csrf_productos'])) {
            $_SESSION['csrf_productos'] = bin2hex(random_bytes(32));
        }
    }

    private function verificarAdmin(): void
    {
        if (empty($_SESSION['usuario']['id_usuario'])) {
            header('Location: index.php?accion=login');
            exit;
        }

        if (($_SESSION['usuario']['rol'] ?? '') !== 'admin') {
            http_response_code(403);
            exit('No tienes permisos para administrar productos.');
        }
    }

    private function validarCsrf(): void
    {
        $token = $_POST['csrf_token'] ?? '';

        if (
            !is_string($token) ||
            !hash_equals($_SESSION['csrf_productos'] ?? '', $token)
        ) {
            http_response_code(419);
            exit('La sesión del formulario expiró. Recarga la página.');
        }
    }

    public function index(): void
    {
        $this->verificarAdmin();

        $estado = $_GET['estado'] ?? '';

        if ($estado === 'poco_stock') {
            $estado = 'bajo';
        }

        $orden = $_GET['orden'] ?? 'recientes';

        $equivalenciasOrden = [
            'precio_menor' => 'precio_asc',
            'precio_mayor' => 'precio_desc'
        ];

        $orden = $equivalenciasOrden[$orden] ?? $orden;

        $filtros = [
            'busqueda' => trim($_GET['busqueda'] ?? $_GET['q'] ?? ''),
            'categoria' => $_GET['categoria'] ?? '',
            'estado' => $estado,
            'orden' => $orden
        ];

        $todos = $this->model->obtenerProductosAdmin($filtros);
        $categorias = $this->model->obtenerCategoriasAdmin();

        $productoEditar = null;

        if (!empty($_GET['editar'])) {
            $productoEditar = $this->model->obtenerProductoAdmin(
                (int) $_GET['editar']
            );
        }

        $total = count($todos);

        $activos = count(array_filter($todos, function ($p) {
            return in_array($p['activo'], [true, 't', '1', 1], true);
        }));

        $bajoStock = count(array_filter($todos, function ($p) {
            return ($p['estado_calculado'] ?? '') === 'bajo';
        }));

        $agotados = count(array_filter($todos, function ($p) {
            return ($p['estado_calculado'] ?? '') === 'agotado';
        }));

        $indicadores = [
            'activos' => $activos,
            'bajo_stock' => $bajoStock,
            'agotados' => $agotados,
            'inactivos' => $total - $activos
        ];

        $csrfToken = $_SESSION['csrf_productos'];

        $mensaje = $_SESSION['mensaje_productos'] ?? null;
        unset($_SESSION['mensaje_productos']);

        // Compatibilidad con la vista.
        $_SESSION['mensaje_producto'] = $mensaje;

        require __DIR__ . '/../views/admin/productos.php';
    }

    public function guardar(): void
    {
        $this->verificarAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirigir();
        }

        $this->validarCsrf();

        $id = filter_var(
            $_POST['id_producto'] ?? '',
            FILTER_VALIDATE_INT
        );

        $nombre = trim($_POST['nombre'] ?? '');
        $sku = trim($_POST['sku'] ?? '');

        $categoria = filter_var(
            $_POST['id_categoria'] ?? '',
            FILTER_VALIDATE_INT
        );

        $precio = filter_var(
            $_POST['precio'] ?? '',
            FILTER_VALIDATE_FLOAT
        );

        $costo = filter_var(
            $_POST['costo'] ?? '0',
            FILTER_VALIDATE_FLOAT
        );

        $stock = filter_var(
            $_POST['stock_disponible'] ?? '0',
            FILTER_VALIDATE_INT
        );

        $minimo = filter_var(
            $_POST['stock_minimo'] ?? '0',
            FILTER_VALIDATE_INT
        );

        if (
            $nombre === '' ||
            mb_strlen($nombre) > 255 ||
            !$categoria ||
            $precio === false ||
            $precio < 0 ||
            $costo === false ||
            $costo < 0 ||
            $stock === false ||
            $stock < 0 ||
            $minimo === false ||
            $minimo < 0
        ) {
            $this->mensaje(
                'error',
                'Revisa los campos obligatorios y los valores numéricos.'
            );
            $this->redirigir();
        }

        $productoActual = $id
            ? $this->model->obtenerProductoAdmin($id)
            : null;

        if ($id && !$productoActual) {
            $this->mensaje('error', 'El producto no existe.');
            $this->redirigir();
        }

        $imagen = $productoActual['imagen_url'] ?? null;

        if (!empty($_POST['imagen_url_actual'])) {
            $imagen = trim($_POST['imagen_url_actual']);
        }

        if (!empty($_POST['quitar_imagen'])) {
            $imagen = null;
        }

        if (
            isset($_FILES['imagen']) &&
            $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE
        ) {
            $archivo = $_FILES['imagen'];

            if (
                $archivo['error'] !== UPLOAD_ERR_OK ||
                $archivo['size'] > 5 * 1024 * 1024
            ) {
                $this->mensaje(
                    'error',
                    'La imagen supera el límite de 5 MB o no se pudo cargar.'
                );
                $this->redirigir();
            }

            $info = @getimagesize($archivo['tmp_name']);
            $mime = $info['mime'] ?? '';

            $extensiones = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp'
            ];

            if (!isset($extensiones[$mime])) {
                $this->mensaje(
                    'error',
                    'Utiliza una imagen JPG, PNG o WEBP.'
                );
                $this->redirigir();
            }

            $directorio = dirname(__DIR__, 2) . '/public/images/productos';

            if (
                !is_dir($directorio) &&
                !mkdir($directorio, 0755, true) &&
                !is_dir($directorio)
            ) {
                $this->mensaje(
                    'error',
                    'No se pudo preparar la carpeta de imágenes.'
                );
                $this->redirigir();
            }

            $nombreArchivo = 'producto_' .
                bin2hex(random_bytes(12)) .
                '.' . $extensiones[$mime];

            if (
                !move_uploaded_file(
                    $archivo['tmp_name'],
                    $directorio . '/' . $nombreArchivo
                )
            ) {
                $this->mensaje(
                    'error',
                    'No se pudo guardar la imagen.'
                );
                $this->redirigir();
            }

            $imagen = 'public/images/productos/' . $nombreArchivo;
        }

        $datos = [
            'id_categoria' => $categoria,
            'nombre' => $nombre,
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'ingredientes' => trim($_POST['ingredientes'] ?? ''),
            'precio' => $precio,
            'costo' => $costo,
            'imagen_url' => $imagen,
            'stock_disponible' => $stock,
            'stock_minimo' => $minimo,
            'activo' => ($_POST['activo'] ?? '1') === '1',
            'subcategoria' => trim($_POST['subcategoria'] ?? ''),
            'incluir_en_paquetes' => isset($_POST['permite_paquetes'])
        ];

        try {
            if ($id) {
                $this->model->actualizarProducto($id, $datos);
                $this->mensaje('success', 'Producto actualizado correctamente.');
            } else {
                $nuevoId = $this->model->crearProducto($datos);
                $this->mensaje(
                    'success',
                    'Producto creado correctamente. Folio: #' . $nuevoId
                );
            }
        } catch (PDOException $e) {
            error_log('Productos admin: ' . $e->getMessage());

            $this->mensaje(
                'error',
                'No se pudo guardar el producto. Revisa los datos y el SKU.'
            );
        }

        $this->redirigir();
    }

    public function eliminar(): void
    {
        $this->verificarAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirigir();
        }

        $this->validarCsrf();

        $id = filter_var(
            $_POST['id_producto'] ?? '',
            FILTER_VALIDATE_INT
        );

        if (
            !$id ||
            !$this->model->desactivarProducto($id)
        ) {
            $this->mensaje(
                'error',
                'No se pudo dar de baja el producto.'
            );
        } else {
            $this->mensaje(
                'success',
                'Producto dado de baja correctamente.'
            );
        }

        $this->redirigir();
    }

    public function exportar(): void
    {
        $this->verificarAdmin();

        $productos = $this->model->obtenerProductosAdmin([
            'busqueda' => trim($_GET['busqueda'] ?? $_GET['q'] ?? ''),
            'categoria' => $_GET['categoria'] ?? '',
            'estado' => $_GET['estado'] ?? '',
            'orden' => $_GET['orden'] ?? 'recientes'
        ]);

        header('Content-Type: text/csv; charset=UTF-8');
        header(
            'Content-Disposition: attachment; filename="fruta-madre-productos-' .
            date('Y-m-d') . '.csv"'
        );

        $salida = fopen('php://output', 'w');
        fwrite($salida, "\xEF\xBB\xBF");

        fputcsv($salida, [
            'SKU',
            'Producto',
            'Categoría',
            'Precio',
            'Costo',
            'Stock',
            'Stock mínimo',
            'Estado',
            'Activo'
        ]);

        foreach ($productos as $p) {
            fputcsv($salida, [
                $p['sku'],
                $p['nombre'],
                $p['nombre_categoria'],
                $p['precio'],
                $p['costo'],
                $p['stock_disponible'],
                $p['stock_minimo'],
                $p['estado_calculado'],
                in_array($p['activo'], [true, 't', '1', 1], true)
                    ? 'Sí'
                    : 'No'
            ]);
        }

        fclose($salida);
        exit;
    }

    private function mensaje(string $tipo, string $texto): void
    {
        $_SESSION['mensaje_productos'] = [
            'tipo' => $tipo,
            'texto' => $texto
        ];
    }

    private function redirigir(): void
    {
        header('Location: index.php?accion=admin-productos');
        exit;
    }
}
