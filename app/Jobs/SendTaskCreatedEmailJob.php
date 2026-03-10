<?php

namespace App\Jobs;

use App\Models\Task;
use App\Mail\TaskCreatedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendTaskCreatedEmailJob implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    public function __construct(
        private Task $task
    ) {}

    public function handle(): void
    {
        if ($this->task->assignee) {
            Mail::to($this->task->assignee->email)
                ->send(new TaskCreatedMail($this->task));
        }
    }
}
