import {
  renderHome,
  renderRegistroRedirect,
  renderVacantes,
  renderVacanteDetail,
  renderWizard,
  renderConfirmacion,
  renderLogin,
  renderPanel,
} from './views.js';

const main = document.getElementById('main-content');
document.getElementById('year').textContent = String(new Date().getFullYear());

async function router() {
  const hash = location.hash.replace(/^#/, '') || '/';
  const parts = hash.split('/').filter(Boolean);

  window.scrollTo(0, 0);

  if (parts.length === 0 || (parts.length === 1 && parts[0] === '')) {
    renderHome(main);
    return;
  }

  switch (parts[0]) {
    case 'vacantes':
      await renderVacantes(main);
      break;
    case 'vacante':
      await renderVacanteDetail(main, parts[1]);
      break;
    case 'postular':
      await renderWizard(main, parts[1] || '1');
      break;
    case 'confirmacion':
      renderConfirmacion(main);
      break;
    case 'login':
      renderLogin(main);
      break;
    case 'registro':
      renderRegistroRedirect(main);
      break;
    case 'panel':
      await renderPanel(main);
      break;
    default:
      renderHome(main);
  }
}

window.addEventListener('hashchange', router);
router();
