
<?php

require_once __DIR__ . '/../models/AdminModel.php';

class AdminController
{
    private AdminModel $adminModel;

    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $this->adminModel = new AdminModel();
    }

    /**
     * Comprueba que exista una sesión administrativa.
     */
    private function verificarAdministrador(): void
    {
        if (empty($_SESSION['usuario']['id_usuario'])) {
            header('Location: index.php?accion=login');
            exit;
        }

        if (($_SESSION['usuario']['rol'] ?? '') !== 'admin') {
            http_response_code(403);
            exit('No tienes permisos para acceder al panel administrativo.');
        }
    }

    /**
     * Página principal del administrador.
     */
    public function index(): void
    {
        $this->verificarAdministrador();

        $resumen = $this->adminModel->obtenerResumen();

        require __DIR__ . '/../views/admin/panel.php';
    }

    /**
     * Muestra todos los pedidos recibidos.
     */
    public function pedidos(): void
    {
        $this->verificarAdministrador();

        $pedidos = $this->adminModel->obtenerPedidos();

        require __DIR__ . '/../views/admin/pedidos.php';
    }

    /**
     * Actualiza el estado de un pedido.
     */
    public function actualizarEstadoPedido(): void
    {
        $this->verificarAdministrador();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?accion=admin-pedidos');
            exit;
        }

        $idPedido = filter_input(INPUT_POST, 'id_pedido', FILTER_VALIDATE_INT);
        $estado = trim($_POST['estado_pedido'] ?? '');

        if (!$idPedido || !$this->adminModel->actualizarEstadoPedido($idPedido, $estado)) {
            $_SESSION['mensaje_admin'] = [
                'tipo' => 'error',
                'texto' => 'No se pudo actualizar el estado del pedido.'
            ];
        } else {
            $_SESSION['mensaje_admin'] = [
                'tipo' => 'success',
                'texto' => 'El estado del pedido se actualizó correctamente.'
            ];
        }

        header('Location: index.php?accion=admin-pedidos');
        exit;
    }

    /**
     * Actualiza el estado de una entrega.
     */
    public function actualizarEstadoEntrega(): void
    {
        $this->verificarAdministrador();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?accion=admin-pedidos');
            exit;
        }

        $idEntrega = filter_input(INPUT_POST, 'id_entrega', FILTER_VALIDATE_INT);
        $estado = trim($_POST['estado_entrega'] ?? '');

        if (!$idEntrega || !$this->adminModel->actualizarEstadoEntrega($idEntrega, $estado)) {
            $_SESSION['mensaje_admin'] = [
                'tipo' => 'error',
                'texto' => 'No se pudo actualizar el estado de la entrega.'
            ];
        } else {
            $_SESSION['mensaje_admin'] = [
                'tipo' => 'success',
                'texto' => 'El estado de la entrega se actualizó correctamente.'
            ];
        }

        header('Location: index.php?accion=admin-pedidos');
        exit;
    }


    /**
     * Confirma el pago de un pedido desde el panel administrativo.
     */
    public function confirmarPago(): void
    {
        $this->verificarAdministrador();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?accion=admin-pedidos');
            exit;
        }

        $idPedido = filter_input(
            INPUT_POST,
            'id_pedido',
            FILTER_VALIDATE_INT
        );

        if (!$idPedido || $idPedido <= 0) {
            $_SESSION['mensaje_admin'] = [
                'tipo' => 'error',
                'texto' => 'El número de pedido no es válido.'
            ];

            header('Location: index.php?accion=admin-pedidos');
            exit;
        }

        $resultado = $this->adminModel->confirmarPago($idPedido);

        $_SESSION['mensaje_admin'] = $resultado
            ? [
                'tipo' => 'success',
                'texto' => 'El pago del pedido #' . $idPedido . ' fue confirmado.'
            ]
            : [
                'tipo' => 'error',
                'texto' => 'No se pudo confirmar el pago. Puede que ya esté confirmado o que no exista un pago pendiente.'
            ];

        header('Location: index.php?accion=admin-pedidos');
        exit;
    }


}
