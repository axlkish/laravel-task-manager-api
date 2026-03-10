<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Task;
use App\Models\Project;
use App\Enums\TaskStatus;
use App\Enums\TaskPriority;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        $project = Project::factory()->create([
            'owner_id' => '1'
        ]);

        return [
            'project_id' => $project->id,
            'creator_id' => '1',
            'assignee_id' => User::factory()->create()->id,
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'status' => $this->faker->randomElement(TaskStatus::cases())->value,
            'priority' => $this->faker->randomElement(TaskPriority::cases())->value,
            'due_date' => $this->faker->dateTimeBetween('+1 day', '+1 month'),
        ];
    }
}
