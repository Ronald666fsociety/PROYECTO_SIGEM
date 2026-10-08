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
        if (Schema::hasTable('conteos_membresia') && ! Schema::hasColumn('conteos_membresia', 'es_sintetico')) {
            Schema::table('conteos_membresia', function (Blueprint $table) {
                $table->boolean('es_sintetico')->default(false)->after('estado');
            });
        }

        if (Schema::hasTable('predicciones') && ! Schema::hasColumn('predicciones', 'modo_datos')) {
            Schema::table('predicciones', function (Blueprint $table) {
                $table->string('modo_datos', 30)->default('evaluacion_real')->after('estado');
                $table->string('mejor_metodo', 50)->nullable()->after('modo_datos');
                $table->decimal('mae_suavizamiento_simple', 12, 4)->nullable()->after('rmse_linea_base');
                $table->decimal('rmse_suavizamiento_simple', 12, 4)->nullable()->after('mae_suavizamiento_simple');
                $table->decimal('mae_regresion_lineal', 12, 4)->nullable()->after('rmse_suavizamiento_simple');
                $table->decimal('rmse_regresion_lineal', 12, 4)->nullable()->after('mae_regresion_lineal');
                $table->json('resultados_validacion')->nullable()->after('observaciones');
                $table->date('fecha_corte_datos')->nullable()->after('resultados_validacion');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('conteos_membresia') && Schema::hasColumn('conteos_membresia', 'es_sintetico')) {
            Schema::table('conteos_membresia', function (Blueprint $table) {
                $table->dropColumn('es_sintetico');
            });
        }

        if (Schema::hasTable('predicciones') && Schema::hasColumn('predicciones', 'modo_datos')) {
            Schema::table('predicciones', function (Blueprint $table) {
                $table->dropColumn([
                    'modo_datos',
                    'mejor_metodo',
                    'mae_suavizamiento_simple',
                    'rmse_suavizamiento_simple',
                    'mae_regresion_lineal',
                    'rmse_regresion_lineal',
                    'resultados_validacion',
                    'fecha_corte_datos',
                ]);
            });
        }
    }
};
