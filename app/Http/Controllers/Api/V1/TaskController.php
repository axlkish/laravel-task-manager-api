<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use App\Services\TaskService;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\TaskResource;
use App\Http\Requests\Api\V1\StoreTaskRequest;
use App\Http\Requests\Api\V1\UpdateTaskRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Filters\TaskFilters;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskService $taskService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = new TaskFilters($request);

        $perPage = $request->integer('per_page', 15);

        $perPage = min($perPage, 100);

        $tasks = $filters
            ->apply(
                Task::query()->with(['project', 'creator', 'assignee'])
            )
            ->paginate($perPage);

        return TaskResource::collection($tasks);
    }

    public function show(Task $task): TaskResource
    {
        $task->load([
            'project',
            'creator',
            'assignee',
            'comments',
        ]);

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

        return response()->json(new TaskResource($task), 201);
    }

    public function update(UpdateTaskRequest $request, Task $task): TaskResource
    {
        $task = $this->taskService->update(
            $task,
            $request->validated()
        );

        return new TaskResource($task);
    }

    public function destroy(Task $task): JsonResponse
    {
        $this->taskService->delete($task);

        return response()->json([
            'message' => 'Task deleted'
        ]);
    }
}
