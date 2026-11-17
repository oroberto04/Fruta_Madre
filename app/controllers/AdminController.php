
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

        require __DIR__ . '/../views/admin/index.php';
    }
}
