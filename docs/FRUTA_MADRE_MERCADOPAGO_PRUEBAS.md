# Fruta Madre · Mercado Pago TEST y Render Free

**Base:** `oroberto04/Fruta_Madre`, rama `desarrollo`, no la rama Flutter llamada `fruta-madre-app`.
**Rama de trabajo:** `integracion-jam-mercadopago-pruebas`.
**Separado:** no usa NutriCampus ni su Render ni sus claves.

## Qué se implementa

- Se conserva el checkout PHP existente (efectivo y transferencia).
- Se añade la opción **Mercado Pago · pruebas**. Se aceptan exclusivamente access tokens `TEST-…`; jamás tokens `APP_USR-…`.
- El backend PHP calcula el monto desde PostgreSQL; el navegador no fija precios ni recibe claves.
- Redirección a Checkout Pro y retorno a la tienda. Antes de marcar `aprobado_prueba`, se consulta el pago en la API de Mercado Pago y se cotejan `external_reference`, importe, moneda y `live_mode=false`.
- El pago TEST se distingue de los pagos reales: estado `aprobado_prueba`, `monto_pagado=0`, etiqueta «sin cobro real». El botón administrativo de confirmar pagos no puede convertirlo en `pagado`.
- El sitio se puede alojar como **un solo servicio Docker PHP** en Render Free; su carpeta pública es `public/`, sin exponer SQL, configuraciones y código privado.
- Los diseños Stitch `-36` y `-37` son idénticos. Se conserva una sola copia de referencia, sin sustituir la administración funcional por un HTML de demostración.

## Paso a paso en Git Bash (Windows)

```bash
cd ~
git clone https://github.com/oroberto04/Fruta_Madre.git
cd Fruta_Madre
git fetch origin
git switch -c integracion-jam-mercadopago-pruebas origin/desarrollo
# Ahora descomprime el archivo FRUTA_MADRE_PAGO_TEST_PARA_RAMA.zip dentro de esta carpeta.
node integracion/instalar.mjs --verificar
node integracion/instalar.mjs
php tests/mercadopago_pruebas_test.php
php -l app/services/MercadoPagoPruebas.php
php -l app/controllers/MercadoPagoPruebasController.php
php -l app/controllers/HomeController.php
php -l app/views/home/pago.php
php -l app/views/home/confirmacion.php
php -l index.php
git diff --stat
git add .gitignore index.php app/ public/ Dockerfile .dockerignore .env.example render.yaml docs/ integracion/ database/migraciones/ tests/
git commit -m "feat(fruta-madre): checkout Mercado Pago TEST sin cobros reales"
git push -u origin integracion-jam-mercadopago-pruebas
```

El comando `git push` al repositorio del propietario necesita permisos **Write** y autenticación GitHub. Si devuelve 403, Roberto debe concederte permiso de colaborador o autorizar el acceso de escritura de tu integración GitHub. No fuerces `main` ni `desarrollo`.

Si ya tienes `~/Fruta_Madre`, ejecuta `git status` antes y conserva tus cambios: **no hagas `git reset --hard`**. El instalador se niega a ejecutar fuera de la rama específica y comprueba sus puntos de inserción antes de editar.

## Mercado Pago: lo que tienes que ingresar manualmente

1. Abre [Mercado Pago Developers](https://www.mercadopago.com.mx/developers/panel/applications), crea o selecciona una **aplicación de Fruta Madre**.
2. Busca **Credenciales de prueba / TEST**; copia su **Access Token de prueba**. Nunca uses el token de producción. Los datos de prueba son independientes de NutriCampus.
3. En Render > tu nuevo servicio `fruta-madre-jam-pruebas` > Environment, crea:
   - `MP_ACCESS_TOKEN_TEST` → tu token `TEST-...` (privado).
   - `DATABASE_URL` → cadena PostgreSQL **exclusiva de Fruta Madre**.
   - `FRUTA_MADRE_URL` → `https://<tu-servicio>.onrender.com` (dirección final del servicio, sin `/` final).
4. Guarda, despliega y entra a `https://<tu-servicio>.onrender.com/salud.php`. Debe indicar `pagos_reales_habilitados:false`.
5. Para un pago de prueba, utiliza una cuenta y tarjeta de prueba oficiales, desde una ventana privada conforme a la documentación vigente. Nunca ingreses tarjetas reales.

No compartas el Access Token en ChatGPT, GitHub, screenshots ni por chat: introdúcelo directamente en **Environment**.

## Render y PostgreSQL sin cobro

- Una vez subida la rama, abre [Render Dashboard](https://dashboard.render.com/) > **New > Blueprint** y selecciona este repo/rama para usar `render.yaml`, o New > Web Service > Docker > Free > rama `integracion-jam-mercadopago-pruebas`.
- **Escoge el plan `free`** y no elijas `starter` ni otro pagado. No se puede garantizar que el workspace permita crear el recurso sin verificación de pago.
- El PostgreSQL gratis de Render **caduca a los 30 días**: para no perder la base considera [Neon Free](https://neon.com/pricing), que permite PostgreSQL gratuito con límites de uso. Copia `DATABASE_URL` desde Neon y conéctala en Render.
- Para una base nueva ejecuta `database/schema.sql`, después `database/migraciones/20261118_checkout_desarrollo.sql` en **la base exclusiva de Fruta Madre**. El esquema base de la rama `desarrollo` podría requerir otras migraciones para funciones no relacionadas con pagos; comprueba el funcionamiento antes de presentarlo como listo.
- **No restaures** `database/backups/fruta_madre_completa.sql` a ciegas: contiene datos antiguos y puede sobrescribir o mezclar registros.
- El repositorio original ignora `app/config/database.php`. Docker lo genera desde `database.example.php`. En local puedes copiarlo y proporcionar `DATABASE_URL` por variable de entorno.

## Limitaciones y pruebas

La prueba unitaria `php tests/mercadopago_pruebas_test.php` valida bloqueo de credenciales de producción, montos, monedas, referencias y `live_mode`. Para probar una transacción completa se necesita la tienda desplegada, una base PostgreSQL disponible y el Access Token TEST del usuario. El retorno de Checkout Pro requiere mantener la sesión de la tienda. No se configuró ningún webhook automático, por lo que un pago que no retorne a la tienda no se actualizará automáticamente; el usuario debe abrir su flujo de retorno. Esta versión **no es un procesador de pagos de producción**.
