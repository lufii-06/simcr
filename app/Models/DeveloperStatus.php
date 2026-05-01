<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeveloperStatus extends Model
{
    use SoftDeletes, \App\Traits\EncryptsRouteKey;

    protected $fillable = ['name'];

    public function projectDevelopers()
    {
        return $this->hasMany(ProjectDeveloper::class);
    }
}
