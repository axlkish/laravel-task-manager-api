<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

readonly class TaskFilters
{
    public function __construct(
        private Request $request
    )
    {
    }

    public function apply(Builder $query): Builder
    {
        $this->status($query);
        $this->priority($query);
        $this->assignee($query);
        $this->sort($query);
        return $query;
    }

    private function status(Builder $query): void
    {
        if ($this->request->filled('status')) {
            $query->where('status', $this->request->status);
        }
    }

    private function priority(Builder $query): void
    {
        if ($this->request->filled('priority')) {
            $query->where('priority', $this->request->priority);
        }
    }

    private function assignee(Builder $query): void
    {
        if ($this->request->filled('assignee_id')) {
            $query->where('assignee_id', $this->request->assignee_id);
        }
    }

    private function sort(Builder $query): void
    {
        if ($this->request->filled('sort')) {
            $sort = $this->request->sort;
            $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
            $column = ltrim($sort, '-');

            $allowed = [
                'created_at',
                'due_date',
                'priority',
            ];

            if (in_array($column, $allowed)) {
                $query->orderBy($column, $direction);
                return;
            }
        }

        $query->latest();
    }
}
