<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveIglesiaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAccesoDistrito() === true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $iglesia = $this->route('iglesia');

        return [
            'circuito_id' => ['required', 'integer', 'exists:circuitos,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'codigo' => ['required', 'string', 'max:50', Rule::unique('iglesias', 'codigo')->ignore($iglesia)],
            'localidad' => ['nullable', 'string', 'max:255'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'pastor_nombre' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'fecha_fundacion' => ['nullable', 'date', 'before_or_equal:today'],
            'estado' => ['required', 'in:activo,inactivo'],
        ];
    }
}
