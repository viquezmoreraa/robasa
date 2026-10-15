<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Carga el DISC (28 grupos de palabras, forzado MAS/MENOS) desde
 * database/seeders/data/disc.json: dimensiones D/I/S/C, 28 preguntas con sus
 * 4 opciones, la matriz de puntuación, los 15 patrones clásicos con su
 * interpretación, y la tabla de clasificación (código de 4 posiciones ->
 * patrón) para los 2401 códigos posibles.
 *
 * Todo el contenido proviene del Excel entregado por Talento Humano. Los
 * códigos que ese material deja sin patrón numerado (ver 'observaciones' en
 * el JSON) se cargan con perfil_id = NULL: el sistema los señala en vez de
 * inventar un resultado.
 *
 * Uso: php artisan db:seed --class=DiscSeeder
 */
class DiscSeeder extends Seeder
{
    private const CATEGORIAS = [
        'E'  => 'Emociones',
        'M'  => 'Meta',
        'J'  => 'Juzga a los demás por',
        'I'  => 'Influye en los demás mediante',
        'S'  => 'Su valor para la organización',
        'A'  => 'Abusa de',
        'B'  => 'Bajo presión',
        'T'  => 'Teme',
        'SE' => 'Sería más eficaz si',
    ];

    public function run(): void
    {
        $datos = json_decode(
            file_get_contents(database_path('seeders/data/disc.json')),
            true, 512, JSON_THROW_ON_ERROR
        );
        $p = $datos['prueba'];

        if (DB::table('pruebas')->where('codigo', $p['codigo'])->where('version', $p['version'])->exists()) {
            $this->command?->warn("La prueba {$p['codigo']} (versión {$p['version']}) ya está cargada. No se hizo nada.");
            return;
        }

        DB::transaction(function () use ($datos, $p) {
            $pruebaId = DB::table('pruebas')->insertGetId([
                'codigo'           => $p['codigo'],
                'version'          => $p['version'],
                'nombre'           => $p['nombre'],
                'instrucciones'    => $p['instrucciones'],
                'metodo_resultado' => $p['metodo_resultado'],
                'minutos_limite'   => null,
                'permite_reanudar' => true,
                'estado'           => 'APROBADA',
                'aprobada_por'     => 'Talento Humano',
                'fecha_aprobacion' => null,
            ]);

            // Dimensiones D, I, S, C (sin jerarquía: a diferencia del Big Five, el
            // DISC no tiene subescalas).
            $dimIds = [];
            foreach (['D', 'I', 'S', 'C'] as $i => $codigo) {
                $dimIds[$codigo] = DB::table('dimensiones')->insertGetId([
                    'prueba_id' => $pruebaId,
                    'codigo'    => $codigo,
                    'nombre'    => $codigo,
                    'orden'     => $i + 1,
                ]);
            }

            // 28 preguntas tipo MAS_MENOS, cada una con sus 4 palabras como opciones.
            // Cada opción suma 1 punto a la dimensión de su letra; el MOTOR decide el
            // signo (+1 si se marcó como MAS, -1 si se marcó como MENOS) según
            // 'respuestas.seleccion', no esta matriz.
            foreach ($datos['grupos'] as $grupo => $items) {
                $preguntaId = DB::table('preguntas')->insertGetId([
                    'prueba_id'   => $pruebaId,
                    'numero'      => (int) $grupo,
                    'enunciado'   => "Grupo {$grupo}: elija la palabra que más y la que menos lo(a) representa.",
                    'tipo'        => 'MAS_MENOS',
                    'obligatoria' => true,
                ]);

                foreach ($items as $item) {
                    $opcionId = DB::table('opciones')->insertGetId([
                        'pregunta_id' => $preguntaId,
                        'orden'       => $item['orden'],
                        'texto'       => $item['palabra'],
                    ]);

                    DB::table('opcion_dimension')->insert([
                        'opcion_id'    => $opcionId,
                        'dimension_id' => $dimIds[$item['letra']],
                        'puntos'       => 1,
                    ]);
                }
            }

            // Los 15 patrones clásicos, con su interpretación completa armada a
            // partir de las categorías del material original.
            $perfilIdPorNumero = [];
            foreach ($datos['patrones'] as $numero => $patron) {
                $texto = "Patrón: {$patron['nombre']}\n\n";
                foreach (self::CATEGORIAS as $clave => $etiqueta) {
                    if (!empty($patron[$clave])) {
                        $texto .= "{$etiqueta}: {$patron[$clave]}\n\n";
                    }
                }
                if (!empty($patron['overview'])) {
                    $texto .= "Descripción general:\n" . implode("\n\n", $patron['overview']);
                }

                $perfilIdPorNumero[$numero] = DB::table('perfiles')->insertGetId([
                    'prueba_id'            => $pruebaId,
                    'codigo'               => (string) $numero,
                    'nombre'               => $patron['nombre'],
                    'texto_interpretacion' => trim($texto),
                ]);
            }

            // Tabla de clasificación: código de 4 posiciones -> perfil (o NULL si el
            // material original no le asignó número a ese patrón).
            $filas = [];
            foreach ($datos['clasificacion'] as $codigo => $numeroPatron) {
                $filas[] = [
                    'prueba_id' => $pruebaId,
                    'codigo'    => $codigo,
                    'perfil_id' => $numeroPatron !== null ? $perfilIdPorNumero[$numeroPatron] : null,
                ];
            }
            foreach (array_chunk($filas, 500) as $lote) {
                DB::table('disc_clasificacion')->insert($lote);
            }
        });

        $sinPerfil = collect($datos['clasificacion'])->filter(fn ($v) => $v === null)->count();
        $this->command?->info('DISC cargado: 28 preguntas, 4 dimensiones, 15 patrones y 2401 códigos de clasificación.');
        $this->command?->warn("{$sinPerfil} de esos códigos quedaron sin perfil asignado (ver observaciones del material original).");
    }
}
