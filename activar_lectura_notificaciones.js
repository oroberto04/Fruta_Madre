const fs = require('fs');

const archivo = 'lib/views/notificaciones_screen.dart';
const codigo = fs.readFileSync(archivo, 'utf8');

const anterior = `                        onTap: pedidoId.isEmpty
                            ? null
                            : () {
                                Navigator.push(`;

const nuevo = `                        onTap: pedidoId.isEmpty
                            ? null
                            : () async {
                                if (!leida) {
                                  try {
                                    await avisos[index].reference.update({
                                      'leida': true,
                                    });
                                  } catch (e) {
                                    if (context.mounted) {
                                      ScaffoldMessenger.of(context).showSnackBar(
                                        const SnackBar(
                                          content: Text(
                                            'No se pudo marcar como leída.',
                                          ),
                                        ),
                                      );
                                    }
                                  }
                                }

                                if (!context.mounted) return;

                                Navigator.push(`;

if (!codigo.includes(anterior)) {
  console.error('No se encontró el bloque esperado.');
  console.error('No se modificó ningún archivo.');
  process.exit(1);
}

fs.copyFileSync(archivo, archivo + '.backup-lectura');
fs.writeFileSync(
  archivo,
  codigo.replace(anterior, nuevo)
);

console.log('Lectura de notificaciones activada correctamente.');
