const ESTADOS_PENDIENTES = new Set([
  'creando',
  'revision',
]);

function obtenerFecha(valor) {
  if (!valor) return null;

  if (typeof valor.toDate === 'function') {
    return valor.toDate();
  }

  const fecha = new Date(valor);

  return Number.isNaN(fecha.getTime())
    ? null
    : fecha;
}

function diagnosticarPreferencia(control, ahora = new Date()) {
  if (!control) {
    return {
      requiereRevision: false,
      motivo: 'sin_control',
    };
  }

  if (!ESTADOS_PENDIENTES.has(control.estado)) {
    return {
      requiereRevision: false,
      motivo: 'estado_no_bloqueado',
    };
  }

  const fecha = obtenerFecha(
    control.actualizadoEn || control.creadoEn
  );

  if (!fecha) {
    return {
      requiereRevision: true,
      motivo: 'fecha_invalida',
    };
  }

  const minutos = Math.max(
    0,
    (ahora.getTime() - fecha.getTime()) / 60000
  );

  return {
    requiereRevision: minutos >= 10,
    motivo: minutos >= 10
      ? 'operacion_antigua'
      : 'operacion_reciente',
    estado: control.estado,
    minutos: Math.round(minutos),
  };
}

module.exports = { diagnosticarPreferencia };
