<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskChecklist extends Model
{
    protected $fillable = [
        'code',
        'task_id',
        'item_text',
        'is_completed',
    ];

    protected static function booted()
    {
        static::created(function ($checklist) {
            $checklist->code = 'CHK-' . str_pad($checklist->id, 4, '0', STR_PAD_LEFT);
            $checklist->save();
        });

        static::saved(function ($checklist) {
            $task = $checklist->task;
            if ($task) {
                $total = $task->checklists()->count();
                if ($total > 0) {
                    $completed = $task->checklists()->where('is_completed', true)->count();
                    
                    if ($completed === $total) {
                        $statusName = 'Done';
                    } elseif ($completed > 0) {
                        $statusName = 'In Progress';
                    } else {
                        $statusName = 'To Do';
                    }

                    $status = \App\Models\TaskStatus::where('name', $statusName)->first();
                    if ($status && $task->task_status_id !== $status->id) {
                        $oldStatusName = $task->status->name ?? 'Unknown';
                        $task->update([
                            'task_status_id' => $status->id
                        ]);

                        // Log the status change
                        \App\Models\TaskLog::create([
                            'task_id' => $task->id,
                            'user_id' => auth()->id() ?? \App\Models\User::where('role', 'pm')->first()->id ?? null,
                            'action' => 'status_changed',
                            'details' => "System automatically updated status from '{$oldStatusName}' to '{$statusName}' due to checklist updates.",
                        ]);
                    }
                }
            }
        });
    }

    protected $casts = [
        'is_completed' => 'boolean',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
