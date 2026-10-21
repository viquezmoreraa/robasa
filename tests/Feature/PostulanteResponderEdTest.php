<?php

namespace Tests\Feature;

use App\Models\AsignacionPrueba;
use App\Models\Postulacion;
use App\Models\User;
use Database\Seeders\EdSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PostulanteResponderEdTest extends TestCase
{
    use RefreshDatabase;

    /** Respuestas reales de la hoja "Prueba" del Excel oficial, mismas que VerificarEd. */
    private const RESPUESTAS = [
        1 => 'B', 2 => 'A', 3 => 'B', 4 => 'A', 5 => 'A', 6 => 'B', 7 => 'B', 8 => 'B', 9 => 'A', 10 => 'B',
        11 => 'A', 12 => 'B', 13 => 'B', 14 => 'A', 15 => 'B', 16 => 'A', 17 => 'A', 18 => 'A', 19 => 'B', 20 => 'B',
        21 => 'B', 22 => 'A', 23 => 'A', 24 => 'A', 25 => 'A', 26 => 'B', 27 => 'A', 28 => 'A', 29 => 'B', 30 => 'B',
        31 => 'A', 32 => 'B', 33 => 'B', 34 => 'A', 35 => 'A', 36 => 'A',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(EdSeeder::class);
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
        $pruebaId = DB::table('pruebas')->where('codigo', 'ED')->value('id');

        $asignacion = AsignacionPrueba::create([
            'postulacion_id' => $postulacion->id,
            'prueba_id'      => $pruebaId,
            'asignada_por'   => $talentoHumano->id,
            'estado'         => 'ASIGNADA',
        ]);

        return [$postulante, $asignacion];
    }

    public function test_el_postulante_responde_ed_en_linea_y_reproduce_el_caso_real_del_excel(): void
    {
        [$postulante, $asignacion] = $this->crearAsignacionAsignada();

        $preguntas = DB::table('preguntas')->where('prueba_id', $asignacion->prueba_id)->orderBy('numero')->get();
        $opcionesPorPregunta = DB::table('opciones')
            ->whereIn('pregunta_id', $preguntas->pluck('id'))
            ->get()
            ->groupBy('pregunta_id');

        $respuestas = [];
        foreach ($preguntas as $pregunta) {
            $letra = self::RESPUESTAS[$pregunta->numero] ?? null;
            if ($letra === null) {
                continue;
            }
            // orden 1 = opción A, orden 2 = opción B (igual que el seeder las insertó).
            $ordenBuscado = $letra === 'A' ? 1 : 2;
            $opcion = $opcionesPorPregunta->get($pregunta->id)->firstWhere('orden', $ordenBuscado);
            $respuestas[$pregunta->id] = $opcion->id;
        }
        $this->assertCount(36, $respuestas);

        // Necesario: pasa la asignación de ASIGNADA a EN_CURSO (calcular() exige ese estado).
        $this->actingAs($postulante)->get(route('postulante.prueba.show', $asignacion))->assertOk();

        $this->actingAs($postulante)
            ->post(route('postulante.prueba.guardar', $asignacion), ['respuestas' => $respuestas])
            ->assertRedirect(route('postulante.index'));

        $this->assertSame('COMPLETADA', $asignacion->fresh()->estado);

        $esperado = ['AUT' => 9, 'RES' => 2, 'DEM' => 7, 'AFI' => 7, 'FOR' => 9, 'DEL' => 2];
        foreach ($esperado as $codigo => $puntosEsperados) {
            $puntajeReal = DB::table('resultados_dimension')
                ->join('dimensiones', 'dimensiones.id', '=', 'resultados_dimension.dimension_id')
                ->where('resultados_dimension.asignacion_id', $asignacion->id)
                ->where('dimensiones.codigo', $codigo)
                ->value('puntaje_bruto');

            $this->assertSame((float) $puntosEsperados, (float) $puntajeReal, "categoría {$codigo}");
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
