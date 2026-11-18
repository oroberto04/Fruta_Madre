CREATE TABLE IF NOT EXISTS mp_orders_test (
    id_pedido INTEGER PRIMARY KEY REFERENCES pedido(id_pedido),
    order_id VARCHAR(160) UNIQUE,
    idempotency_key UUID NOT NULL UNIQUE,
    checkout_url TEXT,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
