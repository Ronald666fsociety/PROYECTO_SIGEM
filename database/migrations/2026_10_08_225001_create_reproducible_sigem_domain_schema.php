<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const KEY = '2026_10_08_225001_create_reproducible_sigem_domain_schema';

    public function up(): void
    {
        $markerCreated = ! Schema::hasTable('sigem_schema_installations');
        if ($markerCreated) {
            Schema::create('sigem_schema_installations', function (Blueprint $table) {
                $table->string('migration')->primary();
                $table->json('created_tables');
                $table->json('added_user_columns');
                $table->json('added_member_columns');
                $table->boolean('marker_created')->default(false);
            });
        }

        $createdTables = [];

        if (! Schema::hasTable('circuitos')) {
            Schema::create('circuitos', function (Blueprint $table) {
                $table->id();
                $table->string('nombre');
                $table->string('codigo')->unique();
                $table->text('descripcion')->nullable();
                $table->string('responsable_nombre')->nullable();
                $table->string('telefono')->nullable();
                $table->enum('estado', ['activo', 'inactivo'])->default('activo');
                $table->timestamps();
            });
            $createdTables[] = 'circuitos';
        }

        if (! Schema::hasTable('iglesias')) {
            Schema::create('iglesias', function (Blueprint $table) {
                $table->id();
                $table->foreignId('circuito_id')->constrained('circuitos')->restrictOnDelete();
                $table->string('nombre');
                $table->string('codigo')->unique();
                $table->string('direccion')->nullable();
                $table->string('localidad')->nullable();
                $table->string('telefono')->nullable();
                $table->date('fecha_fundacion')->nullable();
                $table->string('pastor_nombre')->nullable();
                $table->enum('estado', ['activo', 'inactivo'])->default('activo');
                $table->timestamps();
            });
            $createdTables[] = 'iglesias';
        }

        $addedUserColumns = $this->addUserColumns();

        if (! Schema::hasTable('miembros')) {
            Schema::create('miembros', function (Blueprint $table) {
                $table->id();
                $table->foreignId('iglesia_id')->constrained('iglesias')->restrictOnDelete();
                $table->string('nombres');
                $table->string('apellidos');
                $table->string('ci')->nullable();
                $table->date('fecha_nacimiento')->nullable();
                $table->enum('genero', ['masculino', 'femenino'])->nullable();
                $table->string('telefono')->nullable();
                $table->string('email')->nullable();
                $table->string('direccion')->nullable();
                $table->enum('categoria', ['miembro_pleno', 'miembro_preparatorio', 'simpatizante', 'nino'])->default('miembro_pleno');
                $table->enum('estado', ['activo', 'inactivo', 'transferido', 'fallecido'])->default('activo');
                $table->date('fecha_ingreso')->nullable();
                $table->date('fecha_registro')->nullable();
                $table->date('fecha_bautismo')->nullable();
                $table->date('fecha_baja')->nullable();
                $table->string('motivo_baja')->nullable();
                $table->text('observaciones')->nullable();
                $table->timestamps();
            });
            $createdTables[] = 'miembros';
            $addedMemberColumns = [];
        } else {
            $addedMemberColumns = $this->addMemberCompatibilityColumns();
        }

        if (! Schema::hasTable('conteos_membresia')) {
            Schema::create('conteos_membresia', function (Blueprint $table) {
                $table->id();
                $table->foreignId('iglesia_id')->constrained('iglesias')->restrictOnDelete();
                $table->unsignedInteger('anio');
                $table->unsignedTinyInteger('mes');
                $table->unsignedInteger('total_activos')->default(0);
                $table->unsignedInteger('total_inactivos')->default(0);
                $table->unsignedInteger('total_nuevos')->default(0);
                $table->unsignedInteger('total_transferidos')->default(0);
                $table->unsignedInteger('total_bajas')->default(0);
                $table->date('fecha_corte');
                $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
                $table->enum('estado', ['borrador', 'validado', 'cerrado'])->default('borrador');
                $table->boolean('es_sintetico')->default(false);
                $table->timestamps();
                $table->unique(['iglesia_id', 'anio', 'mes']);
            });
            $createdTables[] = 'conteos_membresia';
        }

        if (! Schema::hasTable('predicciones')) {
            Schema::create('predicciones', function (Blueprint $table) {
                $table->id();
                $table->dateTime('fecha_ejecucion')->useCurrent();
                $table->string('modelo')->default('Holt lineal sin estacionalidad');
                $table->decimal('alpha', 12, 8)->nullable();
                $table->decimal('beta', 12, 8)->nullable();
                $table->decimal('mae', 12, 4)->nullable();
                $table->decimal('rmse', 12, 4)->nullable();
                $table->decimal('mae_linea_base', 12, 4)->nullable();
                $table->decimal('rmse_linea_base', 12, 4)->nullable();
                $table->decimal('mae_suavizamiento_simple', 12, 4)->nullable();
                $table->decimal('rmse_suavizamiento_simple', 12, 4)->nullable();
                $table->decimal('mae_regresion_lineal', 12, 4)->nullable();
                $table->decimal('rmse_regresion_lineal', 12, 4)->nullable();
                $table->unsignedInteger('ventanas_evaluadas')->default(0);
                $table->unsignedInteger('ventanas_superadas')->default(0);
                $table->unsignedInteger('meses_entrenamiento')->default(0);
                $table->unsignedTinyInteger('horizonte_meses')->default(6);
                $table->enum('estado', ['viable', 'no_viable', 'pendiente'])->default('pendiente');
                $table->string('modo_datos', 30)->default('evaluacion_real');
                $table->string('mejor_metodo', 50)->nullable();
                $table->text('observaciones')->nullable();
                $table->json('resultados_validacion')->nullable();
                $table->date('fecha_corte_datos')->nullable();
                $table->foreignId('ejecutado_por')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
            $createdTables[] = 'predicciones';
        }

        if (! Schema::hasTable('prediccion_valores')) {
            Schema::create('prediccion_valores', function (Blueprint $table) {
                $table->id();
                $table->foreignId('prediccion_id')->constrained('predicciones')->cascadeOnDelete();
                $table->unsignedInteger('anio');
                $table->unsignedTinyInteger('mes');
                $table->decimal('valor_predicho', 12, 2);
                $table->decimal('valor_real', 12, 2)->nullable();
                $table->decimal('intervalo_inferior', 12, 2)->nullable();
                $table->decimal('intervalo_superior', 12, 2)->nullable();
                $table->decimal('crecimiento_absoluto', 12, 2)->nullable();
                $table->decimal('crecimiento_porcentual', 12, 4)->nullable();
                $table->timestamps();
                $table->unique(['prediccion_id', 'anio', 'mes']);
            });
            $createdTables[] = 'prediccion_valores';
        }

        if (! Schema::hasTable('actividades')) {
            Schema::create('actividades', function (Blueprint $table) {
                $table->id();
                $table->foreignId('iglesia_id')->nullable()->constrained('iglesias')->nullOnDelete();
                $table->foreignId('circuito_id')->nullable()->constrained('circuitos')->nullOnDelete();
                $table->enum('nivel', ['iglesia', 'circuito', 'distrito']);
                $table->string('titulo');
                $table->text('descripcion')->nullable();
                $table->enum('tipo', ['culto', 'estudio_biblico', 'reunion_administrativa', 'evento_social', 'capacitacion', 'mision', 'otro']);
                $table->date('fecha_inicio');
                $table->date('fecha_fin')->nullable();
                $table->time('hora_inicio')->nullable();
                $table->time('hora_fin')->nullable();
                $table->string('lugar')->nullable();
                $table->unsignedInteger('asistentes')->nullable();
                $table->enum('estado', ['programada', 'en_curso', 'completada', 'cancelada'])->default('programada');
                $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
            $createdTables[] = 'actividades';
        }

        if (! Schema::hasTable('comunicaciones')) {
            Schema::create('comunicaciones', function (Blueprint $table) {
                $table->id();
                $table->foreignId('remitente_id')->constrained('users')->restrictOnDelete();
                $table->enum('tipo', ['circular', 'aviso', 'informe', 'solicitud', 'respuesta']);
                $table->enum('nivel', ['iglesia', 'circuito', 'distrito']);
                $table->string('titulo');
                $table->text('contenido');
                $table->enum('prioridad', ['normal', 'urgente'])->default('normal');
                $table->enum('estado', ['enviada', 'recibida', 'archivada'])->default('enviada');
                $table->dateTime('fecha_envio')->useCurrent();
                $table->timestamps();
            });
            $createdTables[] = 'comunicaciones';
        }

        if (! Schema::hasTable('comunicacion_destinatarios')) {
            Schema::create('comunicacion_destinatarios', function (Blueprint $table) {
                $table->id();
                $table->foreignId('comunicacion_id')->constrained('comunicaciones')->cascadeOnDelete();
                $table->foreignId('destinatario_id')->constrained('users')->cascadeOnDelete();
                $table->boolean('leido')->default(false);
                $table->dateTime('fecha_lectura')->nullable();
                $table->timestamps();
                $table->unique(['comunicacion_id', 'destinatario_id']);
            });
            $createdTables[] = 'comunicacion_destinatarios';
        }

        DB::table('sigem_schema_installations')->updateOrInsert(
            ['migration' => self::KEY],
            [
                'created_tables' => json_encode($createdTables, JSON_THROW_ON_ERROR),
                'added_user_columns' => json_encode($addedUserColumns, JSON_THROW_ON_ERROR),
                'added_member_columns' => json_encode($addedMemberColumns, JSON_THROW_ON_ERROR),
                'marker_created' => $markerCreated,
            ],
        );
    }

    public function down(): void
    {
        if (! Schema::hasTable('sigem_schema_installations')) {
            return;
        }

        $installation = DB::table('sigem_schema_installations')
            ->where('migration', self::KEY)
            ->first();
        if (! $installation) {
            return;
        }

        $userColumns = json_decode($installation->added_user_columns, true, 512, JSON_THROW_ON_ERROR);
        $memberColumns = json_decode($installation->added_member_columns, true, 512, JSON_THROW_ON_ERROR);
        if ($userColumns && Schema::hasTable('users')) {
            Schema::table('users', fn (Blueprint $table) => $table->dropColumn($userColumns));
        }
        if ($memberColumns && Schema::hasTable('miembros')) {
            Schema::table('miembros', fn (Blueprint $table) => $table->dropColumn($memberColumns));
        }

        $createdTables = json_decode($installation->created_tables, true, 512, JSON_THROW_ON_ERROR);
        foreach (array_reverse($createdTables) as $table) {
            Schema::dropIfExists($table);
        }

        DB::table('sigem_schema_installations')->where('migration', self::KEY)->delete();
        if ($installation->marker_created) {
            Schema::dropIfExists('sigem_schema_installations');
        }
    }

    private function addUserColumns(): array
    {
        if (! Schema::hasTable('users')) {
            return [];
        }

        $columns = collect(['rol', 'iglesia_id', 'circuito_id', 'telefono', 'estado'])
            ->reject(fn (string $column): bool => Schema::hasColumn('users', $column))
            ->values()
            ->all();

        Schema::table('users', function (Blueprint $table) use ($columns) {
            if (in_array('rol', $columns, true)) {
                $table->enum('rol', ['admin', 'distrito', 'circuito', 'local'])->default('local');
            }
            if (in_array('iglesia_id', $columns, true)) {
                $table->foreignId('iglesia_id')->nullable()->constrained('iglesias')->nullOnDelete();
            }
            if (in_array('circuito_id', $columns, true)) {
                $table->foreignId('circuito_id')->nullable()->constrained('circuitos')->nullOnDelete();
            }
            if (in_array('telefono', $columns, true)) {
                $table->string('telefono')->nullable();
            }
            if (in_array('estado', $columns, true)) {
                $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            }
        });

        return $columns;
    }

    private function addMemberCompatibilityColumns(): array
    {
        $columns = collect(['direccion', 'fecha_ingreso', 'motivo_baja'])
            ->reject(fn (string $column): bool => Schema::hasColumn('miembros', $column))
            ->values()
            ->all();

        Schema::table('miembros', function (Blueprint $table) use ($columns) {
            if (in_array('direccion', $columns, true)) {
                $table->string('direccion')->nullable();
            }
            if (in_array('fecha_ingreso', $columns, true)) {
                $table->date('fecha_ingreso')->nullable();
            }
            if (in_array('motivo_baja', $columns, true)) {
                $table->string('motivo_baja')->nullable();
            }
        });

        if (in_array('fecha_ingreso', $columns, true) && Schema::hasColumn('miembros', 'fecha_registro')) {
            DB::table('miembros')->whereNull('fecha_ingreso')->update([
                'fecha_ingreso' => DB::raw('fecha_registro'),
            ]);
        }

        return $columns;
    }
};
