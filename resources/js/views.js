import { api } from './api.js';
import { wizardState, setWizard, clearWizard } from './state.js';
import { escapeHtml, showToast, validateStep, NIVEL_LABELS } from './utils.js';

const TERMS_HTML = `
<p><strong>Confidencialidad y protección de datos</strong></p>
<p>Al postularse a ROBASA Distribuidora, usted autoriza el tratamiento de sus datos personales exclusivamente para procesos de reclutamiento, selección y contacto relacionados con vacantes laborales. Sus datos serán almacenados de forma segura y no serán cedidos a terceros sin base legal.</p>
<p>Usted declara que la información proporcionada es verídica. ROBASA se reserva el derecho de verificar referencias y documentación. La falsedad de datos puede descalificar su postulación.</p>
<p>Conservaremos su información mientras dure el proceso de selección o el plazo legal aplicable. Puede solicitar acceso, rectificación o eliminación conforme a la normativa de protección de datos vigente.</p>
<p>Al marcar la casilla de aceptación, confirma haber leído y comprendido estos términos y condiciones.</p>
`;

function wizardProgress(current) {
  return `<div class="wizard-steps" aria-hidden="true">${[1, 2, 3, 4, 5, 6]
    .map((n) => {
      let cls = '';
      if (n < current) cls = 'done';
      else if (n === current) cls = 'active';
      return `<span class="${cls}"></span>`;
    })
    .join('')}</div>`;
}

function errorsHtml(errors) {
  if (!errors?.length) return '';
  return `<div class="form-errors" role="alert"><ul>${errors.map((e) => `<li>${escapeHtml(e)}</li>`).join('')}</ul></div>`;
}

export function renderHome(container) {
  container.innerHTML = `
    <section class="hero card">
      <h1 class="page-title">Bienvenido</h1>
      <p>Únase al equipo de ROBASA Distribuidora. Explore vacantes o acceda con su cuenta de candidato.</p>
      <div class="btn-stack">
        <a href="#/login" class="btn btn-orange">Iniciar sesión</a>
        <a href="#/vacantes" class="btn btn-blue">Trabaje con nosotros</a>
      </div>
      <a href="#/registro" class="link-text">Crear usuario</a>
    </section>
  `;
}

export function renderRegistroRedirect(container) {
  container.innerHTML = `
    <a href="#/" class="back-link">← Inicio</a>
    <section class="card">
      <h1 class="page-title">Crear usuario</h1>
      <p class="page-subtitle">Su cuenta se crea al completar una postulación. Elija una vacante para comenzar.</p>
      <a href="#/vacantes" class="btn btn-orange">Ver vacantes disponibles</a>
    </section>
  `;
}

export async function renderVacantes(container) {
  container.innerHTML = `<p class="page-subtitle">Cargando vacantes…</p>`;
  try {
    const { vacantes } = await api.getVacantes();
    const items = vacantes
      .map((v, i) => {
        const cls = i % 2 === 0 ? 'btn-blue' : 'btn-orange';
        return `<li><a href="#/vacante/${v.id}" class="btn ${cls}">${escapeHtml(v.nombre)}</a></li>`;
      })
      .join('');
    container.innerHTML = `
      <a href="#/" class="back-link">← Inicio</a>
      <h1 class="page-title">Vacantes Disponibles</h1>
      <ul class="vacancy-list">${items}</ul>
    `;
  } catch {
    container.innerHTML = `
      <a href="#/" class="back-link">← Inicio</a>
      <div class="form-errors">No se pudieron cargar las vacantes. Verifique la base de datos e importe <code>database/schema.sql</code>.</div>
    `;
  }
}

export async function renderVacanteDetail(container, id) {
  container.innerHTML = `<p class="page-subtitle">Cargando…</p>`;
  try {
    const { vacante } = await api.getVacante(id);
    setWizard({ vacante_id: Number(vacante.id) });
    container.innerHTML = `
      <a href="#/vacantes" class="back-link">← Vacantes</a>
      <article class="card vacancy-detail">
        <h2>${escapeHtml(vacante.nombre)}</h2>
        <h3>Descripción</h3>
        <p>${escapeHtml(vacante.descripcion)}</p>
        <h3>Requisitos</h3>
        <p>${escapeHtml(vacante.requisitos)}</p>
        <a href="#/postular/1" class="btn btn-orange" style="margin-top:1.25rem">Crear postulación</a>
      </article>
    `;
  } catch {
    container.innerHTML = `
      <a href="#/vacantes" class="back-link">← Vacantes</a>
      <div class="form-errors">Vacante no encontrada.</div>
    `;
  }
}

export async function renderWizard(container, step) {
  const s = wizardState;
  const stepNum = Math.min(6, Math.max(1, Number(step) || 1));
  let inner = '';
  let vacantesOptions = '';

  if (stepNum === 3) {
    try {
      const { vacantes } = await api.getVacantes();
      vacantesOptions = vacantes
        .map(
          (v) =>
            `<option value="${v.id}" ${Number(s.vacante_id) === Number(v.id) ? 'selected' : ''}>${escapeHtml(v.nombre)}</option>`
        )
        .join('');
    } catch {
      vacantesOptions = '<option value="">Error al cargar puestos</option>';
    }
  }

  if (stepNum === 1) {
    inner = `
      ${wizardProgress(1)}
      <h1 class="page-title">Términos y condiciones</h1>
      <div class="terms-box">${TERMS_HTML}</div>
      <div class="checkbox-row">
        <input type="checkbox" id="terminos" name="terminos" ${s.terminos_aceptados ? 'checked' : ''}>
        <label for="terminos">Leí y acepto los términos y condiciones</label>
      </div>
      <div id="step-errors"></div>
      <button type="button" class="btn btn-orange" data-wizard-next="2">Siguiente</button>
    `;
  } else if (stepNum === 2) {
    inner = `
      ${wizardProgress(2)}
      <h1 class="page-title">Datos personales</h1>
      <form id="wizard-form" novalidate>
        <div class="form-group">
          <label for="nombre">Nombre completo</label>
          <input id="nombre" name="nombre_completo" required value="${escapeHtml(s.nombre_completo)}" autocomplete="name">
        </div>
        <div class="form-group">
          <label for="cedula">Número de cédula</label>
          <input id="cedula" name="cedula" required value="${escapeHtml(s.cedula)}" inputmode="numeric">
        </div>
        <div class="form-group">
          <label for="telefono">Teléfono</label>
          <input id="telefono" name="telefono" required value="${escapeHtml(s.telefono)}" type="tel" autocomplete="tel">
        </div>
        <div class="form-group">
          <label for="direccion">Dirección exacta</label>
          <input id="direccion" name="direccion" required value="${escapeHtml(s.direccion)}" autocomplete="street-address">
        </div>
        <div class="form-group">
          <label for="fecha">Fecha de nacimiento (dd/mm/aaaa)</label>
          <input id="fecha" name="fecha_nacimiento" required placeholder="dd/mm/aaaa" value="${escapeHtml(s.fecha_nacimiento)}" pattern="\\d{2}/\\d{2}/\\d{4}">
        </div>
        <div class="form-group">
          <label for="licencia">Licencia de conducir</label>
          <select id="licencia" name="licencia_conducir" required>
            <option value="">Seleccione…</option>
            <option value="si" ${s.licencia_conducir === 'si' ? 'selected' : ''}>Sí</option>
            <option value="no" ${s.licencia_conducir === 'no' ? 'selected' : ''}>No</option>
          </select>
        </div>
        <div id="step-errors"></div>
        <div class="form-row">
          <button type="button" class="btn btn-ghost" data-wizard-back="1">Atrás</button>
          <button type="button" class="btn btn-orange" data-wizard-next="3">Siguiente</button>
        </div>
      </form>
    `;
  } else if (stepNum === 3) {
    inner = `
      ${wizardProgress(3)}
      <h1 class="page-title">Puesto y preferencias</h1>
      <form id="wizard-form" novalidate>
        <div class="form-group">
          <label for="puesto">Puesto</label>
          <select id="puesto" name="vacante_id" required>
            <option value="">Seleccione…</option>
            ${vacantesOptions}
          </select>
        </div>
        <div class="form-group">
          <label for="pretension">Pretensión salarial</label>
          <select id="pretension" name="pretension_salarial" required>
            <option value="">Seleccione…</option>
            ${['Menos de ₡400.000', '₡400.000 - ₡600.000', '₡600.000 - ₡800.000', '₡800.000 - ₡1.000.000', 'Más de ₡1.000.000']
              .map((o) => `<option value="${escapeHtml(o)}" ${s.pretension_salarial === o ? 'selected' : ''}>${escapeHtml(o)}</option>`)
              .join('')}
          </select>
        </div>
        <div class="form-group">
          <label for="zona">Zona</label>
          <select id="zona" name="zona" required>
            <option value="">Seleccione…</option>
            ${['San José', 'Alajuela', 'Cartago', 'Heredia', 'Guanacaste', 'Puntarenas', 'Limón', 'Otra']
              .map((o) => `<option value="${escapeHtml(o)}" ${s.zona === o ? 'selected' : ''}>${escapeHtml(o)}</option>`)
              .join('')}
          </select>
        </div>
        <div class="form-group">
          <label for="disp">Disponibilidad</label>
          <select id="disp" name="disponibilidad" required>
            <option value="">Seleccione…</option>
            ${['Tiempo completo', 'Medio tiempo', 'Fines de semana', 'Inmediata', 'A convenir']
              .map((o) => `<option value="${escapeHtml(o)}" ${s.disponibilidad === o ? 'selected' : ''}>${escapeHtml(o)}</option>`)
              .join('')}
          </select>
        </div>
        <div class="form-group">
          <label for="exp">Experiencia en el puesto</label>
          <select id="exp" name="experiencia_puesto" required>
            <option value="">Seleccione…</option>
            <option value="si" ${s.experiencia_puesto === 'si' ? 'selected' : ''}>Sí</option>
            <option value="no" ${s.experiencia_puesto === 'no' ? 'selected' : ''}>No</option>
          </select>
        </div>
        <div id="step-errors"></div>
        <div class="form-row">
          <button type="button" class="btn btn-ghost" data-wizard-back="2">Atrás</button>
          <button type="button" class="btn btn-orange" data-wizard-next="4">Siguiente</button>
        </div>
      </form>
    `;
  } else if (stepNum === 4) {
    inner = `
      ${wizardProgress(4)}
      <h1 class="page-title">Formación académica</h1>
      <form id="wizard-form" novalidate>
        <div class="form-group">
          <label for="nivel">Nivel educativo</label>
          <select id="nivel" name="nivel_educativo" required>
            <option value="">Seleccione…</option>
            ${Object.entries(NIVEL_LABELS)
              .map(([k, label]) => `<option value="${k}" ${s.nivel_educativo === k ? 'selected' : ''}>${escapeHtml(label)}</option>`)
              .join('')}
          </select>
        </div>
        <div id="step-errors"></div>
        <div class="form-row">
          <button type="button" class="btn btn-ghost" data-wizard-back="3">Atrás</button>
          <button type="button" class="btn btn-orange" data-wizard-next="5">Siguiente</button>
        </div>
      </form>
    `;
  } else if (stepNum === 5) {
    inner = `
      ${wizardProgress(5)}
      <h1 class="page-title">Empleos anteriores</h1>
      <form id="wizard-form" novalidate>
        <div class="form-group">
          <label for="empleo">Detalle su último empleo</label>
          <textarea id="empleo" name="ultimo_empleo" placeholder="Empresa, puesto, periodo y funciones principales…">${escapeHtml(s.ultimo_empleo)}</textarea>
        </div>
        <div id="step-errors"></div>
        <div class="form-row">
          <button type="button" class="btn btn-ghost" data-wizard-back="4">Atrás</button>
          <button type="button" class="btn btn-orange" data-wizard-next="6">Siguiente</button>
        </div>
      </form>
    `;
  } else {
    inner = `
      ${wizardProgress(6)}
      <h1 class="page-title">Credenciales y envío</h1>
      <p class="page-subtitle">Registre su correo y contraseña para acceder al panel de candidato.</p>
      <form id="wizard-form" novalidate>
        <div class="form-group">
          <label for="email">Correo electrónico</label>
          <input id="email" name="email" type="email" required value="${escapeHtml(s.email)}" autocomplete="email">
        </div>
        <div class="form-group">
          <label for="password">Contraseña</label>
          <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password">
        </div>
        <div id="step-errors"></div>
        <div class="form-row">
          <button type="button" class="btn btn-ghost" data-wizard-back="5">Atrás</button>
          <button type="submit" class="btn btn-orange" id="btn-enviar">Enviar Formulario</button>
        </div>
      </form>
    `;
  }

  container.innerHTML = `
    <a href="#/vacantes" class="back-link">← Cancelar postulación</a>
    <section class="card">${inner}</section>
  `;

  bindWizardEvents(container, stepNum);
}

function collectStepData(stepNum, root) {
  const form = root.querySelector('#wizard-form');
  if (stepNum === 1) {
    return {
      terminos_aceptados: root.querySelector('#terminos')?.checked || false,
    };
  }
  if (!form) return {};
  const fd = new FormData(form);
  const obj = Object.fromEntries(fd.entries());
  if (obj.vacante_id) obj.vacante_id = Number(obj.vacante_id);
  return obj;
}

function bindWizardEvents(container, stepNum) {
  container.querySelectorAll('[data-wizard-back]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const prev = btn.getAttribute('data-wizard-back');
      setWizard(collectStepData(stepNum, container));
      location.hash = `#/postular/${prev}`;
    });
  });

  container.querySelectorAll('[data-wizard-next]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const next = btn.getAttribute('data-wizard-next');
      const data = { ...wizardState, ...collectStepData(stepNum, container) };
      setWizard(data);
      const errors = validateStep(stepNum, data);
      const errEl = container.querySelector('#step-errors');
      if (errors.length) {
        errEl.innerHTML = errorsHtml(errors);
        return;
      }
      errEl.innerHTML = '';
      location.hash = `#/postular/${next}`;
    });
  });

  const form = container.querySelector('#wizard-form');
  if (form && stepNum === 6) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const data = { ...wizardState, ...collectStepData(6, container) };
      setWizard(data);
      const errors = validateStep(6, data);
      const errEl = container.querySelector('#step-errors');
      if (errors.length) {
        errEl.innerHTML = errorsHtml(errors);
        return;
      }
      const btn = container.querySelector('#btn-enviar');
      btn.disabled = true;
      btn.textContent = 'Enviando…';
      try {
        const payload = {
          terminos_aceptados: data.terminos_aceptados,
          nombre_completo: data.nombre_completo,
          cedula: data.cedula,
          telefono: data.telefono,
          direccion: data.direccion,
          fecha_nacimiento: data.fecha_nacimiento,
          licencia_conducir: data.licencia_conducir,
          vacante_id: data.vacante_id,
          pretension_salarial: data.pretension_salarial,
          zona: data.zona,
          disponibilidad: data.disponibilidad,
          experiencia_puesto: data.experiencia_puesto,
          nivel_educativo: data.nivel_educativo,
          ultimo_empleo: data.ultimo_empleo,
          email: data.email,
          password: data.password,
        };
        const res = await api.crearPostulacion(payload);
        sessionStorage.setItem(
          'robasa_confirmacion',
          JSON.stringify({
            numero: res.numero_postulacion,
            delante: res.personas_delante,
            vacante: res.vacante_nombre,
          })
        );
        clearWizard();
        location.hash = '#/confirmacion';
      } catch (err) {
        btn.disabled = false;
        btn.textContent = 'Enviar Formulario';
        const list = err.payload?.errors || [err.message];
        errEl.innerHTML = errorsHtml(list);
      }
    });
  }
}

export function renderConfirmacion(container) {
  let info = { numero: '—', delante: 0, vacante: '' };
  try {
    info = { ...info, ...JSON.parse(sessionStorage.getItem('robasa_confirmacion') || '{}') };
  } catch {
    /* ignore */
  }
  container.innerHTML = `
    <section class="card success-card">
      <div class="success-icon" aria-hidden="true">✓</div>
      <h1 class="page-title">¡Gracias por su interés en formar parte del equipo ROBASA!</h1>
      <p class="page-subtitle">Su postulación ha sido registrada correctamente${info.vacante ? ` para <strong>${escapeHtml(info.vacante)}</strong>` : ''}.</p>
      <div class="queue-badge">
        Número de postulación: ${escapeHtml(String(info.numero))}<br>
        <span style="font-weight:500;font-size:0.95rem">(Tienes ${escapeHtml(String(info.delante))} personas por delante de ti en el proceso)</span>
      </div>
      <a href="#/" class="btn btn-orange">Finalizar</a>
    </section>
  `;
}

export function renderLogin(container) {
  container.innerHTML = `
    <a href="#/" class="back-link">← Inicio</a>
    <section class="card">
      <h1 class="page-title">Iniciar sesión</h1>
      <p class="page-subtitle">Acceda con el correo y contraseña registrados en su postulación.</p>
      <form id="login-form" novalidate>
        <div class="form-group">
          <label for="login-email">Correo electrónico</label>
          <input id="login-email" name="email" type="email" required autocomplete="username">
        </div>
        <div class="form-group">
          <label for="login-pass">Contraseña</label>
          <input id="login-pass" name="password" type="password" required autocomplete="current-password">
        </div>
        <div id="login-errors"></div>
        <button type="submit" class="btn btn-orange">Iniciar sesión</button>
      </form>
    </section>
  `;

  container.querySelector('#login-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);
    const email = fd.get('email');
    const password = fd.get('password');
    const errEl = container.querySelector('#login-errors');
    errEl.innerHTML = '';
    try {
      await api.login(email, password);
      location.hash = '#/panel';
    } catch (err) {
      errEl.innerHTML = errorsHtml([err.message]);
    }
  });
}

export async function renderPanel(container) {
  container.innerHTML = `<p class="page-subtitle">Cargando…</p>`;
  try {
    const me = await api.me();
    if (!me.authenticated) {
      location.hash = '#/login';
      return;
    }
    const { postulaciones } = await api.misPostulaciones();
    const list =
      postulaciones.length === 0
        ? '<p>No tiene postulaciones registradas.</p>'
        : postulaciones
            .map(
              (p) => `
          <div class="postulacion-item">
            <strong>${escapeHtml(p.vacante_nombre)}</strong><br>
            Nº ${p.numero_postulacion} — ${p.personas_delante} personas por delante<br>
            <small class="page-subtitle">${escapeHtml(p.zona)} · ${escapeHtml(p.disponibilidad)}</small>
          </div>`
            )
            .join('');

    container.innerHTML = `
      <section class="card">
        <h1 class="page-title">Panel del candidato</h1>
        <p class="panel-greeting">Hola, <strong>${escapeHtml(me.usuario.nombre)}</strong></p>
        <h3 style="color:var(--blue);margin:0 0 0.5rem">Mis postulaciones</h3>
        ${list}
        <button type="button" class="btn btn-blue" id="btn-logout" style="margin-top:1rem">Cerrar sesión</button>
        <a href="#/" class="link-text">Volver al inicio</a>
      </section>
    `;
    container.querySelector('#btn-logout').addEventListener('click', async () => {
      await api.logout();
      showToast('Sesión cerrada', 'success');
      location.hash = '#/';
    });
  } catch {
    location.hash = '#/login';
  }
}
