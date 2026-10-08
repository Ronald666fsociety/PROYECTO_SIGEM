<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrediccionValor extends Model
{
    protected $table = 'prediccion_valores';

    protected $fillable = [
        'prediccion_id', 'anio', 'mes', 'valor_predicho', 'valor_real',
        'intervalo_inferior', 'intervalo_superior',
        'crecimiento_absoluto', 'crecimiento_porcentual',
    ];

    protected $casts = [
        'valor_predicho' => 'decimal:2',
        'valor_real' => 'decimal:2',
        'intervalo_inferior' => 'decimal:2',
        'intervalo_superior' => 'decimal:2',
        'crecimiento_absoluto' => 'decimal:2',
        'crecimiento_porcentual' => 'decimal:4',
    ];

    public function prediccion(): BelongsTo
    {
        return $this->belongsTo(Prediccion::class);
    }

    public function getPeriodoAttribute(): string
    {
        $meses = [
            1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr',
            5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago',
            9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic',
        ];
        return ($meses[$this->mes] ?? '') . " {$this->anio}";
    }
}
