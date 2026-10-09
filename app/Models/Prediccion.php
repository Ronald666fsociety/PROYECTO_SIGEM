<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prediccion extends Model
{
    protected $table = 'predicciones';

    protected $fillable = [
        'fecha_ejecucion', 'modelo', 'alpha', 'beta',
        'mae', 'rmse', 'mae_linea_base', 'rmse_linea_base',
        'mae_suavizamiento_simple', 'rmse_suavizamiento_simple',
        'mae_regresion_lineal', 'rmse_regresion_lineal',
        'ventanas_evaluadas', 'ventanas_superadas',
        'meses_entrenamiento', 'horizonte_meses',
        'estado', 'modo_datos', 'mejor_metodo', 'observaciones',
        'resultados_validacion', 'fecha_corte_datos', 'ejecutado_por',
    ];

    protected $casts = [
        'fecha_ejecucion' => 'datetime',
        'alpha' => 'decimal:6',
        'beta' => 'decimal:6',
        'mae' => 'decimal:4',
        'rmse' => 'decimal:4',
        'mae_linea_base' => 'decimal:4',
        'rmse_linea_base' => 'decimal:4',
        'mae_suavizamiento_simple' => 'decimal:4',
        'rmse_suavizamiento_simple' => 'decimal:4',
        'mae_regresion_lineal' => 'decimal:4',
        'rmse_regresion_lineal' => 'decimal:4',
        'resultados_validacion' => 'array',
        'fecha_corte_datos' => 'date',
    ];

    public function valores(): HasMany
    {
        return $this->hasMany(PrediccionValor::class);
    }

    public function ejecutador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ejecutado_por');
    }

    public function getEsViableAttribute(): bool
    {
        return $this->modo_datos === 'evaluacion_real' && $this->estado === 'viable';
    }

    public function getEstadoEvaluacionDisplayAttribute(): string
    {
        if ($this->modo_datos === 'prueba_funcional') {
            return 'Prueba funcional pendiente de validación real';
        }

        return match ($this->estado) {
            'viable' => 'Holt obtuvo desempeño favorable',
            'no_viable' => 'Holt no superó los métodos de referencia',
            default => 'Evaluación pendiente',
        };
    }
}
