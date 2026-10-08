<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comunicacion extends Model
{
    protected $table = 'comunicaciones';

    protected $fillable = [
        'remitente_id', 'tipo', 'nivel', 'titulo',
        'contenido', 'prioridad', 'estado', 'fecha_envio',
    ];

    protected $casts = [
        'fecha_envio' => 'datetime',
    ];

    public function remitente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'remitente_id');
    }

    public function destinatarios(): HasMany
    {
        return $this->hasMany(ComunicacionDestinatario::class);
    }
}
