<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Repository extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'name',
        'is_public',
        'access_token',
        'url',
        'default_branch',
        'status',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function getRouteKeyName()
    {
        return 'name';
    }
}
