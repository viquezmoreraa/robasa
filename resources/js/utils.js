export function escapeHtml(str) {
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

export function showToast(message, type = '') {
  const el = document.getElementById('toast');
  if (!el) return;
  el.textContent = message;
  el.className = 'toast' + (type ? ` ${type}` : '');
  el.hidden = false;
  clearTimeout(showToast._t);
  showToast._t = setTimeout(() => {
    el.hidden = true;
  }, 4000);
}

export function validateStep(step, data) {
  const errors = [];
  if (step === 1 && !data.terminos_aceptados) {
    errors.push('Debe aceptar los términos y condiciones.');
  }
  if (step === 2) {
    if ((data.nombre_completo || '').trim().length < 3) errors.push('Nombre completo requerido.');
    if (!/^[\d\-]+$/.test((data.cedula || '').replace(/\s/g, ''))) errors.push('Cédula inválida.');
    if (!/^[\d\s+\-()]{8,30}$/.test(data.telefono || '')) errors.push('Teléfono inválido.');
    if ((data.direccion || '').trim().length < 5) errors.push('Dirección requerida.');
    if (!/^\d{2}\/\d{2}\/\d{4}$/.test(data.fecha_nacimiento || '')) {
      errors.push('Fecha de nacimiento en formato dd/mm/aaaa.');
    }
    if (!['si', 'no'].includes(data.licencia_conducir)) errors.push('Seleccione licencia de conducir.');
  }
  if (step === 3) {
    if (!data.vacante_id) errors.push('Seleccione un puesto.');
    if (!data.pretension_salarial) errors.push('Seleccione pretensión salarial.');
    if (!data.zona) errors.push('Seleccione zona.');
    if (!data.disponibilidad) errors.push('Seleccione disponibilidad.');
    if (!['si', 'no'].includes(data.experiencia_puesto)) errors.push('Indique experiencia en el puesto.');
  }
  if (step === 4 && !data.nivel_educativo) {
    errors.push('Seleccione nivel educativo.');
  }
  if (step === 6) {
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email || '')) errors.push('Correo inválido.');
    if ((data.password || '').length < 8) errors.push('Contraseña mínimo 8 caracteres.');
  }
  return errors;
}

export const NIVEL_LABELS = {
  sin_estudios: 'Sin estudios formales',
  primaria: 'Primaria completa',
  noveno: 'Noveno año completo',
  secundaria: 'Secundaria completa',
  tecnica: 'Técnica completa',
  universitaria: 'Universitaria completa',
};
