<?php

declare(strict_types=1);

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateTasksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;

        return $this->user()->can('tasks.update');
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255|min:3',
            'description' => 'nullable|string|max:1000',
            'status' => 'sometimes|string|in:pending,in_progress,completed,cancelled',
            'priority' => 'sometimes|string|in:low,medium,high',
            'assigned_to' => 'nullable|integer|exists:users,id',
            'due_date' => 'nullable|date',
            'completed_at' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El título es obligatorio.',
            'title.max' => 'El título no puede tener más de 255 caracteres.',
            'description.max' => 'La descripción no puede tener más de 1000 caracteres.',
            'status.in' => 'El estado debe ser uno de: pendiente, en progreso, completado, cancelado.',
            'priority.in' => 'La prioridad debe ser una de: baja, media, alta.',
            'assigned_to.exists' => 'El usuario asignado no existe.',
            'due_date.date' => 'La fecha de vencimiento debe ser una fecha válida.',
            'completed_at.date' => 'La fecha de completado debe ser una fecha válida.',
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'título',
            'description' => 'descripción',
            'status' => 'estado',
            'priority' => 'prioridad',
            'assigned_to' => 'usuario asignado',
            'due_date' => 'fecha de vencimiento',
            'completed_at' => 'fecha de completado',
        ];
    }
}
