<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vacante;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VacantesTest extends TestCase
{
    use RefreshDatabase;

    private function talentoHumano(): User
    {
        return User::factory()->create(['rol' => 'talento_humano']);
    }

    private function registro(string $puesto)
    {
        return $this->post('/postulaciones', [
            'puesto' => $puesto, 'username' => 'ana_perez', 'nuevo_correo' => 'ana@gmail.com',
            'password' => 'clave1234', 'password_confirmation' => 'clave1234',
        ]);
    }

    public function test_la_pagina_publica_recibe_las_plazas_por_puesto(): void
    {
        Vacante::where('puesto', 'chofer')->update(['cantidad' => 3]);

        $this->getJson('/vacantes/disponibles')
            ->assertOk()
            ->assertJsonPath('chofer', 3)
            ->assertJsonPath('facturacion', 1)
            ->assertJsonCount(14);
    }

    public function test_talento_humano_actualiza_las_plazas(): void
    {
        $th = $this->talentoHumano();
        $chofer = Vacante::where('puesto', 'chofer')->firstOrFail();
        $almacen = Vacante::where('puesto', 'almacen')->firstOrFail();

        $this->actingAs($th)->get(route('talento-humano.vacantes'))
            ->assertOk()
            ->assertSee('Chofer')
            ->assertSee('Guardar plazas');

        $this->actingAs($th)
            ->from(route('talento-humano.vacantes'))
            ->post(route('talento-humano.vacantes.actualizar'), ['cantidad' => [$chofer->id => 5, $almacen->id => 0]])
            ->assertRedirect(route('talento-humano.vacantes'))
            ->assertSessionHas('ok', 'Plazas de las vacantes actualizadas.');

        $this->assertSame(5, $chofer->fresh()->cantidad);
        $this->assertSame(0, $almacen->fresh()->cantidad);
    }

    public function test_no_acepta_cantidades_negativas_ni_texto(): void
    {
        $th = $this->talentoHumano();
        $chofer = Vacante::where('puesto', 'chofer')->firstOrFail();

        $this->actingAs($th)->post(route('talento-humano.vacantes.actualizar'), ['cantidad' => [$chofer->id => -1]])
            ->assertSessionHasErrors("cantidad.{$chofer->id}");
        $this->actingAs($th)->post(route('talento-humano.vacantes.actualizar'), ['cantidad' => [$chofer->id => 'dos']])
            ->assertSessionHasErrors("cantidad.{$chofer->id}");

        $this->assertSame(1, $chofer->fresh()->cantidad);
    }

    public function test_un_postulante_no_puede_cambiar_las_plazas(): void
    {
        $postulante = User::factory()->create(['rol' => 'postulante']);
        $chofer = Vacante::where('puesto', 'chofer')->firstOrFail();

        $this->actingAs($postulante)->get(route('talento-humano.vacantes'))->assertForbidden();
        $this->actingAs($postulante)
            ->post(route('talento-humano.vacantes.actualizar'), ['cantidad' => [$chofer->id => 9]])
            ->assertForbidden();

        $this->assertSame(1, $chofer->fresh()->cantidad);
    }

    public function test_no_se_puede_postular_a_un_puesto_sin_plazas(): void
    {
        Vacante::where('puesto', 'chofer')->update(['cantidad' => 0]);

        $this->registro('chofer')->assertSessionHasErrors('puesto');
        $this->assertGuest();

        $this->registro('almacen')->assertSessionDoesntHaveErrors();
        $this->assertAuthenticated();
    }
}
