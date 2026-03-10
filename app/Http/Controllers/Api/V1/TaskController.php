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
use OpenApi\Annotations as OA;

class TaskController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly TaskService $taskService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/v1/tasks",
     *     summary="Get tasks list",
     *     description="Returns paginated list of tasks.",
     *     tags={"Tasks"},
     *
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         required=true,
     *         description="Bearer access token",
     *         @OA\Schema(
     *             type="string",
     *             example="Bearer 1|abcdef123456789"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="Full-text search query",
     *         @OA\Schema(
     *             type="string",
     *             example="login"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         description="Filter tasks by status",
     *         @OA\Schema(
     *             type="string",
     *             example="open"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="priority",
     *         in="query",
     *         required=false,
     *         description="Filter tasks by priority",
     *         @OA\Schema(
     *             type="string",
     *             example="high"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Number of items per page (max 100)",
     *         @OA\Schema(
     *             type="integer",
     *             example=15
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of tasks"
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min($request->integer('per_page', 15), 100);
        $cacheKey = 'tasks:' . md5($request->fullUrl());

        $tasks = Cache::remember(
            $cacheKey,
            env('CACHE_TASKS_TTL', 60),
            function () use ($request, $perPage) {
                // full-text search
                if ($request->filled('search')) {
                    return Task::search($request->search)
                        ->query(fn ($query) => (new TaskFilters($request))
                            ->apply($query->with(['project', 'creator', 'assignee']))
                        )
                        ->paginate($perPage);
                }

                // basic
                $filters = new TaskFilters($request);

                return $filters
                    ->apply(
                        Task::query()->with(['project', 'creator', 'assignee'])
                    )
                    ->paginate($perPage);
            }
        );

        return TaskResource::collection($tasks);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/tasks/{id}",
     *     summary="Get task by ID",
     *     description="Returns detailed information about a specific task.",
     *     tags={"Tasks"},
     *
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         required=true,
     *         description="Bearer access token",
     *         @OA\Schema(
     *             type="string",
     *             example="Bearer 1|abcdef123456789"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Task ID",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Task details"
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Task not found"
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function show(Task $task): TaskResource
    {
        $task = Cache::remember(
            "task:{$task->id}",
            env('CACHE_TASKS_TTL', 60),
            fn () => $task->load(['project','creator','assignee','comments'])
        );

        return new TaskResource($task);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/tasks",
     *     summary="Create task",
     *     description="Creates a new task.",
     *     tags={"Tasks"},
     *
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         required=true,
     *         description="Bearer access token",
     *         @OA\Schema(
     *             type="string",
     *             example="Bearer 1|abcdef123456789"
     *         )
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="Task data",
     *         @OA\JsonContent(
     *             required={"project_id","title"},
     *
     *             @OA\Property(
     *                 property="project_id",
     *                 type="integer",
     *                 description="Project ID",
     *                 example=1
     *             ),
     *
     *             @OA\Property(
     *                 property="assignee_id",
     *                 type="integer",
     *                 description="User assigned to the task",
     *                 example=2
     *             ),
     *
     *             @OA\Property(
     *                 property="title",
     *                 type="string",
     *                 description="Task title",
     *                 example="Fix OAuth login bug"
     *             ),
     *
     *             @OA\Property(
     *                 property="description",
     *                 type="string",
     *                 description="Task description",
     *                 example="Users cannot login via Google OAuth"
     *             ),
     *
     *             @OA\Property(
     *                 property="priority",
     *                 type="string",
     *                 description="Task priority",
     *                 example="high"
     *             ),
     *
     *             @OA\Property(
     *                 property="due_date",
     *                 type="string",
     *                 description="Task due date",
     *                 example="2026-03-20 12:00:00"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Task successfully created"
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed"
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Project not found"
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
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

    /**
     * @OA\Patch(
     *     path="/api/v1/tasks/{id}",
     *     summary="Update task",
     *     description="Updates an existing task.",
     *     tags={"Tasks"},
     *
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         required=true,
     *         description="Bearer access token",
     *         @OA\Schema(
     *             type="string",
     *             example="Bearer 1|abcdef123456789"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Task ID",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="Task fields to update",
     *         @OA\JsonContent(
     *
     *             @OA\Property(
     *                 property="title",
     *                 type="string",
     *                 description="Task title",
     *                 example="Fix OAuth login bug"
     *             ),
     *
     *             @OA\Property(
     *                 property="description",
     *                 type="string",
     *                 description="Task description",
     *                 example="OAuth login fails for some users"
     *             ),
     *
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 description="Task status",
     *                 example="review"
     *             ),
     *
     *             @OA\Property(
     *                 property="priority",
     *                 type="string",
     *                 description="Task priority",
     *                 example="high"
     *             ),
     *
     *             @OA\Property(
     *                 property="assignee_id",
     *                 type="integer",
     *                 description="User assigned to the task",
     *                 example=2
     *             ),
     *
     *             @OA\Property(
     *                  property="due_date",
     *                  type="string",
     *                  description="Task due date",
     *                  example="2026-03-20 12:00:00"
     *              )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Task successfully updated"
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Task not found"
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed"
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/api/v1/tasks/{id}",
     *     summary="Delete task",
     *     description="Deletes an existing task.",
     *     tags={"Tasks"},
     *
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         required=true,
     *         description="Bearer access token",
     *         @OA\Schema(
     *             type="string",
     *             example="Bearer 1|abcdef123456789"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Task ID",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Task deleted"
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Task not found"
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
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
