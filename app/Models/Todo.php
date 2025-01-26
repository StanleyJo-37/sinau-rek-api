<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Todo extends Model
{
    //
    protected $table = 'todos';

    protected $fillable = [
        'title',
        'description',
        'start_time',
        'deadline',
        'project_id'
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'todos_users',
            'todo_id',
            'user_id',
            'id',
            'id'
        );
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(
            Project::class,
            'project_id',
            'id'
        );
    }
}
