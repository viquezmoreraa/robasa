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

    protected $attributes = ['estado' => 'POSTULADO'];

    /** Estados del proceso de selección (los asigna Talento Humano): clave => [etiqueta, descripción]. */
    public const ESTADOS = [
        'POSTULADO'       => ['Postulado', 'El candidato completó y envió la postulación.'],
        'EN_REVISION'     => ['En revisión', 'Talento Humano está revisando la información.'],
        'PRESELECCIONADO' => ['Preseleccionado', 'El candidato continúa a la siguiente etapa.'],
        'ENTREVISTA'      => ['Entrevista', 'Se encuentra en proceso de entrevista.'],
        'PRUEBAS'         => ['Pruebas', 'Tiene evaluaciones psicométricas u otras pruebas asignadas.'],
        'REFERENCIAS'     => ['Referencias', 'Se encuentra en validación de referencias, cuando corresponda.'],
        'OFERTA'          => ['Oferta', 'Se encuentra en etapa de propuesta/oferta.'],
        'CONTRATADO'      => ['Contratado', 'Finalizó satisfactoriamente el proceso.'],
        'NO_SELECCIONADO' => ['No seleccionado', 'No continúa en el proceso.'],
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

    /**
     * Asigna la tarea del video de experiencia, salvo que ya tenga una vigente (no se duplica
     * si Talento Humano mueve el estado y lo vuelve a poner en PRESELECCIONADO). Devuelve si la asignó.
     */
    public function asignarVideoExperiencia(User $asignadaPor): bool
    {
        $prueba = Prueba::where('codigo', Prueba::VIDEO_EXPERIENCIA)->where('estado', 'APROBADA')->first();
        if (!$prueba) {
            return false;
        }

        $asignaciones = $this->asignaciones()->where('prueba_id', $prueba->id);
        if ((clone $asignaciones)->where('estado', '!=', 'ANULADA')->exists()) {
            return false;
        }

        $this->asignaciones()->create([
            'prueba_id'        => $prueba->id,
            'intento'          => ($asignaciones->max('intento') ?? 0) + 1,
            'asignada_por'     => $asignadaPor->id,
            'fecha_asignacion' => now(),
            'estado'           => 'ASIGNADA',
        ]);

        return true;
    }

    public function nombreCompleto(): string
    {
        $nombre = trim($this->nombre ?? '');

        return $nombre !== '' ? $nombre : $this->correo;
    }

    public function estadoEtiqueta(): string
    {
        return self::ESTADOS[$this->estado][0] ?? (string) $this->estado;
    }

    public function estadoDescripcion(): string
    {
        return self::ESTADOS[$this->estado][1] ?? '';
    }

    /** Clase del badge según el estado: verde si fue contratado, rojo si no sigue, naranja mientras avanza. */
    public function estadoClase(): string
    {
        return match ($this->estado) {
            'CONTRATADO'      => 'badge-ok',
            'NO_SELECCIONADO' => 'badge-no',
            default           => 'badge-pend',
        };
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
