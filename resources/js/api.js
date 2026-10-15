const API_BASE = (document.querySelector('meta[name="api-base"]')?.content || '../api').replace(/\/$/, '');

async function request(path, options = {}) {
  const url = `${API_BASE}/${path.replace(/^\//, '')}`;
  const res = await fetch(url, {
    credentials: 'include',
    headers: {
      'Content-Type': 'application/json',
      ...(options.headers || {}),
    },
    ...options,
  });
  let data = null;
  const text = await res.text();
  try {
    data = text ? JSON.parse(text) : {};
  } catch {
    throw new Error('Respuesta inválida del servidor.');
  }
  if (!res.ok) {
    const err = new Error(data.error || data.errors?.[0] || 'Error en la solicitud.');
    err.status = res.status;
    err.payload = data;
    throw err;
  }
  return data;
}

export const api = {
  getVacantes: () => request('vacantes'),
  getVacante: (id) => request(`vacantes/${id}`),
  crearPostulacion: (body) => request('postulaciones', { method: 'POST', body: JSON.stringify(body) }),
  login: (email, password) =>
    request('auth/login', { method: 'POST', body: JSON.stringify({ email, password }) }),
  logout: () => request('auth/logout', { method: 'POST', body: '{}' }),
  me: () => request('auth/me'),
  misPostulaciones: () => request('mis-postulaciones'),
};
