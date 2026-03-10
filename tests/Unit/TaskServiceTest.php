<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use App\Models\Project;
use App\Services\TaskService;
use App\Enums\TaskStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaskServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_can_be_created(): void
    {
        $service = new TaskService();
        $user = User::factory()->create();

        $project = Project::factory()->create([
            'owner_id' => $user->id
        ]);

        $data = [
            'title' => 'Test task',
            'description' => 'Test description',
            'priority' => 'high',
        ];

        $task = $service->create($project, $user, $data);
        $this->assertInstanceOf(Task::class, $task);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Test task',
            'creator_id' => $user->id
        ]);
    }

    public function test_task_can_be_updated(): void
    {
        $service = new TaskService();
        $task = Task::factory()->create();

        $service->update($task, [
            'title' => 'Updated task'
        ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated task'
        ]);
    }

    public function test_status_can_be_changed(): void
    {
        $service = new TaskService();

        $task = Task::factory()->create([
            'status' => TaskStatus::OPEN
        ]);

        $service->changeStatus($task, TaskStatus::DONE);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => TaskStatus::DONE->value
        ]);
    }
}
