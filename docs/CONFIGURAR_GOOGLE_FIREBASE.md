# Activar Google en NutriCampus (Firebase)

La integración de la app ya usa Firebase Authentication Web y `google_sign_in` en Android.
Para que funcione con cuentas reales, falta habilitar el proveedor OAuth en el proyecto Firebase.

## 1. Web y Android: habilitar proveedor

1. Abre https://console.firebase.google.com/project/nutricampus-13102/authentication/providers
2. Ve a **Authentication → Sign-in method → Google** y selecciona **Habilitar**.
3. Configura el correo de soporte que solicite Firebase y guarda.
4. En **Authentication → Settings → Authorized domains**, confirma:
   - `nutricampus-13102.web.app`
   - `nutricampus-13102.firebaseapp.com`
   - `localhost` para pruebas locales, si corresponde.

No modifiques la configuración de correo/contraseña existente.

## 2. Android: configurar SHA y OAuth

El `android/app/google-services.json` actual del repositorio todavía tiene `"oauth_client": []`.
Por tanto, Google nativo no funcionará con ese archivo antiguo aunque compile.

1. En Git Bash, desde el repositorio, ejecuta:
   ```bash
   cd android
   ./gradlew signingReport
   cd ..
   ```
2. Copia la huella **SHA-1** y preferentemente también la **SHA-256** del certificado utilizado para instalar la app. En Firebase: **Project settings → Your apps → Android** (`com.nutricampus.nutricampus`) → **Add fingerprint**.
3. Con Google habilitado y las huellas registradas, descarga el **nuevo `google-services.json`** de Firebase y reemplaza `android/app/google-services.json`.
4. Comprueba que el archivo descargado contiene un cliente OAuth Web con `client_type: 3`, necesario para que el plugin `google_sign_in` obtenga el ID token en Android.
5. Si usas otro certificado para una APK de distribución, registra también su SHA-1/SHA-256.

## 3. Probar en Git Bash

```bash
flutter pub get
flutter analyze
flutter test
flutter build web --release
flutter build apk --debug
```

Para publicar una versión web comprobada:

```bash
firebase deploy --only hosting --project nutricampus-13102
```

## 4. Cuentas existentes y permisos

- Si ya tienes un usuario con correo y contraseña y quieres **conservar su UID, historial de pedidos y rol administrador**, primero entra con tu contraseña y luego ve a **Perfil → Conectar Google para usar mi nombre y foto**.
- No se fusionan automáticamente dos cuentas diferentes. Si la misma cuenta Google ya pertenece a otro UID, aparecerá un error y no se moverán pedidos ni permisos.
- El nombre y foto de Google se usan para completar datos de perfil faltantes; un nombre o foto elegidos explícitamente en NutriCampus se conservan.
- Los usuarios nuevos con Google tienen rol `cliente`, no `admin`.

La configuración del proveedor OAuth y las huellas SHA debe realizarse en el proyecto Firebase; cambiar archivos Dart o publicar Hosting no habilita esos permisos.
