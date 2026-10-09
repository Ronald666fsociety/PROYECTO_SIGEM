<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'rol',
        'iglesia_id', 'circuito_id', 'telefono', 'estado',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function iglesia(): BelongsTo
    {
        return $this->belongsTo(Iglesia::class);
    }

    public function circuito(): BelongsTo
    {
        return $this->belongsTo(Circuito::class);
    }

    public function comunicacionesEnviadas(): HasMany
    {
        return $this->hasMany(Comunicacion::class, 'remitente_id');
    }

    public function isAdmin(): bool
    {
        return $this->rol === 'admin';
    }

    public function isDistrito(): bool
    {
        return $this->rol === 'distrito';
    }

    public function isCircuito(): bool
    {
        return $this->rol === 'circuito';
    }

    public function isLocal(): bool
    {
        return $this->rol === 'local';
    }

    public function hasAccesoDistrito(): bool
    {
        return in_array($this->rol, ['admin', 'distrito']);
    }

    public function puedeGestionarIglesia(Iglesia $iglesia): bool
    {
        if ($this->hasAccesoDistrito()) {
            return true;
        }

        if ($this->isCircuito()) {
            return (int) $this->circuito_id === (int) $iglesia->circuito_id;
        }

        return $this->isLocal() && (int) $this->iglesia_id === (int) $iglesia->id;
    }

    public function puedeGestionarCircuito(Circuito $circuito): bool
    {
        return $this->hasAccesoDistrito()
            || ($this->isCircuito() && (int) $this->circuito_id === (int) $circuito->id);
    }

    public function getRolDisplayAttribute(): string
    {
        return match ($this->rol) {
            'admin' => 'Administrador',
            'distrito' => 'Superintendente Distrito',
            'circuito' => 'Responsable Circuito',
            'local' => 'Responsable Local',
            default => 'Sin Rol',
        };
    }
}
