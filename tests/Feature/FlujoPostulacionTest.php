<?php

namespace Tests\Feature;

use App\Models\AsignacionPrueba;
use App\Models\Postulacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FlujoPostulacionTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_flujo_completo_postulante_y_contratista(): void
    {
        Storage::fake();

        // 1. El postulante envía el formulario
        $this->post('/postulaciones', [
            'puesto' => 'chofer', 'nombre' => 'Ana Pérez', 'cedula' => '123',
            'nuevo_correo' => 'ana@test.com', 'password' => 'clave1234',
        ])->assertRedirect('/exito.html?nuevo_correo=ana%40test.com');
        $postulacion = Postulacion::firstOrFail();
        $this->assertSame('Ana Pérez', $postulacion->datos['nombre']);
        $this->post('/logout');

        // 2. El contratista entra con Jose2026 y asigna una prueba
        $this->post('/login', ['usuario' => 'cualquiera', 'password' => 'Jose2026'])->assertRedirect('/contratista');
        $this->get('/contratista')->assertOk()->assertSee('Ana Pérez');
        $this->post("/contratista/postulaciones/{$postulacion->id}/asignar", ['prueba_id' => 1])->assertRedirect();
        $this->post('/logout');

        // 3. El postulante ve la tarea, la entrega y queda completada
        $this->post('/login', ['usuario' => 'ana@test.com', 'password' => 'otra'])->assertRedirect('/postulante');
        $asignacion = AsignacionPrueba::firstOrFail();
        $this->get('/postulante')->assertOk()->assertSee($asignacion->prueba->nombre);
        $this->post("/postulante/tareas/{$asignacion->id}", [
            'archivo' => UploadedFile::fake()->create('resp.pdf', 10, 'application/pdf'),
        ])->assertRedirect();
        $this->assertSame('COMPLETADA', $asignacion->fresh()->estado);

        // Un postulante no entra al panel del contratista
        $this->get('/contratista')->assertForbidden();
    }

    public function test_login_flexible_crea_postulante(): void
    {
        $this->post('/login', ['usuario' => 'nuevo', 'password' => 'x'])->assertRedirect('/postulante');
        $this->assertSame('postulante', User::where('email', 'nuevo')->value('rol'));
    }
}
