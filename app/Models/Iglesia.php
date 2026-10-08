<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Iglesia extends Model
{
    protected $table = 'iglesias';

    protected $fillable = [
        'circuito_id', 'nombre', 'codigo', 'localidad', 'direccion',
        'pastor_nombre', 'fecha_fundacion', 'estado', 'latitud', 'longitud',
    ];

    protected $casts = [
        'fecha_fundacion' => 'date',
    ];

    public function circuito(): BelongsTo
    {
        return $this->belongsTo(Circuito::class);
    }

    public function miembros(): HasMany
    {
        return $this->hasMany(Miembro::class);
    }

    public function miembrosActivos(): HasMany
    {
        return $this->hasMany(Miembro::class)->where('estado', 'activo');
    }

    public function conteos(): HasMany
    {
        return $this->hasMany(ConteoMembresia::class);
    }

    public function actividades(): HasMany
    {
        return $this->hasMany(Actividad::class);
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
