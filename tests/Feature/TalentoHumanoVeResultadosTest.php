<?php

namespace Tests\Feature;

use App\Models\AsignacionPrueba;
use App\Models\Postulacion;
use App\Models\User;
use Database\Seeders\BarsitSeeder;
use Database\Seeders\KeirseySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TalentoHumanoVeResultadosTest extends TestCase
{
    use RefreshDatabase;

    private function crearAsignacionAsignada(string $codigoPrueba): array
    {
        $postulante = User::factory()->create(['rol' => 'postulante']);
        $talentoHumano = User::factory()->create(['rol' => 'talento_humano']);
        $postulacion = Postulacion::create([
            'user_id' => $postulante->id,
            'nombre'  => 'Ana Pérez',
            'correo'  => $postulante->email,
        ]);
        $pruebaId = DB::table('pruebas')->where('codigo', $codigoPrueba)->value('id');

        $asignacion = AsignacionPrueba::create([
            'postulacion_id' => $postulacion->id,
            'prueba_id'      => $pruebaId,
            'asignada_por'   => $talentoHumano->id,
            'estado'         => 'ASIGNADA',
        ]);

        return [$postulante, $talentoHumano, $postulacion, $asignacion];
    }

    public function test_th_ve_el_resumen_y_la_interpretacion_de_keirsey_completado(): void
    {
        $this->seed(KeirseySeeder::class);
        [$postulante, $talentoHumano, $postulacion, $asignacion] = $this->crearAsignacionAsignada('KEIRSEY');

        $preguntas = DB::table('preguntas')->where('prueba_id', $asignacion->prueba_id)->get();
        $opciones = DB::table('opciones')->whereIn('pregunta_id', $preguntas->pluck('id'))->get()->groupBy('pregunta_id');
        $dimPorOpcion = DB::table('opcion_dimension')->pluck('dimension_id', 'opcion_id');
        $codigoPorDimId = DB::table('dimensiones')->where('prueba_id', $asignacion->prueba_id)->pluck('codigo', 'id');

        // Mismo truco que en PostulanteResponderPruebaTest: elegir siempre el lado
        // preferido de cada par (E, S, T, J) basta para que ESTJ gane sin empates.
        $preferidas = ['E', 'S', 'T', 'J'];
        $respuestas = [];
        foreach ($preguntas as $pregunta) {
            foreach ($opciones->get($pregunta->id) as $opcion) {
                if (in_array($codigoPorDimId[$dimPorOpcion[$opcion->id]], $preferidas, true)) {
                    $respuestas[$pregunta->id] = $opcion->id;
                    break;
                }
            }
        }

        $this->actingAs($postulante)->get(route('postulante.prueba.show', $asignacion))->assertOk();
        $this->actingAs($postulante)
            ->post(route('postulante.prueba.guardar', $asignacion), ['respuestas' => $respuestas])
            ->assertRedirect(route('postulante.index'));

        $interpretacionEsperada = DB::table('perfiles')
            ->where('prueba_id', $asignacion->prueba_id)
            ->where('codigo', 'ESTJ')
            ->value('texto_interpretacion');

        $this->actingAs($talentoHumano)
            ->get(route('talento-humano.show', $postulacion))
            ->assertOk()
            ->assertSee('ESTJ')
            ->assertSee($interpretacionEsperada);
    }

    public function test_th_ve_el_puntaje_directo_por_area_de_barsit_que_no_tiene_normas_cargadas(): void
    {
        $this->seed(BarsitSeeder::class);
        [$postulante, $talentoHumano, $postulacion, $asignacion] = $this->crearAsignacionAsignada('BARSIT');

        $preguntas = DB::table('preguntas')->where('prueba_id', $asignacion->prueba_id)->get();
        $opciones = DB::table('opciones')->whereIn('pregunta_id', $preguntas->pluck('id'))->get()->groupBy('pregunta_id');
        $puntosPorOpcion = DB::table('opcion_dimension')->pluck('puntos', 'opcion_id');

        // Todas correctas (60/60): no interesa el puntaje exacto para este test,
        // solo que el informe muestre el puntaje directo en vez de "sin rango".
        $respuestas = [];
        foreach ($preguntas as $pregunta) {
            foreach ($opciones->get($pregunta->id) as $opcion) {
                if ((int) $puntosPorOpcion[$opcion->id] === 1) {
                    $respuestas[$pregunta->id] = $opcion->id;
                    break;
                }
            }
        }

        $this->actingAs($postulante)->get(route('postulante.prueba.show', $asignacion))->assertOk();
        $this->actingAs($postulante)
            ->post(route('postulante.prueba.guardar', $asignacion), ['respuestas' => $respuestas])
            ->assertRedirect(route('postulante.index'));

        $this->actingAs($talentoHumano)
            ->get(route('talento-humano.show', $postulacion))
            ->assertOk()
            ->assertSee('Sin normas de referencia cargadas')
            ->assertSee('Información') // nombre de una de las dimensiones de BARSIT
            ->assertSee('12'); // puntaje máximo de cada área (todas correctas)
    }
}
