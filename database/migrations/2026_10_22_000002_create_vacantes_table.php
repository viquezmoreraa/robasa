<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Cantidad de plazas abiertas por puesto, que Talento Humano actualiza desde su panel.
 * La descripción y los requisitos siguen en public/vacantes.html; 'puesto' es la misma clave
 * que viaja en ?puesto= y se guarda en postulaciones.puesto.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vacantes', function (Blueprint $table) {
            $table->id();
            $table->string('puesto', 60)->unique();
            $table->string('nombre', 100);
            $table->unsignedSmallInteger('cantidad')->default(1);   // 0 = sin plazas: no se aceptan postulaciones
            $table->timestamps();
        });

        // Todas arrancan con 1 plaza para que la página siga igual hasta que Talento Humano las ajuste
        $puestos = [
            'chofer'               => 'Chofer',
            'promotor'             => 'Promotor - mercaderista',
            'encargado_ruta'       => 'Encargado de ruta',
            'almacen'              => 'Almacén',
            'ayudante_carga'       => 'Ayudante de carga',
            'agente_ventas'        => 'Agente de ventas',
            'agente_ventas_junior' => 'Agente de ventas junior',
            'agente_mayorista'     => 'Agente de ventas mayorista',
            'supervisor_ventas'    => 'Supervisor de ventas',
            'cuentas_cobrar'       => 'Cuentas por cobrar',
            'facturacion'          => 'Facturación',
            'director_operaciones' => 'Director de operaciones',
            'analista_datos'       => 'Analista de datos',
            'proveduria_jr'        => 'Encargado de proveeduría jr',
        ];

        DB::table('vacantes')->insert(array_map(
            fn ($puesto, $nombre) => ['puesto' => $puesto, 'nombre' => $nombre, 'cantidad' => 1, 'created_at' => now(), 'updated_at' => now()],
            array_keys($puestos),
            $puestos
        ));
    }

    public function down(): void
    {
        Schema::dropIfExists('vacantes');
    }
};
