import 'package:intl/intl.dart';

final _fmt = NumberFormat.currency(locale: 'es_MX', symbol: r'$');
String moneda(num valor) => _fmt.format(valor);
