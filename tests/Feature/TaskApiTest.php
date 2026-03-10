<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use App\Models\Project;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticate(): User
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        return $user;
    }

    public function test_tasks_list_returns_data(): void
    {
        $this->authenticate();
        Task::factory()->count(5)->create();
        $response = $this->getJson('/api/v1/tasks');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data'
            ]);
    }

    public function test_task_can_be_created(): void
    {
        $user = $this->authenticate();

        $project = Project::factory()->create([
            'owner_id' => $user->id
        ]);

        $payload = [
            'project_id' => $project->id,
            'assignee_id' => $user->id,
            'title' => 'Test task',
            'description' => 'Test description',
            'priority' => 'high',
        ];

        $response = $this->postJson('/api/v1/tasks', $payload);
        $response->assertStatus(201);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Test task'
        ]);
    }

    public function test_task_can_be_updated(): void
    {
        $user = $this->authenticate();

        $task = Task::factory()->create([
            'creator_id' => $user->id
        ]);

        $response = $this->patchJson("/api/v1/tasks/{$task->id}", [
            'status' => 'done'
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'done'
        ]);
    }

    public function test_task_can_be_deleted(): void
    {
        $user = $this->authenticate();

        $task = Task::factory()->create([
            'creator_id' => $user->id
        ]);

        $response = $this->deleteJson("/api/v1/tasks/{$task->id}");
        $response->assertStatus(200);

        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id
        ]);
    }

    public function test_user_cannot_delete_foreign_task(): void
    {
        $this->authenticate();
        $task = Task::factory()->create();
        $response = $this->deleteJson("/api/v1/tasks/{$task->id}");
        $response->assertStatus(403);
    }
}
