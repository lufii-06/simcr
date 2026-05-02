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
    }

    protected $casts = [
        'is_completed' => 'boolean',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
