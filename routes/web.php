<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContratistaController;
use App\Http\Controllers\PostulacionController;
use App\Http\Controllers\PostulanteController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->file(public_path('principal.html'));
})->withoutMiddleware([
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    ValidateCsrfToken::class,
]);

// El formulario de crear-cuenta.html es HTML estático (sin token CSRF)
Route::post('/postulaciones', [PostulacionController::class, 'store'])
    ->withoutMiddleware(ValidateCsrfToken::class)
    ->name('postulaciones.store');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'form'])->name('login');
    Route::get('/admin/login', [AuthController::class, 'form'])->name('admin.login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/postulante', [PostulanteController::class, 'index'])->name('postulante.index');
    Route::post('/postulante/tareas/{asignacion}', [PostulanteController::class, 'entregar'])->name('postulante.entregar');

    Route::middleware('contratista')->prefix('contratista')->name('contratista.')->group(function () {
        Route::get('/', [ContratistaController::class, 'index'])->name('index');
        Route::get('/postulaciones/{postulacion}', [ContratistaController::class, 'show'])->name('show');
        Route::post('/postulaciones/{postulacion}/asignar', [ContratistaController::class, 'asignar'])->name('asignar');
        Route::get('/asignaciones/{asignacion}/archivo', [ContratistaController::class, 'archivo'])->name('archivo');
    });
});
