<?php

declare(strict_types=1);

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

final class StoreTasksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;

        return $this->user()->can('tasks.create');
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => 'sometimes|string|in:pending,in_progress,completed,cancelled',
            'priority' => 'sometimes|string|in:low,medium,high',
            'assigned_to' => 'nullable|integer|exists:users,id',
            'due_date' => 'nullable|date|after_or_equal:today',
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
            'due_date.after_or_equal' => 'La fecha de vencimiento debe ser hoy o una fecha futura.',
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
        ];
    }
}
