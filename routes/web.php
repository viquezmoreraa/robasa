<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostulacionController;
use App\Http\Controllers\PostulanteController;
use App\Http\Controllers\RecuperarContrasenaController;
use App\Http\Controllers\TalentoHumanoController;
use App\Http\Controllers\VacanteController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->file(public_path('principal.html'));
})->withoutMiddleware([
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    ValidateCsrfToken::class,
]);

// Plazas por puesto que consulta public/vacantes.html (público, sin sesión)
Route::get('/vacantes/disponibles', [VacanteController::class, 'disponibles'])->name('vacantes.disponibles')->withoutMiddleware([
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    ValidateCsrfToken::class,
]);

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'form'])->name('login');
    Route::get('/admin/login', [AuthController::class, 'form'])->name('admin.login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');

    // Recuperación de contraseña: enlace con token de un solo uso que vence (config/auth.php)
    Route::get('/olvide-contrasena', [RecuperarContrasenaController::class, 'solicitud'])->name('password.request');
    Route::post('/olvide-contrasena', [RecuperarContrasenaController::class, 'enviarEnlace'])
        ->middleware('throttle:5,1')->name('password.email');
    Route::get('/restablecer-contrasena/{token}', [RecuperarContrasenaController::class, 'formulario'])->name('password.reset');
    Route::post('/restablecer-contrasena', [RecuperarContrasenaController::class, 'restablecer'])
        ->middleware('throttle:5,1')->name('password.update');

    // Registro: último paso del formulario de postulación (única forma de crear cuentas)
    Route::get('/crear-cuenta', [PostulacionController::class, 'create'])->name('postulaciones.create');
    Route::post('/postulaciones', [PostulacionController::class, 'store'])->name('postulaciones.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/postulante', [PostulanteController::class, 'index'])->name('postulante.index');
    Route::post('/postulante/tareas/{asignacion}', [PostulanteController::class, 'entregar'])->name('postulante.entregar');
    Route::post('/postulante/tareas/{asignacion}/video', [PostulanteController::class, 'entregarVideo'])->name('postulante.video');

    // Pruebas psicométricas digitalizadas: se responden pregunta por pregunta
    // (no subiendo archivo) y disparan MotorPsicometrico::calcular().
    Route::get('/postulante/pruebas/{asignacion}', [PostulanteController::class, 'mostrarPrueba'])->name('postulante.prueba.show');
    Route::post('/postulante/pruebas/{asignacion}', [PostulanteController::class, 'guardarPrueba'])->name('postulante.prueba.guardar');

    Route::middleware('talento-humano')->prefix('talento-humano')->name('talento-humano.')->group(function () {
        Route::get('/', [TalentoHumanoController::class, 'index'])->name('index');
        Route::get('/postulaciones/{postulacion}', [TalentoHumanoController::class, 'show'])->name('show');
        Route::post('/postulaciones/{postulacion}/estado', [TalentoHumanoController::class, 'actualizarEstado'])->name('estado');
        Route::post('/postulaciones/{postulacion}/asignar', [TalentoHumanoController::class, 'asignar'])->name('asignar');
        Route::post('/postulaciones/{postulacion}/contrasena', [TalentoHumanoController::class, 'cambiarContrasena'])->name('contrasena');
        Route::get('/asignaciones/{asignacion}/archivo', [TalentoHumanoController::class, 'archivo'])->name('archivo');
        Route::get('/vacantes', [VacanteController::class, 'index'])->name('vacantes');
        Route::post('/vacantes', [VacanteController::class, 'actualizar'])->name('vacantes.actualizar');
    });
});
