<?php

namespace Tests\Feature;

use App\Models\AsignacionPrueba;
use App\Models\Postulacion;
use App\Models\Prueba;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VideoExperienciaTest extends TestCase
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

    private function cambiarEstado(User $talentoHumano, Postulacion $postulacion, string $estado)
    {
        return $this->actingAs($talentoHumano)->post(route('talento-humano.estado', $postulacion), ['estado' => $estado]);
    }

    private function asignacionesDeVideo(Postulacion $postulacion)
    {
        $prueba = Prueba::where('codigo', Prueba::VIDEO_EXPERIENCIA)->firstOrFail();

        return $postulacion->asignaciones()->where('prueba_id', $prueba->id)->get();
    }

    public function test_al_preseleccionar_se_asigna_el_video_y_el_postulante_lo_ve(): void
    {
        [$postulante, $talentoHumano, $postulacion] = $this->crearPostulacion();

        $this->cambiarEstado($talentoHumano, $postulacion, 'PRESELECCIONADO')
            ->assertSessionHas('ok', 'Estado actualizado a «Preseleccionado». Se le asignó la tarea del video de experiencia laboral.');

        $asignaciones = $this->asignacionesDeVideo($postulacion);
        $this->assertCount(1, $asignaciones);
        $this->assertSame('ASIGNADA', $asignaciones->first()->estado);
        $this->assertSame($talentoHumano->id, $asignaciones->first()->asignada_por);

        $this->actingAs($postulante)->get(route('postulante.index'))
            ->assertOk()
            ->assertSee('Video de experiencia laboral')
            ->assertSee('Subir video');
    }

    public function test_otros_estados_no_asignan_el_video(): void
    {
        [, $talentoHumano, $postulacion] = $this->crearPostulacion();

        $this->cambiarEstado($talentoHumano, $postulacion, 'ENTREVISTA');

        $this->assertCount(0, $this->asignacionesDeVideo($postulacion));
    }

    public function test_volver_a_preseleccionar_no_duplica_la_tarea(): void
    {
        [, $talentoHumano, $postulacion] = $this->crearPostulacion();

        $this->cambiarEstado($talentoHumano, $postulacion, 'PRESELECCIONADO');
        $this->cambiarEstado($talentoHumano, $postulacion, 'EN_REVISION');
        $this->cambiarEstado($talentoHumano, $postulacion, 'PRESELECCIONADO')
            ->assertSessionHas('ok', 'Estado actualizado a «Preseleccionado».');

        $this->assertCount(1, $this->asignacionesDeVideo($postulacion));
    }

    public function test_el_postulante_sube_el_video_y_talento_humano_lo_ve(): void
    {
        Storage::fake();
        [$postulante, $talentoHumano, $postulacion] = $this->crearPostulacion();
        $this->cambiarEstado($talentoHumano, $postulacion, 'PRESELECCIONADO');
        $asignacion = $this->asignacionesDeVideo($postulacion)->first();

        $this->actingAs($postulante)
            ->post(route('postulante.video', $asignacion), [
                'video' => UploadedFile::fake()->create('experiencia.mp4', 5000, 'video/mp4'),
            ])
            ->assertRedirect(route('postulante.index'))
            ->assertSessionHas('ok', 'Video entregado correctamente.');

        $asignacion->refresh();
        $this->assertSame('COMPLETADA', $asignacion->estado);
        Storage::assertExists($asignacion->archivo_path);

        $this->actingAs($talentoHumano)->get(route('talento-humano.show', $postulacion))
            ->assertOk()
            ->assertSee('<video src="' . route('talento-humano.archivo', $asignacion) . '"', false);
    }

    public function test_se_rechaza_un_archivo_que_no_es_video_o_muy_pesado(): void
    {
        Storage::fake();
        [$postulante, $talentoHumano, $postulacion] = $this->crearPostulacion();
        $this->cambiarEstado($talentoHumano, $postulacion, 'PRESELECCIONADO');
        $asignacion = $this->asignacionesDeVideo($postulacion)->first();

        $this->actingAs($postulante)
            ->post(route('postulante.video', $asignacion), ['video' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf')])
            ->assertSessionHasErrors('video');

        $this->actingAs($postulante)
            ->post(route('postulante.video', $asignacion), ['video' => UploadedFile::fake()->create('largo.mp4', 40000, 'video/mp4')])
            ->assertSessionHasErrors('video');

        $this->assertSame('ASIGNADA', $asignacion->fresh()->estado);
    }

    public function test_otro_postulante_no_puede_subir_el_video(): void
    {
        Storage::fake();
        [, $talentoHumano, $postulacion] = $this->crearPostulacion();
        $this->cambiarEstado($talentoHumano, $postulacion, 'PRESELECCIONADO');
        $asignacion = $this->asignacionesDeVideo($postulacion)->first();
        $otro = User::factory()->create(['rol' => 'postulante']);

        $this->actingAs($otro)
            ->post(route('postulante.video', $asignacion), ['video' => UploadedFile::fake()->create('v.mp4', 100, 'video/mp4')])
            ->assertForbidden();

        $this->assertSame('ASIGNADA', $asignacion->fresh()->estado);
    }
}
