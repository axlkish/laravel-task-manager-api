<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use App\Models\Comment;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        $users = User::factory(4)->create();

        $allUsers = $users->push($admin);

        $projects = Project::factory(3)->create([
            'owner_id' => $admin->id,
        ]);

        $tasks = Task::factory(20)->create([
            'project_id' => $projects->random()->id,
            'creator_id' => $allUsers->random()->id,
            'assignee_id' => $allUsers->random()->id,
        ]);

        Comment::factory(40)->create([
            'task_id' => $tasks->random()->id,
            'user_id' => $allUsers->random()->id,
        ]);
    }
}
