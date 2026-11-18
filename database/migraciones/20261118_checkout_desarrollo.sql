-- Solo para una base inicial creada desde database/schema.sql.
-- No ejecutarlo en una base ajena a Fruta Madre.
-- Idempotente: agrega columnas del controlador de desarrollo sin borrar registros.
BEGIN;
ALTER TABLE public.pedido
  ADD COLUMN IF NOT EXISTS tipo_servicio varchar(20),
  ADD COLUMN IF NOT EXISTS fecha_preferida date,
  ADD COLUMN IF NOT EXISTS horario_preferido varchar(20),
  ADD COLUMN IF NOT EXISTS observaciones text,
  ADD COLUMN IF NOT EXISTS correo_contacto varchar(180);
COMMIT;
