import 'package:flutter/material.dart';

import 'imagen_contenido.dart';

import '../core/formato.dart';
import '../core/theme.dart';
import '../core/rutas_imagen.dart';
import '../models/platillo.dart';

class DishImage extends StatelessWidget {
  final String path;
  const DishImage(this.path, {super.key});

  @override
  Widget build(BuildContext context) {
    final placeholder = ColoredBox(
      color: AppThemeColors.superficieSuave(context),
      child: Center(
        child: Icon(Icons.restaurant, color: AppThemeColors.secundario(context)),
      ),
    );

    if (path.startsWith('http') || path.startsWith('data:image/jpeg;base64,')) {
      return ImagenContenido(
        path,
        fit: BoxFit.cover,
        errorBuilder: (_, _, _) => placeholder,
      );
    }
    return Image.asset(
      rutaImagenLocal(path),
      fit: BoxFit.cover,
      errorBuilder: (_, _, _) => placeholder,
    );
  }
}

class DishCard extends StatelessWidget {
  final Platillo platillo;
  final VoidCallback onTap;

  const DishCard({super.key, required this.platillo, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        clipBehavior: Clip.antiAlias,
        decoration: BoxDecoration(
          color: AppThemeColors.tarjeta(context),
          borderRadius: BorderRadius.circular(18),
          boxShadow: const [
            BoxShadow(
              color: Colors.black12,
              blurRadius: 10,
              offset: Offset(0, 4),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(child: SizedBox.expand(child: DishImage(platillo.imagen))),
            Padding(
              padding: const EdgeInsets.fromLTRB(12, 10, 12, 12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    platillo.nombre,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(fontWeight: FontWeight.w600),
                  ),
                  if (platillo.descripcion.trim().isNotEmpty) ...[
                    const SizedBox(height: 4),
                    Text(
                      platillo.descripcion,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: TextStyle(
                        fontSize: 12,
                        color: Theme.of(context).colorScheme.onSurfaceVariant,
                      ),
                    ),
                  ],
                  Text(
                    moneda(platillo.precioBase),
                    style: TextStyle(
                      color: AppThemeColors.esOscuro(context)
                          ? const Color(0xFFFFB27A)
                          : AppColors.forest,
                    ),
                  ),
                  const SizedBox(height: 8),
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton(
                      onPressed: onTap,
                      style: ElevatedButton.styleFrom(
                        minimumSize: const Size.fromHeight(36),
                      ),
                      child: const Text('Elegir'),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
