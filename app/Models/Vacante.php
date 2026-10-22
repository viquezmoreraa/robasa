<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vacante extends Model
{
    protected $table = 'vacantes';

    protected $fillable = ['puesto', 'nombre', 'cantidad'];

    protected function casts(): array
    {
        return ['cantidad' => 'integer'];
    }

    /** true si el puesto existe y ya no tiene plazas (un puesto desconocido no se bloquea). */
    public static function sinPlazas(?string $puesto): bool
    {
        return $puesto !== null && static::where('puesto', $puesto)->where('cantidad', 0)->exists();
    }
}
