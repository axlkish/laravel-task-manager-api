<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use App\Enums\TaskStatus;

class TaskService
{
    public function create(
        Project $project,
        User $creator,
        array $data
    ): Task
    {
        $task = new Task();
        $task->project_id = $project->id;
        $task->creator_id = $creator->id;
        $task->assignee_id = $data['assignee_id'] ?? null;
        $task->title = $data['title'];
        $task->description = $data['description'] ?? null;
        $task->status = TaskStatus::OPEN;
        $task->priority = $data['priority'];
        $task->due_date = $data['due_date'] ?? null;
        $task->save();
        return $task;
    }

    public function update(Task $task, array $data): Task
    {
        $task->update($data);
        return $task;
    }

    public function delete(Task $task): void
    {
        $task->delete();
    }
}
