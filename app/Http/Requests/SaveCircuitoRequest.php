<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveCircuitoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAccesoDistrito() === true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $circuito = $this->route('circuito');

        return [
            'nombre' => ['required', 'string', 'max:255'],
            'codigo' => ['required', 'string', 'max:50', Rule::unique('circuitos', 'codigo')->ignore($circuito)],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'responsable_nombre' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'estado' => ['required', 'in:activo,inactivo'],
        ];
    }
}
