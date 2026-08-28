<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReservaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'motivo' => ['required', 'string', 'max:500'],
            'necesidades' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'fecha.required' => 'Selecciona una fecha.',
            'start_time.required' => 'Selecciona la hora de inicio.',
            'end_time.required' => 'Selecciona la hora de término.',
            'end_time.after' => 'La hora de término debe ser posterior a la hora de inicio.',
            'motivo.required' => 'Escribe el motivo del evento.',
            'motivo.max' => 'El motivo no puede superar los 500 caracteres.',
            'necesidades.max' => 'Las necesidades adicionales no pueden superar los 1000 caracteres.',
        ];
    }
}
