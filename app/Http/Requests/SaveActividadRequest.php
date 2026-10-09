<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveActividadRequest extends FormRequest
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
            'nivel' => ['required', 'in:iglesia,circuito,distrito'],
            'iglesia_id' => ['nullable', 'required_if:nivel,iglesia', 'integer', 'exists:iglesias,id'],
            'circuito_id' => ['nullable', 'required_if:nivel,circuito', 'integer', 'exists:circuitos,id'],
            'titulo' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'tipo' => ['required', 'in:culto,estudio_biblico,reunion_administrativa,evento_social,capacitacion,mision,otro'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'hora_inicio' => ['nullable', 'date_format:H:i'],
            'hora_fin' => ['nullable', 'date_format:H:i', 'after:hora_inicio'],
            'lugar' => ['nullable', 'string', 'max:255'],
            'asistentes' => ['nullable', 'integer', 'min:0'],
            'estado' => ['required', 'in:programada,en_curso,completada,cancelada'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'iglesia_id.required_if' => 'Seleccione la iglesia responsable de la actividad.',
            'circuito_id.required_if' => 'Seleccione el circuito responsable de la actividad.',
            'fecha_fin.after_or_equal' => 'La fecha final no puede ser anterior a la fecha de inicio.',
            'hora_fin.after' => 'La hora final debe ser posterior a la hora de inicio.',
        ];
    }
}
