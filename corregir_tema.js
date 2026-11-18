const fs = require('fs');
const path = require('path');

const base = 'lib';
const archivos = [];

function recorrer(dir) {
  for (const nombre of fs.readdirSync(dir)) {
    const ruta = path.join(dir, nombre);
    if (fs.statSync(ruta).isDirectory()) {
      recorrer(ruta);
    } else if (nombre.endsWith('.dart')) {
      archivos.push(ruta);
    }
  }
}

recorrer(base);

let total = 0;

for (const archivo of archivos) {
  let codigo = fs.readFileSync(archivo, 'utf8');
  const original = codigo;

  if (!codigo.includes("package:flutter/material.dart")) {
    continue;
  }

  if (
    !archivo.replace(/\\/g, '/').endsWith('core/theme.dart') &&
    !codigo.includes('core/theme.dart') &&
    !codigo.includes('AppThemeColors')
  ) {
    const relativo = path.relative(
      path.dirname(archivo),
      path.join(base, 'core', 'theme.dart')
    ).replace(/\\/g, '/');

    codigo = codigo.replace(
      "import 'package:flutter/material.dart';",
      `import '${relativo.startsWith('.') ? relativo : './' + relativo}';\nimport 'package:flutter/material.dart';`
    );
  }

  codigo = codigo.replace(
    /backgroundColor:\s*(?:crema|_fondo|fondo),/g,
    'backgroundColor: AppThemeColors.fondo(context),'
  );

  codigo = codigo.replace(
    /backgroundColor:\s*Colors\.white,(?=\s*indicatorColor:)/g,
    'backgroundColor: AppThemeColors.tarjeta(context),'
  );

  codigo = codigo.replace(
    /color:\s*(?:texto|_texto),/g,
    'color: AppThemeColors.texto(context),'
  );

  codigo = codigo.replace(
    /color:\s*(?:gris|_gris|secundario),/g,
    'color: AppThemeColors.secundario(context),'
  );

  if (codigo !== original) {
    fs.copyFileSync(archivo, archivo + '.respaldo_tema');
    fs.writeFileSync(archivo, codigo);
    console.log('Actualizado:', archivo);
    total++;
  }
}

console.log('Archivos modificados:', total);
