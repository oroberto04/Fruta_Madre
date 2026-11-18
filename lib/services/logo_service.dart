import "dart:convert";

import "package:cloud_firestore/cloud_firestore.dart";
import "package:firebase_auth/firebase_auth.dart";
import "package:image/image.dart" as img;
import "package:image_picker/image_picker.dart";

class LogoService {
  final _db = FirebaseFirestore.instance;
  final _auth = FirebaseAuth.instance;
  final _picker = ImagePicker();

  Future<bool> esAdministrador() async {
    final usuario = _auth.currentUser;
    if (usuario == null) return false;

    final documento = await _db.collection("usuarios").doc(usuario.uid).get();

    return documento.data()?["rol"] == "admin";
  }

  Future<String?> seleccionarLogo() async {
    final archivo = await _picker.pickImage(source: ImageSource.gallery);
    if (archivo == null) return null;

    final bytes = await archivo.readAsBytes();
    final imagen = img.decodeImage(bytes);
    if (imagen == null) {
      throw Exception("La imagen seleccionada no es valida");
    }

    final reducida = img.copyResize(
      imagen,
      width: imagen.width > 512 ? 512 : imagen.width,
    );
    final comprimida = img.encodePng(reducida, level: 9);
    final base64 = base64Encode(comprimida);

    if (base64.length > 700000) {
      throw Exception("El logo es demasiado grande");
    }
    return base64;
  }

  Future<void> guardarLogo(String logoBase64) async {
    if (!await esAdministrador()) {
      throw Exception("Solo los administradores pueden cambiar el logo");
    }

    if (logoBase64.isEmpty || logoBase64.length > 700000) {
      throw Exception("El logo no tiene un tamaño valido");
    }

    await _db.collection("configuracion").doc("apariencia").set({
      "logoBase64": logoBase64,
      "actualizado": FieldValue.serverTimestamp(),
    }, SetOptions(merge: true));
  }
}
