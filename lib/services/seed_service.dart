import 'package:cloud_firestore/cloud_firestore.dart';

import '../models/platillo.dart';

class SeedService {
  Future<void> sembrar() async {
    final db = FirebaseFirestore.instance;
    final batch = db.batch();
    var orden = 0;

    for (final p in platillosDemo) {
      batch.set(db.collection('platillos').doc(p.id), {
        'nombre': p.nombre,
        'precioBase': p.precioBase,
        'imagenUrl': p.imagen,
        'orden': orden++,
      });

      final mods = <String, Map<String, dynamic>>{
        'frijoles': {
          'categoria': '¿Lleva frijoles?',
          'multiple': false,
          'obligatorio': true,
          'orden': 1,
          'opciones': [
            {'nombre': 'Sí', 'precioExtra': 0},
            {'nombre': 'No', 'precioExtra': 0},
          ],
        },
        'salsa': {
          'categoria': 'Salsa',
          'multiple': false,
          'obligatorio': true,
          'orden': 2,
          'opciones': [
            {'nombre': 'Verde', 'precioExtra': 0},
            {'nombre': 'Roja', 'precioExtra': 0},
            {'nombre': 'Sin salsa', 'precioExtra': 0},
          ],
        },
        'tortillas': {
          'categoria': 'Tortillas',
          'multiple': false,
          'obligatorio': true,
          'orden': 3,
          'opciones': [
            {'nombre': 'Maíz', 'precioExtra': 0},
            {'nombre': 'Sin tortillas', 'precioExtra': 0},
          ],
        },
        'extras': {
          'categoria': 'Ingredientes extra',
          'multiple': true,
          'obligatorio': false,
          'orden': 4,
          'opciones': [
            {'nombre': 'Aguacate', 'precioExtra': 15},
            {'nombre': 'Queso panela', 'precioExtra': 12},
            {'nombre': 'Porción extra de proteína', 'precioExtra': 30},
          ],
        },
      };

      mods.forEach((k, m) {
        batch.set(db.collection('modificadores').doc('${p.id}_$k'), {
          'platilloId': p.id,
          ...m,
        });
      });
    }

    await batch.commit();
  }
}
