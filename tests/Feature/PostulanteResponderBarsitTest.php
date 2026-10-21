<?php

namespace Tests\Feature;

use App\Models\AsignacionPrueba;
use App\Models\Postulacion;
use App\Models\User;
use Database\Seeders\BarsitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PostulanteResponderBarsitTest extends TestCase
{
    use RefreshDatabase;

    /** Mismos ítems que VerificarBarsit responde mal a propósito: da 57/60 = 95%. */
    private const RESPUESTAS_INCORRECTAS = [36, 51, 53];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(BarsitSeeder::class);
    }

    private function crearAsignacionAsignada(): array
    {
        $postulante = User::factory()->create(['rol' => 'postulante']);
        $talentoHumano = User::factory()->create(['rol' => 'talento_humano']);
        $postulacion = Postulacion::create([
            'user_id' => $postulante->id,
            'nombre'  => 'Ana Pérez',
            'correo'  => $postulante->email,
        ]);
        $pruebaId = DB::table('pruebas')->where('codigo', 'BARSIT')->value('id');

        $asignacion = AsignacionPrueba::create([
            'postulacion_id' => $postulacion->id,
            'prueba_id'      => $pruebaId,
            'asignada_por'   => $talentoHumano->id,
            'estado'         => 'ASIGNADA',
        ]);

        return [$postulante, $asignacion];
    }

    public function test_el_postulante_responde_barsit_en_linea_y_reproduce_el_caso_oficial_del_excel(): void
    {
        [$postulante, $asignacion] = $this->crearAsignacionAsignada();

        $preguntas = DB::table('preguntas')->where('prueba_id', $asignacion->prueba_id)->orderBy('numero')->get();
        $opcionesPorPregunta = DB::table('opciones')
            ->whereIn('pregunta_id', $preguntas->pluck('id'))
            ->get()
            ->groupBy('pregunta_id');
        $puntosPorOpcion = DB::table('opcion_dimension')->pluck('puntos', 'opcion_id');

        $respuestas = [];
        foreach ($preguntas as $pregunta) {
            $esIncorrecta = in_array($pregunta->numero, self::RESPUESTAS_INCORRECTAS, true);
            foreach ($opcionesPorPregunta->get($pregunta->id) as $opcion) {
                $puntos = (int) $puntosPorOpcion[$opcion->id];
                if (($esIncorrecta && $puntos === 0) || (!$esIncorrecta && $puntos === 1)) {
                    $respuestas[$pregunta->id] = $opcion->id;
                    break;
                }
            }
        }
        $this->assertCount(60, $respuestas);

        // Necesario: pasa la asignación de ASIGNADA a EN_CURSO (calcular() exige ese estado).
        $this->actingAs($postulante)->get(route('postulante.prueba.show', $asignacion))->assertOk();

        $this->actingAs($postulante)
            ->post(route('postulante.prueba.guardar', $asignacion), ['respuestas' => $respuestas])
            ->assertRedirect(route('postulante.index'));

        $this->assertSame('COMPLETADA', $asignacion->fresh()->estado);

        $esperado = ['INFO' => 10, 'VOCAB' => 12, 'VERBAL' => 11, 'LOGICO' => 12, 'NUM' => 12];
        foreach ($esperado as $codigo => $puntosEsperados) {
            $puntajeReal = DB::table('resultados_dimension')
                ->join('dimensiones', 'dimensiones.id', '=', 'resultados_dimension.dimension_id')
                ->where('resultados_dimension.asignacion_id', $asignacion->id)
                ->where('dimensiones.codigo', $codigo)
                ->value('puntaje_bruto');

            $this->assertSame((float) $puntosEsperados, (float) $puntajeReal, "área {$codigo}");
        }
    }

    public function test_no_deja_entregar_si_falta_una_pregunta_obligatoria(): void
    {
        [$postulante, $asignacion] = $this->crearAsignacionAsignada();

        $this->actingAs($postulante)
            ->post(route('postulante.prueba.guardar', $asignacion), ['respuestas' => []])
            ->assertSessionHasErrors('general');

        $this->assertSame('ASIGNADA', $asignacion->fresh()->estado);
        $this->assertDatabaseMissing('resultados_prueba', ['asignacion_id' => $asignacion->id]);
    }

    public function test_otro_postulante_no_puede_responder_una_asignacion_que_no_es_suya(): void
    {
        [, $asignacion] = $this->crearAsignacionAsignada();
        $otro = User::factory()->create(['rol' => 'postulante']);
        Postulacion::create(['user_id' => $otro->id, 'nombre' => 'Otro', 'correo' => $otro->email]);

        $this->actingAs($otro)
            ->get(route('postulante.prueba.show', $asignacion))
            ->assertForbidden();
    }
}
