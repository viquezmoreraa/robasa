<?php

namespace Tests\Feature;

use App\Models\AsignacionPrueba;
use App\Models\Postulacion;
use App\Models\User;
use Database\Seeders\KeirseySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PostulanteResponderPruebaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Siembra solo lo que necesita esta clase (no $this->seed() general):
        // cubre, de paso, que la migración de DICOTOMIA_COMBINADA (ALTER ENUM)
        // y MotorPsicometrico::calcular() realmente corran bajo SQLite.
        $this->seed(KeirseySeeder::class);
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
        $pruebaId = DB::table('pruebas')->where('codigo', 'KEIRSEY')->value('id');

        $asignacion = AsignacionPrueba::create([
            'postulacion_id' => $postulacion->id,
            'prueba_id'      => $pruebaId,
            'asignada_por'   => $talentoHumano->id,
            'estado'         => 'ASIGNADA',
        ]);

        return [$postulante, $asignacion];
    }

    public function test_el_postulante_responde_keirsey_en_linea_y_el_motor_calcula_el_resultado(): void
    {
        [$postulante, $asignacion] = $this->crearAsignacionAsignada();

        // Ver la prueba muestra las preguntas y la pasa a EN_CURSO.
        $this->actingAs($postulante)
            ->get(route('postulante.prueba.show', $asignacion))
            ->assertOk()
            ->assertSee('Keirsey');
        $this->assertSame('EN_CURSO', $asignacion->fresh()->estado);

        // Responde siempre el lado "preferido" de cada par (E, S, T, J): como cada
        // ítem de Keirsey es A/B entre las dos letras de UN solo par, esto basta
        // para que esas 4 letras queden estrictamente por encima de su opuesta,
        // sin tener que adivinar el puntaje exacto de cada ítem.
        $preguntas = DB::table('preguntas')->where('prueba_id', $asignacion->prueba_id)->orderBy('numero')->get();
        $opcionesPorPregunta = DB::table('opciones')
            ->whereIn('pregunta_id', $preguntas->pluck('id'))
            ->get()
            ->groupBy('pregunta_id');
        $dimensionPorOpcion = DB::table('opcion_dimension')->pluck('dimension_id', 'opcion_id');
        $codigoPorDimensionId = DB::table('dimensiones')
            ->where('prueba_id', $asignacion->prueba_id)
            ->pluck('codigo', 'id');

        $preferidas = ['E', 'S', 'T', 'J'];
        $respuestas = [];
        foreach ($preguntas as $pregunta) {
            foreach ($opcionesPorPregunta->get($pregunta->id) as $opcion) {
                $letra = $codigoPorDimensionId[$dimensionPorOpcion[$opcion->id]];
                if (in_array($letra, $preferidas, true)) {
                    $respuestas[$pregunta->id] = $opcion->id;
                    break;
                }
            }
        }
        $this->assertCount($preguntas->count(), $respuestas, 'cada pregunta debe tener una opción preferida');

        $this->actingAs($postulante)
            ->post(route('postulante.prueba.guardar', $asignacion), ['respuestas' => $respuestas])
            ->assertRedirect(route('postulante.index'));

        $this->assertSame('COMPLETADA', $asignacion->fresh()->estado);

        $resultado = DB::table('resultados_prueba')->where('asignacion_id', $asignacion->id)->first();
        $this->assertNotNull($resultado);
        $this->assertSame('ESTJ', $resultado->resumen);
        $this->assertNotNull($resultado->perfil_id, 'ESTJ debe existir entre los 16 perfiles sembrados');

        $puntajeE = DB::table('resultados_dimension')
            ->join('dimensiones', 'dimensiones.id', '=', 'resultados_dimension.dimension_id')
            ->where('resultados_dimension.asignacion_id', $asignacion->id)
            ->where('dimensiones.codigo', 'E')
            ->value('puntaje_bruto');
        $this->assertGreaterThan(0, (float) $puntajeE);
    }

    public function test_no_deja_entregar_si_falta_una_pregunta_obligatoria(): void
    {
        [$postulante, $asignacion] = $this->crearAsignacionAsignada();

        $this->actingAs($postulante)
            ->post(route('postulante.prueba.guardar', $asignacion), ['respuestas' => []])
            ->assertSessionHasErrors('general');

        // No quedó completada ni se guardó ningún resultado a medias.
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
