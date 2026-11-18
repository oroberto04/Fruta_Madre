# NutriCampus — pagos y pedidos del cliente

## Arquitectura implementada

- **Flutter Web:** Mercado Pago **Checkout Pro**. El servidor Node.js en Render crea la preferencia con `back_urls`, valida el pago contra Mercado Pago y actualiza el pedido en Firestore. `url_launcher` abre el checkout en la pestaña actual (`_self`), porque abrir una pestaña nueva después de una llamada HTTP puede ser bloqueado.
- **Flutter Android/iOS:** Checkout Pro sigue disponible. Se agrega el SDK oficial `mercadopago_sdk_flutter: ^0.1.0` y su inicialización nativa opcional con una **clave pública**. El SDK permite tokenizar tarjetas mediante formularios PCI, **no realiza por sí mismo el redireccionamiento de Checkout Pro**. Aún no se implementó un formulario de captura/tokenización nativa ni un endpoint de cobro de tokens. La compra actual utiliza Checkout Pro, que ya admite tarjetas.
- **Efectivo:** el backend valida los productos, cantidades y total contra los precios de Firestore, y registra `pendiente_efectivo` sin llamar a Mercado Pago.
- **Mis pedidos (cliente):** muestra activos e historial; permite verificar pagos, reintentar `pendiente_pago` o `rechazado` cuando el servidor lo autoriza, confirmar efectivo pendiente, cancelar sin cobro o solicitar cancelación/reembolso según el estado. La autorización de reembolso corresponde a administración en el backend.
- **Retorno:** Render responde a `/pago/exito`, `/pago/fallo` o `/pago/pendiente`; después envía a la web pública con `?pedidoId=...&pago=...`. Flutter abre Mis pedidos y consulta `/verificar-pago` usando Firebase Auth. **Nunca** considera pagado un pedido por el parámetro de URL.

## Configuración de Render (obligatoria para redirección automática)

En el servicio de pagos que atiende `https://nutricampus-pagos-jam.onrender.com`, revisa **Environment**:

| Variable | Valor o uso |
| --- | --- |
| `BASE_URL` | `https://nutricampus-pagos-jam.onrender.com` (URL pública del backend) |
| `FRONTEND_URL` | URL HTTPS pública **real y desplegada** de Flutter Web, sin `/` final. Por ejemplo, si Firebase Hosting está habilitado para el proyecto, revisa el dominio de hosting asociado antes de ponerlo aquí. |
| `MP_ACCESS_TOKEN` | Access Token **privado** del vendedor, solo en Render |
| `MP_COLLECTOR_ID` | Identificador de cuenta real correspondiente al vendedor |
| `MP_MODO_PRUEBAS`, `MP_TEST_ACCESS_TOKEN`, `MP_TEST_COLLECTOR_ID` y `MP_PAGOS_REALES_HABILITADOS` | Mantener la política actual del servidor para pruebas y producción |
| `FIREBASE_SERVICE_ACCOUNT` | Credencial privada únicamente en Render o archivo secreto |

Después de guardar variables o actualizar el backend, desplegar o reiniciar el servicio según la configuración de Render. `GET /salud` devuelve `retornoWebConfigurado: true` si hay una URL de retorno HTTPS válida; **esto no comprueba que el frontend esté desplegado**.

La app no contiene ni debe recibir `MP_ACCESS_TOKEN`. Los callbacks de Mercado Pago deben seguir verificándose con su firma y los cobros conciliándose con la API de Mercado Pago.

## Desplegar Flutter Web

`firebase.json` incorpora Hosting con `build/web` como directorio público y una reescritura SPA a `index.html`. En la **carpeta existente del proyecto**, desde Git Bash:

```bash
git checkout integracion-jam-companeros
git pull --ff-only origin integracion-jam-companeros
flutter --version
flutter pub get
flutter analyze
flutter test
flutter build web --release
firebase deploy --only hosting --project nutricampus-13102
```

El proyecto exige **Dart 3.13.5 o superior compatible con `^3.13.5`**, y el SDK oficial exige Flutter 3.44.2+ / Dart 3.12+. Si el SDK Flutter instalado es anterior, actualízalo antes de `flutter pub get`. Después de resolver dependencias, incluir el `pubspec.lock` actualizado en un commit normal.

Comprueba el dominio HTTPS efectivo mostrado por Firebase Hosting después del deploy; configúralo como `FRONTEND_URL` en Render. **No uses localhost ni una dirección de desarrollo en `FRONTEND_URL`**: Mercado Pago exige una URL pública de retorno. En pruebas locales con `flutter run -d web-server --web-port 8080`, la devolución seguirá apuntando al frontend desplegado.

## SDK nativo opcional

El SDK oficial 0.1.0 es preliminar, compatible con Android API 23+ e iOS 15+. Android tiene configurado el repositorio Maven de MercadoLibre y `minSdk` 23. Para inicializar la tokenización en una ejecución **Android/iOS**:

```bash
flutter run --dart-define=MP_PUBLIC_KEY=TEST-CLAVE_PUBLICA
```

Usa la **clave pública** obtenida en el panel de Mercado Pago y reemplaza el ejemplo. No es el Access Token. Flutter Web utiliza una importación condicional que **no importa** el SDK nativo ni sus dependencias `dart:io`. Si se compila para iOS, comprobar que el target de deployment del proyecto es 15.0 o superior.

La inicialización de este SDK **no habilita** un formulario nativo de tarjetas; el flujo de tarjetas ya disponible es Checkout Pro. Añadir tokenización nativa requeriría construir su UI segura y un endpoint independiente con las validaciones del vendedor y de los montos.

## Pruebas de aceptación recomendadas

1. Cliente autenticado: realizar pedido con Mercado Pago, pagar en Checkout Pro y regresar a `Mis pedidos`. Confirmar que `/verificar-pago` muestra el cobro aprobado solo cuando Mercado Pago lo confirma.
2. Cerrar o cancelar Checkout Pro: verificar que el pedido no aparezca como pagado; permitir reintento únicamente si el backend habilita `puedePagar`.
3. Pago pendiente o en revisión: mostrar el estado y evitar nuevos cobros simultáneos.
4. Cliente con efectivo: registrar pedido sin cobro en línea, mostrarlo activo como pendiente; simular interrupción y recuperarlo con `Confirmar pedido en efectivo`.
5. Cancelación antes de empezar el cobro: cancelar desde Mis pedidos y mover a historial; si se inició preferencia o pago, solicitar cancelación y no prometer un reembolso automático.
6. Cancelación después de aprobación: verificar la autorización exclusiva de administración y la conciliación del reembolso antes de marcarlo como reembolsado.
7. Con usuario no autenticado: el retorno lleva a Mis pedidos, solicita iniciar sesión y verifica el pedido tras autenticar.
8. Revisar `flutter analyze`, pruebas, compilación web y compilación Android/iOS en entornos con las versiones requeridas. **Estas verificaciones todavía requieren ejecución real**.

## Notas de operación

- El checkout web requiere **frontend publicado**, variable `FRONTEND_URL` en Render y las credenciales de Mercado Pago correctamente configuradas. Modificar GitHub no implica por sí solo desplegar esos servicios.
- Todas las operaciones definitivas de pago, cancelación y reembolso se autorizan en el backend y se concilian contra Mercado Pago; la app cliente no puede asumir que un enlace de retorno es un comprobante.
- No se creó una rama nueva para esta integración: los commits se hicieron en `integracion-jam-companeros`.
