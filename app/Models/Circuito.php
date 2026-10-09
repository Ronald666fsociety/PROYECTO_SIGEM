<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Circuito extends Model
{
    use HasFactory;

    protected $table = 'circuitos';

    protected $fillable = [
        'nombre', 'codigo', 'descripcion', 'responsable_nombre', 'telefono', 'estado',
    ];

    public function iglesias(): HasMany
    {
        return $this->hasMany(Iglesia::class);
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function actividades(): HasMany
    {
        return $this->hasMany(Actividad::class);
    }

    public function totalMiembrosActivos(): int
    {
        return Miembro::whereHas('iglesia', function ($q) {
            $q->where('circuito_id', $this->id);
        })->where('estado', 'activo')->count();
    }
}
