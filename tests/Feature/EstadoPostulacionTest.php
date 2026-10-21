<?php

namespace Tests\Feature;

use App\Models\Postulacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstadoPostulacionTest extends TestCase
{
    use RefreshDatabase;

    private function crearPostulacion(): array
    {
        $postulante = User::factory()->create(['rol' => 'postulante']);
        $talentoHumano = User::factory()->create(['rol' => 'talento_humano']);
        $postulacion = Postulacion::create([
            'user_id' => $postulante->id,
            'nombre'  => 'Ana Pérez',
            'correo'  => $postulante->email,
        ]);

        return [$postulante, $talentoHumano, $postulacion];
    }

    public function test_una_postulacion_nueva_queda_en_postulado_y_el_postulante_lo_ve(): void
    {
        $this->post('/postulaciones', [
            'puesto' => 'chofer', 'username' => 'ana_perez', 'nuevo_correo' => 'ana@gmail.com',
            'password' => 'clave1234', 'password_confirmation' => 'clave1234',
        ])->assertRedirect();

        $this->assertSame('POSTULADO', Postulacion::firstOrFail()->estado);
        $this->get('/postulante')
            ->assertOk()
            ->assertSee('Estado de su postulación:')
            ->assertSee('Postulado')
            ->assertSee('El candidato completó y envió la postulación.');
    }

    public function test_talento_humano_cambia_el_estado_y_el_postulante_lo_ve(): void
    {
        [$postulante, $talentoHumano, $postulacion] = $this->crearPostulacion();

        $this->actingAs($talentoHumano)
            ->from(route('talento-humano.show', $postulacion))
            ->post(route('talento-humano.estado', $postulacion), ['estado' => 'ENTREVISTA'])
            ->assertRedirect(route('talento-humano.show', $postulacion))
            ->assertSessionHas('ok', 'Estado actualizado a «Entrevista».');

        $this->assertSame('ENTREVISTA', $postulacion->fresh()->estado);
        $this->actingAs($talentoHumano)->get(route('talento-humano.index'))->assertOk()->assertSee('Entrevista');
        $this->actingAs($postulante)
            ->get(route('postulante.index'))
            ->assertOk()
            ->assertSee('Entrevista')
            ->assertSee('Se encuentra en proceso de entrevista.');
    }

    public function test_un_estado_que_no_existe_se_rechaza(): void
    {
        [, $talentoHumano, $postulacion] = $this->crearPostulacion();

        $this->actingAs($talentoHumano)
            ->post(route('talento-humano.estado', $postulacion), ['estado' => 'APROBADO'])
            ->assertSessionHasErrors('estado');

        $this->assertSame('POSTULADO', $postulacion->fresh()->estado);
    }

    public function test_un_postulante_no_puede_cambiar_su_estado(): void
    {
        [$postulante, , $postulacion] = $this->crearPostulacion();

        $this->actingAs($postulante)
            ->post(route('talento-humano.estado', $postulacion), ['estado' => 'CONTRATADO'])
            ->assertForbidden();

        $this->assertSame('POSTULADO', $postulacion->fresh()->estado);
    }
}
