<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PullRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'repository_id',
        'user_id',
        'title',
        'description',
        'source_branch',
        'target_branch',
        'status',
    ];

    public function repository()
    {
        return $this->belongsTo(Repository::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
