<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('conteos_membresia', function (Blueprint $table) {
            $table->string('fuente_datos')->nullable()->after('es_sintetico');
            $table->string('version_datos')->nullable()->after('fuente_datos');
            $table->string('archivo_origen')->nullable()->after('version_datos');
            $table->string('hash_archivo', 64)->nullable()->after('archivo_origen');
            $table->text('observaciones_calidad')->nullable()->after('hash_archivo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conteos_membresia', function (Blueprint $table) {
            $table->dropColumn([
                'fuente_datos',
                'version_datos',
                'archivo_origen',
                'hash_archivo',
                'observaciones_calidad',
            ]);
        });
    }
};
