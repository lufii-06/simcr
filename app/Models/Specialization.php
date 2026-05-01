<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Specialization extends Model
{
    use \App\Traits\EncryptsRouteKey;

    protected $fillable = ['name'];

    public function developers()
    {
        return $this->hasMany(Developer::class);
    }
}
