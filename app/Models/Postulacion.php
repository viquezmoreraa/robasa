<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Postulacion extends Model
{
    protected $table = 'postulaciones';

    protected $fillable = ['user_id', 'puesto', 'correo', 'datos'];

    protected function casts(): array
    {
        return ['datos' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(AsignacionPrueba::class, 'postulacion_id');
    }

    public function nombreCompleto(): string
    {
        $d = $this->datos ?? [];
        $nombre = trim(($d['nombres'] ?? $d['nombre'] ?? '').' '.($d['apellidos'] ?? $d['apellido'] ?? ''));

        return $nombre !== '' ? $nombre : $this->correo;
    }
}
