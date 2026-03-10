<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use App\Services\TaskService;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\TaskResource;
use App\Http\Requests\Api\V1\StoreTaskRequest;
use App\Http\Requests\Api\V1\UpdateTaskRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Filters\TaskFilters;
use Illuminate\Support\Facades\Cache;

class TaskController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly TaskService $taskService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min($request->integer('per_page', 15), 100);
        $cacheKey = 'tasks:' . md5($request->fullUrl());
        $tasks = Cache::remember($cacheKey, env('CACHE_TASKS_TTL', 60), function () use ($request, $perPage) {
            $filters = new TaskFilters($request);

            return $filters
                ->apply(
                    Task::query()->with(['project', 'creator', 'assignee'])
                )
                ->paginate($perPage);

        });

        return TaskResource::collection($tasks);
    }

    public function show(Task $task): TaskResource
    {
        $task = Cache::remember(
            "task:{$task->id}",
            env('CACHE_TASKS_TTL', 60),
            fn () => $task->load(['project','creator','assignee','comments'])
        );

        return new TaskResource($task);
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $data = $request->validated();
        $project = Project::findOrFail($data['project_id']);

        /** @var User $user */
        $user = $request->user();

        $task = $this->taskService->create(
            $project,
            $user,
            $data
        );

        Cache::tags(['tasks'])->flush();

        return response()->json(new TaskResource($task), 201);
    }

    public function update(UpdateTaskRequest $request, Task $task): TaskResource
    {
        $this->authorize('update', $task);

        $task = $this->taskService->update(
            $task,
            $request->validated()
        );

        Cache::tags(['tasks'])->forget("task:{$task->id}");
        Cache::tags(['tasks'])->flush();

        return new TaskResource($task);
    }

    public function destroy(Task $task): JsonResponse
    {
        $this->authorize('delete', $task);

        $this->taskService->delete($task);

        Cache::tags(['tasks'])->forget("task:{$task->id}");
        Cache::tags(['tasks'])->flush();

        return response()->json([
            'message' => 'Task deleted'
        ]);
    }
}
