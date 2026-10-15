const STORAGE_KEY = 'robasa_wizard';

export const wizardDefaults = () => ({
  terminos_aceptados: false,
  nombre_completo: '',
  cedula: '',
  telefono: '',
  direccion: '',
  fecha_nacimiento: '',
  licencia_conducir: '',
  vacante_id: null,
  pretension_salarial: '',
  zona: '',
  disponibilidad: '',
  experiencia_puesto: '',
  nivel_educativo: '',
  ultimo_empleo: '',
  email: '',
  password: '',
});

export function loadWizard() {
  try {
    const raw = sessionStorage.getItem(STORAGE_KEY);
    if (!raw) return wizardDefaults();
    return { ...wizardDefaults(), ...JSON.parse(raw) };
  } catch {
    return wizardDefaults();
  }
}

export function saveWizard(data) {
  sessionStorage.setItem(STORAGE_KEY, JSON.stringify(data));
}

export function clearWizard() {
  sessionStorage.removeItem(STORAGE_KEY);
}

export let wizardState = loadWizard();

export function setWizard(patch) {
  wizardState = { ...wizardState, ...patch };
  saveWizard(wizardState);
  return wizardState;
}

export function setVacanteId(id) {
  return setWizard({ vacante_id: id ? Number(id) : null });
}
