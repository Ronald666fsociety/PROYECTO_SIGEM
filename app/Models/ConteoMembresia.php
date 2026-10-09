<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConteoMembresia extends Model
{
    use HasFactory;

    protected $table = 'conteos_membresia';

    protected $fillable = [
        'iglesia_id', 'anio', 'mes', 'fecha_corte',
        'total_activos', 'total_inactivos', 'total_nuevos', 'total_transferidos', 'total_bajas',
        'estado', 'es_sintetico', 'registrado_por',
        'fuente_datos', 'version_datos', 'archivo_origen', 'hash_archivo', 'observaciones_calidad',
    ];

    protected $casts = [
        'fecha_corte' => 'date',
        'es_sintetico' => 'boolean',
    ];

    public function iglesia(): BelongsTo
    {
        return $this->belongsTo(Iglesia::class);
    }

    public function registrador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    public function getPeriodoAttribute(): string
    {
        $meses = [
            1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr',
            5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago',
            9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic',
        ];

        return ($meses[$this->mes] ?? '')." {$this->anio}";
    }
}
