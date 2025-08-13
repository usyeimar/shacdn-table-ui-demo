<?php

declare(strict_types=1);

namespace App\Http\Resource;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => [
                'value' => $this->status,
                'label' => $this->status_label,
                'color' => $this->status_color,
            ],
            'priority' => [
                'value' => $this->priority,
                'label' => $this->priority_label,
                'color' => $this->priority_color,
            ],
            'assigned_to' => $this->assignedUser?->id ?? null,
            'assigned_user_name' => $this->assignedUser?->name ?? 'Sin asignar',
            'due_date' => $this->due_date?->toISOString(),
            'due_date_formatted' => $this->due_date?->format('d/m/Y H:i'),
            'completed_at' => $this->completed_at?->toISOString(),
            'completed_at_formatted' => $this->completed_at?->format('d/m/Y H:i'),
            'created_at' => $this->created_at->toISOString(),
            'created_at_formatted' => $this->created_at->format('d/m/Y H:i'),
            'updated_at' => $this->updated_at->toISOString(),
            'updated_at_formatted' => $this->updated_at->format('d/m/Y H:i'),
            'deleted_at' => $this->deleted_at?->toISOString(),
            'deleted_at_formatted' => $this->deleted_at?->format('d/m/Y H:i'),

            'is_overdue' => $this->is_overdue,
            'is_due_today' => $this->is_due_today,
            'can_edit' => $this->when($request->user(), function () use ($request) {
                return $request->user()->can('tasks.update');
            }),
            'can_delete' => $this->when($request->user(), function () use ($request) {
                return $request->user()->can('tasks.delete');
            }),
            'can_restore' => $this->when($request->user(), function () use ($request) {
                return $request->user()->can('tasks.restore');
            }),
            'can_force_delete' => $this->when($request->user(), function () use ($request) {
                return $request->user()->can('tasks.forceDelete');
            }),
            'can_archive' => $this->when($request->user(), function () use ($request) {
                return $request->user()->can('tasks.archive');
            }),
        ];
    }
}
