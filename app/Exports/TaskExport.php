<?php

namespace App\Exports;

use App\Models\Task;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class TaskExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $filters;
    private $rowNumber = 0;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Task::with(['project', 'assignee', 'status']);
        
        if (!empty($this->filters['project_id'])) $query->where('project_id', $this->filters['project_id']);
        if (!empty($this->filters['assigned_to'])) $query->where('assigned_to', $this->filters['assigned_to']);
        if (!empty($this->filters['status_id'])) $query->where('task_status_id', $this->filters['status_id']);
        if (!empty($this->filters['keyword'])) {
            $keyword = $this->filters['keyword'];
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                  ->orWhere('code', 'like', "%{$keyword}%")
                  ->orWhereHas('project', function ($pq) use ($keyword) {
                      $pq->where('name', 'like', "%{$keyword}%");
                  })
                  ->orWhereHas('assignee', function ($aq) use ($keyword) {
                      $aq->where('name', 'like', "%{$keyword}%");
                  });
            });
        }
        
        return $query->latest()->get();
    }

    public function headings(): array
    {
        return [
            "NO", 
            "KODE TASK", 
            "PROJECT", 
            "JUDUL TASK", 
            "ASSIGNEE", 
            "STATUS", 
            "PROGRESS (%)"
        ];
    }

    public function map($task): array
    {
        $this->rowNumber++;
        return [
            $this->rowNumber,
            $task->code,
            $task->project->name,
            $task->title,
            $task->assignee->name ?? 'Unassigned',
            $task->status->name,
            round($task->progress) . '%'
        ];
    }
}
