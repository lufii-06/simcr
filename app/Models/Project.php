<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes, \App\Traits\EncryptsRouteKey;

    protected $fillable = [
        'code',
        'client_id',
        'project_status_id',
        'user_id',
        'name',
        'description',
        'start_date',
        'end_date',
    ];

    protected static function booted()
    {
        static::created(function ($project) {
            $project->code = 'PRJ-' . str_pad($project->id, 4, '0', STR_PAD_LEFT);
            $project->save();
        });
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function status()
    {
        return $this->belongsTo(ProjectStatus::class, 'project_status_id');
    }

    public function repository()
    {
        return $this->hasOne(Repository::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function developers()
    {
        return $this->hasMany(ProjectDeveloper::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
