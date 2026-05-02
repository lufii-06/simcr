<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskStatus extends Model
{
    use \App\Traits\EncryptsRouteKey;

    protected $fillable = ['name'];

    public function tasks()
    {
        return $this->hasMany(Task::class, 'task_status_id');
    }
}
