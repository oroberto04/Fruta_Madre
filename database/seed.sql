-- Datos iniciales de Fruta Madre.
-- Ejecutar después de database/schema.sql.

BEGIN;

INSERT INTO categoria
    (id_categoria, nombre_categoria, descripcion, activa)
VALUES
    (6, 'Antojitos frutales',
     'Fruta fresca preparada con diferentes ingredientes y toppings.', TRUE),
    (7, 'Dulces',
     'Postres, dulces y antojos para compartir.', TRUE),
    (8, 'Salados',
     'Botanas y antojos salados preparados al momento.', TRUE),
    (9, 'Pasteles',
     'Pasteles para celebraciones y ocasiones especiales.', TRUE),
    (10, 'Arreglos florales',
     'Arreglos florales para regalar y celebrar.', TRUE),
    (11, 'Carritos de snacks',
     'Servicios de carritos de snacks para eventos.', TRUE);

INSERT INTO producto
    (id_producto, id_categoria, nombre, descripcion, ingredientes,
     precio, imagen_url, stock_disponible, activo)
VALUES
    (20, 6, 'Vaso Fruta Madre',
     'Vaso de fruta fresca preparada.',
     'Fruta fresca y toppings', 65.00, NULL, 16, TRUE),

    (21, 6, 'Mango Loco',
     'Mango preparado con una combinación de sabores.',
     'Mango y chamoy', 75.00, NULL, 14, TRUE),

    (22, 6, 'Fresas con crema',
     'Fresas frescas con crema.',
     'Fresas y crema', 80.00, NULL, 15, TRUE),

    (23, 7, 'Gomitas con chamoy',
     'Gomitas preparadas con chamoy.',
     'Gomitas y chamoy', 65.00, NULL, 20, TRUE),

    (24, 7, 'Manzana cubierta',
     'Manzana cubierta preparada.',
     'Manzana y cobertura', 55.00, NULL, 13, TRUE),

    (25, 6, 'Brocheta de fruta con chocolate',
     'Brocheta de fruta con chocolate.',
     'Fruta fresca y chocolate', 70.00, NULL, 15, TRUE),

    (26, 7, 'Mini fresas con chocolate',
     'Fresas pequeñas cubiertas de chocolate.',
     'Fresas y chocolate', 85.00, NULL, 15, TRUE),

    (27, 8, 'Papas preparadas',
     'Papas preparadas al gusto.',
     'Papas y aderezos', 70.00, NULL, 15, TRUE),

    (28, 8, 'Nachos Fruta Madre',
     'Nachos preparados.',
     'Totopos, queso y aderezos', 75.00, NULL, 15, TRUE),

    (29, 8, 'Chicharrón preparado',
     'Chicharrón preparado con acompañamientos.',
     'Chicharrón y aderezos', 80.00, NULL, 12, TRUE),

    (30, 9, 'Pastel de chocolate',
     'Pastel de chocolate para celebraciones.',
     'Chocolate', 450.00, NULL, 5, TRUE),

    (31, 9, 'Pastel de vainilla con fresas',
     'Pastel de vainilla con fresas.',
     'Vainilla y fresas', 480.00, NULL, 5, TRUE),

    (32, 10, 'Arreglo floral básico',
     'Arreglo floral básico para regalar.',
     'Flores variadas', 550.00, NULL, 5, TRUE),

    (33, 10, 'Arreglo floral mediano',
     'Arreglo floral mediano para regalar.',
     'Flores variadas', 750.00, NULL, 5, TRUE),

    (34, 10, 'Arreglo floral especial',
     'Arreglo floral especial para ocasiones especiales.',
     'Flores variadas', 950.00, NULL, 3, TRUE);

-- Ajustar las secuencias para continuar después de los IDs incluidos.
SELECT setval(
    pg_get_serial_sequence('categoria', 'id_categoria'),
    GREATEST((SELECT MAX(id_categoria) FROM categoria), 1)
);

SELECT setval(
    pg_get_serial_sequence('producto', 'id_producto'),
    GREATEST((SELECT MAX(id_producto) FROM producto), 1)
);

COMMIT;
