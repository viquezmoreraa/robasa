<?php

namespace Tests\Feature;

use App\Models\Postulacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TalentoHumanoCambiaContrasenaTest extends TestCase
{
    use RefreshDatabase;

    private function crearPostulacion(string $rol = 'postulante'): array
    {
        $postulante = User::factory()->create(['rol' => $rol]);
        $talentoHumano = User::factory()->create(['rol' => 'talento_humano']);
        $postulacion = Postulacion::create([
            'user_id' => $postulante->id,
            'nombre'  => 'Ana Pérez',
            'correo'  => $postulante->email,
        ]);

        return [$postulante, $talentoHumano, $postulacion];
    }

    public function test_talento_humano_cambia_la_contrasena_del_postulante(): void
    {
        [$postulante, $talentoHumano, $postulacion] = $this->crearPostulacion();

        $this->actingAs($talentoHumano)
            ->get(route('talento-humano.show', $postulacion))
            ->assertOk()
            ->assertSee('Cambiar contraseña del postulante');

        $this->actingAs($talentoHumano)
            ->from(route('talento-humano.show', $postulacion))
            ->post(route('talento-humano.contrasena', $postulacion), [
                'password' => 'nueva1234', 'password_confirmation' => 'nueva1234',
            ])
            ->assertRedirect(route('talento-humano.show', $postulacion))
            ->assertSessionHas('ok');

        $this->assertTrue(Hash::check('nueva1234', $postulante->fresh()->password));
    }

    public function test_la_contrasena_debe_cumplir_las_reglas(): void
    {
        [$postulante, $talentoHumano, $postulacion] = $this->crearPostulacion();
        $anterior = $postulante->password;

        $this->actingAs($talentoHumano)
            ->post(route('talento-humano.contrasena', $postulacion), [
                'password' => 'corta', 'password_confirmation' => 'otra',
            ])
            ->assertSessionHasErrors('password');

        $this->assertSame($anterior, $postulante->fresh()->password);
    }

    public function test_un_postulante_no_puede_cambiar_contrasenas(): void
    {
        [$postulante, , $postulacion] = $this->crearPostulacion();

        $this->actingAs($postulante)
            ->post(route('talento-humano.contrasena', $postulacion), [
                'password' => 'nueva1234', 'password_confirmation' => 'nueva1234',
            ])
            ->assertForbidden();
    }

    public function test_no_se_cambia_la_contrasena_de_una_cuenta_de_talento_humano(): void
    {
        [$otro, $talentoHumano, $postulacion] = $this->crearPostulacion('talento_humano');
        $anterior = $otro->password;

        $this->actingAs($talentoHumano)
            ->post(route('talento-humano.contrasena', $postulacion), [
                'password' => 'nueva1234', 'password_confirmation' => 'nueva1234',
            ])
            ->assertNotFound();

        $this->assertSame($anterior, $otro->fresh()->password);
    }
}
