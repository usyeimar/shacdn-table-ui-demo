<?php

declare(strict_types=1);

namespace App\Http\Controllers\Task\Api\Docs;

/**
 * @OA\Tag(
 *     name="Tasks",
 *     description="API Endpoints for Task management"
 * )
 */
trait TasksControllerDocs
{
    /**
     * @OA\Get(
     *     path="/api/v1/tasks",
     *     operationId="getTasks",
     *     tags={"Tasks"},
     *     summary="Get paginated list of tasks",
     *     description="Returns a paginated list of tasks with optional filtering and search",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for title, description, or assigned user",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status (can be multiple)",
     *         required=false,
     *         @OA\Schema(type="array", @OA\Items(type="string", enum={"pending","in_progress","completed","cancelled"}))
     *     ),
     *     @OA\Parameter(
     *         name="priority",
     *         in="query",
     *         description="Filter by priority (can be multiple)",
     *         required=false,
     *         @OA\Schema(type="array", @OA\Items(type="string", enum={"low","medium","high"}))
     *     ),
     *     @OA\Parameter(
     *         name="assigned_to",
     *         in="query",
     *         description="Filter by assigned user ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort field",
     *         required=false,
     *         @OA\Schema(type="string", default="created_at")
     *     ),
     *     @OA\Parameter(
     *         name="direction",
     *         in="query",
     *         description="Sort direction",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc","desc"}, default="desc")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Task")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function index()
    {
    }

    /**
     * @OA\Post(
     *     path="/api/v1/tasks",
     *     operationId="createTask",
     *     tags={"Tasks"},
     *     summary="Create a new task",
     *     description="Creates a new task with the provided data",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/TaskRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Task created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tarea creada exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/Task")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function store()
    {
    }

    /**
     * @OA\Get(
     *     path="/api/v1/tasks/{taskId}",
     *     operationId="getTask",
     *     tags={"Tasks"},
     *     summary="Get a specific task",
     *     description="Returns the details of a specific task",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="taskId",
     *         in="path",
     *         description="Task ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tarea obtenida exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/Task")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function show()
    {
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/tasks/{taskId}",
     *     operationId="updateTask",
     *     tags={"Tasks"},
     *     summary="Update a task",
     *     description="Updates an existing task with the provided data",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="taskId",
     *         in="path",
     *         description="Task ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/TaskRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tarea actualizada exitosamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/Task")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function update()
    {
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/tasks/{taskId}",
     *     operationId="deleteTask",
     *     tags={"Tasks"},
     *     summary="Delete a task",
     *     description="Soft deletes a task",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="taskId",
     *         in="path",
     *         description="Task ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Task deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function destroy()
    {
    }

    /**
     * @OA\Post(
     *     path="/api/v1/tasks/{taskId}/restore",
     *     operationId="restoreTask",
     *     tags={"Tasks"},
     *     summary="Restore a deleted task",
     *     description="Restores a soft-deleted task",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="taskId",
     *         in="path",
     *         description="Task ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Task restored successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function restore()
    {
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/tasks/{taskId}/force-delete",
     *     operationId="forceDeleteTask",
     *     tags={"Tasks"},
     *     summary="Permanently delete a task",
     *     description="Permanently deletes a task (cannot be restored)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="taskId",
     *         in="path",
     *         description="Task ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Task permanently deleted"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function forceDelete()
    {
    }

    /**
     * @OA\Post(
     *     path="/api/v1/tasks/export",
     *     operationId="exportTasks",
     *     tags={"Tasks"},
     *     summary="Export tasks",
     *     description="Exports tasks in various formats (CSV, Excel, PDF, JSON)",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="format", type="string", enum={"csv","xlsx","pdf","json"}, example="csv"),
     *             @OA\Property(property="selected_rows", type="array", @OA\Items(type="integer")),
     *             @OA\Property(property="filters", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Export completed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Exportación completada exitosamente"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="filename", type="string"),
     *                 @OA\Property(property="download_url", type="string"),
     *                 @OA\Property(property="format", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function export()
    {
    }

    /**
     * @OA\Get(
     *     path="/api/v1/tasks/stats",
     *     operationId="getTaskStats",
     *     tags={"Tasks"},
     *     summary="Get task statistics",
     *     description="Returns statistics about tasks",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Estadísticas obtenidas exitosamente"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(property="pending", type="integer"),
     *                 @OA\Property(property="in_progress", type="integer"),
     *                 @OA\Property(property="completed", type="integer"),
     *                 @OA\Property(property="cancelled", type="integer"),
     *                 @OA\Property(property="overdue", type="integer"),
     *                 @OA\Property(property="due_today", type="integer"),
     *                 @OA\Property(property="deleted", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function stats()
    {
    }
}
