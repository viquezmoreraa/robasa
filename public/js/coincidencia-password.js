// Avisa en vivo si "Confirmar contraseña" coincide con "Contraseña".
// Uso: <input id="password_confirmation" data-coincide-con="password" aria-describedby="idDelMensaje">
// El mensaje recibe data-estado="ok" | "error" y la confirmación las clases .valido / .invalido.
(function () {
    document.querySelectorAll('[data-coincide-con]').forEach((confirmacion) => {
        const original = document.getElementById(confirmacion.dataset.coincideCon);
        const mensaje = document.getElementById(confirmacion.getAttribute('aria-describedby'));

        function mostrar(texto, estado) {
            mensaje.textContent = texto;
            mensaje.dataset.estado = estado;
            confirmacion.classList.toggle('valido', estado === 'ok');
            confirmacion.classList.toggle('invalido', estado === 'error');
        }

        // terminado = el usuario ya salió del campo o intentó enviar
        function comparar(terminado) {
            const a = original.value;
            const b = confirmacion.value;
            const coincide = a === b;

            // Bloquea el envío del formulario mientras no coincidan
            confirmacion.setCustomValidity(b && !coincide ? 'Las contraseñas no coinciden.' : '');

            if (!b) {
                mostrar('', '');
            } else if (coincide) {
                mostrar('✓ Las contraseñas coinciden.', 'ok');
            } else if (terminado || !a.startsWith(b)) {
                // Mientras lo que escribe sigue siendo el inicio de la contraseña, todavía no es un error
                mostrar('✗ Las contraseñas no coinciden.', 'error');
            } else {
                mostrar('', '');
            }
        }

        confirmacion.addEventListener('input', () => comparar(false));
        confirmacion.addEventListener('blur', () => comparar(true));
        confirmacion.addEventListener('invalid', () => comparar(true));
        // Si cambia la contraseña después de haberla confirmado, se vuelve a comparar
        original.addEventListener('input', () => comparar(true));

        comparar(false);
    });
})();
