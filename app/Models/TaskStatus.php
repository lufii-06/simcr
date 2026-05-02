<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskStatus extends Model
{
    use \App\Traits\EncryptsRouteKey;

    protected $fillable = ['name'];
}
