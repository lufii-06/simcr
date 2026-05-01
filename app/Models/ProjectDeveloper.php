<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectDeveloper extends Model
{
    protected $fillable = [
        'project_id',
        'user_id',
        'developer_status_id',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function role()
    {
        return $this->belongsTo(DeveloperStatus::class, 'developer_status_id');
    }
}
