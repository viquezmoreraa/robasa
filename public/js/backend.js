// Las rutas /login, /postulaciones, /postulante, etc. solo existen en Laravel.
// Si la página se abre desde VS Code (Live Server o el archivo directo),
// los enlaces y formularios que empiezan con "/" se envían al servidor de Laravel.
(function () {
    const BACKEND = 'http://127.0.0.1:8000';   // `php artisan serve`

    const servidoPorLaravel = location.port === '8000';
    if (servidoPorLaravel) return;

    document.querySelectorAll('a[href^="/"]').forEach(a => {
        a.href = BACKEND + a.getAttribute('href');
    });
    document.querySelectorAll('form[action^="/"]').forEach(f => {
        f.action = BACKEND + f.getAttribute('action');
    });
})();
