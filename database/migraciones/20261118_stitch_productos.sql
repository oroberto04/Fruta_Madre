-- Amplía únicamente la tabla producto para los controles reales del diseño Stitch.
-- Idempotente. NO borra datos ni reinicia secuencias.
ALTER TABLE public.producto ADD COLUMN IF NOT EXISTS sku varchar(60);
ALTER TABLE public.producto ADD COLUMN IF NOT EXISTS costo numeric(10,2) NOT NULL DEFAULT 0;
ALTER TABLE public.producto ADD COLUMN IF NOT EXISTS stock_minimo integer NOT NULL DEFAULT 0;
ALTER TABLE public.producto ADD COLUMN IF NOT EXISTS subcategoria varchar(255);
ALTER TABLE public.producto ADD COLUMN IF NOT EXISTS permite_paquetes boolean NOT NULL DEFAULT false;
ALTER TABLE public.producto ADD COLUMN IF NOT EXISTS actualizado_en timestamp without time zone DEFAULT CURRENT_TIMESTAMP;
-- SKU para filas existentes solo si no fue asignado previamente.
UPDATE public.producto SET sku = 'FM-PRD-' || LPAD(id_producto::text, 5, '0')
 WHERE sku IS NULL OR BTRIM(sku) = '';
CREATE UNIQUE INDEX IF NOT EXISTS ix_fruta_madre_producto_sku ON public.producto (LOWER(sku));
