<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Repository extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'project_id',
        'name',
        'url',
        'default_branch',
        'status',
    ];

    protected static function booted()
    {
        static::created(function ($repo) {
            $repo->code = 'REPO-' . str_pad($repo->id, 4, '0', STR_PAD_LEFT);
            $repo->save();
        });
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function getRouteKeyName()
    {
        return 'name';
    }
}
