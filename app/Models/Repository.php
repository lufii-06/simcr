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
        'url',
        'default_branch',
        'status',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
