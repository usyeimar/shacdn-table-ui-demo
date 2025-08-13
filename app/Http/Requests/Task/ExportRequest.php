<?php

declare(strict_types=1);

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

final class ExportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;

        return $this->user()->can('tasks.export');
    }

    public function rules(): array
    {
        return [
            'format' => 'sometimes|string|in:csv,xlsx,pdf,json',
            'selected_rows' => 'sometimes|array',
            'selected_rows.*' => 'integer|exists:tasks,id',
            'filters' => 'sometimes|array',
            'include_metadata' => 'sometimes|boolean',
            'include_filters' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'format.in' => 'El formato debe ser uno de: csv, xlsx, pdf, json.',
            'selected_rows.array' => 'Las filas seleccionadas deben ser un array.',
            'selected_rows.*.integer' => 'Cada ID de tarea debe ser un número entero.',
            'selected_rows.*.exists' => 'Una o más tareas seleccionadas no existen.',
            'filters.array' => 'Los filtros deben ser un array.',
            'include_metadata.boolean' => 'El campo incluir metadatos debe ser verdadero o falso.',
            'include_filters.boolean' => 'El campo incluir filtros debe ser verdadero o falso.',
        ];
    }

    public function attributes(): array
    {
        return [
            'format' => 'formato',
            'selected_rows' => 'filas seleccionadas',
            'selected_rows.*' => 'ID de tarea',
            'filters' => 'filtros',
            'include_metadata' => 'incluir metadatos',
            'include_filters' => 'incluir filtros',
        ];
    }
}
