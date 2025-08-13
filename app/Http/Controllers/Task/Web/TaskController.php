<?php

declare(strict_types=1);

namespace App\Http\Controllers\Task\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class TaskController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Tasks/Index');
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Tasks/Create');
    }

    public function show(Request $request, int $taskId): Response
    {
        return Inertia::render('Tasks/Show', [
            'taskId' => $taskId
        ]);
    }

    public function edit(Request $request, int $taskId): Response
    {
        return Inertia::render('Tasks/Edit', [
            'taskId' => $taskId
        ]);
    }
}
