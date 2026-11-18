/// Completa únicamente los datos ausentes de un perfil al conectar Google.
/// No modifica el rol, el correo, las direcciones ni la información del usuario.
Map<String, String> completarPerfilDesdeGoogle(
  Map<String, dynamic> actual, {
  required String nombreGoogle,
  required String fotoGoogle,
}) {
  final cambios = <String, String>{};
  const camposNombre = [
    'nombre_completo', 'nombreCompleto', 'nombre', 'displayName',
    'nombre_usuario',
  ];
  const camposFoto = [
    'fotoPerfil', 'foto_perfil', 'foto_url', 'photoURL', 'avatarUrl',
  ];
  final tieneNombre = camposNombre.any(
    (campo) => (actual[campo] is String) &&
        (actual[campo] as String).trim().isNotEmpty,
  );
  final tieneFoto = camposFoto.any(
    (campo) => (actual[campo] is String) &&
        (actual[campo] as String).trim().isNotEmpty,
  );
  if (!tieneNombre && nombreGoogle.trim().isNotEmpty) {
    cambios['nombre_completo'] = nombreGoogle.trim();
  }
  if (!tieneFoto && fotoGoogle.trim().isNotEmpty) {
    cambios['fotoPerfil'] = fotoGoogle.trim();
  }
  return cambios;
}
