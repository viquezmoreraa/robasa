<?php

use App\Models\Prueba;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
 * Tarea que se asigna sola cuando Talento Humano pasa al postulante a PRESELECCIONADO:
 * subir un video de máximo 2 minutos hablando de su experiencia laboral. Va en una migración
 * (no en el seeder) porque el cambio de estado depende de que la fila exista.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('pruebas')->updateOrInsert(
            ['codigo' => Prueba::VIDEO_EXPERIENCIA, 'version' => '1'],
            [
                'nombre'           => 'Video de experiencia laboral',
                'instrucciones'    => "Grabe un video de máximo 2 minutos donde se presente y hable de su experiencia laboral: empresas, puestos, funciones principales y logros.\nPuede grabarlo con el celular. Formatos MP4, MOV o WEBM, máximo 35 MB.",
                'metodo_resultado' => 'TOTAL_CORRECTAS',
                'estado'           => 'APROBADA',
                'aprobada_por'     => 'Talento Humano',
            ]
        );
    }

    public function down(): void
    {
        $id = DB::table('pruebas')->where('codigo', Prueba::VIDEO_EXPERIENCIA)->value('id');
        DB::table('asignaciones_prueba')->where('prueba_id', $id)->delete();
        DB::table('pruebas')->where('id', $id)->delete();
    }
};
