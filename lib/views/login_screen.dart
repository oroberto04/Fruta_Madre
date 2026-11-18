import 'package:flutter/material.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({Key? key}) : super(key: key);

  @override
  _LoginScreenState createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  // TUS CONTROLADORES ORIGINALES (Se mantiene la lógica intacta)
  final TextEditingController _emailController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();
  bool _obscurePassword = true;
  bool _rememberMe = false;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFFFF8F0), // Fondo crema institucional
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Color(0xFF201B0F)),
          onPressed: () => Navigator.pop(context),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.close, color: Color(0xFF201B0F)),
            onPressed: () {},
          ),
        ],
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 24.0, vertical: 12.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Badges superiores de diseño
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                    decoration: BoxDecoration(
                      color: const Color(0xFF2F612F),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: const Text(
                      "🔥 PUESTO DIGITAL NO. 01",
                      style: TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                    decoration: BoxDecoration(
                      color: const Color(0xFFAC2D50),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: const Text(
                      "100% FRESCO",
                      style: TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 20),
              
              // Título principal con tono de barrio / frutería
              const Text(
                "¡QUÉ GUSTO VERTE\nPOR ACÁ,\nMARCHANTE! 👋",
                style: TextStyle(
                  fontSize: 26,
                  fontWeight: FontWeight.w900,
                  color: Color(0xFF201B0F),
                  height: 1.1,
                ),
              ),
              const SizedBox(height: 8),
              const Text(
                "Inicia sesión para pedir tus antojitos en friega y rastrear tu pedido en vivo.",
                style: TextStyle(fontSize: 14, color: Color(0xDE201B0F)),
              ),
              const SizedBox(height: 24),

              // Campo de Teléfono o Correo
              const Text("TELÉFONO O CORREO", style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0x8A201B0F))),
              const SizedBox(height: 8),
              TextField(
                controller: _emailController, // Conectado a tu lógica original
                decoration: InputDecoration(
                  hintText: "ej. 55 1234 5678 o marchante@fruta.mx",
                  filled: true,
                  fillColor: Colors.white,
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: const BorderSide(color: Color(0xDE201B0F), width: 1.5),
                  ),
                  prefixIcon: const Icon(Icons.storefront_outlined),
                ),
              ),
              const SizedBox(height: 16),

              // Campo de Contraseña y Recuperación
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text("CONTRASEÑA", style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0x8A201B0F))),
                  GestureDetector(
                    onTap: () {
                      // Redirige a la vista de recuperación (Vista 13)
                    },
                    child: const Text(
                      "¿Olvidaste tu contraseña?",
                      style: TextStyle(fontSize: 12, color: Color(0xFFBA1A1A), fontWeight: FontWeight.bold),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              TextField(
                controller: _passwordController, // Conectado a tu lógica original
                obscureText: _obscurePassword,
                decoration: InputDecoration(
                  hintText: "Tu clave secreta",
                  filled: true,
                  fillColor: Colors.white,
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: const BorderSide(color: Color(0xDE201B0F), width: 1.5),
                  ),
                  prefixIcon: const Icon(Icons.lock_outline),
                  suffixIcon: TextButton(
                    onPressed: () {
                      setState(() {
                        _obscurePassword = !_obscurePassword;
                      });
                    },
                    child: Text(_obscurePassword ? "MOSTRAR" : "OCULTAR", style: const TextStyle(color: Color(0xDE201B0F), fontSize: 11, fontWeight: FontWeight.bold)),
                  ),
                ),
              ),
              const SizedBox(height: 12),

              // Checkbox Recordar datos
              Row(
                children: [
                  Checkbox(
                    value: _rememberMe,
                    activeColor: const Color(0xFF2F612F),
                    onChanged: (val) {
                      setState(() {
                        _rememberMe = val ?? false;
                      });
                    },
                  ),
                  const Text("Recordar mis datos en este teléfono", style: TextStyle(fontSize: 13, color: Color(0xDE201B0F))),
                ],
              ),
              const SizedBox(height: 16),

              // Botón Entrar Principal (Verde Institucional)
              SizedBox(
                width: double.infinity,
                height: 54,
                child: ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF2F612F),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    elevation: 2,
                  ),
                  onPressed: () {
                    // AQUÍ LLAMAS A TU FUNCIÓN DE FIREBASE / AUTH ORIGINAL
                    // Ejemplo: AuthService().login(_emailController.text, _passwordController.text);
                  },
                  child: const Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text(
                        "ENTRAR AL PUESTO",
                        style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.white),
                      ),
                      SizedBox(width: 8),
                      Icon(Icons.arrow_forward, color: Colors.white),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 20),

              // Separador
              Row(
                children: const [
                  Expanded(child: Divider(color: Color(0x42201B0F))),
                  Padding(
                    padding: EdgeInsets.symmetric(horizontal: 8.0),
                    child: Text("O ENTRA EN CALIENTE CON:", style: TextStyle(fontSize: 11, color: Color(0x8A201B0F))),
                  ),
                  Expanded(child: Divider(color: Color(0x42201B0F))),
                ],
              ),
              const SizedBox(height: 16),

              // Botón Google (Lógica intacta)
              OutlinedButton(
                style: OutlinedButton.styleFrom(
                  minimumSize: const Size(double.infinity, 50),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  side: const BorderSide(color: Color(0xDE201B0F), width: 1.2),
                  backgroundColor: Colors.white,
                ),
                onPressed: () {
                  // Tu lógica de Google Sign-In
                },
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: const [
                    Icon(Icons.g_mobiledata, size: 28, color: Color(0xFFBA1A1A)),
                    SizedBox(width: 8),
                    Text("Continuar con Google", style: TextStyle(color: Color(0xFF201B0F), fontWeight: FontWeight.bold)),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}