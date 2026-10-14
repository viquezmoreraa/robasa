<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dimensiones', function (Blueprint $table) {
            // Una subescala apunta a su dimensión principal (ej.: Dinamismo -> Energía)
            $table->foreignId('dimension_padre_id')->nullable()->after('prueba_id')
                ->constrained('dimensiones');
        });
    }

    public function down(): void
    {
        Schema::table('dimensiones', function (Blueprint $table) {
            $table->dropForeign(['dimension_padre_id']);
            $table->dropColumn('dimension_padre_id');
        });
    }
};
