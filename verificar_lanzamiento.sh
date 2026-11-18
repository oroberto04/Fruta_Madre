#!/usr/bin/env bash
set -Eeuo pipefail

cd "$(dirname "$0")"

if [[ "$(git branch --show-current)" != "integracion-jam-companeros" ]]; then
  echo "ERROR: debes ejecutar esta verificacion en integracion-jam-companeros." >&2
  exit 1
fi

echo "== NutriCampus: validar codigo Flutter =="
flutter pub get
flutter analyze
flutter test

echo "== NutriCampus: compilar Web =="
flutter build web --release

echo "== NutriCampus: validar sintaxis backend =="
node --check servidor_pagos/index.js
node --check servidor_pagos/control_preferencias.js
node --check servidor_pagos/seguridad_webhook.js
node --check servidor_pagos/diagnostico_preferencias.js

echo "== NutriCampus: pruebas automatizadas del backend =="
npm test --prefix servidor_pagos

if [[ "${1:-}" == "--android" ]]; then
  echo "== NutriCampus: compilar APK Android de prueba =="
  flutter build apk --debug
fi

echo
echo "VERIFICACIONES AUTOMATIZADAS APROBADAS."
echo "ATENCION: esto NO comprueba cobros reales ni reembolsos en Mercado Pago."
echo "Para publicar Firestore: firebase deploy --only firestore:rules --project nutricampus-13102"
echo "Para publicar Web: firebase deploy --only hosting --project nutricampus-13102"
