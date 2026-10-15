<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Postulacion extends Model
{
    protected $table = 'postulaciones';

    protected $fillable = [
        'user_id', 'acepto_terminos_at',
        'nombre', 'cedula', 'fecha_nacimiento', 'telefono', 'correo_contacto', 'direccion',
        'tiene_licencia', 'tipo_licencia', 'tiene_vehiculo',
        'puesto', 'salario', 'zona', 'disponibilidad', 'tiene_experiencia', 'detalles_experiencia',
        'nivel_educativo', 'empleos_anteriores', 'correo',
    ];

    /** Etiqueta => columna, para mostrar la postulación en las vistas. */
    public const CAMPOS = [
        'Nombre'               => 'nombre',
        'Cédula'               => 'cedula',
        'Fecha de nacimiento'  => 'fecha_nacimiento',
        'Teléfono'             => 'telefono',
        'Correo de contacto'   => 'correo_contacto',
        'Dirección'            => 'direccion',
        'Licencia'             => 'tiene_licencia',
        'Tipo de licencia'     => 'tipo_licencia',
        'Vehículo propio'      => 'tiene_vehiculo',
        'Salario esperado'     => 'salario',
        'Zona'                 => 'zona',
        'Disponibilidad'       => 'disponibilidad',
        'Experiencia'          => 'tiene_experiencia',
        'Detalle experiencia'  => 'detalles_experiencia',
        'Nivel educativo'      => 'nivel_educativo',
        'Empleos anteriores'   => 'empleos_anteriores',
    ];

    protected function casts(): array
    {
        return [
            'acepto_terminos_at' => 'datetime',
            'fecha_nacimiento'   => 'date',
            'tiene_licencia'     => 'boolean',
            'tiene_experiencia'  => 'boolean',
        ];
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
        $nombre = trim($this->nombre ?? '');

        return $nombre !== '' ? $nombre : $this->correo;
    }

    /** Campos del formulario ya formateados para mostrar: etiqueta => texto. */
    public function camposFormulario(): array
    {
        $campos = [];
        foreach (self::CAMPOS as $etiqueta => $columna) {
            $valor = $this->{$columna};
            $campos[$etiqueta] = match (true) {
                is_bool($valor)                     => $valor ? 'Sí' : 'No',
                $valor instanceof \DateTimeInterface => $valor->format('d/m/Y'),
                default                             => $valor,
            };
        }

        return $campos;
    }
}
