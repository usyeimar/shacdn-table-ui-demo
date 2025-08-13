<?php

declare(strict_types=1);

namespace App\Services\Task;

use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\AllowedInclude;

final class TasksService
{
    public function index(Request $request): LengthAwarePaginator
    {
        $this->authorize('tasks.read');

        $query = QueryBuilder::for(Task::class)
            ->allowedIncludes([
                'assignedUser',
                'createdByUser',
                'updatedByUser'
            ])
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::exact('priority'),
                AllowedFilter::exact('assigned_to'),
                AllowedFilter::scope('overdue'),
                AllowedFilter::scope('due_today'),
                AllowedFilter::scope('due_soon'),
                AllowedFilter::scope('only_trashed'),
                AllowedFilter::trashed(),
                AllowedFilter::callback('search', function (Builder $query, $value) {
                    $query->where(function (Builder $q) use ($value) {
                        $q->where('title', 'like', "%{$value}%")
                            ->orWhere('description', 'like', "%{$value}%")
                            ->orWhereHas('assignedUser', function (Builder $userQuery) use ($value) {
                                $userQuery->where('name', 'like', "%{$value}%")
                                    ->orWhere('email', 'like', "%{$value}%");
                            });
                    });
                }),
                AllowedFilter::callback('assigned_user', function (Builder $query, $value) {
                    $query->whereHas('assignedUser', function (Builder $userQuery) use ($value) {
                        $userQuery->where('name', 'like', "%{$value}%")
                            ->orWhere('email', 'like', "%{$value}%");
                    });
                }),
                AllowedFilter::callback('due_date', function (Builder $query, $value) {
                    if (is_array($value) && count($value) === 2) {
                        $query->whereBetween('due_date', $value);
                    } elseif (is_string($value)) {
                        $dates = explode(',', $value);
                        if (count($dates) === 2) {
                            $query->whereBetween('due_date', $dates);
                        }
                    }
                }),
                AllowedFilter::callback('created_at', function (Builder $query, $value) {
                    if (is_array($value) && count($value) === 2) {
                        $query->whereBetween('created_at', $value);
                    } elseif (is_string($value)) {
                        $dates = explode(',', $value);
                        if (count($dates) === 2) {
                            $query->whereBetween('created_at', $dates);
                        }
                    }
                }),
            ])
            ->allowedSorts([
                'id',
                'title',
                'status',
                'priority',
                'due_date',
                'created_at',
                'updated_at',
            ])
            ->defaultSort('-created_at');

        // Handle deleted mode
        if ($request->boolean('trashed') || $request->boolean('deleted_mode')) {
            $query->withTrashed();
        }

        if ($request->boolean('only_trashed')) {
            $query->onlyTrashed();
        }

        // Apply pagination
        $perPage = $request->get('per_page', 25);
        if ($request->has('page')) {
            $page = $request->get('page');
            if (is_array($page)) {
                $pageNumber = $page['number'] ?? 1;
                $pageSize = $page['size'] ?? $perPage;
            } else {
                $pageNumber = $page;
                $pageSize = $perPage;
            }
        } else {
            $pageNumber = 1;
            $pageSize = $perPage;
        }

        return $query->paginate($pageSize, ['*'], 'page', $pageNumber);
    }

    public function store(array $data): Task
    {
        $this->authorize('tasks.create');

        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();

        return Task::create($data);
    }

    public function show(int $taskId): Task
    {
        $this->authorize('tasks.read');

        $task = QueryBuilder::for(Task::class)
            ->allowedIncludes([
                'assignedUser',
                'createdByUser',
                'updatedByUser'
            ])
            ->findOrFail($taskId);

        return $task;
    }

    public function update(int $taskId, array $data): Task
    {
        $this->authorize('tasks.update');

        $task = Task::findOrFail($taskId);

        $data['updated_by'] = Auth::id();
        $task->update($data);

        return $task->fresh(['assignedUser', 'createdByUser', 'updatedByUser']);
    }

    public function destroy(int $taskId): void
    {
        $this->authorize('tasks.delete');

        $task = Task::findOrFail($taskId);
        $task->delete();
    }

    public function restore(int $taskId): void
    {
        $this->authorize('tasks.restore');

        $task = Task::withTrashed()->findOrFail($taskId);
        $task->restore();
    }

    public function forceDelete(int $taskId): void
    {
        $this->authorize('tasks.forceDelete');

        $task = Task::withTrashed()->findOrFail($taskId);
        $task->forceDelete();
    }

    public function archive(int $taskId): void
    {
        $this->authorize('tasks.archive');

        $task = Task::findOrFail($taskId);
        $task->update(['status' => 'archived']);
    }

    public function markAsCompleted(int $taskId): Task
    {
        $this->authorize('tasks.update');

        $task = Task::findOrFail($taskId);
        $task->update([
            'status' => 'completed',
            'completed_at' => now(),
            'updated_by' => Auth::id()
        ]);

        return $task->fresh(['assignedUser', 'createdByUser', 'updatedByUser']);
    }

    public function assignTo(int $taskId, int $userId): Task
    {
        $this->authorize('tasks.update');

        $task = Task::findOrFail($taskId);
        $task->update([
            'assigned_to' => $userId,
            'updated_by' => Auth::id()
        ]);

        return $task->fresh(['assignedUser', 'createdByUser', 'updatedByUser']);
    }

    public function updatePriority(int $taskId, string $priority): Task
    {
        $this->authorize('tasks.update');

        $task = Task::findOrFail($taskId);
        $task->update([
            'priority' => $priority,
            'updated_by' => Auth::id()
        ]);

        return $task->fresh(['assignedUser', 'createdByUser', 'updatedByUser']);
    }

    public function bulkDelete(array $taskIds): void
    {
        $this->authorize('tasks.delete');

        Task::whereIn('id', $taskIds)->delete();
    }

    public function bulkRestore(array $taskIds): void
    {
        $this->authorize('tasks.restore');

        Task::withTrashed()->whereIn('id', $taskIds)->restore();
    }

    public function bulkForceDelete(array $taskIds): void
    {
        $this->authorize('tasks.forceDelete');

        Task::withTrashed()->whereIn('id', $taskIds)->forceDelete();
    }

    public function bulkArchive(array $taskIds): void
    {
        $this->authorize('tasks.archive');

        Task::whereIn('id', $taskIds)->update(['status' => 'archived']);
    }

    public function getDeletedTasks(Request $request): LengthAwarePaginator
    {
        $this->authorize('tasks.read');

        $query = QueryBuilder::for(Task::class)
            ->allowedIncludes([
                'assignedUser',
                'createdByUser',
                'updatedByUser'
            ])
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::exact('priority'),
                AllowedFilter::callback('search', function (Builder $query, $value) {
                    $query->where(function (Builder $q) use ($value) {
                        $q->where('title', 'like', "%{$value}%")
                            ->orWhere('description', 'like', "%{$value}%");
                    });
                }),
            ])
            ->allowedSorts([
                'id',
                'title',
                'status',
                'priority',
                'deleted_at',
            ])
            ->defaultSort('-deleted_at')
            ->onlyTrashed();

        $perPage = $request->get('per_page', 25);
        return $query->paginate($perPage);
    }

    public function export(Request $request, string $format): string
    {
        $this->authorize('tasks.export');

        $query = QueryBuilder::for(Task::class)
            ->allowedIncludes([
                'assignedUser',
                'createdByUser',
                'updatedByUser'
            ])
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::exact('priority'),
                AllowedFilter::exact('assigned_to'),
                AllowedFilter::scope('overdue'),
                AllowedFilter::scope('due_today'),
                AllowedFilter::scope('trashed'),
                AllowedFilter::scope('only_trashed'),
                AllowedFilter::callback('search', function (Builder $query, $value) {
                    $query->where(function (Builder $q) use ($value) {
                        $q->where('title', 'like', "%{$value}%")
                            ->orWhere('description', 'like', "%{$value}%");
                    });
                }),
            ])
            ->allowedSorts([
                'id',
                'title',
                'status',
                'priority',
                'due_date',
                'created_at',
            ])
            ->defaultSort('-created_at');

        // Handle deleted mode
        if ($request->boolean('trashed') || $request->boolean('deleted_mode')) {
            $query->withTrashed();
        }

        if ($request->boolean('only_trashed')) {
            $query->onlyTrashed();
        }

        // Get selected rows if specified
        if ($request->filled('selected_rows')) {
            $selectedIds = $request->get('selected_rows');
            $query->whereIn('id', $selectedIds);
        }

        $tasks = $query->get();

        return $this->generateExport($tasks, $format);
    }

    public function getStats(): array
    {
        $this->authorize('tasks.read');

        return [
            'total' => Task::count(),
            'pending' => Task::pending()->count(),
            'in_progress' => Task::inProgress()->count(),
            'completed' => Task::completed()->count(),
            'cancelled' => Task::cancelled()->count(),
            'overdue' => Task::overdue()->count(),
            'due_today' => Task::dueToday()->count(),
            'deleted' => Task::withTrashed()->whereNotNull('deleted_at')->count(),
        ];
    }

    public function getStatuses(): array
    {
        return [
            ['value' => 'pending', 'label' => 'Pendiente'],
            ['value' => 'in_progress', 'label' => 'En Progreso'],
            ['value' => 'completed', 'label' => 'Completada'],
            ['value' => 'cancelled', 'label' => 'Cancelada'],
        ];
    }

    public function getPriorities(): array
    {
        return [
            ['value' => 'low', 'label' => 'Baja'],
            ['value' => 'medium', 'label' => 'Media'],
            ['value' => 'high', 'label' => 'Alta'],
        ];
    }

    private function generateExport(Collection $tasks, string $format): string
    {
        $filename = 'tasks-' . now()->format('Y-m-d-H-i-s');

        switch ($format) {
            case 'csv':
                return $this->exportToCsv($tasks, $filename);
            case 'xlsx':
                return $this->exportToXlsx($tasks, $filename);
            case 'pdf':
                return $this->exportToPdf($tasks, $filename);
            case 'json':
                return $this->exportToJson($tasks, $filename);
            default:
                return $this->exportToCsv($tasks, $filename);
        }
    }

    private function exportToCsv(Collection $tasks, string $filename): string
    {
        $headers = [
            'ID',
            'Título',
            'Descripción',
            'Estado',
            'Prioridad',
            'Asignado a',
            'Fecha de Vencimiento',
            'Fecha de Creación',
            'Fecha de Actualización'
        ];

        $csv = fopen('php://temp', 'r+');
        fputcsv($csv, $headers);

        foreach ($tasks as $task) {
            fputcsv($csv, [
                $task->id,
                $task->title,
                $task->description,
                $task->status,
                $task->priority,
                $task->assignedUser?->name ?? 'Sin asignar',
                $task->due_date?->format('Y-m-d') ?? 'Sin fecha',
                $task->created_at->format('Y-m-d H:i:s'),
                $task->updated_at->format('Y-m-d H:i:s')
            ]);
        }

        rewind($csv);
        $content = stream_get_contents($csv);
        fclose($csv);

        return $content;
    }

    private function exportToXlsx(Collection $tasks, string $filename): string
    {
        // This would typically use a library like PhpSpreadsheet
        // For now, we'll return a simple CSV as placeholder
        return $this->exportToCsv($tasks, $filename);
    }

    private function exportToJson(Collection $tasks, string $filename): string
    {
        return $tasks->toJson(JSON_PRETTY_PRINT);
    }

    private function exportToPdf(Collection $tasks, string $filename): string
    {
        // This would typically use a library like Dompdf
        // For now, we'll return a simple HTML as placeholder
        $html = '<html><body>';
        $html .= '<h1>Reporte de Tareas</h1>';
        $html .= '<table border="1">';
        $html .= '<tr><th>ID</th><th>Título</th><th>Estado</th><th>Prioridad</th><th>Asignado</th></tr>';

        foreach ($tasks as $task) {
            $html .= '<tr>';
            $html .= '<td>' . $task->id . '</td>';
            $html .= '<td>' . htmlspecialchars($task->title) . '</td>';
            $html .= '<td>' . $task->status . '</td>';
            $html .= '<td>' . $task->priority . '</td>';
            $html .= '<td>' . ($task->assignedUser?->name ?? 'Sin asignar') . '</td>';
            $html .= '</tr>';
        }

        $html .= '</table></body></html>';

        return $html;
    }

    private function authorize(string $permission): void
    {
        $user = Auth::user();

        if (!$user) {
            abort(401, 'No autenticado.');
        }

        if (!$user->can($permission)) {
            abort(403, 'No tienes permisos para realizar esta acción.');
        }
    }
}
