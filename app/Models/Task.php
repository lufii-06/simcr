<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use \App\Traits\EncryptsRouteKey;

    protected $fillable = [
        'code',
        'project_id',
        'created_by',
        'assigned_to',
        'task_status_id',
        'title',
        'description',
    ];

    protected static function booted()
    {
        static::created(function ($task) {
            $task->code = 'T-' . str_pad($task->id, 4, '0', STR_PAD_LEFT);
            $task->save();
        });
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function status()
    {
        return $this->belongsTo(TaskStatus::class, 'task_status_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function checklists()
    {
        return $this->hasMany(TaskChecklist::class);
    }

    public function getProgressAttribute()
    {
        $total = $this->checklists()->count();
        if ($total === 0) return 0;
        
        $completed = $this->checklists()->where('is_completed', true)->count();
        return ($completed / $total) * 100;
    }

    public function getProgressTextAttribute()
    {
        $total = $this->checklists()->count();
        $completed = $this->checklists()->where('is_completed', true)->count();
        return "{$completed}/{$total}";
    }
}
