<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../app/models/CatalogoModel.php';

try {

    $catalogo = new CatalogoModel();

    $productos = $catalogo->obtenerProductos();
    $categorias = $catalogo->obtenerCategorias();

    echo json_encode([
        'success' => true,
        'categorias' => $categorias,
        'productos' => $productos
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'mensaje' => 'Error al obtener el catálogo.'
    ], JSON_UNESCAPED_UNICODE);
}