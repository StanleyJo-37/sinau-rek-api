<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Project extends Model
{
    //
    protected $table = 'projects';

    protected $fillable = [
        'title',
        'description',
        'start_time',
        'deadline'
    ];

    public function todos(): HasMany
    {
        return $this->hasMany(
            Todo::class,
            'project_id',
            'id'
        );
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'projects_users',
            'project_id',
            'user_id',
            'id',
            'id'
        );
    }
}
