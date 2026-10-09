<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreComunicacionRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $user = $this->user();
        if ($user?->isLocal()) {
            $this->merge([
                'nivel' => 'iglesia',
                'iglesia_id' => $user->iglesia_id,
                'circuito_id' => $user->circuito_id,
            ]);
        } elseif ($user?->isCircuito()) {
            $this->merge(['circuito_id' => $user->circuito_id]);
        }
    }

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'tipo' => ['required', 'in:circular,aviso,informe,solicitud,respuesta'],
            'nivel' => ['required', 'in:iglesia,circuito,distrito'],
            'iglesia_id' => ['nullable', 'required_if:nivel,iglesia', 'integer', 'exists:iglesias,id'],
            'circuito_id' => ['nullable', 'required_if:nivel,circuito', 'integer', 'exists:circuitos,id'],
            'titulo' => ['required', 'string', 'max:255'],
            'contenido' => ['required', 'string', 'max:5000'],
            'prioridad' => ['required', 'in:normal,urgente'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'iglesia_id.required_if' => 'Seleccione la iglesia destinataria.',
            'circuito_id.required_if' => 'Seleccione el circuito destinatario.',
        ];
    }
}
