<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsignacionPrueba extends Model
{
    protected $table = 'asignaciones_prueba';

    public $timestamps = false;

    protected $fillable = [
        'postulacion_id', 'prueba_id', 'intento', 'asignada_por', 'fecha_asignacion',
        'fecha_fin', 'estado',
    ];

    protected function casts(): array
    {
        return ['fecha_asignacion' => 'datetime', 'fecha_fin' => 'datetime'];
    }

    public function prueba(): BelongsTo
    {
        return $this->belongsTo(Prueba::class);
    }

    public function postulacion(): BelongsTo
    {
        return $this->belongsTo(Postulacion::class);
    }

    public function completada(): bool
    {
        return $this->estado === 'COMPLETADA';
    }
}
