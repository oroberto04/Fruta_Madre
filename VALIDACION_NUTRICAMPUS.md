# NutriCampus: verificación y pruebas de aceptación

Rama existente: `integracion-jam-companeros`  
Web: https://nutricampus-13102.web.app  
Backend: https://nutricampus-pagos-jam.onrender.com

## Revisión automatizada

Desde la carpeta actual del proyecto en Git Bash:

```bash
git pull --ff-only origin integracion-jam-companeros
bash verificar_lanzamiento.sh
```

Si tienes Android SDK instalado, incluye la compilación APK:

```bash
bash verificar_lanzamiento.sh --android
```

**La compilación no equivale a probar el servicio real.** La comprobación de Marketplace y reembolsos se debe hacer de manera supervisada, sin simular transacciones reales.

## Casos de aceptación del cliente

- Acceder con cuenta cliente y comprobar que no aparezcan acciones de administración.
- Añadir un producto, cambiar cantidades y comprobar total en carrito y checkout.
- Entrega a domicilio: dirección completa, punto GPS opcional, permiso denegado y selección manual.
- Recogida: mostrar punto activo; si no hay punto activo, impedir registrar una entrega inexistente.
- Efectivo: confirmar pedido sin abrir Mercado Pago; estado inicial `pendiente` y mensaje de pago a la entrega.
- Cancelar efectivo `pendiente`: pasa de **Activos** a **Historial**; el documento permanece en Firestore.
- Pagar con Checkout Pro: validar importe, cuenta cobradora, retorno a `?pedidoId=...` y verificación en servidor.
- Cerrar el checkout antes de finalizar y regresar: consultar el pedido, no crear un duplicado automáticamente.
- Cancelar antes de generar enlace de Mercado Pago: pasa al historial sin cobrar.
- Solicitar cancelación cuando el enlace ya existe: se muestra en **Historial** como **Cancelación en revisión**; no se permite pagar de nuevo.
- Verificar rechazo de solicitud: el pedido vuelve a activos (si sigue operativo) y el cliente recibe notificación.
- Verificar autorización y devolución: mostrar `reembolso_en_proceso`, `reembolso_en_revision` o `reembolsado` según confirmación real; no afirmar una devolución antes de verificarse.
- Revisar historial, pedidos completados, errores de red, sesión expirada y cambio de usuario.

## Casos de aceptación de administración

- Acceso solo de rol `admin` (o `operador` para funciones operativas).
- **Activos** e **Historial** están separados y cambian tanto con mouse como con tacto.
- Avanzar estados en orden, de `pendiente/pagado` a `preparacion`, `listo`, `camino` y `entregado`.
- Impedir cambiar importe, marcar cobro como aprobado o avanzar pedidos no pagados con Mercado Pago.
- Asignar ETA para pedidos confirmados, con notificación correspondiente.
- Administrador puede aprobar o rechazar solicitudes. Operador no puede ejecutar reembolsos.
- La aprobación de Mercado Pago con cobro real utiliza validación de vendedor/importe/moneda y clave de idempotencia.
- Si un pago sigue pendiente no se aprueba cancelación ni se devuelve dinero sin verificación.

## Plataformas y accesibilidad

- Web en Edge/Chrome, ventana ancha y estrecha, teclas Tab/Enter y clic.
- Android: dispositivo real, tocar tarjetas de método de pago y botones de acción; regreso desde navegador externo.
- Comprobar VoiceOver/TalkBack, textos con escala mayor y contraste de tema oscuro.
- En Web, Checkout Pro redirige en la misma pestaña; en Android/iOS usa navegador externo, no un WebView obsoleto.

## Despliegue seguro

1. Comprobar que `flutter analyze`, `flutter test` y ambas compilaciones necesarias finalicen correctamente.
2. Publicar **reglas** de Firestore: `firebase deploy --only firestore:rules --project nutricampus-13102`.
3. Publicar frontend: `firebase deploy --only hosting --project nutricampus-13102`.
4. Confirmar en Render `FRONTEND_URL=https://nutricampus-13102.web.app` y que `/salud` indique `retornoWebConfigurado: true`.
5. No usar `git push --force` ni borrar historial. No exponer Access Tokens en GitHub.
6. Validar reglas y pagos con usuarios de prueba y ambiente de pruebas **separado** antes de hacer cargos reales.

## Limitaciones y verificación pendiente

- Es imposible afirmar ausencia total de errores sin probar roles, Firebase real, red y Mercado Pago con un dispositivo/navegador.
- La implementación nativa de **formulario de tarjeta PCI dentro de Flutter** no existe todavía; tarjetas se cobran con Checkout Pro externo.
- Si Mercado Pago marca una integración o cuenta como no disponible, es necesario revisar su configuración y elegibilidad desde Mercado Pago. El código no puede reactivar una cuenta bloqueada.
- Los enlaces de preferencia ya generados podrían necesitar revisión; **nunca** crear un nuevo cobro a ciegas ni borrar la preferencia sin comprobar pagos anteriores.
- Los cambios del repositorio no actualizan automáticamente Firebase Hosting ni las reglas de Firestore; deben publicarse explícitamente.
