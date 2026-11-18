import "../core/theme.dart";

import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

import '../core/estados.dart';
import '../core/formato.dart';

class PedidoCard extends StatelessWidget {
  final Map<String, dynamic> pedido;
  final Widget? extra; // controles extra, solo para el panel admin

  const PedidoCard({super.key, required this.pedido, this.extra});

  @override
  Widget build(BuildContext context) {
    // Tomamos solo los primeros 6 caracteres del ID para que se vea como un código corto
    final id = (pedido['id'] as String).substring(0, 6).toUpperCase();

    // Armamos el texto de los platillos que pidió
    final items = (pedido['items'] as List? ?? [])
        .map((i) => '${i['cantidad']} x ${i['nombre']}')
        .join(', ');

    final creado = pedido['creado'];
    final fecha = creado is Timestamp
        ? DateFormat('dd/MM/yyyy HH:mm').format(creado.toDate())
        : '';

    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppThemeColors.tarjeta(context),
        borderRadius: BorderRadius.circular(16),
        boxShadow: const [
          BoxShadow(color: Colors.black12, blurRadius: 8, offset: Offset(0, 3)),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                'Pedido #$id',
                style: const TextStyle(fontWeight: FontWeight.w700),
              ),
              Text(
                fecha,
                style: TextStyle(
                  color: AppThemeColors.secundario(context),
                  fontSize: 12,
                ),
              ),
            ],
          ),
          const SizedBox(height: 6),
          Text(items),
          const SizedBox(height: 6),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Chip(
                label: Text(
                  etiquetaEstado(pedido['estado'] as String? ?? ''),
                  style: TextStyle(color: AppThemeColors.texto(context)),
                ),
                backgroundColor: AppThemeColors.superficieSuave(context),
                side: BorderSide.none,
              ),
              Text(
                moneda(pedido['total'] as num? ?? 0),
                style: const TextStyle(
                  fontWeight: FontWeight.w700,
                  fontSize: 16,
                ),
              ),
            ],
          ),
          if (extra != null) ...[const SizedBox(height: 6), extra!],
        ],
      ),
    );
  }
}