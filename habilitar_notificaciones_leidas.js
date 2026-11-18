const fs = require('fs');

const archivo = 'firestore.rules';
const codigo = fs.readFileSync(archivo, 'utf8');

const anterior = '      allow update, delete: if false;';

const nuevo = `      allow update: if request.auth != null &&
        resource.data.usuarioId == request.auth.uid &&
        request.resource.data.diff(resource.data)
          .affectedKeys().hasOnly(['leida']) &&
        request.resource.data.leida is bool;

      allow delete: if false;`;

const inicio = codigo.indexOf('match /notificaciones/{notificacionId}');
if (inicio === -1) {
  console.error('No se encontró la colección notificaciones.');
  process.exit(1);
}

const antes = codigo.slice(0, inicio);
const seccion = codigo.slice(inicio);

if (!seccion.includes(anterior)) {
  console.error('No se encontró la regla esperada. No se modificó nada.');
  process.exit(1);
}

fs.copyFileSync(archivo, archivo + '.backup-leidas');
fs.writeFileSync(
  archivo,
  antes + seccion.replace(anterior, nuevo)
);

console.log('Reglas actualizadas correctamente.');
