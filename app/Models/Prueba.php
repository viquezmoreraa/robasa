<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Prueba extends Model
{
    protected $table = 'pruebas';

    public $timestamps = false;

    /** Tarea de video que se asigna sola al pasar a PRESELECCIONADO (se entrega subiendo el video). */
    public const VIDEO_EXPERIENCIA = 'VIDEO_EXPERIENCIA';

    public function esVideo(): bool
    {
        return $this->codigo === self::VIDEO_EXPERIENCIA;
    }

    /**
     * true si la prueba ya tiene preguntas/opciones digitalizadas en la
     * base de datos (p. ej. Keirsey, BARSIT, E.D.) y por tanto puede
     * responderse en línea a través de MotorPsicometrico; false si solo
     * se completa subiendo un archivo (p. ej. CONOCIMIENTOS, CASO_PRACTICO).
     * No se guarda como columna aparte: se deriva de los datos, como el
     * resto del motor.
     */
    public function tienePreguntas(): bool
    {
        return DB::table('preguntas')->where('prueba_id', $this->id)->exists();
    }
}
