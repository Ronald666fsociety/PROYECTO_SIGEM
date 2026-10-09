<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Miembro extends Model
{
    use HasFactory;

    protected $table = 'miembros';

    protected $fillable = [
        'iglesia_id', 'nombres', 'apellidos', 'ci', 'fecha_nacimiento',
        'genero', 'telefono', 'email', 'direccion', 'categoria',
        'estado', 'fecha_ingreso', 'fecha_registro', 'fecha_baja', 'motivo_baja', 'observaciones',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'fecha_ingreso' => 'date',
        'fecha_registro' => 'date',
        'fecha_baja' => 'date',
    ];

    public function iglesia(): BelongsTo
    {
        return $this->belongsTo(Iglesia::class);
    }

    public function getNombreCompletoAttribute(): string
    {
        return "{$this->nombres} {$this->apellidos}";
    }

    public function getCategoriaDisplayAttribute(): string
    {
        return match ($this->categoria) {
            'miembro_pleno' => 'Miembro Pleno',
            'miembro_preparatorio' => 'Miembro Preparatorio',
            'simpatizante' => 'Simpatizante',
            'nino' => 'Niño/a',
            default => 'Otro',
        };
    }
}
