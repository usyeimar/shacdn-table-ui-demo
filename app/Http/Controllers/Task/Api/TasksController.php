<?php

declare(strict_types=1);

namespace App\Http\Controllers\Task\Api;


use App\Http\Controllers\Task\Api\Docs\TasksControllerDocs;
use App\Http\Requests\Task\BulkActionRequest;
use App\Http\Requests\Task\ExportRequest;
use App\Http\Requests\Task\StoreTasksRequest;
use App\Http\Requests\Task\UpdateTasksRequest;
use App\Http\Resource\TaskResource;
use App\Services\Task\TasksService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

final class TasksController extends Controller
{
    use TasksControllerDocs;

    public function __construct(
        public TasksService $service
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $tasks = $this->service->index($request);

        return TaskResource::collection($tasks);
    }

    public function store(StoreTasksRequest $request): JsonResponse
    {
        $task = $this->service->store($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Tarea creada exitosamente',
            'data' => new TaskResource($task),
        ], 201);
    }

    public function show(int $taskId): JsonResponse
    {
        $task = $this->service->show($taskId);

        return response()->json([
            'success' => true,
            'message' => 'Tarea obtenida exitosamente',
            'data' => new TaskResource($task),
        ]);
    }

    public function update(UpdateTasksRequest $request, int $taskId): JsonResponse
    {
        $task = $this->service->update($taskId, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Tarea actualizada exitosamente',
            'data' => new TaskResource($task),
        ]);
    }

    public function destroy(int $taskId): Response
    {
        $this->service->destroy($taskId);

        return response()->noContent();
    }

    public function restore(int $taskId): Response
    {
        $this->service->restore($taskId);

        return response()->noContent();
    }

    public function forceDelete(int $taskId): Response
    {
        $this->service->forceDelete($taskId);

        return response()->noContent();
    }

    public function archive(int $taskId): JsonResponse
    {
        $this->service->archive($taskId);

        return response()->json([
            'success' => true,
            'message' => 'Tarea archivada exitosamente',
        ]);
    }

    public function markAsCompleted(int $taskId): JsonResponse
    {
        $task = $this->service->markAsCompleted($taskId);

        return response()->json([
            'success' => true,
            'message' => 'Tarea marcada como completada',
            'data' => new TaskResource($task),
        ]);
    }

    public function assignTo(Request $request, int $taskId): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $task = $this->service->assignTo($taskId, $request->get('user_id'));

        return response()->json([
            'success' => true,
            'message' => 'Tarea asignada exitosamente',
            'data' => new TaskResource($task),
        ]);
    }

    public function updatePriority(Request $request, int $taskId): JsonResponse
    {
        $request->validate([
            'priority' => 'required|string|in:low,medium,high',
        ]);

        $task = $this->service->updatePriority($taskId, $request->get('priority'));

        return response()->json([
            'success' => true,
            'message' => 'Prioridad actualizada exitosamente',
            'data' => new TaskResource($task),
        ]);
    }

    public function getDeletedTasks(Request $request): AnonymousResourceCollection
    {
        $tasks = $this->service->getDeletedTasks($request);

        return TaskResource::collection($tasks);
    }

    public function bulkDelete(BulkActionRequest $request): JsonResponse
    {
        $this->service->bulkDelete($request->get('task_ids'));

        return response()->json([
            'success' => true,
            'message' => 'Tareas eliminadas exitosamente',
        ]);
    }

    public function bulkRestore(BulkActionRequest $request): JsonResponse
    {
        $this->service->bulkRestore($request->get('task_ids'));

        return response()->json([
            'success' => true,
            'message' => 'Tareas restauradas exitosamente',
        ]);
    }

    public function bulkForceDelete(BulkActionRequest $request): JsonResponse
    {
        $this->service->bulkForceDelete($request->get('task_ids'));

        return response()->json([
            'success' => true,
            'message' => 'Tareas eliminadas permanentemente',
        ]);
    }

    public function bulkArchive(BulkActionRequest $request): JsonResponse
    {
        $this->service->bulkArchive($request->get('task_ids'));

        return response()->json([
            'success' => true,
            'message' => 'Tareas archivadas exitosamente',
        ]);
    }

    public function export(ExportRequest $request): JsonResponse
    {
        $format = $request->get('format', 'csv');
        $content = $this->service->export($request, $format);

        $filename = 'tasks-' . now()->format('Y-m-d-H-i-s') . '.' . $format;

        // Store the file temporarily
        $path = 'exports/' . $filename;
        Storage::put($path, $content);

        // Generate download URL
        $url = Storage::url($path);

        return response()->json([
            'success' => true,
            'message' => 'Exportación completada exitosamente',
            'data' => [
                'filename' => $filename,
                'download_url' => $url,
                'format' => $format,
            ],
        ]);
    }

    public function stats(): JsonResponse
    {
        $stats = $this->service->getStats();

        return response()->json([
            'success' => true,
            'message' => 'Estadísticas obtenidas exitosamente',
            'data' => $stats,
        ]);
    }

    public function getStatuses(): JsonResponse
    {
        $statuses = [
            ['id' => 'pending', 'display_name' => 'Pendiente'],
            ['id' => 'in_progress', 'display_name' => 'En Progreso'],
            ['id' => 'completed', 'display_name' => 'Completado'],
            ['id' => 'cancelled', 'display_name' => 'Cancelado'],
        ];

        return response()->json([
            'success' => true,
            'message' => 'Estados obtenidos exitosamente',
            'data' => $statuses,
        ]);
    }

    public function getPriorities(): JsonResponse
    {
        $priorities = [
            ['id' => 'low', 'display_name' => 'Baja'],
            ['id' => 'medium', 'display_name' => 'Media'],
            ['id' => 'high', 'display_name' => 'Alta'],
        ];

        return response()->json([
            'success' => true,
            'message' => 'Prioridades obtenidas exitosamente',
            'data' => $priorities,
        ]);
    }
}
