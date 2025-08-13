<?php

declare(strict_types=1);

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

final class BulkActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
        return $this->user()->can('tasks.delete');
    }

    public function rules(): array
    {
        return [
            'task_ids' => 'required|array|min:1',
            'task_ids.*' => 'integer|exists:tasks,id',
        ];
    }

    public function messages(): array
    {
        return [
            'task_ids.required' => 'Debe seleccionar al menos una tarea.',
            'task_ids.array' => 'Los IDs de tareas deben ser un array.',
            'task_ids.min' => 'Debe seleccionar al menos una tarea.',
            'task_ids.*.integer' => 'Cada ID de tarea debe ser un número entero.',
            'task_ids.*.exists' => 'Una o más tareas seleccionadas no existen.',
        ];
    }

    public function attributes(): array
    {
        return [
            'task_ids' => 'IDs de tareas',
            'task_ids.*' => 'ID de tarea',
        ];
    }
}
