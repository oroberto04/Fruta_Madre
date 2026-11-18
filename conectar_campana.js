const fs = require('fs');

const archivo = 'lib/views/lobby_screen.dart';
const codigo = fs.readFileSync(archivo, 'utf8');

const importAnterior = "import 'avisos_screen.dart';";
const importNuevo = "import 'notificaciones_screen.dart';";

const pantallaAnterior = 'const AvisosScreen()';
const pantallaNueva = 'const NotificacionesScreen()';

if (
  !codigo.includes(importAnterior) ||
  !codigo.includes(pantallaAnterior)
) {
  console.error('No se encontró el código esperado.');
  console.error('No se modificó ningún archivo.');
  process.exit(1);
}

fs.copyFileSync(archivo, archivo + '.backup-campana');

const actualizado = codigo
  .replace(importAnterior, importNuevo)
  .replace(pantallaAnterior, pantallaNueva);

fs.writeFileSync(archivo, actualizado);

console.log('Campana conectada correctamente.');
console.log('Respaldo creado.');
