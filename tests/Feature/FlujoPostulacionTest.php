<?php

namespace Tests\Feature;

use App\Models\AsignacionPrueba;
use App\Models\Postulacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FlujoPostulacionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Se siembra dentro de la transacción de cada prueba: no depende del orden de los tests
        // ni deja datos (como el usuario jose) para las demás clases
        $this->seed();
    }

    public function test_flujo_completo_postulante_y_contratista(): void
    {
        Storage::fake();

        // 1. El postulante envía el formulario y crea su cuenta
        $this->post('/postulaciones', [
            'puesto' => 'chofer', 'nombre' => 'Ana Pérez', 'cedula' => '123',
            'dia' => '5', 'mes' => '3', 'anio' => '1995', 'tiene_licencia' => 'si', 'tipo_licencia' => 'B1',
            'username' => 'ana_perez', 'nuevo_correo' => 'ana@gmail.com',
            'password' => 'clave1234', 'password_confirmation' => 'clave1234',
        ])->assertRedirect('/exito.html?nuevo_correo=ana%40gmail.com');
        $postulacion = Postulacion::firstOrFail();
        $this->assertSame('Ana Pérez', $postulacion->nombre);
        $this->assertSame('1995-03-05', $postulacion->fecha_nacimiento->toDateString());
        $this->assertTrue($postulacion->tiene_licencia);
        $this->post('/logout');

        // 2. El contratista entra con su usuario y contraseña, y asigna una prueba
        $this->post('/login', ['usuario' => 'jose', 'password' => 'Jose2026'])->assertRedirect('/contratista');
        $this->get('/contratista')->assertOk()->assertSee('Ana Pérez');
        $this->post("/contratista/postulaciones/{$postulacion->id}/asignar", ['prueba_id' => 1])->assertRedirect();
        $this->post('/logout');

        // 3. El postulante ve la tarea, la entrega y queda completada
        $this->post('/login', ['usuario' => 'ana@gmail.com', 'password' => 'clave1234'])->assertRedirect('/postulante');
        $asignacion = AsignacionPrueba::firstOrFail();
        $this->get('/postulante')->assertOk()->assertSee($asignacion->prueba->nombre);
        $this->post("/postulante/tareas/{$asignacion->id}", [
            'archivo' => UploadedFile::fake()->create('resp.pdf', 10, 'application/pdf'),
        ])->assertRedirect();
        $this->assertSame('COMPLETADA', $asignacion->fresh()->estado);

        // Un postulante no entra al panel del contratista
        $this->get('/contratista')->assertForbidden();
    }
}
